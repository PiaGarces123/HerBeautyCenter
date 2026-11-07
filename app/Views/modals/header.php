<!-- Header / Navigation Bar -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <!-- Mobile Hamburger Button -->
        <button aria-label="Menu" class="header-menu-btn" id="mobileMenuOpen">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Brand Logo -->
        <a class="header-logo" href="<?= base_url('/') ?>">
            <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Logo Her Beauty Center" class="header-logo__img" />
        </a>

        <!-- Desktop Nav -->
        <nav class="header-nav">
            <a class="header-link" href="<?= base_url('/#servicios') ?>">Servicios</a>
            <a class="header-link" href="<?= base_url('/#profesionales') ?>">Profesionales</a>
            <a class="header-link" href="<?= base_url('/#nosotros') ?>">Nosotros</a>
            <a class="header-link" href="<?= base_url('/#contacto') ?>">Contacto</a>
        </nav>

        <!-- Trailing Profile / User Section -->
        <?php if (function_exists('session') && session()->get('usuario_id')): ?>
            <div class="header-user-section">
                <?php if (in_array(session()->get('rol'), ['admin', 'profesional'], true)): ?>
                    <a href="<?= base_url('admin') ?>" aria-label="Panel de Gestión"
                        class="btn btn--outline header-admin-link"
                        title="<?= session()->get('rol') === 'admin' ? 'Panel Admin' : 'Mi Agenda' ?>">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="header-admin-text"><?= session()->get('rol') === 'admin' ? 'Panel Admin' : 'Mi Agenda' ?></span>
                    </a>
                <?php else: ?>
                    <!-- Accesos directos del Panel del Cliente (CU05) -->
                    <a href="<?= base_url('mis-turnos') ?>" aria-label="Mis Turnos"
                        class="btn btn--outline header-admin-link"
                        title="Mis Turnos">
                        <span class="material-symbols-outlined">calendar_month</span>
                        <span class="header-admin-text">Mis Turnos</span>
                    </a>
                    <a href="<?= base_url('mi-perfil') ?>" aria-label="Mi Perfil"
                        class="header-user-badge text-decoration-none" title="Ir a Mi Perfil">
                        <span class="material-symbols-outlined">account_circle</span>
                        <span><?= esc(explode(' ', session()->get('usuario_nombre') ?? '')[0]) ?></span>
                    </a>
                <?php endif; ?>
                <!-- Botón de cerrar sesión -->
                <a href="<?= base_url('logout') ?>" class="btn btn--outline header-logout-link"
                    title="Cerrar sesión" aria-label="Cerrar sesión">
                    <span class="header-logout-text">Cerrar Sesión</span>
                    <span class="material-symbols-outlined">logout</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Botón que abre el modal de login (para usuarios no logueados) -->
            <button aria-label="Perfil" class="header-profile-btn" id="loginBtn" data-bs-toggle="modal"
                data-bs-target="#loginModal">
                <span class="material-symbols-outlined">person</span>
            </button>
        <?php endif; ?>
    </div>
</header>

<!-- Mobile Navigation Drawer (compartido en todas las vistas públicas y de cliente) -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu__overlay" id="mobileMenuOverlay"></div>
    <nav class="mobile-menu__content">
        <button aria-label="Cerrar menú" class="mobile-menu__close-btn" id="mobileMenuClose">
            <span class="material-symbols-outlined">close</span>
        </button>
        <a class="mobile-menu__logo" href="<?= base_url('/') ?>">
            <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Her Beauty Center" class="mobile-menu__logo-img" />
        </a>
        <ul class="mobile-menu__links">
            <li><a class="mobile-menu__link" href="<?= base_url('/#servicios') ?>">Servicios</a></li>
            <li><a class="mobile-menu__link" href="<?= base_url('/#profesionales') ?>">Profesionales</a></li>
            <li><a class="mobile-menu__link" href="<?= base_url('/#nosotros') ?>">Nosotros</a></li>
            <li><a class="mobile-menu__link" href="<?= base_url('/#contacto') ?>">Contacto</a></li>
            <li class="mobile-menu__user-section">
                <?php if (function_exists('session') && session()->get('usuario_id')): ?>
                    <div class="mobile-menu__user-buttons">
                        <?php if (in_array(session()->get('rol'), ['admin', 'profesional'], true)): ?>
                            <a class="btn mobile-menu__btn mobile-menu__btn--admin" href="<?= base_url('admin') ?>">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span><?= session()->get('rol') === 'admin' ? 'Panel Admin' : 'Mi Agenda' ?></span>
                            </a>
                        <?php else: ?>
                            <a class="btn mobile-menu__btn mobile-menu__btn--admin" href="<?= base_url('mis-turnos') ?>">
                                <span class="material-symbols-outlined">calendar_month</span>
                                <span>Mis Turnos</span>
                            </a>
                            <a class="btn btn--outline mobile-menu__btn" href="<?= base_url('mi-perfil') ?>">
                                <span class="material-symbols-outlined">account_circle</span>
                                <span>Mi Perfil (<?= esc(explode(' ', session()->get('usuario_nombre') ?? '')[0]) ?>)</span>
                            </a>
                        <?php endif; ?>
                        <a class="btn mobile-menu__btn mobile-menu__btn--logout" href="<?= base_url('logout') ?>">
                            <span class="material-symbols-outlined">logout</span>
                            <span>Cerrar Sesión</span>
                        </a>
                    </div>
                <?php else: ?>
                    <button class="btn btn--primary mobile-menu__login-btn" id="mobileLoginBtn" data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                        <span class="material-symbols-outlined">login</span> Iniciar Sesión / Registro
                    </button>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</div>
