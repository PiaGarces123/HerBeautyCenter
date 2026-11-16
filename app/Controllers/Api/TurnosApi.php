<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TurnoModel;
use CodeIgniter\API\ResponseTrait;

/**
 * TurnosApi
 * Her Beauty Center — Incremento N° 2 (CU07 - Gestionar Turnos)
 *
 * Controlador encargado de administrar los turnos desde la API.
 *
 * Sus responsabilidades principales son:
 * 1. Consultar los turnos de una profesional y sus detalles.
 * 2. Obtener los clientes disponibles para asignar turnos.
 * 3. Crear y asignar turnos a clientes existentes o nuevos.
 * 4. Actualizar el estado, el pago y la duración de los turnos.
 * 5. Preparar los datos necesarios para exportar turnos a Google Calendar.
 *
 * Ubicación: app/Controllers/Api/TurnosApi.php
 */
class TurnosApi extends BaseController
{
    // Incorpora métodos para generar respuestas HTTP en formato JSON,
    // como respond(), respondCreated(), fail(), failUnauthorized(), entre otros.
    use ResponseTrait;

    // -------------------------------------------------------
    // Helper: verificar sesión activa y rol válido
    // -------------------------------------------------------

    /**
     * Comprueba que exista una sesión iniciada y que el usuario
     * tenga permisos de administrador o profesional.
     *
     * @return array|null Datos de autenticación o null si no tiene acceso.
     */
    private function verificarSesion(): ?array
    {
        // Recupera la sesión actual.
        $session = session();

        // Obtiene el identificador del usuario y su rol.
        $uid = $session->get('usuario_id');
        $rol = $session->get('rol');

        // Rechaza el acceso si no hay usuario autenticado
        // o si su rol no se encuentra entre los permitidos.
        if (!$uid || !in_array($rol, ['admin', 'profesional'], true))
            return null;

        // Devuelve los datos necesarios para identificar al usuario
        // y determinar qué operaciones puede realizar.
        return ['uid' => $uid, 'rol' => $rol];
    }

    /**
     * Obtiene el identificador del perfil profesional asociado
     * a un usuario del sistema.
     *
     * @param int $uid Identificador del usuario.
     * @return int|null Identificador profesional o null si no existe.
     */
    private function getProfesionalId(int $uid): ?int
    {
        // Busca el registro de profesional cuyo u_id coincide
        // con el identificador del usuario recibido.
        $row = \Config\Database::connect()
            ->table('profesional')->select('p_id')->where('u_id', $uid)
            ->get()->getRowArray();

        // Si encuentra el registro, devuelve p_id como entero.
        // En caso contrario, devuelve null.
        return $row ? (int) $row['p_id'] : null;
    }

    // -------------------------------------------------------
    // GET /api/turnos/listar
    // Lista turnos de la profesional con filtros opcionales
    // -------------------------------------------------------

    /**
     * Recupera los turnos asociados al perfil profesional autenticado.
     *
     * Permite filtrar los resultados por estado y por un rango de fechas.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta JSON con los turnos.
     */
    public function listar(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica que el usuario tenga una sesión válida.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional correspondiente al usuario autenticado.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Recupera los filtros enviados mediante parámetros GET.
        // Si alguno no fue enviado, se establece como una cadena vacía.
        $filtros = [
            'estado' => $this->request->getGet('estado') ?? '',
            'fecha_desde' => $this->request->getGet('fecha_desde') ?? '',
            'fecha_hasta' => $this->request->getGet('fecha_hasta') ?? '',
        ];

        // Instancia el modelo encargado de gestionar las consultas de turnos.
        $model = new TurnoModel();

        // Obtiene los turnos de la profesional, incluyendo sus datos relacionados
        // y aplicando los filtros recibidos.
        $turnos = $model->getTurnosConDetalle($pId, $filtros);

        // Devuelve el listado de turnos en formato JSON.
        return $this->respond(['turnos' => $turnos]);
    }

    // -------------------------------------------------------
    // GET /api/turnos/clientes
    // Retorna clientes activos para asignación de turnos
    // -------------------------------------------------------

    /**
     * Obtiene los clientes activos registrados en el sistema.
     *
     * Se utiliza para completar la lista de clientes disponibles
     * al momento de asignar un turno.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta JSON con los clientes.
     */
    public function clientes(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Comprueba que exista una sesión autorizada.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Consulta los clientes y obtiene sus datos personales
        // mediante una unión entre las tablas cliente y usuario.
        $clientes = $db->table('cliente')
            ->select('cliente.c_id, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')

            // Incluye únicamente usuarios activos.
            ->where('usuario.u_activo', 1)

            // Ordena los resultados alfabéticamente por nombre completo.
            ->orderBy('usuario.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        // Devuelve el listado de clientes en formato JSON.
        return $this->respond(['clientes' => $clientes]);
    }

    // -------------------------------------------------------
    // POST /api/turnos/crear
    // Permite al profesional / admin crear y asignar un turno a un cliente
    // -------------------------------------------------------

    /**
     * Crea un nuevo turno y lo vincula con un cliente, un servicio,
     * una profesional y un horario determinado.
     *
     * También permite registrar un cliente nuevo directamente
     * durante el proceso de asignación.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta con el resultado de la operación.
     */
    public function crear(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica que el usuario esté autenticado y tenga un rol permitido.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene la conexión a la base de datos y los datos enviados
        // por el formulario o mediante una solicitud JSON.
        $db = \Config\Database::connect();
        $data = $this->getRequestData();

        // Recupera el identificador del servicio.
        // Se contemplan distintos nombres de campo para admitir
        // diferentes formatos de envío desde el frontend.
        $sId = (int) ($data['s_id'] ?? $data['servicio_id'] ?? $data['servicio'] ?? 0);

        // Si se recibió un identificador profesional, lo utiliza.
        // De lo contrario, intenta obtener el perfil asociado al usuario autenticado.
        $pId = !empty($data['p_id']) ? (int) $data['p_id'] : $this->getProfesionalId($auth['uid']);

        // Si no se encontró un perfil y el usuario es administrador,
        // se intenta asignar automáticamente una profesional.
        if (!$pId && $auth['rol'] === 'admin') {

            // Primero busca una profesional asociada al servicio seleccionado.
            // Si encuentra una relación, utiliza su identificador.
            if ($sId) {
                $pRow = $db->table('profesional_servicio')->where('p_sId', $sId)->get()->getRowArray();
                if ($pRow)
                    $pId = (int) $pRow['p_id'];
            }

            // Si todavía no hay una profesional asignada,
            // obtiene el primer registro disponible de la tabla profesional.
            if (!$pId) {
                $pRow = $db->table('profesional')->get()->getRowArray();
                if ($pRow)
                    $pId = (int) $pRow['p_id'];
            }
        }

        // Si no fue posible determinar una profesional, no se puede continuar.
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Recupera y normaliza los datos del turno y del cliente.
        // Se admiten nombres alternativos para algunos campos.
        $fecha = trim($data['fecha'] ?? $data['t_fecha'] ?? '');
        $horaDesde = trim($data['hora_desde'] ?? $data['t_horaDesde'] ?? $data['desde'] ?? '');
        $horaHasta = trim($data['hora_hasta'] ?? $data['t_horaHasta'] ?? $data['hasta'] ?? '');
        $nuevoCliente = !empty($data['nuevo_cliente']);
        $cId = (int) ($data['c_id'] ?? $data['cliente_id'] ?? $data['cliente'] ?? 0);
        $clienteNombre = trim($data['cliente_nombre'] ?? $data['nombre_cliente'] ?? '');
        $clienteTel = trim($data['cliente_tel'] ?? $data['tel_cliente'] ?? '');
        $clienteEmail = trim($data['cliente_email'] ?? $data['correo_cliente'] ?? '');

        // Convierte el monto a número decimal cuando fue informado.
        // Si no se especificó, conserva null para utilizar luego el precio del servicio.
        $monto = isset($data['monto']) && $data['monto'] !== '' ? floatval($data['monto']) : null;

        // Convierte el valor de abonado a 1 o 0, según corresponda.
        $abonado = !empty($data['abonado']) ? 1 : 0;

        // Acepta únicamente los estados permitidos para la creación.
        // Si el estado recibido no es válido o no fue informado,
        // establece Confirmado como valor predeterminado.
        $estado = in_array($data['estado'] ?? '', ['Solicitado', 'Confirmado', 'Realizado'], true)
            ? $data['estado']
            : 'Confirmado';

        // 1. Validar servicio

        // Comprueba que se haya seleccionado un servicio.
        if (!$sId) {
            return $this->fail('Debés seleccionar un servicio.');
        }

        // Busca el servicio seleccionado y verifica que esté activo.
        $servicio = $db->table('servicio')->where('s_id', $sId)->where('s_activo', 1)->get()->getRowArray();

        // Si no existe o está inactivo, interrumpe la operación.
        if (!$servicio) {
            return $this->fail('El servicio seleccionado no existe o no está disponible.');
        }

        // 2. Validar fecha

        // Comprueba que se haya informado una fecha con formato AAAA-MM-DD.
        if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $this->fail('La fecha seleccionada no es válida.');
        }

        // Impide registrar turnos en fechas anteriores al día actual.
        if ($fecha < date('Y-m-d')) {
            return $this->fail('No se pueden programar turnos en fechas pasadas.');
        }

        // 3. Validar horarios

        // Verifica que se hayan informado las horas de inicio y finalización.
        if (!$horaDesde || !$horaHasta) {
            return $this->fail('Las horas de inicio y fin son obligatorias.');
        }

        // Normaliza los horarios al formato HH:MM:SS.
        // Se agregan los segundos para trabajar con un formato uniforme en la base de datos.
        $horaDesdeNorm = substr($horaDesde, 0, 5) . ':00';
        $horaHastaNorm = substr($horaHasta, 0, 5) . ':00';

        // Comprueba que la hora de finalización sea posterior a la de inicio.
        if ($horaHastaNorm <= $horaDesdeNorm) {
            return $this->fail('La hora de fin debe ser posterior a la hora de inicio.');
        }

        // 4. Resolver o crear cliente

        // Si se indicó que se registrará un cliente nuevo,
        // se validan sus datos antes de insertarlo.
        if ($nuevoCliente) {

            // Comprueba que el nombre tenga al menos tres caracteres.
            if (mb_strlen($clienteNombre) < 3) {
                return $this->fail('El nombre del cliente debe tener al menos 3 caracteres.');
            }

            // Comprueba que el teléfono tenga al menos seis caracteres.
            if (mb_strlen($clienteTel) < 6) {
                return $this->fail('El teléfono del cliente debe tener al menos 6 caracteres.');
            }

            // Si se ingresó un correo electrónico, verifica su formato.
            if (!empty($clienteEmail)) {
                if (!filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
                    return $this->fail('El correo electrónico ingresado no tiene un formato válido.');
                }

                // Comprueba si ya existe un usuario registrado con ese correo.
                $existUser = $db->table('usuario')->where('u_correo', $clienteEmail)->get()->getRowArray();

                if ($existUser) {

                    // Si el correo ya pertenece a un usuario, comprueba
                    // si ese usuario tiene asociado un perfil de cliente.
                    $existCli = $db->table('cliente')->where('c_uId', $existUser['u_id'])->get()->getRowArray();

                    if ($existCli) {

                        // Si ya existe como cliente, reutiliza su identificador.
                        $cId = (int) $existCli['c_id'];
                    } else {

                        // Si el correo pertenece a otro tipo de usuario,
                        // impide crear un registro duplicado.
                        return $this->fail('Ya existe un usuario con este correo electrónico.');
                    }
                }
            }

            // Si no se encontró un cliente existente, crea uno nuevo.
            if (!$cId) {

                // Cuando no se proporciona correo, genera uno interno
                // utilizando la fecha actual y un número aleatorio.
                if (empty($clienteEmail)) {
                    $clienteEmail = 'cliente_' . time() . '_' . rand(100, 999) . '@herbeauty.local';
                }

                // Inserta los datos personales en la tabla usuario.
                // La contraseña se genera aleatoriamente y se almacena mediante password_hash().
                $db->table('usuario')->insert([
                    'u_nbreCompleto' => $clienteNombre,
                    'u_correo' => $clienteEmail,
                    'u_tel' => $clienteTel,
                    'u_pass' => password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
                    'u_activo' => 1,
                ]);

                // Recupera el identificador del usuario recién insertado.
                $newUId = (int) $db->insertID();

                // Crea el registro correspondiente en la tabla cliente,
                // vinculándolo con el usuario mediante c_uId.
                $db->table('cliente')->insert(['c_uId' => $newUId]);

                // Recupera el identificador del nuevo perfil de cliente.
                $cId = (int) $db->insertID();
            }
        } else {

            // Si no se solicitó crear un cliente nuevo,
            // es obligatorio seleccionar uno existente.
            if (!$cId) {
                return $this->fail('Debés seleccionar un cliente de la lista o marcar la opción para registrar uno nuevo.');
            }

            // Verifica que el identificador corresponda a un cliente registrado.
            $checkCli = $db->table('cliente')->where('c_id', $cId)->get()->getRowArray();

            if (!$checkCli) {
                return $this->fail('El cliente seleccionado no fue encontrado.');
            }
        }

        // 5. Verificar solapamiento de turno para la profesional

        // Busca si la profesional ya tiene otro turno en la misma fecha
        // cuyo horario se superponga con el que se intenta registrar.
        // Solo se consideran los turnos solicitados o confirmados.
        $solapado = $db->table('turno')
            ->where('t_pId', $pId)
            ->where('t_fecha', $fecha)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])

            // Comprueba si el turno existente comienza antes de que termine el nuevo.
            ->where('t_horaDesde <', $horaHastaNorm)

            // Comprueba si el turno existente termina después de que comienza el nuevo.
            ->where('t_horaHasta >', $horaDesdeNorm)
            ->get()->getRowArray();

        // Si existe una superposición, obtiene las horas del turno encontrado
        // y devuelve un mensaje indicando el horario que ya está reservado.
        if ($solapado) {
            $desdeSol = substr($solapado['t_horaDesde'], 0, 5);
            $hastaSol = substr($solapado['t_horaHasta'], 0, 5);
            return $this->fail("Ya tenés un turno reservado en ese horario ({$desdeSol} a {$hastaSol}).");
        }

        // 6. Gestionar horario asociado (respetando constraint UNIQUE en turno.t_hId)

        // Busca si ya existe un horario con la misma profesional, servicio,
        // fecha y rango horario.
        $horarioExistente = $db->table('horario')
            ->where('h_pId', $pId)
            ->where('h_sId', $sId)
            ->where('h_fecha', $fecha)
            ->where('h_horaDesde', $horaDesdeNorm)
            ->where('h_horaHasta', $horaHastaNorm)
            ->get()->getRowArray();

        // Inicializa el identificador del horario.
        $hId = null;

        // Si encontró un horario existente, verifica si ya tiene un turno asociado.
        if ($horarioExistente) {
            $turnoAsoc = $db->table('turno')->where('t_hId', $horarioExistente['h_id'])->get()->getRowArray();

            // Si todavía no tiene un turno asociado, puede reutilizarse.
            if (!$turnoAsoc) {
                $hId = (int) $horarioExistente['h_id'];
            }
        }

        // Si no se encontró un horario reutilizable, crea uno nuevo.
        if (!$hId) {
            $db->table('horario')->insert([
                'h_pId' => $pId,
                'h_sId' => $sId,
                'h_fecha' => $fecha,
                'h_horaDesde' => $horaDesdeNorm,
                'h_horaHasta' => $horaHastaNorm,
            ]);

            // Recupera el identificador del horario recién creado.
            $hId = (int) $db->insertID();
        }

        // 7. Insertar el turno

        // Si no se indicó un monto manualmente, utiliza el precio
        // establecido para el servicio seleccionado.
        if ($monto === null) {
            $monto = floatval($servicio['s_precio']);
        }

        // Instancia el modelo de turnos para realizar la inserción.
        $turnoModel = new TurnoModel();

        // Registra el turno vinculándolo con el horario, la profesional
        // y el cliente correspondientes.
        $tId = $turnoModel->insert([
            't_hId' => $hId,
            't_pId' => $pId,
            't_cId' => $cId,
            't_fecha' => $fecha,
            't_horaDesde' => $horaDesdeNorm,
            't_horaHasta' => $horaHastaNorm,
            'estado' => $estado,
            'abonado' => $abonado,
            'monto' => $monto,
        ]);

        // Devuelve una respuesta HTTP 201 (Created) indicando
        // que el turno fue creado correctamente.
        return $this->respondCreated([
            'success' => true,
            'message' => 'Turno creado y asignado exitosamente.',
            't_id' => $tId,
        ]);
    }

    // -------------------------------------------------------
    // GET /api/turnos/detalle/:id
    // -------------------------------------------------------

    /**
     * Obtiene toda la información de un turno específico.
     *
     * Verifica que el turno pertenezca a la profesional autenticada.
     *
     * @param int $tId Identificador del turno.
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta con el detalle del turno.
     */
    public function detalle(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        // Comprueba que el usuario tenga una sesión válida.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el identificador del perfil profesional autenticado.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Instancia el modelo y recupera el turno con todos sus datos relacionados.
        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        // Comprueba que el turno exista y que pertenezca a la profesional.
        // Si alguna condición no se cumple, devuelve un error 404.
        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        // Devuelve los datos completos del turno.
        return $this->respond(['turno' => $turno]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/estado/:id
    // Actualiza el estado de un turno (con validación de transición)
    // -------------------------------------------------------

    /**
     * Modifica el estado de un turno y permite actualizar
     * información relacionada con su cancelación y pago.
     *
     * Antes de guardar los cambios, verifica que la transición
     * entre el estado actual y el nuevo esté permitida.
     *
     * @param int $tId Identificador del turno.
     * @return \CodeIgniter\HTTP\ResponseInterface Resultado de la actualización.
     */
    public function actualizarEstado(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica la sesión del usuario.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Recupera el perfil profesional asociado al usuario autenticado.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Busca el turno por su identificador.
        $model = new TurnoModel();
        $turno = $model->find($tId);

        // Comprueba que el turno exista y pertenezca a la profesional.
        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        // Recupera los datos enviados para modificar el turno.
        $data = $this->getRequestData();

        // Obtiene el nuevo estado solicitado.
        $estadoNuevo = trim($data['estado'] ?? '');

        // Convierte el valor de abonado a entero (1 o 0).
        // Si no se envió este dato, conserva null para no modificarlo.
        $abonado = isset($data['abonado']) ? (int) (bool) $data['abonado'] : null;

        // Convierte el monto a número decimal, si fue informado.
        $monto = isset($data['monto']) ? floatval($data['monto']) : null;

        // Recupera el motivo de cancelación, si corresponde.
        $motivo = trim($data['motivo_cancelacion'] ?? '');

        // El nuevo estado es obligatorio para realizar la actualización.
        if (!$estadoNuevo) {
            return $this->fail('El nuevo estado es obligatorio.');
        }

        // -- Validar transición de estado (RN12) --

        // Consulta al modelo si el cambio de estado solicitado
        // respeta las reglas de negocio definidas para los turnos.
        if (!$model->esTransicionValida($turno['estado'], $estadoNuevo)) {
            return $this->fail("Transición inválida: no se puede pasar de \"{$turno['estado']}\" a \"$estadoNuevo\".");
        }

        // -- Cancelación requiere motivo --

        // Si el nuevo estado es Cancelado, exige que se informe un motivo.
        if ($estadoNuevo === 'Cancelado' && empty($motivo)) {
            return $this->fail('Debes ingresar un motivo para la cancelación.');
        }

        // Inicializa los datos que se actualizarán en la base de datos.
        $updateData = ['estado' => $estadoNuevo];

        // Guarda el motivo únicamente cuando el turno se cancela.
        if ($estadoNuevo === 'Cancelado')
            $updateData['motivo_cancelacion'] = $motivo;

        // Actualiza el estado de pago solamente si se recibió ese dato.
        if ($abonado !== null)
            $updateData['abonado'] = $abonado;

        // Actualiza el monto solamente si fue informado.
        if ($monto !== null)
            $updateData['monto'] = $monto;

        // Guarda los cambios del turno mediante el modelo.
        $model->update($tId, $updateData);

        // -- Si se cancela, liberamos la disponibilidad (RN12) --
        // (La disponibilidad queda intacta; el turno queda en historial — baja lógica)

        // Devuelve una respuesta indicando que la actualización fue realizada.
        return $this->respond([
            'success' => true,
            'message' => "Turno actualizado a \"$estadoNuevo\" correctamente.",
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/pago/:id
    // Actualiza el estado de abono y monto
    // -------------------------------------------------------

    /**
     * Actualiza la información de pago de un turno.
     *
     * Permite modificar si el turno fue abonado y cuál es su monto.
     * Los valores que no se envían conservan los datos actuales.
     *
     * @param int $tId Identificador del turno.
     * @return \CodeIgniter\HTTP\ResponseInterface Resultado de la actualización.
     */
    public function actualizarPago(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica que el usuario tenga una sesión válida.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional del usuario autenticado.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Busca el turno que se desea modificar.
        $model = new TurnoModel();
        $turno = $model->find($tId);

        // Comprueba que el turno exista y pertenezca a la profesional.
        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        // Recupera los datos enviados desde el formulario o la solicitud JSON.
        $data = $this->getRequestData();

        // Si se recibió el dato de abonado, lo convierte a 1 o 0.
        // De lo contrario, conserva el valor almacenado actualmente.
        $abonado = isset($data['abonado']) ? (int) (bool) $data['abonado'] : (int) $turno['abonado'];

        // Si se recibió un nuevo monto, lo convierte a decimal.
        // Si no se envió, utiliza el monto que ya tiene el turno.
        $monto = isset($data['monto']) ? floatval($data['monto']) : floatval($turno['monto']);

        // Impide registrar importes negativos.
        if ($monto < 0) {
            return $this->fail('El monto no puede ser negativo.');
        }

        // Actualiza ambos datos de pago en el registro del turno.
        $model->update($tId, ['abonado' => $abonado, 'monto' => $monto]);

        // Informa que la información de pago fue actualizada.
        return $this->respond(['success' => true, 'message' => 'Información de pago actualizada.']);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/duracion/:id
    // Actualiza la duración real del servicio (recalculando la hora de fin)
    // -------------------------------------------------------

    /**
     * Modifica la duración de un turno y recalcula su hora de finalización.
     *
     * Actualiza tanto el registro del turno como el horario vinculado,
     * para mantener sincronizada la información de la agenda.
     *
     * @param int $tId Identificador del turno.
     * @return \CodeIgniter\HTTP\ResponseInterface Resultado de la operación.
     */
    public function actualizarDuracion(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        // Comprueba que exista una sesión autorizada.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional asociado al usuario.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Busca el turno que se desea modificar.
        $model = new TurnoModel();
        $turno = $model->find($tId);

        // Comprueba que el turno exista.
        // Si el usuario no es administrador, también verifica que sea
        // la profesional responsable del turno.
        if (!$turno || ($auth['rol'] !== 'admin' && (int) $turno['t_pId'] !== $pId)) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        // Recupera los datos enviados para establecer la nueva duración.
        $data = $this->getRequestData();

        // Acepta dos nombres posibles para el campo de duración.
        // Convierte el valor recibido a entero.
        $duracion = isset($data['duracion_minutos']) ? intval($data['duracion_minutos']) : (isset($data['duracion']) ? intval($data['duracion']) : null);

        // Valida que la duración esté comprendida entre 5 y 480 minutos.
        if (!$duracion || $duracion < 5 || $duracion > 480) {
            return $this->fail('La duración debe ser un número entero de entre 5 y 480 minutos.');
        }

        // Recupera la hora de inicio del turno.
        $horaInicio = $turno['t_horaDesde'];

        // Convierte la hora de inicio en una marca de tiempo Unix.
        // Esto permite realizar operaciones aritméticas con fechas y horas.
        $horaDesdeTimestamp = strtotime($horaInicio);

        // Calcula la nueva hora de finalización sumando la duración en segundos.
        // Se multiplica por 60 porque la duración está expresada en minutos.
        $nuevaHoraHastaTimestamp = $horaDesdeTimestamp + ($duracion * 60);

        // Convierte el resultado nuevamente al formato HH:MM:SS.
        $nuevaHoraHasta = date('H:i:s', $nuevaHoraHastaTimestamp);

        // Si la nueva hora no es posterior al inicio o el cálculo
        // supera el día de inicio, limita la finalización a las 23:59.
        if ($nuevaHoraHasta <= $horaInicio || date('Y-m-d', $horaDesdeTimestamp) !== date('Y-m-d', $nuevaHoraHastaTimestamp)) {
            $nuevaHoraHasta = '23:59:00';
        }

        // Establece la conexión con la base de datos e inicia una transacción.
        // De esta manera, las actualizaciones de turno y horario
        // se confirman conjuntamente.
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Actualizar t_horaHasta en turno

        // Actualiza la hora de finalización del registro del turno.
        $db->table('turno')->where('t_id', $tId)->update([
            't_horaHasta' => $nuevaHoraHasta
        ]);

        // 2. Actualizar h_horaHasta en horario vinculado para mantener sincronizada la agenda

        // Si el turno tiene un horario asociado, también actualiza
        // la hora de finalización de ese registro.
        if (!empty($turno['t_hId'])) {
            $db->table('horario')->where('h_id', $turno['t_hId'])->update([
                'h_horaHasta' => $nuevaHoraHasta
            ]);
        }

        // Finaliza la transacción. CodeIgniter confirma los cambios
        // si las operaciones fueron correctas o realiza un rollback si corresponde.
        $db->transComplete();

        // Comprueba si la transacción finalizó correctamente.
        if ($db->transStatus() === false) {
            return $this->failServerError('Error al actualizar la duración del servicio.');
        }

        // Devuelve la nueva duración y las horas actualizadas.
        // substr() se utiliza para mostrar únicamente horas y minutos.
        return $this->respond([
            'success' => true,
            'message' => "Duración del servicio actualizada a {$duracion} minutos.",
            'duracion_minutos' => $duracion,
            't_horaDesde' => substr($horaInicio, 0, 5),
            't_horaHasta' => substr($nuevaHoraHasta, 0, 5)
        ]);
    }

    // -------------------------------------------------------
    // POST /api/turnos/google-calendar/:id
    // Retorna datos estructurados del turno para exportar a GCal
    // La integración real OAuth2 requiere credenciales de cada profesional
    // -------------------------------------------------------

    /**
     * Prepara los datos de un turno confirmado para su incorporación
     * a Google Calendar.
     *
     * Genera la información del evento y un enlace que permite abrir
     * Google Calendar con los datos del turno precargados.
     *
     * @param int $tId Identificador del turno.
     * @return \CodeIgniter\HTTP\ResponseInterface Datos del evento y enlace de Google Calendar.
     */
    public function exportarGoogleCalendar(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica que el usuario tenga una sesión activa y un rol permitido.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional asociado al usuario autenticado.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Recupera el turno junto con los datos del servicio y del cliente.
        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        // Verifica que el turno exista y pertenezca a la profesional.
        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        // Solo permite exportar turnos que ya estén confirmados.
        if ($turno['estado'] !== 'Confirmado') {
            return $this->fail('Solo se pueden exportar a Google Calendar los turnos en estado Confirmado.');
        }

        // Datos mínimos necesarios para el evento de Google Calendar

        // Construye un arreglo con la estructura básica de un evento.
        // Incluye el título, la descripción, la fecha, las horas
        // y la zona horaria correspondiente.
        $eventoGCal = [
            'summary' => 'Turno: ' . $turno['nombre_servicio'],
            'description' => 'Cliente: ' . ($turno['nombre_cliente'] ?? 'Sin asignar'),
            'start' => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaDesde'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
            'end' => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaHasta'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
        ];

        // Construir enlace "Agregar a Google Calendar" (RFC 5545 compatible)

        // Convierte la fecha y hora de inicio y finalización
        // en marcas de tiempo para poder formatearlas.
        $startTs = strtotime($turno['fecha'] . ' ' . $turno['h_horaDesde']);
        $endTs = strtotime($turno['fecha'] . ' ' . $turno['h_horaHasta']);

        // Transforma las fechas al formato AAAAMMDDTHHMMSS,
        // utilizado por el parámetro dates del enlace de Google Calendar.
        $startDt = date('Ymd\THis', $startTs);
        $endDt = date('Ymd\THis', $endTs);

        // Construye la descripción completa del evento.
        // Incluye información del servicio, cliente, contacto, monto y estado.
        // Los datos opcionales se agregan únicamente cuando están disponibles.
        $detalles = "Her Beauty Center — Turno Confirmado\n"
            . "Servicio: " . ($turno['nombre_servicio'] ?? 'Servicio') . "\n"
            . "Cliente: " . ($turno['nombre_cliente'] ?? 'Sin asignar') . "\n"
            . (!empty($turno['tel_cliente']) ? "Teléfono: " . $turno['tel_cliente'] . "\n" : "")
            . (!empty($turno['correo_cliente']) ? "Email: " . $turno['correo_cliente'] . "\n" : "")
            . "Monto: $" . number_format($turno['monto'] ?? 0, 0, ',', '.') . "\n"
            . "Estado: Confirmado";

        // Construye el enlace de Google Calendar utilizando la acción TEMPLATE.
        // Los parámetros permiten precargar el título, las fechas,
        // la descripción, la ubicación y la zona horaria.
        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode('Turno: ' . ($turno['nombre_servicio'] ?? 'Estética') . ' — ' . ($turno['nombre_cliente'] ?? 'Cliente'))
            . '&dates=' . $startDt . '/' . $endDt
            . '&details=' . urlencode($detalles)
            . '&location=' . urlencode('Her Beauty Center')
            . '&ctz=' . urlencode('America/Argentina/Buenos_Aires');

        // Devuelve los datos estructurados del evento y el enlace generado.
        // La incorporación efectiva al calendario se realiza desde Google Calendar.
        return $this->respond([
            'success' => true,
            'evento' => $eventoGCal,
            'google_link' => $link,
            'message' => 'Datos del turno preparados para Google Calendar.',
        ]);
    }
}
