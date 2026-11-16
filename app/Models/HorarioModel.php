<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * HorarioModel
 * Her Beauty Center — Incrementos N° 2 y N° 3
 *
 * Gestiona los bloques de disponibilidad horaria de los profesionales
 * (tabla `horario`).
 *
 * Sus responsabilidades principales son:
 * 1. Obtener los horarios de una profesional junto con la información del servicio asociado.
 * 2. Proporcionar los eventos necesarios para mostrar los horarios y turnos en FullCalendar.
 * 3. Detectar superposiciones entre franjas horarias para evitar conflictos de disponibilidad.
 * 4. Verificar si un horario tiene turnos activos asociados.
 * 5. Comprobar si existen turnos activos dentro de un rango horario propuesto.
 * 6. Obtener la disponibilidad de un servicio aplicando filtros de búsqueda.
 * 7. Consultar el detalle de un horario para el proceso de reserva y pre-confirmación.
 */
class HorarioModel extends Model
{
    protected $table = 'horario';
    protected $primaryKey = 'h_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // Lista de campos permitidos para insertar o actualizar registros.
    protected $allowedFields = [
        'h_pId',
        'h_sId',
        'h_fecha',
        'h_horaDesde',
        'h_horaHasta',
    ];

    // ------------------------------------------------------------
    // REGLAS DE VALIDACIÓN
    // ------------------------------------------------------------
    protected $validationRules = [
        // El ID del profesional es obligatorio, debe ser un número entero
        // y tiene que ser mayor que cero.
        'h_pId' => 'required|integer|greater_than[0]',
        // El ID del servicio también es obligatorio y debe ser un entero positivo.
        'h_sId' => 'required|integer|greater_than[0]',
        // La fecha es obligatoria y debe respetar el formato año-mes-día.
        'h_fecha' => 'required|valid_date[Y-m-d]',
        // La hora de inicio es obligatoria.
        'h_horaDesde' => 'required',
        // La hora de finalización también es obligatoria.
        'h_horaHasta' => 'required',
    ];

    // ------------------------------------------------------------
    // OBTENER HORARIOS DE UNA PROFESIONAL
    // Incluye información del servicio asociado.
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
    // EVENTOS PARA FULLCALENDAR
    // Obtiene horarios y turnos dentro de un rango de fechas.
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
        if ($excludeId !== null)
            $builder->where('h_id !=', $excludeId);
        return $builder->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // VERIFICAR SI UN HORARIO TIENE UN TURNO ACTIVO
    // ------------------------------------------------------------
    public function tieneTurnoActivo(int $hId): bool
    {
        return $this->db->table('turno')
            ->where('t_hId', $hId)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // VERIFICAR TURNOS ACTIVOS EN UN RANGO HORARIO
    // Comprueba si una nueva franja se superpone con un turno activo.
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
        if ($excludeHId !== null)
            $builder->where('horario.h_id !=', $excludeHId);
        return $builder->countAllResults() > 0;
    }

    // ------------------------------------------------------------
    // OBTENER DISPONIBILIDAD DE UN SERVICIO
    // Devuelve los horarios disponibles para el proceso de reserva.
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

        // FILTRO OPCIONAL: PROFESIONAL
        if (!empty($filtros['p_id'])) {
            $builder->where('horario.h_pId', (int) $filtros['p_id']);
        }

        // FILTRO OPCIONAL: FECHA EXACTA
        if (!empty($filtros['fecha'])) {
            $builder->where('horario.h_fecha', $filtros['fecha']);
        }

        // FILTRO OPCIONAL: FECHA INICIAL
        if (!empty($filtros['start'])) {
            $builder->where('horario.h_fecha >=', $filtros['start']);
        }

        // FILTRO OPCIONAL: FECHA FINAL
        if (!empty($filtros['end'])) {
            $builder->where('horario.h_fecha <=', $filtros['end']);
        }

        // FILTRO OPCIONAL: FRANJA HORARIA
        if (!empty($filtros['franja'])) {
            if ($filtros['franja'] === 'manana') {
                $builder->where('horario.h_horaDesde <', '13:00:00');
            } elseif ($filtros['franja'] === 'tarde') {
                $builder->where('horario.h_horaDesde >=', '13:00:00');
            }
        }


        // Ejecuta la consulta y ordena los resultados:
        // 1. Por fecha, de la más próxima a la más lejana.
        // 2. Por hora de inicio, de menor a mayor.
        // 3. Por nombre de profesional, en orden alfabético.
        $rows = $builder->orderBy('horario.h_fecha', 'ASC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->orderBy('u_prof.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        // Filtrado opcional por día de la semana (1 = Lunes .. 7 = Domingo, compatible con MySQL y SQLite)
        if (!empty($filtros['dia_semana'])) {
            $diaBuscado = (int) $filtros['dia_semana'];

            // array_filter recorre los resultados y conserva únicamente
            // aquellos horarios cuyo día de la semana coincide con el solicitado.

            // static function define una función anónima que no necesita
            // acceder al contexto del objeto actual ($this).

            // use ($diaBuscado) permite utilizar dentro de esa función
            // la variable definida fuera de ella.
            $rows = array_values(array_filter($rows, static function (array $row) use ($diaBuscado): bool {

                // strtotime convierte la fecha en un valor de tiempo
                // que PHP puede interpretar.
                // date('N', ...) obtiene el día de la semana:
                // 1 = Lunes, 2 = Martes, ..., 7 = Domingo.
                // Se compara con el día solicitado.
                return (int) date('N', strtotime($row['h_fecha'])) === $diaBuscado;
            }));
        }

        return $rows;
    }

    // ------------------------------------------------------------
    // OBTENER DETALLE DE UN HORARIO PARA RESERVA
    // Recupera toda la información necesaria para la pre-confirmación.
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

        // Si se encontró el horario, devuelve sus datos.
        // Si la consulta no encontró ningún registro, devuelve NULL.

        return $row ?: null;
    }
}
