<?php
$pageTitle = 'Mi Perfil';
$activeNav = 'perfil';
$this->extend('profesional/_layout');
$this->section('content');

$u = $usuario_data ?? [];
$p = $profesional_data ?? null;
$redes = $redes_data ?? [];

$defaultAvatar = base_url('public/assets/media/íconoPerfil.png');
$avatar = !empty($u['u_avatar']) ? base_url($u['u_avatar']) : $defaultAvatar;
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Mi Perfil</h2>
        <p class="admin-page-header__subtitle">Tu información personal y de acceso</p>
    </div>
</div>

<div class="admin-profile-grid">

    <!-- Card izquierda: avatar + resumen -->
    <div class="admin-profile-card admin-profile-card--summary">
        <img id="profileAvatarImg" src="<?= esc($avatar) ?>" class="admin-profile-card__avatar <?= empty($u['u_avatar']) ? 'avatar-default' : 'avatar-custom' ?>" alt="Foto de perfil" />

        <p class="admin-profile-card__name"><?= esc($u['u_nbreCompleto'] ?? '-') ?></p>
        <p class="admin-profile-card__role"><?= $p ? esc($p['p_titulo'] ?? 'Profesional') : 'Administrador' ?></p>
        <p class="admin-profile-card__email"><?= esc($u['u_correo'] ?? '') ?></p>

        <?php if (!empty($u['u_fRegistro'])): ?>
            <p class="admin-text-sm text-muted mt-2">
                Miembro desde <?= date('d/m/Y', strtotime($u['u_fRegistro'])) ?>
            </p>
        <?php endif; ?>

        <input type="file" id="avatarInput" accept="image/jpeg, image/png, image/webp" class="d-none" />
        <button type="button" id="btnChangeAvatar" class="btn btn--outline admin-w-100 mt-3 admin-btn-sm d-inline-flex align-items-center justify-content-center gap-2">
            <span class="material-symbols-outlined" style="font-size: 1.1rem;">photo_camera</span>
            <span>Cambiar foto</span>
        </button>
        <div id="avatarFeedback" class="admin-text-sm mt-2"></div>
    </div>

    <!-- Card derecha: formulario de edición -->
    <div class="admin-table-card admin-profile-card admin-profile-card--form">
        <h3 class="admin-table-card__title mb-4">Editar datos</h3>

        <form id="perfilForm" novalidate>
            <div id="perfilFeedback" class="alert d-none admin-modal-alert mb-3"></div>

            <div class="admin-grid-2">
                <div class="form-group m-0">
                    <label class="form-label" for="nombre_completo">Nombre completo</label>
                    <input class="form-input" type="text" id="nombre_completo" name="nombre_completo"
                           value="<?= esc($u['u_nbreCompleto'] ?? '') ?>" required minlength="3" />
                    <div class="invalid-feedback">El nombre completo es obligatorio (mínimo 3 caracteres).</div>
                </div>
                <div class="form-group m-0">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input class="form-input" type="tel" id="telefono" name="telefono"
                           value="<?= esc($u['u_tel'] ?? '') ?>" />
                    <div class="invalid-feedback">El teléfono debe contener al menos 8 dígitos.</div>
                </div>
            </div>

            <div class="form-group admin-form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input class="form-input" type="email" id="correo" name="correo"
                       value="<?= esc($u['u_correo'] ?? '') ?>" required />
                <div class="invalid-feedback">Ingresá un correo electrónico válido.</div>
            </div>

            <?php if ($p): ?>
            <div class="form-group admin-form-group">
                <label class="form-label" for="titulo">Título Profesional</label>
                <input class="form-input" type="text" id="titulo" name="titulo"
                       value="<?= esc($p['p_titulo'] ?? '') ?>" placeholder="Ej: Especialista en Uñas" required minlength="3" />
                <div class="invalid-feedback">El título profesional es obligatorio (mínimo 3 caracteres).</div>
            </div>
            <?php endif; ?>

            <div class="admin-profile-actions d-flex justify-content-between align-items-center mt-4">
                <button type="button" class="btn btn--outline admin-btn-sm text-muted" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    Cambiar contraseña
                </button>
                <button type="submit" class="btn btn--primary admin-btn-sm">
                    Guardar cambios
                </button>
            </div>
        </form>

        <?php if ($p): ?>
        <!-- REDES SOCIALES -->
        <hr class="admin-hr" />
        <h3 class="admin-table-card__title mb-3">Mis Redes Sociales</h3>
        <p class="admin-text-sm text-muted mb-4">
            Añade tus redes sociales. Solo se permiten enlaces seguros (<code>https://</code>).
        </p>

        <form id="redesForm">
            <input type="hidden" name="redes_sync" value="1" />
            <div id="redesFeedback" class="alert d-none admin-modal-alert mb-3"></div>
            
            <div id="redesContainer">
                <?php if (empty($redes)): ?>
                    <div id="noRedesMessage" class="text-muted admin-text-sm py-2 mb-3">
                        No tienes redes sociales configuradas actualmente. Haz clic en <strong>Añadir red</strong> para añadir una.
                    </div>
                <?php else: ?>
                    <?php 
                        $phMap = [
                            'instagram' => 'https://instagram.com/tu_usuario',
                            'facebook'  => 'https://facebook.com/tu_pagina',
                            'tiktok'    => 'https://tiktok.com/@tu_usuario',
                            'x'         => 'https://x.com/tu_usuario',
                            'linkedin'  => 'https://linkedin.com/in/tu_usuario',
                            'whatsapp'  => 'https://wa.me/54911xxxxxxxx',
                        ];
                        foreach ($redes as $i => $red): 
                            $rTipo = $red['rs_tipo'] ?? $red['tipo'] ?? '';
                            $rLink = $red['rs_link'] ?? $red['link'] ?? '';
                            $rPlaceholder = $phMap[$rTipo] ?? 'https://...';
                    ?>
                        <div class="red-social-row admin-social-row">
                            <select name="redes[<?= $i ?>][tipo]" class="form-control admin-social-select">
                                <option value="">Seleccionar...</option>
                                <option value="instagram" <?= $rTipo === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                                <option value="facebook" <?= $rTipo === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                                <option value="tiktok" <?= $rTipo === 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                                <option value="x" <?= $rTipo === 'x' ? 'selected' : '' ?>>X (Twitter)</option>
                                <option value="linkedin" <?= $rTipo === 'linkedin' ? 'selected' : '' ?>>LinkedIn</option>
                                <option value="whatsapp" <?= $rTipo === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            </select>
                            <input type="url" name="redes[<?= $i ?>][link]" class="form-control admin-social-input" value="<?= esc($rLink) ?>" placeholder="<?= $rPlaceholder ?>" />
                            <button type="button" class="btn btn-outline-danger btn-remove-red admin-form-input" title="Eliminar red social">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="btnAddRed" class="btn btn--outline admin-btn-sm mb-4 d-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-5">add</span> Añadir red
            </button>

            <div class="d-flex justify-content-end admin-social-actions">
                <button type="submit" class="btn btn--primary admin-btn-sm">
                    Guardar redes
                </button>
            </div>
        </form>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="passwordForm" novalidate>
                    <div id="passwordFeedback" class="alert d-none admin-modal-alert"></div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="password_antigua">Contraseña actual</label>
                        <input type="password" id="password_antigua" name="password_antigua" class="form-control admin-form-input" required />
                        <div class="invalid-feedback">Ingresá tu contraseña actual.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="password_nueva">Nueva contraseña</label>
                        <input type="password" id="password_nueva" name="password_nueva" class="form-control admin-form-input" required minlength="8" />
                        <div class="invalid-feedback">La nueva contraseña debe tener al menos 8 caracteres.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="password_confirmar">Confirmar contraseña</label>
                        <input type="password" id="password_confirmar" name="password_confirmar" class="form-control admin-form-input" required minlength="8" />
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn--primary">Actualizar</button>
                    </div>
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

<!-- Modal Encuadrar Foto de Perfil -->
<div class="modal fade" id="avatarCropModal" tabindex="-1" aria-labelledby="avatarCropTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="avatarCropTitle">Encuadrar Foto de Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <p class="text-muted text-center mb-3">Arrastrá la imagen para centrarla y usá el control para acercar o alejar.</p>
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
            <div class="modal-footer admin-modal-footer d-flex justify-content-between p-3">
                <button type="button" class="btn btn--outline" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarAvatar" class="btn btn--primary d-inline-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" id="btnGuardarAvatarIcon">save</span>
                    <span id="btnGuardarAvatarText">Guardar Nueva Foto</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    const clearFieldErrors = (form) => {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
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
        if (message) {
            feedback.textContent = message;
        }
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

    // Quitar clases is-invalid automáticamente mientras el usuario escribe
    document.querySelectorAll('#perfilForm input, #passwordForm input').forEach(input => {
        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
                input.classList.remove('is-invalid');
            }
        });
    });

    // Actualizar Perfil
    const perfilForm = document.getElementById('perfilForm');
    perfilForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(perfilForm);

        let hasClientError = false;
        const nombreInput = document.getElementById('nombre_completo');
        const correoInput = document.getElementById('correo');
        const telefonoInput = document.getElementById('telefono');
        const tituloInput = document.getElementById('titulo');

        const nombreVal = nombreInput ? nombreInput.value.trim() : '';
        if (!nombreVal) {
            showFieldError(nombreInput, 'El nombre completo es obligatorio.');
            hasClientError = true;
        } else if (nombreVal.length < 3) {
            showFieldError(nombreInput, 'El nombre debe tener al menos 3 caracteres.');
            hasClientError = true;
        }

        const correoVal = correoInput ? correoInput.value.trim() : '';
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!correoVal) {
            showFieldError(correoInput, 'El correo electrónico es obligatorio.');
            hasClientError = true;
        } else if (!emailRegex.test(correoVal)) {
            showFieldError(correoInput, 'Ingresá un correo electrónico válido.');
            hasClientError = true;
        }

        if (telefonoInput && telefonoInput.value.trim()) {
            const digits = telefonoInput.value.replace(/[^0-9]/g, '');
            if (digits.length < 8) {
                showFieldError(telefonoInput, 'El teléfono debe contener al menos 8 dígitos.');
                hasClientError = true;
            }
        }

        if (tituloInput) {
            const tituloVal = tituloInput.value.trim();
            if (!tituloVal) {
                showFieldError(tituloInput, 'El título profesional es obligatorio.');
                hasClientError = true;
            } else if (tituloVal.length < 3) {
                showFieldError(tituloInput, 'El título profesional debe tener al menos 3 caracteres.');
                hasClientError = true;
            }
        }

        if (hasClientError) {
            const first = perfilForm.querySelector('.is-invalid');
            if (first) first.focus();
            return;
        }

        const fd = new FormData(perfilForm);
        const submitBtn = perfilForm.querySelector('button[type="submit"]');
        const origText = submitBtn ? submitBtn.innerText : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Guardando...';
        }

        try {
            const res = await fetch("<?= base_url('api/perfil/actualizar') ?>", { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message || 'Perfil actualizado correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#d6858e',
                    timer: 2000,
                    showConfirmButton: false
                });
                const nameEl = document.querySelector('.admin-profile-card__name');
                if (nameEl) nameEl.textContent = nombreVal;
                const emailEl = document.querySelector('.admin-profile-card__email');
                if (emailEl) emailEl.textContent = correoVal;
                const roleEl = document.querySelector('.admin-profile-card__role');
                if (roleEl && tituloInput) roleEl.textContent = tituloInput.value.trim();
            } else {
                const hadFieldErrors = applyServerErrors(perfilForm, data.errors || data.messages);
                if (!hadFieldErrors) {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || data.messages?.error || 'No se pudo actualizar el perfil.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        } catch (err) {
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = origText;
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
    const BRAND_COLOR = '#4a2c1d';

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

    document.getElementById('btnChangeAvatar').addEventListener('click', () => {
        avatarInput.click();
    });

    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (!file) return;

        // Validación de formato y extensión
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
                confirmButtonColor: BRAND_COLOR
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
                confirmButtonColor: BRAND_COLOR
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
                Swal.fire({ title: 'Error', text: 'No se pudo procesar la imagen.', icon: 'error', confirmButtonColor: BRAND_COLOR });
                resetBtnGuardar();
                return;
            }

            const fd = new FormData();
            fd.append('avatar', blob, 'avatar.jpg');

            try {
                const res = await fetch("<?= base_url('api/perfil/avatar') ?>", {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (res.ok) {
                    cropModal.hide();
                    const newUrl = data.avatar_url + '?t=' + Date.now();
                    const avatarImg = document.getElementById('profileAvatarImg');
                    if (avatarImg) {
                        avatarImg.src = newUrl;
                        avatarImg.style.objectFit = 'cover';
                        avatarImg.style.padding = '0';
                        avatarImg.classList.remove('avatar-default');
                        avatarImg.classList.add('avatar-custom');
                    }
                    const sidebarAvatar = document.querySelector('.admin-sidebar__avatar');
                    if (sidebarAvatar) {
                        sidebarAvatar.innerHTML = `<img src="${newUrl}" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" />`;
                    }

                    Swal.fire({
                        title: '¡Foto actualizada!',
                        text: data.message || 'Tu nueva foto de perfil ha sido guardada correctamente.',
                        icon: 'success',
                        confirmButtonColor: BRAND_COLOR
                    });
                } else {
                    Swal.fire({
                        title: 'Error al subir',
                        text: data.messages?.error || data.message || 'No se pudo guardar la imagen.',
                        icon: 'error',
                        confirmButtonColor: BRAND_COLOR
                    });
                }
            } catch (err) {
                Swal.fire({
                    title: 'Error de conexión',
                    text: 'No se pudo comunicar con el servidor.',
                    icon: 'error',
                    confirmButtonColor: BRAND_COLOR
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

    // Cambiar Password
    const passwordForm = document.getElementById('passwordForm');
    passwordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(passwordForm);

        const antigua = document.getElementById('password_antigua');
        const nueva = document.getElementById('password_nueva');
        const confirmar = document.getElementById('password_confirmar');

        let hasClientError = false;
        if (!antigua || !antigua.value) {
            showFieldError(antigua, 'La contraseña actual es obligatoria.');
            hasClientError = true;
        }
        if (!nueva || !nueva.value) {
            showFieldError(nueva, 'La nueva contraseña es obligatoria.');
            hasClientError = true;
        } else if (nueva.value.length < 8) {
            showFieldError(nueva, 'La nueva contraseña debe tener al menos 8 caracteres.');
            hasClientError = true;
        }
        if (!confirmar || !confirmar.value) {
            showFieldError(confirmar, 'Debes confirmar la nueva contraseña.');
            hasClientError = true;
        } else if (nueva && nueva.value !== confirmar.value) {
            showFieldError(confirmar, 'Las contraseñas no coinciden.');
            hasClientError = true;
        }

        if (hasClientError) {
            const first = passwordForm.querySelector('.is-invalid');
            if (first) first.focus();
            return;
        }

        const fd = new FormData(passwordForm);
        try {
            const res = await fetch("<?= base_url('api/perfil/password') ?>", { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message || 'Contraseña actualizada correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#d6858e',
                    timer: 2000,
                    showConfirmButton: false
                });
                passwordForm.reset();
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'))?.hide();
                }, 1500);
            } else {
                const hadFieldErrors = applyServerErrors(passwordForm, data.errors || data.messages);
                if (!hadFieldErrors) {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || data.messages?.error || 'Error al cambiar contraseña.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        } catch (err) {
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    });

    // Añadir/quitar redes sociales dinámicamente
    <?php if ($p): ?>
    let redIndex = <?= count($redes) > 0 ? count($redes) : 0 ?>;
    
    const placeholdersMap = {
        instagram: 'https://instagram.com/tu_usuario',
        facebook: 'https://facebook.com/tu_pagina',
        tiktok: 'https://tiktok.com/@tu_usuario',
        x: 'https://x.com/tu_usuario',
        linkedin: 'https://linkedin.com/in/tu_usuario',
        whatsapp: 'https://wa.me/54911xxxxxxxx'
    };

    const allowedHostsMap = {
        instagram: ['instagram.com', 'www.instagram.com'],
        facebook: ['facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com', 'm.facebook.com'],
        tiktok: ['tiktok.com', 'www.tiktok.com'],
        x: ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'],
        linkedin: ['linkedin.com', 'www.linkedin.com'],
        whatsapp: ['wa.me', 'api.whatsapp.com', 'chat.whatsapp.com', 'web.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com']
    };

    const checkEmptyRedes = () => {
        const container = document.getElementById('redesContainer');
        const rows = container.querySelectorAll('.red-social-row');
        let emptyMsg = document.getElementById('noRedesMessage');
        if (rows.length === 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('div');
                emptyMsg.id = 'noRedesMessage';
                emptyMsg.className = 'text-muted admin-text-sm py-2 mb-3';
                emptyMsg.innerHTML = 'No tienes redes sociales configuradas actualmente. Haz clic en <strong>Añadir red</strong> para añadir una.';
                container.appendChild(emptyMsg);
            }
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    };

    // Actualizar placeholder automáticamente al cambiar la plataforma
    document.getElementById('redesContainer').addEventListener('change', (e) => {
        if (e.target.classList.contains('admin-social-select')) {
            const row = e.target.closest('.red-social-row');
            const input = row ? row.querySelector('input') : null;
            if (input) {
                input.placeholder = placeholdersMap[e.target.value] || 'https://...';
            }
        }
    });

    document.getElementById('btnAddRed').addEventListener('click', () => {
        const container = document.getElementById('redesContainer');
        const emptyMsg = document.getElementById('noRedesMessage');
        if (emptyMsg) {
            emptyMsg.remove();
        }
        const row = document.createElement('div');
        row.className = 'red-social-row admin-social-row';
        row.innerHTML = `
            <select name="redes[${redIndex}][tipo]" class="form-control admin-social-select">
                <option value="">Seleccionar...</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="tiktok">TikTok</option>
                <option value="x">X (Twitter)</option>
                <option value="linkedin">LinkedIn</option>
                <option value="whatsapp">WhatsApp</option>
            </select>
            <input type="url" name="redes[${redIndex}][link]" class="form-control admin-social-input" placeholder="https://..." />
            <button type="button" class="btn btn-outline-danger btn-remove-red admin-form-input" title="Eliminar red social">&times;</button>
        `;
        container.appendChild(row);
        redIndex++;
    });

    document.getElementById('redesContainer').addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-remove-red');
        if (!btn) return;

        const row = btn.closest('.red-social-row');
        if (!row) return;

        const select = row.querySelector('select');
        const input = row.querySelector('input');
        const tipoVal = select ? select.value : '';
        const linkVal = input ? input.value.trim() : '';

        // Si la fila estaba totalmente vacía, quitarla directamente
        if (!tipoVal && !linkVal) {
            row.remove();
            checkEmptyRedes();
            return;
        }

        const tipoLabel = tipoVal ? select.options[select.selectedIndex]?.text : 'esta red social';

        Swal.fire({
            title: '¿Eliminar red social?',
            text: `¿Estás segura de que deseas eliminar ${tipoLabel}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                row.remove();
                checkEmptyRedes();

                // Persistir inmediatamente los cambios en la base de datos
                const form = document.getElementById('redesForm');
                const fd = new FormData(form);

                try {
                    const res = await fetch("<?= base_url('api/perfil/redes') ?>", { method: 'POST', body: fd });
                    const data = await res.json();
                    if (res.ok) {
                        Swal.fire({
                            title: '¡Eliminada!',
                            text: 'La red social se eliminó y los cambios fueron guardados.',
                            icon: 'success',
                            timer: 1800,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.messages?.error || data.message || 'No se pudieron guardar los cambios.',
                            icon: 'error'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo comunicar con el servidor.',
                        icon: 'error'
                    });
                }
            }
        });
    });

    const showRowError = (element, message) => {
        if (!element) return;
        element.classList.add('is-invalid');
        const row = element.closest('.red-social-row');
        if (row) {
            let feedback = row.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block w-100';
                row.appendChild(feedback);
            }
            feedback.textContent = message;
        }
        element.focus();
    };

    const clearRedesErrors = () => {
        document.querySelectorAll('#redesContainer .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('#redesContainer .invalid-feedback').forEach(el => el.remove());
    };

    document.getElementById('redesContainer').addEventListener('input', (e) => {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const row = e.target.closest('.red-social-row');
            if (row) {
                const fb = row.querySelector('.invalid-feedback');
                if (fb) fb.remove();
            }
        }
    });

    document.getElementById('redesContainer').addEventListener('change', (e) => {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const row = e.target.closest('.red-social-row');
            if (row) {
                const fb = row.querySelector('.invalid-feedback');
                if (fb) fb.remove();
            }
        }
    });

    const validateRedesForm = () => {
        clearRedesErrors();
        const rows = document.querySelectorAll('#redesContainer .red-social-row');
        const seenTipos = new Set();

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const select = row.querySelector('select');
            const input = row.querySelector('input');
            const tipo = select ? select.value.trim().toLowerCase() : '';
            let link = input ? input.value.trim() : '';
            const tipoLabel = select && select.value ? select.options[select.selectedIndex]?.text : 'esta red social';

            // 1. Control de fila completamente vacía
            if (!tipo && !link) {
                showRowError(select, 'Completa los datos de la red social o elimínala antes de guardar.');
                return false;
            }

            // 2. Control de tipo seleccionado pero link vacío
            if (tipo && !link) {
                showRowError(input, `El enlace para ${tipoLabel} no puede estar vacío.`);
                return false;
            }

            // 3. Control de link ingresado pero tipo sin seleccionar
            if (!tipo && link) {
                showRowError(select, 'Debes seleccionar a qué red social corresponde el enlace ingresado.');
                return false;
            }

            // 4. Control de duplicados
            if (seenTipos.has(tipo)) {
                showRowError(select, `No puedes agregar ${tipoLabel} más de una vez.`);
                return false;
            }
            seenTipos.add(tipo);

            // 5. Rechazo estricto de HTTP inseguro
            if (link.toLowerCase().startsWith('http://')) {
                showRowError(input, `El enlace de ${tipoLabel} debe usar protocolo seguro HTTPS (https://) en lugar de HTTP.`);
                return false;
            }

            // 6. Normalizar y validar estructura de URL
            if (!link.includes('/')) {
                if (tipo === 'whatsapp') {
                    const cleanNum = link.replace(/[^0-9]/g, '');
                    if (!cleanNum) {
                        showRowError(input, `Debes ingresar un número telefónico con código de país para WhatsApp. Ejemplo: ${placeholdersMap[tipo]}`);
                        return false;
                    }
                    link = 'https://wa.me/' + cleanNum;
                } else if (tipo === 'linkedin') {
                    link = 'https://linkedin.com/in/' + link.replace(/^@/, '');
                } else if (tipo === 'tiktok') {
                    link = 'https://tiktok.com/@' + link.replace(/^@/, '');
                } else {
                    link = `https://${tipo}.com/` + link.replace(/^@/, '');
                }
                if (input) input.value = link;
            } else if (!link.toLowerCase().startsWith('https://')) {
                link = 'https://' + link.replace(/^\/+/, '');
                if (input) input.value = link;
            }

            // 7. Parsear URL y verificar protocolo
            let parsed;
            try {
                parsed = new URL(link);
            } catch (err) {
                showRowError(input, `El enlace ingresado para ${tipoLabel} no es una URL válida. Ejemplo: ${placeholdersMap[tipo]}`);
                return false;
            }

            if (parsed.protocol !== 'https:') {
                showRowError(input, `El enlace de ${tipoLabel} debe ser estrictamente HTTPS.`);
                return false;
            }

            // 8. Dominio oficial auténtico
            const allowed = allowedHostsMap[tipo] || [];
            if (!allowed.includes(parsed.hostname.toLowerCase())) {
                showRowError(input, `El enlace no corresponde a la plataforma oficial de ${tipoLabel}. Debe ser del formato: ${placeholdersMap[tipo]}`);
                return false;
            }

            // 9. Verificar que incluya usuario/perfil real y no solo la portada
            const path = parsed.pathname.replace(/^\/+|\/+$/g, '');
            if (tipo === 'whatsapp') {
                if (!path && !parsed.searchParams.get('phone')) {
                    showRowError(input, `Debes incluir tu número con código de país en el enlace de WhatsApp. Ejemplo: ${placeholdersMap[tipo]}`);
                    return false;
                }
            } else if (!path) {
                showRowError(input, `Debes incluir tu usuario o enlace a tu perfil en ${tipoLabel}, no solo la página de inicio (${placeholdersMap[tipo]}).`);
                return false;
            }
        }

        return true;
    };

    // Guardar Redes manualmente
    document.getElementById('redesForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        // Controlar en frontend antes de enviar
        if (!validateRedesForm()) {
            return;
        }

        const fd = new FormData(e.target);
        
        try {
            const res = await fetch("<?= base_url('api/perfil/redes') ?>", { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message || 'Redes sociales actualizadas correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#d6858e',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.messages?.error || data.message || 'Error al guardar redes.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (err) {
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    });
    <?php endif; ?>

});
</script>

<?php $this->endSection(); ?>





