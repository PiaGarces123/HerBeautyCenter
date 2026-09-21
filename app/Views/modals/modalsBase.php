<!-- ==========================================================================
     MODALES REUTILIZABLES (modalsBase.php) — Bootstrap 5.3
     ========================================================================== -->

<!-- 1. MODAL DE INICIO DE SESIÓN (LOGIN) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="border-radius: var(--radius-xl, 1rem); background-color: #ffffff !important;">
            <div class="modal-header border-0 pb-0 d-flex flex-column align-items-center position-relative">
                <button type="button" class="btn-close position-absolute" style="top: 1rem; right: 1rem;"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Her Beauty Center Logo"
                    style="height: 60px; margin-top: 0.5rem; margin-bottom: 1rem;">
                <h2 class="modal-title fs-4 fw-bold text-center w-100" id="loginModalLabel" style="color: #50453b;">
                    Iniciar Sesión</h2>
            </div>
            <div class="modal-body pt-2">
                <form id="loginForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="loginEmail">Email</label>
                        <input class="form-control form-control-lg" id="loginEmail" type="email" required
                            placeholder="tu@email.com" autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="loginPassword">Contraseña</label>
                        <div class="input-group">
                            <input class="form-control form-control-lg" id="loginPassword" type="password" required
                                placeholder="••••••••" autocomplete="current-password" style="border-radius: 0.5rem 0 0 0.5rem;">
                            <button class="btn btn-outline-secondary toggle-password" type="button" style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pink w-100 btn-lg mt-2 fw-semibold"
                        id="loginSubmitBtn">Ingresar</button>
                    <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                        ¿No tienes cuenta? <a href="#" id="switchToRegister"
                            class="text-decoration-none fw-bold text-pink">Regístrate</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. MODAL DE REGISTRO (REGISTER) -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            style="border-radius: var(--radius-xl, 1rem); background-color: #ffffff !important;">
            <div class="modal-header border-0 pb-0 d-flex flex-column align-items-center position-relative">
                <button type="button" class="btn-close position-absolute" style="top: 1rem; right: 1rem;"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Her Beauty Center Logo"
                    style="height: 60px; margin-top: 0.5rem; margin-bottom: 1rem;">
                <h2 class="modal-title fs-4 fw-bold text-center w-100" id="registerModalLabel" style="color: #50453b;">
                    Crear Cuenta</h2>
            </div>
            <div class="modal-body pt-2">
                <form id="registerForm" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="regName">Nombre completo</label>
                        <input class="form-control form-control-lg" id="regName" type="text" required
                            placeholder="Tu nombre" autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="regEmail">Email</label>
                        <input class="form-control form-control-lg" id="regEmail" type="email" required
                            placeholder="tu@email.com" autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="regPassword">Contraseña</label>
                        <div class="input-group">
                            <input class="form-control form-control-lg" id="regPassword" type="password" required
                                minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password" style="border-radius: 0.5rem 0 0 0.5rem;">
                            <button class="btn btn-outline-secondary toggle-password" type="button" style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="regPasswordConfirm">Confirmar Contraseña</label>
                        <div class="input-group">
                            <input class="form-control form-control-lg" id="regPasswordConfirm" type="password" required
                                minlength="8" placeholder="Mínimo 8 caracteres" autocomplete="new-password" style="border-radius: 0.5rem 0 0 0.5rem;">
                            <button class="btn btn-outline-secondary toggle-password" type="button" style="border-radius: 0 0.5rem 0.5rem 0; border-color: #dee2e6;">
                                <span class="material-symbols-outlined" style="font-size: 1.1rem; line-height: 1;">visibility</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pink w-100 btn-lg mt-2 fw-semibold"
                        id="registerSubmitBtn">Registrarse</button>
                    <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                        ¿Ya tienes cuenta? <a href="#" id="switchToLogin"
                            class="text-decoration-none fw-bold text-pink">Inicia sesión</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. MODAL DE CONFIRMACIÓN DE CERRAR SESIÓN (LOGOUT) -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: var(--radius-xl, 1rem);">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-icon-badge modal-icon-badge--warning mx-auto mb-3">
                    <span class="material-symbols-outlined">logout</span>
                </div>
                <h2 class="fs-5 fw-bold mb-2" id="logoutConfirmLabel">¿Cerrar sesión?</h2>
                <p class="text-muted mb-4">¿Estás seguro de que deseas salir de tu cuenta?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?= base_url('logout') ?>" class="btn btn-pink px-4" id="confirmLogoutBtn">Salir</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. MODAL DE SOLICITUD DE CONFIRMACIÓN GENÉRICO -->
<div class="modal fade" id="genericConfirmModal" tabindex="-1" aria-labelledby="genericConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: var(--radius-xl, 1rem);">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-icon-badge modal-icon-badge--primary mx-auto mb-3" id="genericConfirmIconWrap">
                    <span class="material-symbols-outlined" id="genericConfirmIcon">help</span>
                </div>
                <h2 class="fs-5 fw-bold mb-2" id="genericConfirmTitle">¿Confirmar acción?</h2>
                <p class="text-muted mb-4" id="genericConfirmMessage">¿Estás seguro de que deseas continuar con esta
                    acción?</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4" id="genericConfirmCancel"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-pink px-4" id="genericConfirmAccept">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 5. MODAL DE ACCIÓN EXITOSA -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: var(--radius-xl, 1rem);">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-icon-badge modal-icon-badge--success mx-auto mb-3">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
                <h2 class="fs-5 fw-bold mb-2" id="successModalTitle" style="color: #2e7d32;">¡Acción Exitosa!</h2>
                <p class="text-muted mb-4" id="successModalMessage">La operación se completó correctamente.</p>
                <div class="d-grid">
                    <button type="button" class="btn btn-success btn-lg" id="successModalBtn"
                        data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 6. MODAL DE ACCIÓN FALLIDA / ERROR -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center" style="border-radius: var(--radius-xl, 1rem);">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="modal-icon-badge modal-icon-badge--error mx-auto mb-3">
                    <span class="material-symbols-outlined">error</span>
                </div>
                <h2 class="fs-5 fw-bold mb-2" id="errorModalTitle" style="color: #d32f2f;">Hubo un problema</h2>
                <p class="text-muted mb-4" id="errorModalMessage">Ocurrió un error al procesar tu solicitud. Por favor
                    intentá nuevamente.</p>
                <div class="d-grid">
                    <button type="button" class="btn btn-danger btn-lg" id="errorModalBtn"
                        data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>
</div>