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
    <button class="btn btn--primary" style="gap:0.5rem; font-size:0.75rem;" data-bs-toggle="modal"
        data-bs-target="#createProfesionalModal">
        <span class="material-symbols-outlined" style="font-size:1rem;">add</span>
        Nueva profesional
    </button>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header" style="justify-content: center; width: 100%; margin-bottom: 1rem;">
        <h3 class="admin-table-card__title"
            style="text-transform: uppercase; font-weight: bold; text-align: center; width: 100%;">Listado de
            profesionales</h3>
    </div>

    <form method="GET" action="<?= base_url('admin/profesionales') ?>" class="admin-filter-bar" style="display: flex; gap: 1rem; align-items: center; padding: 0 1.5rem 1.5rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre..." value="<?= esc($_GET['search'] ?? '') ?>" style="border-radius: 0.5rem; padding: 0.6rem 1rem;">
        </div>
        <div style="min-width: 180px;">
            <select name="servicio" class="form-control" style="border-radius: 0.5rem; padding: 0.6rem;">
                <option value="">Servicio: Todos</option>
                <?php foreach ($todos_servicios as $srv): ?>
                    <option value="<?= $srv['s_id'] ?>" <?= (isset($_GET['servicio']) && $_GET['servicio'] == $srv['s_id']) ? 'selected' : '' ?>><?= esc($srv['s_nbre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width: 150px;">
            <select name="estado" class="form-control" style="border-radius: 0.5rem; padding: 0.6rem;">
                <option value="">Estado: Todos</option>
                <option value="activo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn--primary" style="padding: 0.6rem 1.5rem; border-radius: 0.5rem;">
            Filtrar
        </button>
        <?php if (!empty($_GET['search']) || !empty($_GET['servicio']) || !empty($_GET['estado'])): ?>
            <a href="<?= base_url('admin/profesionales') ?>" class="btn" style="padding: 0.6rem 1.5rem; border-radius: 0.5rem; border: 1px solid var(--color-outline); text-decoration: none; color: var(--color-primary);">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($profesionales)): ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
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
                        <tr>
                            <td>
                                <div class="table-user-cell">
                                    <?php if (!empty($prof['u_avatar'])): ?>
                                        <img src="<?= esc($prof['u_avatar']) ?>" class="table-avatar" alt="Avatar" />
                                    <?php else: ?>
                                        <img src="<?= base_url('public/assets/media/íconoPerfil.png') ?>" class="table-avatar" alt="Avatar por defecto" style="object-fit: contain; background: #fff; padding: 4px;" />
                                    <?php endif; ?>
                                    <div>
                                        <p class="table-user-name"><?= esc($prof['u_nbreCompleto']) ?></p>
                                        <p class="table-user-email"><?= esc($prof['u_correo']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($prof['p_titulo'] ?? '—') ?></td>
                            <td
                                style="max-width: 18rem; white-space: normal; font-size: 0.82rem; color: var(--color-on-surface-variant);">
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
                                        <span class="material-symbols-outlined" style="font-size:1rem;">checklist</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-edit-profesional" title="Editar"
                                        data-bs-toggle="modal" data-bs-target="#editProfesionalModal"
                                        data-id="<?= esc($prof['p_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>"
                                        data-email="<?= esc($prof['u_correo']) ?>"
                                        data-phone="<?= esc($prof['u_tel']) ?>"
                                        data-title="<?= esc($prof['p_titulo']) ?>"
                                        data-year="<?= esc($prof['p_anioInicioAct']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">edit</span>
                                    </button>
                                    <button class="table-btn table-btn--edit btn-password-profesional" title="Cambiar Contraseña"
                                        data-bs-toggle="modal" data-bs-target="#passwordProfesionalModal"
                                        data-id="<?= esc($prof['u_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>">
                                        <span class="material-symbols-outlined" style="font-size:1rem;">key</span>
                                    </button>
                                    <button class="table-btn table-btn--delete btn-delete-profesional" title="Eliminar"
                                        data-id="<?= esc($prof['p_id']) ?>"
                                        data-name="<?= esc($prof['u_nbreCompleto']) ?>">
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
                    Mostrando <?= $pager_start ?>-<?= $pager_end ?> de <?= $pager_total ?> Profesionales
                </span>
                <div>
                    <?= $pager_links ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="admin-empty-state">
            <span class="material-symbols-outlined">group_off</span>
            <p class="admin-empty-state__text">Aún no hay profesionales registradas.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Crear Profesional -->
<div class="modal fade" id="createProfesionalModal" tabindex="-1" aria-labelledby="createProfesionalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="createProfesionalModalLabel"
                    style="color: var(--color-primary);">Nueva Profesional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="createProfesionalForm" class="needs-validation" novalidate>
                    <div id="createProfesionalError" class="alert alert-danger d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div id="createProfesionalSuccess" class="alert alert-success d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="profEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profPhone" placeholder="Teléfono" required
                                minlength="8" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="profTitle"
                                placeholder="Título profesional (Ej: Manicurista)" required
                                pattern="^(?:[^a-zA-ZáéíóúÁÉÍÓÚñÑ]*[a-zA-ZáéíóúÁÉÍÓÚñÑ]){3}.*$"
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 3 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="profPassword"
                                    placeholder="Contraseña de acceso temporal" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined"
                                        style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback" id="pwdError">Campo obligatorio: al menos 2 mayúsculas,
                                    2 minúsculas, 2 números, 2 símbolos).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="profPasswordConfirm"
                                    placeholder="Confirmar contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined"
                                        style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <select class="form-select" id="profYear" required
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
                        <button type="submit" class="btn btn-pink"
                            style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Guardar
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
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="assignServicesModalLabel" style="color: var(--color-primary);">
                    Asignar Servicios <br><small id="assignServicesProfName"
                        style="font-size:0.8rem; color:#666; font-weight:normal;"></small>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="assignServicesForm">
                    <input type="hidden" id="assignProfId" name="profesional_id">
                    <div id="assignServicesError" class="alert alert-danger d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div id="assignServicesSuccess" class="alert alert-success d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>

                    <p style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 1rem;">Selecciona los
                        servicios:</p>

                    <div class="services-list"
                        style="display:flex; flex-direction:column; gap:0.5rem; max-height: 250px; overflow-y: auto; padding-right: 0.5rem;">
                        <?php if (!empty($todos_servicios)): ?>
                            <?php foreach ($todos_servicios as $srv): ?>
                                <div class="form-check custom-checkbox">
                                    <input class="form-check-input service-checkbox" type="checkbox"
                                        value="<?= esc($srv['s_id']) ?>" id="srv_<?= esc($srv['s_id']) ?>"
                                        name="servicios[]">
                                    <label class="form-check-label" for="srv_<?= esc($srv['s_id']) ?>"
                                        style="font-size: 0.9rem; cursor:pointer;">
                                        <?= esc($srv['s_nbre']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted" style="font-size: 0.8rem;">No hay servicios registrados en el sistema.</p>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink"
                            style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Guardar
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
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="editProfesionalModalLabel"
                    style="color: var(--color-primary);">Editar Profesional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="editProfesionalForm" class="needs-validation" novalidate>
                    <input type="hidden" id="editProfId">
                    <div id="editProfesionalError" class="alert alert-danger d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfName" placeholder="Nombre completo" required
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Debe contener al menos 7 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="editProfEmail" placeholder="Correo electrónico"
                                required pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfPhone" placeholder="Teléfono" required
                                minlength="8" style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.</div>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="editProfTitle"
                                placeholder="Título profesional (Ej: Manicurista)" required
                                pattern="^(?:[^a-zA-ZáéíóúÁÉÍÓÚñÑ]*[a-zA-ZáéíóúÁÉÍÓÚñÑ]){3}.*$"
                                style="border-radius: 0.5rem; padding: 0.8rem;">
                            <div class="invalid-feedback">Campo obligatorio: al menos 3 letras.</div>
                        </div>
                        <div class="col-md-12">
                            <select class="form-select" id="editProfYear" required
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
                        <button type="submit" class="btn btn-pink"
                            style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Guardar
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
        <div class="modal-content" style="border-radius: var(--radius-xl); border: none; box-shadow: var(--shadow-lg);">
            <div class="modal-header" style="border-bottom: 1px solid var(--color-outline-variant); padding: 1.5rem;">
                <h5 class="modal-title fs-5 fw-bold" id="passwordProfesionalModalLabel"
                    style="color: var(--color-primary);">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <form id="passwordProfesionalForm" class="needs-validation" novalidate>
                    <input type="hidden" id="pwdProfId">
                    <div id="passwordProfesionalError" class="alert alert-danger d-none"
                        style="border-radius: 0.5rem; font-size: 0.9rem; padding: 0.75rem;"></div>
                    <p style="font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 1rem;">Establecer nueva contraseña para <span id="pwdProfNameText"></span>:</p>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newProfPassword"
                                    placeholder="Nueva contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined"
                                        style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback" id="newPwdError">Campo obligatorio: al menos 2 mayúsculas,
                                    2 minúsculas, 2 números, 2 símbolos.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group has-validation">
                                <input type="password" class="form-control" id="newProfPasswordConfirm"
                                    placeholder="Confirmar nueva contraseña" required minlength="8"
                                    style="border-radius: 0.5rem 0 0 0.5rem; padding: 0.8rem;">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                    <span class="material-symbols-outlined"
                                        style="font-size: 1.1rem; line-height: 1;">visibility</span>
                                </button>
                                <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink"
                            style="border-radius: 0.5rem; padding: 0.75rem; font-weight: 600;">Actualizar Contraseña</button>
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

<?php $this->endSection(); ?>