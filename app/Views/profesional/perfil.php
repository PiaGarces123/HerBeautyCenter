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
        fd.append('u_avatar', file);

        try {
            const res = await fetch('<?= base_url('api/perfil/avatar') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (res.ok) {
                showFeedback(true, data.message || 'Avatar actualizado correctamente.');
                const avatarImg = document.getElementById('profileAvatarImg');
                if (avatarImg) {
                    avatarImg.src = data.avatar_url;
                    avatarImg.style.objectFit = 'cover';
                    avatarImg.style.padding = '0';
                    avatarImg.classList.remove('avatar-default');
                    avatarImg.classList.add('avatar-custom');
                }
                const sidebarAvatar = document.querySelector('.admin-sidebar__avatar');
                if (sidebarAvatar) {
                    sidebarAvatar.innerHTML = `<img src="${data.avatar_url}" alt="Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" />`;
                }
            } else {
                showFeedback(false, data.messages?.error || data.message || 'Error al subir la imagen');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        } finally {
            e.target.value = '';
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
                    const res = await fetch('<?= base_url('api/perfil/redes') ?>', { method: 'POST', body: fd });
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

    const validateRedesForm = () => {
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
                Swal.fire({
                    title: 'Fila incompleta',
                    text: 'Hay una fila de red social vacía. Completa los datos o elimínala antes de guardar.',
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                select && select.focus();
                return false;
            }

            // 2. Control de tipo seleccionado pero link vacío
            if (tipo && !link) {
                Swal.fire({
                    title: 'Enlace requerido',
                    text: `El enlace para ${tipoLabel} no puede estar vacío.`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
                return false;
            }

            // 3. Control de link ingresado pero tipo sin seleccionar
            if (!tipo && link) {
                Swal.fire({
                    title: 'Red no seleccionada',
                    text: 'Debes seleccionar a qué red social corresponde el enlace ingresado.',
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                select && select.focus();
                return false;
            }

            // 4. Control de duplicados
            if (seenTipos.has(tipo)) {
                Swal.fire({
                    title: 'Red duplicada',
                    text: `No puedes agregar ${tipoLabel} más de una vez.`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                select && select.focus();
                return false;
            }
            seenTipos.add(tipo);

            // 5. Rechazo estricto de HTTP inseguro
            if (link.toLowerCase().startsWith('http://')) {
                Swal.fire({
                    title: 'Protocolo inseguro',
                    text: `El enlace de ${tipoLabel} debe usar protocolo seguro HTTPS (https://) en lugar de HTTP.`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
                return false;
            }

            // 6. Normalizar y validar estructura de URL
            if (!link.includes('/')) {
                if (tipo === 'whatsapp') {
                    const cleanNum = link.replace(/[^0-9]/g, '');
                    if (!cleanNum) {
                        Swal.fire({
                            title: 'Número requerido',
                            text: `Debes ingresar un número telefónico con código de país para WhatsApp. Ejemplo: ${placeholdersMap[tipo]}`,
                            icon: 'warning',
                            confirmButtonColor: '#d6858e'
                        });
                        input && input.focus();
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
                Swal.fire({
                    title: 'Enlace inválido',
                    text: `El enlace ingresado para ${tipoLabel} no es una URL válida. Ejemplo: ${placeholdersMap[tipo]}`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
                return false;
            }

            if (parsed.protocol !== 'https:') {
                Swal.fire({
                    title: 'Protocolo inseguro',
                    text: `El enlace de ${tipoLabel} debe ser estrictamente HTTPS.`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
                return false;
            }

            // 8. Dominio oficial auténtico
            const allowed = allowedHostsMap[tipo] || [];
            if (!allowed.includes(parsed.hostname.toLowerCase())) {
                Swal.fire({
                    title: 'Dominio incorrecto',
                    text: `El enlace no corresponde a la plataforma oficial de ${tipoLabel}. Debe ser del formato: ${placeholdersMap[tipo]}`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
                return false;
            }

            // 9. Verificar que incluya usuario/perfil real y no solo la portada
            const path = parsed.pathname.replace(/^\/+|\/+$/g, '');
            if (tipo === 'whatsapp') {
                if (!path && !parsed.searchParams.get('phone')) {
                    Swal.fire({
                        title: 'Teléfono requerido',
                        text: `Debes incluir tu número con código de país en el enlace de WhatsApp. Ejemplo: ${placeholdersMap[tipo]}`,
                        icon: 'warning',
                        confirmButtonColor: '#d6858e'
                    });
                    input && input.focus();
                    return false;
                }
            } else if (!path) {
                Swal.fire({
                    title: 'Perfil requerido',
                    text: `Debes incluir tu usuario o enlace a tu perfil en ${tipoLabel}, no solo la página de inicio (${placeholdersMap[tipo]}).`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                input && input.focus();
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
            const res = await fetch('<?= base_url('api/perfil/redes') ?>', { method: 'POST', body: fd });
            const data = await res.json();
            if (res.ok) {
                showFeedback(true, data.message);
            } else {
                showFeedback(false, data.messages?.error || data.message || 'Error al guardar');
            }
        } catch (err) {
            showFeedback(false, 'Error de conexión');
        }
    });
    <?php endif; ?>

});
</script>

<?php $this->endSection(); ?>





