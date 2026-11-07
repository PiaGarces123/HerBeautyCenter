<?php
/**
 * Vista: Calendario Interactivo — Incremento N° 2
 * Her Beauty Center
 *
 * Integra FullCalendar v6 con soporte de vistas Mes / Semana / Día.
 * CU08 (Gestionar Disponibilidad) + CU07 (Gestionar Turnos).
 * Todos los estilos personalizados están en: public/assets/css/calendario.css
 * Toda la lógica JS está en: public/assets/js/calendario.js
 */
$pageTitle = 'Calendario';
$activeNav = 'calendario';
$this->extend('profesional/_layout');
$this->section('content');
?>

<?php $this->renderSection('extra_head') ?>

<div class="cal-page-header">
    <div class="cal-page-header__info">
        <h2 class="cal-page-header__title">Mi Calendario</h2>
        <p class="cal-page-header__subtitle">Gestión de disponibilidades y turnos</p>
    </div>
    <?php if (!empty($profesional)): ?>
    <div class="d-flex gap-2 flex-wrap">
        <button id="btnCrearTurnoCal" class="btn btn--primary admin-action-btn" data-bs-toggle="modal" data-bs-target="#modalCrearTurno">
            <span class="material-symbols-outlined">event_available</span>
            Crear Turno
        </button>
        <button id="btnAgregarDisponibilidad" class="btn btn--primary admin-action-btn" data-bs-toggle="modal" data-bs-target="#modalDisponibilidad">
            <span class="material-symbols-outlined">add_circle</span>
            Agregar Disponibilidad
        </button>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($profesional)): ?>
    <div class="admin-alert admin-alert--error">
        <span class="material-symbols-outlined flex-shrink-0">error</span>
        Tu usuario no está vinculado a un perfil de profesional. Contactá al administrador principal.
    </div>
<?php else: ?>

<!-- Leyenda y Filtros por Estado -->
<div class="cal-legend" role="region" aria-label="Filtrar por estado de turnos">
    <div class="cal-legend__title">
        <span class="material-symbols-outlined cal-legend__icon">filter_alt</span>
        <span>Filtrar por estado:</span>
    </div>
    <div class="cal-legend__items">
        <label class="cal-legend__filter" for="filtroEstadoDisponible" title="Mostrar/ocultar turnos disponibles">
            <input type="checkbox" id="filtroEstadoDisponible" class="cal-filter-checkbox" value="Disponible" checked>
            <span class="cal-legend__dot" data-estado="disponible"></span>
            <span class="cal-legend__name">Disponible</span>
        </label>
        <label class="cal-legend__filter" for="filtroEstadoSolicitado" title="Mostrar/ocultar turnos solicitados">
            <input type="checkbox" id="filtroEstadoSolicitado" class="cal-filter-checkbox" value="Solicitado" checked>
            <span class="cal-legend__dot" data-estado="solicitado"></span>
            <span class="cal-legend__name">Solicitado</span>
        </label>
        <label class="cal-legend__filter" for="filtroEstadoConfirmado" title="Mostrar/ocultar turnos confirmados">
            <input type="checkbox" id="filtroEstadoConfirmado" class="cal-filter-checkbox" value="Confirmado" checked>
            <span class="cal-legend__dot" data-estado="confirmado"></span>
            <span class="cal-legend__name">Confirmado</span>
        </label>
        <label class="cal-legend__filter" for="filtroEstadoRealizado" title="Mostrar/ocultar turnos realizados">
            <input type="checkbox" id="filtroEstadoRealizado" class="cal-filter-checkbox" value="Realizado" checked>
            <span class="cal-legend__dot" data-estado="realizado"></span>
            <span class="cal-legend__name">Realizado</span>
        </label>
        <label class="cal-legend__filter" for="filtroEstadoCancelado" title="Mostrar/ocultar turnos cancelados">
            <input type="checkbox" id="filtroEstadoCancelado" class="cal-filter-checkbox" value="Cancelado" checked>
            <span class="cal-legend__dot" data-estado="cancelado"></span>
            <span class="cal-legend__name">Cancelado</span>
        </label>
    </div>
</div>

<!-- Contenedor del Calendario -->
<div class="cal-wrapper">
    <div id="herCalendar"></div>
</div>

<!-- ============================================================
     MODAL — Agregar / Editar Disponibilidad (CU08)
     ============================================================ -->
<div class="modal fade" id="modalDisponibilidad" tabindex="-1" aria-labelledby="modalDispTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title admin-modal-title" id="modalDispTitle">
                    <span class="material-symbols-outlined">schedule</span>
                    <span id="modalDispTitleText">Agregar Disponibilidad</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" id="btnCerrarModalDisp"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="formDisponibilidad" novalidate>
                    <input type="hidden" id="dispHId" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="dispServicio">
                            <span class="material-symbols-outlined align-middle me-1">spa</span> Servicio <span class="text-danger">*</span>
                        </label>
                        <select id="dispServicio" name="h_sId" class="form-select admin-form-input" required>
                            <option value="">— Seleccioná un servicio —</option>
                            <?php foreach ($servicios as $s): ?>
                            <option value="<?= esc($s['s_id']) ?>"
                                    data-duracion="<?= esc($s['s_duracionMinutos']) ?>"
                                    data-precio="<?= esc($s['s_precio']) ?>">
                                <?= esc($s['s_nbre']) ?> (<?= esc($s['s_duracionMinutos']) ?> min — $<?= number_format($s['s_precio'], 0, ',', '.') ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="dispFecha">
                            <span class="material-symbols-outlined align-middle me-1">calendar_today</span> Fecha <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="dispFecha" name="h_fecha" class="form-control admin-form-input"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="dispDesde">
                                <span class="material-symbols-outlined align-middle me-1">schedule</span> Hora Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="time" id="dispDesde" name="h_horaDesde" class="form-control admin-form-input" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="dispHasta">
                                <span class="material-symbols-outlined align-middle me-1">schedule</span> Hora Fin <span class="text-danger">*</span>
                            </label>
                            <input type="time" id="dispHasta" name="h_horaHasta" class="form-control admin-form-input" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelarDisp">Cancelar</button>
                <button type="button" class="btn btn--primary" id="btnGuardarDisp">
                    <span class="material-symbols-outlined">save</span>
                    <span id="btnGuardarDispText">Guardar Disponibilidad</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Detalle del Turno / Disponibilidad (CU07)
     ============================================================ -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title admin-modal-title d-flex align-items-center gap-2" id="modalDetTitle">
                    <span class="material-symbols-outlined" id="modalDetIcon">event</span>
                    <span id="modalDetTitleText">Detalle</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="cal-badge" id="modalDetBadge"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" id="btnCerrarModalDet"></button>
                </div>
            </div>
            <div class="modal-body admin-modal-body" id="modalDetBody">
                <!-- Cargado dinámicamente por JS -->
            </div>
            <div class="modal-footer" id="modalDetFooter">
                <!-- Botones dinámicos según estado -->
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Cancelar Turno (solicitar motivo)
     ============================================================ -->
<div class="modal fade" id="modalCancel" tabindex="-1" aria-labelledby="modalCancelTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title admin-modal-title d-flex align-items-center gap-2" id="modalCancelTitle">
                    <span class="material-symbols-outlined">cancel</span>
                    Cancelar Turno
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" id="btnCerrarModalCancel"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <div class="cal-cancel-warning">
                    <span class="material-symbols-outlined">warning</span>
                    Esta acción cancelará el turno. Por favor, indicá el motivo.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="motivoCancelacion">Motivo de cancelación <span class="text-danger">*</span></label>
                    <textarea id="motivoCancelacion" class="form-control admin-form-input" rows="3"
                              placeholder="Ej: La profesional no está disponible ese día..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCerrarCancelModal">Volver</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancel">
                    <span class="material-symbols-outlined">cancel</span>
                    Confirmar Cancelación
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->include('profesional/_modal_crear_turno') ?>

<!-- Datos de contexto para el JS -->
<script>
    window.HBC_CAL = {
        baseUrl:          '<?= base_url() ?>',
        apiEventos:       '<?= base_url('api/horarios/eventos') ?>',
        apiCrear:         '<?= base_url('api/horarios/crear') ?>',
        apiEditar:        '<?= base_url('api/horarios/editar') ?>',
        apiEliminar:      '<?= base_url('api/horarios/eliminar') ?>',
        apiTurnoEstado:   '<?= base_url('api/turnos/estado') ?>',
        apiTurnoPago:     '<?= base_url('api/turnos/pago') ?>',
        apiGoogleCal:     '<?= base_url('api/turnos/google-calendar') ?>',
        apiCrearTurno:    '<?= base_url('api/turnos/crear') ?>',
        rol:              '<?= esc(session()->get('rol')) ?>',
        hoy:              '<?= date('Y-m-d') ?>',
    };
</script>

<?php endif; ?>

<?php $this->endSection(); ?>
