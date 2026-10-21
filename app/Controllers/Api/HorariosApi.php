<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\HorarioModel;
use CodeIgniter\API\ResponseTrait;

/**
 * HorariosApi
 * Her Beauty Center — Incremento N° 2 (CU08 - Gestionar Disponibilidad)
 *
 * Endpoints para crear, editar, eliminar y consultar bloques de disponibilidad.
 * Ubicación: app/Controllers/Api/HorariosApi.php
 */
class HorariosApi extends BaseController
{
    use ResponseTrait;

    // -------------------------------------------------------
    // Helper: verificar sesión y roles permitidos
    // -------------------------------------------------------
    private function verificarSesion(): ?array
    {
        $session = session();
        $uid     = $session->get('usuario_id');
        $rol     = $session->get('rol');

        if (!$uid || !in_array($rol, ['admin', 'profesional'], true)) {
            return null;
        }
        return ['uid' => $uid, 'rol' => $rol];
    }

    // -------------------------------------------------------
    // Helper: obtener p_id de la sesión activa
    // -------------------------------------------------------
    private function getProfesionalId(int $uid): ?int
    {
        $db  = \Config\Database::connect();
        $row = $db->table('profesional')->select('p_id')->where('u_id', $uid)->get()->getRowArray();
        return $row ? (int) $row['p_id'] : null;
    }

    // -------------------------------------------------------
    // GET /api/horarios/eventos?start=&end=
    // Devuelve eventos para FullCalendar en formato JSON
    // -------------------------------------------------------
    public function eventos(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        // Sanear fechas
        if (!$this->esDateValida($start)) $start = date('Y-m-01');
        if (!$this->esDateValida($end))   $end   = date('Y-m-t');

        $model   = new HorarioModel();
        $filas   = $model->getEventosCalendario($pId, $start, $end);
        $eventos = [];

        foreach ($filas as $f) {
            $estado    = $f['estado'] ?? null;         // null => disponibilidad sin turno
            $titulo    = $f['nombre_servicio'] ?? 'Disponibilidad';
            $color     = $this->colorPorEstado($estado);

            $eventos[] = [
                'id'              => 'h_' . $f['h_id'],
                'h_id'            => (int) $f['h_id'],
                't_id'            => $f['t_id'] ? (int) $f['t_id'] : null,
                'title'           => $titulo,
                'start'           => $f['h_fecha'] . 'T' . $f['h_horaDesde'],
                'end'             => $f['h_fecha'] . 'T' . $f['h_horaHasta'],
                'color'           => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'tipo'           => $estado ? 'turno' : 'disponibilidad',
                    'estado'         => $estado ?? 'Disponible',
                    'nombre_servicio'=> $titulo,
                    'servicio_id'    => $f['servicio_id'] ?? null,
                    'nombre_cliente' => $f['nombre_cliente'] ?? null,
                    'monto'          => $f['monto'] ?? 0,
                    'abonado'        => (bool) ($f['abonado'] ?? false),
                ],
            ];
        }

        return $this->respond(['eventos' => $eventos]);
    }

    // -------------------------------------------------------
    // POST /api/horarios/crear
    // -------------------------------------------------------
    public function crear(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $db = \Config\Database::connect();
        $data = $this->getRequestData();

        $sId   = (int) ($data['h_sId'] ?? $data['s_id'] ?? $data['servicio_id'] ?? 0);
        $fecha = trim($data['h_fecha'] ?? $data['fecha'] ?? '');
        $desde = trim($data['h_horaDesde'] ?? $data['hora_desde'] ?? $data['desde'] ?? '');
        $hasta = trim($data['h_horaHasta'] ?? $data['hora_hasta'] ?? $data['hasta'] ?? '');

        $pId = !empty($data['p_id']) ? (int) $data['p_id'] : $this->getProfesionalId($auth['uid']);
        if (!$pId && $auth['rol'] === 'admin') {
            if ($sId) {
                $pRow = $db->table('profesional_servicio')->where('p_sId', $sId)->get()->getRowArray();
                if ($pRow) $pId = (int) $pRow['p_id'];
            }
            if (!$pId) {
                $pRow = $db->table('profesional')->get()->getRowArray();
                if ($pRow) $pId = (int) $pRow['p_id'];
            }
        }
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        // -- Validaciones de campos obligatorios --
        if (!$sId || !$fecha || !$desde || !$hasta) {
            return $this->fail('Todos los campos son obligatorios: servicio, fecha, hora inicio y hora fin.');
        }

        // -- Fecha no puede ser pasada --
        if (!$this->esDateValida($fecha) || $fecha < date('Y-m-d')) {
            return $this->fail('No se puede registrar disponibilidad en fechas ya pasadas.');
        }

        // -- Hora fin debe ser posterior a hora inicio --
        if ($hasta <= $desde) {
            return $this->fail('La hora de fin debe ser posterior a la hora de inicio.');
        }

        // -- Verificar que el servicio pertenece a la profesional --
        if (!$this->servicioEsDeProfesional($pId, $sId)) {
            return $this->fail('El servicio seleccionado no está asignado a tu perfil.');
        }

        $model = new HorarioModel();

        // -- Escenario 1: Superposición de horarios (RN11) --
        if ($model->existeSuperposicion($pId, $sId, $fecha, $desde, $hasta)) {
            return $this->fail('Superposición de horarios: ya tenés un bloque registrado en esa franja para este servicio.');
        }

        // -- Escenario 3: Turno activo en ese rango --
        if ($model->existeTurnoEnRango($pId, $sId, $fecha, $desde, $hasta)) {
            return $this->fail('No es posible crear la disponibilidad: ya tienes un turno reservado en esa franja horaria para este servicio.');
        }

        $hId = $model->insert([
            'h_pId'      => $pId,
            'h_sId'      => $sId,
            'h_fecha'    => $fecha,
            'h_horaDesde'=> $desde,
            'h_horaHasta'=> $hasta,
        ]);

        if (!$hId) {
            return $this->failServerError('Error al guardar la disponibilidad.');
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Disponibilidad registrada exitosamente.',
            'h_id'    => $hId,
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/horarios/editar/:id
    // -------------------------------------------------------
    public function editar(int $hId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId && $auth['rol'] === 'admin') {
            $hRow = \Config\Database::connect()->table('horario')->where('h_id', $hId)->get()->getRowArray();
            if ($hRow) $pId = (int) $hRow['h_pId'];
        }
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model   = new HorarioModel();
        $horario = $model->find($hId);

        if (!$horario || ((int) $horario['h_pId'] !== $pId && $auth['rol'] !== 'admin')) {
            return $this->failNotFound('Disponibilidad no encontrada o sin permiso.');
        }

        // -- RN11: No modificar si tiene turno activo --
        if ($model->tieneTurnoActivo($hId)) {
            return $this->fail('No se puede modificar este bloque: tiene un turno activo (Solicitado o Confirmado) vinculado.');
        }

        $data = $this->getRequestData();

        $sId   = (int) ($data['h_sId'] ?? $data['s_id'] ?? $data['servicio_id'] ?? $horario['h_sId']);
        $fecha = trim($data['h_fecha'] ?? $data['fecha'] ?? $horario['h_fecha']);
        $desde = trim($data['h_horaDesde'] ?? $data['hora_desde'] ?? $data['desde'] ?? $horario['h_horaDesde']);
        $hasta = trim($data['h_horaHasta'] ?? $data['hora_hasta'] ?? $data['hasta'] ?? $horario['h_horaHasta']);

        if ($hasta <= $desde) {
            return $this->fail('La hora de fin debe ser posterior a la hora de inicio.');
        }

        if ($model->existeSuperposicion($pId, $sId, $fecha, $desde, $hasta, $hId)) {
            return $this->fail('Superposición de horarios: ya existe un bloque en esa franja para este servicio.');
        }

        $model->update($hId, [
            'h_sId'      => $sId,
            'h_fecha'    => $fecha,
            'h_horaDesde'=> $desde,
            'h_horaHasta'=> $hasta,
        ]);

        return $this->respond(['success' => true, 'message' => 'Disponibilidad actualizada correctamente.']);
    }

    // -------------------------------------------------------
    // DELETE /api/horarios/eliminar/:id
    // -------------------------------------------------------
    public function eliminar(int $hId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $model   = new HorarioModel();
        $horario = $model->find($hId);

        if (!$horario || (int) $horario['h_pId'] !== $pId) {
            return $this->failNotFound('Disponibilidad no encontrada o sin permiso.');
        }

        // -- RN11: No eliminar si tiene turno activo --
        if ($model->tieneTurnoActivo($hId)) {
            return $this->fail('No se puede eliminar este bloque: tiene un turno activo (Solicitado o Confirmado) vinculado.');
        }

        $model->delete($hId);
        return $this->respondDeleted(['success' => true, 'message' => 'Disponibilidad eliminada correctamente.']);
    }

    // -------------------------------------------------------
    // GET /api/horarios/servicios
    // Lista servicios asignados a la profesional en sesión
    // -------------------------------------------------------
    public function serviciosProfesional(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth) return $this->failUnauthorized('Acceso denegado.');

        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId) return $this->failNotFound('No existe un perfil profesional asociado.');

        $db = \Config\Database::connect();
        $servicios = $db->table('profesional_servicio')
            ->select('servicio.s_id, servicio.s_nbre, servicio.s_duracionMinutos, servicio.s_precio')
            ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
            ->where('profesional_servicio.p_id', $pId)
            ->where('servicio.s_activo', 1)
            ->orderBy('servicio.s_nbre', 'ASC')
            ->get()->getResultArray();

        return $this->respond(['servicios' => $servicios]);
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------
    private function esDateValida(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function servicioEsDeProfesional(int $pId, int $sId): bool
    {
        $db = \Config\Database::connect();
        return $db->table('profesional_servicio')
            ->where('p_id', $pId)->where('p_sId', $sId)
            ->countAllResults() > 0;
    }

    private function colorPorEstado(?string $estado): string
    {
        return match ($estado) {
            'Solicitado' => '#d97706',  // amber
            'Confirmado' => '#059669',  // emerald
            'Realizado'  => '#6b7280',  // slate
            'Cancelado'  => '#dc2626',  // red
            default      => '#7d562d',  // brand brown (disponible)
        };
    }
}
