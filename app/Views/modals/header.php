<!-- Header / Navigation Bar -->
<header class="main-header" id="mainHeader">
    <div class="header-container">
        <!-- Mobile Hamburger Button -->
        <button aria-label="Menu" class="header-menu-btn" id="mobileMenuOpen">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Brand Logo -->
        <a class="header-logo" href="#">
            <img src="public/assets/media/logoText.jpeg" alt="Logo Her Beauty Center" class="header-logo__img" />
        </a>

        <!-- Desktop Nav -->
        <nav class="header-nav">
            <a class="header-link" href="#servicios">Servicios</a>
            <a class="header-link" href="#profesionales">Profesionales</a>
            <a class="header-link" href="#nosotros">Nosotros</a>
            <a class="header-link" href="#contacto">Contacto</a>
        </nav>

        <!-- Trailing Profile / User Section -->
        <?php if (function_exists('session') && session()->get('usuario_id')): ?>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <?php if (session()->get('rol') === 'admin'): ?>
                    <a href="<?= base_url('admin') ?>" aria-label="Panel Admin" class="header-profile-btn btn btn--outline"
                        style="text-decoration: none; display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                        <span class="material-symbols-outlined" style="font-size: 1.15rem;">dashboard</span>
                        <span>Panel Admin</span>
                    </a>
                <?php else: ?>
                    <div class="header-user-badge"
                        style="display: flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.8rem; background: var(--color-surface-container-high); border-radius: 9999px; font-size: 0.85rem; font-weight: 500;">
                        <span class="material-symbols-outlined"
                            style="font-size: 1.15rem; color: var(--color-primary);">account_circle</span>
                        <span>
                            <?= esc(explode(' ', session()->get('usuario_nombre') ?? '')[0]) ?>
                        </span>
                    </div>
                <?php endif; ?>
                <a href="<?= base_url('logout') ?>" class="btn btn--outline" title="Cerrar sesión"
                    style="text-decoration: none; display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.85rem; font-size: 0.85rem; border-color: var(--color-outline-variant);"
                    aria-label="Cerrar sesión">
                    <span>Cerrar Sesión</span>
                    <span class="material-symbols-outlined"
                        style="font-size: 1.15rem; color: var(--color-on-surface-variant);">logout</span>
                </a>
            </div>
        <?php else: ?>
            <button aria-label="Perfil" class="header-profile-btn" id="loginBtn"
                data-bs-toggle="modal" data-bs-target="#loginModal">
                <span class="material-symbols-outlined">person</span>
            </button>
        <?php endif; ?>
    </div>
</header>