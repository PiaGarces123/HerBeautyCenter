<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

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
