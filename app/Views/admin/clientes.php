<?php
$pageTitle = 'Clientes';
$activeNav = 'clientes';
$this->extend('admin/_layout');
$this->section('content');
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Clientes</h2>
        <p class="admin-page-header__subtitle">Gestión de usuarios registrados como clientes</p>
    </div>
    <button class="btn btn--primary" style="gap:0.5rem; font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#createClienteModal">
        <span class="material-symbols-outlined" style="font-size:1rem;">person_add</span>
        Nuevo cliente
    </button>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header" style="justify-content: center; width: 100%;">
        <h3 class="admin-table-card__title"
            style="text-transform: uppercase; font-weight: bold; text-align: center; width: 100%;">Listado de clientes</h3>
    </div>

    <?php if (!empty($clientes)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cli): ?>
                        <tr>
                            <td>
                                <div class="table-user-cell">
                                    <?php if (!empty($cli['avatar'])): ?>
                                        <img src="<?= esc($cli['avatar']) ?>" class="table-avatar" alt="Avatar" />
                                    <?php else: ?>
                                        <span class="table-avatar-placeholder">
                                            <span class="material-symbols-outlined" style="font-size:1rem;">person</span>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <p class="table-user-name"><?= esc($cli['nombre_completo']) ?></p>
                                        <p class="table-user-email"><?= esc($cli['correo']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($cli['telefono'] ?? '—') ?></td>
                            <td><?= date('d/m/Y', strtotime($cli['fecha_registro'])) ?></td>
                            <td>
                                <?php if ($cli['activo']): ?>
                                    <span class="badge badge--green">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge--gray">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn table-btn--edit btn-convert-cliente" title="Gestionar Rol (Ascender a Profesional)"
                                        data-bs-toggle="modal" data-bs-target="#convertClienteModal"
                                        data-id="<?= esc($cli['id_cliente']) ?>"
                                        data-name="<?= esc($cli['nombre_completo']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">manage_accounts</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-edit-cliente" title="Editar"
                                        data-bs-toggle="modal" data-bs-target="#editClienteModal"
                                        data-id="<?= esc($cli['id_cliente']) ?>"
                                        data-name="<?= esc($cli['nombre_completo']) ?>"
                                        data-email="<?= esc($cli['correo']) ?>"
                                        data-phone="<?= esc($cli['telefono']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-password-cliente" title="Cambiar Contraseña"
                                        data-bs-toggle="modal" data-bs-target="#passwordClienteModal"
                                        data-id="<?= esc($cli['id_usuario']) ?>"
                                        data-name="<?= esc($cli['nombre_completo']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">key</span>
                                    </button>
                                    <button class="table-btn table-btn--delete btn-delete-cliente" title="Eliminar"
                                        data-id="<?= esc($cli['id_cliente']) ?>"
                                        data-name="<?= esc($cli['nombre_completo']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if (isset($pager_links) && $pager_links): ?>
            <div class="d-flex justify-content-between align-items-center mt-4 px-3" style="flex-wrap: wrap; gap: 1rem;">
                <span style="font-size: 0.85rem; color: var(--color-on-surface-variant); font-weight: 500;">
                    Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> Clientes
                </span>
                <div>
                    <?= $pager_links ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">group_off</span>
            <p class="admin-empty-state__text">Aún no hay clientes registrados o todos son profesionales.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Crear Cliente -->
<div class="modal fade" id="createClienteModal" tabindex="-1" aria-labelledby="createClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="createClienteModalLabel" style="color: var(--color-primary);">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="createClienteForm" class="needs-validation" novalidate>
                    <div id="createClienteError" class="alert alert-danger d-none" style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="createCliName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="createCliEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="createCliPhone" placeholder="Teléfono" required
                                minlength="8" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="createCliPassword"
                                    placeholder="Contraseña de acceso" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback">Obligatorio: 2 mayúsculas, 2 minúsculas, 2 números, 2 símbolos.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="createCliPasswordConfirm"
                                    placeholder="Confirmar contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink" style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Guardar cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Cliente -->
<div class="modal fade" id="editClienteModal" tabindex="-1" aria-labelledby="editClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="editClienteModalLabel" style="color: var(--color-primary);">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="editClienteForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editCliId">
                    <div id="editClienteError" class="alert alert-danger d-none" style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="editCliName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="editCliEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Ingresá un correo válido.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editCliPhone" placeholder="Teléfono" required
                                minlength="8" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.</div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink" style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="passwordClienteModal" tabindex="-1" aria-labelledby="passwordClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="passwordClienteModalLabel" style="color: var(--color-primary);">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="passwordClienteForm" class="needs-validation" novalidate>
                    <input type="hidden" id="pwdCliId">
                    <div id="passwordClienteError" class="alert alert-danger d-none" style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <p style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 1rem;">Establecer nueva contraseña para <span id="pwdCliNameText"></span>:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newCliPassword"
                                    placeholder="Nueva contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback" id="newCliPwdError">Campo obligatorio: al menos 2 mayúsculas, 2 minúsculas, 2 números, 2 símbolos.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newCliPasswordConfirm"
                                    placeholder="Confirmar nueva contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink" style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Convertir a Profesional -->
<div class="modal fade" id="convertClienteModal" tabindex="-1" aria-labelledby="convertClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="convertClienteModalLabel" style="color: var(--color-primary);">Convertir a Profesional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="convertClienteForm" class="needs-validation" novalidate>
                    <input type="hidden" id="convertCliId">
                    <div id="convertClienteError" class="alert alert-danger d-none" style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <p style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 1rem;">Estás por dar rol de profesional a <span id="convertCliNameText" style="color:var(--color-primary);"></span>. Por favor completa los datos faltantes:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="convertCliTitle"
                                placeholder="Título profesional (Ej: Manicurista)" required
                                pattern="^(?:[^a-zA-ZáéíóúÁÉÍÓÚñÑ]*[a-zA-ZáéíóúÁÉÍÓÚñÑ]){3}.*$"
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 3 letras.</div>
                        </div>
                        <div class="col-md-12">
                            <select class="form-select" id="convertCliYear" required
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                                <option value="" disabled selected>Año de inicio de actividades</option>
                                <?php
                                $currentYear = date('Y');
                                for ($i = $currentYear; $i >= $currentYear - 100; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="invalid-feedback">Seleccioná un año de inicio.</div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink" style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Confirmar Conversión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                const icon = btn.querySelector('span');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerText = 'visibility_off';
                } else {
                    input.type = 'password';
                    icon.innerText = 'visibility';
                }
            });
        });

        // ---- Create Client ----
        const createForm = document.getElementById('createClienteForm');
        if (createForm) {
            const pwdInput = document.getElementById('createCliPassword');
            const pwdConfirmInput = document.getElementById('createCliPasswordConfirm');

            const validateCreatePasswords = () => {
                const pwd = pwdInput.value;
                const pwdConfirm = pwdConfirmInput.value;
                const upperCount = (pwd.match(/[A-Z]/g) || []).length;
                const lowerCount = (pwd.match(/[a-z]/g) || []).length;
                const numCount = (pwd.match(/[0-9]/g) || []).length;
                const symCount = (pwd.match(/[^a-zA-Z0-9]/g) || []).length;
                let pwdValid = true;
                if (pwd.length < 8 || upperCount < 2 || lowerCount < 2 || numCount < 2 || symCount < 2) {
                    pwdInput.setCustomValidity('Inválido');
                    pwdValid = false;
                } else {
                    pwdInput.setCustomValidity('');
                }
                if (pwd !== pwdConfirm) {
                    pwdConfirmInput.setCustomValidity('Inválido');
                    pwdValid = false;
                } else {
                    pwdConfirmInput.setCustomValidity('');
                }
                return pwdValid;
            };

            if(pwdInput && pwdConfirmInput) {
                pwdInput.addEventListener('input', validateCreatePasswords);
                pwdConfirmInput.addEventListener('input', validateCreatePasswords);
            }

            createForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!createForm.checkValidity() || !validateCreatePasswords()) {
                    e.stopPropagation();
                    createForm.classList.add('was-validated');
                    return;
                }

                const btn = createForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Guardando...';

                const payload = {
                    name: document.getElementById('createCliName').value.trim(),
                    email: document.getElementById('createCliEmail').value.trim(),
                    phone: document.getElementById('createCliPhone').value.trim(),
                    password: pwdInput.value
                };

                try {
                    const res = await fetch('<?= base_url("api/clientes/crear") ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire({
                            title: '¡Cliente Creado!',
                            text: 'El cliente ha sido registrado exitosamente.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => window.location.reload());
                    } else {
                        const errorDiv = document.getElementById('createClienteError');
                        errorDiv.innerText = data.message || 'Error al crear cliente.';
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    const errorDiv = document.getElementById('createClienteError');
                    errorDiv.innerText = 'Error de conexión.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Edit Client ----
        const editForm = document.getElementById('editClienteForm');
        if (editForm) {
            document.querySelectorAll('.btn-edit-cliente').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editCliId').value = btn.getAttribute('data-id');
                    document.getElementById('editCliName').value = btn.getAttribute('data-name');
                    document.getElementById('editCliEmail').value = btn.getAttribute('data-email');
                    document.getElementById('editCliPhone').value = btn.getAttribute('data-phone');
                    document.getElementById('editClienteError').classList.add('d-none');
                    editForm.classList.remove('was-validated');
                });
            });

            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!editForm.checkValidity()) {
                    e.stopPropagation();
                    editForm.classList.add('was-validated');
                    return;
                }

                const btn = editForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Guardando...';

                const id = document.getElementById('editCliId').value;
                const payload = {
                    name: document.getElementById('editCliName').value.trim(),
                    email: document.getElementById('editCliEmail').value.trim(),
                    phone: document.getElementById('editCliPhone').value.trim()
                };

                try {
                    const res = await fetch(`<?= base_url("api/clientes/editar") ?>/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: 'Cliente actualizado exitosamente.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => window.location.reload());
                    } else {
                        const errorDiv = document.getElementById('editClienteError');
                        errorDiv.innerText = data.message || 'Error al actualizar.';
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    const errorDiv = document.getElementById('editClienteError');
                    errorDiv.innerText = 'Error de conexión.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Delete Client ----
        document.querySelectorAll('.btn-delete-cliente').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');

                Swal.fire({
                    title: '¿Eliminar cliente?',
                    text: `Estás a punto de eliminar a ${name}. Se borrarán sus turnos. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`<?= base_url("api/clientes/eliminar") ?>/${id}`, { method: 'DELETE' });
                            const data = await res.json();

                            if (data.success) {
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: 'El cliente ha sido eliminado.',
                                    icon: 'success',
                                    confirmButtonColor: '#d6858e'
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Error de conexión.', 'error');
                        }
                    }
                });
            });
        });

        // ---- Change Password ----
        const pwdForm = document.getElementById('passwordClienteForm');
        if (pwdForm) {
            const newPwdInput = document.getElementById('newCliPassword');
            const newPwdConfirmInput = document.getElementById('newCliPasswordConfirm');

            const validateNewPasswords = () => {
                const pwd = newPwdInput.value;
                const pwdConfirm = newPwdConfirmInput.value;
                const upperCount = (pwd.match(/[A-Z]/g) || []).length;
                const lowerCount = (pwd.match(/[a-z]/g) || []).length;
                const numCount = (pwd.match(/[0-9]/g) || []).length;
                const symCount = (pwd.match(/[^a-zA-Z0-9]/g) || []).length;
                let pwdValid = true;
                if (pwd.length < 8 || upperCount < 2 || lowerCount < 2 || numCount < 2 || symCount < 2) {
                    newPwdInput.setCustomValidity('Inválido');
                    pwdValid = false;
                } else {
                    newPwdInput.setCustomValidity('');
                }
                if (pwd !== pwdConfirm) {
                    newPwdConfirmInput.setCustomValidity('Inválido');
                    pwdValid = false;
                } else {
                    newPwdConfirmInput.setCustomValidity('');
                }
                return pwdValid;
            };

            newPwdInput.addEventListener('input', validateNewPasswords);
            newPwdConfirmInput.addEventListener('input', validateNewPasswords);

            document.querySelectorAll('.btn-password-cliente').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('pwdCliId').value = btn.getAttribute('data-id');
                    document.getElementById('pwdCliNameText').innerText = btn.getAttribute('data-name');
                    newPwdInput.value = '';
                    newPwdConfirmInput.value = '';
                    document.getElementById('passwordClienteError').classList.add('d-none');
                    pwdForm.classList.remove('was-validated');
                });
            });

            pwdForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!pwdForm.checkValidity() || !validateNewPasswords()) {
                    e.stopPropagation();
                    pwdForm.classList.add('was-validated');
                    return;
                }

                const btn = pwdForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Actualizando...';

                const id = document.getElementById('pwdCliId').value;
                try {
                    const res = await fetch(`<?= base_url("api/clientes/password") ?>/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password: newPwdInput.value })
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire({
                            title: '¡Contraseña actualizada!',
                            text: 'La nueva contraseña ha sido establecida.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('passwordClienteModal'));
                            if (modal) modal.hide();
                        });
                    } else {
                        const errorDiv = document.getElementById('passwordClienteError');
                        errorDiv.innerText = data.message || 'Error al cambiar contraseña.';
                        errorDiv.classList.remove('d-none');
                    }
                } catch (error) {
                    const errorDiv = document.getElementById('passwordClienteError');
                    errorDiv.innerText = 'Error de conexión.';
                    errorDiv.classList.remove('d-none');
                } finally {
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Convert to Professional ----
        const convertForm = document.getElementById('convertClienteForm');
        if (convertForm) {
            document.querySelectorAll('.btn-convert-cliente').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('convertCliId').value = btn.getAttribute('data-id');
                    document.getElementById('convertCliNameText').innerText = btn.getAttribute('data-name');
                    document.getElementById('convertCliTitle').value = '';
                    document.getElementById('convertCliYear').value = '';
                    document.getElementById('convertClienteError').classList.add('d-none');
                    convertForm.classList.remove('was-validated');
                });
            });

            convertForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!convertForm.checkValidity()) {
                    e.stopPropagation();
                    convertForm.classList.add('was-validated');
                    return;
                }

                const btn = convertForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Convirtiendo...';

                const id = document.getElementById('convertCliId').value;
                const payload = {
                    title: document.getElementById('convertCliTitle').value.trim(),
                    year: document.getElementById('convertCliYear').value
                };

                try {
                    const res = await fetch(`<?= base_url("api/clientes/convertir") ?>/${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        Swal.fire({
                            title: '¡Convertido!',
                            text: 'El cliente ahora es profesional.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => window.location.reload());
                    } else {
                        const errorDiv = document.getElementById('convertClienteError');
                        errorDiv.innerText = data.message || 'Error al convertir.';
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    const errorDiv = document.getElementById('convertClienteError');
                    errorDiv.innerText = 'Error de conexión.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }
    });
</script>

<?php $this->endSection(); ?>
