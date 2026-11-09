<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Marco Legal, Términos y Política de Privacidad (Ley 25.326)
$routes->get('politica-de-privacidad', 'Home::politicaPrivacidad');
$routes->get('terminos-y-condiciones', 'Home::politicaPrivacidad');

// =========================================================================
// VISTAS PÚBLICAS Y DEL CLIENTE (Incremento N° 3)
// =========================================================================
// CU01: Vista de detalle de servicio con carrusel de fotos (acceso público)
$routes->get('servicio/(:num)', 'Cliente::servicio/$1');

// Rutas web exclusivas para Clientes (CU04 y CU05)
$routes->group('', ['filter' => 'auth:cliente'], function ($routes) {
    // CU04: Flujo de solicitud y reserva de turnos
    $routes->get('reservar/(:num)',           'Cliente::reservar/$1');
    $routes->get('reservar/confirmar/(:num)', 'Cliente::confirmar/$1');

    // CU05: Panel del Cliente ("Mis Turnos" y "Mi Perfil")
    $routes->get('mis-turnos',                'Cliente::misTurnos');
    $routes->get('mi-perfil',                 'Cliente::perfil');
});

// Auth pública & Logout
$routes->match(['get', 'post'], '/logout', 'Auth::logout');

// Compatibilidad en despliegues con prefijo /public
$routes->post('/public/api/login', 'Auth::login');
$routes->post('/public/api/register', 'Auth::register');
$routes->match(['get', 'post'], '/public/logout', 'Auth::logout');
$routes->match(['get', 'post'], '/public/api/logout', 'Auth::logout');

// =========================================================================
// API ROUTES
// =========================================================================
$routes->group('api', function ($routes) {
    // Rutas públicas de autenticación
    $routes->post('login',    'Auth::login');
    $routes->post('register', 'Auth::register');
    $routes->match(['get', 'post'], 'logout', 'Auth::logout');

    // ---------------------------------------------------------------------
    // API: Exclusivo Administrador
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin'], function ($routes) {
        // Dashboard Admin
        $routes->get('dashboard/admin', 'Api\DashboardApi::adminStats');

        // Servicios APIs
        $routes->post('servicios/crear',                    'Api\ServiciosApi::crear');
        $routes->post('servicios/editar/(:num)',            'Api\ServiciosApi::editar/$1');
        $routes->delete('servicios/eliminar/(:num)',        'Api\ServiciosApi::eliminar/$1');
        $routes->put('servicios/profesionales/(:num)',      'Api\ServiciosApi::profesionales/$1');
        $routes->post('servicios/ordenar',                  'Api\ServiciosApi::ordenar');
        $routes->get('servicios/imagenes/(:num)',           'Api\ServiciosApi::imagenes/$1');
        $routes->post('servicios/imagenes/subir/(:num)',    'Api\ServiciosApi::subirImagenes/$1');
        $routes->post('servicios/imagenes/ordenar/(:num)',  'Api\ServiciosApi::ordenarImagenes/$1');
        $routes->delete('servicios/imagenes/eliminar/(:num)','Api\ServiciosApi::eliminarImagen/$1');

        // Profesionales APIs
        $routes->post('profesionales/crear',            'Api\ProfesionalesApi::crear');
        $routes->post('profesionales/servicios',        'Api\ProfesionalesApi::servicios');
        $routes->put('profesionales/editar/(:num)',     'Api\ProfesionalesApi::editar/$1');
        $routes->put('profesionales/password/(:num)',   'Api\ProfesionalesApi::password/$1');
        $routes->delete('profesionales/eliminar/(:num)','Api\ProfesionalesApi::eliminar/$1');
        $routes->post('profesionales/ordenar',          'Api\ProfesionalesApi::ordenar');

        // Clientes APIs
        $routes->post('clientes/crear',                 'Api\ClientesApi::crear');
        $routes->put('clientes/editar/(:num)',          'Api\ClientesApi::editar/$1');
        $routes->put('clientes/password/(:num)',        'Api\ClientesApi::password/$1');
        $routes->delete('clientes/eliminar/(:num)',     'Api\ClientesApi::eliminar/$1');
        $routes->post('clientes/convertir/(:num)',      'Api\ClientesApi::convertir/$1');
    });

    // ---------------------------------------------------------------------
    // API: Perfil compartido (Cliente, Profesional y Administrador)
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin,profesional,cliente'], function ($routes) {
        $routes->post('perfil/actualizar', 'Api\PerfilApi::actualizar');
        $routes->post('perfil/password',   'Api\PerfilApi::password');
        $routes->post('perfil/avatar',     'Api\PerfilApi::avatar');
    });

    // ---------------------------------------------------------------------
    // API: Exclusivo Cliente (Reservas y Turnos — CU04 y CU05)
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:cliente'], function ($routes) {
        $routes->get('reservas/disponibilidad',                  'Api\ReservasApi::disponibilidad');
        $routes->post('reservas/confirmar',                      'Api\ReservasApi::confirmar');
        $routes->put('cliente/turnos/cancelar/(:num)',           'Api\ReservasApi::cancelarTurno/$1');
        $routes->post('cliente/turnos/google-calendar/(:num)',   'Api\ReservasApi::exportarGoogleCalendar/$1');
    });

    // ---------------------------------------------------------------------
    // API: Administrador y Profesional (Incrementos 1 y 2)
    // ---------------------------------------------------------------------
    $routes->group('', ['filter' => 'auth:admin,profesional'], function ($routes) {
        // Dashboard Profesional
        $routes->get('dashboard/profesional', 'Api\DashboardApi::profesionalStats');

        // Redes sociales de profesional
        $routes->post('perfil/redes',      'Api\PerfilApi::redes');

        // Horarios / Disponibilidades (CU08)
        $routes->get('horarios/eventos',            'Api\HorariosApi::eventos');
        $routes->get('horarios/servicios',          'Api\HorariosApi::serviciosProfesional');
        $routes->post('horarios/crear',             'Api\HorariosApi::crear');
        $routes->put('horarios/editar/(:num)',      'Api\HorariosApi::editar/$1');
        $routes->delete('horarios/eliminar/(:num)', 'Api\HorariosApi::eliminar/$1');

        // Turnos (CU07)
        $routes->get('turnos/listar',                   'Api\TurnosApi::listar');
        $routes->get('turnos/clientes',                 'Api\TurnosApi::clientes');
        $routes->post('turnos/crear',                   'Api\TurnosApi::crear');
        $routes->get('turnos/detalle/(:num)',          'Api\TurnosApi::detalle/$1');
        $routes->put('turnos/estado/(:num)',           'Api\TurnosApi::actualizarEstado/$1');
        $routes->put('turnos/pago/(:num)',             'Api\TurnosApi::actualizarPago/$1');
        $routes->post('turnos/google-calendar/(:num)', 'Api\TurnosApi::exportarGoogleCalendar/$1');
    });
});

// =========================================================================
// PANEL DE ADMINISTRACIÓN / PROFESIONAL (Vistas Web)
// =========================================================================
$routes->group('admin', function ($routes) {
    $routes->get('logout', 'Admin::logout');

    // Vistas exclusivas Administrador
    $routes->group('', ['filter' => 'auth:admin'], function ($routes) {
        $routes->get('profesionales', 'Admin::profesionales');
        $routes->get('servicios',     'Admin::servicios');
        $routes->get('clientes',      'Admin::clientes');
    });

    // Vistas compartidas: Administrador y Profesional
    $routes->group('', ['filter' => 'auth:admin,profesional'], function ($routes) {
        $routes->get('/',           'Admin::dashboard');
        $routes->get('turnos',      'Admin::turnos');
        $routes->get('horarios',    'Admin::horarios');
        $routes->get('calendario',  'Admin::calendario');
        $routes->get('perfil',      'Admin::perfil');
    });
});
