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
    <button id="btnAgregarDisponibilidad" class="btn-cal-primary">
        <span class="material-symbols-outlined">add_circle</span>
        Agregar Disponibilidad
    </button>
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
<div class="cal-modal-overlay" id="modalDisponibilidadOverlay" aria-hidden="true">
    <div class="cal-modal" role="dialog" aria-modal="true" aria-labelledby="modalDispTitle">
        <div class="cal-modal__header">
            <h3 class="cal-modal__title" id="modalDispTitle">
                <span class="material-symbols-outlined">schedule</span>
                <span id="modalDispTitleText">Agregar Disponibilidad</span>
            </h3>
            <button class="cal-modal__close" id="btnCerrarModalDisp" aria-label="Cerrar">&times;</button>
        </div>
        <div class="cal-modal__body">
            <form id="formDisponibilidad" novalidate>
                <input type="hidden" id="dispHId" value="">

                <div class="cal-form-group">
                    <label class="cal-form-label" for="dispServicio">
                        <span class="material-symbols-outlined">spa</span> Servicio
                    </label>
                    <select id="dispServicio" name="h_sId" class="cal-form-select" required>
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

                <div class="cal-form-group">
                    <label class="cal-form-label" for="dispFecha">
                        <span class="material-symbols-outlined">calendar_today</span> Fecha
                    </label>
                    <input type="date" id="dispFecha" name="h_fecha" class="cal-form-input"
                           min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="cal-form-row">
                    <div class="cal-form-group">
                        <label class="cal-form-label" for="dispDesde">
                            <span class="material-symbols-outlined">schedule</span> Hora Inicio
                        </label>
                        <input type="time" id="dispDesde" name="h_horaDesde" class="cal-form-input" required>
                    </div>
                    <div class="cal-form-group">
                        <label class="cal-form-label" for="dispHasta">
                            <span class="material-symbols-outlined">schedule</span> Hora Fin
                        </label>
                        <input type="time" id="dispHasta" name="h_horaHasta" class="cal-form-input" required>
                    </div>
                </div>
            </form>
        </div>
        <div class="cal-modal__footer">
            <button type="button" class="btn-cal-secondary" id="btnCancelarDisp">Cancelar</button>
            <button type="button" class="btn-cal-primary" id="btnGuardarDisp">
                <span class="material-symbols-outlined">save</span>
                <span id="btnGuardarDispText">Guardar Disponibilidad</span>
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Detalle del Turno / Disponibilidad (CU07)
     ============================================================ -->
<div class="cal-modal-overlay" id="modalDetalleOverlay" aria-hidden="true">
    <div class="cal-modal cal-modal--detalle" role="dialog" aria-modal="true" aria-labelledby="modalDetTitle">
        <div class="cal-modal__header" id="modalDetHeader">
            <h3 class="cal-modal__title" id="modalDetTitle">
                <span class="material-symbols-outlined" id="modalDetIcon">event</span>
                <span id="modalDetTitleText">Detalle</span>
            </h3>
            <div class="cal-modal__header-actions">
                <span class="cal-badge" id="modalDetBadge"></span>
                <button class="cal-modal__close" id="btnCerrarModalDet" aria-label="Cerrar">&times;</button>
            </div>
        </div>
        <div class="cal-modal__body" id="modalDetBody">
            <!-- Cargado dinámicamente por JS -->
        </div>
        <div class="cal-modal__footer" id="modalDetFooter">
            <!-- Botones dinámicos según estado -->
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL — Cancelar Turno (solicitar motivo)
     ============================================================ -->
<div class="cal-modal-overlay" id="modalCancelOverlay" aria-hidden="true">
    <div class="cal-modal cal-modal--sm" role="dialog" aria-modal="true" aria-labelledby="modalCancelTitle">
        <div class="cal-modal__header">
            <h3 class="cal-modal__title" id="modalCancelTitle">
                <span class="material-symbols-outlined">cancel</span>
                Cancelar Turno
            </h3>
            <button class="cal-modal__close" id="btnCerrarModalCancel" aria-label="Cerrar">&times;</button>
        </div>
        <div class="cal-modal__body">
            <p class="cal-cancel-warning">
                <span class="material-symbols-outlined">warning</span>
                Esta acción cancelará el turno. Por favor, indicá el motivo.
            </p>
            <div class="cal-form-group">
                <label class="cal-form-label" for="motivoCancelacion">Motivo de cancelación</label>
                <textarea id="motivoCancelacion" class="cal-form-textarea" rows="3"
                          placeholder="Ej: La profesional no está disponible ese día..."></textarea>
            </div>
        </div>
        <div class="cal-modal__footer">
            <button type="button" class="btn-cal-secondary" id="btnCerrarCancelModal">Volver</button>
            <button type="button" class="btn-cal-danger" id="btnConfirmarCancel">
                <span class="material-symbols-outlined">cancel</span>
                Confirmar Cancelación
            </button>
        </div>
    </div>
</div>

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
        rol:              '<?= esc(session()->get('rol')) ?>',
        hoy:              '<?= date('Y-m-d') ?>',
    };
</script>

<?php endif; ?>

<?php $this->endSection(); ?>
