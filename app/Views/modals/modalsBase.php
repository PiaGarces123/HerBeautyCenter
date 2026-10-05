<!-- ==========================================================================
     MODALES REUTILIZABLES (modalsBase.php) — Bootstrap 5.3
     ========================================================================== -->

<!-- 1. MODAL DE INICIO DE SESIÓN (LOGIN) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg"
            class="admin-modal-content">
            <div class="modal-header border-0 pb-0 d-flex flex-column align-items-center position-relative">
                <button type="button" class="btn-close position-absolute admin-modal-close-btn"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Her Beauty Center Logo"
                    class="admin-modal-logo">
                <h2 class="modal-title fs-4 fw-bold text-center w-100" id="loginModalLabel" class="admin-modal-title">
                    Iniciar Sesión</h2>
            </div>
            <div class="modal-body pt-2">
                <form id="loginForm" class="needs-validation" novalidate>
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-medium" for="loginEmail">Email</label>
                        <input class="form-control form-control-lg" id="loginEmail" type="email" required
                            placeholder="ejemplo@gmail.com" autocomplete="email" pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$">
                        <div class="invalid-feedback">Ingresá un correo válido.</div>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-medium" for="loginPassword">Contraseña</label>
                        <div class="input-group has-validation">
                            <input class="form-control form-control-lg" id="loginPassword" type="password" required
                                placeholder="••••••••" autocomplete="current-password" minlength="8"
                                class="admin-form-input-left">
                            <button class="btn btn-outline-secondary toggle-password" type="button"
                                class="admin-form-input-group-text">
                                <span class="material-symbols-outlined"
                                    class="admin-visibility-icon">visibility</span>
                            </button>
                            <div class="invalid-feedback">Campo requerido.</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-pink w-100 btn-lg mt-2 fw-semibold"
                        id="loginSubmitBtn">Ingresar</button>
                    <p class="text-center text-muted mt-3 mb-0 admin-text-sm">
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg"
            class="admin-modal-content">
            <div class="modal-header border-0 pb-0 d-flex flex-column align-items-center position-relative">
                <button type="button" class="btn-close position-absolute admin-modal-close-btn"
                    data-bs-dismiss="modal" aria-label="Cerrar"></button>
                <img src="<?= base_url('public/assets/media/logoText.png') ?>" alt="Her Beauty Center Logo"
                    class="admin-modal-logo">
                <h2 class="modal-title fs-4 fw-bold text-center w-100" id="registerModalLabel" class="admin-modal-title">
                    Crear Cuenta</h2>
            </div>
            <div class="modal-body pt-2 px-4 pb-4">
                <div id="registerError" class="alert alert-danger d-none"
                    class="admin-modal-alert"></div>
                <div id="registerSuccess" class="alert alert-success d-none"
                    class="admin-modal-alert"></div>
                <form id="registerForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input class="form-control" id="regName" type="text" required placeholder="Nombre completo"
                                autocomplete="name" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Debe contener al menos 3 letras.</div>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" id="regEmail" type="email" required
                                placeholder="Correo electrónico" autocomplete="email"
                                pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$"
                                class="admin-form-input">
                            <div class="invalid-feedback">Ingresá un correo válido (ej: correo@gmail.com).</div>
                        </div>
                        <div class="col-md-12">
                            <input class="form-control" id="regPhone" type="text" required placeholder="Teléfono"
                                minlength="8" class="admin-form-input">
                            <div class="invalid-feedback">Campo obligatorio: al menos 8 números.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input class="form-control" id="regPassword" type="password" required minlength="8"
                                    placeholder="Contraseña" autocomplete="new-password"
                                    class="admin-form-input-left">
                                <button class="btn btn-outline-secondary toggle-password" type="button"
                                    class="admin-form-input-group-text">
                                    <span class="material-symbols-outlined"
                                        class="admin-visibility-icon">visibility</span>
                                </button>
                                <div class="invalid-feedback">Mínimo 8 caracteres.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group has-validation">
                                <input class="form-control" id="regPasswordConfirm" type="password" required
                                    minlength="8" placeholder="Confirmar contraseña" autocomplete="new-password"
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
                    <button type="submit" class="btn btn-pink w-100 btn-lg mt-2 fw-semibold"
                        id="registerSubmitBtn">Registrarse</button>
                    <p class="text-center text-muted mt-3 mb-0 admin-text-sm">
                        ¿Ya tienes cuenta? <a href="#" id="switchToLogin"
                            class="text-decoration-none fw-bold text-pink">Inicia sesión</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
