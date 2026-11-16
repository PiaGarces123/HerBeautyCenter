<?php

use CodeIgniter\Router\RouteCollection;

// -------------------------------------------------------------------------
// RUTA PRINCIPAL
// -------------------------------------------------------------------------

// GET: muestra la página de inicio del sitio web.
// '/' representa la dirección principal del dominio.
// Home::index ejecuta el método index del controlador Home.
/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// -------------------------------------------------------------------------
// MARCO LEGAL, TÉRMINOS Y POLÍTICA DE PRIVACIDAD
// -------------------------------------------------------------------------
// Lleva al archivo PoliticaPrivacidad.php en la carpeta views
$routes->get('politica-de-privacidad', 'Home::politicaPrivacidad');
$routes->get('terminos-y-condiciones', 'Home::politicaPrivacidad');

// =========================================================================
// VISTAS PÚBLICAS Y DEL CLIENTE
// =========================================================================
// Vista de detalle de servicio con carrusel de fotos (acceso público)
// (:num) representa un número que identifica al servicio.
// $1 envía ese número como parámetro al método del controlador.
$routes->get('servicio/(:num)', 'Cliente::servicio/$1');


// -------------------------------------------------------------------------
// RUTAS WEB EXCLUSIVAS PARA CLIENTES
// -------------------------------------------------------------------------
// Agrupa las rutas destinadas exclusivamente a Clientes
// El filtro auth:cliente verifica que el usuario haya iniciado sesión
// y que su tipo de cuenta tenga los permisos correspondientes.
$routes->group('', ['filter' => 'auth:cliente'], function ($routes) {

    // ----------- Flujo de solicitud y reserva de turnos ----------- 
    // Recibe el identificador del servicio seleccionado por el cliente.
    $routes->get('reservar/(:num)', 'Cliente::reservar/$1');
    // Muestra el modal de confirmación de turno, recibiendo el ID de la disponibilidad seleccionada.
    $routes->get('reservar/confirmar/(:num)', 'Cliente::confirmar/$1');

    // ----------- Panel del Cliente ("Mis Turnos" y "Mi Perfil") ----------- 
    $routes->get('mis-turnos', 'Cliente::misTurnos');
    $routes->get('mi-perfil', 'Cliente::perfil');
});

// -------------------------------------------------------------------------
// AUTENTICACIÓN Y CIERRE DE SESIÓN
// -------------------------------------------------------------------------
// Cierra la sesión del usuario actual.
$routes->match(['get', 'post'], '/logout', 'Auth::logout');


// -------------------------------------------------------------------------
// COMPATIBILIDAD CON DESPLIEGUES QUE UTILIZAN EL PREFIJO /PUBLIC
// -------------------------------------------------------------------------
// Estas rutas permiten que las solicitudes de autenticación sigan
// funcionando cuando la aplicación se encuentra publicada bajo
// una dirección que incluye el prefijo /public.

// POST: procesa el inicio de sesión de un usuario.
$routes->post('/public/api/login', 'Auth::login');

// POST: procesa el registro de una nueva cuenta.
$routes->post('/public/api/register', 'Auth::register');

// GET o POST: permite cerrar la sesión desde la dirección /public/logout.
$routes->match(['get', 'post'], '/public/logout', 'Auth::logout');

// GET o POST: permite cerrar la sesión utilizando la dirección de la API.
$routes->match(['get', 'post'], '/public/api/logout', 'Auth::logout');

// =========================================================================
// API ROUTES
// =========================================================================

// Agrupa las rutas que forman parte de la API del sistema.
// Estas rutas permiten que las vistas del sistema se comuniquen
// con los controladores para consultar o modificar información.

$routes->group('api', function ($routes) {
    // Rutas públicas de autenticación
    $routes->post('login', 'Auth::login');
    $routes->post('register', 'Auth::register');
    $routes->match(['get', 'post'], 'logout', 'Auth::logout');

    // ---------------------------------------------------------------------
    // API: FUNCIONES DE ADMIN
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin'], function ($routes) {
        // Dashboard Admin
        $routes->get('dashboard/admin', 'Api\DashboardApi::adminStats');

        // Servicios APIs
        $routes->post('servicios/crear', 'Api\ServiciosApi::crear');
        $routes->post('servicios/editar/(:num)', 'Api\ServiciosApi::editar/$1');
        $routes->put('servicios/toggle-estado/(:num)', 'Api\ServiciosApi::toggleEstado/$1');
        $routes->delete('servicios/eliminar/(:num)', 'Api\ServiciosApi::eliminar/$1');
        $routes->put('servicios/profesionales/(:num)', 'Api\ServiciosApi::profesionales/$1');
        $routes->post('servicios/ordenar', 'Api\ServiciosApi::ordenar');
        $routes->get('servicios/imagenes/(:num)', 'Api\ServiciosApi::imagenes/$1');
        $routes->post('servicios/imagenes/subir/(:num)', 'Api\ServiciosApi::subirImagenes/$1');
        $routes->post('servicios/imagenes/ordenar/(:num)', 'Api\ServiciosApi::ordenarImagenes/$1');
        $routes->delete('servicios/imagenes/eliminar/(:num)', 'Api\ServiciosApi::eliminarImagen/$1');

        // Profesionales APIs
        $routes->post('profesionales/crear', 'Api\ProfesionalesApi::crear');
        $routes->post('profesionales/servicios', 'Api\ProfesionalesApi::servicios');
        $routes->put('profesionales/editar/(:num)', 'Api\ProfesionalesApi::editar/$1');
        $routes->put('profesionales/password/(:num)', 'Api\ProfesionalesApi::password/$1');
        $routes->put('profesionales/toggle-estado/(:num)', 'Api\ProfesionalesApi::toggleEstado/$1');
        $routes->delete('profesionales/eliminar/(:num)', 'Api\ProfesionalesApi::eliminar/$1');
        $routes->post('profesionales/ordenar', 'Api\ProfesionalesApi::ordenar');

        // Clientes APIs
        $routes->post('clientes/crear', 'Api\ClientesApi::crear');
        $routes->put('clientes/editar/(:num)', 'Api\ClientesApi::editar/$1');
        $routes->put('clientes/password/(:num)', 'Api\ClientesApi::password/$1');
        $routes->put('clientes/toggle-estado/(:num)', 'Api\ClientesApi::toggleEstado/$1');
        $routes->delete('clientes/eliminar/(:num)', 'Api\ClientesApi::eliminar/$1');
        $routes->post('clientes/convertir/(:num)', 'Api\ClientesApi::convertir/$1');
    });

    // ---------------------------------------------------------------------
    // API: PERFIL COMPARTIDO (ADMINISTRADOR, PROFESIONAL Y CLIENTE)
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin,profesional,cliente'], function ($routes) {
        $routes->post('perfil/actualizar', 'Api\PerfilApi::actualizar');
        $routes->post('perfil/password', 'Api\PerfilApi::password');
        $routes->post('perfil/avatar', 'Api\PerfilApi::avatar');
    });

    // ---------------------------------------------------------------------
    // API: FUNCIONES DE CLIENTE 
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:cliente'], function ($routes) {
        // GET: consulta los horarios disponibles para reservar un turno,
        $routes->get('reservas/disponibilidad', 'Api\ReservasApi::disponibilidad');
        // POST: procesa y confirma la reserva de un turno.
        $routes->post('reservas/confirmar', 'Api\ReservasApi::confirmar');
        // PUT: permite cancelar un turno previamente reservado.
        $routes->put('cliente/turnos/cancelar/(:num)', 'Api\ReservasApi::cancelarTurno/$1');
        // POST: genera o descarga la información necesaria para
        // incorporar el turno seleccionado al calendario de Google.
        $routes->post('cliente/turnos/google-calendar/(:num)', 'Api\ReservasApi::exportarGoogleCalendar/$1');
    });

    // ---------------------------------------------------------------------
    // API: FUNCIONES COMPARTIDAS ENTRE ADMINISTRADOR Y PROFESIONAL
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin,profesional'], function ($routes) {
        // Dashboard Profesional
        $routes->get('dashboard/profesional', 'Api\DashboardApi::profesionalStats');

        // Redes sociales de profesional
        $routes->post('perfil/redes', 'Api\PerfilApi::redes');

        // -------------------------
        // GESTIÓN DE HORARIOS Y DISPONIBILIDADES
        // -------------------------

        // GET: obtiene los eventos del calendario, incluyendo
        // los horarios y disponibilidades correspondientes.
        $routes->get('horarios/eventos', 'Api\HorariosApi::eventos');

        // GET: consulta los servicios que tiene asignados el profesional.
        $routes->get('horarios/servicios', 'Api\HorariosApi::serviciosProfesional');

        // POST: registra una nueva disponibilidad horaria.
        $routes->post('horarios/crear', 'Api\HorariosApi::crear');

        // PUT: modifica una disponibilidad existente.
        $routes->put('horarios/editar/(:num)', 'Api\HorariosApi::editar/$1');

        // DELETE: elimina una disponibilidad horaria.
        $routes->delete('horarios/eliminar/(:num)', 'Api\HorariosApi::eliminar/$1');

        // -------------------------
        // GESTIÓN DE TURNOS (CU07)
        // -------------------------

        // GET: obtiene el listado de turnos correspondiente al usuario.
        $routes->get('turnos/listar', 'Api\TurnosApi::listar');

        // GET: obtiene los clientes necesarios para gestionar los turnos.
        $routes->get('turnos/clientes', 'Api\TurnosApi::clientes');

        // POST: permite registrar un nuevo turno desde el panel.
        $routes->post('turnos/crear', 'Api\TurnosApi::crear');

        // GET: consulta la información detallada de un turno específico.
        $routes->get('turnos/detalle/(:num)', 'Api\TurnosApi::detalle/$1');

        // PUT: actualiza el estado de un turno
        $routes->put('turnos/estado/(:num)', 'Api\TurnosApi::actualizarEstado/$1');

        // PUT: actualiza la información relacionada con el pago de un turno.
        $routes->put('turnos/pago/(:num)', 'Api\TurnosApi::actualizarPago/$1');

        // PUT: modifica la duración asignada a un turno.
        $routes->put('turnos/duracion/(:num)', 'Api\TurnosApi::actualizarDuracion/$1');

        // POST: genera o descarga la información necesaria para
        // incorporar un turno al calendario de Google.
        $routes->post('turnos/google-calendar/(:num)', 'Api\TurnosApi::exportarGoogleCalendar/$1');
    });
});

// =========================================================================
// PANEL DE ADMINISTRACIÓN / PROFESIONAL (Vistas Web)
// =========================================================================
$routes->group('admin', function ($routes) {
    $routes->get('logout', 'Admin::logout');

    // ---------------------------------------------------------------------
    // VISTAS EXCLUSIVAS DEL ADMINISTRADOR
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin'], function ($routes) {
        $routes->get('profesionales', 'Admin::profesionales');
        $routes->get('servicios', 'Admin::servicios');
        $routes->get('clientes', 'Admin::clientes');
    });

    // ---------------------------------------------------------------------
    // VISTAS COMPARTIDAS ENTRE ADMINISTRADOR Y PROFESIONAL
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin,profesional'], function ($routes) {
        $routes->get('/', 'Admin::dashboard');
        $routes->get('turnos', 'Admin::turnos');
        $routes->get('horarios', 'Admin::horarios');
        $routes->get('calendario', 'Admin::calendario');
        $routes->get('perfil', 'Admin::perfil');
    });
});
