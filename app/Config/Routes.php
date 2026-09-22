<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Frontend Auth
$routes->post('/api/login', 'Auth::login');
$routes->post('/api/register', 'Auth::register');
$routes->match(['get', 'post'], '/logout', 'Auth::logout');
$routes->match(['get', 'post'], '/api/logout', 'Auth::logout');

// Compatibilidad en despliegues con prefijo /public
$routes->post('/public/api/login', 'Auth::login');
$routes->post('/public/api/register', 'Auth::register');
$routes->match(['get', 'post'], '/public/logout', 'Auth::logout');
$routes->match(['get', 'post'], '/public/api/logout', 'Auth::logout');

// Dashboard APIs
$routes->get('/api/dashboard/admin', 'Api\DashboardApi::adminStats');
$routes->get('/api/dashboard/profesional', 'Api\DashboardApi::profesionalStats');

// Servicios APIs
$routes->post('/api/servicios/crear', 'Api\ServiciosApi::crear');
$routes->post('/api/servicios/editar/(:num)', 'Api\ServiciosApi::editar/$1');
$routes->delete('/api/servicios/eliminar/(:num)', 'Api\ServiciosApi::eliminar/$1');
$routes->put('/api/servicios/profesionales/(:num)', 'Api\ServiciosApi::profesionales/$1');
$routes->post('/api/servicios/ordenar', 'Api\ServiciosApi::ordenar');

// Profesionales APIs
$routes->post('/api/profesionales/crear', 'Api\ProfesionalesApi::crear');
$routes->post('/api/profesionales/servicios', 'Api\ProfesionalesApi::servicios');
$routes->put('/api/profesionales/editar/(:num)', 'Api\ProfesionalesApi::editar/$1');
$routes->put('/api/profesionales/password/(:num)', 'Api\ProfesionalesApi::password/$1');
$routes->delete('/api/profesionales/eliminar/(:num)', 'Api\ProfesionalesApi::eliminar/$1');

// Clientes APIs
$routes->post('/api/clientes/crear', 'Api\ClientesApi::crear');
$routes->put('/api/clientes/editar/(:num)', 'Api\ClientesApi::editar/$1');
$routes->put('/api/clientes/password/(:num)', 'Api\ClientesApi::password/$1');
$routes->delete('/api/clientes/eliminar/(:num)', 'Api\ClientesApi::eliminar/$1');
$routes->post('/api/clientes/convertir/(:num)', 'Api\ClientesApi::convertir/$1');

// Panel de Administración
$routes->get('/admin/logout',   'Admin::logout');
$routes->get('/admin',          'Admin::dashboard');
$routes->get('/admin/profesionales', 'Admin::profesionales');
$routes->get('/admin/servicios',     'Admin::servicios');
$routes->get('/admin/turnos',        'Admin::turnos');
$routes->get('/admin/perfil',        'Admin::perfil');
$routes->get('/admin/horarios',      'Admin::horarios');
$routes->get('/admin/clientes',      'Admin::clientes');
