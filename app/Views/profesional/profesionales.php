<?php
$pageTitle = 'Profesionales';
$activeNav = 'profesionales';
$this->extend('profesional/_layout');
$this->section('content');
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Profesionales</h2>
        <p class="admin-page-header__subtitle">Equipo de especialistas del centro</p>
    </div>
    <div class="admin-table-actions-row">
        <button id="btnToggleOrden" class="btn admin-action-btn admin-action-btn--outline">
            <span class="material-symbols-outlined admin-action-icon">drag_indicator</span>
            Reordenar
        </button>
        <button id="btnGuardarOrden" class="btn btn--primary d-none admin-action-btn">
            <span class="material-symbols-outlined admin-action-icon">save</span>
            Guardar Orden
        </button>
        <button class="btn btn--primary admin-action-btn" data-bs-toggle="modal"
            data-bs-target="#createProfesionalModal">
            <span class="material-symbols-outlined admin-action-icon">add</span>
            Nueva profesional
        </button>
    </div>
</div>

<!-- Filtros de Profesionales -->
<div class="admin-table-card mb-3">
    <form method="GET" action="<?= base_url('admin/profesionales') ?>" class="admin-filter-bar admin-table-filter-bar p-3 d-flex flex-wrap gap-2 align-items-center">
        <div class="admin-table-filter-col flex-grow-1" style="min-width: 200px;">
            <input type="text" name="search" class="form-control admin-form-input" placeholder="Buscar por nombre..." value="<?= esc($_GET['search'] ?? '') ?>">
        </div>
        <div class="admin-table-filter-col-sm flex-grow-1" style="min-width: 170px;">
            <select name="servicio" class="form-control admin-form-input">
                <option value="">Servicio: Todos</option>
                <?php foreach ($todos_servicios as $srv): ?>
                    <option value="<?= $srv['s_id'] ?>" <?= (isset($_GET['servicio']) && $_GET['servicio'] == $srv['s_id']) ? 'selected' : '' ?>><?= esc($srv['s_nbre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="admin-table-filter-col-sm flex-grow-1" style="min-width: 160px;">
            <select name="estado" class="form-control admin-form-input">
                <option value="">Estado: Todos</option>
                <option value="activo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn--primary admin-modal-submit">
            Filtrar
        </button>
        <a href="<?= base_url('admin/profesionales') ?>" class="btn admin-modal-submit admin-action-btn--outline">
            Limpiar Filtros
        </a>
    </form>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">
            Listado de profesionales
            <?php if ($pager_total > 0): ?>
            <span class="badge badge--blue ms-2"><?= $pager_total ?></span>
            <?php endif; ?>
        </h3>
    </div>

    <?php if (!empty($profesionales)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th id="thDragHandle" class="d-none admin-table-text-cell--sm"></th>
                        <th class="admin-table-text-cell--sm">#</th>
                        <th>Profesional</th>
                        <th>Título</th>
                        <th>Servicios</th>
                        <th>Año de inicio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profesionales as $prof): ?>
                        <tr data-id="<?= esc($prof['p_id']) ?>">
                            <td class="drag-handle-cell d-none text-center text-muted">
                                <span class="material-symbols-outlined admin-drag-icon">drag_indicator</span>
                            </td>
                            <td class="orden-cell fw-bold text-muted admin-text-sm"><?= esc($prof['p_orden'] ?? '—') ?></td>
                            <td>
                                <div class="table-user-cell">
                                    <?php if (!empty($prof['u_avatar'])): ?>
                                        <img src="<?= esc($prof['u_avatar']) ?>" class="table-avatar" alt="Avatar" />
                                    <?php else: ?>
                                        <img src="<?= base_url('public/assets/media/íconoPerfil.png') ?>" class="table-avatar" alt="Avatar por defecto" class="avatar-default" />
                                    <?php endif; ?>
                                    <div>
                                        <p class="table-user-name"><?= esc($prof['u_nbreCompleto']) ?></p>
                                        <p class="table-user-email"><?= esc($prof['u_correo']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($prof['p_titulo'] ?? '—') ?></td>
                            <td
                                class="admin-table-text-cell">
                                <?= esc($prof['servicios_ofrecidos'] ?: 'Sin servicios registrados') ?>
                            </td>
                            <td><?= esc($prof['p_anioInicioAct'] ?? '—') ?></td>
                            <td>
                                <?php if ($prof['u_activo']): ?>
                                    <span class="badge badge--green">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge--gray">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="table-btn table-btn--edit btn-assign-services" title="Asignar Servicios"
                                        data-bs-toggle="modal" data-bs-target="#assignServicesModal"
                                        data-id="<?= esc($prof['p_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>"
                                        data-services="<?= esc($prof['servicios_ids'] ?? '') ?>">
                                        <span class="material-symbols-outlined admin-action-icon">checklist</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-edit-profesional" title="Editar"
                                        data-bs-toggle="modal" data-bs-target="#editProfesionalModal"
                                        data-id="<?= esc($prof['p_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>"
                                        data-email="<?= esc($prof['u_correo']) ?>"
                                        data-phone="<?= esc($prof['u_tel']) ?>"
                                        data-title="<?= esc($prof['p_titulo']) ?>"
                                        data-year="<?= esc($prof['p_anioInicioAct']) ?>">
                                        <span class="material-symbols-outlined admin-action-icon">edit</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-password-profesional" title="Cambiar Contraseña"
                                        data-bs-toggle="modal" data-bs-target="#passwordProfesionalModal"
                                        data-id="<?= esc($prof['u_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>">
                                        <span class="material-symbols-outlined admin-action-icon">key</span>
                                    </button>
                                    <button class="table-btn table-btn--delete btn-delete-profesional" title="Eliminar"
                                        data-id="<?= esc($prof['p_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>">
                                        <span class="material-symbols-outlined admin-action-icon">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">group_off</span>
            <p class="admin-empty-state__text">Aún no hay profesionales registradas.</p>
        </div>
    <?php endif; ?>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 px-3 py-2 border-top admin-table-actions-row">
        <span class="fw-bold text-muted" style="font-size: 0.85rem;">
            <?php if ($pager_total > 0): ?>
                Mostrando <?= $pager_start ?>–<?= $pager_end ?> de <?= $pager_total ?> profesionales
            <?php else: ?>
                Mostrando 0 de 0 profesionales
            <?php endif; ?>
        </span>
        <div>
            <?php if (!empty($pager_links)): ?>
                <?= $pager_links ?>
            <?php else: ?>
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination pagination-sm justify-content-center mb-0 admin-pagination">
                        <li class="page-item disabled">
                            <span class="page-link admin-page-link--disabled">
                                <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_left</span>
                            </span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link admin-page-link--active">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link admin-page-link--disabled">
                                <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_right</span>
                            </span>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Crear Profesional -->
<div class="modal fade" id="createProfesionalModal" tabindex="-1" aria-labelledby="createProfesionalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold" id="createProfesionalModalLabel"
                    class="admin-modal-title">Nueva Profesional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="createProfesionalForm" class="needs-validation" novalidate>
                    <div id="createProfesionalError" class="alert alert-danger d-none"
                        class="admin-modal-alert"></div>
                    <div id="createProfesionalSuccess" class="alert alert-success d-none"
                        class="admin-modal-alert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" class="admin-form-input">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="profEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profPhone" placeholder="Teléfono" required
                                minlength="8" class="admin-form-input">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profTitle"
                                placeholder="Título profesional (Ej: Manicurista)" required
                                pattern="^(?:[^a-zA-ZáéíóúÁÉÍÓÚñÑ]*[a-zA-ZáéíóúÁÉÍÓÚñÑ]){3}.*$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Campo obligatorio: al menos 3 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="profPassword"
                                    placeholder="Contraseña de acceso temporal" required minlength="8"
                                    class="admin-form-input-left">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    class="admin-form-input-group-text">
                                    <span class="material-symbols-outlined"
                                        class="admin-visibility-icon">visibility</span>
                                </button>
                                <div class="invalid-feedback" id="pwdError">Campo obligatorio: al menos 2 mayúsculas,
                                    2 minúsculas, 2 números, 2 símbolos).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="profPasswordConfirm"
                                    placeholder="Confirmar contraseña" required minlength="8"
                                    class="admin-form-input-left">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    class="admin-form-input-group-text">
                                    <span class="material-symbols-outlined"
                                        class="admin-visibility-icon">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <select class="form-select" id="profYear" required
                                class="admin-form-input">
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
                        <button type="submit" class="btn btn-pink"
                            class="admin-modal-submit">Guardar
                            profesional</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Servicios -->
<div class="modal fade" id="assignServicesModal" tabindex="-1" aria-labelledby="assignServicesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="assignServicesModalLabel">
                    Asignar Servicios <br><small id="assignServicesProfName"
                        class="admin-text-sm text-muted fw-normal"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="assignServicesForm">
                    <input type="hidden" id="assignProfId" name="profesional_id">
                    <div id="assignServicesError" class="alert alert-danger d-none"
                        class="admin-modal-alert"></div>
                    <div id="assignServicesSuccess" class="alert alert-success d-none"
                        class="admin-modal-alert"></div>

                    <p class="admin-modal-subtitle">Selecciona los
                        servicios:</p>

                    <div class="services-list"
                        class="admin-modal-scroll-list">
                        <?php if (!empty($todos_servicios)): ?>
                            <?php foreach ($todos_servicios as $srv): ?>
                                <div class="form-check custom-checkbox">
                                    <input class="form-check-input service-checkbox" type="checkbox"
                                        value="<?= esc($srv['s_id']) ?>" id="srv_<?= esc($srv['s_id']) ?>"
                                        name="servicios[]">
                                    <label class="form-check-label" for="srv_<?= esc($srv['s_id']) ?>"
                                        class="admin-text-sm cursor-pointer">
                                        <?= esc($srv['s_nbre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted admin-text-sm text-muted">No hay servicios registrados en el sistema.</p>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink"
                            class="admin-modal-submit">Guardar
                            Asignación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Profesional -->
<div class="modal fade" id="editProfesionalModal" tabindex="-1" aria-labelledby="editProfesionalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold" id="editProfesionalModalLabel"
                    class="admin-modal-title">Editar Profesional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="editProfesionalForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editProfId">
                    <div id="editProfesionalError" class="alert alert-danger d-none"
                        class="admin-modal-alert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" class="admin-form-input">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="editProfEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfPhone" placeholder="Teléfono" required
                                minlength="8" class="admin-form-input">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfTitle"
                                placeholder="Título profesional (Ej: Manicurista)" required
                                pattern="^(?:[^a-zA-ZáéíóúÁÉÍÓÚñÑ]*[a-zA-ZáéíóúÁÉÍÓÚñÑ]){3}.*$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Campo obligatorio: al menos 3 letras.</div>
                        </div>
                        <div class="col-md-12">
                            <select class="form-select" id="editProfYear" required
                                class="admin-form-input">
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
                        <button type="submit" class="btn btn-pink"
                            class="admin-modal-submit">Guardar
                            cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Contraseña -->
<div class="modal fade" id="passwordProfesionalModal" tabindex="-1" aria-labelledby="passwordProfesionalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold" id="passwordProfesionalModalLabel"
                    class="admin-modal-title">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="passwordProfesionalForm" class="needs-validation" novalidate>
                    <input type="hidden" id="pwdProfId">
                    <div id="passwordProfesionalError" class="alert alert-danger d-none"
                        class="admin-modal-alert"></div>
                    <p class="admin-modal-subtitle">Establecer nueva contraseña para <span id="pwdProfNameText"></span>:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newProfPassword"
                                    placeholder="Nueva contraseña" required minlength="8"
                                    class="admin-form-input-left">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    class="admin-form-input-group-text">
                                    <span class="material-symbols-outlined"
                                        class="admin-visibility-icon">visibility</span>
                                </button>
                                <div class="invalid-feedback" id="newPwdError">Campo obligatorio: al menos 2 mayúsculas,
                                    2 minúsculas, 2 números, 2 símbolos.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newProfPasswordConfirm"
                                    placeholder="Confirmar nueva contraseña" required minlength="8"
                                    class="admin-form-input-left">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    class="admin-form-input-group-text">
                                    <span class="material-symbols-outlined"
                                        class="admin-visibility-icon">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink"
                            class="admin-modal-submit">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ---- Logic for Create Professional Modal ----
        const form = document.getElementById('createProfesionalForm');
        if (form) {
            const pwdInput = document.getElementById('profPassword');
            const pwdConfirmInput = document.getElementById('profPasswordConfirm');

            const validatePasswords = () => {
                const pwd = pwdInput.value;
                const pwdConfirm = pwdConfirmInput.value;

                // Count occurrences
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

            pwdInput.addEventListener('input', validatePasswords);
            pwdConfirmInput.addEventListener('input', validatePasswords);

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const pwdValid = validatePasswords();

                if (!form.checkValidity() || !pwdValid) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                const pwd = pwdInput.value;

                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Guardando...';

                const payload = {
                    name: document.getElementById('profName').value.trim(),
                    email: document.getElementById('profEmail').value.trim(),
                    phone: document.getElementById('profPhone').value.trim(),
                    password: pwd,
                    title: document.getElementById('profTitle').value.trim(),
                    year: document.getElementById('profYear').value
                };

                try {
                    const res = await fetch('<?= base_url("api/profesionales/crear") ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();

                    const errorDiv = document.getElementById('createProfesionalError');
                    const successDiv = document.getElementById('createProfesionalSuccess');

                    if (data.success) {
                        errorDiv.classList.add('d-none');

                        // Ocultar modal de bootstrap
                        const createModalEl = document.getElementById('createProfesionalModal');
                        const createModal = bootstrap.Modal.getInstance(createModalEl);
                        if (createModal) createModal.hide();

                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Profesional creada exitosamente.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        successDiv.classList.add('d-none');
                        let errorMsg = data.message || 'Error al crear profesional.';
                        if (data.messages && data.messages.error) {
                            errorMsg = data.messages.error;
                        }
                        errorDiv.innerText = errorMsg;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    console.error(error);
                    const errorDiv = document.getElementById('createProfesionalError');
                    errorDiv.innerText = 'Error de conexión. Inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Logic for Edit Professional Modal ----
        const editForm = document.getElementById('editProfesionalForm');
        if (editForm) {
            document.querySelectorAll('.btn-edit-profesional').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editProfId').value = btn.getAttribute('data-id');
                    document.getElementById('editProfName').value = btn.getAttribute('data-name');
                    document.getElementById('editProfEmail').value = btn.getAttribute('data-email');
                    document.getElementById('editProfPhone').value = btn.getAttribute('data-phone');
                    document.getElementById('editProfTitle').value = btn.getAttribute('data-title');
                    document.getElementById('editProfYear').value = btn.getAttribute('data-year');
                    
                    document.getElementById('editProfesionalError').classList.add('d-none');
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

                const id = document.getElementById('editProfId').value;
                const payload = {
                    name: document.getElementById('editProfName').value.trim(),
                    email: document.getElementById('editProfEmail').value.trim(),
                    phone: document.getElementById('editProfPhone').value.trim(),
                    title: document.getElementById('editProfTitle').value.trim(),
                    year: document.getElementById('editProfYear').value
                };

                try {
                    const res = await fetch(`<?= base_url("api/profesionales/editar") ?>/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    
                    const errorDiv = document.getElementById('editProfesionalError');
                    if (data.success) {
                        errorDiv.classList.add('d-none');
                        
                        const modalEl = document.getElementById('editProfesionalModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        Swal.fire({
                            title: '¡Actualizada!',
                            text: 'Profesional actualizada exitosamente.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => window.location.reload());
                    } else {
                        let errorMsg = data.message || 'Error al actualizar profesional.';
                        if (data.messages && data.messages.error) {
                            errorMsg = data.messages.error;
                        }
                        errorDiv.innerText = errorMsg;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    console.error(error);
                    const errorDiv = document.getElementById('editProfesionalError');
                    errorDiv.innerText = 'Error de conexión. Inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Logic for Change Password Modal ----
        const pwdForm = document.getElementById('passwordProfesionalForm');
        if (pwdForm) {
            const newPwdInput = document.getElementById('newProfPassword');
            const newPwdConfirmInput = document.getElementById('newProfPasswordConfirm');

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

            document.querySelectorAll('.btn-password-profesional').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('pwdProfId').value = btn.getAttribute('data-id');
                    document.getElementById('pwdProfNameText').innerText = btn.getAttribute('data-name');
                    newPwdInput.value = '';
                    newPwdConfirmInput.value = '';
                    document.getElementById('passwordProfesionalError').classList.add('d-none');
                    pwdForm.classList.remove('was-validated');
                });
            });

            pwdForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const pwdValid = validateNewPasswords();
                
                if (!pwdForm.checkValidity() || !pwdValid) {
                    e.stopPropagation();
                    pwdForm.classList.add('was-validated');
                    return;
                }

                const btn = pwdForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Actualizando...';

                const id = document.getElementById('pwdProfId').value;
                try {
                    const res = await fetch(`<?= base_url("api/profesionales/password") ?>/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ password: newPwdInput.value })
                    });
                    const data = await res.json();
                    
                    const errorDiv = document.getElementById('passwordProfesionalError');
                    if (data.success) {
                        errorDiv.classList.add('d-none');
                        
                        const modalEl = document.getElementById('passwordProfesionalModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        Swal.fire({
                            title: '¡Contraseña actualizada!',
                            text: 'La nueva contraseña ha sido establecida.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        });
                    } else {
                        let errorMsg = data.message || 'Error al cambiar contraseña.';
                        if (data.messages && data.messages.error) {
                            errorMsg = data.messages.error;
                        }
                        errorDiv.innerText = errorMsg;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    console.error(error);
                    const errorDiv = document.getElementById('passwordProfesionalError');
                    errorDiv.innerText = 'Error de conexión. Inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Logic for Assign Services Modal ----
        const assignForm = document.getElementById('assignServicesForm');
        if (assignForm) {
            // Populate modal data when opened
            const assignButtons = document.querySelectorAll('.btn-assign-services');
            assignButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name');
                    const servicesStr = btn.getAttribute('data-services') || '';

                    // Parse currently assigned service IDs
                    const assignedIds = servicesStr ? String(servicesStr).split(',').map(s => s.trim()).filter(s => s !== '') : [];

                    document.getElementById('assignProfId').value = id;
                    document.getElementById('assignServicesProfName').innerText = `Profesional: ${name}`;

                    // Uncheck all first
                    document.querySelectorAll('.service-checkbox').forEach(chk => chk.checked = false);

                    // Check the ones already assigned
                    assignedIds.forEach(srvId => {
                        const chk = document.getElementById('srv_' + srvId);
                        if (chk) chk.checked = true;
                    });
                });
            });

            // Submit form
            assignForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = assignForm.querySelector('button[type="submit"]');
                const originalText = btn.innerText;
                btn.disabled = true;
                btn.innerText = 'Guardando...';

                const profId = document.getElementById('assignProfId').value;
                const checkedBoxes = Array.from(document.querySelectorAll('.service-checkbox:checked'));
                const serviceIds = checkedBoxes.map(chk => chk.value);

                try {
                    const res = await fetch('<?= base_url("api/profesionales/servicios") ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ profesional_id: profId, servicios: serviceIds })
                    });
                    const data = await res.json();

                    const errorDiv = document.getElementById('assignServicesError');
                    const successDiv = document.getElementById('assignServicesSuccess');

                    if (data.success) {
                        errorDiv.classList.add('d-none');
                        
                        const modalEl = document.getElementById('assignServicesModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        Swal.fire({
                            title: '¡Asignados!',
                            text: 'Servicios asignados correctamente.',
                            icon: 'success',
                            confirmButtonColor: '#d6858e'
                        }).then(() => window.location.reload());
                    } else {
                        successDiv.classList.add('d-none');
                        let errorMsg = data.message || 'Error al asignar servicios.';
                        if (data.messages && data.messages.error) {
                            errorMsg = data.messages.error;
                        }
                        errorDiv.innerText = errorMsg;
                        errorDiv.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }
                } catch (error) {
                    console.error(error);
                    const errorDiv = document.getElementById('assignServicesError');
                    errorDiv.innerText = 'Error de conexión. Inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
        }

        // ---- Logic for Delete Professional ----
        document.querySelectorAll('.btn-delete-profesional').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');

                Swal.fire({
                    title: '¿Eliminar profesional?',
                    text: `Estás a punto de eliminar a ${name}. Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`<?= base_url("api/profesionales/eliminar") ?>/${id}`, {
                                method: 'DELETE'
                            });
                            const data = await res.json();

                            if (data.success) {
                                Swal.fire({
                                    title: '¡Eliminada!',
                                    text: 'La profesional ha sido eliminada correctamente.',
                                    icon: 'success',
                                    confirmButtonColor: '#d6858e'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'No se pudo eliminar la profesional.', 'error');
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire('Error', 'Problema de conexión al eliminar.', 'error');
                        }
                    }
                });
            });
        });

        // ---- Password Visibility Toggle ----
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                const icon = btn.querySelector('.material-symbols-outlined');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerText = 'visibility_off';
                } else {
                    input.type = 'password';
                    icon.innerText = 'visibility';
                }
            });
        });
    });
</script>

<!-- SortableJS para Reordenar Profesionales Arrastrando -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnToggle = document.getElementById('btnToggleOrden');
    const btnGuardar = document.getElementById('btnGuardarOrden');
    const thDrag = document.getElementById('thDragHandle');
    const tbody = document.querySelector('.admin-table tbody');
    let sortable = null;
    let ordenModoActivo = false;

    if (!btnToggle || !tbody) return;

    btnToggle.addEventListener('click', () => {
        ordenModoActivo = !ordenModoActivo;

        // Mostrar / ocultar columna de arrastre
        if (thDrag) thDrag.classList.toggle('d-none', !ordenModoActivo);
        document.querySelectorAll('.drag-handle-cell').forEach(td => {
            td.classList.toggle('d-none', !ordenModoActivo);
        });

        btnGuardar.classList.toggle('d-none', !ordenModoActivo);
        btnToggle.style.background = ordenModoActivo ? 'var(--color-primary)' : 'transparent';
        btnToggle.style.color = ordenModoActivo ? '#fff' : 'var(--color-primary)';

        if (ordenModoActivo) {
            tbody.style.cursor = 'grab';
            tbody.querySelectorAll('tr').forEach(tr => {
                tr.style.transition = 'background 0.2s';
            });

            sortable = new Sortable(tbody, {
                animation: 180,
                handle: '.drag-handle-cell',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: () => {
                    let i = 1;
                    tbody.querySelectorAll('tr').forEach(tr => {
                        const ordenCell = tr.querySelector('.orden-cell');
                        if (ordenCell) ordenCell.textContent = i++;
                    });
                }
            });
        } else {
            if (sortable) { sortable.destroy(); sortable = null; }
            tbody.style.cursor = '';
        }
    });

    btnGuardar.addEventListener('click', async () => {
        const ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(tr => tr.getAttribute('data-id'));
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="material-symbols-outlined admin-action-icon">hourglass_top</span> Guardando...';

        try {
            const res = await fetch('<?= base_url("api/profesionales/ordenar") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orden: ids })
            });
            const data = await res.json();

            if (data.success) {
                ordenModoActivo = false;
                if (thDrag) thDrag.classList.add('d-none');
                document.querySelectorAll('.drag-handle-cell').forEach(td => td.classList.add('d-none'));
                btnGuardar.classList.add('d-none');
                btnToggle.style.background = 'transparent';
                btnToggle.style.color = 'var(--color-primary)';
                if (sortable) { sortable.destroy(); sortable = null; }

                Swal.fire({
                    title: '¡Orden guardado!',
                    text: 'Las profesionales ahora se muestran en el orden que definiste.',
                    icon: 'success',
                    confirmButtonColor: '#d6858e'
                });
            } else {
                Swal.fire({ title: 'Error', text: data.message || 'Error al guardar el orden.', icon: 'error', confirmButtonColor: '#d6858e' });
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<span class="material-symbols-outlined admin-action-icon">save</span> Guardar Orden';
            }
        } catch (err) {
            console.error(err);
            Swal.fire({ title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', icon: 'error', confirmButtonColor: '#d6858e' });
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<span class="material-symbols-outlined admin-action-icon">save</span> Guardar Orden';
        }
    });
});
</script>

<?php $this->endSection(); ?>




