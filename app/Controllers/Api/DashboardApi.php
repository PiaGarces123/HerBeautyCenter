<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * DashboardApi
 * Her Beauty Center
 *
 * Controlador de API encargado de obtener las estadísticas
 * que se muestran en los paneles de administración y profesionales.
 *
 * Sus responsabilidades principales son:
 * 1. Obtener estadísticas generales del sistema para el administrador.
 * 2. Consultar la cantidad de clientes y profesionales registrados.
 * 3. Identificar los servicios más y menos solicitados.
 * 4. Calcular la tendencia de solicitudes de un servicio.
 * 5. Obtener estadísticas de turnos y servicios de cada profesional.
 *
 * Las respuestas se envían en formato JSON mediante ResponseTrait.
 * El acceso está restringido según el rol del usuario autenticado.
 */
class DashboardApi extends BaseController
{
    use ResponseTrait;


    //----------------------
    // ADMIN STATS
    //----------------------
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

        // Valor predeterminado cuando no hay información reciente
        // para calcular una tendencia.
        $trendStr = 'Sin datos recientes';

        // Solo realiza la comparación si existe un servicio más solicitado.
        if ($mostRequested) {
            // Recupera el nombre del servicio identificado anteriormente.
            $nombreSvc = $mostRequested['nombre'];

            // Turnos en los últimos 30 días
            $currentPeriodCountRow = $db->query("
                SELECT COUNT(t.t_id) AS total
                FROM turno t
                JOIN horario h ON t.t_hId = h.h_id
                JOIN servicio s ON h.h_sId = s.s_id
                WHERE s.s_nbre = ? AND h.h_fecha >= ?
            ", [$nombreSvc, $date30DaysAgo])->getRow();

            // Obtiene el total como número entero.
            $currentPeriodCount = $currentPeriodCountRow ? (int) $currentPeriodCountRow->total : 0;

            // Turnos en 30 días anteriores
            $previousPeriodCountRow = $db->query("
                SELECT COUNT(t.t_id) AS total
                FROM turno t
                JOIN horario h ON t.t_hId = h.h_id
                JOIN servicio s ON h.h_sId = s.s_id
                WHERE s.s_nbre = ? AND h.h_fecha >= ? AND h.h_fecha < ?
            ", [$nombreSvc, $date60DaysAgo, $date30DaysAgo])->getRow();

            // Obtiene el total del período anterior.
            $previousPeriodCount = $previousPeriodCountRow ? (int) $previousPeriodCountRow->total : 0;

            // Calcula la variación porcentual únicamente si el período anterior
            // tuvo al menos un turno registrado.
            if ($previousPeriodCount > 0) {
                // Calcula cuánto aumentaron o disminuyeron las solicitudes
                // en comparación con el período anterior.
                $percentage = (($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100;

                // Selecciona una flecha según el resultado:
                $arrow = $percentage >= 0 ? '↑' : '↓';
                $trendStr = sprintf("%s %d%% respecto al período anterior", $arrow, abs((int) $percentage));

                // Si el período anterior no tuvo turnos, pero el actual sí,
                // se informa un crecimiento del 100%.
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

        // Devuelve todas las estadísticas obtenidas en un único objeto JSON
        return $this->respond([
            'clientes' => $clientes,
            'profesionales' => $profesionales,
            'masSolicitado' => [
                'nombre' => $mostRequested ? $mostRequested['nombre'] : 'N/A',
                'total' => $mostRequested ? (int) $mostRequested['total'] : 0,
                'tendencia' => $trendStr
            ],
            'menosSolicitado' => [
                'nombre' => $leastRequested ? $leastRequested['nombre'] : 'N/A',
                'total' => $leastRequested ? (int) $leastRequested['total'] : 0
            ]
        ]);
    }


    //----------------------
    // PROFESIONAL STATS
    //----------------------
    public function profesionalStats()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return $this->failUnauthorized('No autorizado');
        }

        // Intenta obtener el identificador del profesional desde la sesión.
        // Se contemplan dos posibles nombres de variable de sesión.
        $idProfesional = $session->get('p_id') ?? $session->get('id_profesional');

        $db = \Config\Database::connect();

        // Si el identificador no está almacenado en la sesión,
        // busca el perfil profesional asociado al usuario autenticado.
        if (!$idProfesional) {
            $profRow = $db->table('profesional')->where('u_id', $session->get('usuario_id'))->get()->getRowArray();

            // Recupera el identificador del profesional si existe.
            // Si no se encuentra el registro, conserva el valor null.
            $idProfesional = $profRow ? (int) $profRow['p_id'] : null;
        }

        // Verifica que el usuario tenga un perfil profesional asociado.
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


        /*
         * RESPUESTA DE LA API
         *
         * Devuelve las estadísticas del profesional en formato JSON.
         * Si todavía no tiene turnos registrados, el servicio más solicitado
         * se informa como N/A y su cantidad como cero.
         */
        return $this->respond([
            'turnosHoy' => $turnosHoy,
            'proximosTurnos' => $proximosTurnos,
            'misServiciosMasSolicitados' => [
                'nombre' => $misServicios ? $misServicios['nombre'] : 'N/A',
                'total' => $misServicios ? (int) $misServicios['total'] : 0
            ],
            'serviciosOfrecidos' => $serviciosOfrecidos
        ]);
    }
}
