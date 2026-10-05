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
    <div class="admin-profile-card">
        <img id="profileAvatarImg" src="<?= esc($avatar) ?>" class="admin-profile-card__avatar" alt="Foto de perfil" class="<?= empty($u['u_avatar']) ? 'avatar-default' : 'avatar-custom' ?>" />

        <p class="admin-profile-card__name"><?= esc($u['u_nbreCompleto'] ?? '—') ?></p>
        <p class="admin-profile-card__role"><?= $p ? 'Profesional' : 'Administrador' ?></p>
        <p class="admin-profile-card__email"><?= esc($u['u_correo'] ?? '') ?></p>

        <?php if (!empty($u['u_fRegistro'])): ?>
            <p class="admin-text-sm text-muted mt-2">
                Miembro desde <?= date('d/m/Y', strtotime($u['u_fRegistro'])) ?>
            </p>
        <?php endif; ?>

        <input type="file" id="avatarInput" accept="image/jpeg, image/png, image/webp" class="d-none" />
        <button type="button" id="btnChangeAvatar" class="btn btn--outline admin-w-100 mt-3 admin-btn-sm">
            Cambiar foto
        </button>
        <div id="avatarFeedback" class="admin-text-sm mt-2"></div>
    </div>

    <!-- Card derecha: formulario de edición -->
    <div class="admin-table-card admin-profile-card">
        <h3 class="admin-table-card__title mb-4">Editar datos</h3>

        <form id="perfilForm">
            <div id="perfilFeedback" class="alert d-none admin-modal-alert mb-3"></div>

            <div class="admin-grid-2">
                <div class="form-group m-0">
                    <label class="form-label" for="nombre_completo">Nombre completo</label>
                    <input class="form-input" type="text" id="nombre_completo" name="nombre_completo"
                           value="<?= esc($u['u_nbreCompleto'] ?? '') ?>" required />
                </div>
                <div class="form-group m-0">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input class="form-input" type="tel" id="telefono" name="telefono"
                           value="<?= esc($u['u_tel'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-group admin-form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <input class="form-input" type="email" id="correo" name="correo"
                       value="<?= esc($u['u_correo'] ?? '') ?>" required />
            </div>

            <?php if ($p): ?>
            <div class="form-group admin-form-group">
                <label class="form-label" for="titulo">Título Profesional</label>
                <input class="form-input" type="text" id="titulo" name="titulo"
                       value="<?= esc($p['p_titulo'] ?? '') ?>" placeholder="Ej: Especialista en Uñas" required minlength="3" />
            </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="button" class="btn btn--outline" data-bs-toggle="modal" data-bs-target="#changePasswordModal" class="admin-btn-sm text-muted">
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
            <div id="redesFeedback" class="alert d-none admin-modal-alert mb-3"></div>
            
            <div id="redesContainer">
                <?php if (empty($redes)): ?>
                    <div class="red-social-row admin-social-row">
                        <select name="redes[0][tipo]" class="form-control admin-social-select">
                            <option value="">Seleccionar...</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                            <option value="tiktok">TikTok</option>
                            <option value="x">X (Twitter)</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                        <input type="url" name="redes[0][link]" class="form-control" placeholder="https://..." class="admin-social-input" />
                        <button type="button" class="btn btn-outline-danger btn-remove-red admin-form-input">&times;</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($redes as $i => $red): ?>
                        <div class="red-social-row admin-social-row">
                            <select name="redes[<?= $i ?>][tipo]" class="form-control admin-social-select">
                                <option value="">Seleccionar...</option>
                                <option value="instagram" <?= $red['tipo'] == 'instagram' ? 'selected' : '' ?>>Instagram</option>
                                <option value="facebook" <?= $red['tipo'] == 'facebook' ? 'selected' : '' ?>>Facebook</option>
                                <option value="tiktok" <?= $red['tipo'] == 'tiktok' ? 'selected' : '' ?>>TikTok</option>
                                <option value="x" <?= $red['tipo'] == 'x' ? 'selected' : '' ?>>X (Twitter)</option>
                                <option value="linkedin" <?= $red['tipo'] == 'linkedin' ? 'selected' : '' ?>>LinkedIn</option>
                                <option value="whatsapp" <?= $red['tipo'] == 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            </select>
                            <input type="url" name="redes[<?= $i ?>][link]" class="form-control" value="<?= esc($red['link']) ?>" placeholder="https://..." class="admin-social-input" />
                            <button type="button" class="btn btn-outline-danger btn-remove-red admin-form-input">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button" id="btnAddRed" class="btn btn--outline admin-btn-sm mb-4 d-flex align-items-center gap-1">
                <span class="material-symbols-outlined fs-5">add</span> Añadir red
            </button>

            <div class="d-flex justify-content-end">
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
                <form id="passwordForm">
                    <div id="passwordFeedback" class="alert d-none admin-modal-alert"></div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="password_antigua" class="form-control" required class="admin-form-input">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password_nueva" class="form-control" required minlength="8" class="admin-form-input">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmar" class="form-control" required minlength="8" class="admin-form-input">
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn--primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // Función genérica para mostrar mensajes con SweetAlert
    const showFeedback = (success, message) => {
        Swal.fire({
            title: success ? '¡Éxito!' : 'Error',
            text: message,
            icon: success ? 'success' : 'error',
            confirmButtonColor: success ? '#d6858e' : '#d33',
            timer: success ? 2500 : undefined
        });
    };

    // Actualizar Perfil
    document.getElementById('perfilForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!e.target.checkValidity()) {
            e.target.reportValidity();
            return;
        }

        const fd = new FormData(e.target);
        
        try {
            const res = await fetch('<?= base_url('api/perfil/actualizar') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                showFeedback(true, data.message);
                document.querySelector('.admin-profile-card__name').textContent = document.getElementById('nombre_completo').value;
                document.querySelector('.admin-profile-card__email').textContent = document.getElementById('correo').value;
            } else {
                showFeedback(false, data.messages?.error || 'Error al actualizar');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        }
    });

    // Subir Avatar
    document.getElementById('btnChangeAvatar').addEventListener('click', () => {
        document.getElementById('avatarInput').click();
    });

    document.getElementById('avatarInput').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const fd = new FormData();
        fd.append('avatar', file);

        try {
            const res = await fetch('<?= base_url('api/perfil/avatar') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (res.ok) {
                showFeedback(true, data.message);
                document.getElementById('profileAvatarImg').src = data.avatar_url;
                document.getElementById('profileAvatarImg').style.objectFit = 'cover';
                document.getElementById('profileAvatarImg').style.padding = '0';
            } else {
                showFeedback(false, data.messages?.error || 'Error al subir la imagen');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        }
    });

    // Cambiar Password
    document.getElementById('passwordForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        
        try {
            const res = await fetch('<?= base_url('api/perfil/password') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                showFeedback(true, data.message);
                e.target.reset();
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                }, 1500);
            } else {
                showFeedback(false, data.messages?.error || 'Error al cambiar contraseña');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        }
    });

    // Añadir/quitar redes sociales dinámicamente
    <?php if ($p): ?>
    let redIndex = <?= count($redes) > 0 ? count($redes) : 1 ?>;
    
    document.getElementById('btnAddRed').addEventListener('click', () => {
        const container = document.getElementById('redesContainer');
        const row = document.createElement('div');
        row.className = 'red-social-row';
        row.style = 'display: flex; gap: 0.5rem; margin-bottom: 0.5rem;';
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
            <input type="url" name="redes[${redIndex}][link]" class="form-control" placeholder="https://..." class="admin-social-input" />
            <button type="button" class="btn btn-outline-danger btn-remove-red admin-form-input">&times;</button>
        `;
        container.appendChild(row);
        redIndex++;
    });

    document.getElementById('redesContainer').addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-red')) {
            Swal.fire({
                title: '¿Eliminar red social?',
                text: "Se quitará esta fila. Recuerda guardar los cambios.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.closest('.red-social-row').remove();
                    Swal.fire({
                        title: '¡Eliminada!',
                        text: 'Fila removida. No olvides guardar.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });

    // Guardar Redes
    document.getElementById('redesForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        
        try {
            const res = await fetch('<?= base_url('api/perfil/redes') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                showFeedback(true, data.message);
            } else {
                showFeedback(false, data.messages?.error || 'Error al guardar');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        }
    });
    <?php endif; ?>

});
</script>

<?php $this->endSection(); ?>





