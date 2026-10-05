<?php
$pageTitle = 'Mis Horarios';
$activeNav = 'horarios';
$this->extend('profesional/_layout');
$this->section('content');

$diasSemana = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
               'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mis Horarios</h2>
        <p class="admin-page-header__subtitle">Tu disponibilidad para recibir turnos</p>
    </div>
    <a href="#" class="btn btn--primary admin-action-btn">
        <span class="material-symbols-outlined admin-action-icon">add</span>
        Agregar horario
    </a>
</div>

<?php if (empty($profesional)): ?>
    <div class="admin-alert admin-alert--error">
        <span class="material-symbols-outlined admin-visibility-icon flex-shrink-0">error</span>
        Tu usuario no está vinculado a un perfil de profesional. Contactá al administrador principal.
    </div>
<?php else: ?>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">Horarios configurados</h3>
    </div>

    <?php if (!empty($horarios)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Servicio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horarios as $h): ?>
                <tr>
                    <td class="fw-bold"><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                    <td>
                        <?php
                            $diaSemana = date('l', strtotime($h['fecha']));
                            echo esc($diasSemana[$diaSemana] ?? $diaSemana);
                        ?>
                    </td>
                    <td>
                        <?= date('H:i', strtotime($h['t_horaDesde'])) ?> –
                        <?= date('H:i', strtotime($h['t_horaHasta'])) ?>
                    </td>
                    <td><?= esc($h['nombre_servicio'] ?? '—') ?></td>
                    <td>
                        <div class="table-actions">
                            <button class="table-btn table-btn--edit" title="Editar">
                                <span class="material-symbols-outlined admin-action-icon">edit</span>
                            </button>
                            <button class="table-btn table-btn--delete" title="Eliminar">
                                <span class="material-symbols-outlined admin-action-icon">delete</span>
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
        <div class="d-flex justify-content-between align-items-center mt-4 px-3 admin-table-actions-row">
            <span class="fw-bold text-muted">
                Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> Horarios
            </span>
            <div>
                <?= $pager_links ?>
            </div>
        </div>
    <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">schedule</span>
            <p class="admin-empty-state__text">Aún no tenés horarios configurados.</p>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php $this->endSection(); ?>





