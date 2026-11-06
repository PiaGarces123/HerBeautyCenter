<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * HorarioModel
 * Her Beauty Center — Incremento N° 2
 *
 * Gestiona los bloques de disponibilidad horaria (tabla `horario`).
 */
class HorarioModel extends Model
{
    protected $table      = 'horario';
    protected $primaryKey = 'h_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'h_pId', 'h_sId', 'h_fecha', 'h_horaDesde', 'h_horaHasta',
    ];

    protected $validationRules = [
        'h_pId'       => 'required|integer|greater_than[0]',
        'h_sId'       => 'required|integer|greater_than[0]',
        'h_fecha'     => 'required|valid_date[Y-m-d]',
        'h_horaDesde' => 'required',
        'h_horaHasta' => 'required',
    ];

    // ------------------------------------------------------------
    // Obtener horarios de una profesional (con nombre de servicio)
    // ------------------------------------------------------------
    public function getHorariosProfesional(int $pId): array
    {
        return $this->db->table('horario')
            ->select('horario.*, servicio.s_nbre AS nombre_servicio, servicio.s_precio AS precio_servicio')
            ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
            ->where('horario.h_pId', $pId)
            ->orderBy('horario.h_fecha', 'ASC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->get()->getResultArray();
    }

    // ------------------------------------------------------------
    // Eventos para el calendario FullCalendar (rango de fechas)
    // ------------------------------------------------------------
    public function getEventosCalendario(int $pId, string $start, string $end): array
    {
        return $this->db->table('horario')
            ->select('horario.h_id, horario.h_fecha, horario.h_horaDesde, horario.h_horaHasta,
                      servicio.s_nbre AS nombre_servicio, servicio.s_id AS servicio_id,
                      turno.t_id, turno.estado, turno.abonado, turno.monto,
                      usuario_cli.u_nbreCompleto AS nombre_cliente, turno.t_cId')
            ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
            ->join('turno', 'turno.t_hId = horario.h_id', 'left')
            ->join('cliente', 'cliente.c_id = turno.t_cId', 'left')
            ->join('usuario AS usuario_cli', 'usuario_cli.u_id = cliente.c_uId', 'left')
            ->where('horario.h_pId', $pId)
            ->where('horario.h_fecha >=', $start)
            ->where('horario.h_fecha <=', $end)
            ->orderBy('horario.h_fecha', 'ASC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->get()->getResultArray();
    }

    // ------------------------------------------------------------
    // Control de superposición de franjas horarias
    // ------------------------------------------------------------
    public function existeSuperposicion(int $pId, int $sId, string $fecha, string $desde, string $hasta, ?int $excludeId = null): bool
    {
        $builder = $this->db->table('horario')
            ->where('h_pId', $pId)
            ->where('h_sId', $sId)
            ->where('h_fecha', $fecha)
            ->groupStart()
                ->where('h_horaDesde <', $hasta)
                ->where('h_horaHasta >', $desde)
            ->groupEnd();
        if ($excludeId !== null) $builder->where('h_id !=', $excludeId);
        return $builder->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // Verificar turno activo vinculado a un horario
    // ------------------------------------------------------------
    public function tieneTurnoActivo(int $hId): bool
    {
        return $this->db->table('turno')
            ->where('t_hId', $hId)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // Verificar si hay turno activo en rango propuesto
    // ------------------------------------------------------------
    public function existeTurnoEnRango(int $pId, int $sId, string $fecha, string $desde, string $hasta, ?int $excludeHId = null): bool
    {
        $builder = $this->db->table('horario')
            ->join('turno', 'turno.t_hId = horario.h_id')
            ->where('horario.h_pId', $pId)
            ->where('horario.h_sId', $sId)
            ->where('horario.h_fecha', $fecha)
            ->whereIn('turno.estado', ['Solicitado', 'Confirmado'])
            ->groupStart()
                ->where('horario.h_horaDesde <', $hasta)
                ->where('horario.h_horaHasta >', $desde)
            ->groupEnd();
        if ($excludeHId !== null) $builder->where('horario.h_id !=', $excludeHId);
        return $builder->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // Incremento N° 3 (CU04): Obtener disponibilidad de un servicio
    // para el calendario y el modal de selección de horarios
    // ------------------------------------------------------------
    public function getDisponibilidadServicio(int $sId, array $filtros = []): array
    {
        $hoy = date('Y-m-d');

        $builder = $this->db->table('horario')
            ->select('horario.h_id, horario.h_pId, horario.h_sId, horario.h_fecha, horario.h_horaDesde, horario.h_horaHasta,
                      servicio.s_nbre AS nombre_servicio, servicio.s_duracionMinutos, servicio.s_precio,
                      profesional.p_id, profesional.p_titulo AS titulo_profesional,
                      u_prof.u_nbreCompleto AS nombre_profesional, u_prof.u_avatar AS avatar_profesional')
            ->join('servicio', 'servicio.s_id = horario.h_sId')
            ->join('profesional', 'profesional.p_id = horario.h_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->where('horario.h_sId', $sId)
            ->where('servicio.s_activo', 1)
            ->where('u_prof.u_activo', 1)
            ->where('horario.h_fecha >=', $hoy)
            ->where("NOT EXISTS (SELECT 1 FROM turno WHERE turno.t_hId = horario.h_id AND turno.estado IN ('Solicitado', 'Confirmado', 'Realizado'))", null, false);

        if (!empty($filtros['p_id'])) {
            $builder->where('horario.h_pId', (int) $filtros['p_id']);
        }

        if (!empty($filtros['fecha'])) {
            $builder->where('horario.h_fecha', $filtros['fecha']);
        }

        if (!empty($filtros['start'])) {
            $builder->where('horario.h_fecha >=', $filtros['start']);
        }

        if (!empty($filtros['end'])) {
            $builder->where('horario.h_fecha <=', $filtros['end']);
        }

        if (!empty($filtros['franja'])) {
            if ($filtros['franja'] === 'manana') {
                $builder->where('horario.h_horaDesde <', '13:00:00');
            } elseif ($filtros['franja'] === 'tarde') {
                $builder->where('horario.h_horaDesde >=', '13:00:00');
            }
        }

        $rows = $builder->orderBy('horario.h_fecha', 'ASC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->orderBy('u_prof.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        // Filtrado opcional por día de la semana (1 = Lunes .. 7 = Domingo, compatible con MySQL y SQLite)
        if (!empty($filtros['dia_semana'])) {
            $diaBuscado = (int) $filtros['dia_semana'];
            $rows = array_values(array_filter($rows, static function (array $row) use ($diaBuscado): bool {
                return (int) date('N', strtotime($row['h_fecha'])) === $diaBuscado;
            }));
        }

        return $rows;
    }

    // ------------------------------------------------------------
    // Incremento N° 3 (CU04): Detalle completo de un horario para pre-confirmación
    // ------------------------------------------------------------
    public function getHorarioParaReserva(int $hId): ?array
    {
        $row = $this->db->table('horario')
            ->select('horario.*,
                      servicio.s_id, servicio.s_nbre, servicio.s_desc, servicio.s_duracionMinutos, servicio.s_precio, servicio.s_activo,
                      profesional.p_id, profesional.p_titulo,
                      u_prof.u_nbreCompleto AS nombre_profesional, u_prof.u_avatar AS avatar_profesional, u_prof.u_activo AS prof_activo')
            ->join('servicio', 'servicio.s_id = horario.h_sId')
            ->join('profesional', 'profesional.p_id = horario.h_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->where('horario.h_id', $hId)
            ->get()->getRowArray();

        return $row ?: null;
    }
}
