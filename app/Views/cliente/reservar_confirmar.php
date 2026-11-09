<?php
$h = $horario ?? [];
$u = $usuario ?? [];
$sId = (int) ($h['s_id'] ?? 0);
$fecha = !empty($h['h_fecha']) ? date('d/m/Y', strtotime($h['h_fecha'])) : '—';
$desde = substr($h['h_horaDesde'] ?? '', 0, 5);
$hasta = substr($h['h_horaHasta'] ?? '', 0, 5);
$avatar = !empty($h['avatar_profesional']) ? base_url($h['avatar_profesional']) : base_url('public/assets/media/íconoPerfil.png');
?>
<?= view('cliente/_head', ['pageTitle' => 'Confirmar reserva — ' . ($h['s_nbre'] ?? '')]) ?>

    <main class="cl-main">
        <div class="cl-wrap">

            <ol class="cl-stepper" aria-label="Progreso de la reserva">
                <li class="cl-step cl-step--done"><span class="cl-step__num">✓</span><span class="cl-step__label">Servicio</span></li>
                <li class="cl-step__line" aria-hidden="true"></li>
                <li class="cl-step cl-step--done"><span class="cl-step__num">✓</span><span class="cl-step__label">Fecha y horario</span></li>
                <li class="cl-step__line" aria-hidden="true"></li>
                <li class="cl-step cl-step--active" aria-current="step"><span class="cl-step__num">3</span><span class="cl-step__label">Tus datos</span></li>
            </ol>

            <div class="cl-confirm">

                <!-- Tu Turno -->
                <aside class="cl-confirm__summary cl-card cl-card--rose cl-rise" aria-labelledby="tuTurnoTitle">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="cl-eyebrow m-0">Resumen</span>
                        <a href="<?= base_url('reservar/' . $sId) ?>" class="cl-muted text-decoration-underline">Cambiar horario</a>
                    </div>
                    <h2 class="cl-title" id="tuTurnoTitle">Tu <em>Turno</em></h2>

                    <div class="cl-summary">
                        <div class="cl-row">
                            <span class="cl-row__icon"><span class="material-symbols-outlined">spa</span></span>
                            <div>
                                <div class="cl-row__label">Servicio</div>
                                <div class="cl-row__value m-0"><?= esc($h['s_nbre'] ?? '') ?></div>
                                <?php if (!empty($h['s_precio']) && $h['s_precio'] > 0): ?>
                                    <div class="cl-row__extra m-0">Valor base: $<?= number_format((float) $h['s_precio'], 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="cl-row">
                            <span class="cl-row__icon"><span class="material-symbols-outlined">calendar_today</span></span>
                            <div>
                                <div class="cl-row__label">Fecha</div>
                                <div class="cl-row__value m-0"><?= esc($fecha) ?></div>
                            </div>
                        </div>
                        <div class="cl-row">
                            <span class="cl-row__icon"><span class="material-symbols-outlined">schedule</span></span>
                            <div>
                                <div class="cl-row__label">Hora</div>
                                <div class="cl-row__value m-0"><?= esc($desde) ?> a <?= esc($hasta) ?> hs</div>
                            </div>
                        </div>
                        <div class="cl-row">
                            <img src="<?= esc($avatar) ?>" alt="" class="cl-avatar" />
                            <div>
                                <div class="cl-row__label">Profesional</div>
                                <div class="cl-row__value m-0"><?= esc($h['nombre_profesional'] ?? '') ?></div>
                                <div class="cl-row__extra m-0"><?= esc($h['p_titulo'] ?? 'Especialista') ?></div>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Completá tus Datos -->
                <section class="cl-card cl-rise cl-rise--2" aria-labelledby="datosTitle">
                    <span class="cl-eyebrow">Último paso</span>
                    <h1 class="cl-subtitle" id="datosTitle">Completá tus Datos</h1>
                    <p class="cl-muted mb-4">Verificá cómo puede contactarte la especialista.</p>

                    <form id="confirmarReservaForm" class="needs-validation" novalidate>
                        <input type="hidden" id="inputHId" value="<?= (int) ($h['h_id'] ?? 0) ?>" />

                        <div class="cl-form-grid">
                            <div class="form-group cl-span-2">
                                <label class="form-label" for="u_nbreCompleto">Nombre completo *</label>
                                <input type="text" class="form-input" id="u_nbreCompleto" autocomplete="name"
                                    value="<?= esc($u['u_nbreCompleto'] ?? '') ?>" required minlength="3" maxlength="100" />
                                <div class="invalid-feedback">Ingresá tu nombre completo (mínimo 3 caracteres).</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="u_tel">Teléfono / WhatsApp *</label>
                                <input type="tel" class="form-input" id="u_tel" autocomplete="tel" inputmode="tel"
                                    value="<?= esc($u['u_tel'] ?? '') ?>" required minlength="8" maxlength="20" pattern="^[0-9+\s\-()]{8,20}$" />
                                <div class="invalid-feedback">Ingresá un teléfono válido (mínimo 8 dígitos).</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="u_correo">Email *</label>
                                <input type="email" class="form-input" id="u_correo" autocomplete="email"
                                    value="<?= esc($u['u_correo'] ?? '') ?>" required maxlength="120" />
                                <div class="invalid-feedback">Ingresá un correo válido.</div>
                            </div>
                            <div class="form-group cl-span-2">
                                <label class="form-label" for="consideraciones">Consideraciones especiales (opcional)</label>
                                <textarea class="form-textarea" id="consideraciones" rows="3" maxlength="500"
                                    placeholder="Preferencias de atención, tipo de piel o ideas de diseño…"><?= esc($u['consideraciones'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="cl-notice">
                            <span class="material-symbols-outlined">info</span>
                            <p class="m-0">Al confirmar la reserva, la profesional se pondrá en contacto contigo mediante los medios de contacto que has seleccionado, y así hablar del diseño, monto y duración del servicio.</p>
                        </div>

                        <div class="cl-actions">
                            <a href="<?= base_url('reservar/' . $sId) ?>" class="btn btn--outline">
                                <span class="material-symbols-outlined">arrow_back</span> Volver
                            </a>
                            <button type="submit" id="btnConfirmarReserva" class="btn btn--primary">
                                <span class="material-symbols-outlined">check_circle</span> Confirmar reserva
                            </button>
                        </div>
                    </form>
                </section>

            </div>
        </div>
    </main>

<?= view('cliente/_scripts') ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const BRAND = window.APP_CONFIG.brand;
        const form = document.getElementById('confirmarReservaForm');
        const btn = document.getElementById('btnConfirmarReserva');
        const resumen = <?= json_encode([
            'servicio' => $h['s_nbre'] ?? '',
            'fecha' => $fecha,
            'hora' => $desde,
            'prof' => $h['nombre_profesional'] ?? '',
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            Swal.fire({
                title: '¿Confirmar tu reserva?',
                text: `${resumen.servicio} — ${resumen.fecha} a las ${resumen.hora} hs con ${resumen.prof}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: BRAND,
                cancelButtonColor: '#82756a',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Revisar',
                reverseButtons: true
            }).then(async ({ isConfirmed }) => {
                if (!isConfirmed) return;
                btn.disabled = true;

                try {
                    const res = await fetch('<?= base_url('api/reservas/confirmar') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({
                            h_id: document.getElementById('inputHId').value,
                            u_nbreCompleto: document.getElementById('u_nbreCompleto').value.trim(),
                            u_tel: document.getElementById('u_tel').value.trim(),
                            u_correo: document.getElementById('u_correo').value.trim(),
                            consideraciones: document.getElementById('consideraciones').value.trim()
                        })
                    });
                    const data = await res.json();

                    if (res.ok) {
                        await Swal.fire({ title: '¡Reserva solicitada!', text: data.message, icon: 'success', confirmButtonColor: BRAND, confirmButtonText: 'Ver mis turnos' });
                        location.href = data.redirect_url || '<?= base_url('mis-turnos') ?>';
                        return;
                    }

                    await Swal.fire({ title: 'Atención', text: data.messages?.error || data.message || 'No se pudo completar la reserva.', icon: res.status === 409 ? 'warning' : 'error', confirmButtonColor: BRAND });
                    if (res.status === 409) location.href = '<?= base_url('reservar/' . $sId . '?ocupado=1') ?>';
                } catch (err) {
                    Swal.fire({ title: 'Error de conexión', text: 'Intentá nuevamente en unos segundos.', icon: 'error', confirmButtonColor: BRAND });
                }
                btn.disabled = false;
            });
        });
    });
    </script>
</body>

</html>
