<!-- Header / Navigation Bar -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <!-- Mobile Hamburger Button -->
        <button aria-label="Menu" class="header-menu-btn" id="mobileMenuOpen">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Brand Logo -->
        <a class="header-logo" href="#">
            <img src="public/assets/media/logoText.png" alt="Logo Her Beauty Center" class="header-logo__img" />
        </a>

        <!-- Desktop Nav -->
        <nav class="header-nav">
            <a class="header-link" href="#servicios">Servicios</a>
            <a class="header-link" href="#profesionales">Profesionales</a>
            <a class="header-link" href="#nosotros">Nosotros</a>
            <a class="header-link" href="#contacto">Contacto</a>
        </nav>

        <!-- Trailing Profile / User Section —
             Si hay sesión iniciada, muestra el badge de usuario o el link al panel admin.
             Si NO hay sesión, muestra el botón de login que abre el modal (loginModal). -->
        <?php if (function_exists('session') && session()->get('usuario_id')): ?>
            <!-- Contenedor del badge de usuario + botón de logout -->
            <div class="header-user-section">
                <?php if (session()->get('rol') === 'admin'): ?>
                    <!-- Enlace al panel admin (sólo visible para rol admin) -->
                    <a href="<?= base_url('admin') ?>" aria-label="Panel Admin"
                        class="header-profile-btn btn btn--outline header-admin-link">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Panel Admin</span>
                    </a>
                <?php else: ?>
                    <!-- Badge/pastilla con el nombre del usuario logueado -->
                    <div class="header-user-badge">
                        <span class="material-symbols-outlined">account_circle</span>
                        <span>
                            <?= esc(explode(' ', session()->get('usuario_nombre') ?? '')[0]) ?>
                        </span>
                    </div>
                <?php endif; ?>
                <!-- Botón de cerrar sesión (visible para todos los usuarios logueados) -->
                <a href="<?= base_url('logout') ?>" class="btn btn--outline header-logout-link"
                    title="Cerrar sesión" aria-label="Cerrar sesión">
                    <span>Cerrar Sesión</span>
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
