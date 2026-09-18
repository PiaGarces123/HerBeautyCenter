<?php
$pageTitle = 'Mi Perfil';
$activeNav = 'perfil';
$this->extend('admin/_layout');
$this->section('content');

$u = $usuario_data ?? [];
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mi Perfil</h2>
        <p class="admin-page-header__subtitle">Tu información personal y de acceso</p>
    </div>
</div>

<div class="admin-profile-grid">

    <!-- Card izquierda: avatar + resumen -->
    <div class="admin-profile-card">
        <?php if (!empty($u['avatar'])): ?>
            <img src="<?= esc($u['avatar']) ?>" class="admin-profile-card__avatar" alt="Foto de perfil" />
        <?php else: ?>
            <div class="admin-profile-card__avatar-placeholder">
                <span class="material-symbols-outlined">person</span>
            </div>
        <?php endif; ?>

        <p class="admin-profile-card__name"><?= esc($u['nombre_completo'] ?? '—') ?></p>
        <p class="admin-profile-card__role">Administradora</p>
        <p class="admin-profile-card__email"><?= esc($u['correo'] ?? '') ?></p>

        <?php if (!empty($u['fecha_registro'])): ?>
            <p style="font-size:0.75rem; color: var(--color-on-surface-variant); margin-top: 0.5rem;">
                Miembro desde <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
            </p>
        <?php endif; ?>

        <a href="#" class="btn btn--outline" style="width:100%; margin-top:1rem; font-size:0.75rem;">
            Cambiar foto
        </a>
    </div>

    <!-- Card derecha: formulario de edición -->
    <div class="admin-table-card" style="padding: 1.75rem;">
        <h3 class="admin-table-card__title" style="margin-bottom: 1.5rem;">Editar datos</h3>

        <form method="POST" action="/admin/perfil/actualizar">
            <?= csrf_field() ?>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem; margin-bottom:1.25rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="nombre_completo">Nombre completo</label>
                    <input class="form-input" type="text" id="nombre_completo" name="nombre_completo"
                           value="<?= esc($u['nombre_completo'] ?? '') ?>" required />
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input class="form-input" type="tel" id="telefono" name="telefono"
                           value="<?= esc($u['telefono'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input class="form-input" type="email" id="correo" name="correo"
                       value="<?= esc($u['correo'] ?? '') ?>" required />
            </div>

            <hr style="border:none; border-top: 1px solid var(--color-outline-variant); margin: 1.5rem 0;" />
            <h4 style="font-family: var(--font-serif); font-weight:400; color: var(--color-primary); margin-bottom: 1rem;">
                Cambiar contraseña
            </h4>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem; margin-bottom:1.5rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="password_nueva">Nueva contraseña</label>
                    <input class="form-input" type="password" id="password_nueva" name="password_nueva"
                           placeholder="Dejá en blanco para no cambiar" />
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="password_confirmar">Confirmar contraseña</label>
                    <input class="form-input" type="password" id="password_confirmar" name="password_confirmar"
                           placeholder="Repetí la nueva contraseña" />
                </div>
            </div>

            <button type="submit" class="btn btn--primary" style="font-size:0.75rem;">
                Guardar cambios
            </button>
        </form>
    </div>

</div>

<?php $this->endSection(); ?>
