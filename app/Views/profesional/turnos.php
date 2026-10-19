<?php
/**
 * Vista: Gestionar Turnos (CU07) — Incremento N° 2
 * Her Beauty Center
 *
 * Lista tabular de turnos con filtros, estados y acciones inline.
 * Lógica JS: public/assets/js/turnos.js
 */
$pageTitle = 'Mis Turnos';
$activeNav = 'turnos';
$this->extend('profesional/_layout');
$this->section('content');

$estadoBadge = [
    'Disponible' => 'badge--blue',
    'Solicitado' => 'badge--orange',
    'Confirmado' => 'badge--green',
    'Realizado'  => 'badge--gray',
    'Cancelado'  => 'badge--rose',
];

$diasSemana = [
    'Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
    'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'
];
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mis Turnos</h2>
        <p class="admin-page-header__subtitle">Seguimiento y administración de reservas</p>
    </div>
    <a href="<?= base_url('admin/calendario') ?>" class="btn btn--primary admin-action-btn">
        <span class="material-symbols-outlined admin-action-icon">calendar_month</span>
        Ver Calendario
    </a>
</div>

<!-- Filtros de búsqueda -->
<div class="admin-table-card mb-3">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">Filtros</h3>
    </div>
    <div class="p-3">
        <form method="get" action="" id="formFiltros" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label form-label--sm" for="filtroEstado">Estado</label>
                <select name="estado" id="filtroEstado" class="form-select form-select-sm">
                    <option value="">Todos los estados</option>
                    <?php foreach(['Disponible','Solicitado','Confirmado','Realizado','Cancelado'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filtroEstado ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label form-label--sm" for="filtroDesde">Desde</label>
                <input type="date" name="fecha_desde" id="filtroDesde" class="form-control form-control-sm"
                       value="<?= esc($fechaDesde ?? '') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label form-label--sm" for="filtroHasta">Hasta</label>
                <input type="date" name="fecha_hasta" id="filtroHasta" class="form-control form-control-sm"
                       value="<?= esc($fechaHasta ?? '') ?>">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn--primary flex-fill">
                    <span class="material-symbols-outlined" style="font-size:1rem;">search</span> Filtrar
                </button>
                <a href="<?= base_url('admin/turnos') ?>" class="btn btn-outline-secondary">
                    <span class="material-symbols-outlined" style="font-size:1rem;">refresh</span>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">
            Turnos
            <?php if ($pager_total > 0): ?>
            <span class="badge badge--blue ms-2"><?= $pager_total ?></span>
            <?php endif; ?>
        </h3>
    </div>

    <?php if (!empty($turnos)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table" id="tablaTurnos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Abonado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turnos as $t): ?>
                <tr data-tid="<?= esc($t['t_id']) ?>" data-estado="<?= esc($t['estado']) ?>">
                    <td class="text-muted admin-text-sm">#<?= esc($t['t_id']) ?></td>
                    <td>
                        <p class="fw-bold mb-0"><?= date('d/m/Y', strtotime($t['fecha'])) ?></p>
                        <p class="text-muted admin-text-sm mb-0">
                            <?php $dia = date('l', strtotime($t['fecha'])); echo esc($diasSemana[$dia] ?? $dia); ?>
                        </p>
                    </td>
                    <td class="admin-text-sm">
                        <?= date('H:i', strtotime($t['t_horaDesde'])) ?> – <?= date('H:i', strtotime($t['t_horaHasta'])) ?>
                    </td>
                    <td><?= esc($t['nombre_servicio'] ?? '—') ?></td>
                    <td><?= esc($t['nombre_cliente'] ?? '—') ?></td>
                    <td class="fw-bold">$<?= number_format($t['monto'], 0, ',', '.') ?></td>
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
                            <button class="table-btn table-btn--edit btn-detalle-turno"
                                    title="Ver / Gestionar"
                                    data-tid="<?= esc($t['t_id']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">manage_search</span>
                            </button>
                            <?php if ($t['estado'] === 'Solicitado'): ?>
                            <button class="table-btn btn-confirmar-turno"
                                    title="Confirmar turno"
                                    data-tid="<?= esc($t['t_id']) ?>"
                                    style="color:#059669">
                                <span class="material-symbols-outlined admin-action-icon">check_circle</span>
                            </button>
                            <?php endif; ?>
                            <?php if ($t['estado'] === 'Confirmado'): ?>
                            <button class="table-btn btn-gcal-turno"
                                    title="Exportar a Google Calendar"
                                    data-tid="<?= esc($t['t_id']) ?>"
                                    style="color:#4285F4">
                                <span class="material-symbols-outlined admin-action-icon">event_available</span>
                            </button>
                            <?php endif; ?>
                            <?php if (in_array($t['estado'], ['Solicitado','Confirmado'])): ?>
                            <button class="table-btn table-btn--delete btn-cancelar-turno"
                                    title="Cancelar turno"
                                    data-tid="<?= esc($t['t_id']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">cancel</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager_links) && $pager_links): ?>
        <div class="d-flex justify-content-between align-items-center mt-4 px-3 admin-table-actions-row">
            <span class="fw-bold text-muted">
                Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> turnos
            </span>
            <div><?= $pager_links ?></div>
        </div>
    <?php endif; ?>

    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">calendar_month</span>
            <p class="admin-empty-state__text">No se encontraron turnos que coincidan con los filtros aplicados.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detalle / Gestión de Turno -->
<div class="modal fade" id="modalTurnoDetalle" tabindex="-1" aria-labelledby="modalTurnoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title" id="modalTurnoLabel">
                    <span class="material-symbols-outlined">event</span>
                    Detalle del Turno
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span id="modalTurnoBadge" class="badge"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body" id="modalTurnoBody">
                <div class="cal-loading">
                    <div class="cal-spinner"></div>
                    Cargando detalle...
                </div>
            </div>
            <div class="modal-footer" id="modalTurnoFooter"></div>
        </div>
    </div>
</div>

<!-- Modal Cancelación de Turno -->
<div class="modal fade" id="modalCancelarTurno" tabindex="-1" aria-labelledby="modalCancelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title" id="modalCancelLabel">
                    <span class="material-symbols-outlined">cancel</span>
                    Cancelar Turno
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="cal-cancel-warning">
                    <span class="material-symbols-outlined">warning</span>
                    Esta acción cancelará el turno permanentemente (se mantendrá en el historial).
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="motivoCancelTurno">Motivo de cancelación <span class="text-danger">*</span></label>
                    <textarea id="motivoCancelTurno" class="form-control" rows="3"
                              placeholder="Ej: La profesional no está disponible ese día..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancelTurno">
                    <span class="material-symbols-outlined" style="font-size:1rem;">cancel</span>
                    Confirmar Cancelación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Datos de contexto para JS -->
<script>
window.HBC_TUR = {
    apiDetalle:   '<?= base_url('api/turnos/detalle') ?>',
    apiEstado:    '<?= base_url('api/turnos/estado') ?>',
    apiPago:      '<?= base_url('api/turnos/pago') ?>',
    apiGoogleCal: '<?= base_url('api/turnos/google-calendar') ?>',
};
</script>
<script src="<?= base_url('public/assets/js/turnos.js') ?>"></script>

<?php $this->endSection(); ?>
