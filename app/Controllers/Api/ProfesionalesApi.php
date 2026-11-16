<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * ProfesionalesApi
 * Her Beauty Center
 *
 * Gestiona las operaciones administrativas relacionadas con los profesionales
 * del establecimiento.
 *
 * Sus responsabilidades principales son:
 * 1. Crear nuevos profesionales y sus correspondientes cuentas de usuario.
 * 2. Asignar los servicios que puede ofrecer cada profesional.
 * 3. Editar los datos personales y profesionales.
 * 4. Eliminar profesionales, respetando las restricciones de integridad.
 * 5. Actualizar la contraseña de una cuenta.
 * 6. Modificar el orden de visualización de los profesionales.
 * 7. Activar o desactivar las cuentas de los profesionales.
 *
 * Todos los métodos requieren que el usuario tenga una sesión iniciada
 * y el rol de administrador.
 */
class ProfesionalesApi extends BaseController
{
    // Incorpora métodos para generar respuestas HTTP en formato JSON.
    use ResponseTrait;

    /**
     * Crea un nuevo profesional.
     *
     * Primero registra sus datos personales en la tabla usuario y luego
     * crea su perfil profesional asociado mediante el campo u_id.
     *
     * Ambas inserciones se realizan dentro de una transacción para evitar
     * que quede creada una cuenta sin su correspondiente perfil profesional.
     *
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function crear()
    {
        // Verifica que exista una sesión iniciada y que el usuario sea administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Obtiene los datos enviados por el formulario o mediante una petición JSON.
        $data = $this->getRequestData();

        // Recupera los datos aceptando distintos nombres de campo.
        // El operador ?? utiliza el primer valor que exista y no sea null.
        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');
        $password = $data['password'] ?? $data['u_pass'] ?? $data['pass'] ?? '';
        $title = trim($data['title'] ?? $data['p_titulo'] ?? $data['titulo'] ?? '');

        // Convierte el año recibido a entero.
        // Si no se proporciona, utiliza el año actual.
        $year = isset($data['year']) ? intval($data['year']) : (isset($data['p_anioInicioAct']) ? intval($data['p_anioInicioAct']) : date('Y'));

        // Comprueba que todos los campos obligatorios tengan contenido.
        if (
            empty($name) ||
            empty($email) ||
            empty($phone) ||
            empty($password) ||
            empty($title)
        ) {
            return $this->fail('Datos incompletos.');
        }

        // Valida que el nombre contenga al menos 7 letras y espacios.
        // Se permiten letras con acentos y la letra ñ.
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$/u', $name)) {
            return $this->fail('El nombre debe tener al menos 7 letras y no contener signos extraños.');
        }

        // Comprueba que el correo tenga una estructura válida.
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }

        // Verifica que el teléfono tenga al menos 8 caracteres.
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        // Evalúa la complejidad de la contraseña contando:
        // - Letras mayúsculas.
        // - Letras minúsculas.
        // - Números.
        // - Símbolos o caracteres especiales.
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        // Exige una longitud mínima de 8 caracteres y al menos
        // 2 caracteres de cada uno de los cuatro grupos anteriores.
        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        // Comprueba que el título profesional contenga al menos 3 letras.
        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        // Establece la conexión con la base de datos utilizando
        // la configuración definida en Config\Database.
        $db = \Config\Database::connect();

        // Comprueba que no exista otra cuenta registrada con el mismo correo.
        // getRow() devuelve un único registro o null si no encuentra resultados.
        $existing = $db->table('usuario')->where('u_correo', $email)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe una cuenta con este correo.');
        }

        // Inicia una transacción para ejecutar las inserciones como una operación conjunta.
        $db->transStart();

        // 1. Registra los datos personales y de acceso en la tabla usuario.
        $usuarioData = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => !empty($phone) ? $phone : null,
            // Genera un hash seguro de la contraseña antes de almacenarla.
            'u_pass' => password_hash($password, PASSWORD_DEFAULT),
            // La cuenta se crea inicialmente activa.
            'u_activo' => 1,
            'u_fRegistro' => date('Y-m-d H:i:s')
        ];

        // Inserta el registro y recupera el ID autogenerado del usuario.
        $db->table('usuario')->insert($usuarioData);
        $userId = $db->insertID();

        // 2. Crea el perfil profesional y lo vincula con el usuario recién registrado.
        $profesionalData = [
            'u_id' => $userId,
            'p_titulo' => $title,
            'p_anioInicioAct' => $year
        ];

        $db->table('profesional')->insert($profesionalData);

        // Finaliza la transacción y comprueba si las operaciones se ejecutaron correctamente.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al crear la profesional.');
        }

        // Devuelve HTTP 201 (Created) indicando que el profesional fue creado.
        return $this->respondCreated(['success' => true, 'message' => 'Profesional creada exitosamente.']);
    }

    /**
     * Actualiza los servicios asignados a un profesional.
     *
     * Reemplaza las relaciones existentes en la tabla intermedia
     * profesional_servicio por las nuevas seleccionadas.
     *
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function servicios()
    {
        // Verifica que el usuario tenga una sesión administrativa.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Obtiene los datos enviados en la petición.
        $data = $this->getRequestData();

        // Recupera el identificador del profesional.
        // Si no se recibe ninguno de los campos esperados, utiliza 0.
        $profesional_id = intval($data['profesional_id'] ?? $data['p_id'] ?? 0);
        if (!$profesional_id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        // Obtiene el listado de servicios.
        // Si el valor recibido no es un array, utiliza un array vacío.
        $servicios = isset($data['servicios']) && is_array($data['servicios']) ? $data['servicios'] : [];

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Inicia una transacción para reemplazar las asignaciones de forma conjunta.
        $db->transStart();

        // 1. Elimina las relaciones actuales del profesional.
        // No elimina los servicios de la tabla servicio, solamente sus asignaciones.
        $db->table('profesional_servicio')->where('p_id', $profesional_id)->delete();

        // 2. Inserta las nuevas relaciones seleccionadas.
        if (!empty($servicios)) {
            $insertData = [];

            // Recorre los identificadores recibidos y prepara
            // un registro por cada relación profesional-servicio.
            foreach ($servicios as $id_servicio) {
                $insertData[] = [
                    'p_id' => $profesional_id,
                    'p_sId' => intval($id_servicio)
                ];
            }

            // Inserta todos los registros preparados mediante una inserción por lote.
            $db->table('profesional_servicio')->insertBatch($insertData);
        }

        // Finaliza la transacción y verifica su estado.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al asignar los servicios.');
        }

        // Informa que las asignaciones se actualizaron correctamente.
        return $this->respond(['success' => true, 'message' => 'Servicios asignados correctamente.']);
    }

    /**
     * Elimina un profesional y los registros relacionados con su perfil.
     *
     * Antes de eliminarlo, comprueba que no sea la cuenta del administrador
     * que está en sesión y que no tenga turnos registrados.
     *
     * @param int $id Identificador del profesional.
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function eliminar($id)
    {
        // Verifica que el usuario tenga permisos de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el identificador del profesional.
        if (!$id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        // Busca el profesional que se desea eliminar.
        $db = \Config\Database::connect();
        $profesional = $db->table('profesional')->where('p_id', $id)->get()->getRow();

        // Si no existe, devuelve una respuesta HTTP 404.
        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        // 1. Evita que el administrador elimine su propia cuenta.
        // Compara el usuario asociado al profesional con el usuario de la sesión.
        if ((int) $profesional->u_id === (int) $session->get('usuario_id')) {
            return $this->fail('No podés eliminar tu propia cuenta mientras estés en sesión.');
        }

        // 2. Comprueba si el profesional tiene turnos registrados.
        // countAllResults() devuelve la cantidad de registros encontrados.
        $tieneTurnos = $db->table('turno')->where('t_pId', $id)->countAllResults();

        // Si existen turnos, impide la eliminación para conservar el historial.
        // En ese caso, se debe utilizar la baja lógica mediante el estado de la cuenta.
        if ($tieneTurnos > 0) {
            return $this->fail('No es posible eliminar la profesional porque posee turnos registrados en el establecimiento. Debes desactivarla mediante la baja lógica (interruptor de estado).');
        }

        // Inicia una transacción para eliminar los registros relacionados.
        $db->transStart();

        // Elimina el registro de administrador si el profesional también cumple ese rol.
        $db->table('administrador')->where('a_pId', $id)->delete();

        // Elimina las relaciones entre el profesional y los servicios asignados.
        $db->table('profesional_servicio')->where('p_id', $id)->delete();

        // Elimina las redes sociales asociadas al profesional.
        $db->table('red_social')->where('rs_pId', $id)->delete();

        // Elimina los horarios configurados para el profesional.
        // Esta operación se realiza luego de comprobar que no tenga turnos registrados.
        $db->table('horario')->where('h_pId', $id)->delete();

        // Elimina el perfil de la tabla profesional.
        $db->table('profesional')->where('p_id', $id)->delete();

        // Finalmente, elimina la cuenta de usuario vinculada al perfil.
        $db->table('usuario')->where('u_id', $profesional->u_id)->delete();

        // Finaliza la transacción y comprueba si todas las eliminaciones fueron exitosas.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar la profesional.');
        }

        // Devuelve HTTP 200 indicando que el profesional fue eliminado.
        return $this->respondDeleted(['success' => true, 'message' => 'Profesional eliminada correctamente.']);
    }

    /**
     * Actualiza los datos personales y profesionales de una cuenta.
     *
     * Modifica la información del usuario en la tabla usuario y los datos
     * específicos de su perfil en la tabla profesional.
     *
     * @param int $id Identificador del profesional.
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function editar($id)
    {
        // Verifica que exista una sesión administrativa.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el ID del profesional.
        if (!$id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        // Obtiene los datos enviados en la petición.
        $data = $this->getRequestData();

        // Recupera los datos personales aceptando distintos nombres de campo.
        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');
        $title = trim($data['title'] ?? $data['p_titulo'] ?? $data['titulo'] ?? '');

        // Recupera el año de inicio de actividad o utiliza el año actual.
        $year = isset($data['year']) ? intval($data['year']) : (isset($data['p_anioInicioAct']) ? intval($data['p_anioInicioAct']) : date('Y'));

        // Comprueba que los campos obligatorios estén completos.
        if (
            empty($name) ||
            empty($email) ||
            empty($phone) ||
            empty($title)
        ) {
            return $this->fail('Datos incompletos.');
        }

        // Verifica que el nombre tenga al menos 7 caracteres.
        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }

        // Valida el formato del correo electrónico.
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }

        // Comprueba que el teléfono tenga al menos 8 caracteres.
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        // Verifica que el título profesional contenga al menos 3 letras.
        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Busca el perfil profesional que se desea modificar.
        $profesional = $db->table('profesional')->where('p_id', $id)->get()->getRow();

        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        // Comprueba que el correo no esté registrado en otra cuenta.
        // Se excluye el usuario asociado al profesional que se está editando,
        // ya que puede conservar su propio correo electrónico.
        $existing = $db->table('usuario')->where('u_correo', $email)->where('u_id !=', $profesional->u_id)->get()->getRow();

        if ($existing) {
            return $this->fail('Ya existe otra cuenta con este correo.');
        }

        // Inicia una transacción para actualizar los datos personales
        // y profesionales como parte de una misma operación.
        $db->transStart();

        // Prepara los datos que se actualizarán en la tabla usuario.
        $usuarioData = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => $phone
        ];

        // Si se recibió un nuevo estado, lo convierte a entero.
        if (isset($data['u_activo']) || isset($data['activo'])) {
            $nuevoActivo = intval($data['u_activo'] ?? $data['activo']);

            // Impide que el administrador desactive su propia cuenta.
            // Si se trata de otro usuario, permite actualizar el estado.
            // También permite que el usuario actual conserve o establezca el valor 1.
            if ((int) $profesional->u_id !== (int) $session->get('usuario_id') || $nuevoActivo === 1) {
                $usuarioData['u_activo'] = $nuevoActivo;
            }
        }

        // Actualiza los datos personales en la tabla usuario.
        $db->table('usuario')->where('u_id', $profesional->u_id)->update($usuarioData);

        // Prepara los datos específicos del perfil profesional.
        $profesionalData = [
            'p_titulo' => $title,
            'p_anioInicioAct' => $year
        ];

        // Actualiza el título y el año de inicio de actividad del profesional.
        $db->table('profesional')->where('p_id', $id)->update($profesionalData);

        // Finaliza la transacción y comprueba si las actualizaciones fueron exitosas.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al actualizar la profesional.');
        }

        // Devuelve una respuesta indicando que los datos fueron actualizados.
        return $this->respond(['success' => true, 'message' => 'Profesional actualizada exitosamente.']);
    }

    /**
     * Actualiza la contraseña de un usuario.
     *
     * Esta operación está destinada al administrador y permite establecer
     * una nueva contraseña para la cuenta indicada.
     *
     * @param int $id_usuario Identificador del usuario cuya contraseña se modificará.
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function password($id_usuario)
    {
        // Verifica que el usuario tenga permisos de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el identificador del usuario.
        if (!$id_usuario) {
            return $this->fail('ID de usuario no proporcionado.');
        }

        // Obtiene los datos enviados en la petición.
        $data = $this->getRequestData();

        // Recupera la nueva contraseña aceptando distintos nombres de campo.
        $password = trim($data['password'] ?? $data['u_pass'] ?? $data['new_password'] ?? '');

        if (empty($password)) {
            return $this->fail('Contraseña no proporcionada.');
        }

        // Cuenta los distintos tipos de caracteres presentes en la contraseña.
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        // Aplica los requisitos mínimos de complejidad de la contraseña.
        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Busca la cuenta a la que se le cambiará la contraseña.
        $usuario = $db->table('usuario')->where('u_id', $id_usuario)->get()->getRow();

        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        // Genera un nuevo hash de la contraseña y actualiza el registro.
        // PASSWORD_DEFAULT utiliza el algoritmo de hash recomendado por PHP.
        $db->table('usuario')->where('u_id', $id_usuario)->update([
            'u_pass' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Informa que la contraseña fue actualizada.
        return $this->respond(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }

    /**
     * Modifica el orden de visualización de los profesionales.
     *
     * Recibe un array de identificadores y asigna una posición numérica
     * a cada profesional según el orden en el que aparecen.
     *
     * @return ResponseInterface Respuesta JSON con el resultado de la operación.
     */
    public function ordenar()
    {
        // Verifica que el usuario tenga permisos de administrador.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Obtiene los datos enviados en la petición.
        $data = $this->getRequestData();

        // Recupera el array de IDs, aceptando dos nombres posibles para el campo.
        $orden = $data['p_orden'] ?? $data['orden'] ?? null;

        // Comprueba que se haya recibido un array con identificadores.
        if (!is_array($orden) || empty($orden)) {
            return $this->fail('Se requiere un array de IDs en orden.');
        }

        // Establece la conexión e inicia una transacción.
        $db = \Config\Database::connect();
        $db->transStart();

        // Recorre el array:
        // - $position representa la posición del elemento dentro del array.
        // - $id_profesional representa el identificador del profesional.
        foreach ($orden as $position => $id_profesional) {
            $db->table('profesional')
                ->where('p_id', intval($id_profesional))
                // Asigna posiciones comenzando desde 1.
                ->update(['p_orden' => intval($position) + 1]);
        }

        // Finaliza la transacción y verifica su resultado.
        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al guardar el orden.');
        }

        // Informa que el orden de visualización fue guardado.
        return $this->respond(['success' => true, 'message' => 'Orden de profesionales guardado correctamente.']);
    }

    /**
     * Activa o desactiva la cuenta de un profesional.
     *
     * Modifica el campo u_activo de la cuenta de usuario asociada.
     * No elimina registros de la base de datos.
     *
     * @param int $id Identificador del profesional.
     * @return ResponseInterface Respuesta JSON con el nuevo estado de la cuenta.
     */
    public function toggleEstado($id)
    {
        // Verifica que exista una sesión administrativa.
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el ID del profesional.
        if (!$id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        // Establece la conexión y busca el perfil profesional.
        $db = \Config\Database::connect();
        $profesional = $db->table('profesional')->where('p_id', intval($id))->get()->getRow();

        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        // Impide que el administrador cambie el estado de su propia cuenta.
        if ($profesional->u_id == $session->get('usuario_id')) {
            return $this->fail('No puedes cambiar el estado de tu propia cuenta mientras estás en sesión.');
        }

        // Busca la cuenta de usuario asociada al perfil profesional.
        $usuario = $db->table('usuario')->where('u_id', $profesional->u_id)->get()->getRow();

        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        // Invierte el estado actual:
        // Si está activo (1), pasa a inactivo (0).
        // Si está inactivo (0), pasa a activo (1).
        $nuevoEstado = $usuario->u_activo ? 0 : 1;

        // Actualiza el estado de la cuenta en la tabla usuario.
        $db->table('usuario')->where('u_id', $profesional->u_id)->update(['u_activo' => $nuevoEstado]);

        // Construye el mensaje según el estado resultante.
        $accion = $nuevoEstado ? 'activada' : 'dada de baja (inactiva)';

        // Devuelve el resultado y el nuevo estado para que el cliente
        // pueda actualizar la interfaz sin realizar otra consulta.
        return $this->respond([
            'success' => true,
            'message' => "Profesional {$accion} exitosamente.",
            'nuevo_estado' => $nuevoEstado
        ]);
    }
}
