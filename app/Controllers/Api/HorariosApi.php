<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\HorarioModel;
use CodeIgniter\API\ResponseTrait;

/**
 * HorariosApi
 * Her Beauty Center — Incremento N° 2 (CU08 - Gestionar Disponibilidad)
 *
 * Administra los bloques de disponibilidad de los profesionales.
 *
 * Sus responsabilidades principales son:
 * 1. Verificar la sesión y los roles autorizados para acceder a los endpoints.
 * 2. Obtener el perfil profesional asociado al usuario autenticado.
 * 3. Consultar los eventos del calendario para mostrarlos en FullCalendar.
 * 4. Crear, editar y eliminar bloques de disponibilidad.
 * 5. Consultar los servicios asignados a una profesional.
 * 6. Validar fechas, pertenencia de servicios y superposición de horarios.
 *
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
        // Obtiene la sesión actual.
        $session = session();

        // Recupera el identificador del usuario y su rol almacenados en sesión.
        $uid = $session->get('usuario_id');
        $rol = $session->get('rol');

        if (!$uid || !in_array($rol, ['admin', 'profesional'], true)) {
            return null;
        }

        // Devuelve los datos necesarios para identificar al usuario
        // y determinar qué permisos corresponden según su rol.
        return ['uid' => $uid, 'rol' => $rol];
    }

    // -------------------------------------------------------
    // Helper: obtener p_id de la sesión activa
    // -------------------------------------------------------
    private function getProfesionalId(int $uid): ?int
    {
        // Busca el identificador del perfil profesional asociado
        // al usuario autenticado.
        $db = \Config\Database::connect();
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
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional asociado al usuario de la sesión.
        $pId = $this->getProfesionalId($auth['uid']);

        // Si el usuario no tiene un perfil profesional, no puede consultar
        // los eventos de un calendario profesional.
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Recupera el rango de fechas enviado por FullCalendar mediante GET.
        // Si no se reciben fechas, utiliza el primer y último día del mes actual.
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end = $this->request->getGet('end') ?? date('Y-m-t');

        // Comprueba que ambas fechas tengan un formato válido Y-m-d.
        // Si alguna no es válida, utiliza el período del mes actual como alternativa.
        if (!$this->esDateValida($start))
            $start = date('Y-m-01');
        if (!$this->esDateValida($end))
            $end = date('Y-m-t');

        // Consulta los eventos correspondientes a la profesional y al período.
        // El modelo se encarga de obtener los datos relacionados con horarios,
        // servicios y turnos.
        $model = new HorarioModel();
        $filas = $model->getEventosCalendario($pId, $start, $end);
        $eventos = [];

        // Recorre cada registro obtenido de la base de datos.
        foreach ($filas as $f) {

            // Estado del turno asociado al bloque.
            // Si es null, significa que el horario todavía no tiene un turno.
            $estado = $f['estado'] ?? null;

            // Obtiene el nombre del servicio.
            // Si no está disponible, utiliza "Disponibilidad" como título.
            $titulo = $f['nombre_servicio'] ?? 'Disponibilidad';

            // Determina el color del evento según el estado del turno.
            $color = $this->colorPorEstado($estado);

            // Construye el objeto que FullCalendar utilizará para representar
            // cada bloque en el calendario.
            $eventos[] = [
                'id' => 'h_' . $f['h_id'],
                'h_id' => (int) $f['h_id'],
                't_id' => $f['t_id'] ? (int) $f['t_id'] : null,
                'title' => $titulo,
                'start' => $f['h_fecha'] . 'T' . $f['h_horaDesde'],
                'end' => $f['h_fecha'] . 'T' . $f['h_horaHasta'],
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'tipo' => $estado ? 'turno' : 'disponibilidad',
                    'estado' => $estado ?? 'Disponible',
                    'nombre_servicio' => $titulo,
                    'servicio_id' => $f['servicio_id'] ?? null,
                    'nombre_cliente' => $f['nombre_cliente'] ?? null,
                    'monto' => $f['monto'] ?? 0,
                    'abonado' => (bool) ($f['abonado'] ?? false),
                ],
            ];
        }

        // Devuelve todos los eventos en formato JSON.
        return $this->respond(['eventos' => $eventos]);
    }

    // -------------------------------------------------------
    // POST /api/horarios/crear
    // -------------------------------------------------------
    public function crear(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Verifica que el usuario tenga una sesión válida y un rol autorizado.
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        $db = \Config\Database::connect();

        // Obtiene los datos enviados mediante POST o JSON.
        $data = $this->getRequestData();

        // Recupera el identificador del servicio.
        $sId = (int) ($data['h_sId'] ?? $data['s_id'] ?? $data['servicio_id'] ?? 0);

        // Recupera la fecha y las horas del bloque.
        $fecha = trim($data['h_fecha'] ?? $data['fecha'] ?? '');
        $desde = trim($data['h_horaDesde'] ?? $data['hora_desde'] ?? $data['desde'] ?? '');
        $hasta = trim($data['h_horaHasta'] ?? $data['hora_hasta'] ?? $data['hasta'] ?? '');

        // Si se envió un p_id, utiliza ese profesional.
        // De lo contrario, intenta obtener el perfil profesional
        // asociado al usuario que inició sesión.
        $pId = !empty($data['p_id']) ? (int) $data['p_id'] : $this->getProfesionalId($auth['uid']);

        // Si el usuario es administrador y todavía no se obtuvo un p_id,
        // se intenta determinar qué profesional utilizar.
        if (!$pId && $auth['rol'] === 'admin') {

            // Primero busca un profesional que tenga asignado el servicio recibido.
            if ($sId) {
                $pRow = $db->table('profesional_servicio')->where('p_sId', $sId)->get()->getRowArray();
                if ($pRow)
                    $pId = (int) $pRow['p_id'];
            }

            // Si no se encontró un profesional asociado al servicio,
            // utiliza el primer registro de la tabla profesional.
            if (!$pId) {
                $pRow = $db->table('profesional')->get()->getRowArray();
                if ($pRow)
                    $pId = (int) $pRow['p_id'];
            }
        }

        // Si no fue posible obtener un perfil profesional, se interrumpe la operación.
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

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

        // Instancia el modelo encargado de gestionar los horarios.
        $model = new HorarioModel();

        // -- Escenario 1: Superposición de horarios (RN11) --
        if ($model->existeSuperposicion($pId, $sId, $fecha, $desde, $hasta)) {
            return $this->fail('Superposición de horarios: ya tenés un bloque registrado en esa franja para este servicio.');
        }

        // -- Escenario 3: Turno activo en ese rango --
        if ($model->existeTurnoEnRango($pId, $sId, $fecha, $desde, $hasta)) {
            return $this->fail('No es posible crear la disponibilidad: ya tienes un turno reservado en esa franja horaria para este servicio.');
        }

        // Inserta el nuevo bloque de disponibilidad.
        // El modelo se encarga de interactuar con la tabla correspondiente.
        $hId = $model->insert([
            'h_pId' => $pId,
            'h_sId' => $sId,
            'h_fecha' => $fecha,
            'h_horaDesde' => $desde,
            'h_horaHasta' => $hasta,
        ]);

        // Si la inserción falla, devuelve un error del servidor.
        if (!$hId) {
            return $this->failServerError('Error al guardar la disponibilidad.');
        }

        // Informa que el recurso fue creado correctamente.
        // También devuelve el identificador del nuevo horario.
        return $this->respondCreated([
            'success' => true,
            'message' => 'Disponibilidad registrada exitosamente.',
            'h_id' => $hId,
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/horarios/editar/:id
    // -------------------------------------------------------
    public function editar(int $hId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional asociado al usuario autenticado.
        $pId = $this->getProfesionalId($auth['uid']);

        // Si es administrador y no tiene un perfil profesional propio,
        // obtiene el profesional al que pertenece el horario solicitado.
        if (!$pId && $auth['rol'] === 'admin') {
            $hRow = \Config\Database::connect()->table('horario')->where('h_id', $hId)->get()->getRowArray();
            if ($hRow)
                $pId = (int) $hRow['h_pId'];
        }

        // Si no se pudo determinar un perfil profesional, se interrumpe la operación.
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Instancia el modelo y busca el horario por su clave primaria.
        $model = new HorarioModel();
        $horario = $model->find($hId);

        // Comprueba que el horario exista y que pertenezca al profesional
        // de la sesión. Los administradores quedan exceptuados de esta
        // comprobación de pertenencia.
        if (!$horario || ((int) $horario['h_pId'] !== $pId && $auth['rol'] !== 'admin')) {
            return $this->failNotFound('Disponibilidad no encontrada o sin permiso.');
        }

        // -- RN11: No modificar si tiene turno activo --
        if ($model->tieneTurnoActivo($hId)) {
            return $this->fail('No se puede modificar este bloque: tiene un turno activo (Solicitado o Confirmado) vinculado.');
        }

        // Obtiene los datos enviados para la modificación.
        $data = $this->getRequestData();

        // Para cada campo se utiliza el valor recibido.
        // Si no se envió, se conserva el valor que ya tenía el horario.
        $sId = (int) ($data['h_sId'] ?? $data['s_id'] ?? $data['servicio_id'] ?? $horario['h_sId']);
        $fecha = trim($data['h_fecha'] ?? $data['fecha'] ?? $horario['h_fecha']);
        $desde = trim($data['h_horaDesde'] ?? $data['hora_desde'] ?? $data['desde'] ?? $horario['h_horaDesde']);
        $hasta = trim($data['h_horaHasta'] ?? $data['hora_hasta'] ?? $data['hasta'] ?? $horario['h_horaHasta']);

        // Comprueba que la hora de finalización sea posterior a la de inicio.
        if ($hasta <= $desde) {
            return $this->fail('La hora de fin debe ser posterior a la hora de inicio.');
        }

        // Verifica que el nuevo rango no se superponga con otro horario.
        if ($model->existeSuperposicion($pId, $sId, $fecha, $desde, $hasta, $hId)) {
            return $this->fail('Superposición de horarios: ya existe un bloque en esa franja para este servicio.');
        }

        // Actualiza el servicio, la fecha y las horas del bloque.
        $model->update($hId, [
            'h_sId' => $sId,
            'h_fecha' => $fecha,
            'h_horaDesde' => $desde,
            'h_horaHasta' => $hasta,
        ]);

        // Devuelve éxito
        return $this->respond(['success' => true, 'message' => 'Disponibilidad actualizada correctamente.']);
    }

    // -------------------------------------------------------
    // DELETE /api/horarios/eliminar/:id
    // -------------------------------------------------------
    public function eliminar(int $hId): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional de la sesión.
        $pId = $this->getProfesionalId($auth['uid']);

        // Si no existe un perfil profesional, no se puede continuar.
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        // Busca el horario que se desea eliminar.
        $model = new HorarioModel();
        $horario = $model->find($hId);

        // Comprueba que el horario exista y pertenezca al profesional autenticado.
        if (!$horario || (int) $horario['h_pId'] !== $pId) {
            return $this->failNotFound('Disponibilidad no encontrada o sin permiso.');
        }

        // -- RN11: No eliminar si tiene turno activo --
        if ($model->tieneTurnoActivo($hId)) {
            return $this->fail('No se puede eliminar este bloque: tiene un turno activo (Solicitado o Confirmado) vinculado.');
        }

        // Elimina el registro mediante el método delete() del modelo.
        $model->delete($hId);

        // Devuelve éxito.
        return $this->respondDeleted(['success' => true, 'message' => 'Disponibilidad eliminada correctamente.']);
    }

    // -------------------------------------------------------
    // GET /api/horarios/servicios
    // Lista servicios asignados a la profesional en sesión
    // -------------------------------------------------------
    public function serviciosProfesional(): \CodeIgniter\HTTP\ResponseInterface
    {
        $auth = $this->verificarSesion();
        if (!$auth)
            return $this->failUnauthorized('Acceso denegado.');

        // Obtiene el perfil profesional asociado al usuario.
        $pId = $this->getProfesionalId($auth['uid']);
        if (!$pId)
            return $this->failNotFound('No existe un perfil profesional asociado.');

        $db = \Config\Database::connect();

        // Consulta la tabla intermedia profesional_servicio y la relaciona
        // con servicio para recuperar los datos de cada servicio asignado.
        $servicios = $db->table('profesional_servicio')
            ->select('servicio.s_id, servicio.s_nbre, servicio.s_duracionMinutos, servicio.s_precio')
            ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
            ->where('profesional_servicio.p_id', $pId)
            ->where('servicio.s_activo', 1)
            ->orderBy('servicio.s_nbre', 'ASC')
            ->get()->getResultArray();

        // Devuelve el listado de servicios en formato JSON.
        return $this->respond(['servicios' => $servicios]);
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------
    private function esDateValida(string $date): bool
    {
        // Intenta interpretar la fecha con el formato año-mes-día.
        $d = \DateTime::createFromFormat('Y-m-d', $date);

        // Comprueba que la conversión haya sido posible y que,
        // al volver a formatearla, coincida exactamente con el valor recibido.
        return $d && $d->format('Y-m-d') === $date;
    }

    private function servicioEsDeProfesional(int $pId, int $sId): bool
    {
        $db = \Config\Database::connect();

        // Busca una asignación que coincida con ambos identificadores.
        // countAllResults() devuelve la cantidad de registros encontrados.
        // El resultado es mayor que cero si existe la relación.
        return $db->table('profesional_servicio')
            ->where('p_id', $pId)->where('p_sId', $sId)
            ->countAllResults() > 0;
    }

    private function colorPorEstado(?string $estado): string
    {
        // match compara el estado recibido con cada alternativa
        // y devuelve el color correspondiente.
        return match ($estado) {
            'Solicitado' => '#d97706',  // amber
            'Confirmado' => '#059669',  // emerald
            'Realizado' => '#6b7280',  // slate
            'Cancelado' => '#dc2626',  // red
            default => '#7d562d',  // brand brown (disponible)
        };
    }
}
