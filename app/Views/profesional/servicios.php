<?php
$pageTitle = 'Servicios';
$activeNav = 'servicios';
$this->extend('profesional/_layout');
$this->section('content');
?>

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-header__title">Servicios</h2>
        <p class="admin-page-header__subtitle">Tratamientos y servicios que ofrece el centro</p>
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
        <button class="btn btn--primary admin-action-btn" data-bs-toggle="modal" data-bs-target="#createServicioModal">
            <span class="material-symbols-outlined admin-action-icon">add</span>
            Nuevo servicio
        </button>
    </div>
</div>

<!-- Filtros de Servicios -->
<div class="admin-table-card mb-3">
    <form method="GET" action="<?= base_url('admin/servicios') ?>" class="admin-filter-bar admin-table-filter-bar p-3 d-flex flex-wrap gap-2 align-items-center">
        <div class="admin-table-filter-col flex-grow-1" style="min-width: 200px;">
            <input type="text" name="search" class="form-control admin-form-input" placeholder="Buscar por nombre..." value="<?= esc($_GET['search'] ?? '') ?>">
        </div>
        <div class="admin-table-filter-col-sm flex-grow-1" style="min-width: 160px;">
            <select name="estado" class="form-control admin-form-input">
                <option value="">Estado: Todos</option>
                <option value="activo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= (isset($_GET['estado']) && $_GET['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="admin-table-filter-col flex-grow-1" style="min-width: 200px;">
            <select name="sort" class="form-control admin-form-input">
                <option value="">Predeterminado</option>
                <option value="a-z" <?= (isset($_GET['sort']) && $_GET['sort'] === 'a-z') ? 'selected' : '' ?>>Nombre &uarr; &mdash; A &rarr; Z</option>
                <option value="z-a" <?= (isset($_GET['sort']) && $_GET['sort'] === 'z-a') ? 'selected' : '' ?>>Nombre &darr; &mdash; Z &rarr; A</option>
                <option value="menor-tiempo" <?= (isset($_GET['sort']) && $_GET['sort'] === 'menor-tiempo') ? 'selected' : '' ?>>Tiempo &uarr; &mdash; Menor a mayor</option>
                <option value="mayor-tiempo" <?= (isset($_GET['sort']) && $_GET['sort'] === 'mayor-tiempo') ? 'selected' : '' ?>>Tiempo &darr; &mdash; Mayor a menor</option>
            </select>
        </div>
        <button type="submit" class="btn btn--primary admin-modal-submit">
            Filtrar
        </button>
        <a href="<?= base_url('admin/servicios') ?>" class="btn admin-modal-submit admin-action-btn--outline">
            Limpiar Filtros
        </a>
    </form>
</div>

<div class="admin-table-card">
    <div class="admin-table-card__header">
        <h3 class="admin-table-card__title">
            Listado de servicios
            <?php if ($pager_total > 0): ?>
            <span class="badge badge--blue ms-2"><?= $pager_total ?></span>
            <?php endif; ?>
        </h3>
    </div>

    <?php if (!empty($servicios)): ?>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th id="thDragHandle" class="d-none admin-table-text-cell--sm"></th>
                    <th class="admin-table-text-cell--sm">#</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Profesionales</th>
                    <th>Duración</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicios as $s): ?>
                <tr data-id="<?= esc($s['s_id']) ?>">
                    <td class="drag-handle-cell d-none text-center text-muted">
                        <span class="material-symbols-outlined admin-drag-icon">drag_indicator</span>
                    </td>
                    <td class="orden-cell fw-bold text-muted admin-text-sm"><?= esc($s['s_orden']) ?></td>
                    <td>
                        <?php if (!empty($s['imagen_ruta'])): ?>
                            <img src="<?= esc($s['imagen_ruta']) ?>"
                                 class="admin-table-img"
                                 alt="<?= esc($s['s_nbre']) ?>" loading="lazy" />
                        <?php else: ?>
                            <span class="table-avatar-placeholder admin-table-img">
                                <span class="material-symbols-outlined admin-visibility-icon">image</span>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <p class="table-user-name"><?= esc($s['s_nbre']) ?></p>
                    </td>
                    <td class="admin-table-text-cell">
                        <?= esc(mb_strimwidth($s['s_desc'] ?? '', 0, 70, '…')) ?>
                    </td>
                    <td class="admin-table-text-cell--sm">
                        <?= esc($s['profesionales_nombres'] ?: 'Sin asignar') ?>
                    </td>
                    <td><?= esc($s['s_duracionMinutos']) ?> min</td>
                    <td class="fw-bold">Desde $<?= number_format($s['s_precio'], 0, ',', '.') ?></td>
                    <td>
                        <?php if (!empty($s['s_activo'])): ?>
                            <span class="badge badge--green">Activo</span>
                        <?php else: ?>
                            <span class="badge badge--gray">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="table-btn table-btn--edit btn-assign-prof" title="Asignar Profesionales"
                                data-bs-toggle="modal" data-bs-target="#assignProfesionalesModal"
                                data-id="<?= esc($s['s_id']) ?>"
                                data-name="<?= esc($s['s_nbre']) ?>"
                                data-profs="<?= esc($s['profesionales_ids']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">group_add</span>
                            </button>
                            <button class="table-btn table-btn--edit btn-manage-images" title="Gestionar Imágenes"
                                data-id="<?= esc($s['s_id']) ?>"
                                data-name="<?= esc($s['s_nbre']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">photo_library</span>
                            </button>
                            <button class="table-btn table-btn--edit btn-edit-servicio" title="Editar"
                                data-bs-toggle="modal" data-bs-target="#editServicioModal"
                                data-id="<?= esc($s['s_id']) ?>"
                                data-name="<?= esc($s['s_nbre']) ?>"
                                data-desc="<?= esc($s['s_desc']) ?>"
                                data-dur="<?= esc($s['s_duracionMinutos']) ?>"
                                data-price="<?= esc($s['s_precio']) ?>"
                                data-active="<?= esc($s['s_activo'] ?? 0) ?>"
                                data-img="<?= esc($s['imagen_ruta']) ?>">
                                <span class="material-symbols-outlined admin-action-icon">edit</span>
                            </button>
                            <button class="table-btn table-btn--delete btn-delete-servicio" title="Eliminar"
                                data-id="<?= esc($s['s_id']) ?>"
                                data-name="<?= esc($s['s_nbre']) ?>">
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
            <span class="material-symbols-outlined">spa</span>
            <p class="admin-empty-state__text">Aún no hay servicios registrados.</p>
        </div>
    <?php endif; ?>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 px-3 py-2 border-top admin-table-actions-row">
        <span class="fw-bold text-muted" style="font-size: 0.85rem;">
            <?php if ($pager_total > 0): ?>
                Mostrando <?= $pager_start ?>–<?= $pager_end ?> de <?= $pager_total ?> servicios
            <?php else: ?>
                Mostrando 0 de 0 servicios
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

<!-- Modal: Crear Servicio -->
<div class="modal fade" id="createServicioModal" tabindex="-1" aria-labelledby="createServicioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="createServicioModalLabel">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="createServicioForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div id="createServicioError" class="alert alert-danger d-none admin-modal-alert"></div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="createSrvName" name="nombre" placeholder="Nombre del servicio" required minlength="3" pattern="^[^-].*"
                                class="admin-form-input">
                            <div class="invalid-feedback">Mínimo 3 caracteres y no puede empezar con "-".</div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="createSrvActive" name="activo" required class="admin-form-input">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <textarea class="form-control" id="createSrvDesc" name="descripcion" placeholder="Descripción breve" rows="3" required minlength="10"
                                class="admin-form-input"></textarea>
                            <div class="invalid-feedback">Mínimo 10 caracteres y no puede empezar con "-".</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <span class="input-group-text admin-form-input-left bg-light"><span class="material-symbols-outlined admin-visibility-icon">schedule</span></span>
                                <input type="number" class="form-control" id="createSrvDur" name="duracion_minutos" placeholder="Duración (minutos)" required
                                    min="5" max="480" class="admin-form-input-group-text p-2">
                                <div class="invalid-feedback">Entre 5 y 480 minutos.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <span class="input-group-text admin-form-input-left bg-light"><span class="material-symbols-outlined admin-visibility-icon">attach_money</span></span>
                                <input type="number" step="0.01" class="form-control" id="createSrvPrice" name="precio" placeholder="Precio ($) Opcional"
                                    min="0" class="admin-form-input-group-text p-2">
                                <div class="invalid-feedback">Mínimo 0.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted admin-text-sm">Imágenes del Servicio</label>
                            <input class="form-control" type="file" id="createSrvImg" name="imagenes[]" accept=".png, .jpg, .jpeg, .webp" multiple required
                                class="admin-form-input">
                            <div class="form-text admin-text-sm text-muted">Podés seleccionar una o varias imágenes (la 1ra será la portada). Máx: 2MB por imagen. Formatos: PNG, JPG, WEBP.</div>
                            <div class="invalid-feedback">Por favor, subí al menos una imagen válida (Máx 2MB por archivo).</div>
                            <div class="mt-2 text-center d-none" id="createSrvImgPreviewContainer">
                                <img id="createSrvImgPreview" src="" alt="Previsualización" class="admin-img-preview">
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink admin-modal-submit">Guardar servicio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Servicio -->
<div class="modal fade" id="editServicioModal" tabindex="-1" aria-labelledby="editServicioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="editServicioModalLabel">Editar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="editServicioForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" id="editSrvId">
                    <div id="editServicioError" class="alert alert-danger d-none admin-modal-alert"></div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="editSrvName" name="nombre" placeholder="Nombre del servicio" required minlength="3" pattern="^[^-].*"
                                class="admin-form-input">
                            <div class="invalid-feedback">Mínimo 3 caracteres y no puede empezar con "-".</div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="editSrvActive" name="activo" required class="admin-form-input">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <textarea class="form-control" id="editSrvDesc" name="descripcion" placeholder="Descripción breve" rows="3" required minlength="10"
                                class="admin-form-input"></textarea>
                            <div class="invalid-feedback">Mínimo 10 caracteres y no puede empezar con "-".</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <span class="input-group-text admin-form-input-left bg-light"><span class="material-symbols-outlined admin-visibility-icon">schedule</span></span>
                                <input type="number" class="form-control" id="editSrvDur" name="duracion_minutos" placeholder="Duración (minutos)" required
                                    min="5" max="480" class="admin-form-input-group-text p-2">
                                <div class="invalid-feedback">Entre 5 y 480 minutos.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <span class="input-group-text admin-form-input-left bg-light"><span class="material-symbols-outlined admin-visibility-icon">attach_money</span></span>
                                <input type="number" step="0.01" class="form-control" id="editSrvPrice" name="precio" placeholder="Precio ($) Opcional"
                                    min="0" class="admin-form-input-group-text p-2">
                                <div class="invalid-feedback">Mínimo 0.</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted admin-text-sm">Galería de Imágenes</label>
                            <div class="p-3 border rounded-3 bg-light d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative" style="width: 52px; height: 52px;">
                                        <img id="editSrvCurrentThumb" src="" alt="Portada" class="w-100 h-100 rounded object-fit-cover d-none border shadow-sm">
                                        <div id="editSrvNoThumb" class="w-100 h-100 rounded bg-white border d-flex align-items-center justify-content-center text-muted">
                                            <span class="material-symbols-outlined fs-3">image</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark admin-text-sm">Fotos del Servicio</div>
                                        <small class="text-muted d-block">La 1ra foto se usa como portada. Podés sumar más fotos y arrastrarlas para ordenarlas.</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-pink admin-btn-sm" id="btnEditOpenManageImages">
                                    <span class="material-symbols-outlined fs-6 align-middle me-1">photo_library</span>
                                    Gestionar Imágenes
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink admin-modal-submit">Actualizar servicio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Profesionales -->
<div class="modal fade" id="assignProfesionalesModal" tabindex="-1" aria-labelledby="assignProfesionalesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="assignProfesionalesModalLabel">Asignar Profesionales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body">
                <form id="assignProfesionalesForm">
                    <input type="hidden" id="assignSrvId">
                    <div id="assignProfesionalesError" class="alert alert-danger d-none admin-modal-alert"></div>
                    <p class="admin-modal-subtitle">Seleccioná lxs profesionales que brindarán: <span id="assignSrvNameText" class="admin-modal-title"></span></p>
                    
                    <div class="list-group mb-3 admin-modal-alert">
                        <?php if(!empty($todos_profesionales)): foreach($todos_profesionales as $prof): ?>
                            <label class="list-group-item d-flex gap-3 align-items-center border-0 border-bottom p-3">
                                <input class="form-check-input flex-shrink-0 assign-prof-checkbox" type="checkbox" value="<?= esc($prof['p_id']) ?>" class="fs-4">
                                <span class="pt-1 form-checked-content">
                                    <strong><?= esc($prof['u_nbreCompleto']) ?></strong>
                                </span>
                            </label>
                        <?php endforeach; else: ?>
                            <div class="p-3 text-center text-muted admin-text-sm">No hay profesionales disponibles.</div>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-pink admin-modal-submit">Guardar Asignaciones</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gestionar Imágenes del Servicio (Galería y Ordenamiento Drag & Drop) -->
<div class="modal fade" id="manageImagesModal" tabindex="-1" aria-labelledby="manageImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fs-5 fw-bold admin-modal-title" id="manageImagesModalLabel">
                        Imágenes del Servicio
                    </h5>
                    <p class="admin-modal-subtitle m-0 text-muted" id="manageImagesSrvSubtitle">Gestionar fotos y orden de portada</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body admin-modal-body p-4">
                <input type="hidden" id="manageImagesSrvId" value="">

                <!-- Zona de Carga / Dropzone para Sumar Imágenes -->
                <div class="admin-gallery-dropzone mb-3" id="dropzoneArea">
                    <input type="file" id="inputUploadNewImages" accept=".png, .jpg, .jpeg, .webp" multiple class="d-none">
                    <span class="material-symbols-outlined fs-1 text-muted mb-1">add_photo_alternate</span>
                    <div class="fw-bold text-dark admin-text-sm mb-1">Sumar imágenes al servicio</div>
                    <p class="text-muted small mb-2">Hacé clic aquí o arrastrá tus fotos (podés elegir múltiples a la vez). Máx. 2MB por imagen.</p>
                    <button type="button" class="btn btn--primary admin-action-btn" id="btnSelectFiles">
                        <span class="material-symbols-outlined admin-action-icon">upload_file</span>
                        Seleccionar Archivos
                    </button>
                    <div id="uploadSpinner" class="mt-2 text-primary fw-semibold small d-none">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Subiendo y optimizando a WebP...
                    </div>
                </div>

                <!-- Notificación / Guía -->
                <div class="alert alert-info py-2 px-3 d-flex align-items-center gap-2 mb-3" style="background: var(--color-surface-container-low); border-color: var(--color-outline-variant); color: var(--color-on-surface-variant); font-size: 0.82rem;">
                    <span class="material-symbols-outlined fs-5" style="color: var(--color-primary);">info</span>
                    <span>
                        <strong><span class="material-symbols-outlined align-middle fs-6" style="color: #b8860b;">star</span> La 1ra foto (con icono de estrella)</strong> es la que se mostrará como portada en las cards y en la página principal. <strong>Arrastrá las tarjetas</strong> para ordenar las fotos dinámicamente.
                    </span>
                </div>

                <!-- Grid de Imágenes Ordenables por Drag and Drop -->
                <div id="manageImagesGrid" class="admin-gallery-grid">
                    <!-- Se inyectan dinámicamente las tarjetas de imágenes -->
                </div>

                <!-- Estado Vacío -->
                <div id="manageImagesEmptyState" class="text-center py-4 d-none">
                    <span class="material-symbols-outlined fs-1 text-muted mb-2">image_not_supported</span>
                    <p class="text-muted fw-semibold mb-1">Este servicio aún no tiene imágenes asociadas.</p>
                    <small class="text-muted">Subí la primera foto para definir la portada.</small>
                </div>
            </div>
            <div class="modal-footer border-top p-3 d-flex justify-content-between align-items-center">
                <span id="manageImagesCounter" class="text-muted small fw-semibold">0 imágenes</span>
                <button type="button" class="btn btn-pink admin-btn-sm" data-bs-dismiss="modal">
                    Listo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const setupImagePreviewAndValidation = (inputId, previewContainerId, previewImgId) => {
        const input = document.getElementById(inputId);
        const container = document.getElementById(previewContainerId);
        const img = document.getElementById(previewImgId);

        if (input) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Check size (2MB = 2097152 bytes)
                    if (file.size > 2097152) {
                        this.setCustomValidity('El archivo es demasiado grande (Máximo 2MB).');
                        container.classList.add('d-none');
                        img.src = '';
                    } else {
                        this.setCustomValidity('');
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            container.classList.remove('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                } else {
                    container.classList.add('d-none');
                    img.src = '';
                    this.setCustomValidity('');
                }
            });
        }
    };

    setupImagePreviewAndValidation('createSrvImg', 'createSrvImgPreviewContainer', 'createSrvImgPreview');

    const validateTextarea = (textareaId) => {
        const textarea = document.getElementById(textareaId);
        if(textarea) {
            textarea.addEventListener('input', function() {
                if(this.value.trim().startsWith('-')) {
                    this.setCustomValidity('No puede empezar con "-".');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
    validateTextarea('createSrvDesc');
    validateTextarea('editSrvDesc');

    // --- Create ---
    const createForm = document.getElementById('createServicioForm');
    if (createForm) {
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!createForm.checkValidity()) {
                e.stopPropagation();
                createForm.classList.add('was-validated');
                return;
            }

            const btn = createForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Subiendo y convirtiendo a WebP...';

            const formData = new FormData(createForm);

            try {
                const res = await fetch('<?= base_url("api/servicios/crear") ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Creado!',
                        text: 'Servicio subido con éxito.',
                        icon: 'success',
                        confirmButtonColor: '#d6858e'
                    }).then(() => window.location.reload());
                } else {
                    const errorDiv = document.getElementById('createServicioError');
                    errorDiv.innerText = (data.messages && data.messages.error) ? data.messages.error : (data.message || 'Error al crear.');
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (error) {
                const errorDiv = document.getElementById('createServicioError');
                errorDiv.innerText = 'Error de conexión.';
                errorDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    }

    // --- Edit ---
    const editForm = document.getElementById('editServicioForm');
    if (editForm) {
        document.querySelectorAll('.btn-edit-servicio').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editSrvId').value = btn.getAttribute('data-id');
                document.getElementById('editSrvName').value = btn.getAttribute('data-name');
                document.getElementById('editSrvDesc').value = btn.getAttribute('data-desc');
                document.getElementById('editSrvDur').value = btn.getAttribute('data-dur');
                document.getElementById('editSrvPrice').value = btn.getAttribute('data-price');
                document.getElementById('editSrvActive').value = btn.getAttribute('data-active');

                // Actualizar miniatura actual de portada
                const imgUrl = btn.getAttribute('data-img');
                const editThumb = document.getElementById('editSrvCurrentThumb');
                const editNoThumb = document.getElementById('editSrvNoThumb');
                if (imgUrl) {
                    editThumb.src = imgUrl;
                    editThumb.classList.remove('d-none');
                    editNoThumb.classList.add('d-none');
                } else {
                    editThumb.src = '';
                    editThumb.classList.add('d-none');
                    editNoThumb.classList.remove('d-none');
                }
                
                document.getElementById('editServicioError').classList.add('d-none');
                editForm.classList.remove('was-validated');
            });
        });

        // Botón "Gestionar Imágenes" dentro del modal de edición
        const btnEditManage = document.getElementById('btnEditOpenManageImages');
        if (btnEditManage) {
            btnEditManage.addEventListener('click', () => {
                const srvId = document.getElementById('editSrvId').value;
                const srvName = document.getElementById('editSrvName').value;
                const editModalInst = bootstrap.Modal.getInstance(document.getElementById('editServicioModal'));
                if (editModalInst) editModalInst.hide();
                abrirModalGestionImagenes(srvId, srvName);
            });
        }

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
            btn.innerText = 'Actualizando...';

            const id = document.getElementById('editSrvId').value;
            const formData = new FormData(editForm);

            try {
                const res = await fetch(`<?= base_url("api/servicios/editar") ?>/${id}`, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'Servicio actualizado.',
                        icon: 'success',
                        confirmButtonColor: '#d6858e'
                    }).then(() => window.location.reload());
                } else {
                    const errorDiv = document.getElementById('editServicioError');
                    errorDiv.innerText = (data.messages && data.messages.error) ? data.messages.error : (data.message || 'Error al actualizar.');
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (error) {
                const errorDiv = document.getElementById('editServicioError');
                errorDiv.innerText = 'Error de conexión.';
                errorDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    }

    // --- Delete ---
    document.querySelectorAll('.btn-delete-servicio').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');

            Swal.fire({
                title: '¿Eliminar servicio?',
                text: `Se borrará "${name}" y todos sus turnos y asignaciones vinculadas.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`<?= base_url("api/servicios/eliminar") ?>/${id}`, { method: 'DELETE' });
                        const data = await res.json();

                        if (data.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: 'El servicio ha sido eliminado.',
                                icon: 'success',
                                confirmButtonColor: '#d6858e'
                            }).then(() => window.location.reload());
                        } else {
                            const errorMsg = (data.messages && data.messages.error) ? data.messages.error : (data.message || 'No se pudo eliminar.');
                            Swal.fire('Error', errorMsg, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Error de conexión.', 'error');
                    }
                }
            });
        });
    });

    // --- Assign Professionals ---
    const assignForm = document.getElementById('assignProfesionalesForm');
    if (assignForm) {
        document.querySelectorAll('.btn-assign-prof').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const profsRaw = btn.getAttribute('data-profs');
                const profs = profsRaw ? String(profsRaw).split(',').map(s => s.trim()) : [];

                document.getElementById('assignSrvId').value = id;
                document.getElementById('assignSrvNameText').innerText = name;
                document.getElementById('assignProfesionalesError').classList.add('d-none');

                document.querySelectorAll('.assign-prof-checkbox').forEach(cb => {
                    cb.checked = profs.includes(String(cb.value).trim());
                });
            });
        });

        assignForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = assignForm.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Guardando...';

            const id = document.getElementById('assignSrvId').value;
            
            const selected = [];
            document.querySelectorAll('.assign-prof-checkbox:checked').forEach(cb => {
                selected.push(cb.value);
            });

            try {
                const res = await fetch(`<?= base_url("api/servicios/profesionales") ?>/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ profesionales: selected })
                });
                const data = await res.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Asignados!',
                        text: 'Profesionales guardados exitosamente.',
                        icon: 'success',
                        confirmButtonColor: '#d6858e'
                    }).then(() => window.location.reload());
                } else {
                    const errorDiv = document.getElementById('assignProfesionalesError');
                    errorDiv.innerText = (data.messages && data.messages.error) ? data.messages.error : (data.message || 'Error al asignar.');
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            } catch (error) {
                const errorDiv = document.getElementById('assignProfesionalesError');
                errorDiv.innerText = 'Error de conexión.';
                errorDiv.classList.remove('d-none');
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    }

});
</script>

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // 1. ORDENAMIENTO DE SERVICIOS EN TABLA PRINCIPAL
    // =========================================================================
    const btnToggle = document.getElementById('btnToggleOrden');
    const btnGuardar = document.getElementById('btnGuardarOrden');
    const thDrag = document.getElementById('thDragHandle');
    const tbody = document.querySelector('.admin-table tbody');
    let sortable = null;
    let ordenModoActivo = false;

    if (btnToggle && tbody) {
        btnToggle.addEventListener('click', () => {
            ordenModoActivo = !ordenModoActivo;

            // Toggle drag handle column visibility
            thDrag.classList.toggle('d-none', !ordenModoActivo);
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
                const res = await fetch('<?= base_url("api/servicios/ordenar") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orden: ids })
                });
                const data = await res.json();

                if (data.success) {
                    ordenModoActivo = false;
                    thDrag.classList.add('d-none');
                    document.querySelectorAll('.drag-handle-cell').forEach(td => td.classList.add('d-none'));
                    btnGuardar.classList.add('d-none');
                    btnToggle.style.background = 'transparent';
                    btnToggle.style.color = 'var(--color-primary)';
                    if (sortable) { sortable.destroy(); sortable = null; }

                    Swal.fire({
                        title: '¡Orden guardado!',
                        text: 'Los servicios ahora se muestran en el orden que definiste.',
                        icon: 'success',
                        confirmButtonColor: '#d6858e'
                    });
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'Error al guardar el orden.', icon: 'error', confirmButtonColor: '#d6858e' });
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = '<span class="material-symbols-outlined admin-action-icon">save</span> Guardar Orden';
                }
            } catch (e) {
                Swal.fire({ title: 'Error', text: 'Error de conexión.', icon: 'error', confirmButtonColor: '#d6858e' });
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<span class="material-symbols-outlined admin-action-icon">save</span> Guardar Orden';
            }
        });
    }

    // =========================================================================
    // 2. GESTIÓN DE GALERÍA Y DRAG & DROP DE IMÁGENES POR SERVICIO
    // =========================================================================
    let currentSrvId = null;
    let sortableGallery = null;
    const manageImagesModalEl = document.getElementById('manageImagesModal');
    const manageImagesModal = manageImagesModalEl ? new bootstrap.Modal(manageImagesModalEl) : null;
    const manageImagesGrid = document.getElementById('manageImagesGrid');
    const manageImagesEmpty = document.getElementById('manageImagesEmptyState');
    const manageImagesCounter = document.getElementById('manageImagesCounter');
    const dropzoneArea = document.getElementById('dropzoneArea');
    const inputUploadNewImages = document.getElementById('inputUploadNewImages');
    const btnSelectFiles = document.getElementById('btnSelectFiles');
    const uploadSpinner = document.getElementById('uploadSpinner');

    const toastAlert = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    function sincronizarPortadaEnTabla(srvId, nuevaRutaPortada) {
        const row = document.querySelector(`tr[data-id="${srvId}"]`);
        if (row) {
            const btnEdit = row.querySelector('.btn-edit-servicio');
            if (btnEdit) {
                btnEdit.setAttribute('data-img', nuevaRutaPortada || '');
            }
            const avatarEl = row.querySelector('.admin-table-img');
            if (avatarEl) {
                if (nuevaRutaPortada) {
                    avatarEl.outerHTML = `<img src="${nuevaRutaPortada}" alt="Servicio" class="table-avatar admin-table-img">`;
                } else {
                    avatarEl.outerHTML = `<span class="table-avatar-placeholder admin-table-img"><span class="material-symbols-outlined admin-visibility-icon">image</span></span>`;
                }
            }
        }

        const editSrvId = document.getElementById('editSrvId');
        if (editSrvId && editSrvId.value === String(srvId)) {
            const editThumb = document.getElementById('editSrvCurrentThumb');
            const editNoThumb = document.getElementById('editSrvNoThumb');
            if (nuevaRutaPortada) {
                if (editThumb) { editThumb.src = nuevaRutaPortada; editThumb.classList.remove('d-none'); }
                if (editNoThumb) editNoThumb.classList.add('d-none');
            } else {
                if (editThumb) { editThumb.src = ''; editThumb.classList.add('d-none'); }
                if (editNoThumb) editNoThumb.classList.remove('d-none');
            }
        }
    }

    function actualizarBadgesEnDOM() {
        if (!manageImagesGrid) return;
        const cards = manageImagesGrid.querySelectorAll('.admin-gallery-card');
        cards.forEach((card, index) => {
            const badge = card.querySelector('.admin-gallery-badge');
            if (badge) {
                if (index === 0) {
                    badge.className = 'admin-gallery-badge bg-warning text-dark';
                    badge.innerHTML = '<span class="material-symbols-outlined">star</span> Portada';
                } else {
                    badge.className = 'admin-gallery-badge bg-light text-muted border';
                    badge.textContent = `#${index + 1}`;
                }
            }
        });
        if (manageImagesCounter) {
            manageImagesCounter.textContent = `${cards.length} imagen(es)`;
        }
    }

    async function guardarOrdenGaleria() {
        if (!currentSrvId || !manageImagesGrid) return;
        const cards = Array.from(manageImagesGrid.querySelectorAll('.admin-gallery-card'));
        const ids = cards.map(c => c.getAttribute('data-id'));
        if (ids.length === 0) return;

        // Actualizar visualmente badges de inmediato
        actualizarBadgesEnDOM();

        // Obtener portada local
        const firstImg = cards[0].querySelector('img');
        const nuevaPortadaUrl = firstImg ? firstImg.src : null;
        sincronizarPortadaEnTabla(currentSrvId, nuevaPortadaUrl);

        try {
            const res = await fetch(`<?= base_url("api/servicios/imagenes/ordenar") ?>/${currentSrvId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orden: ids })
            });
            const data = await res.json();
            if (data.success) {
                toastAlert.fire({
                    icon: 'success',
                    title: 'Portada y orden actualizados.'
                });
            } else {
                toastAlert.fire({
                    icon: 'error',
                    title: data.message || 'Error al ordenar las fotos.'
                });
            }
        } catch (e) {
            toastAlert.fire({
                icon: 'error',
                title: 'Error de conexión al ordenar fotos.'
            });
        }
    }

    function renderGaleria(imagenes) {
        if (!manageImagesGrid) return;
        manageImagesGrid.innerHTML = '';
        if (!imagenes || imagenes.length === 0) {
            if (manageImagesEmpty) manageImagesEmpty.classList.remove('d-none');
            if (manageImagesCounter) manageImagesCounter.textContent = '0 imágenes';
            sincronizarPortadaEnTabla(currentSrvId, null);
            return;
        }

        if (manageImagesEmpty) manageImagesEmpty.classList.add('d-none');
        if (manageImagesCounter) manageImagesCounter.textContent = `${imagenes.length} imagen(es)`;

        imagenes.forEach((img, idx) => {
            const isCover = idx === 0;
            const card = document.createElement('div');
            card.className = 'admin-gallery-card';
            card.setAttribute('data-id', img.img_id);

            card.innerHTML = `
                <div class="admin-gallery-thumb-wrap">
                    <span class="admin-gallery-badge ${isCover ? 'bg-warning text-dark' : 'bg-light text-muted border'}">
                        ${isCover ? '<span class="material-symbols-outlined">star</span> Portada' : `#${idx + 1}`}
                    </span>
                    <button type="button" class="admin-gallery-delete" data-img-id="${img.img_id}" title="Eliminar imagen">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                    <img src="${img.img_ruta}" alt="Servicio" class="admin-gallery-thumb" loading="lazy">
                </div>
                <div class="admin-gallery-foot">
                    <span class="material-symbols-outlined">drag_indicator</span> Arrastrar
                </div>
            `;

            // Botón eliminar
            const btnDelete = card.querySelector('.admin-gallery-delete');
            if (btnDelete) {
                btnDelete.addEventListener('click', (e) => {
                    e.stopPropagation();
                    eliminarImagenGaleria(img.img_id);
                });
            }

            manageImagesGrid.appendChild(card);
        });

        // Sincronizar portada con la tabla principal
        if (imagenes[0] && imagenes[0].img_ruta) {
            sincronizarPortadaEnTabla(currentSrvId, imagenes[0].img_ruta);
        }

        // Inicializar SortableJS para la galería
        if (sortableGallery) {
            sortableGallery.destroy();
        }
        sortableGallery = new Sortable(manageImagesGrid, {
            animation: 200,
            ghostClass: 'is-dragging',
            chosenClass: 'is-drag-over',
            onEnd: () => {
                guardarOrdenGaleria();
            }
        });
    }

    async function cargarGaleria(srvId) {
        try {
            const res = await fetch(`<?= base_url("api/servicios/imagenes") ?>/${srvId}`);
            const data = await res.json();
            if (data.success) {
                renderGaleria(data.imagenes);
            } else {
                toastAlert.fire({ icon: 'error', title: data.message || 'No se pudieron cargar las fotos.' });
            }
        } catch (e) {
            toastAlert.fire({ icon: 'error', title: 'Error al conectar con el servidor.' });
        }
    }

    window.abrirModalGestionImagenes = function(srvId, srvName) {
        currentSrvId = srvId;
        const inputHidden = document.getElementById('manageImagesSrvId');
        if (inputHidden) inputHidden.value = srvId;
        const subtitle = document.getElementById('manageImagesSrvSubtitle');
        if (subtitle) subtitle.textContent = `Fotos del servicio: "${srvName}"`;
        if (manageImagesGrid) manageImagesGrid.innerHTML = '';
        if (manageImagesEmpty) manageImagesEmpty.classList.add('d-none');
        if (manageImagesModal) {
            manageImagesModal.show();
        }
        cargarGaleria(srvId);
    };

    // Listeners en botones de la tabla principal
    document.querySelectorAll('.btn-manage-images').forEach(btn => {
        btn.addEventListener('click', () => {
            const srvId = btn.getAttribute('data-id');
            const srvName = btn.getAttribute('data-name');
            window.abrirModalGestionImagenes(srvId, srvName);
        });
    });

    // Subida de múltiples fotos
    async function subirArchivosGaleria(files) {
        if (!currentSrvId || !files || files.length === 0) return;

        for (let i = 0; i < files.length; i++) {
            if (files[i].size > 2097152) {
                Swal.fire({
                    title: 'Archivo demasiado grande',
                    text: `El archivo "${files[i].name}" supera el límite de 2MB.`,
                    icon: 'warning',
                    confirmButtonColor: '#d6858e'
                });
                return;
            }
        }

        if (uploadSpinner) uploadSpinner.classList.remove('d-none');
        if (btnSelectFiles) btnSelectFiles.disabled = true;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('imagenes[]', files[i]);
        }

        try {
            const res = await fetch(`<?= base_url("api/servicios/imagenes/subir") ?>/${currentSrvId}`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                toastAlert.fire({ icon: 'success', title: data.message || 'Imágenes subidas exitosamente.' });
                renderGaleria(data.imagenes);
            } else {
                Swal.fire('Error', data.message || 'No se pudieron subir las fotos.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión al subir imágenes.', 'error');
        } finally {
            if (uploadSpinner) uploadSpinner.classList.add('d-none');
            if (btnSelectFiles) btnSelectFiles.disabled = false;
            if (inputUploadNewImages) inputUploadNewImages.value = '';
        }
    }

    // Drag & Drop en el dropzone
    if (dropzoneArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.classList.add('is-drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.classList.remove('is-drag-over');
            });
        });

        dropzoneArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt ? dt.files : null;
            if (files && files.length > 0) {
                subirArchivosGaleria(files);
            }
        });

        dropzoneArea.addEventListener('click', (e) => {
            if (e.target !== btnSelectFiles && (!btnSelectFiles || !btnSelectFiles.contains(e.target))) {
                if (inputUploadNewImages) inputUploadNewImages.click();
            }
        });
    }

    if (btnSelectFiles && inputUploadNewImages) {
        btnSelectFiles.addEventListener('click', (e) => {
            e.stopPropagation();
            inputUploadNewImages.click();
        });
    }

    if (inputUploadNewImages) {
        inputUploadNewImages.addEventListener('change', () => {
            if (inputUploadNewImages.files && inputUploadNewImages.files.length > 0) {
                subirArchivosGaleria(inputUploadNewImages.files);
            }
        });
    }

    // Eliminar foto con confirmación
    async function eliminarImagenGaleria(imgId) {
        const confirm = await Swal.fire({
            title: '¿Eliminar imagen?',
            text: 'Esta acción borrará el archivo permanentemente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`<?= base_url("api/servicios/imagenes/eliminar") ?>/${imgId}`, {
                method: 'DELETE'
            });
            const data = await res.json();
            if (data.success) {
                toastAlert.fire({ icon: 'success', title: 'Imagen eliminada.' });
                cargarGaleria(currentSrvId);
            } else {
                Swal.fire('Error', data.message || 'No se pudo eliminar la imagen.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Error de conexión al eliminar la imagen.', 'error');
        }
    }
});
</script>

<style>
.sortable-ghost { opacity: 0.4; background: var(--color-surface-container-high) !important; }
.sortable-chosen { background: var(--color-surface-container) !important; box-shadow: 0 4px 16px rgba(125,86,45,0.15); }
</style>

<?php $this->endSection(); ?>





