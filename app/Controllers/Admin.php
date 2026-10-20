<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\ProfesionalModel;
use App\Models\AdministradorModel;

class Admin extends BaseController
{
    private function verificarSesionAdmin()
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return redirect()->to('/?login=1');
        }
        return null;
    }

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
            'activeNav' => 'dashboard',
        ]);
    }

    public function profesionales()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        $search = $this->request->getGet('search');
        $estado = $this->request->getGet('estado');
        $servicioFiltro = $this->request->getGet('servicio');

        $query = $db->table('profesional')
            ->join('usuario', 'usuario.u_id = profesional.u_id');

        if (!empty($search)) {
            $query->like('usuario.u_nbreCompleto', $search);
        }
        if (!empty($estado)) {
            $val = $estado === 'activo' ? 1 : 0;
            $query->where('usuario.u_activo', $val);
        }
        if (!empty($servicioFiltro)) {
            $query->where("EXISTS (SELECT 1 FROM profesional_servicio ps WHERE ps.p_id = profesional.p_id AND ps.p_sId = " . intval($servicioFiltro) . ")", null, false);
        }

        $total = $query->countAllResults(false);

        $profesionales = $query
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel, usuario.u_avatar, usuario.u_activo, GROUP_CONCAT(servicio.s_nbre SEPARATOR ", ") as servicios_ofrecidos, GROUP_CONCAT(servicio.s_id SEPARATOR ",") as servicios_ids')
            ->join('profesional_servicio', 'profesional_servicio.p_id = profesional.p_id', 'left')
            ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId', 'left')
            ->groupBy('profesional.p_id')
            ->orderBy('profesional.p_orden', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        $todos_servicios = $db->table('servicio')->select('s_id, s_nbre')->get()->getResultArray();
        
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

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

    public function servicios()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        $search = $this->request->getGet('search');
        $estado = $this->request->getGet('estado');
        $sort = $this->request->getGet('sort');

        $query = $db->table('servicio')
            ->select('servicio.*, imagen.img_ruta as imagen_ruta, GROUP_CONCAT(DISTINCT profesional_servicio.p_id SEPARATOR ",") as profesionales_ids, GROUP_CONCAT(DISTINCT usuario.u_nbreCompleto SEPARATOR ", ") as profesionales_nombres')
            ->join('imagen', 'imagen.img_sId = servicio.s_id', 'left')
            ->join('profesional_servicio', 'profesional_servicio.p_sId = servicio.s_id', 'left')
            ->join('profesional', 'profesional.p_id = profesional_servicio.p_id', 'left')
            ->join('usuario', 'usuario.u_id = profesional.u_id', 'left')
            ->groupBy('servicio.s_id');

        if (!empty($search)) {
            $query->like('servicio.s_nbre', $search);
        }
        if (!empty($estado)) {
            $val = $estado === 'activo' ? 1 : 0;
            $query->where('servicio.s_activo', $val);
        }
        if (!empty($sort)) {
            if ($sort === 'a-z') $query->orderBy('servicio.s_nbre', 'ASC');
            elseif ($sort === 'z-a') $query->orderBy('servicio.s_nbre', 'DESC');
            elseif ($sort === 'menor-tiempo') $query->orderBy('servicio.s_duracionMinutos', 'ASC');
            elseif ($sort === 'mayor-tiempo') $query->orderBy('servicio.s_duracionMinutos', 'DESC');
        } else {
            $query->orderBy('servicio.s_orden', 'ASC');
        }

        $total = $query->countAllResults(false);
        $servicios = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $todos_profesionales = $db->table('profesional')
            ->select('profesional.p_id, usuario.u_nbreCompleto')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

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

    public function turnos()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();

        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Filtros
        $filtroEstado   = trim($this->request->getGet('estado') ?? '');
        $periodo        = trim($this->request->getGet('periodo') ?? '');
        $servicioFiltro = (int) ($this->request->getGet('servicio') ?? 0);

        // Obtener p_id del usuario en sesión
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();
        $pId = $profesional ? (int) $profesional['p_id'] : null;

        // Servicios ofrecidos por la profesional para el dropdown de filtros
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
        if (empty($servicios_profesional)) {
            $servicios_profesional = $db->table('servicio')
                ->select('s_id, s_nbre')
                ->where('s_activo', 1)
                ->orderBy('s_nbre', 'ASC')
                ->get()->getResultArray();
        }

        $query = $db->table('turno')
            ->select('turno.t_id, turno.t_pId, turno.t_cId, turno.t_hId,
                      turno.estado, turno.abonado, turno.monto,
                      horario.h_fecha AS fecha, horario.h_horaDesde AS t_horaDesde, horario.h_horaHasta AS t_horaHasta,
                      servicio.s_nbre AS nombre_servicio,
                      u_prof.u_nbreCompleto AS nombre_profesional,
                      u_cli.u_nbreCompleto AS nombre_cliente')
            ->join('horario',   'horario.h_id   = turno.t_hId')
            ->join('servicio',  'servicio.s_id  = horario.h_sId',  'left')
            ->join('profesional','profesional.p_id = turno.t_pId')
            ->join('usuario AS u_prof', 'u_prof.u_id = profesional.u_id')
            ->join('cliente',   'cliente.c_id   = turno.t_cId',    'left')
            ->join('usuario AS u_cli', 'u_cli.u_id = cliente.c_uId', 'left');

        // Filtrar por profesional propia (RN14)
        if ($pId) $query->where('turno.t_pId', $pId);

        if (!empty($filtroEstado)) {
            $query->where('turno.estado', $filtroEstado);
        }
        if ($servicioFiltro > 0) {
            $query->where('horario.h_sId', $servicioFiltro);
        }
        if ($periodo === 'hoy') {
            $query->where('horario.h_fecha', date('Y-m-d'));
        } elseif ($periodo === 'semana') {
            $lunesSemana   = date('Y-m-d', strtotime('monday this week'));
            $domingoSemana = date('Y-m-d', strtotime('sunday this week'));
            $query->where('horario.h_fecha >=', $lunesSemana)
                  ->where('horario.h_fecha <=', $domingoSemana);
        } elseif ($periodo === 'mes') {
            $inicioMes = date('Y-m-01');
            $finMes    = date('Y-m-t');
            $query->where('horario.h_fecha >=', $inicioMes)
                  ->where('horario.h_fecha <=', $finMes);
        }

        $total = $query->countAllResults(false);
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

        // Servicios con detalles (duración y precio) para el modal de nuevo turno
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
        if (empty($servicios_modal)) {
            $servicios_modal = $db->table('servicio')
                ->select('s_id, s_nbre, s_duracionMinutos, s_precio')
                ->where('s_activo', 1)
                ->orderBy('s_nbre', 'ASC')
                ->get()->getResultArray();
        }

        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end   = min($page * $perPage, $total);

        return view('profesional/turnos', [
            'turnos'                => $turnos,
            'usuario'               => ['s_nbre' => $session->get('usuario_nombre')],
            'rol'                   => $session->get('rol'),
            'activeNav'             => 'turnos',
            'filtroEstado'          => $filtroEstado,
            'periodo'               => $periodo,
            'servicioFiltro'        => $servicioFiltro,
            'servicios_profesional' => $servicios_profesional,
            'servicios_modal'       => $servicios_modal,
            'clientes'              => $clientes,
            'pager_links'           => $pager_links,
            'pager_start'           => $start,
            'pager_end'             => $end,
            'pager_total'           => $total,
        ]);
    }

    public function perfil()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();
            
        $redes = [];
        if ($profesional) {
            $redes = $db->table('red_social')
                ->where('rs_pId', $profesional['p_id'])
                ->get()->getResultArray();
        }

        return view('profesional/perfil', [
            'usuario_data' => $usuario,
            'profesional_data' => $profesional,
            'redes_data' => $redes,
            'usuario' => ['s_nbre' => $session->get('usuario_nombre')],
            'rol' => $session->get('rol'),
            'activeNav' => 'perfil'
        ]);
    }

    public function horarios()
    {
        $session = session();
        if (!$session->get('usuario_id') || !in_array($session->get('rol'), ['admin', 'profesional'])) {
            return redirect()->to('/?login=1');
        }

        $db = \Config\Database::connect();

        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        $search         = trim($this->request->getGet('search') ?? '');
        $periodo        = trim($this->request->getGet('periodo') ?? '');
        $servicioFiltro = (int) ($this->request->getGet('servicio') ?? 0);

        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        $horarios = [];
        $servicios_profesional = [];
        $total    = 0;

        if ($profesional) {
            // Servicios ofrecidos por la profesional para el dropdown de filtros
            $servicios_profesional = $db->table('profesional_servicio')
                ->select('servicio.s_id, servicio.s_nbre')
                ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId')
                ->where('profesional_servicio.p_id', $profesional['p_id'])
                ->where('servicio.s_activo', 1)
                ->orderBy('servicio.s_nbre', 'ASC')
                ->get()->getResultArray();

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
            if ($periodo === 'hoy') {
                $query->where('horario.h_fecha', $hoy);
            } elseif ($periodo === 'semana') {
                $lunesSemana   = date('Y-m-d', strtotime('monday this week'));
                $domingoSemana = date('Y-m-d', strtotime('sunday this week'));
                $query->where('horario.h_fecha >=', $lunesSemana)
                      ->where('horario.h_fecha <=', $domingoSemana);
            } elseif ($periodo === 'mes') {
                $inicioMes = date('Y-m-01');
                $finMes    = date('Y-m-t');
                $query->where('horario.h_fecha >=', $inicioMes)
                      ->where('horario.h_fecha <=', $finMes);
            }

            $total    = $query->countAllResults(false);
            $horarios = $query->orderBy('horario.h_fecha', 'ASC')
                ->orderBy('horario.h_horaDesde', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()->getResultArray();
        }

        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end   = min($page * $perPage, $total);

        return view('profesional/horarios', [
            'horarios'              => $horarios,
            'profesional'           => $profesional,
            'servicios_profesional' => $servicios_profesional,
            'usuario'               => ['s_nbre' => $session->get('usuario_nombre')],
            'rol'                   => $session->get('rol'),
            'activeNav'             => 'horarios',
            'search'                => $search,
            'periodo'               => $periodo,
            'servicioFiltro'        => $servicioFiltro,
            'pager_links'           => $pager_links,
            'pager_start'           => $start,
            'pager_end'             => $end,
            'pager_total'           => $total,
        ]);
    }

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

        return view('profesional/calendario', [
            'profesional' => $profesional,
            'servicios'   => $servicios,
            'clientes'    => $clientes,
            'usuario'     => ['s_nbre' => $session->get('usuario_nombre')],
            'rol'         => $session->get('rol'),
            'activeNav'   => 'calendario',
        ]);
    }

    public function clientes()
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        // We want users in 'cliente' table who are not in 'profesional' table
        $search = $this->request->getGet('search');
        $sort = $this->request->getGet('sort');

        $query = $db->table('cliente')
            ->select('cliente.*, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel, usuario.u_avatar, usuario.u_activo, usuario.u_fRegistro, usuario.u_id')
            ->join('usuario', 'usuario.u_id = cliente.c_uId')
            ->join('profesional', 'profesional.u_id = cliente.c_uId', 'left')
            ->where('profesional.p_id IS NULL');

        if (!empty($search)) {
            $query->like('usuario.u_nbreCompleto', $search);
        }

        if (!empty($sort)) {
            if ($sort === 'a-z') $query->orderBy('usuario.u_nbreCompleto', 'ASC');
            elseif ($sort === 'z-a') $query->orderBy('usuario.u_nbreCompleto', 'DESC');
            elseif ($sort === 'nuevos') $query->orderBy('usuario.u_fRegistro', 'DESC');
            elseif ($sort === 'viejos') $query->orderBy('usuario.u_fRegistro', 'ASC');
        } else {
            $query->orderBy('usuario.u_fRegistro', 'DESC');
        }

        $total = $query->countAllResults(false);
        $clientes = $query->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('profesional/clientes', [
            'clientes'    => $clientes,
            'usuario'     => ['s_nbre' => session()->get('usuario_nombre')],
            'rol'         => session()->get('rol'),
            'activeNav'   => 'clientes',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end'   => $end,
            'pager_total' => $total
        ]);
    }

    // -------------------------------------------------------
    // Login / Logout
    // -------------------------------------------------------

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url(''));
    }
}
