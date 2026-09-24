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
            $val = $estado === 'u_activo' ? 1 : 0;
            $query->where('usuario.u_activo', $val);
        }
        if (!empty($servicioFiltro)) {
            $query->where("EXISTS (SELECT 1 FROM profesional_servicio ps WHERE ps.id_profesional = profesional.p_id AND ps.id_servicio = " . intval($servicioFiltro) . ")", null, false);
        }

        $total = $query->countAllResults(false);

        $profesionales = $query
            ->select('profesional.*, usuario.u_nbreCompleto, usuario.u_correo, usuario.u_tel, usuario.u_avatar, usuario.u_activo, GROUP_CONCAT(servicio.s_nbre SEPARATOR ", ") as servicios_ofrecidos, GROUP_CONCAT(servicio.s_id SEPARATOR ",") as servicios_ids')
            ->join('profesional_servicio', 'profesional_servicio.p_id = profesional.p_id', 'left')
            ->join('servicio', 'servicio.s_id = profesional_servicio.p_sId', 'left')
            ->groupBy('profesional.p_id')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        $todos_servicios = $db->table('servicio')->select('id_servicio, nombre')->get()->getResultArray();
        
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
            $val = $estado === 'u_activo' ? 1 : 0;
            $query->where('servicio.activo', $val);
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
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        $total = $db->table('turno')->countAllResults();

        $turnos = $db->table('turno')
            ->select('turno.*, 
                      profesional.p_id,
                      u_prof.nombre_completo as nombre_profesional,
                      u_cli.nombre_completo as nombre_cliente')
            ->join('profesional', 'profesional.p_id = turno.t_pId')
            ->join('usuario as u_prof', 'u_prof.u_id = profesional.u_id')
            ->join('cliente', 'cliente.c_id = turno.t_cId', 'left')
            ->join('usuario as u_cli', 'u_cli.u_id = cliente.c_uId', 'left')
            ->orderBy('turno.fecha', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('profesional/turnos', [
            'turnos' => $turnos,
            'usuario' => ['s_nbre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'turnos',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
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
                ->where('p_id', $profesional['p_id'])
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
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $session = session();
        $db = \Config\Database::connect();

        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;

        // Buscar el profesional ligado a este usuario
        $profesional = $db->table('profesional')
            ->where('u_id', $session->get('usuario_id'))
            ->get()->getRowArray();

        $horarios = [];
        $total = 0;
        if ($profesional) {
            $total = $db->table('horario')->where('p_id', $profesional['p_id'])->countAllResults();
            
            $horarios = $db->table('horario')
                ->select('horario.*, servicio.s_nbre as nombre_servicio')
                ->join('servicio', 'servicio.s_id = horario.h_sId', 'left')
                ->where('horario.h_pId', $profesional['p_id'])
                ->orderBy('horario.fecha', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()->getResultArray();
        }
        
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('profesional/horarios', [
            'horarios'    => $horarios,
            'profesional' => $profesional,
            'usuario'     => ['s_nbre' => $session->get('usuario_nombre')],
            'rol'         => $session->get('rol'),
            'activeNav'   => 'horarios',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end'   => $end,
            'pager_total' => $total
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
