<?php
$pageTitle = 'Profesionales';
$activeNav = 'profesionales';
$this->extend('admin/_layout');
$this->section('content');
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Profesionales</h2>
        <p class="admin-page-header__subtitle">Equipo de especialistas del centro</p>
    </div>
    <a href="#" class="btn btn--primary" style="gap:0.5rem; font-size:0.75rem;">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        Nueva profesional
    </a>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">Listado de profesionales</h3>
    </div>

    <?php if (!empty($profesionales)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Profesional</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Año de inicio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profesionales as $prof): ?>
                <tr>
                    <td>
                        <div class="table-user-cell">
                            <?php if (!empty($prof['avatar'])): ?>
                                <img src="<?= esc($prof['avatar']) ?>" class="table-avatar" alt="Avatar" />
                            <?php else: ?>
                                <span class="table-avatar-placeholder">
                                    <span class="material-symbols-outlined" style="font-size:1rem;">person</span>
                                </span>
                            <?php endif; ?>
                            <div>
                                <p class="table-user-name"><?= esc($prof['nombre_completo']) ?></p>
                                <p class="table-user-email"><?= esc($prof['correo']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td><?= esc($prof['titulo'] ?? '—') ?></td>
                    <td style="max-width: 18rem; white-space: normal; font-size: 0.82rem; color: var(--color-on-surface-variant);">
                        <?= esc(mb_strimwidth($prof['descripcion'] ?? '', 0, 80, '…')) ?>
                    </td>
                    <td><?= esc($prof['anio_inicio_actividades'] ?? '—') ?></td>
                    <td>
                        <?php if ($prof['activo']): ?>
                            <span class="badge badge--green">Activa</span>
                        <?php else: ?>
                            <span class="badge badge--gray">Inactiva</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="table-btn table-btn--edit" title="Editar">
                                <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>
                            </button>
                            <button class="table-btn table-btn--delete" title="Eliminar">
                                <span class="material-symbols-outlined" style="font-size:1rem;">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">group_off</span>
            <p class="admin-empty-state__text">Aún no hay profesionales registradas.</p>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
