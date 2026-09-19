<?php
/**
 * Layout compartido del Panel Administración
 * Her Beauty Center
 *
 * Variables esperadas:
 * - $pageTitle (string) - Título de la página actual
 * - $activeNav (string) - ID del nav link activo
 * - $usuario   (array)  - Datos del usuario logueado
 */

$pageTitle  = $pageTitle ?? 'Panel';
$activeNav  = $activeNav  ?? 'dashboard';
$nombre     = $usuario['nombre'] ?? 'Administradora';
$avatar     = $usuario['avatar'] ?? null;

$navItems = [
    'dashboard'     => ['label' => 'Dashboard',      'icon' => 'dashboard',         'href' => base_url('admin')],
    'profesionales' => ['label' => 'Profesionales',  'icon' => 'group',             'href' => base_url('admin/profesionales')],
    'servicios'     => ['label' => 'Servicios',       'icon' => 'spa',               'href' => base_url('admin/servicios')],
    'turnos'        => ['label' => 'Mis Turnos',      'icon' => 'calendar_month',    'href' => base_url('admin/turnos')],
    'horarios'      => ['label' => 'Mis Horarios',    'icon' => 'schedule',          'href' => base_url('admin/horarios')],
    'perfil'        => ['label' => 'Mi Perfil',       'icon' => 'manage_accounts',   'href' => base_url('admin/perfil')],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($pageTitle) ?> | Admin — Her Beauty Center</title>
    <meta name="robots" content="noindex, nofollow" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin.css') ?>" />
</head>
<body class="admin-body">

    <!-- Overlay para cerrar sidebar en móvil -->
    <div class="admin-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- Brand -->
        <div class="admin-sidebar__brand">
            <img src="<?= base_url('public/assets/media/logoText.jpeg') ?>" alt="Her Beauty Center" class="admin-sidebar__logo" />
            <p class="admin-sidebar__brand-text">Her Beauty Center</p>
            <p class="admin-sidebar__subtitle">Panel de Administración</p>
        </div>

        <!-- Navigation -->
        <nav class="admin-sidebar__nav" aria-label="Navegación principal">
            <span class="admin-nav-label">Menú</span>

            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="admin-nav-link <?= ($activeNav === $key) ? 'admin-nav-link--active' : '' ?>"
                   aria-current="<?= ($activeNav === $key) ? 'page' : 'false' ?>">
                    <span class="material-symbols-outlined"><?= $item['icon'] ?></span>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>

            <span class="admin-nav-label" style="margin-top: 0.5rem;">Cuenta</span>
            <a href="<?= base_url('admin/logout') ?>" class="admin-nav-link">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </nav>

        <!-- User Footer -->
        <div class="admin-sidebar__footer">
            <div class="admin-sidebar__user">
                <div class="admin-sidebar__avatar">
                    <?php if ($avatar): ?>
                        <img src="<?= esc($avatar) ?>" alt="Avatar" />
                    <?php else: ?>
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">person</span>
                    <?php endif; ?>
                </div>
                <div class="admin-sidebar__user-info">
                    <p class="admin-sidebar__user-name"><?= esc($nombre) ?></p>
                    <p class="admin-sidebar__user-role">Administradora</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN AREA ===== -->
    <div class="admin-main">

        <!-- Top Bar -->
        <header class="admin-topbar">
            <div style="display:flex; align-items:center; gap: 0.75rem;">
                <!-- Botón hamburguesa (solo móvil) -->
                <button class="admin-topbar__icon-btn admin-topbar__menu-btn" id="sidebarToggle" aria-label="Abrir menú">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h1 class="admin-topbar__title"><?= esc($pageTitle) ?></h1>
            </div>
            <div class="admin-topbar__actions">
                <a href="<?= base_url() ?>" class="admin-topbar__icon-btn" title="Ver sitio web" target="_blank">
                    <span class="material-symbols-outlined">open_in_new</span>
                </a>
            </div>
        </header>

        <!-- Page Content (inyectado por cada vista) -->
        <div class="admin-content">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <!-- JS del panel -->
    <script>
        // Sidebar móvil toggle
        const sidebar  = document.getElementById('adminSidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('admin-sidebar--open');
                overlay.classList.toggle('admin-sidebar-overlay--visible');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('admin-sidebar--open');
                overlay.classList.remove('admin-sidebar-overlay--visible');
            });
        }
    </script>
</body>
</html>
