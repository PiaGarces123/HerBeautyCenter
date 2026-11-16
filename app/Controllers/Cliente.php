<?php

namespace App\Controllers;

use App\Models\HorarioModel;
use App\Models\ImagenModel;
use App\Models\ServicioModel;
use App\Models\TurnoModel;
use App\Models\UsuarioModel;

/**
 * Cliente
 * Her Beauty Center — Incremento N° 3: Consulta y Reserva de Turnos
 *
 * Controlador encargado de gestionar las funcionalidades
 * disponibles para los clientes y visitantes del sitio.
 *
 * Sus responsabilidades principales son:
 *
 * 1. Consultar el detalle de los servicios, sus imágenes
 *    y los profesionales que los realizan.
 * 2. Mostrar el calendario con los horarios disponibles
 *    para reservar un servicio.
 * 3. Validar y mostrar la información de un horario
 *    antes de confirmar una reserva.
 * 4. Mostrar al cliente sus turnos, próximos turnos
 *    e historial de reservas.
 * 5. Gestionar la información del perfil del cliente.
 * 6. Obtener el identificador del cliente asociado
 *    al usuario que inició sesión.
 */
class Cliente extends BaseController
{
    //----------------------
    // CLIENTE
    //----------------------
    // Busca en la tabla cliente el registro relacionado con el
    // usuario autenticado. Si no existe, crea uno automáticamente.
    private function obtenerClienteId(int $usuarioId): int
    {
        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')
            ->where('c_uId', $usuarioId)
            ->get()
            ->getRowArray();

        //Si existe: devuelve el id
        if ($cliente) {
            return (int) $cliente['c_id'];
        }

        //Si no existe: crea uno nuevo
        $db->table('cliente')->insert(['c_uId' => $usuarioId]);
        return (int) $db->insertID();
    }

    // =========================================================================
    // CONSULTAR SERVICIO (Vista de Detalle con Carrusel de Fotos)
    // =========================================================================
    public function servicio(int $sId = 0)
    {
        // Verifica que el identificador recibido sea válido.
        if ($sId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Instancia el modelo de servicios.
        $servicioModel = new ServicioModel();
        // Busca el servicio solicitado.
        $servicio = $servicioModel->where('s_id', $sId)
            ->where('s_activo', 1)
            ->first();

        // Si el servicio no existe o está inactivo,
        // RETORNA A SERVICIOS
        if (!$servicio) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Obtener galería de imágenes del servicio (para el carrusel)
        $imagenModel = new ImagenModel();

        // El método getImagenesByServicio() pertenece a ImagenModel
        // y devuelve las imágenes ordenadas según su configuración.
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

        // Consulta los horarios futuros disponibles para el servicio.
        $horarioModel = new HorarioModel();

        // El método getDisponibilidadServicio() pertenece a HorarioModel
        // y se encarga de aplicar las condiciones de disponibilidad.
        $horariosDisponibles = $horarioModel->getDisponibilidadServicio($sId);

        // Otros servicios activos sugeridos (con su imagen principal de portada)
        $otrosServicios = $db->table('servicio')
            ->select('servicio.*, (SELECT img_ruta FROM imagen WHERE imagen.img_sId = servicio.s_id ORDER BY imagen.img_orden ASC, imagen.img_id ASC LIMIT 1) AS imagen_ruta')
            ->where('servicio.s_activo', 1)
            ->where('servicio.s_id !=', $sId)
            ->orderBy('servicio.s_orden', 'ASC')
            ->limit(3)
            ->get()
            ->getResultArray();

        // Carga la vista de detalle del servicio.
        return view('cliente/servicio_detalle', [
            'servicio' => $servicio,
            'imagenes' => $imagenes,
            'profesionales' => $profesionales,
            'totalHorarios' => count($horariosDisponibles),
            'otrosServicios' => $otrosServicios,
        ]);
    }

    // =========================================================================
    // CU04 (Paso 1 y 2) — "Reservá tu Turno" (Calendario + Filtros + Modal)
    // =========================================================================
    public function reservar(int $sId = 0)
    {
        $session = session();
        if (!$session->get('usuario_id')) {
            return redirect()->to(base_url('/?login=1'));
        }

        // Impide que profesionales y administradores utilicen
        // el calendario de reservas destinado a los clientes.
        if (in_array($session->get('rol'), ['admin', 'profesional'], true)) {
            return redirect()->to(base_url('admin'));
        }

        // Comprueba que el identificador del servicio sea válido.
        if ($sId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Busca el servicio solicitado y comprueba que esté activo.
        $servicioModel = new ServicioModel();
        $servicio = $servicioModel->where('s_id', $sId)
            ->where('s_activo', 1)
            ->first();

        // Si el servicio no existe o está inactivo,
        // redirige a la sección de servicios.
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

        // Recupera el identificador de profesional enviado
        // mediante el parámetro GET p_id.

        // Esto permite abrir el calendario con un profesional
        // previamente seleccionado, si se recibió ese parámetro.
        $pIdPreseleccionado = (int) ($this->request->getGet('p_id') ?? 0);

        // Carga la vista del calendario de reservas.
        return view('cliente/reservar_calendario', [
            'servicio' => $servicio,
            'profesionales' => $profesionales,
            'pIdPreseleccionado' => $pIdPreseleccionado,
        ]);
    }

    // =========================================================================
    // Paso 3 — Pre-Confirmación del Turno y Datos del Cliente
    // Ruta: GET /reservar/confirmar/(:num)
    // =========================================================================
    public function confirmar(int $hId = 0)
    {
        $session = session();
        $usuarioId = (int) $session->get('usuario_id');
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        // Impide que administradores y profesionales accedan
        // al proceso de reserva destinado a clientes.
        if (in_array($session->get('rol'), ['admin', 'profesional'], true)) {
            return redirect()->to(base_url('admin'));
        }

        // Verifica que el identificador del horario sea válido.
        if ($hId <= 0) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Instancia el modelo de horarios.
        $horarioModel = new HorarioModel();

        // Recupera los datos del horario seleccionado,
        // incluyendo la información del servicio y del profesional.
        $horario = $horarioModel->getHorarioParaReserva($hId);

        // Comprueba que el horario exista y que tanto el servicio
        // como el profesional asociado estén activos.
        if (!$horario || empty($horario['s_activo']) || empty($horario['prof_activo'])) {
            return redirect()->to(base_url('/#servicios'));
        }

        // Validar que el horario no sea de una fecha pasada ni esté ocupado
        if ($horario['h_fecha'] < date('Y-m-d') || $horarioModel->tieneTurnoActivo($hId)) {
            return redirect()->to(base_url('reservar/' . $horario['s_id'] . '?ocupado=1'));
        }

        // Instancia el modelo de usuarios.
        $usuarioModel = new UsuarioModel();

        // Busca los datos del usuario autenticado mediante su ID.
        // Si no se encuentra el registro, utiliza un array vacío.
        $usuario = $usuarioModel->find($usuarioId) ?? [];

        $db = \Config\Database::connect();
        // Busca el registro de cliente asociado al usuario autenticado.
        $cliente = $db->table('cliente')->where('c_uId', $usuarioId)->get()->getRowArray();

        // Agrega las consideraciones del cliente al array del usuario.
        // Si no hay registro o el campo está vacío, asigna una cadena vacía.
        $usuario['consideraciones'] = $cliente['c_consideraciones'] ?? '';

        // Carga la vista de pre-confirmación.
        // Se envían los datos del horario y del usuario para mostrarlos
        return view('cliente/reservar_confirmar', [
            'horario' => $horario,
            'usuario' => $usuario,
        ]);
    }

    // =========================================================================
    // CU05 — Panel del Cliente: "Mis Turnos" + «Mi próximo turno»
    // Ruta: GET /mis-turnos
    // =========================================================================
    public function misTurnos()
    {
        $session = session();
        $usuarioId = (int) $session->get('usuario_id');
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        //Si no es cliente, redirige a panel admin
        if (in_array($session->get('rol'), ['admin', 'profesional'], true)) {
            return redirect()->to(base_url('admin'));
        }

        // Obtiene el identificador del cliente asociado al usuario.
        // Si no existe, el método obtenerClienteId() crea el registro.
        $cId = $this->obtenerClienteId($usuarioId);

        // Instancia el modelo encargado de consultar los turnos.
        $turnoModel = new TurnoModel();

        //Filtro Estado
        $filtroEstado = trim((string) ($this->request->getGet('estado') ?? ''));
        //Filtro Período
        $filtroPeriodo = trim((string) ($this->request->getGet('periodo') ?? 'todos'));

        // Próximo turno destacado (Solicitado o Confirmado a partir de hoy)
        $proximoTurno = $turnoModel->getProximoTurnoCliente($cId);

        // Todos los turnos sin filtro para contadores rápidos
        $todosLosTurnos = $turnoModel->getTurnosCliente($cId);

        // Obtiene la fecha actual
        $hoy = date('Y-m-d');

        // Inicializa los contadores que se mostrarán en la vista.
        $contadores = [
            'todos' => count($todosLosTurnos),
            'proximos' => 0,
            'historial' => 0,
            'Solicitado' => 0,
            'Confirmado' => 0,
            'Realizado' => 0,
            'Cancelado' => 0,
        ];

        // Recorre todos los turnos para calcular las cantidades
        // correspondientes a cada estado y período.
        foreach ($todosLosTurnos as $t) {


            // Recupera el estado del turno.
            // Si no existe el índice estado, utiliza una cadena vacía.
            $est = $t['estado'] ?? '';

            // Comprueba si existe un contador para ese estado.
            // Si existe, incrementa su valor en uno.
            if (isset($contadores[$est])) {
                $contadores[$est]++;
            }

            // Determina si el turno debe contabilizarse como próximo. Debe
            // 1. Estar Solicitado o Confirmado.
            // 2. Tener una fecha igual o posterior a la fecha actual.
            if (in_array($est, ['Solicitado', 'Confirmado'], true) && $t['fecha'] >= $hoy) {
                $contadores['proximos']++;
            } else {
                // Si no cumple las condiciones anteriores,
                // se contabiliza dentro del historial.
                $contadores['historial']++;
            }
        }

        // Turnos filtrados según la selección del cliente
        $turnos = $turnoModel->getTurnosCliente($cId, [
            'estado' => $filtroEstado,
            'periodo' => $filtroPeriodo !== 'todos' ? $filtroPeriodo : '',
        ]);

        // Carga la vista "Mis Turnos" con toda la información necesaria:
        // próximo turno, listado filtrado, contadores y filtros actuales.
        return view('cliente/mis_turnos', [
            'proximoTurno' => $proximoTurno,
            'turnos' => $turnos,
            'contadores' => $contadores,
            'filtroEstado' => $filtroEstado,
            'filtroPeriodo' => $filtroPeriodo,
        ]);
    }

    // =========================================================================
    // Mi Perfil del Cliente
    // Ruta: GET /mi-perfil
    // =========================================================================
    public function perfil()
    {
        $session = session();
        $usuarioId = (int) $session->get('usuario_id');

        // Si no existe un usuario autenticado, solicita iniciar sesión.
        if (!$usuarioId) {
            return redirect()->to(base_url('/?login=1'));
        }

        // Si el usuario es administrador o profesional,
        // lo redirige al perfil correspondiente del panel administrativo.
        if (in_array($session->get('rol'), ['admin', 'profesional'], true)) {
            return redirect()->to(base_url('admin/perfil'));
        }

        // Instancia el modelo de usuarios.
        $usuarioModel = new UsuarioModel();
        // Recupera los datos personales del usuario autenticado.
        $usuario = $usuarioModel->find($usuarioId) ?? [];

        // Obtiene el identificador del cliente asociado al usuario.
        $cId = $this->obtenerClienteId($usuarioId);

        // Instancia el modelo de turnos.
        $turnoModel = new TurnoModel();

        // Recupera el próximo turno del cliente.
        $proximoTurno = $turnoModel->getProximoTurnoCliente($cId);

        // Contar exclusivamente reservas vigentes (no canceladas ni pasadas)
        $ahora = date('Y-m-d H:i:s');

        // Recupera los turnos correspondientes al período próximo.
        $turnosProximos = $turnoModel->getTurnosCliente($cId, ['periodo' => 'proximos']);

        // Filtra los turnos para conservar exclusivamente aquellos
        // que todavía no finalizaron y cuyo estado es Solicitado o Confirmado.
        $turnosPorAsistir = array_filter($turnosProximos, function ($t) use ($ahora) {
            $finTurno = trim(($t['fecha'] ?? '') . ' ' . ($t['h_horaHasta'] ?? '23:59:59'));
            return $finTurno >= $ahora && in_array($t['estado'] ?? '', ['Solicitado', 'Confirmado'], true);
        });

        $db = \Config\Database::connect();
        // Recupera el registro del cliente mediante su identificador.
        $cliente = $db->table('cliente')->where('c_id', $cId)->get()->getRowArray();
        // Agrega las consideraciones del cliente al array de usuario.
        // Si no existen datos, asigna una cadena vacía.
        $usuario['consideraciones'] = $cliente['c_consideraciones'] ?? '';

        // Carga la vista del perfil del cliente.
        return view('cliente/perfil', [
            'usuario' => $usuario,
            'proximoTurno' => $proximoTurno,
            'totalTurnos' => count($turnosPorAsistir),
        ]);
    }
}

