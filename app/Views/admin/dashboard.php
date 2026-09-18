<?php
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$this->extend('admin/_layout');
$this->section('content');
?>

<!-- ===== STAT CARDS ===== -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-card__icon">
            <span class="material-symbols-outlined">group</span>
        </div>
        <p class="stat-card__value"><?= count($profesionales ?? []) ?></p>
        <p class="stat-card__label">Profesionales</p>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#ffd3c8; color:#7a5950;">
            <span class="material-symbols-outlined">spa</span>
        </div>
        <p class="stat-card__value"><?= count($servicios ?? []) ?></p>
        <p class="stat-card__label">Servicios activos</p>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#e2ede6; color:#2d6a4f;">
            <span class="material-symbols-outlined">calendar_month</span>
        </div>
        <p class="stat-card__value"><?= count($turnosHoy ?? []) ?></p>
        <p class="stat-card__label">Turnos hoy</p>
    </div>
    <div class="stat-card">
        <div class="stat-card__icon" style="background:#dce9f7; color:#1e4f8a;">
            <span class="material-symbols-outlined">people</span>
        </div>
        <p class="stat-card__value"><?= $totalClientes ?? 0 ?></p>
        <p class="stat-card__label">Clientes registrados</p>
    </div>
</div>

<!-- ===== ACCESOS RÁPIDOS ===== -->
<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Accesos rápidos</h2>
        <p class="admin-page-header__subtitle">Gestioná las secciones principales del centro desde aquí</p>
    </div>
</div>

<div class="admin-quick-grid">
    <a href="/admin/profesionales" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--primary">
            <span class="material-symbols-outlined">group</span>
        </div>
        <p class="quick-card__title">Profesionales</p>
        <p class="quick-card__desc">Administrá el equipo de profesionales del centro: perfiles, títulos y especialidades.</p>
        <span class="quick-card__arrow">
            Ver profesionales <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="/admin/servicios" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--rose">
            <span class="material-symbols-outlined">spa</span>
        </div>
        <p class="quick-card__title">Servicios</p>
        <p class="quick-card__desc">Gestioná los servicios que ofrece el centro: precios, descripciones e imágenes.</p>
        <span class="quick-card__arrow">
            Ver servicios <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="/admin/turnos" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--green">
            <span class="material-symbols-outlined">calendar_month</span>
        </div>
        <p class="quick-card__title">Mis Turnos</p>
        <p class="quick-card__desc">Revisá todos los turnos agendados, su estado y los datos del cliente.</p>
        <span class="quick-card__arrow">
            Ver turnos <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="/admin/horarios" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--blue">
            <span class="material-symbols-outlined">schedule</span>
        </div>
        <p class="quick-card__title">Mis Horarios</p>
        <p class="quick-card__desc">Configurá tu disponibilidad horaria para que los clientes puedan reservar turnos.</p>
        <span class="quick-card__arrow">
            Ver horarios <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="/admin/perfil" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--purple">
            <span class="material-symbols-outlined">manage_accounts</span>
        </div>
        <p class="quick-card__title">Mi Perfil</p>
        <p class="quick-card__desc">Actualizá tu información personal, foto de perfil y contraseña de acceso.</p>
        <span class="quick-card__arrow">
            Ver perfil <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>
</div>

<?php $this->endSection(); ?>
