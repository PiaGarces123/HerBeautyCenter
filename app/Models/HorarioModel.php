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
}
