<?php
$u = $usuario ?? [];
$prox = $proximoTurno ?? null;
$defaultAvatar = base_url('public/assets/media/íconoPerfil.png');
$avatar = !empty($u['u_avatar']) ? base_url($u['u_avatar']) : $defaultAvatar;
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Mi Perfil — Panel del Cliente | Her Beauty Center</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />

    <!-- Estilos del Sistema (reutilizando admin.css para las tarjetas de perfil) -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/cliente.css?v=' . time()) ?>" />
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>

    <main class="client-page-main">
        <div class="client-container">

            <!-- Pestañas de Navegación del Panel del Cliente -->
            <nav class="client-panel-tabs" aria-label="Navegación del panel del cliente">
                <a href="<?= base_url('mis-turnos') ?>" class="client-panel-tab">
                    <span class="material-symbols-outlined fs-5">calendar_month</span>
                    <span>Mis Turnos</span>
                </a>
                <a href="<?= base_url('mi-perfil') ?>" class="client-panel-tab client-panel-tab--active">
                    <span class="material-symbols-outlined fs-5">person</span>
                    <span>Mi Perfil</span>
                </a>
                <a href="<?= base_url('/#servicios') ?>" class="client-panel-tab ms-md-auto">
                    <span class="material-symbols-outlined fs-5">add_circle</span>
                    <span>Reservar Nuevo Turno</span>
                </a>
            </nav>

            <!-- Encabezado -->
            <div class="mb-4">
                <h1 class="mb-1" style="font-size: clamp(1.85rem, 3vw, 2.4rem);">Mi Perfil</h1>
                <p class="text-muted mb-0">Consultá y actualizá tu información personal, foto de perfil y contraseña.</p>
            </div>

            <!-- Grilla Reutilizada de Perfil (.admin-profile-grid) -->
            <div class="admin-profile-grid">

                <!-- Card Izquierda: Avatar y Resumen del Cliente -->
                <div class="admin-profile-card">
                    <img id="profileAvatarImg" src="<?= esc($avatar) ?>"
                        class="admin-profile-card__avatar <?= empty($u['u_avatar']) ? 'avatar-default' : 'avatar-custom' ?>"
                        alt="Foto de perfil" />

                    <p class="admin-profile-card__name" id="profileDisplayName"><?= esc($u['u_nbreCompleto'] ?? '—') ?></p>
                    <p class="admin-profile-card__role">Clienta</p>
                    <p class="admin-profile-card__email" id="profileDisplayEmail"><?= esc($u['u_correo'] ?? '') ?></p>

                    <?php if (!empty($u['u_fRegistro'])): ?>
                        <p class="admin-text-sm text-muted mt-2 mb-1">
                            Miembro desde <?= date('d/m/Y', strtotime($u['u_fRegistro'])) ?>
                        </p>
                    <?php endif; ?>

                    <p class="admin-text-sm text-muted mb-2">
                        Total de reservas: <strong><?= (int) ($totalTurnos ?? 0) ?></strong>
                    </p>

                    <input type="file" id="avatarInput" accept="image/jpeg, image/png, image/webp" class="d-none" />
                    <button type="button" id="btnChangeAvatar" class="btn btn--outline admin-w-100 mt-2 admin-btn-sm">
                        <span class="material-symbols-outlined fs-6">photo_camera</span>
                        Cambiar foto
                    </button>
                </div>

                <!-- Card Derecha: Formulario de Edición de Datos Personales -->
                <div class="admin-table-card admin-profile-card">
                    <h2 class="admin-table-card__title mb-4">Editar mis datos personales</h2>

                    <form id="perfilClienteForm" class="needs-validation" novalidate>
                        <div class="admin-grid-2">
                            <div class="form-group m-0">
                                <label class="form-label" for="nombre_completo">Nombre completo *</label>
                                <input class="form-input" type="text" id="nombre_completo" name="nombre_completo"
                                    value="<?= esc($u['u_nbreCompleto'] ?? '') ?>" required minlength="3" />
                            </div>
                            <div class="form-group m-0">
                                <label class="form-label" for="telefono">Teléfono / WhatsApp *</label>
                                <input class="form-input" type="tel" id="telefono" name="telefono"
                                    value="<?= esc($u['u_tel'] ?? '') ?>" required minlength="8" />
                            </div>
                        </div>

                        <div class="form-group admin-form-group mt-3">
                            <label class="form-label" for="correo">Correo electrónico *</label>
                            <input class="form-input" type="email" id="correo" name="correo"
                                value="<?= esc($u['u_correo'] ?? '') ?>" required />
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4 pt-2 border-top" style="border-color: var(--color-border) !important;">
                            <button type="button" class="btn btn--outline admin-btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <span class="material-symbols-outlined fs-6">lock</span>
                                Cambiar contraseña
                            </button>
                            <button type="submit" class="btn btn--primary admin-btn-sm">
                                <span class="material-symbols-outlined fs-6">save</span>
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </main>

    <!-- Modal Reutilizable: Cambiar Contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-modal-content">
                <div class="modal-header admin-modal-header">
                    <h5 class="modal-title fs-5 fw-bold admin-modal-title">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body admin-modal-body">
                    <form id="passwordClienteForm">
                        <div class="form-group mb-3">
                            <label class="form-label" for="password_antigua">Contraseña actual</label>
                            <input type="password" id="password_antigua" name="password_antigua" class="form-control admin-form-input" required />
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="password_nueva">Nueva contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva" class="form-control admin-form-input" required minlength="8" />
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="password_confirmar">Confirmar nueva contraseña</label>
                            <input type="password" id="password_confirmar" name="password_confirmar" class="form-control admin-form-input" required minlength="8" />
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn--primary">Actualizar contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
        const showFeedback = (success, message) => {
            Swal.fire({
                title: success ? '¡Éxito!' : 'Atención',
                text: message,
                icon: success ? 'success' : 'error',
                confirmButtonColor: '#d6858e'
            });
        };

        // Actualizar datos personales (reutilizando POST /api/perfil/actualizar)
        document.getElementById('perfilClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!e.target.checkValidity()) {
                e.target.reportValidity();
                return;
            }

            const fd = new FormData(e.target);

            try {
                const res = await fetch('<?= base_url('api/perfil/actualizar') ?>', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (res.ok) {
                    showFeedback(true, data.message || 'Datos actualizados correctamente.');
                    document.getElementById('profileDisplayName').textContent = document.getElementById('nombre_completo').value;
                    document.getElementById('profileDisplayEmail').textContent = document.getElementById('correo').value;
                } else {
                    showFeedback(false, data.messages?.error || data.message || 'Error al actualizar el perfil.');
                }
            } catch (err) {
                showFeedback(false, 'Error de conexión con el servidor.');
            }
        });

        // Cambiar Avatar (reutilizando POST /api/perfil/avatar)
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
                const res = await fetch('<?= base_url('api/perfil/avatar') ?>', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (res.ok) {
                    showFeedback(true, data.message || 'Foto de perfil actualizada.');
                    const avatarImg = document.getElementById('profileAvatarImg');
                    if (avatarImg && data.avatar_url) {
                        avatarImg.src = data.avatar_url;
                        avatarImg.classList.remove('avatar-default');
                        avatarImg.classList.add('avatar-custom');
                    }
                } else {
                    showFeedback(false, data.messages?.error || data.message || 'No se pudo subir la imagen.');
                }
            } catch (err) {
                showFeedback(false, 'Error de conexión.');
            } finally {
                e.target.value = '';
            }
        });

        // Cambiar Contraseña (reutilizando POST /api/perfil/password)
        document.getElementById('passwordClienteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);

            try {
                const res = await fetch('<?= base_url('api/perfil/password') ?>', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                if (res.ok) {
                    showFeedback(true, data.message || 'Contraseña actualizada correctamente.');
                    e.target.reset();
                    const modalInst = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                    if (modalInst) modalInst.hide();
                } else {
                    showFeedback(false, data.messages?.error || data.message || 'Error al actualizar la contraseña.');
                }
            } catch (err) {
                showFeedback(false, 'Error de conexión.');
            }
        });
    });
    </script>
</body>

</html>

