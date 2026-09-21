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
        ]);
    }

    public function profesionales(): string
    {
        $redirect = $this->verificarSesionAdmin();
        if ($redirect) return $redirect;

        $db = \Config\Database::connect();
        $profesionales = $db->table('profesional')
            ->select('profesional.*, usuario.nombre_completo, usuario.correo, usuario.telefono, usuario.avatar, usuario.activo, GROUP_CONCAT(servicio.nombre SEPARATOR ", ") as servicios_ofrecidos, GROUP_CONCAT(servicio.id_servicio SEPARATOR ",") as servicios_ids')
            ->join('usuario', 'usuario.id_usuario = profesional.id_usuario')
            ->join('profesional_servicio', 'profesional_servicio.id_profesional = profesional.id_profesional', 'left')
            ->join('servicio', 'servicio.id_servicio = profesional_servicio.id_servicio', 'left')
            ->groupBy('profesional.id_profesional')
            ->get()->getResultArray();

        $todos_servicios = $db->table('servicio')->select('id_servicio, nombre')->get()->getResultArray();

        return view('admin/profesionales', [
            'profesionales' => $profesionales,
            'todos_servicios' => $todos_servicios,
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

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url(''));
    }
}
