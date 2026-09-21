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

        return view('admin/dashboard', [
            'usuario' => [
                'nombre' => $session->get('usuario_nombre'),
                'correo' => $session->get('usuario_correo'),
                'avatar' => $session->get('usuario_avatar'),
            ],
            'rol' => $session->get('rol'),
            'id_profesional' => $session->get('id_profesional'),
            'activeNav' => 'dashboard',
        ]);
    }

    public function profesionales(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        $total = $db->table('profesional')->countAllResults();

        $profesionales = $db->table('profesional')
            ->select('profesional.*, usuario.nombre_completo, usuario.correo, usuario.telefono, usuario.avatar, usuario.activo, GROUP_CONCAT(servicio.nombre SEPARATOR ", ") as servicios_ofrecidos, GROUP_CONCAT(servicio.id_servicio SEPARATOR ",") as servicios_ids')
            ->join('usuario', 'usuario.id_usuario = profesional.id_usuario')
            ->join('profesional_servicio', 'profesional_servicio.id_profesional = profesional.id_profesional', 'left')
            ->join('servicio', 'servicio.id_servicio = profesional_servicio.id_servicio', 'left')
            ->groupBy('profesional.id_profesional')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        $todos_servicios = $db->table('servicio')->select('id_servicio, nombre')->get()->getResultArray();
        
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('admin/profesionales', [
            'profesionales' => $profesionales,
            'todos_servicios' => $todos_servicios,
            'usuario' => ['nombre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'profesionales',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }

    public function servicios(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        $total = $db->table('servicio')->countAllResults();

        $servicios = $db->table('servicio')
            ->select('servicio.*, imagen.ruta as imagen_ruta')
            ->join('imagen', 'imagen.id_servicio = servicio.id_servicio', 'left')
            ->groupBy('servicio.id_servicio')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('admin/servicios', [
            'servicios' => $servicios,
            'usuario' => ['nombre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'servicios',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }

    public function turnos(): string
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
                      profesional.id_profesional,
                      u_prof.nombre_completo as nombre_profesional,
                      u_cli.nombre_completo as nombre_cliente')
            ->join('profesional', 'profesional.id_profesional = turno.id_profesional')
            ->join('usuario as u_prof', 'u_prof.id_usuario = profesional.id_usuario')
            ->join('cliente', 'cliente.id_cliente = turno.id_cliente', 'left')
            ->join('usuario as u_cli', 'u_cli.id_usuario = cliente.id_usuario', 'left')
            ->orderBy('turno.fecha', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('admin/turnos', [
            'turnos' => $turnos,
            'usuario' => ['nombre' => session()->get('usuario_nombre')],
            'rol' => session()->get('rol'),
            'activeNav' => 'turnos',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end' => $end,
            'pager_total' => $total
        ]);
    }

    public function perfil(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $session = session();
        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('id_usuario', $session->get('usuario_id'))
            ->get()->getRowArray();

        return view('admin/perfil', [
            'usuario_data' => $usuario,
            'usuario' => ['nombre' => $session->get('usuario_nombre')],
            'rol' => $session->get('rol'),
            'activeNav' => 'perfil'
        ]);
    }

    public function horarios(): string
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
            ->where('id_usuario', $session->get('usuario_id'))
            ->get()->getRowArray();

        $horarios = [];
        $total = 0;
        if ($profesional) {
            $total = $db->table('horario')->where('id_profesional', $profesional['id_profesional'])->countAllResults();
            
            $horarios = $db->table('horario')
                ->select('horario.*, servicio.nombre as nombre_servicio')
                ->join('servicio', 'servicio.id_servicio = horario.id_servicio', 'left')
                ->where('horario.id_profesional', $profesional['id_profesional'])
                ->orderBy('horario.fecha', 'ASC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()->getResultArray();
        }
        
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('admin/horarios', [
            'horarios'    => $horarios,
            'profesional' => $profesional,
            'usuario'     => ['nombre' => $session->get('usuario_nombre')],
            'rol'         => $session->get('rol'),
            'activeNav'   => 'horarios',
            'pager_links' => $pager_links,
            'pager_start' => $start,
            'pager_end'   => $end,
            'pager_total' => $total
        ]);
    }

    public function clientes(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        
        $pager = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        
        // We want users in 'cliente' table who are not in 'profesional' table
        $total = $db->table('cliente')
            ->join('profesional', 'profesional.id_usuario = cliente.id_usuario', 'left')
            ->where('profesional.id_profesional IS NULL')
            ->countAllResults();

        $clientes = $db->table('cliente')
            ->select('cliente.id_cliente, usuario.id_usuario, usuario.nombre_completo, usuario.correo, usuario.telefono, usuario.activo, usuario.fecha_registro, usuario.avatar')
            ->join('usuario', 'usuario.id_usuario = cliente.id_usuario')
            ->join('profesional', 'profesional.id_usuario = cliente.id_usuario', 'left')
            ->where('profesional.id_profesional IS NULL')
            ->orderBy('usuario.fecha_registro', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();
            
        $pager_links = $pager->makeLinks($page, $perPage, $total, 'admin_pagination');
        $start = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
        $end = min($page * $perPage, $total);

        return view('admin/clientes', [
            'clientes'    => $clientes,
            'usuario'     => ['nombre' => session()->get('usuario_nombre')],
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
