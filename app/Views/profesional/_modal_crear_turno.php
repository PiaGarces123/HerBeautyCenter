<!-- ============================================================
     MODAL — Crear y Asignar Turno a Cliente
     Her Beauty Center
     ============================================================ -->
<div class="modal fade" id="modalCrearTurno" tabindex="-1" aria-labelledby="modalCrearTurnoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title" id="modalCrearTurnoLabel">
                    <span class="material-symbols-outlined">event_available</span>
                    Crear Turno
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearTurno" novalidate>
                    <!-- Selección / Creación de Cliente -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipoClienteRadio" id="tipoClienteExistente" value="existente" checked>
                                <label class="form-check-label" for="tipoClienteExistente">Cliente existente</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipoClienteRadio" id="tipoClienteNuevo" value="nuevo">
                                <label class="form-check-label" for="tipoClienteNuevo">+ Nuevo cliente</label>
                            </div>
                        </div>

                        <!-- Dropdown de clientes existentes -->
                        <div id="grupoClienteExistente">
                            <select id="turnoClienteId" name="c_id" class="form-select admin-form-input">
                                <option value="">— Seleccioná un cliente —</option>
                                <?php if (!empty($clientes)): ?>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?= $cli['c_id'] ?>">
                                            <?= esc($cli['u_nbreCompleto']) ?> <?= !empty($cli['u_tel']) ? '— Tel: ' . esc($cli['u_tel']) : (!empty($cli['u_correo']) ? '— ' . esc($cli['u_correo']) : '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Campos para nuevo cliente -->
                        <div id="grupoClienteNuevo" style="display: none;" class="p-2 border rounded bg-light">
                            <div class="mb-2">
                                <label class="form-label form-label--sm fw-semibold" for="nuevoCliNombre">Nombre y Apellido <span class="text-danger">*</span></label>
                                <input type="text" id="nuevoCliNombre" class="form-control form-control-sm" placeholder="Ej: Mirtha Legrand">
                            </div>
                            <div class="mb-2">
                                <label class="form-label form-label--sm fw-semibold" for="nuevoCliTel">Teléfono <span class="text-danger">*</span></label>
                                <input type="tel" id="nuevoCliTel" class="form-control form-control-sm" placeholder="Ej: 1123456789">
                            </div>
                            <div>
                                <label class="form-label form-label--sm fw-semibold" for="nuevoCliEmail">Correo electrónico (opcional)</label>
                                <input type="email" id="nuevoCliEmail" class="form-control form-control-sm" placeholder="cliente@ejemplo.com">
                            </div>
                        </div>
                    </div>

                    <!-- Servicio -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="turnoServicioId">Servicio <span class="text-danger">*</span></label>
                        <select id="turnoServicioId" name="s_id" class="form-select admin-form-input" required>
                            <option value="">— Seleccioná un servicio —</option>
                            <?php 
                                $srvs = !empty($servicios_modal) ? $servicios_modal : (!empty($servicios) ? $servicios : []);
                                if (!empty($srvs)): 
                            ?>
                                <?php foreach ($srvs as $sm): ?>
                                    <option value="<?= $sm['s_id'] ?>" data-duracion="<?= $sm['s_duracionMinutos'] ?? 30 ?>" data-precio="<?= $sm['s_precio'] ?? 0 ?>">
                                        <?= esc($sm['s_nbre']) ?> (<?= $sm['s_duracionMinutos'] ?? 30 ?> min — $<?= number_format($sm['s_precio'] ?? 0, 0, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Fecha -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="turnoFecha">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="turnoFecha" class="form-control admin-form-input" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Horarios Desde y Hasta -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="turnoHoraDesde">Hora Inicio <span class="text-danger">*</span></label>
                            <input type="time" id="turnoHoraDesde" class="form-control admin-form-input" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="turnoHoraHasta">Hora Fin <span class="text-danger">*</span></label>
                            <input type="time" id="turnoHoraHasta" class="form-control admin-form-input" required>
                        </div>
                    </div>

                    <!-- Monto y Estado -->
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="turnoMonto">Monto ($)</label>
                            <input type="number" step="0.01" min="0" id="turnoMonto" class="form-control admin-form-input" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="turnoEstado">Estado inicial</label>
                            <select id="turnoEstado" class="form-select admin-form-input">
                                <option value="Confirmado" selected>Confirmado</option>
                                <option value="Solicitado">Solicitado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Abonado -->
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="turnoAbonado">
                        <label class="form-check-label fw-semibold" for="turnoAbonado">
                            ¿Turno abonado (pagado)?
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn--primary" id="btnGuardarNuevoTurno">
                    <span class="material-symbols-outlined" style="font-size:1.1rem;">check_circle</span>
                    Crear y Asignar Turno
                </button>
            </div>
        </div>
    </div>
</div>
