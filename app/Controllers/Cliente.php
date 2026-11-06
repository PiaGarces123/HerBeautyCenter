<?php

namespace App\Controllers;

use App\Models\HorarioModel;
use App\Models\ImagenModel;
use App\Models\ServicioModel;
use App\Models\TurnoModel;
use App\Models\UsuarioModel;

/**
 * Cliente Controller
 * Her Beauty Center — Incremento N° 3: Consulta y Reserva de Turnos
 *
 * Gestiona las vistas orientadas al cliente e invitado:
 * - CU01: Detalle de Servicio con carrusel de fotos y profesionales (servicio)
 * - CU04: Calendario de Reserva con filtros y Modal de horarios (reservar)
 * - CU04: Vista de Pre-Confirmación y Datos del Cliente (confirmar)
 * - CU05: Panel del Cliente "Mis Turnos" con tarjeta destacada «Mi próximo turno» (misTurnos)
 * - Gestión de Perfil del Cliente (perfil)
 */
class Cliente extends BaseController
{
    /**
     * Obtiene (o crea si no existe) el ID de cliente (c_id) asociado al usuario en sesión.
     */
    private function obtenerClienteId(int $usuarioId): int
    {
        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')
            ->where('c_uId', $usuarioId)
            ->get()
            ->getRowArray();

        if ($cliente) {
            return (int) $cliente['c_id'];
        }

        $db->table('cliente')->insert(['c_uId' => $usuarioId]);
        return (int) $db->insertID();
    }

    // =========================================================================
    // CU01 — Consultar Servicio (Vista de Detalle con Carrusel de Fotos)
    // Ruta pública: GET /servicio/(:num)
    // =========================================================================
    public function servicio(int $sId = 0)
    {
        if ($sId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        $servicioModel = new ServicioModel();
        $servicio = $servicioModel->where('s_id', $sId)
            ->where('s_activo', 1)
            ->first();

        if (!$servicio) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Obtener galería de imágenes del servicio (para el carrusel Bootstrap 5)
        $imagenModel = new ImagenModel();
        $imagenes = $imagenModel->getImagenesByServicio($sId);

        // Obtener profesionales activas que brindan este servicio
        $db = \Config\Database::connect();
        $profesionales = $db->table('profesional_servicio')
            ->select('profesional.p_id, profesional.p_titulo, profesional.p_desc,
                      usuario.u_nbreCompleto, usuario.u_avatar')
            ->join('profesional', 'profesional.p_id = profesional_servicio.p_id')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->where('profesional_servicio.p_sId', $sId)
            ->where('usuario.u_activo', 1)
            ->orderBy('profesional.p_orden', 'ASC')
            ->get()
            ->getResultArray();

        // Cantidad de horarios futuros disponibles para informar al usuario
        $horarioModel = new HorarioModel();
        $horariosDisponibles = $horarioModel->getDisponibilidadServicio($sId);

        // Otros servicios activos sugeridos
        $otrosServicios = $db->table('servicio')
            ->select('servicio.*, MIN(imagen.img_ruta) as imagen_ruta')
            ->join('imagen', 'imagen.img_sId = servicio.s_id', 'left')
            ->where('servicio.s_activo', 1)
            ->where('servicio.s_id !=', $sId)
            ->groupBy('servicio.s_id')
            ->orderBy('servicio.s_orden', 'ASC')
            ->limit(3)
            ->get()
            ->getResultArray();

        return view('cliente/servicio_detalle', [
            'servicio'       => $servicio,
            'imagenes'       => $imagenes,
            'profesionales'  => $profesionales,
            'totalHorarios'  => count($horariosDisponibles),
            'otrosServicios' => $otrosServicios,
        ]);
    }

    // =========================================================================
    // CU04 (Paso 1 y 2) — "Reservá tu Turno" (Calendario + Filtros + Modal)
    // Ruta autenticada: GET /reservar/(:num)
    // =========================================================================
    public function reservar(int $sId = 0)
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to(base_url('/?login=1'));
        }

        if ($sId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        $servicioModel = new ServicioModel();
        $servicio = $servicioModel->where('s_id', $sId)
            ->where('s_activo', 1)
            ->first();

        if (!$servicio) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Profesionales activas que dictan el servicio (para el <select> de filtro)
        $db = \Config\Database::connect();
        $profesionales = $db->table('profesional_servicio')
            ->select('profesional.p_id, profesional.p_titulo, usuario.u_nbreCompleto, usuario.u_avatar')
            ->join('profesional', 'profesional.p_id = profesional_servicio.p_id')
            ->join('usuario', 'usuario.u_id = profesional.u_id')
            ->where('profesional_servicio.p_sId', $sId)
            ->where('usuario.u_activo', 1)
            ->orderBy('usuario.u_nbreCompleto', 'ASC')
            ->get()
            ->getResultArray();

        $pIdPreseleccionado = (int) ($this->request->getGet('p_id') ?? 0);

        return view('cliente/reservar_calendario', [
            'servicio'           => $servicio,
            'profesionales'      => $profesionales,
            'pIdPreseleccionado' => $pIdPreseleccionado,
        ]);
    }

    // =========================================================================
    // CU04 (Paso 3) — Pre-Confirmación del Turno y Datos del Cliente
    // Ruta autenticada: GET /reservar/confirmar/(:num)
    // =========================================================================
    public function confirmar(int $hId = 0)
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        if ($hId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        $horarioModel = new HorarioModel();
        $horario = $horarioModel->getHorarioParaReserva($hId);

        if (!$horario || empty($horario['s_activo']) || empty($horario['prof_activo'])) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Validar que el horario no sea de una fecha pasada ni esté ocupado
        if ($horario['h_fecha'] < date('Y-m-d') || $horarioModel->tieneTurnoActivo($hId)) {
            return redirect()->to(base_url('reservar/' . $horario['s_id'] . '?ocupado=1'));
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($usuarioId) ?? [];

        return view('cliente/reservar_confirmar', [
            'horario' => $horario,
            'usuario' => $usuario,
        ]);
    }

    // =========================================================================
    // CU05 — Panel del Cliente: "Mis Turnos" + Tarjeta «Mi próximo turno»
    // Ruta autenticada: GET /mis-turnos
    // =========================================================================
    public function misTurnos()
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        $cId = $this->obtenerClienteId($usuarioId);
        $turnoModel = new TurnoModel();

        $filtroEstado  = trim((string) ($this->request->getGet('estado') ?? ''));
        $filtroPeriodo = trim((string) ($this->request->getGet('periodo') ?? 'todos'));

        // Próximo turno destacado (Solicitado o Confirmado a partir de hoy)
        $proximoTurno = $turnoModel->getProximoTurnoCliente($cId);

        // Todos los turnos sin filtro para contadores rápidos
        $todosLosTurnos = $turnoModel->getTurnosCliente($cId);
        $hoy = date('Y-m-d');

        $contadores = [
            'todos'      => count($todosLosTurnos),
            'proximos'   => 0,
            'historial'  => 0,
            'Solicitado' => 0,
            'Confirmado' => 0,
            'Realizado'  => 0,
            'Cancelado'  => 0,
        ];

        foreach ($todosLosTurnos as $t) {
            $est = $t['estado'] ?? '';
            if (isset($contadores[$est])) {
                $contadores[$est]++;
            }
            if (in_array($est, ['Solicitado', 'Confirmado'], true) && $t['fecha'] >= $hoy) {
                $contadores['proximos']++;
            } else {
                $contadores['historial']++;
            }
        }

        // Turnos filtrados según la selección del cliente
        $turnos = $turnoModel->getTurnosCliente($cId, [
            'estado'  => $filtroEstado,
            'periodo' => $filtroPeriodo !== 'todos' ? $filtroPeriodo : '',
        ]);

        return view('cliente/mis_turnos', [
            'proximoTurno'  => $proximoTurno,
            'turnos'        => $turnos,
            'contadores'    => $contadores,
            'filtroEstado'  => $filtroEstado,
            'filtroPeriodo' => $filtroPeriodo,
        ]);
    }

    // =========================================================================
    // Mi Perfil del Cliente
    // Ruta autenticada: GET /mi-perfil
    // =========================================================================
    public function perfil()
    {
        $usuarioId = (int) session()->get('usuario_id');
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($usuarioId) ?? [];

        $cId = $this->obtenerClienteId($usuarioId);
        $turnoModel = new TurnoModel();
        $proximoTurno = $turnoModel->getProximoTurnoCliente($cId);
        $todosLosTurnos = $turnoModel->getTurnosCliente($cId);

        return view('cliente/perfil', [
            'usuario'      => $usuario,
            'proximoTurno' => $proximoTurno,
            'totalTurnos'  => count($todosLosTurnos),
        ]);
    }
}

