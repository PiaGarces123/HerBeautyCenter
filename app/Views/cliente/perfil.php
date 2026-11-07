<?php
$u = $usuario ?? [];
$prox = $proximoTurno ?? null;
$proxFecha = !empty($prox['fecha']) ? date('d/m', strtotime($prox['fecha'])) : '-';
$avatar = !empty($u['u_avatar']) ? base_url($u['u_avatar']) : base_url('public/assets/media/íconoPerfil.png');
?>
<?= view('cliente/_head', ['pageTitle' => 'Mi Perfil']) ?>

<main class="cl-main">
    <div class="cl-wrap">

        <nav class="cl-tabs" aria-label="Panel del cliente">
            <a href="<?= base_url('mis-turnos') ?>" class="cl-tab">
                <span class="material-symbols-outlined">calendar_month</span> Mis Turnos
            </a>
            <a href="<?= base_url('mi-perfil') ?>" class="cl-tab cl-tab--active" aria-current="page">
                <span class="material-symbols-outlined">person</span> Mi Perfil
            </a>
            <a href="<?= base_url('/#servicios') ?>" class="cl-tab cl-tab--end">
                <span class="material-symbols-outlined">add</span> Nuevo turno
            </a>
        </nav>

        <header class="cl-head cl-rise">
            <span class="cl-eyebrow">Mi cuenta</span>
            <h1 class="cl-title">Mi <em>Perfil</em></h1>
            <p class="cl-lead">Consultá y actualizá tus datos personales, tu foto y tu contraseña.</p>
        </header>

        <div class="cl-profile">

            <!-- Resumen -->
            <aside class="cl-card cl-card--rose cl-profile__card cl-rise cl-rise--2">
                <img id="profileAvatarImg" src="<?= esc($avatar) ?>" class="cl-profile__avatar" alt="Foto de perfil" />
                <p class="cl-profile__name" id="profileDisplayName"><?= esc($u['u_nbreCompleto'] ?? '') ?></p>
                <p class="cl-muted m-0" id="profileDisplayEmail"><?= esc($u['u_correo'] ?? '') ?></p>

                <div class="cl-profile__stats">
                    <div class="cl-stat">
                        <span class="cl-stat__num"><?= (int) ($totalTurnos ?? 0) ?></span>
                        <span class="cl-stat__label">Reservas</span>
                    </div>
                    <div class="cl-stat">
                        <span class="cl-stat__num"><?= esc($proxFecha) ?></span>
                        <span class="cl-stat__label">Próximo turno</span>
                    </div>
                </div>

                <?php if (!empty($u['u_fRegistro'])): ?>
                    <p class="cl-muted">Miembro desde <?= date('d/m/Y', strtotime($u['u_fRegistro'])) ?></p>
                <?php endif; ?>

                <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp" class="d-none" />
                <button type="button" id="btnChangeAvatar" class="btn btn--outline cl-btn-sm w-100">
                    <span class="material-symbols-outlined">photo_camera</span> Cambiar foto
                </button>
            </aside>

            <!-- Formulario -->
            <section class="cl-card cl-rise cl-rise--3" aria-labelledby="editTitle">
                <h2 class="cl-subtitle" id="editTitle">Mis datos personales</h2>
                <p class="cl-muted mb-4">La especialista usará estos datos para contactarte.</p>

                <form id="perfilClienteForm" class="needs-validation" novalidate>
                    <div class="cl-form-grid">
                        <div class="form-group">
                            <label class="form-label" for="nombre_completo">Nombre completo *</label>
                            <input class="form-input" type="text" id="nombre_completo" name="nombre_completo"
                                autocomplete="name" value="<?= esc($u['u_nbreCompleto'] ?? '') ?>" required
                                minlength="3" maxlength="100" />
                            <div class="invalid-feedback">Mínimo 3 caracteres.</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="telefono">Teléfono / WhatsApp *</label>
                            <input class="form-input" type="tel" id="telefono" name="telefono" autocomplete="tel"
                                inputmode="tel" value="<?= esc($u['u_tel'] ?? '') ?>" required minlength="8"
                                maxlength="20" />
                            <div class="invalid-feedback">Mínimo 8 dígitos.</div>
                        </div>
                        <div class="form-group cl-span-2">
                            <label class="form-label" for="correo">Correo electrónico *</label>
                            <input class="form-input" type="email" id="correo" name="correo" autocomplete="email"
                                value="<?= esc($u['u_correo'] ?? '') ?>" required maxlength="120" />
                            <div class="invalid-feedback">Ingresá un correo válido.</div>
                        </div>
                        <div class="form-group cl-span-2">
                            <label class="form-label" for="consideraciones">Alergias o consideraciones
                                (opcional)</label>
                            <textarea class="form-textarea" id="consideraciones" name="consideraciones" rows="3"
                                maxlength="500"
                                placeholder="Indicá si tenés alergias a productos, piel sensible o alguna consideración especial..."><?= esc($u['consideraciones'] ?? '') ?></textarea>
                            <small class="text-muted d-block mt-1">Esta información se autocompletará en el formulario
                                al solicitar tus turnos.</small>
                        </div>
                    </div>

                    <div class="cl-actions mt-4">
                        <button type="button" class="btn btn--outline" data-bs-toggle="modal"
                            data-bs-target="#changePasswordModal">
                            <span class="material-symbols-outlined">lock</span> Cambiar contraseña
                        </button>
                        <button type="submit" class="btn btn--primary">
                            <span class="material-symbols-outlined">save</span> Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</main>

<!-- Modal: Cambiar contraseña -->
<div class="modal fade cl-modal" id="changePasswordModal" tabindex="-1" aria-labelledby="pwdTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="cl-subtitle m-0" id="pwdTitle">Cambiar contraseña</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="passwordClienteForm" class="d-grid gap-3" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="password_antigua_cli">Contraseña actual</label>
                        <input type="password" id="password_antigua_cli" name="password_antigua" class="form-input"
                            autocomplete="current-password" required />
                        <div class="invalid-feedback">Ingresá tu contraseña actual.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_nueva_cli">Nueva contraseña</label>
                        <input type="password" id="password_nueva_cli" name="password_nueva" class="form-input"
                            autocomplete="new-password" required minlength="8" />
                        <div class="invalid-feedback">La nueva contraseña debe tener al menos 8 caracteres.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmar_cli">Confirmar contraseña</label>
                        <input type="password" id="password_confirmar_cli" name="password_confirmar" class="form-input"
                            autocomplete="new-password" required minlength="8" />
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>
                    <button type="submit" class="btn btn--primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('cliente/_scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const BRAND = window.APP_CONFIG.brand;
        const notify = (ok, msg) => Swal.fire({ title: ok ? '¡Listo!' : 'Atención', text: msg, icon: ok ? 'success' : 'error', confirmButtonColor: BRAND });

        // Envío genérico reutilizando los endpoints de PerfilApi
        const send = async (url, body) => {
            try {
                const res = await fetch(url, { method: 'POST', body, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                return { ok: res.ok, data };
            } catch (e) {
                return { ok: false, data: { message: 'Error de conexión.' } };
            }
        };
        const msgOf = (d, def) => d.messages?.error || d.message || def;

        const clearFieldErrors = (form) => {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.classList.remove('was-validated');
        };

        const showFieldError = (input, message) => {
            if (!input) return;
            input.classList.add('is-invalid');
            const parent = input.closest('.form-group') || input.parentElement;
            let feedback = parent ? parent.querySelector('.invalid-feedback') : null;
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                parent.appendChild(feedback);
            }
            if (message) feedback.textContent = message;
        };

        const applyServerErrors = (form, errors) => {
            if (!errors) return false;
            let first = null;
            if (typeof errors === 'object') {
                for (const [key, msg] of Object.entries(errors)) {
                    if (!msg || typeof msg !== 'string') continue;
                    const input = form.querySelector(`[name="${key}"]`) || document.getElementById(key);
                    if (input) {
                        showFieldError(input, msg);
                        if (!first) first = input;
                    }
                }
            }
            if (first) {
                first.focus();
                return true;
            }
            return false;
        };

        document.querySelectorAll('#perfilClienteForm input, #passwordClienteForm input').forEach(input => {
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    input.classList.remove('is-invalid');
                }
            });
        });

        document.getElementById('perfilClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            clearFieldErrors(form);

            let hasClientError = false;
            const nombreInput = form.nombre_completo;
            const telefonoInput = form.telefono;
            const correoInput = form.correo;

            if (!nombreInput.value.trim()) {
                showFieldError(nombreInput, 'El nombre completo es obligatorio.');
                hasClientError = true;
            } else if (nombreInput.value.trim().length < 3) {
                showFieldError(nombreInput, 'Mínimo 3 caracteres.');
                hasClientError = true;
            }

            const telDigits = telefonoInput.value.replace(/[^0-9]/g, '');
            if (!telefonoInput.value.trim()) {
                showFieldError(telefonoInput, 'El teléfono es obligatorio.');
                hasClientError = true;
            } else if (telDigits.length < 8) {
                showFieldError(telefonoInput, 'Mínimo 8 dígitos.');
                hasClientError = true;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoInput.value.trim()) {
                showFieldError(correoInput, 'El correo electrónico es obligatorio.');
                hasClientError = true;
            } else if (!emailRegex.test(correoInput.value.trim())) {
                showFieldError(correoInput, 'Ingresá un correo válido.');
                hasClientError = true;
            }

            if (hasClientError) {
                form.querySelector('.is-invalid')?.focus();
                return;
            }

            const { ok, data } = await send(window.APP_CONFIG.apiUrl + '/perfil/actualizar', new FormData(form));
            if (ok) {
                notify(true, data.message || 'Datos actualizados correctamente.');
                document.getElementById('profileDisplayName').textContent = form.nombre_completo.value;
                document.getElementById('profileDisplayEmail').textContent = form.correo.value;
            } else {
                const hadFieldErrors = applyServerErrors(form, data.errors || data.messages);
                if (!hadFieldErrors) {
                    notify(false, msgOf(data, 'No se pudo actualizar.'));
                }
            }
        });

        const avatarInput = document.getElementById('avatarInput');
        document.getElementById('btnChangeAvatar').addEventListener('click', () => avatarInput.click());
        avatarInput.addEventListener('change', async () => {
            const file = avatarInput.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                notify(false, 'La imagen no puede superar los 2 MB.');
                avatarInput.value = '';
                return;
            }
            const fd = new FormData();
            fd.append('avatar', file);
            const { ok, data } = await send(window.APP_CONFIG.apiUrl + '/perfil/avatar', fd);
            notify(ok, msgOf(data, ok ? 'Foto actualizada.' : 'No se pudo subir la imagen.'));
            if (ok && data.avatar_url) document.getElementById('profileAvatarImg').src = data.avatar_url;
            avatarInput.value = '';
        });

        document.getElementById('passwordClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            clearFieldErrors(form);

            const antigua = form.password_antigua;
            const nueva = form.password_nueva;
            const confirmar = form.password_confirmar;

            let hasClientError = false;
            if (!antigua.value) {
                showFieldError(antigua, 'La contraseña actual es obligatoria.');
                hasClientError = true;
            }
            if (!nueva.value) {
                showFieldError(nueva, 'La nueva contraseña es obligatoria.');
                hasClientError = true;
            } else if (nueva.value.length < 8) {
                showFieldError(nueva, 'Mínimo 8 caracteres.');
                hasClientError = true;
            }
            if (!confirmar.value) {
                showFieldError(confirmar, 'Debes confirmar la nueva contraseña.');
                hasClientError = true;
            } else if (nueva.value !== confirmar.value) {
                showFieldError(confirmar, 'Las contraseñas no coinciden.');
                hasClientError = true;
            }

            if (hasClientError) {
                form.querySelector('.is-invalid')?.focus();
                return;
            }

            const { ok, data } = await send(window.APP_CONFIG.apiUrl + '/perfil/password', new FormData(form));
            if (ok) {
                notify(true, data.message || 'Contraseña actualizada correctamente.');
                form.reset();
                bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'))?.hide();
            } else {
                const hadFieldErrors = applyServerErrors(form, data.errors || data.messages);
                if (!hadFieldErrors) {
                    notify(false, msgOf(data, 'No se pudo actualizar la contraseña.'));
                }
            }
        });
    });
</script>
</body>

</html>