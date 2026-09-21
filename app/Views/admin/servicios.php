<?php
$pageTitle = 'Servicios';
$activeNav = 'servicios';
$this->extend('admin/_layout');
$this->section('content');
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Servicios</h2>
        <p class="admin-page-header__subtitle">Tratamientos y servicios que ofrece el centro</p>
    </div>
    <a href="#" class="btn btn--primary" style="gap:0.5rem; font-size:0.75rem;">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        Nuevo servicio
    </a>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">Listado de servicios</h3>
    </div>

    <?php if (!empty($servicios)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Duración</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicios as $s): ?>
                <tr>
                    <td>
                        <?php if (!empty($s['imagen_ruta'])): ?>
                            <img src="<?= esc($s['imagen_ruta']) ?>"
                                 style="width:3.5rem; height:2.5rem; object-fit:cover; border-radius:var(--radius-md);"
                                 alt="<?= esc($s['nombre']) ?>" />
                        <?php else: ?>
                            <span class="table-avatar-placeholder" style="width:3.5rem; height:2.5rem; border-radius:var(--radius-md);">
                                <span class="material-symbols-outlined" style="font-size:1.1rem;">image</span>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <p class="table-user-name"><?= esc($s['nombre']) ?></p>
                    </td>
                    <td style="max-width: 18rem; white-space: normal; font-size: 0.82rem; color: var(--color-on-surface-variant);">
                        <?= esc(mb_strimwidth($s['descripcion'] ?? '', 0, 70, '…')) ?>
                    </td>
                    <td><?= esc($s['duracion_minutos']) ?> min</td>
                    <td style="font-weight:600;">$<?= number_format($s['precio'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($s['activo']): ?>
                            <span class="badge badge--green">Activo</span>
                        <?php else: ?>
                            <span class="badge badge--gray">Inactivo</span>
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
    
    <!-- Paginación -->
    <?php if (isset($pager_links) && $pager_links): ?>
        <div class="d-flex justify-content-between align-items-center mt-4 px-3" style="flex-wrap: wrap; gap: 1rem;">
            <span style="font-size: 0.85rem; color: var(--color-on-surface-variant); font-weight: 500;">
                Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> Servicios
            </span>
            <div>
                <?= $pager_links ?>
            </div>
        </div>
    <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">spa</span>
            <p class="admin-empty-state__text">Aún no hay servicios registrados.</p>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
