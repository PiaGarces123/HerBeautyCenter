<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\HorarioModel;
use App\Models\TurnoModel;
use App\Models\UsuarioModel;
use CodeIgniter\API\ResponseTrait;

/**
 * ReservasApi
 * Her Beauty Center — Incremento N° 3 (CU04 y CU05)
 *
 * API RESTful encargada de gestionar las operaciones relacionadas
 * con las reservas de turnos realizadas por los clientes.
 *
 * Sus responsabilidades principales son:
 * 1. Consultar la disponibilidad horaria de los servicios.
 * 2. Confirmar reservas y registrar los turnos solicitados.
 * 3. Evitar reservas duplicadas mediante controles de concurrencia.
 * 4. Permitir que los clientes cancelen sus propios turnos.
 * 5. Generar enlaces para exportar turnos a Google Calendar.
 */
class ReservasApi extends BaseController
{
    // Permite utilizar métodos para generar respuestas HTTP y JSON
    // como respond(), respondCreated(), fail(), failNotFound(), entre otros.
    use ResponseTrait;

    // Modelos utilizados para acceder a los datos de horarios, turnos y usuarios.
    private HorarioModel $horarioModel;
    private TurnoModel $turnoModel;
    private UsuarioModel $usuarioModel;

    /**
     * Constructor de la clase.
     *
     * Instancia los modelos que serán utilizados por los diferentes
     * métodos del controlador.
     */
    public function __construct()
    {
        $this->horarioModel = new HorarioModel();
        $this->turnoModel = new TurnoModel();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Obtiene o crea el registro de cliente (c_id) para el usuario autenticado.
     *
     * Primero busca si el usuario ya tiene un registro asociado en la tabla
     * cliente. Si existe, devuelve su identificador. De lo contrario,
     * crea el registro y devuelve el ID generado por la base de datos.
     *
     * @param int $usuarioId Identificador del usuario autenticado.
     * @return int Identificador del cliente.
     */
    private function obtenerClienteId(int $usuarioId): int
    {
        // Establecer la conexión con la base de datos.
        $db = \Config\Database::connect();

        // Buscar el registro de cliente asociado al usuario recibido.
        $cliente = $db->table('cliente')
            ->where('c_uId', $usuarioId)
            ->get()
            ->getRowArray();

        // Si el cliente ya existe, devolver su identificador.
        if ($cliente) {
            return (int) $cliente['c_id'];
        }

        // Si no existe, crear el registro y recuperar el ID autogenerado.
        $db->table('cliente')->insert(['c_uId' => $usuarioId]);
        return (int) $db->insertID();
    }

    // =========================================================================
    // GET /api/reservas/disponibilidad
    // Devuelve las franjas horarias libres de un servicio (sin exponer datos de otros clientes).
    // =========================================================================

    /**
     * Consulta los horarios disponibles de un servicio.
     *
     * Recibe el identificador del servicio y distintos filtros opcionales,
     * consulta la disponibilidad mediante HorarioModel y organiza los
     * resultados tanto en una lista general como agrupados por fecha.
     *
     * Requiere que el usuario tenga una sesión iniciada.
     */
    public function disponibilidad()
    {
        // Verificar que exista una sesión autenticada.
        if (!session()->get('usuario_id')) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        // Recuperar el ID del servicio desde los parámetros GET.
        // Si no se proporciona, se establece en 0.
        $sId = (int) ($this->request->getGet('s_id') ?? 0);

        // Validar que se haya recibido un identificador válido.
        if ($sId <= 0) {
            return $this->failValidationErrors('El identificador del servicio (s_id) es obligatorio.');
        }

        // Recuperar los filtros opcionales enviados mediante GET.
        // Se convierten los identificadores y días a enteros, y los textos
        // se limpian de espacios al principio y al final.
        $filtros = [
            'p_id' => (int) ($this->request->getGet('p_id') ?? 0),
            'fecha' => trim((string) ($this->request->getGet('fecha') ?? '')),
            'start' => trim((string) ($this->request->getGet('start') ?? '')),
            'end' => trim((string) ($this->request->getGet('end') ?? '')),
            'dia_semana' => (int) ($this->request->getGet('dia_semana') ?? 0),
            'franja' => trim((string) ($this->request->getGet('franja') ?? '')),
        ];

        // Normalizar fechas ISO si vienen desde FullCalendar (ej: 2026-11-01T00:00:00-03:00).
        // Se conservan únicamente los primeros 10 caracteres, correspondientes
        // a la fecha en formato YYYY-MM-DD, descartando la hora y la zona horaria.
        if (!empty($filtros['start'])) {
            $filtros['start'] = substr($filtros['start'], 0, 10);
        }
        if (!empty($filtros['end'])) {
            $filtros['end'] = substr($filtros['end'], 0, 10);
        }

        // Consultar en HorarioModel los bloques que cumplen con los filtros
        // y que se encuentran disponibles para el servicio seleccionado.
        $rows = $this->horarioModel->getDisponibilidadServicio($sId, $filtros);

        // Inicializar los arreglos que contendrán los resultados.
        // $horarios: lista completa de horarios disponibles.
        // $porFecha: mismos horarios agrupados según su fecha.
        $horarios = [];
        $porFecha = [];

        // Recorrer cada horario devuelto por el modelo.
        foreach ($rows as $r) {

            // Obtener la fecha y normalizar las horas al formato HH:MM.
            $fecha = $r['h_fecha'];
            $horaDesde = substr($r['h_horaDesde'], 0, 5);
            $horaHasta = substr($r['h_horaHasta'], 0, 5);

            // Construir el arreglo con los datos que necesita el cliente
            // para visualizar y seleccionar un horario disponible.
            $item = [
                'h_id' => (int) $r['h_id'],
                'h_fecha' => $fecha,
                'h_horaDesde' => $horaDesde,
                'h_horaHasta' => $horaHasta,
                'p_id' => (int) $r['p_id'],
                'nombre_profesional' => $r['nombre_profesional'],
                'titulo_profesional' => $r['titulo_profesional'] ?? 'Especialista',

                // Si la profesional tiene un avatar, se genera su URL completa.
                // Si no tiene imagen, se utiliza el ícono de perfil predeterminado.
                'avatar_profesional' => !empty($r['avatar_profesional'])
                    ? base_url($r['avatar_profesional'])
                    : base_url('public/assets/media/íconoPerfil.png'),
            ];

            // Incorporar el horario a la lista general.
            $horarios[] = $item;

            // Si todavía no existe un grupo para esta fecha, crearlo.
            if (!isset($porFecha[$fecha])) {
                $porFecha[$fecha] = [];
            }

            // Agregar el horario al grupo correspondiente a su fecha.
            $porFecha[$fecha][] = $item;
        }

        // Devolver los horarios en formato JSON.
        // Se incluyen la lista completa, los horarios agrupados por fecha
        // y la cantidad total de resultados encontrados.
        return $this->respond([
            'status' => 200,
            'horarios' => $horarios,
            'por_fecha' => $porFecha,
            'total' => count($horarios),
        ]);
    }

    // =========================================================================
    // POST /api/reservas/confirmar
    // Confirma la reserva del turno en estado 'Solicitado' con control de concurrencia.
    // =========================================================================

    /**
     * Confirma la reserva de un horario seleccionado por el cliente.
     *
     * Valida los datos personales y el horario elegido, comprueba que
     * el bloque siga disponible y registra el turno dentro de una
     * transacción para mantener la integridad de los datos.
     *
     * El turno se registra inicialmente con estado "Solicitado".
     */
    public function confirmar()
    {
        // Recuperar el ID del usuario autenticado desde la sesión.
        $usuarioId = (int) session()->get('usuario_id');

        // Impedir que usuarios sin sesión puedan confirmar una reserva.
        if (!$usuarioId) {
            return $this->failUnauthorized('Debes iniciar sesión para confirmar una reserva.');
        }

        // Recuperar los datos enviados por el cliente.
        // getRequestData() permite recibir información mediante POST o JSON.
        $data = $this->getRequestData();

        // Obtener los datos del horario y de contacto.
        // Se contemplan nombres alternativos para admitir distintos formatos
        // de envío desde el formulario o desde las solicitudes de la API.
        $hId = (int) ($data['h_id'] ?? 0);
        $nombreCompleto = trim((string) ($data['u_nbreCompleto'] ?? $data['nombre_completo'] ?? ''));
        $telefono = trim((string) ($data['u_tel'] ?? $data['telefono'] ?? ''));
        $correo = trim((string) ($data['u_correo'] ?? $data['correo'] ?? ''));
        $consideraciones = trim((string) ($data['consideraciones'] ?? ''));

        // Validar que se haya seleccionado un horario.
        if ($hId <= 0) {
            return $this->failValidationErrors('Debes seleccionar un horario válido.');
        }

        // Validar que el nombre completo tenga al menos 3 caracteres.
        if (mb_strlen($nombreCompleto) < 3) {
            return $this->failValidationErrors('El nombre completo es obligatorio y debe tener al menos 3 caracteres.');
        }

        // Validar que el teléfono tenga al menos 8 caracteres.
        if (mb_strlen($telefono) < 8) {
            return $this->failValidationErrors('El teléfono de contacto es obligatorio y debe tener al menos 8 caracteres.');
        }

        // Verificar que el correo tenga un formato válido.
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors('Debes ingresar un correo electrónico válido.');
        }

        // Limitar las consideraciones a un máximo de 500 caracteres.
        // Si se supera ese límite, se conserva únicamente el comienzo del texto.
        if (mb_strlen($consideraciones) > 500) {
            $consideraciones = mb_substr($consideraciones, 0, 500);
        }

        // 1. Verificar que el horario exista y pertenezca a un servicio y profesional activos.
        // Se obtiene la información del horario junto con los datos relacionados
        // mediante el método getHorarioParaReserva() del HorarioModel.
        $horario = $this->horarioModel->getHorarioParaReserva($hId);

        // Si no existe el horario, el servicio está inactivo o la profesional
        // está inactiva, se impide continuar con la reserva.
        if (!$horario || empty($horario['s_activo']) || empty($horario['prof_activo'])) {
            return $this->failNotFound('El bloque horario seleccionado no existe o ya no se encuentra habilitado.');
        }

        // 2. No permitir reservar fechas pasadas.
        // Se compara la fecha del horario con la fecha actual del servidor.
        if ($horario['h_fecha'] < date('Y-m-d')) {
            return $this->failValidationErrors('No es posible reservar un turno en una fecha pasada.');
        }

        // 3. Control de concurrencia: verificar que no tenga un turno activo.
        // Se consulta si el horario ya tiene asociado un turno en un estado
        // que impide que otra persona pueda reservarlo.
        if ($this->horarioModel->tieneTurnoActivo($hId)) {
            return $this->fail('Este horario acaba de ser reservado por otra persona. Por favor, elegí otro horario disponible.', 409);
        }

        // 4. Verificar que el correo no pertenezca a otro usuario distinto.
        // Se permite conservar el correo de la cuenta autenticada,
        // pero no utilizar el correo registrado por otra cuenta.
        $db = \Config\Database::connect();
        $existeCorreo = $db->table('usuario')
            ->where('u_correo', $correo)
            ->where('u_id !=', $usuarioId)
            ->countAllResults();

        // Si se encuentra otra cuenta con ese correo, se rechaza la operación.
        if ($existeCorreo > 0) {
            return $this->failValidationErrors('El correo electrónico ingresado ya está registrado en otra cuenta.');
        }

        // 5. Transacción atómica para actualizar datos de contacto y registrar el turno.
        // La transacción agrupa las operaciones para que puedan confirmarse
        // conjuntamente o revertirse ante un error.
        $db->transBegin();

        try {
            // Actualizar los datos personales del usuario autenticado.
            $db->table('usuario')
                ->where('u_id', $usuarioId)
                ->update([
                    'u_nbreCompleto' => $nombreCompleto,
                    'u_tel' => $telefono,
                    'u_correo' => $correo,
                ]);

            // Actualizar también los datos almacenados en la sesión,
            // para que el nombre y el correo reflejen los valores nuevos.
            session()->set([
                'usuario_nombre' => $nombreCompleto,
                'usuario_correo' => $correo,
            ]);

            // Obtener el ID del cliente asociado al usuario.
            // Si todavía no existe el registro, el método lo crea.
            $cId = $this->obtenerClienteId($usuarioId);

            // Guardar las consideraciones indicadas por el cliente.
            $db->table('cliente')->where('c_id', $cId)->update(['c_consideraciones' => $consideraciones]);

            // Re-verificar dentro de la transacción si existe fila en `turno` para `t_hId`.
            // Esta consulta permite comprobar nuevamente si otro proceso registró
            // un turno para el mismo horario desde la primera validación.
            $turnoExistente = $db->table('turno')
                ->where('t_hId', $hId)
                ->get()
                ->getRowArray();

            // Si ya existe un turno en estado Solicitado, Confirmado o Realizado,
            // se revierte la transacción y se informa que el horario no está disponible.
            if ($turnoExistente && in_array($turnoExistente['estado'], ['Solicitado', 'Confirmado', 'Realizado'], true)) {
                $db->transRollback();
                return $this->fail('Este horario ya no se encuentra disponible.', 409);
            }

            // Preparar una nota con las consideraciones del cliente.
            // Si no se ingresó ningún texto, se guarda null.
            $notaCliente = $consideraciones !== ''
                ? 'Consideraciones: ' . $consideraciones
                : null;

            // Construir el conjunto de datos que se guardará en la tabla turno.
            $datosTurno = [
                't_hId' => $hId,
                't_pId' => (int) $horario['h_pId'],
                't_cId' => $cId,
                't_fecha' => $horario['h_fecha'],
                't_horaDesde' => $horario['h_horaDesde'],
                't_horaHasta' => $horario['h_horaHasta'],
                'estado' => 'Solicitado',
                'abonado' => 0,
                'monto' => (float) ($horario['s_precio'] ?? 0),
                'motivo_cancelacion' => $notaCliente,
            ];

            // Si ya existe un registro asociado al horario, se reutiliza.
            // Esto permite actualizar registros anteriores que se encontraban
            // en estado Disponible o Cancelado, respetando UNIQUE(t_hId).
            if ($turnoExistente) {

                // Recuperar el ID del turno existente y actualizar sus datos.
                $tId = (int) $turnoExistente['t_id'];
                $this->turnoModel->update($tId, $datosTurno);

            } else {

                // Si no existe un registro previo, insertar un nuevo turno
                // y recuperar el identificador generado.
                $tId = (int) $this->turnoModel->insert($datosTurno);
            }

            // Comprobar si alguna operación de la transacción falló
            // o si no se obtuvo un identificador válido para el turno.
            if ($db->transStatus() === false || !$tId) {

                // Revertir todos los cambios realizados durante la transacción.
                $db->transRollback();

                return $this->failServerError('No se pudo registrar la reserva. Intentá nuevamente.');
            }

            // Confirmar definitivamente las operaciones de la transacción.
            $db->transCommit();

            // Devolver una respuesta HTTP 201 (Created) indicando que
            // la solicitud de turno fue registrada correctamente.
            return $this->respondCreated([
                'status' => 201,
                'message' => '¡Tu turno fue solicitado con éxito! La profesional se pondrá en contacto con vos a la brevedad.',
                't_id' => $tId,
                'redirect_url' => base_url('mis-turnos'),
            ]);

        } catch (\Throwable $e) {

            // Ante cualquier excepción o error capturado, revertir
            // los cambios realizados durante la transacción.
            $db->transRollback();

            return $this->failServerError('Error al procesar la reserva: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PUT /api/cliente/turnos/cancelar/(:num)
    // Permite al cliente cancelar su propio turno si está Solicitado o Confirmado.
    // =========================================================================

    /**
     * Cancela un turno perteneciente al cliente autenticado.
     *
     * Comprueba que el turno exista, que pertenezca al cliente que realiza
     * la solicitud y que su estado permita efectuar la cancelación.
     * Luego actualiza el estado y almacena el motivo correspondiente.
     *
     * @param mixed $tId Identificador del turno que se desea cancelar.
     */
    public function cancelarTurno($tId = null)
    {
        // Recuperar el identificador del usuario desde la sesión.
        $usuarioId = (int) session()->get('usuario_id');

        // Verificar que el cliente tenga una sesión iniciada.
        if (!$usuarioId) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        // Convertir el ID recibido a entero y validar que sea positivo.
        $tId = (int) $tId;
        if ($tId <= 0) {
            return $this->failValidationErrors('Identificador de turno inválido.');
        }

        // Obtener el ID del cliente asociado al usuario autenticado.
        $cId = $this->obtenerClienteId($usuarioId);

        // Buscar el turno por su clave primaria.
        $turno = $this->turnoModel->find($tId);

        // Si no existe un registro con ese ID, devolver error 404.
        if (!$turno) {
            return $this->failNotFound('El turno solicitado no existe.');
        }

        // Control estricto anti-IDOR: el turno debe pertenecer al cliente autenticado.
        // Se compara el cliente asociado al turno con el ID del cliente de la sesión.
        // Esto evita que un usuario modifique un turno ajeno cambiando su identificador.
        if ((int) ($turno['t_cId'] ?? 0) !== $cId) {
            return $this->failForbidden('No tenés permiso para modificar este turno.');
        }

        // Consultar en TurnoModel si la transición desde el estado actual
        // hacia "Cancelado" está permitida según las reglas del sistema.
        if (!$this->turnoModel->esTransicionValida($turno['estado'], 'Cancelado')) {
            return $this->failValidationErrors(
                "No es posible cancelar un turno que se encuentra en estado '{$turno['estado']}'."
            );
        }

        // Recuperar los datos enviados en la solicitud para obtener el motivo.
        $data = $this->getRequestData();
        $motivo = trim((string) ($data['motivo_cancelacion'] ?? ''));

        // Si el cliente no proporciona un motivo, utilizar un texto predeterminado.
        if ($motivo === '') {
            $motivo = 'Cancelado por la clienta desde Mi Panel';
        }

        // Actualizar el estado del turno y guardar el motivo de cancelación.
        // mb_substr limita el texto a un máximo de 255 caracteres.
        $this->turnoModel->update($tId, [
            'estado' => 'Cancelado',
            'motivo_cancelacion' => mb_substr($motivo, 0, 255),
        ]);

        // Devolver una respuesta indicando que la cancelación fue realizada.
        return $this->respond([
            'status' => 200,
            'message' => 'Tu turno ha sido cancelado correctamente y el horario quedó liberado.',
        ]);
    }

    // =========================================================================
    // POST /api/cliente/turnos/google-calendar/(:num)
    // Genera URL estructurada RFC 5545 para agendar el turno en Google Calendar.
    // =========================================================================

    /**
     * Genera un enlace para exportar un turno a Google Calendar.
     *
     * Recupera el detalle del turno, verifica que pertenezca al cliente
     * autenticado y construye una URL con los datos del evento para que
     * pueda incorporarse al calendario de Google.
     *
     * @param mixed $tId Identificador del turno que se desea exportar.
     */
    public function exportarGoogleCalendar($tId = null)
    {
        // Recuperar el ID del usuario autenticado.
        $usuarioId = (int) session()->get('usuario_id');

        // Verificar que exista una sesión iniciada.
        if (!$usuarioId) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        // Convertir el identificador del turno a entero.
        $tId = (int) $tId;

        // Obtener el ID del cliente correspondiente al usuario autenticado.
        $cId = $this->obtenerClienteId($usuarioId);

        // Recuperar el detalle completo del turno mediante TurnoModel.
        $turno = $this->turnoModel->getTurnoDetalle($tId);

        // Si no se encuentra el turno, devolver un error 404.
        if (!$turno) {
            return $this->failNotFound('Turno no encontrado.');
        }

        // Verificar que el turno pertenezca al cliente autenticado.
        // Esta comprobación impide acceder a información de reservas ajenas.
        if ((int) ($turno['t_cId'] ?? 0) !== $cId) {
            return $this->failForbidden('No tenés permiso para acceder a este turno.');
        }

        // Establecer la zona horaria de Argentina para interpretar
        // correctamente las fechas y horas del turno.
        $tz = new \DateTimeZone('America/Argentina/Buenos_Aires');

        // Crear los objetos DateTime correspondientes al inicio y final del turno.
        $dtStart = new \DateTime($turno['fecha'] . ' ' . $turno['h_horaDesde'], $tz);
        $dtEnd = new \DateTime($turno['fecha'] . ' ' . $turno['h_horaHasta'], $tz);

        // Formatear las fechas para Google Calendar.
        // Ymd representa año, mes y día; \T separa la fecha de la hora;
        // His representa horas, minutos y segundos.
        // La barra separa la fecha de inicio de la fecha de finalización.
        $dates = $dtStart->format('Ymd\THis') . '/' . $dtEnd->format('Ymd\THis');

        // Construir el título que aparecerá en el evento del calendario.
        $title = 'Turno: ' . ($turno['nombre_servicio'] ?? 'Servicio') . ' — Her Beauty Center';

        // Preparar la descripción del evento con información del turno:
        // servicio contratado, profesional asignada, estado y ubicación del centro.
        $details = "Servicio: " . ($turno['nombre_servicio'] ?? '—') . "\n"
            . "Profesional: " . ($turno['nombre_profesional'] ?? '—') . "\n"
            . "Estado: " . ($turno['estado'] ?? 'Solicitado') . "\n"
            . "Centro: Her Beauty Center (San Luis, Argentina)";

        // Construir la URL de Google Calendar con los parámetros necesarios.
        // http_build_query() codifica los valores para incorporarlos correctamente
        // a la cadena de consulta de la URL.
        $gcalUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $dates,
            'details' => $details,
            'location' => 'Her Beauty Center, San Luis, Argentina',
            'ctz' => 'America/Argentina/Buenos_Aires',
        ]);

        // Devolver el enlace generado en formato JSON.
        // El cliente podrá utilizarlo para abrir Google Calendar y agregar el evento.
        return $this->respond([
            'status' => 200,
            'message' => 'Enlace de Google Calendar generado.',
            'google_sync_url' => $gcalUrl,
        ]);
    }
}
