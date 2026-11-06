<?php
$h = $horario ?? [];
$u = $usuario ?? [];
$fechaFormateada = !empty($h['h_fecha']) ? date('d/m/Y', strtotime($h['h_fecha'])) : '—';
$horaDesde = !empty($h['h_horaDesde']) ? substr($h['h_horaDesde'], 0, 5) : '—';
$horaHasta = !empty($h['h_horaHasta']) ? substr($h['h_horaHasta'], 0, 5) : '—';
$profAvatar = !empty($h['avatar_profesional'])
    ? base_url($h['avatar_profesional'])
    : base_url('public/assets/media/íconoPerfil.png');
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Confirmar Reserva — <?= esc($h['s_nbre'] ?? 'Turno') ?> | Her Beauty Center</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />

    <!-- Estilos del Sistema -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/cliente.css?v=' . time()) ?>" />
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>

    <main class="client-page-main">
        <div class="client-container">

            <!-- Breadcrumb -->
            <nav aria-label="Ruta de navegación" class="client-breadcrumb">
                <a href="<?= base_url('/') ?>">Inicio</a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <a href="<?= base_url('servicio/' . ($h['s_id'] ?? 0)) ?>"><?= esc($h['s_nbre'] ?? 'Servicio') ?></a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <a href="<?= base_url('reservar/' . ($h['s_id'] ?? 0)) ?>">Reservá tu Turno</a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <span class="fw-semibold text-dark">Confirmar Reserva</span>
            </nav>

            <!-- Stepper de Progreso (CU04) -->
            <div class="booking-stepper">
                <a href="<?= base_url('servicio/' . ($h['s_id'] ?? 0)) ?>" class="booking-step booking-step--done text-decoration-none">
                    <span class="booking-step__num">✓</span>
                    <span>1. Servicio elegido</span>
                </a>
                <div class="booking-stepper__line"></div>
                <a href="<?= base_url('reservar/' . ($h['s_id'] ?? 0)) ?>" class="booking-step booking-step--done text-decoration-none">
                    <span class="booking-step__num">✓</span>
                    <span>2. Fecha y Horario</span>
                </a>
                <div class="booking-stepper__line"></div>
                <div class="booking-step booking-step--active">
                    <span class="booking-step__num">3</span>
                    <span>3. Confirmar Datos</span>
                </div>
            </div>

            <!-- Grilla de Pre-Confirmación: Resumen "Tu Turno" + Formulario "Completá tus Datos" -->
            <section class="confirm-booking-grid">

                <!-- Columna Izquierda: Tarjeta "Tu Turno" -->
                <div class="confirm-summary-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase fw-bold small" style="color: var(--color-primary); letter-spacing: 0.08em;">
                            Resumen de Selección
                        </span>
                        <a href="<?= base_url('reservar/' . ($h['s_id'] ?? 0)) ?>" class="text-decoration-none small fw-semibold" style="color: var(--color-primary);">
                            Cambiar horario
                        </a>
                    </div>
                    <h2 class="h3 mb-1">Tu Turno</h2>
                    <p class="text-muted small mb-0">Revisá los detalles del horario seleccionado antes de confirmar.</p>

                    <div class="confirm-summary-list">
                        <!-- Servicio -->
                        <div class="confirm-summary-row">
                            <span class="confirm-summary-icon">
                                <span class="material-symbols-outlined">spa</span>
                            </span>
                            <div>
                                <div class="text-muted small">Servicio</div>
                                <div class="fw-bold text-dark fs-6"><?= esc($h['s_nbre'] ?? '') ?></div>
                                <?php if (!empty($h['s_precio']) && $h['s_precio'] > 0): ?>
                                    <div class="small fw-semibold" style="color: var(--color-primary-hover);">
                                        Valor base estimado: $<?= number_format((float) $h['s_precio'], 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="confirm-summary-row">
                            <span class="confirm-summary-icon">
                                <span class="material-symbols-outlined">calendar_today</span>
                            </span>
                            <div>
                                <div class="text-muted small">Fecha</div>
                                <div class="fw-bold text-dark fs-6"><?= esc($fechaFormateada) ?></div>
                            </div>
                        </div>

                        <!-- Hora -->
                        <div class="confirm-summary-row">
                            <span class="confirm-summary-icon">
                                <span class="material-symbols-outlined">schedule</span>
                            </span>
                            <div>
                                <div class="text-muted small">Hora</div>
                                <div class="fw-bold text-dark fs-6"><?= esc($horaDesde) ?> hs a <?= esc($horaHasta) ?> hs</div>
                            </div>
                        </div>

                        <!-- Profesional y Título de la Profesional -->
                        <div class="confirm-summary-row">
                            <img src="<?= esc($profAvatar) ?>" alt="<?= esc($h['nombre_profesional'] ?? '') ?>"
                                class="srv-detail-pro-avatar" />
                            <div>
                                <div class="text-muted small">Profesional</div>
                                <div class="fw-bold text-dark fs-6"><?= esc($h['nombre_profesional'] ?? '') ?></div>
                                <div class="text-muted small">
                                    <strong>Título de la Profesional:</strong> <?= esc($h['p_titulo'] ?? 'Especialista') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Formulario "Completá tus Datos" -->
                <div class="confirm-form-card">
                    <span class="text-uppercase fw-bold small" style="color: var(--color-primary); letter-spacing: 0.08em;">
                        Último Paso
                    </span>
                    <h1 class="h3 mt-1 mb-1">Completá tus Datos</h1>
                    <p class="text-muted small mb-4">
                        Verificá tus datos de contacto para que la especialista pueda comunicarse con vos.
                    </p>

                    <form id="confirmarReservaForm" class="needs-validation" novalidate>
                        <input type="hidden" name="h_id" id="inputHId" value="<?= (int) ($h['h_id'] ?? 0) ?>" />

                        <div class="row g-3">
                            <!-- Nombre Completo -->
                            <div class="col-12">
                                <label class="form-label" for="u_nbreCompleto">Nombre Completo *</label>
                                <input type="text" class="form-input" id="u_nbreCompleto" name="u_nbreCompleto"
                                    value="<?= esc($u['u_nbreCompleto'] ?? session()->get('usuario_nombre') ?? '') ?>"
                                    required minlength="3" placeholder="Tu nombre y apellido" />
                                <div class="invalid-feedback">Ingresá tu nombre completo (mínimo 3 caracteres).</div>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="u_tel">Teléfono / WhatsApp *</label>
                                <input type="tel" class="form-input" id="u_tel" name="u_tel"
                                    value="<?= esc($u['u_tel'] ?? '') ?>"
                                    required minlength="8" placeholder="Ej: 2664123456" />
                                <div class="invalid-feedback">Ingresá un teléfono válido (al menos 8 dígitos).</div>
                            </div>

                            <!-- Email -->
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="u_correo">Email *</label>
                                <input type="email" class="form-input" id="u_correo" name="u_correo"
                                    value="<?= esc($u['u_correo'] ?? session()->get('usuario_correo') ?? '') ?>"
                                    required placeholder="ejemplo@gmail.com" />
                                <div class="invalid-feedback">Ingresá un correo electrónico válido.</div>
                            </div>

                            <!-- Alergias o Consideraciones (opcional) -->
                            <div class="col-12">
                                <label class="form-label" for="consideraciones">
                                    Alergias o Consideraciones <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <textarea class="form-textarea" id="consideraciones" name="consideraciones" rows="3"
                                    maxlength="500"
                                    placeholder="¿Tenés alguna alergia en la piel, sensibilidad o idea de diseño que quieras comentarle a la profesional?"></textarea>
                            </div>
                        </div>

                        <!-- Aviso informativo para el cliente -->
                        <div class="confirm-notice-box">
                            <span class="material-symbols-outlined fs-4" style="color: var(--color-primary);">info</span>
                            <div>
                                <strong>Importante:</strong> Al confirmar la reserva, la profesional se pondrá en contacto contigo mediante los medios de contacto que has seleccionado, y así hablar del diseño, monto y duración del servicio.
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <a href="<?= base_url('reservar/' . ($h['s_id'] ?? 0)) ?>" class="btn btn--outline">
                                <span class="material-symbols-outlined fs-6">arrow_back</span>
                                Volver al calendario
                            </a>
                            <button type="submit" id="btnConfirmarReserva" class="btn btn--primary py-3 px-4 fs-6">
                                <span class="material-symbols-outlined">check_circle</span>
                                Confirmar Reserva
                            </button>
                        </div>
                    </form>
                </div>

            </section>

        </div>
    </main>

    <?= $this->include('modals/footer') ?>
    <?= $this->include('modals/modalsBase') ?>

    <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            apiUrl: '<?= rtrim(base_url('api'), '/') ?>'
        };
    </script>
    <script src="<?= base_url('public/assets/js/index.js?v=' . time()) ?>" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('confirmarReservaForm');
        const btnSubmit = document.getElementById('btnConfirmarReserva');

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            form.classList.add('was-validated');

            Swal.fire({
                title: '¿Confirmar tu reserva?',
                text: 'Reservaremos tu turno para <?= esc($h['s_nbre'] ?? '') ?> el día <?= esc($fechaFormateada) ?> a las <?= esc($horaDesde) ?> hs con <?= esc($h['nombre_profesional'] ?? '') ?>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d6858e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar reserva',
                cancelButtonText: 'Revisar datos',
                reverseButtons: true
            }).then(async (result) => {
                if (!result.isConfirmed) return;

                btnSubmit.disabled = true;
                const originalHtml = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<span>Procesando...</span>';

                try {
                    const payload = {
                        h_id: document.getElementById('inputHId').value,
                        u_nbreCompleto: document.getElementById('u_nbreCompleto').value.trim(),
                        u_tel: document.getElementById('u_tel').value.trim(),
                        u_correo: document.getElementById('u_correo').value.trim(),
                        consideraciones: document.getElementById('consideraciones').value.trim()
                    };

                    const res = await fetch('<?= base_url('api/reservas/confirmar') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok) {
                        Swal.fire({
                            title: '¡Reserva Solicitada!',
                            text: data.message || 'Tu turno fue registrado exitosamente. La profesional se pondrá en contacto con vos pronto.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e',
                            confirmButtonText: 'Ver Mis Turnos'
                        }).then(() => {
                            window.location.href = data.redirect_url || '<?= base_url('mis-turnos') ?>';
                        });
                    } else {
                        const msg = data.messages?.error || data.message || 'No se pudo completar la reserva.';
                        Swal.fire({
                            title: 'Atención',
                            text: msg,
                            icon: res.status === 409 ? 'warning' : 'error',
                            confirmButtonColor: '#d6858e'
                        }).then(() => {
                            if (res.status === 409) {
                                window.location.href = '<?= base_url('reservar/' . ($h['s_id'] ?? 0) . '?ocupado=1') ?>';
                            }
                        });
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalHtml;
                    }
                } catch (err) {
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'Ocurrió un problema al conectar con el servidor. Intentá nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#d6858e'
                    });
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalHtml;
                }
            });
        });
    });
    </script>
</body>

</html>

