<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class DashboardApi extends BaseController
{
    use ResponseTrait;

    public function adminStats()
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('No autorizado');
        }

        $db = \Config\Database::connect();
        
        // 1. Cantidad de Clientes
        $clientes = $db->table('cliente')->countAllResults();
        
        // 2. Cantidad de Profesionales
        $profesionales = $db->table('profesional')->countAllResults();

        // Calcular periodo actual (30 dias) y anterior (30-60 dias)
        $date30DaysAgo = date('Y-m-d', strtotime('-30 days'));
        $date60DaysAgo = date('Y-m-d', strtotime('-60 days'));
        
        // 3. Servicio más solicitado
        $mostRequested = $db->query("
            SELECT s.nombre, COUNT(t.id_turno) as total
            FROM turno t
            JOIN horario h ON t.id_horario = h.id_horario
            JOIN servicio s ON h.id_servicio = s.id_servicio
            GROUP BY s.id_servicio
            ORDER BY total DESC
            LIMIT 1
        ")->getRowArray();

        // Tendencia de este servicio
        $trendStr = 'Sin datos recientes';
        if ($mostRequested) {
            $nombreSvc = $mostRequested['nombre'];
            // Turnos en los últimos 30 días
            $currentPeriodCount = $db->query("
                SELECT COUNT(t.id_turno) as total
                FROM turno t
                JOIN horario h ON t.id_horario = h.id_horario
                JOIN servicio s ON h.id_servicio = s.id_servicio
                WHERE s.nombre = ? AND t.fecha >= ?
            ", [$nombreSvc, $date30DaysAgo])->getRow()->total;

            // Turnos en 30 días anteriores
            $previousPeriodCount = $db->query("
                SELECT COUNT(t.id_turno) as total
                FROM turno t
                JOIN horario h ON t.id_horario = h.id_horario
                JOIN servicio s ON h.id_servicio = s.id_servicio
                WHERE s.nombre = ? AND t.fecha >= ? AND t.fecha < ?
            ", [$nombreSvc, $date60DaysAgo, $date30DaysAgo])->getRow()->total;

            if ($previousPeriodCount > 0) {
                $percentage = (($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100;
                $arrow = $percentage >= 0 ? '↑' : '↓';
                $trendStr = sprintf("%s %d%% respecto al período anterior", $arrow, abs((int)$percentage));
            } else if ($currentPeriodCount > 0) {
                $trendStr = "↑ 100% respecto al período anterior";
            }
        }

        // 4. Servicio menos solicitado
        $leastRequested = $db->query("
            SELECT s.nombre, COUNT(t.id_turno) as total
            FROM turno t
            JOIN horario h ON t.id_horario = h.id_horario
            JOIN servicio s ON h.id_servicio = s.id_servicio
            GROUP BY s.id_servicio
            ORDER BY total ASC
            LIMIT 1
        ")->getRowArray();

        return $this->respond([
            'clientes' => $clientes,
            'profesionales' => $profesionales,
            'masSolicitado' => [
                'nombre' => $mostRequested ? $mostRequested['nombre'] : 'N/A',
                'total' => $mostRequested ? $mostRequested['total'] : 0,
                'tendencia' => $trendStr
            ],
            'menosSolicitado' => [
                'nombre' => $leastRequested ? $leastRequested['nombre'] : 'N/A',
                'total' => $leastRequested ? $leastRequested['total'] : 0
            ]
        ]);
    }

    public function profesionalStats()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return $this->failUnauthorized('No autorizado');
        }

        $idProfesional = $session->get('id_profesional');
        if (!$idProfesional) {
            return $this->failUnauthorized('No eres profesional');
        }

        $db = \Config\Database::connect();
        
        $hoy = date('Y-m-d');

        // 1. Turnos Hoy
        $turnosHoy = $db->table('turno')
            ->where('id_profesional', $idProfesional)
            ->where('fecha', $hoy)
            ->countAllResults();

        // 2. Próximos turnos (futuros, a partir de hoy)
        $proximosTurnos = $db->table('turno')
            ->where('id_profesional', $idProfesional)
            ->where('fecha >=', $hoy)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->countAllResults();

        // 3. Mis servicios más solicitados
        $misServicios = $db->query("
            SELECT s.nombre, COUNT(t.id_turno) as total
            FROM turno t
            JOIN horario h ON t.id_horario = h.id_horario
            JOIN servicio s ON h.id_servicio = s.id_servicio
            WHERE t.id_profesional = ?
            GROUP BY s.id_servicio
            ORDER BY total DESC
            LIMIT 1
        ", [$idProfesional])->getRowArray();

        // 4. Servicios que ofrezco
        $serviciosOfrecidos = $db->table('profesional_servicio')
            ->where('id_profesional', $idProfesional)
            ->countAllResults();

        return $this->respond([
            'turnosHoy' => $turnosHoy,
            'proximosTurnos' => $proximosTurnos,
            'misServiciosMasSolicitados' => [
                'nombre' => $misServicios ? $misServicios['nombre'] : 'N/A',
                'total' => $misServicios ? $misServicios['total'] : 0
            ],
            'serviciosOfrecidos' => $serviciosOfrecidos
        ]);
    }
}
