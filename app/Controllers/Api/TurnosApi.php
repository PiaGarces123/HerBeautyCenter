<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TurnoModel;
use CodeIgniter\API\ResponseTrait;

/**
 * TurnosApi
 * Her Beauty Center — Incremento N° 2 (CU07 - Gestionar Turnos)
 *
 * Endpoints para visualizar y administrar el ciclo de vida de los turnos.
 * Ubicación: app/Controllers/Api/TurnosApi.php
 */
class TurnosApi extends BaseController
{
    use ResponseTrait;

    // -------------------------------------------------------
    // Helper: verificar sesión activa y rol válido
    // -------------------------------------------------------
    private function verificarSesion(): ?array
    {
        $session = session();
        $uid     = $session->get('usuario_id');
        $rol     = $session->get('rol');
        if (!$uid || !in_array($rol, ['admin', 'profesional'], true)) return null;
        return ['uid' => $uid, 'rol' => $rol];
    }

    private function getProfesionalId(int $uid): ?int
    {
        $row = \Config\Database::connect()
            ->table('profesional')->select('p_id')->where('u_id', $uid)
            ->get()->getRowArray();
        return $row ? (int) $row['p_id'] : null;
    }

    // -------------------------------------------------------
    // GET /api/turnos/listar
    // Lista turnos de la profesional con filtros opcionales
    // -------------------------------------------------------
    public function listar(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $filtros = [
            'estado'      => $this->request->getGet('estado')      ?? '',
            'fecha_desde' => $this->request->getGet('fecha_desde') ?? '',
            'fecha_hasta' => $this->request->getGet('fecha_hasta') ?? '',
        ];

        $model  = new TurnoModel();
        $turnos = $model->getTurnosConDetalle($pId, $filtros);

        return $this->respond(['turnos' => $turnos]);
    }

    // -------------------------------------------------------
    // GET /api/turnos/detalle/:id
    // -------------------------------------------------------
    public function detalle(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        return $this->respond(['turno' => $turno]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/estado/:id
    // Actualiza el estado de un turno (con validación de transición)
    // -------------------------------------------------------
    public function actualizarEstado(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->find($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        $data        = $this->request->getJSON(true) ?: $this->request->getPost();
        $estadoNuevo = trim($data['estado'] ?? '');
        $abonado     = isset($data['abonado']) ? (int) (bool) $data['abonado'] : null;
        $monto       = isset($data['monto']) ? floatval($data['monto']) : null;
        $motivo      = trim($data['motivo_cancelacion'] ?? '');

        if (!$estadoNuevo) {
            return $this->fail('El nuevo estado es obligatorio.');
        }

        // -- Validar transición de estado (RN12) --
        if (!$model->esTransicionValida($turno['estado'], $estadoNuevo)) {
            return $this->fail("Transición inválida: no se puede pasar de \"{$turno['estado']}\" a \"$estadoNuevo\".");
        }

        // -- Cancelación requiere motivo --
        if ($estadoNuevo === 'Cancelado' && empty($motivo)) {
            return $this->fail('Debes ingresar un motivo para la cancelación.');
        }

        $updateData = ['estado' => $estadoNuevo];
        if ($estadoNuevo === 'Cancelado')  $updateData['motivo_cancelacion'] = $motivo;
        if ($abonado !== null)             $updateData['abonado']            = $abonado;
        if ($monto    !== null)            $updateData['monto']              = $monto;

        $model->update($tId, $updateData);

        // -- Si se cancela, liberamos la disponibilidad (RN12) --
        // (La disponibilidad queda intacta; el turno queda en historial — baja lógica)

        return $this->respond([
            'success' => true,
            'message' => "Turno actualizado a \"$estadoNuevo\" correctamente.",
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/turnos/pago/:id
    // Actualiza el estado de abono y monto
    // -------------------------------------------------------
    public function actualizarPago(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->find($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        $data    = $this->request->getJSON(true) ?: $this->request->getPost();
        $abonado = isset($data['abonado']) ? (int) (bool) $data['abonado'] : (int) $turno['abonado'];
        $monto   = isset($data['monto'])   ? floatval($data['monto'])      : floatval($turno['monto']);

        if ($monto < 0) {
            return $this->fail('El monto no puede ser negativo.');
        }

        $model->update($tId, ['abonado' => $abonado, 'monto' => $monto]);

        return $this->respond(['success' => true, 'message' => 'Información de pago actualizada.']);
    }

    // -------------------------------------------------------
    // POST /api/turnos/google-calendar/:id
    // Retorna datos estructurados del turno para exportar a GCal
    // La integración real OAuth2 requiere credenciales de cada profesional
    // -------------------------------------------------------
    public function exportarGoogleCalendar(int $tId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model = new TurnoModel();
        $turno = $model->getTurnoDetalle($tId);

        if (!$turno || (int) $turno['t_pId'] !== $pId) {
            return $this->failNotFound('Turno no encontrado o sin permiso.');
        }

        if ($turno['estado'] !== 'Confirmado') {
            return $this->fail('Solo se pueden exportar a Google Calendar los turnos en estado Confirmado.');
        }

        // Datos mínimos necesarios para el evento de Google Calendar
        $eventoGCal = [
            'summary'     => 'Turno: ' . $turno['nombre_servicio'],
            'description' => 'Cliente: ' . ($turno['nombre_cliente'] ?? 'Sin asignar'),
            'start'       => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaDesde'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
            'end'         => [
                'dateTime' => $turno['fecha'] . 'T' . $turno['h_horaHasta'],
                'timeZone' => 'America/Argentina/Buenos_Aires',
            ],
        ];

        // Construir enlace "Agregar a Google Calendar" (RFC 5545 compatible)
        $startTs = strtotime($turno['fecha'] . ' ' . $turno['h_horaDesde']);
        $endTs   = strtotime($turno['fecha'] . ' ' . $turno['h_horaHasta']);
        $startDt = date('Ymd\THis', $startTs);
        $endDt   = date('Ymd\THis', $endTs);

        $detalles = "Her Beauty Center — Turno Confirmado\n"
                  . "Servicio: " . ($turno['nombre_servicio'] ?? 'Servicio') . "\n"
                  . "Cliente: " . ($turno['nombre_cliente'] ?? 'Sin asignar') . "\n"
                  . (!empty($turno['tel_cliente']) ? "Teléfono: " . $turno['tel_cliente'] . "\n" : "")
                  . (!empty($turno['correo_cliente']) ? "Email: " . $turno['correo_cliente'] . "\n" : "")
                  . "Monto: $" . number_format($turno['monto'] ?? 0, 0, ',', '.') . "\n"
                  . "Estado: Confirmado";

        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode('Turno: ' . ($turno['nombre_servicio'] ?? 'Estética') . ' — ' . ($turno['nombre_cliente'] ?? 'Cliente'))
            . '&dates=' . $startDt . '/' . $endDt
            . '&details=' . urlencode($detalles)
            . '&location=' . urlencode('Her Beauty Center')
            . '&ctz=' . urlencode('America/Argentina/Buenos_Aires');

        return $this->respond([
            'success'       => true,
            'evento'        => $eventoGCal,
            'google_link'   => $link,
            'message'       => 'Datos del turno preparados para Google Calendar.',
        ]);
    }
}
