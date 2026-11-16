<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * ClientesApi
 * Her Beauty Center
 *
 * Controlador de API encargado de administrar las operaciones
 * relacionadas con los clientes del sistema.
 *
 * Sus responsabilidades principales son:
 * 1. Crear nuevos clientes y sus correspondientes usuarios.
 * 2. Editar los datos personales y las consideraciones de un cliente.
 * 3. Eliminar clientes, verificando que no tengan turnos activos.
 * 4. Actualizar la contraseña de un usuario.
 * 5. Convertir un cliente en profesional.
 * 6. Activar o desactivar las cuentas de los clientes.
 *
 * Las respuestas se envían en formato JSON mediante ResponseTrait.
 * Todas las operaciones están restringidas a usuarios con rol administrador.
 */
class ClientesApi extends BaseController
{
    use ResponseTrait;

    //----------------------
    // CREAR CLIENTES
    //----------------------
    public function crear()
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Obtiene los datos enviados en la solicitud, ya sea mediante POST
        // o mediante JSON, utilizando el método heredado de BaseController.
        $data = $this->getRequestData();

        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');
        $password = $data['password'] ?? $data['u_pass'] ?? $data['pass'] ?? '';

        // Comprueba que todos los campos obligatorios tengan contenido.
        if (
            empty($name) ||
            empty($email) ||
            empty($phone) ||
            empty($password)
        ) {
            return $this->fail('Datos incompletos.');
        }

        // Controla Nombre
        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }
        // Controla Mail
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }
        // Controla Teléfono
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        // Cuenta la cantidad de caracteres de cada tipo que contiene
        // la contraseña para comprobar que cumpla con los requisitos
        // mínimos de complejidad.
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        $db = \Config\Database::connect();

        // Comprueba si ya existe un usuario registrado conn ese mail
        $existing = $db->table('usuario')->where('u_correo', $email)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe una cuenta con este correo.');
        }

        // Inicia una transacción y habilita el lanzamiento de excepciones
        // ante errores de las operaciones transaccionales.
        $db->transException(true)->transStart();

        try {
            // Prepara los datos que se insertarán en la tabla usuario.
            $usuarioData = [
                'u_nbreCompleto' => $name,
                'u_correo' => $email,
                'u_tel' => $phone,
                'u_pass' => password_hash($password, PASSWORD_DEFAULT),
                'u_activo' => 1,
                'u_fRegistro' => date('Y-m-d H:i:s')
            ];

            // Inserta el usuario y obtiene el identificador generado
            // automáticamente por la base de datos.
            $db->table('usuario')->insert($usuarioData);
            $userId = $db->insertID();

            // Crea el registro correspondiente en la tabla cliente,
            // vinculándolo con el usuario recién creado.
            $db->table('cliente')->insert([
                'c_uId' => $userId
            ]);

            // Finaliza la transacción. Si las operaciones fueron exitosas,
            // los cambios quedan confirmados.
            $db->transComplete();

            // Devuelve una respuesta HTTP 201 (Created) indicando
            // que el cliente fue creado correctamente.
            return $this->respondCreated(['success' => true, 'message' => 'Cliente creado exitosamente.']);

        } catch (\Exception $e) {
            // Si ocurre una excepción, intenta revertir las operaciones
            // realizadas durante la transacción.
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            // Devuelve una respuesta HTTP 500 indicando un error interno
            // durante la operación con la base de datos.
            return $this->failServerError('Error en base de datos: ' . $e->getMessage());
        }
    }


    //----------------------
    // ELIMINAR CLIENTES
    //----------------------
    public function eliminar($id)
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el identificador del cliente.
        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        // Establece la conexión con la base de datos y busca al cliente.
        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')->where('c_id', $id)->get()->getRow();

        // Si no existe un cliente con ese identificador, devuelve un 404.
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        // 1. Evitar auto-eliminación si la usuaria en sesión es la misma
        if ((int) $cliente->c_uId === (int) $session->get('usuario_id')) {
            return $this->fail('No podés eliminar tu propia cuenta mientras estés en sesión.');
        }

        // 2. Control de turnos activos pendientes (Solicitado o Confirmado)
        $turnosActivos = $db->table('turno')
            ->where('t_cId', $id)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->countAllResults();

        // Impide eliminar al cliente si tiene turnos pendientes por asistir.
        if ($turnosActivos > 0) {
            return $this->fail('No se puede eliminar la clienta porque posee turnos pendientes por asistir (Solicitados o Confirmados). Debes cancelarlos primero o desactivar la cuenta.');
        }

        // Inicia una transacción para ejecutar conjuntamente
        // las modificaciones y eliminaciones relacionadas.
        $db->transStart();

        // En turnos pasados o cancelados, desvincular t_cId a NULL (ON DELETE SET NULL / Ley 25.326)
        $db->table('turno')->where('t_cId', $id)->update(['t_cId' => null]);

        // Eliminar cliente
        $db->table('cliente')->where('c_id', $id)->delete();
        // Eliminar usuario
        $db->table('usuario')->where('u_id', $cliente->c_uId)->delete();

        // Finaliza la transacción.
        $db->transComplete();

        // Comprueba si alguna de las operaciones de la transacción falló.
        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar el cliente.');
        }

        //Devuelve éxito
        return $this->respondDeleted(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
    }


    //----------------------
    // EDITAR CLIENTES
    //----------------------
    public function editar($id)
    {
        //Que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        // Comprueba que se haya recibido el identificador del cliente.
        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        // Obtiene los datos enviados desde el frontend.
        $data = $this->getRequestData();

        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');

        // Comprueba que los tres campos obligatorios estén completos.
        if (
            empty($name) ||
            empty($email) ||
            empty($phone)
        ) {
            return $this->fail('Datos incompletos.');
        }

        // Valida la longitud mínima del nombre.
        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }

        // Verifica el formato del correo electrónico.
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }

        // Valida la longitud mínima del teléfono.
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        $db = \Config\Database::connect();

        // Busca al cliente que se desea modificar.
        $cliente = $db->table('cliente')->where('c_id', $id)->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        // Comprueba que el correo no esté registrado en otro usuario.
        // Se excluye al usuario asociado al cliente actual para permitir
        // conservar su propio correo electrónico.
        $existing = $db->table('usuario')->where('u_correo', $email)->where('u_id !=', $cliente->c_uId)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe otra cuenta con este correo.');
        }

        $usuarioData = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => $phone
        ];

        // Comprueba si se recibió un nuevo estado para la cuenta.
        if (isset($data['u_activo']) || isset($data['activo'])) {
            $nuevoActivo = intval($data['u_activo'] ?? $data['activo']);
            // Permite modificar el estado si el cliente no corresponde
            // al administrador en sesión, o si el nuevo estado es activo (1).
            if ((int) $cliente->c_uId !== (int) $session->get('usuario_id') || $nuevoActivo === 1) {
                $usuarioData['u_activo'] = $nuevoActivo;
            }
        }

        // Actualiza los datos del usuario asociado al cliente.
        if (!$db->table('usuario')->where('u_id', $cliente->c_uId)->update($usuarioData)) {
            return $this->failServerError('Error al actualizar el cliente.');
        }

        // Comprueba si se enviaron consideraciones para el cliente.
        if (array_key_exists('consideraciones', $data) || array_key_exists('c_consideraciones', $data)) {
            $consideraciones = trim($data['consideraciones'] ?? $data['c_consideraciones'] ?? '');
            $db->table('cliente')->where('c_id', $id)->update(['c_consideraciones' => $consideraciones]);
        }

        //Devuelve éxito
        return $this->respond(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
    }


    //----------------------
    // CONTRASEÑA
    //----------------------
    public function password($id_usuario)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id_usuario) {
            return $this->fail('ID de usuario no proporcionado.');
        }

        // Obtiene los datos enviados en la solicitud.
        $data = $this->getRequestData();

        // Recupera la nueva contraseña, admitiendo distintos nombres de campo.
        $password = trim($data['password'] ?? $data['u_pass'] ?? $data['new_password'] ?? '');
        // Comprueba que se haya proporcionado una contraseña.
        if (empty($password)) {
            return $this->fail('Contraseña no proporcionada.');
        }

        // Cuenta la cantidad de mayúsculas, minúsculas, números y símbolos.
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        // Comprueba que la contraseña cumpla con los requisitos de seguridad.
        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        $db = \Config\Database::connect();

        // Busca al usuario cuya contraseña se desea actualizar.
        $usuario = $db->table('usuario')->where('u_id', $id_usuario)->get()->getRow();
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        // Genera un nuevo hash de la contraseña y lo almacena en la base de datos.
        // PASSWORD_DEFAULT permite utilizar el algoritmo recomendado por PHP.
        $db->table('usuario')->where('u_id', $id_usuario)->update([
            'u_pass' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        //Devuelve éxito
        return $this->respond(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }


    //----------------------
    // CONVERTIR
    //----------------------
    public function convertir($id_cliente)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id_cliente) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        // Obtiene los datos enviados desde el frontend.
        $data = $this->getRequestData();

        // Recupera el título o formación profesional.
        $title = trim($data['title'] ?? $data['p_titulo'] ?? $data['titulo'] ?? '');

        // Obtiene el año de inicio de actividad.
        // Si no se proporciona, utiliza el año actual.
        $year = isset($data['year']) ? intval($data['year']) : (isset($data['p_anioInicioAct']) ? intval($data['p_anioInicioAct']) : date('Y'));

        // Comprueba que se haya proporcionado el título profesional
        if (empty($title)) {
            return $this->fail('Datos incompletos.');
        }

        // Verifica que el título contenga al menos tres letras
        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        $db = \Config\Database::connect();

        // Busca el cliente que se desea convertir
        $cliente = $db->table('cliente')->where('c_id', $id_cliente)->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        // Verificar que no sea profesional ya
        $profesional = $db->table('profesional')->where('u_id', $cliente->c_uId)->get()->getRow();
        if ($profesional) {
            return $this->fail('Este usuario ya es un profesional.');
        }

        // Prepara los datos para crear el nuevo perfil profesional.
        $profesionalData = [
            'u_id' => $cliente->c_uId,
            'p_titulo' => $title,
            'p_anioInicioAct' => $year
        ];

        // Inserta el nuevo registro en la tabla profesional.
        if (!$db->table('profesional')->insert($profesionalData)) {
            return $this->failServerError('Error al convertir el cliente a profesional.');
        }

        //Devuelve éxito
        return $this->respond(['success' => true, 'message' => 'Cliente convertido a profesional exitosamente.']);
    }


    //----------------------
    // TOGGLE ESTADO
    //----------------------
    public function toggleEstado($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        $db = \Config\Database::connect();

        $cliente = $db->table('cliente')->where('c_id', intval($id))->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        // Impide que el administrador modifique el estado de su propia cuenta.
        if ($cliente->c_uId == $session->get('usuario_id')) {
            return $this->fail('No puedes cambiar el estado de tu propia cuenta mientras estás en sesión.');
        }

        // Busca el usuario asociado al cliente para consultar su estado actual.
        $usuario = $db->table('usuario')->where('u_id', $cliente->c_uId)->get()->getRow();
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        // Invierte el estado actual de la cuenta:
        // si está activa (1), pasa a inactiva (0);
        // si está inactiva (0), pasa a activa (1).
        $nuevoEstado = $usuario->u_activo ? 0 : 1;

        // Actualiza el estado del usuario en la base de datos.
        $db->table('usuario')->where('u_id', $cliente->c_uId)->update(['u_activo' => $nuevoEstado]);

        // Construye el mensaje según el nuevo estado de la cuenta.
        $accion = $nuevoEstado ? 'activado' : 'dado de baja (inactivo)';
        return $this->respond([
            'success' => true,
            'message' => "Cliente {$accion} exitosamente.",
            'nuevo_estado' => $nuevoEstado
        ]);
    }
}
