<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TurnoModel
 * Her Beauty Center — Incremento N° 2
 *
 * Gestiona el ciclo de vida de los turnos (tabla `turno`).
 * Estados válidos: Disponible → Solicitado → Confirmado → Realizado | Cancelado
 */
class TurnoModel extends Model
{
    protected $table      = 'turno';
    protected $primaryKey = 't_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        't_hId', 't_pId', 't_cId', 't_fecha', 't_horaDesde', 't_horaHasta',
        'estado', 'abonado', 'monto', 'motivo_cancelacion',
    ];

    // Transiciones de estado válidas (RN12 - ciclo de vida)
    public const TRANSICIONES_VALIDAS = [
        'Disponible'  => ['Solicitado'],
        'Solicitado'  => ['Confirmado', 'Cancelado'],
        'Confirmado'  => ['Realizado', 'Cancelado'],
        'Realizado'   => [],
        'Cancelado'   => [],
    ];

    // ------------------------------------------------------------
    // Obtener turnos con datos completos de profesional y cliente
    // ------------------------------------------------------------
    public function getTurnosConDetalle(int $pId, array $filtros = []): array
    {
        $builder = $this->db->table('turno')
            ->select('turno.*, horario.h_fecha AS fecha, horario.h_horaDesde, horario.h_horaHasta,
                      servicio.s_nbre AS nombre_servicio, servicio.s_id AS servicio_id,
                      u_prof.u_nbreCompleto AS nombre_profesional,
                      u_cli.u_nbreCompleto AS nombre_cliente,
                      cliente.c_id AS cliente_id')
            ->join('horario', 'horario.h_id = turno.t_hId')
            ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
            ->join('profesional', 'profesional.p_id = turno.t_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->join('cliente', 'cliente.c_id = turno.t_cId', 'left')
            ->join('usuario AS u_cli', 'u_cli.u_id = cliente.c_uId', 'left')
            ->where('turno.t_pId', $pId);

        // Filtros opcionales
        if (!empty($filtros['estado'])) {
            $builder->where('turno.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $builder->where('horario.h_fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $builder->where('horario.h_fecha <=', $filtros['fecha_hasta']);
        }

        return $builder->orderBy('horario.h_fecha', 'DESC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->get()->getResultArray();
    }

    // ------------------------------------------------------------
    // Obtener un turno con toda su información (para modal detalle)
    // ------------------------------------------------------------
    public function getTurnoDetalle(int $tId): ?array
    {
        $result = $this->db->table('turno')
            ->select('turno.*, horario.h_fecha AS fecha, horario.h_horaDesde, horario.h_horaHasta,
                      servicio.s_nbre AS nombre_servicio, servicio.s_precio AS precio_servicio,
                      u_prof.u_nbreCompleto AS nombre_profesional, u_prof.u_correo AS correo_profesional,
                      u_cli.u_nbreCompleto AS nombre_cliente, u_cli.u_correo AS correo_cliente,
                      u_cli.u_tel AS tel_cliente')
            ->join('horario', 'horario.h_id = turno.t_hId')
            ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
            ->join('profesional', 'profesional.p_id = turno.t_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->join('cliente', 'cliente.c_id = turno.t_cId', 'left')
            ->join('usuario AS u_cli', 'u_cli.u_id = cliente.c_uId', 'left')
            ->where('turno.t_id', $tId)
            ->get()->getRowArray();

        return $result ?: null;
    }

    // ------------------------------------------------------------
    // Validar que la transición de estado sea lógica
    // ------------------------------------------------------------
    public function esTransicionValida(string $estadoActual, string $estadoNuevo): bool
    {
        return in_array($estadoNuevo, self::TRANSICIONES_VALIDAS[$estadoActual] ?? [], true);
    }
}
