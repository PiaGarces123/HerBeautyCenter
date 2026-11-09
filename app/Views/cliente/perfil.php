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
                        <span class="cl-stat__label">Por asistir</span>
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
                            <label class="form-label" for="consideraciones">Consideraciones especiales
                                (opcional)</label>
                            <textarea class="form-textarea" id="consideraciones" name="consideraciones" rows="3"
                                maxlength="500"
                                placeholder="Indicá preferencias para tu atención, tipo de piel o alguna consideración especial..."><?= esc($u['consideraciones'] ?? '') ?></textarea>
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

<!-- Estilos específicos del Modal de Encuadre de Avatar -->
<style>
    .avatar-crop-container {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        padding: 1rem 0 !important;
        width: 100% !important;
    }
    .avatar-crop-viewport {
        width: 240px !important;
        height: 240px !important;
        border-radius: 50% !important;
        position: relative !important;
        overflow: hidden !important;
        margin: 0 auto !important;
        border: 4px solid var(--color-primary, #4a2c1d) !important;
        box-shadow: 0 8px 30px rgba(74, 44, 29, 0.25) !important;
        background: #1a1a1a !important;
        cursor: grab !important;
        touch-action: none !important;
        user-select: none !important;
    }
    .avatar-crop-viewport:active {
        cursor: grabbing !important;
    }
    .avatar-crop-viewport img {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform-origin: center center !important;
        max-width: none !important;
        max-height: none !important;
        pointer-events: none !important;
        user-select: none !important;
        will-change: transform;
    }
    .avatar-zoom-controls {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        max-width: 320px !important;
        margin: 1rem auto 0 !important;
        gap: 0.75rem !important;
    }
    .avatar-zoom-controls .form-range {
        cursor: pointer !important;
    }
</style>

<!-- Modal: Encuadrar Foto de Perfil -->
<div class="modal fade cl-modal" id="avatarCropModal" tabindex="-1" aria-labelledby="avatarCropTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="cl-subtitle m-0" id="avatarCropTitle">Encuadrar Foto de Perfil</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="cl-muted text-center mb-3">Arrastrá la imagen para centrarla y usá el control para acercar o alejar.</p>
                <div class="avatar-crop-container" style="display: flex; justify-content: center; align-items: center; padding: 1rem 0; width: 100%;">
                    <div class="avatar-crop-viewport" id="avatarCropViewport" style="width: 240px; height: 240px; border-radius: 50%; position: relative; overflow: hidden; margin: 0 auto; border: 4px solid #4a2c1d; box-shadow: 0 8px 30px rgba(74, 44, 29, 0.25); background: #1a1a1a; cursor: grab; touch-action: none; user-select: none;">
                        <img id="avatarCropPreview" src="" alt="Previsualización" draggable="false" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); transform-origin: center center; max-width: none; max-height: none; pointer-events: none; user-select: none;" />
                    </div>
                </div>
                <div class="avatar-zoom-controls" style="display: flex; align-items: center; justify-content: center; max-width: 320px; margin: 1rem auto 0; gap: 0.75rem;">
                    <span class="material-symbols-outlined text-muted" style="font-size: 1.25rem;">zoom_out</span>
                    <input type="range" class="form-range" id="avatarZoomRange" min="1" max="3" step="0.02" value="1" style="flex: 1; cursor: pointer;" />
                    <span class="material-symbols-outlined text-muted" style="font-size: 1.25rem;">zoom_in</span>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">Podés arrastrar la imagen para ajustar el encuadre.</small>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between p-3">
                <button type="button" class="btn btn--outline cl-btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarAvatar" class="btn btn--primary cl-btn-sm d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" id="btnGuardarAvatarIcon">save</span>
                    <span id="btnGuardarAvatarText">Guardar Nueva Foto</span>
                </button>
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

        // Manejo y encuadre de Avatar
        const avatarInput = document.getElementById('avatarInput');
        const cropModalEl = document.getElementById('avatarCropModal');
        const cropModal = new bootstrap.Modal(cropModalEl);
        const cropViewport = document.getElementById('avatarCropViewport');
        const cropImg = document.getElementById('avatarCropPreview');
        const zoomRange = document.getElementById('avatarZoomRange');
        const btnGuardarAvatar = document.getElementById('btnGuardarAvatar');
        const btnGuardarAvatarIcon = document.getElementById('btnGuardarAvatarIcon');
        const btnGuardarAvatarText = document.getElementById('btnGuardarAvatarText');

        let baseScale = 1;
        let currentZoom = 1;
        let panX = 0;
        let panY = 0;
        let isDragging = false;
        let startPointerX = 0;
        let startPointerY = 0;
        const VIEWPORT_SIZE = 240;

        const updateTransform = () => {
            cropImg.style.transform = `translate(-50%, -50%) translate(${panX}px, ${panY}px) scale(${currentZoom})`;
        };

        const clampOffsets = () => {
            const currentW = cropImg.naturalWidth * baseScale * currentZoom;
            const currentH = cropImg.naturalHeight * baseScale * currentZoom;
            const maxPanX = Math.max(0, (currentW - VIEWPORT_SIZE) / 2);
            const maxPanY = Math.max(0, (currentH - VIEWPORT_SIZE) / 2);
            panX = Math.min(maxPanX, Math.max(-maxPanX, panX));
            panY = Math.min(maxPanY, Math.max(-maxPanY, panY));
        };

        document.getElementById('btnChangeAvatar').addEventListener('click', () => avatarInput.click());

        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (!file) return;

            // Validación de extensión y formato
            const validExts = ['.jpg', '.jpeg', '.png', '.webp'];
            const validMimes = ['image/jpeg', 'image/png', 'image/webp'];
            const nameLower = file.name.toLowerCase();
            const hasValidExt = validExts.some(ext => nameLower.endsWith(ext));
            const hasValidMime = validMimes.includes(file.type);

            if (!hasValidExt || (!hasValidMime && file.type)) {
                Swal.fire({
                    title: 'Formato no permitido',
                    text: 'Por favor seleccioná una imagen en formato JPG, PNG o WEBP.',
                    icon: 'warning',
                    confirmButtonColor: BRAND
                });
                avatarInput.value = '';
                return;
            }

            // Validación de peso (máximo 3 MB)
            const MAX_BYTES = 3 * 1024 * 1024;
            if (file.size > MAX_BYTES) {
                const pesoMb = (file.size / (1024 * 1024)).toFixed(1);
                Swal.fire({
                    title: 'Imagen demasiado pesada',
                    text: `La imagen seleccionada pesa ${pesoMb} MB. El tamaño máximo permitido es de 3 MB.`,
                    icon: 'warning',
                    confirmButtonColor: BRAND
                });
                avatarInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                cropImg.src = e.target.result;
                cropImg.onload = () => {
                    baseScale = Math.max(VIEWPORT_SIZE / cropImg.naturalWidth, VIEWPORT_SIZE / cropImg.naturalHeight);
                    cropImg.style.width = `${cropImg.naturalWidth * baseScale}px`;
                    cropImg.style.height = `${cropImg.naturalHeight * baseScale}px`;
                    currentZoom = 1;
                    zoomRange.value = 1;
                    panX = 0;
                    panY = 0;
                    updateTransform();
                    cropModal.show();
                };
            };
            reader.readAsDataURL(file);
        });

        // Eventos de arrastre en viewport
        cropViewport.addEventListener('pointerdown', (e) => {
            isDragging = true;
            startPointerX = e.clientX - panX;
            startPointerY = e.clientY - panY;
            cropViewport.setPointerCapture(e.pointerId);
        });

        cropViewport.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            panX = e.clientX - startPointerX;
            panY = e.clientY - startPointerY;
            clampOffsets();
            updateTransform();
        });

        const stopDragging = () => { isDragging = false; };
        cropViewport.addEventListener('pointerup', stopDragging);
        cropViewport.addEventListener('pointercancel', stopDragging);

        // Control de Zoom con slider
        zoomRange.addEventListener('input', () => {
            currentZoom = parseFloat(zoomRange.value);
            clampOffsets();
            updateTransform();
        });

        // Guardar nueva foto recortada
        btnGuardarAvatar.addEventListener('click', () => {
            btnGuardarAvatar.disabled = true;
            btnGuardarAvatarIcon.classList.add('d-none');
            btnGuardarAvatarText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Actualizando imagen...';

            Swal.fire({
                title: 'Actualizando imagen',
                text: 'Estamos procesando tu nueva foto de perfil...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            // Generar canvas 400x400
            const canvas = document.createElement('canvas');
            canvas.width = 400;
            canvas.height = 400;
            const ctx = canvas.getContext('2d');
            const factor = 400 / VIEWPORT_SIZE;

            ctx.save();
            ctx.translate(200, 200);
            ctx.scale(factor, factor);
            ctx.translate(panX, panY);
            ctx.scale(currentZoom, currentZoom);
            ctx.drawImage(
                cropImg,
                -(cropImg.naturalWidth * baseScale) / 2,
                -(cropImg.naturalHeight * baseScale) / 2,
                cropImg.naturalWidth * baseScale,
                cropImg.naturalHeight * baseScale
            );
            ctx.restore();

            canvas.toBlob(async (blob) => {
                if (!blob) {
                    Swal.fire({ title: 'Error', text: 'No se pudo procesar la imagen.', icon: 'error', confirmButtonColor: BRAND });
                    resetBtnGuardar();
                    return;
                }

                const fd = new FormData();
                fd.append('avatar', blob, 'avatar.jpg');

                try {
                    const res = await fetch(window.APP_CONFIG.apiUrl + '/perfil/avatar', {
                        method: 'POST',
                        body: fd,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    if (res.ok) {
                        cropModal.hide();
                        const newUrl = data.avatar_url + '?t=' + Date.now();
                        const avatarEl = document.getElementById('profileAvatarImg');
                        if (avatarEl) avatarEl.src = newUrl;

                        document.querySelectorAll('.user-avatar, .header-avatar, .cl-profile__avatar').forEach(img => {
                            img.src = newUrl;
                        });

                        Swal.fire({
                            title: '¡Foto actualizada!',
                            text: data.message || 'Tu nueva foto de perfil ha sido guardada correctamente.',
                            icon: 'success',
                            confirmButtonColor: BRAND
                        });
                    } else {
                        Swal.fire({
                            title: 'Error al subir',
                            text: data.messages?.error || data.message || 'No se pudo guardar la imagen.',
                            icon: 'error',
                            confirmButtonColor: BRAND
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo contactar con el servidor. Intentá nuevamente.',
                        icon: 'error',
                        confirmButtonColor: BRAND
                    });
                } finally {
                    resetBtnGuardar();
                    avatarInput.value = '';
                }
            }, 'image/jpeg', 0.92);
        });

        const resetBtnGuardar = () => {
            btnGuardarAvatar.disabled = false;
            btnGuardarAvatarIcon.classList.remove('d-none');
            btnGuardarAvatarText.textContent = 'Guardar Nueva Foto';
        };

        cropModalEl.addEventListener('shown.bs.modal', () => {
            if (cropImg.naturalWidth) {
                baseScale = Math.max(VIEWPORT_SIZE / cropImg.naturalWidth, VIEWPORT_SIZE / cropImg.naturalHeight);
                cropImg.style.width = `${Math.round(cropImg.naturalWidth * baseScale)}px`;
                cropImg.style.height = `${Math.round(cropImg.naturalHeight * baseScale)}px`;
                clampOffsets();
                updateTransform();
            }
        });

        cropModalEl.addEventListener('hidden.bs.modal', () => {
            avatarInput.value = '';
            resetBtnGuardar();
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