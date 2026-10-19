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
            SELECT s.s_nbre AS nombre, COUNT(t.t_id) AS total
            FROM turno t
            JOIN horario h ON t.t_hId = h.h_id
            JOIN servicio s ON h.h_sId = s.s_id
            GROUP BY s.s_id, s.s_nbre
            ORDER BY total DESC
            LIMIT 1
        ")->getRowArray();

        // Tendencia de este servicio
        $trendStr = 'Sin datos recientes';
        if ($mostRequested) {
            $nombreSvc = $mostRequested['nombre'];
            // Turnos en los últimos 30 días
            $currentPeriodCountRow = $db->query("
                SELECT COUNT(t.t_id) AS total
                FROM turno t
                JOIN horario h ON t.t_hId = h.h_id
                JOIN servicio s ON h.h_sId = s.s_id
                WHERE s.s_nbre = ? AND h.h_fecha >= ?
            ", [$nombreSvc, $date30DaysAgo])->getRow();
            $currentPeriodCount = $currentPeriodCountRow ? (int)$currentPeriodCountRow->total : 0;

            // Turnos en 30 días anteriores
            $previousPeriodCountRow = $db->query("
                SELECT COUNT(t.t_id) AS total
                FROM turno t
                JOIN horario h ON t.t_hId = h.h_id
                JOIN servicio s ON h.h_sId = s.s_id
                WHERE s.s_nbre = ? AND h.h_fecha >= ? AND h.h_fecha < ?
            ", [$nombreSvc, $date60DaysAgo, $date30DaysAgo])->getRow();
            $previousPeriodCount = $previousPeriodCountRow ? (int)$previousPeriodCountRow->total : 0;

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
            SELECT s.s_nbre AS nombre, COUNT(t.t_id) AS total
            FROM turno t
            JOIN horario h ON t.t_hId = h.h_id
            JOIN servicio s ON h.h_sId = s.s_id
            GROUP BY s.s_id, s.s_nbre
            ORDER BY total ASC
            LIMIT 1
        ")->getRowArray();

        return $this->respond([
            'clientes' => $clientes,
            'profesionales' => $profesionales,
            'masSolicitado' => [
                'nombre' => $mostRequested ? $mostRequested['nombre'] : 'N/A',
                'total' => $mostRequested ? (int)$mostRequested['total'] : 0,
                'tendencia' => $trendStr
            ],
            'menosSolicitado' => [
                'nombre' => $leastRequested ? $leastRequested['nombre'] : 'N/A',
                'total' => $leastRequested ? (int)$leastRequested['total'] : 0
            ]
        ]);
    }

    public function profesionalStats()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return $this->failUnauthorized('No autorizado');
        }

        $idProfesional = $session->get('p_id') ?? $session->get('id_profesional');
        $db = \Config\Database::connect();

        if (!$idProfesional) {
            $profRow = $db->table('profesional')->where('u_id', $session->get('usuario_id'))->get()->getRowArray();
            $idProfesional = $profRow ? (int)$profRow['p_id'] : null;
        }

        if (!$idProfesional) {
            return $this->failUnauthorized('No eres profesional');
        }
        
        $hoy = date('Y-m-d');

        // 1. Turnos Hoy
        $turnosHoy = $db->table('turno')
            ->join('horario', 'horario.h_id = turno.t_hId')
            ->where('turno.t_pId', $idProfesional)
            ->where('horario.h_fecha', $hoy)
            ->countAllResults();

        // 2. Próximos turnos (futuros, a partir de hoy)
        $proximosTurnos = $db->table('turno')
            ->join('horario', 'horario.h_id = turno.t_hId')
            ->where('turno.t_pId', $idProfesional)
            ->where('horario.h_fecha >=', $hoy)
            ->whereIn('turno.estado', ['Solicitado', 'Confirmado'])
            ->countAllResults();

        // 3. Mis servicios más solicitados
        $misServicios = $db->query("
            SELECT s.s_nbre AS nombre, COUNT(t.t_id) AS total
            FROM turno t
            JOIN horario h ON t.t_hId = h.h_id
            JOIN servicio s ON h.h_sId = s.s_id
            WHERE t.t_pId = ?
            GROUP BY s.s_id, s.s_nbre
            ORDER BY total DESC
            LIMIT 1
        ", [$idProfesional])->getRowArray();

        // 4. Servicios que ofrezco
        $serviciosOfrecidos = $db->table('profesional_servicio')
            ->where('p_id', $idProfesional)
            ->countAllResults();

        return $this->respond([
            'turnosHoy' => $turnosHoy,
            'proximosTurnos' => $proximosTurnos,
            'misServiciosMasSolicitados' => [
                'nombre' => $misServicios ? $misServicios['nombre'] : 'N/A',
                'total' => $misServicios ? (int)$misServicios['total'] : 0
            ],
            'serviciosOfrecidos' => $serviciosOfrecidos
        ]);
    }
}
