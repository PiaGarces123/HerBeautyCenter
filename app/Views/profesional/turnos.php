<?php
$pageTitle = 'Mis Turnos';
$activeNav = 'turnos';
$this->extend('profesional/_layout');
$this->section('content');

$estadoBadge = [
    'Disponible'  => 'badge--blue',
    'Solicitado'  => 'badge--orange',
    'Confirmado'  => 'badge--green',
    'Realizado'   => 'badge--gray',
];
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mis Turnos</h2>
        <p class="admin-page-header__subtitle">Seguimiento de todos los turnos agendados</p>
    </div>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">Todos los turnos</h3>
    </div>

    <?php if (!empty($turnos)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Profesional</th>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Abonado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turnos as $t): ?>
                <tr>
                    <td style="color: var(--color-on-surface-variant); font-size:0.8rem;">#<?= esc($t['t_id']) ?></td>
                    <td>
                        <p style="font-weight:600;"><?= date('d/m/Y', strtotime($t['fecha'])) ?></p>
                        <p style="font-size:0.75rem; color: var(--color-on-surface-variant);">
                            <?= date('l', strtotime($t['fecha'])) ?>
                        </p>
                    </td>
                    <td style="font-size:0.875rem;">
                        <?= date('H:i', strtotime($t['t_horaDesde'])) ?> –
                        <?= date('H:i', strtotime($t['t_horaHasta'])) ?>
                    </td>
                    <td><?= esc($t['nombre_profesional'] ?? '—') ?></td>
                    <td><?= esc($t['nombre_cliente'] ?? '<span style="color:var(--color-on-surface-variant)">Sin asignar</span>') ?></td>
                    <td style="font-weight:600;">$<?= number_format($t['t_monto'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($t['abonado']): ?>
                            <span class="badge badge--green">Sí</span>
                        <?php else: ?>
                            <span class="badge badge--rose">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $estadoBadge[$t['estado']] ?? 'badge--gray' ?>">
                            <?= esc($t['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="table-btn table-btn--edit" title="Editar turno">
                                <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>
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
                Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> Turnos
            </span>
            <div>
                <?= $pager_links ?>
            </div>
        </div>
    <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">calendar_month</span>
            <p class="admin-empty-state__text">Aún no hay turnos registrados.</p>
        </div>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
