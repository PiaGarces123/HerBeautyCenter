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
 * API RESTful para la consulta de disponibilidad horaria de clientes,
 * confirmación transaccional de reservas, cancelación de turnos propios
 * y exportación a Google Calendar (estándar RFC 5545).
 */
class ReservasApi extends BaseController
{
    use ResponseTrait;

    private HorarioModel $horarioModel;
    private TurnoModel $turnoModel;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->horarioModel = new HorarioModel();
        $this->turnoModel   = new TurnoModel();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Obtiene o crea el registro de cliente (c_id) para el usuario autenticado.
     */
    private function obtenerClienteId(int $usuarioId): int
    {
        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')
            ->where('c_uId', $usuarioId)
            ->get()
            ->getRowArray();

        if ($cliente) {
            return (int) $cliente['c_id'];
        }

        $db->table('cliente')->insert(['c_uId' => $usuarioId]);
        return (int) $db->insertID();
    }

    // =========================================================================
    // GET /api/reservas/disponibilidad
    // Devuelve las franjas horarias libres de un servicio (sin exponer datos de otros clientes)
    // =========================================================================
    public function disponibilidad()
    {
        if (!session()->get('usuario_id')) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        $sId = (int) ($this->request->getGet('s_id') ?? 0);
        if ($sId <= 0) {
            return $this->failValidationErrors('El identificador del servicio (s_id) es obligatorio.');
        }

        $filtros = [
            'p_id'       => (int) ($this->request->getGet('p_id') ?? 0),
            'fecha'      => trim((string) ($this->request->getGet('fecha') ?? '')),
            'start'      => trim((string) ($this->request->getGet('start') ?? '')),
            'end'        => trim((string) ($this->request->getGet('end') ?? '')),
            'dia_semana' => (int) ($this->request->getGet('dia_semana') ?? 0),
            'franja'     => trim((string) ($this->request->getGet('franja') ?? '')),
        ];

        // Normalizar fechas ISO si vienen desde FullCalendar (ej: 2026-11-01T00:00:00-03:00)
        if (!empty($filtros['start'])) {
            $filtros['start'] = substr($filtros['start'], 0, 10);
        }
        if (!empty($filtros['end'])) {
            $filtros['end'] = substr($filtros['end'], 0, 10);
        }

        $rows = $this->horarioModel->getDisponibilidadServicio($sId, $filtros);

        $horarios = [];
        $porFecha = [];

        foreach ($rows as $r) {
            $fecha = $r['h_fecha'];
            $horaDesde = substr($r['h_horaDesde'], 0, 5);
            $horaHasta = substr($r['h_horaHasta'], 0, 5);

            $item = [
                'h_id'               => (int) $r['h_id'],
                'h_fecha'            => $fecha,
                'h_horaDesde'        => $horaDesde,
                'h_horaHasta'        => $horaHasta,
                'p_id'               => (int) $r['p_id'],
                'nombre_profesional' => $r['nombre_profesional'],
                'titulo_profesional' => $r['titulo_profesional'] ?? 'Especialista',
                'avatar_profesional' => !empty($r['avatar_profesional'])
                    ? base_url($r['avatar_profesional'])
                    : base_url('public/assets/media/íconoPerfil.png'),
            ];

            $horarios[] = $item;
            if (!isset($porFecha[$fecha])) {
                $porFecha[$fecha] = [];
            }
            $porFecha[$fecha][] = $item;
        }

        return $this->respond([
            'status'    => 200,
            'horarios'  => $horarios,
            'por_fecha' => $porFecha,
            'total'     => count($horarios),
        ]);
    }

    // =========================================================================
    // POST /api/reservas/confirmar
    // Confirma la reserva del turno en estado 'Solicitado' con control de concurrencia
    // =========================================================================
    public function confirmar()
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return $this->failUnauthorized('Debes iniciar sesión para confirmar una reserva.');
        }

        $data = $this->getRequestData();

        $hId             = (int) ($data['h_id'] ?? 0);
        $nombreCompleto  = trim((string) ($data['u_nbreCompleto'] ?? $data['nombre_completo'] ?? ''));
        $telefono        = trim((string) ($data['u_tel'] ?? $data['telefono'] ?? ''));
        $correo          = trim((string) ($data['u_correo'] ?? $data['correo'] ?? ''));
        $consideraciones = trim((string) ($data['consideraciones'] ?? ''));

        if ($hId <= 0) {
            return $this->failValidationErrors('Debes seleccionar un horario válido.');
        }

        if (mb_strlen($nombreCompleto) < 3) {
            return $this->failValidationErrors('El nombre completo es obligatorio y debe tener al menos 3 caracteres.');
        }

        if (mb_strlen($telefono) < 8) {
            return $this->failValidationErrors('El teléfono de contacto es obligatorio y debe tener al menos 8 caracteres.');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $this->failValidationErrors('Debes ingresar un correo electrónico válido.');
        }

        if (mb_strlen($consideraciones) > 500) {
            $consideraciones = mb_substr($consideraciones, 0, 500);
        }

        // 1. Verificar que el horario exista y pertenezca a un servicio y profesional activos
        $horario = $this->horarioModel->getHorarioParaReserva($hId);
        if (!$horario || empty($horario['s_activo']) || empty($horario['prof_activo'])) {
            return $this->failNotFound('El bloque horario seleccionado no existe o ya no se encuentra habilitado.');
        }

        // 2. No permitir reservar fechas pasadas
        if ($horario['h_fecha'] < date('Y-m-d')) {
            return $this->failValidationErrors('No es posible reservar un turno en una fecha pasada.');
        }

        // 3. Control de concurrencia: verificar que no tenga un turno activo
        if ($this->horarioModel->tieneTurnoActivo($hId)) {
            return $this->fail('Este horario acaba de ser reservado por otra persona. Por favor, elegí otro horario disponible.', 409);
        }

        // 4. Verificar que el correo no pertenezca a otro usuario distinto
        $db = \Config\Database::connect();
        $existeCorreo = $db->table('usuario')
            ->where('u_correo', $correo)
            ->where('u_id !=', $usuarioId)
            ->countAllResults();

        if ($existeCorreo > 0) {
            return $this->failValidationErrors('El correo electrónico ingresado ya está registrado en otra cuenta.');
        }

        // 5. Transacción atómica para actualizar datos de contacto y registrar el turno
        $db->transBegin();

        try {
            // Actualizar datos de contacto del usuario
            $db->table('usuario')
                ->where('u_id', $usuarioId)
                ->update([
                    'u_nbreCompleto' => $nombreCompleto,
                    'u_tel'          => $telefono,
                    'u_correo'       => $correo,
                ]);

            session()->set([
                'usuario_nombre' => $nombreCompleto,
                'usuario_correo' => $correo,
            ]);

            $cId = $this->obtenerClienteId($usuarioId);
            $db->table('cliente')->where('c_id', $cId)->update(['c_consideraciones' => $consideraciones]);

            // Re-verificar dentro de la transacción si existe fila en `turno` para `t_hId`
            $turnoExistente = $db->table('turno')
                ->where('t_hId', $hId)
                ->get()
                ->getRowArray();

            if ($turnoExistente && in_array($turnoExistente['estado'], ['Solicitado', 'Confirmado', 'Realizado'], true)) {
                $db->transRollback();
                return $this->fail('Este horario ya no se encuentra disponible.', 409);
            }

            $notaCliente = $consideraciones !== ''
                ? 'Consideraciones: ' . $consideraciones
                : null;

            $datosTurno = [
                't_hId'              => $hId,
                't_pId'              => (int) $horario['h_pId'],
                't_cId'              => $cId,
                't_fecha'            => $horario['h_fecha'],
                't_horaDesde'        => $horario['h_horaDesde'],
                't_horaHasta'        => $horario['h_horaHasta'],
                'estado'             => 'Solicitado',
                'abonado'            => 0,
                'monto'              => (float) ($horario['s_precio'] ?? 0),
                'motivo_cancelacion' => $notaCliente,
            ];

            if ($turnoExistente) {
                // Reutilizar el registro previo (Disponible o Cancelado) respetando UNIQUE(t_hId)
                $tId = (int) $turnoExistente['t_id'];
                $this->turnoModel->update($tId, $datosTurno);
            } else {
                $tId = (int) $this->turnoModel->insert($datosTurno);
            }

            if ($db->transStatus() === false || !$tId) {
                $db->transRollback();
                return $this->failServerError('No se pudo registrar la reserva. Intentá nuevamente.');
            }

            $db->transCommit();

            return $this->respondCreated([
                'status'       => 201,
                'message'      => '¡Tu turno fue solicitado con éxito! La profesional se pondrá en contacto con vos a la brevedad.',
                't_id'         => $tId,
                'redirect_url' => base_url('mis-turnos'),
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->failServerError('Error al procesar la reserva: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PUT /api/cliente/turnos/cancelar/(:num)
    // Permite al cliente cancelar su propio turno si está Solicitado o Confirmado
    // =========================================================================
    public function cancelarTurno($tId = null)
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        $tId = (int) $tId;
        if ($tId <= 0) {
            return $this->failValidationErrors('Identificador de turno inválido.');
        }

        $cId = $this->obtenerClienteId($usuarioId);
        $turno = $this->turnoModel->find($tId);

        if (!$turno) {
            return $this->failNotFound('El turno solicitado no existe.');
        }

        // Control estricto anti-IDOR: el turno debe pertenecer al cliente autenticado
        if ((int) ($turno['t_cId'] ?? 0) !== $cId) {
            return $this->failForbidden('No tenés permiso para modificar este turno.');
        }

        if (!$this->turnoModel->esTransicionValida($turno['estado'], 'Cancelado')) {
            return $this->failValidationErrors(
                "No es posible cancelar un turno que se encuentra en estado '{$turno['estado']}'."
            );
        }

        $data = $this->getRequestData();
        $motivo = trim((string) ($data['motivo_cancelacion'] ?? ''));
        if ($motivo === '') {
            $motivo = 'Cancelado por la clienta desde Mi Panel';
        }

        $this->turnoModel->update($tId, [
            'estado'             => 'Cancelado',
            'motivo_cancelacion' => mb_substr($motivo, 0, 255),
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Tu turno ha sido cancelado correctamente y el horario quedó liberado.',
        ]);
    }

    // =========================================================================
    // POST /api/cliente/turnos/google-calendar/(:num)
    // Genera URL estructurada RFC 5545 para agendar el turno en Google Calendar
    // =========================================================================
    public function exportarGoogleCalendar($tId = null)
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return $this->failUnauthorized('Sesión no iniciada.');
        }

        $tId = (int) $tId;
        $cId = $this->obtenerClienteId($usuarioId);
        $turno = $this->turnoModel->getTurnoDetalle($tId);

        if (!$turno) {
            return $this->failNotFound('Turno no encontrado.');
        }

        if ((int) ($turno['t_cId'] ?? 0) !== $cId) {
            return $this->failForbidden('No tenés permiso para acceder a este turno.');
        }

        $tz = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $dtStart = new \DateTime($turno['fecha'] . ' ' . $turno['h_horaDesde'], $tz);
        $dtEnd   = new \DateTime($turno['fecha'] . ' ' . $turno['h_horaHasta'], $tz);

        $dates = $dtStart->format('Ymd\THis') . '/' . $dtEnd->format('Ymd\THis');
        $title = 'Turno: ' . ($turno['nombre_servicio'] ?? 'Servicio') . ' — Her Beauty Center';
        $details = "Servicio: " . ($turno['nombre_servicio'] ?? '—') . "\n"
                 . "Profesional: " . ($turno['nombre_profesional'] ?? '—') . "\n"
                 . "Estado: " . ($turno['estado'] ?? 'Solicitado') . "\n"
                 . "Centro: Her Beauty Center (San Luis, Argentina)";

        $gcalUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action'   => 'TEMPLATE',
            'text'     => $title,
            'dates'    => $dates,
            'details'  => $details,
            'location' => 'Her Beauty Center, San Luis, Argentina',
            'ctz'      => 'America/Argentina/Buenos_Aires',
        ]);

        return $this->respond([
            'status'          => 200,
            'message'         => 'Enlace de Google Calendar generado.',
            'google_sync_url' => $gcalUrl,
        ]);
    }
}

