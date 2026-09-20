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
            return redirect()->to('/admin/login')->with('error', 'Acceso restringido.');
        }
        return null;
    }

    public function dashboard(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $session = session();
        $db = \Config\Database::connect();

        $profesionales  = $db->table('profesional')->countAllResults();
        $servicios      = $db->table('servicio')->where('activo', 1)->countAllResults();
        $totalClientes  = $db->table('cliente')->countAllResults();
        $turnosHoy      = $db->table('turno')->where('fecha', date('Y-m-d'))->get()->getResultArray();

        return view('admin/dashboard', [
            'usuario'        => [
                'nombre' => $session->get('usuario_nombre'),
                'correo' => $session->get('usuario_correo'),
                'avatar' => $session->get('usuario_avatar'),
            ],
            'profesionales'  => array_fill(0, $profesionales, []),
            'servicios'      => array_fill(0, $servicios, []),
            'totalClientes'  => $totalClientes,
            'turnosHoy'      => $turnosHoy,
        ]);
    }

    public function profesionales(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        $profesionales = $db->table('profesional')
            ->select('profesional.*, usuario.nombre_completo, usuario.correo, usuario.avatar, usuario.activo')
            ->join('usuario', 'usuario.id_usuario = profesional.id_usuario')
            ->get()->getResultArray();

        return view('admin/profesionales', [
            'profesionales' => $profesionales,
            'usuario' => ['nombre' => session()->get('usuario_nombre')]
        ]);
    }

    public function servicios(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        $servicios = $db->table('servicio')
            ->select('servicio.*, imagen.ruta as imagen_ruta')
            ->join('imagen', 'imagen.id_servicio = servicio.id_servicio', 'left')
            ->groupBy('servicio.id_servicio')
            ->get()->getResultArray();

        return view('admin/servicios', [
            'servicios' => $servicios,
            'usuario' => ['nombre' => session()->get('usuario_nombre')]
        ]);
    }

    public function turnos(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
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
            ->get()->getResultArray();

        return view('admin/turnos', [
            'turnos' => $turnos,
            'usuario' => ['nombre' => session()->get('usuario_nombre')]
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
            'usuario' => ['nombre' => $session->get('usuario_nombre')]
        ]);
    }

    public function horarios(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $session = session();
        $db = \Config\Database::connect();

        // Buscar el profesional ligado a este usuario
        $profesional = $db->table('profesional')
            ->where('id_usuario', $session->get('usuario_id'))
            ->get()->getRowArray();

        $horarios = [];
        if ($profesional) {
            $horarios = $db->table('horario')
                ->select('horario.*, servicio.nombre as nombre_servicio')
                ->join('servicio', 'servicio.id_servicio = horario.id_servicio', 'left')
                ->where('horario.id_profesional', $profesional['id_profesional'])
                ->orderBy('horario.fecha', 'ASC')
                ->get()->getResultArray();
        }

        return view('admin/horarios', [
            'horarios'    => $horarios,
            'profesional' => $profesional,
            'usuario'     => ['nombre' => $session->get('usuario_nombre')]
        ]);
    }

    // -------------------------------------------------------
    // Login / Logout
    // -------------------------------------------------------

    public function login(): string
    {
        if (session()->get('usuario_id')) {
            return redirect()->to('/admin');
        }
        return view('admin/login');
    }

    public function loginPost()
    {
        $correo   = $this->request->getPost('correo');
        $password = $this->request->getPost('password');

        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('correo', $correo)
            ->where('activo', 1)
            ->get()->getRowArray();

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return redirect()->to('/admin/login')
                ->with('error', 'Correo o contraseña incorrectos.');
        }

        // Verificar que sea admin
        $admin = $db->table('administrador')
            ->join('profesional', 'profesional.id_profesional = administrador.id_profesional')
            ->where('profesional.id_usuario', $usuario['id_usuario'])
            ->get()->getRowArray();

        if (!$admin) {
            return redirect()->to('/admin/login')
                ->with('error', 'No tenés permisos de administrador.');
        }

        session()->set([
            'usuario_id'     => $usuario['id_usuario'],
            'usuario_nombre' => $usuario['nombre_completo'],
            'usuario_correo' => $usuario['correo'],
            'usuario_avatar' => $usuario['avatar'],
            'rol'            => 'admin',
        ]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('admin/login'));
    }
}
