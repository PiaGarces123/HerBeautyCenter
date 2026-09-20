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

// Panel de Administración
$routes->get('/admin/login',    'Admin::login');
$routes->post('/admin/login',   'Admin::loginPost');
$routes->get('/admin/logout',   'Admin::logout');
$routes->get('/admin',          'Admin::dashboard');
$routes->get('/admin/profesionales', 'Admin::profesionales');
$routes->get('/admin/servicios',     'Admin::servicios');
$routes->get('/admin/turnos',        'Admin::turnos');
$routes->get('/admin/perfil',        'Admin::perfil');
$routes->get('/admin/horarios',      'Admin::horarios');
