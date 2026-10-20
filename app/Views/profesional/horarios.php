<?php
/**
 * Vista: Mi Disponibilidad (CU08 - Gestionar Disponibilidad) — Incremento N° 2
 * Her Beauty Center
 *
 * Lista tabular de disponibilidades publicadas con filtros por período, servicio y búsqueda.
 * La lógica JS está en: public/assets/js/horarios.js
 */
$pageTitle = 'Mi Disponibilidad';
$activeNav = 'horarios';
$this->extend('profesional/_layout');
$this->section('content');

$diasSemana = [
    'Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
    'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'
];
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mi Disponibilidad</h2>
        <p class="admin-page-header__subtitle">Franjas horarias disponibles publicadas para reserva de turnos</p>
    </div>
    <?php if (!empty($profesional)): ?>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= base_url('admin/calendario') ?>" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <span class="material-symbols-outlined admin-action-icon">calendar_month</span>
            Ver Calendario
        </a>
        <button id="btnNuevoHorario" class="btn btn--primary admin-action-btn">
            <span class="material-symbols-outlined admin-action-icon">add</span>
            Agregar Disponibilidad
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($profesional)): ?>
    <div class="admin-alert admin-alert--error">
        <span class="material-symbols-outlined admin-visibility-icon flex-shrink-0">error</span>
        Tu usuario no está vinculado a un perfil de profesional. Contactá al administrador principal.
    </div>
<?php else: ?>

<!-- Filtros de Disponibilidad -->
<div class="admin-table-card mb-3">
    <form method="GET" action="<?= base_url('admin/horarios') ?>" class="admin-filter-bar admin-table-filter-bar p-3 d-flex flex-wrap gap-2 align-items-center">
        <div class="admin-table-filter-col-sm flex-grow-1" style="min-width: 180px;">
            <select name="periodo" class="form-control admin-form-input">
                <option value="">Período: Todos</option>
                <option value="hoy" <?= ($periodo ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= ($periodo ?? '') === 'semana' ? 'selected' : '' ?>>Esta semana</option>
                <option value="mes" <?= ($periodo ?? '') === 'mes' ? 'selected' : '' ?>>Este mes</option>
            </select>
        </div>
        <div class="admin-table-filter-col-sm flex-grow-1" style="min-width: 220px;">
            <select name="servicio" class="form-control admin-form-input">
                <option value="">Servicio: Todos</option>
                <?php if (!empty($servicios_profesional)): ?>
                    <?php foreach ($servicios_profesional as $srv): ?>
                        <option value="<?= $srv['s_id'] ?>" <?= ((int)($servicioFiltro ?? 0) === (int)$srv['s_id']) ? 'selected' : '' ?>>
                            <?= esc($srv['s_nbre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" class="btn btn--primary admin-modal-submit">
            Filtrar
        </button>
        <a href="<?= base_url('admin/horarios') ?>" class="btn admin-modal-submit admin-action-btn--outline">
            Limpiar Filtros
        </a>
    </form>
</div>

<!-- Tabla de Disponibilidades -->
<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">
            Disponibilidades publicadas
            <?php if ($pager_total > 0): ?>
            <span class="badge badge--blue ms-2"><?= $pager_total ?></span>
            <?php endif; ?>
        </h3>
    </div>

    <?php if (!empty($horarios)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table" id="tablaHorarios">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horarios as $h): ?>
                <tr data-hid="<?= esc($h['h_id']) ?>">
                    <td class="fw-bold"><?= date('d/m/Y', strtotime($h['h_fecha'])) ?></td>
                    <td>
                        <?php
                            $dia = date('l', strtotime($h['h_fecha']));
                            echo esc($diasSemana[$dia] ?? $dia);
                        ?>
                    </td>
                    <td class="admin-text-sm">
                        <?= date('H:i', strtotime($h['h_horaDesde'])) ?> –
                        <?= date('H:i', strtotime($h['h_horaHasta'])) ?>
                    </td>
                    <td><?= esc($h['nombre_servicio'] ?? '—') ?></td>
                    <td>
                        <?php if ($h['h_fecha'] < date('Y-m-d')): ?>
                            <span class="badge badge--gray">Expirado</span>
                        <?php else: ?>
                            <span class="badge badge--blue">Disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="table-btn table-btn--edit btn-editar-horario"
                                    title="Editar Disponibilidad"
                                    data-hid="<?= esc($h['h_id']) ?>"
                                    data-sid="<?= esc($h['h_sId']) ?>"
                                    data-fecha="<?= esc($h['h_fecha']) ?>"
                                    data-desde="<?= esc(substr($h['h_horaDesde'],0,5)) ?>"
                                    data-hasta="<?= esc(substr($h['h_horaHasta'],0,5)) ?>">
                                <span class="material-symbols-outlined admin-action-icon">edit</span>
                            </button>
                            <button class="table-btn table-btn--delete btn-eliminar-horario"
                                    title="Eliminar Disponibilidad"
                                    data-hid="<?= esc($h['h_id']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">delete</span>
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
            <span class="material-symbols-outlined">event_available</span>
            <p class="admin-empty-state__text">No hay disponibilidades publicadas con los filtros seleccionados.</p>
            <p class="text-muted admin-text-sm">Hacé clic en "Agregar Disponibilidad" o usá el <a href="<?= base_url('admin/calendario') ?>">Calendario</a> para publicar nuevas franjas horarias.</p>
        </div>
    <?php endif; ?>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 px-3 py-2 border-top admin-table-actions-row">
        <span class="fw-bold text-muted" style="font-size: 0.85rem;">
            <?php if ($pager_total > 0): ?>
                Mostrando <?= $pager_start ?>–<?= $pager_end ?> de <?= $pager_total ?> disponibilidades
            <?php else: ?>
                Mostrando 0 de 0 disponibilidades
            <?php endif; ?>
        </span>
        <div>
            <?php if (!empty($pager_links)): ?>
                <?= $pager_links ?>
            <?php else: ?>
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination pagination-sm justify-content-center mb-0 admin-pagination">
                        <li class="page-item disabled">
                            <span class="page-link admin-page-link--disabled">
                                <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_left</span>
                            </span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link admin-page-link--active">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link admin-page-link--disabled">
                                <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_right</span>
                            </span>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Agregar / Editar Horario
     ============================================================ -->
<div class="modal fade" id="modalHorario" tabindex="-1" aria-labelledby="modalHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title" id="modalHorarioLabel">
                    <span class="material-symbols-outlined">schedule</span>
                    <span id="modalHorTitleText">Agregar Disponibilidad</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formHorario" novalidate>
                    <input type="hidden" id="horHId" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="horServicio">Servicio <span class="text-danger">*</span></label>
                        <select id="horServicio" name="h_sId" class="form-select" required>
                            <option value="">— Seleccioná un servicio —</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="horFecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="horFecha" name="h_fecha" class="form-control"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="horDesde">Hora Inicio <span class="text-danger">*</span></label>
                            <input type="time" id="horDesde" name="h_horaDesde" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="horHasta">Hora Fin <span class="text-danger">*</span></label>
                            <input type="time" id="horHasta" name="h_horaHasta" class="form-control" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn--primary" id="btnGuardarHorario">
                    <span class="material-symbols-outlined">save</span>
                    <span id="btnGuardarHorarioText">Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Datos de contexto para JS -->
<script>
window.HBC_HOR = {
    apiCrear:    '<?= base_url('api/horarios/crear') ?>',
    apiEditar:   '<?= base_url('api/horarios/editar') ?>',
    apiEliminar: '<?= base_url('api/horarios/eliminar') ?>',
    apiServicios:'<?= base_url('api/horarios/servicios') ?>',
    hoy:         '<?= date('Y-m-d') ?>',
};
</script>
<script src="<?= base_url('public/assets/js/horarios.js') ?>"></script>

<?php endif; ?>

<?php $this->endSection(); ?>
