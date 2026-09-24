<?php
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$this->extend('profesional/_layout');
$this->section('content');
?>

<!-- ===== STAT CARDS (Cargados por API) ===== -->
<div class="admin-stats-grid" id="dashboardCardsContainer">
    <!-- El contenido se cargará por JS dependiendo del rol -->
    <div class="stat-card placeholder-glow"><span class="placeholder col-12" style="height:100px;"></span></div>
    <div class="stat-card placeholder-glow"><span class="placeholder col-12" style="height:100px;"></span></div>
    <div class="stat-card placeholder-glow"><span class="placeholder col-12" style="height:100px;"></span></div>
    <div class="stat-card placeholder-glow"><span class="placeholder col-12" style="height:100px;"></span></div>
</div>

<!-- ===== ACCESOS RÁPIDOS ===== -->
<div class="admin-page-header mt-5">
    <div>
        <h2 class="admin-page-header__title">Accesos rápidos</h2>
        <p class="admin-page-header__subtitle">Gestioná las secciones principales del centro desde aquí</p>
    </div>
</div>

<div class="admin-quick-grid">
    <?php if ($rol === 'admin'): ?>
    <a href="<?= base_url('admin/profesionales') ?>" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--primary">
            <span class="material-symbols-outlined">group</span>
        </div>
        <p class="quick-card__title">Profesionales</p>
        <p class="quick-card__desc">Administrá el equipo de profesionales del centro: perfiles, títulos y especialidades.</p>
        <span class="quick-card__arrow">
            Ver profesionales <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="<?= base_url('admin/servicios') ?>" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--rose">
            <span class="material-symbols-outlined">spa</span>
        </div>
        <p class="quick-card__title">Servicios</p>
        <p class="quick-card__desc">Gestioná los servicios que ofrece el centro: precios, descripciones e imágenes.</p>
        <span class="quick-card__arrow">
            Ver servicios <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>
    <?php endif; ?>

    <a href="<?= base_url('admin/turnos') ?>" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--green">
            <span class="material-symbols-outlined">calendar_month</span>
        </div>
        <p class="quick-card__title">Mis Turnos</p>
        <p class="quick-card__desc">Revisá todos los turnos agendados, su estado y los datos del cliente.</p>
        <span class="quick-card__arrow">
            Ver turnos <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="<?= base_url('admin/horarios') ?>" class="quick-card">
        <div class="quick-card__icon-wrap quick-card__icon-wrap--blue">
            <span class="material-symbols-outlined">schedule</span>
        </div>
        <p class="quick-card__title">Mis Horarios</p>
        <p class="quick-card__desc">Configurá tu disponibilidad horaria para que los clientes puedan reservar turnos.</p>
        <span class="quick-card__arrow">
            Ver horarios <span class="material-symbols-outlined" style="font-size:1rem;">arrow_forward</span>
        </span>
    </a>

    <a href="<?= base_url('admin/perfil') ?>" class="quick-card">
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

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const rol = "<?= esc($rol) ?>";
    const container = document.getElementById('dashboardCardsContainer');
    
    let htmlContent = '';
    
    try {
        // Fetch Admin Stats si es admin
        if (rol === 'admin') {
            const resAdmin = await fetch('<?= base_url("api/dashboard/admin") ?>');
            if (resAdmin.ok) {
                const dataAdmin = await resAdmin.json();
                htmlContent += `
                    <div class="stat-card">
                        <span class="material-symbols-outlined stat-card__watermark">spa</span>
                        <div class="stat-card__icon" style="background:#ffd3c8; color:#7a5950;">
                            <span class="material-symbols-outlined">spa</span>
                        </div>
                        <p class="stat-card__value" style="font-size: 1.2rem;">${dataAdmin.masSolicitado.nombre}</p>
                        <p class="stat-card__label" style="font-size: 0.8rem;">Servicio más solicitado</p>
                        <p class="text-muted" style="font-size:0.75rem;">${dataAdmin.masSolicitado.total} turnos <br><strong style="color:var(--color-primary);">${dataAdmin.masSolicitado.tendencia}</strong></p>
                    </div>
                    <div class="stat-card">
                        <span class="material-symbols-outlined stat-card__watermark">trending_down</span>
                        <div class="stat-card__icon" style="background:#fce4ec; color:#880e4f;">
                            <span class="material-symbols-outlined">trending_down</span>
                        </div>
                        <p class="stat-card__value" style="font-size: 1.2rem;">${dataAdmin.menosSolicitado.nombre}</p>
                        <p class="stat-card__label" style="font-size: 0.8rem;">Servicio menos solicitado</p>
                        <p class="text-muted" style="font-size:0.75rem;">${dataAdmin.menosSolicitado.total} turnos</p>
                    </div>
                    <div class="stat-card">
                        <span class="material-symbols-outlined stat-card__watermark">group</span>
                        <div class="stat-card__icon" style="background:#dce9f7; color:#1e4f8a;">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <p class="stat-card__value">${dataAdmin.clientes}</p>
                        <p class="stat-card__label">Cantidad de Clientes</p>
                    </div>
                    <div class="stat-card">
                        <span class="material-symbols-outlined stat-card__watermark">badge</span>
                        <div class="stat-card__icon">
                            <span class="material-symbols-outlined">badge</span>
                        </div>
                        <p class="stat-card__value">${dataAdmin.profesionales}</p>
                        <p class="stat-card__label">Cantidad de Profesionales</p>
                    </div>
                `;
            }
        }
        
        // Fetch Profesional Stats
        const resProf = await fetch('<?= base_url("api/dashboard/profesional") ?>');
        if (resProf.ok) {
            const dataProf = await resProf.json();
            htmlContent += `
                <div class="stat-card">
                    <span class="material-symbols-outlined stat-card__watermark">today</span>
                    <div class="stat-card__icon" style="background:#e2ede6; color:#2d6a4f;">
                        <span class="material-symbols-outlined">today</span>
                    </div>
                    <p class="stat-card__value">${dataProf.turnosHoy}</p>
                    <p class="stat-card__label">Mis Turnos Hoy</p>
                </div>
                <div class="stat-card">
                    <span class="material-symbols-outlined stat-card__watermark">event_upcoming</span>
                    <div class="stat-card__icon" style="background:#e8eaf6; color:#283593;">
                        <span class="material-symbols-outlined">event_upcoming</span>
                    </div>
                    <p class="stat-card__value">${dataProf.proximosTurnos}</p>
                    <p class="stat-card__label">Próximos Turnos</p>
                </div>
                <div class="stat-card">
                    <span class="material-symbols-outlined stat-card__watermark">star</span>
                    <div class="stat-card__icon" style="background:#fff3e0; color:#e65100;">
                        <span class="material-symbols-outlined">star</span>
                    </div>
                    <p class="stat-card__value" style="font-size: 1.2rem;">${dataProf.misServiciosMasSolicitados.nombre}</p>
                    <p class="stat-card__label" style="font-size: 0.8rem;">Mi servicio más solicitado</p>
                    <p class="text-muted" style="font-size:0.75rem;">${dataProf.misServiciosMasSolicitados.total} turnos</p>
                </div>
                <div class="stat-card">
                    <span class="material-symbols-outlined stat-card__watermark">list_alt</span>
                    <div class="stat-card__icon" style="background:#e0f7fa; color:#006064;">
                        <span class="material-symbols-outlined">list_alt</span>
                    </div>
                    <p class="stat-card__value">${dataProf.serviciosOfrecidos}</p>
                    <p class="stat-card__label">Servicios que ofrezco</p>
                </div>
            `;
        }
        
        if (htmlContent === '') {
            htmlContent = '<p class="text-muted">No se pudieron cargar las estadísticas.</p>';
        }
        
        container.innerHTML = htmlContent;
        
    } catch (error) {
        console.error("Error al cargar dashboard stats:", error);
        container.innerHTML = '<p class="text-danger">Hubo un error de conexión al cargar los datos.</p>';
    }
});
</script>

<?php $this->endSection(); ?>
