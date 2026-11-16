<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\ProfesionalModel;
use App\Models\AdministradorModel;


/**
 * Admin
 * Her Beauty Center
 *
 * Controlador encargado de gestionar las secciones del panel
 * administrativo y profesional.
 *
 * Sus responsabilidades principales son:
 * 1. Controlar el acceso a las secciones según el rol del usuario.
 * 2. Obtener y mostrar información de profesionales, servicios y clientes.
 * 3. Consultar y filtrar turnos y horarios.
 * 4. Obtener los datos del perfil y sus redes sociales.
 * 5. Preparar la información que necesitan las vistas del panel.
 * 6. Cerrar la sesión del usuario.
 */
class Admin extends BaseController
{
    //Verifica sesión activa y rol de amin
    private function verificarSesionAdmin()
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return redirect()->to('/?login=1');
        }
        return null;
    }

    //----------------------
    // DASHBOARD
    //----------------------
    public function dashboard()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        return view('profesional/dashboard', [
            'usuario' => [
                's_nbre' => $session->get('usuario_nombre'),
                'u_correo' => $session->get('usuario_correo'),
                'u_avatar' => $session->get('usuario_avatar'),
            ],
            'rol' => $session->get('rol'),
            'p_id' => $session->get('p_id'),
            'activeNav' => 'dashboard', //opción de navegación: dashboard
        ]);
    }

    //----------------------
    // PROFESIONALES
    //----------------------
    public function profesionales()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect)
            return $redirect;

        $db = \Config\Database::connect();

        // Obtiene el servicio de paginación de CodeIgniter.
        $pager = \Config\Services::pager();

        // Lee el número de página solicitado mediante GET.
        // Si no se proporciona, comienza en la página 1.
        $page = (int) ($this->request->getGet('page') ?? 1);

        // Cantidad máxima de profesionales que se mostrarán por página.
        $perPage = 20;

        $search = $this->request->getGet('search');
        $estado = $this->request->getGet('estado');
        $servicioFiltro = $this->request->getGet('servicio');

        // Construye la consulta inicial:
        // parte de la tabla profesional y la relaciona con usuario.
        //
        // El JOIN permite obtener tanto los datos profesionales
        // como los datos personales de cada profesional.
        $query = $db->table('profesional')
            ->join('usuario', 'usuario.u_id = profesional.u_id');

        // Si se ingresó un texto de búsqueda,
        // busca coincidencias en el nombre completo del usuario.
        if (!empty($search)) {
            $query->like('usuario.u_nbreCompleto', $search);
        }

        //Filtro de estado
        if (!empty($estado)) {
            $val = $estado === 'activo' ? 1 : 0;
            $query->where('usuario.u_activo', $val);
        }

        //Filtro de Servicio
        if (!empty($servicioFiltro)) {
            $query->where("EXISTS (SELECT 1 FROM profesional_servicio ps WHERE ps.p_id = profesional.p_id AND ps.p_sId = " . intval($servicioFiltro) . ")", null, false);
        }

        //Cuenta cantidad de resultaos
        $total = $query->countAllResults(false);

        // Construye los resultados de los profesionales con sus servicios       
        $profesionales = $query
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel, usuario.u_avatar, usuario.u_activo, GROUP_CONCAT(servicio.s_nbre SEPARATOR ", ") as servicios_ofrecidos, GROUP_CONCAT(servicio.s_id SEPARATOR ",") as servicios_ids')
            ->join('profesional_servicio', 'profesional_servicio.p_id = profesional.p_id', 'left')
            ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId', 'left')
            ->groupBy('profesional.p_id')
            ->orderBy('profesional.p_orden', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        //Trae todos los servicios para el filtro
        $todos_servicios = $db->table('servicio')->select('s_id, s_nbre')->get()->getResultArray();

        // Genera los enlaces de navegación entre páginas.
        // Se utiliza la plantilla personalizada admin_pagination.
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');

        // Calcula el número del primer registro mostrado en la página.
        // Si no hay resultados, comienza en 0.
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;

        // Calcula el número del último registro mostrado.
        // min() evita superar la cantidad total de resultados.
        $end = min($page * $perPage, $total);

        // Envía los datos obtenidos a la vista.
        return view('profesional/profesionales', [
            'profesionales' => $profesionales,
            'todos_servicios' => $todos_servicios,
            'usuario' => ['s_nbre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'profesionales',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }


    //----------------------
    // LISTADO DE SERVICIOS
    //----------------------
    public function servicios()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect)
            return $redirect;

        $db = \Config\Database::connect();

        //Paginación
        $pager = \Config\Services::pager();
        //Página solicitada y cantidad de resultados por página.
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Recupera los filtros enviados mediante GET.
        $search = $this->request->getGet('search');
        $estado = $this->request->getGet('estado');
        $sort = $this->request->getGet('sort');

        $query = $db->table('servicio')
            ->select('servicio.*, (SELECT img_ruta FROM imagen WHERE imagen.img_sId = servicio.s_id ORDER BY imagen.img_orden ASC, imagen.img_id ASC LIMIT 1) as imagen_ruta, GROUP_CONCAT(DISTINCT profesional_servicio.p_id SEPARATOR ",") as profesionales_ids, GROUP_CONCAT(DISTINCT usuario.u_nbreCompleto SEPARATOR ", ") as profesionales_nombres')
            ->join('profesional_servicio', 'profesional_servicio.p_sId = servicio.s_id', 'left')
            ->join('profesional', 'profesional.p_id = profesional_servicio.p_id', 'left')
            ->join('usuario', 'usuario.u_id = profesional.u_id', 'left')
            ->groupBy('servicio.s_id');

        //Filtros de búsqueda  
        if (!empty($search)) {
            $query->like('servicio.s_nbre', $search);
        }

        //Filtro de estado
        if (!empty($estado)) {
            $val = $estado === 'activo' ? 1 : 0;
            $query->where('servicio.s_activo', $val);
        }

        //Filtro de orden
        if (!empty($sort)) {
            if ($sort === 'a-z')
                $query->orderBy('servicio.s_nbre', 'ASC');
            elseif ($sort === 'z-a')
                $query->orderBy('servicio.s_nbre', 'DESC');
            elseif ($sort === 'menor-tiempo')
                $query->orderBy('servicio.s_duracionMinutos', 'ASC');
            elseif ($sort === 'mayor-tiempo')
                $query->orderBy('servicio.s_duracionMinutos', 'DESC');
        } else {
            $query->orderBy('servicio.s_orden', 'ASC');
        }

        // Cuenta los servicios que cumplen los filtros,
        // conservando la consulta para recuperar luego los registros.
        $total = $query->countAllResults(false);

        // Limita la consulta a los registros de la página actual
        // y obtiene los resultados como un array.
        $servicios = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $todos_profesionales = $db->table('profesional')
            ->select('profesional.p_id, usuario.u_nbreCompleto')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->get()->getResultArray();

        // Construye los enlaces de paginación.
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        // Calcula el primer y último registro de la página actual.
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        // Envía los servicios, profesionales y datos de paginación
        // a la vista encargada de mostrar el catálogo administrativo.
        return view('profesional/servicios', [
            'servicios' => $servicios,
            'todos_profesionales' => $todos_profesionales,
            'usuario' => ['s_nbre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'servicios',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }



    //----------------------
    // LISTADO DE TURNOS
    //----------------------
    public function turnos()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();

        // Configura la paginación.
        $pager = \Config\Services::pager();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Filtros
        $filtroEstado = trim($this->request->getGet('estado') ?? '');
        $periodo = trim($this->request->getGet('periodo') ?? '');
        $servicioFiltro = (int) ($this->request->getGet('servicio') ?? 0);

        // Obtener p_id del usuario en sesión
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();
        $pId = $profesional ? (int) $profesional['p_id'] : null;

        // Inicializa el listado de servicios que aparecerá
        // en el selector de filtros.
        $servicios_profesional = [];
        if ($profesional) {
            $servicios_profesional = $db->table('profesional_servicio')
                ->select('servicio.s_id, servicio.s_nbre')
                ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
                ->where('profesional_servicio.p_id', $profesional['p_id'])
                ->where('servicio.s_activo', 1)
                ->orderBy('servicio.s_nbre', 'ASC')
                ->get()->getResultArray();
        }
        //Si no tiene servicios asociados
        if (empty($servicios_profesional)) {
            $servicios_profesional = $db->table('servicio')
                ->select('s_id, s_nbre')
                ->where('s_activo', 1)
                ->orderBy('s_nbre', 'ASC')
                ->get()->getResultArray();
        }

        // Construye la consulta principal para obtener los turnos.
        // Si el turno tiene horas propias, utiliza esas hora
        // Si no, utiliza las horas del horario original.
        $query = $db->table('turno')
            ->select('turno.t_id, turno.t_pId, turno.t_cId, turno.t_hId,
                      turno.estado, turno.abonado, turno.monto,
                      horario.h_fecha AS fecha,
                      COALESCE(turno.t_horaDesde, horario.h_horaDesde) AS t_horaDesde,
                      COALESCE(turno.t_horaHasta, horario.h_horaHasta) AS t_horaHasta,
                      servicio.s_duracionMinutos AS duracion_estimada,
                      TIMESTAMPDIFF(MINUTE, COALESCE(turno.t_horaDesde, horario.h_horaDesde), COALESCE(turno.t_horaHasta, horario.h_horaHasta)) AS duracion_real,
                      servicio.s_nbre AS nombre_servicio,
                      u_prof.u_nbreCompleto AS nombre_profesional,
                      u_cli.u_nbreCompleto AS nombre_cliente')
            ->join('horario', 'horario.h_id   = turno.t_hId')
            ->join('servicio', 'servicio.s_id  = horario.h_sId', 'left')
            ->join('profesional', 'profesional.p_id = turno.t_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->join('cliente', 'cliente.c_id   = turno.t_cId', 'left')
            ->join('usuario AS u_cli', 'u_cli.u_id = cliente.c_uId', 'left');

        // Filtrar turnos que le pertenecen a la profesional
        if ($pId)
            $query->where('turno.t_pId', $pId);

        //Filtro de estado
        if (!empty($filtroEstado)) {
            $query->where('turno.estado', $filtroEstado);
        }

        //Filtro ded servicio
        if ($servicioFiltro > 0) {
            $query->where('horario.h_sId', $servicioFiltro);
        }

        //Filtro de período
        if ($periodo === 'hoy') {//Día
            $query->where('horario.h_fecha', date('Y-m-d'));
        } elseif ($periodo === 'semana') { //Semana
            $lunesSemana = date('Y-m-d', strtotime('monday this week'));
            $domingoSemana = date('Y-m-d', strtotime('sunday this week'));
            $query->where('horario.h_fecha >=', $lunesSemana)
                ->where('horario.h_fecha <=', $domingoSemana);
        } elseif ($periodo === 'mes') {//Mes
            $inicioMes = date('Y-m-01');
            $finMes = date('Y-m-t');
            $query->where('horario.h_fecha >=', $inicioMes)
                ->where('horario.h_fecha <=', $finMes);
        }

        // Cuenta los turnos que cumplen los filtros aplicados
        $total = $query->countAllResults(false);

        // Ordena los turnos por fecha descendente
        // y por hora de inicio ascendente.
        // Después aplica la paginación y ejecuta la consulta.
        $turnos = $query->orderBy('horario.h_fecha', 'DESC')
            ->orderBy('horario.h_horaDesde', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        // Clientes activos para el modal de nuevo turno
        $clientes = $db->table('cliente')
            ->select('cliente.c_id, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')
            ->where('usuario.u_activo', 1)
            ->orderBy('usuario.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        // Si existe un profesional asociado al usuario,
        // obtiene los servicios que tiene habilitados.
        $servicios_modal = [];
        if ($profesional) {
            $servicios_modal = $db->table('profesional_servicio')
                ->select('servicio.s_id, servicio.s_nbre, servicio.s_duracionMinutos, servicio.s_precio')
                ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
                ->where('profesional_servicio.p_id', $profesional['p_id'])
                ->where('servicio.s_activo', 1)
                ->orderBy('servicio.s_nbre', 'ASC')
                ->get()->getResultArray();
        }
        // Si no hay servicios asociados, obtiene todos los servicios activos.
        if (empty($servicios_modal)) {
            $servicios_modal = $db->table('servicio')
                ->select('s_id, s_nbre, s_duracionMinutos, s_precio')
                ->where('s_activo', 1)
                ->orderBy('s_nbre', 'ASC')
                ->get()->getResultArray();
        }

        // Genera los enlaces para navegar entre las páginas de resultados.
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');

        // Calcula los números del primer y último turno de la página actual.
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        // Envía toda la información a la vista de turnos.
        return view('profesional/turnos', [
            'turnos' => $turnos,
            'usuario' => ['s_nbre' => $session->get('usuario_nombre')],
            'rol' => $session->get('rol'),
            'activeNav' => 'turnos',
            'filtroEstado' => $filtroEstado,
            'periodo' => $periodo,
            'servicioFiltro' => $servicioFiltro,
            'servicios_profesional' => $servicios_profesional,
            'servicios_modal' => $servicios_modal,
            'clientes' => $clientes,
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total,
        ]);
    }


    //----------------------
    // PERFIL
    //----------------------

    public function perfil()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();
        //Obtiene lod datos grales del user autenticado
        $usuario = $db->table('usuario')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        // Busca el registro profesional asociado a esa misma cuenta.
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        // Inicializa el listado de redes sociales.
        $redes = [];
        // Si existe un perfil profesional,
        // obtiene todas las redes sociales asociadas a él.
        if ($profesional) {
            $redes = $db->table('red_social')
                ->where('rs_pId', $profesional['p_id'])
                ->get()->getResultArray();
        }

        // Envía a la vista los datos generales, profesionales
        // y las redes sociales correspondientes.
        return view('profesional/perfil', [
            'usuario_data' => $usuario,
            'profesional_data' => $profesional,
            'redes_data' => $redes,
            'usuario' => [
                's_nbre' => $session->get('usuario_nombre'),
                'u_correo' => $session->get('usuario_correo'),
                'u_avatar' => $session->get('usuario_avatar') ?? ($usuario['u_avatar'] ?? null),
            ],
            'rol' => $session->get('rol'),
            'activeNav' => 'perfil'
        ]);
    }


    //----------------------
    // HORARIOS
    //----------------------
    public function horarios()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();

        // Configura la paginación
        $pager = \Config\Services::pager();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        //Filtros
        $search = trim($this->request->getGet('search') ?? '');
        $periodo = trim($this->request->getGet('periodo') ?? '');
        $servicioFiltro = (int) ($this->request->getGet('servicio') ?? 0);

        // Busca el registro profesional vinculado al usuario autenticado.
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        // Inicializa las variables que contendrán los resultados.
        // Si no existe un profesional asociado, permanecerán vacías.
        $horarios = [];
        $servicios_profesional = [];
        $total = 0;

        //SI ES PROFESIONAL
        if ($profesional) {

            // Servicios ofrecidos por la profesional para el SELECT de filtros
            $servicios_profesional = $db->table('profesional_servicio')
                ->select('servicio.s_id, servicio.s_nbre')
                ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
                ->where('profesional_servicio.p_id', $profesional['p_id'])
                ->where('servicio.s_activo', 1)
                ->orderBy('servicio.s_nbre', 'ASC')
                ->get()->getResultArray();

            // Construye la consulta de horarios del profesional.
            $query = $db->table('horario')
                ->select('horario.*, servicio.s_nbre AS nombre_servicio')
                ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
                ->where('horario.h_pId', $profesional['p_id']);

            // SÓLO LAS DISPONIBLES: Si alguien la solicitó, confirmó o realizó, aparece en Mis Turnos
            $query->where("NOT EXISTS (
                SELECT 1 FROM turno 
                WHERE turno.t_hId = horario.h_id 
                  AND turno.estado IN ('Solicitado', 'Confirmado', 'Realizado')
            )", null, false);

            // Búsqueda por texto (nombre de servicio o fecha)
            if (!empty($search)) {
                $query->groupStart()
                    ->like('servicio.s_nbre', $search)
                    ->orLike('horario.h_fecha', $search)
                    ->groupEnd();
            }

            // Filtro por servicio que ofrece
            if ($servicioFiltro > 0) {
                $query->where('horario.h_sId', $servicioFiltro);
            }

            // Filtro por período temporal: 'hoy', 'semana', 'mes'
            $hoy = date('Y-m-d');
            if ($periodo === 'hoy') { //Día
                $query->where('horario.h_fecha', $hoy);
            } elseif ($periodo === 'semana') { //Semana
                $lunesSemana = date('Y-m-d', strtotime('monday this week'));
                $domingoSemana = date('Y-m-d', strtotime('sunday this week'));
                $query->where('horario.h_fecha >=', $lunesSemana)
                    ->where('horario.h_fecha <=', $domingoSemana);
            } elseif ($periodo === 'mes') { //Mes
                $inicioMes = date('Y-m-01');
                $finMes = date('Y-m-t');
                $query->where('horario.h_fecha >=', $inicioMes)
                    ->where('horario.h_fecha <=', $finMes);
            }

            //Cuenta los horarios que cumplen los filtros
            $total = $query->countAllResults(false);

            // Ordena los horarios por fecha y hora de inicio ascendente,
            // aplica la paginación y ejecuta la consulta.
            $horarios = $query->orderBy('horario.h_fecha', 'ASC')
                ->orderBy('horario.h_horaDesde', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()->getResultArray();
        }

        // Genera los enlaces para navegar entre las páginas.
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');

        // Calcula el primer y último registro de la página actual.
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        // Envía a la vista los horarios, servicios y filtros seleccionados.
        return view('profesional/horarios', [
            'horarios' => $horarios,
            'profesional' => $profesional,
            'servicios_profesional' => $servicios_profesional,
            'usuario' => ['s_nbre' => $session->get('usuario_nombre')],
            'rol' => $session->get('rol'),
            'activeNav' => 'horarios',
            'search' => $search,
            'periodo' => $periodo,
            'servicioFiltro' => $servicioFiltro,
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total,
        ]);
    }

    //----------------------
    // CALENDARIO
    //----------------------
    public function calendario()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        // Servicios propios para el formulario de disponibilidad
        $servicios = [];
        if ($profesional) {
            $servicios = $db->table('profesional_servicio')
                ->select('servicio.s_id, servicio.s_nbre, servicio.s_duracionMinutos, servicio.s_precio')
                ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
                ->where('profesional_servicio.p_id', $profesional['p_id'])
                ->where('servicio.s_activo', 1)
                ->orderBy('servicio.s_nbre', 'ASC')
                ->get()->getResultArray();
        }

        // Clientes activos para el modal de nuevo turno
        $clientes = $db->table('cliente')
            ->select('cliente.c_id, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')
            ->where('usuario.u_activo', 1)
            ->orderBy('usuario.u_nbreCompleto', 'ASC')
            ->get()->getResultArray();

        // Carga la vista del calendario y le entrega los datos
        return view('profesional/calendario', [
            'profesional' => $profesional,
            'servicios' => $servicios,
            'clientes' => $clientes,
            'usuario' => ['s_nbre' => $session->get('usuario_nombre')],
            'rol' => $session->get('rol'),
            'activeNav' => 'calendario',
        ]);
    }


    //----------------------
    // CLIENTES
    //----------------------
    public function clientes()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect)
            return $redirect;

        $db = \Config\Database::connect();

        // Configura la paginación.
        $pager = \Config\Services::pager();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Recupera los filtros enviados por GET.
        $search = $this->request->getGet('search');
        $sort = $this->request->getGet('sort');

        // Construye la consulta para obtener los clientes.
        $query = $db->table('cliente')
            ->select('cliente.*, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel, usuario.u_avatar, usuario.u_activo, usuario.u_fRegistro, usuario.u_id')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')
            ->join('profesional', 'profesional.u_id = cliente.c_uId', 'left')
            ->where('profesional.p_id IS NULL');

        //Filtro por nombre
        if (!empty($search)) {
            $query->like('usuario.u_nbreCompleto', $search);
        }

        // Filtro por ordenamiento        
        if (!empty($sort)) {
            if ($sort === 'a-z')
                $query->orderBy('usuario.u_nbreCompleto', 'ASC');
            elseif ($sort === 'z-a')
                $query->orderBy('usuario.u_nbreCompleto', 'DESC');
            elseif ($sort === 'nuevos')
                $query->orderBy('usuario.u_fRegistro', 'DESC');
            elseif ($sort === 'viejos')
                $query->orderBy('usuario.u_fRegistro', 'ASC');
        } else {
            $query->orderBy('usuario.u_fRegistro', 'DESC');
        }

        // Cuenta los clientes que cumplen las condiciones
        $total = $query->countAllResults(false);
        // Aplica la paginación y obtiene los clientes.
        $clientes = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        // Genera los enlaces de paginación.
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');

        // Calcula el primer y último registro de la página actual.
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        // Envía los resultados y los datos de navegación a la vista.
        return view('profesional/clientes', [
            'clientes' => $clientes,
            'usuario' => ['s_nbre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'clientes',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }


    //----------------------
    // CIERRE DE SESIÒN
    //----------------------

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url(''));
    }
}
