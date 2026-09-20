/**
 * BOUTIQUE - INTERACTIVIDAD Y EFECTOS VISUALES (JS)
 * ==========================================================================
 */

function bootApp() {
    initHeaderScroll();
    initMobileMenu();
    initScrollReveal();
    initFormHandlers();
    initAuthModals();
}

// Con defer, el DOM ya está parseado; si no, esperamos DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootApp);
} else {
    bootApp();
}

/**
 * 1. Efecto del Header al hacer Scroll
 * Reduce el padding y añade sombreado cuando el usuario se desplaza.
 */
function initHeaderScroll() {
    const header = document.getElementById("mainHeader");
    if (!header) return;

    const scrollThreshold = 50;

    const handleScroll = () => {
        if (window.scrollY > scrollThreshold) {
            header.classList.add("main-header--scrolled");
        } else {
            header.classList.remove("main-header--scrolled");
        }
    };

    // Ejecutar al cargar por si el usuario ya está desplazado
    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });
}

/**
 * 2. Menú de Navegación Móvil
 * Abre y cierra el panel lateral con transiciones fluidas.
 */
function initMobileMenu() {
    const menu = document.getElementById("mobileMenu");
    const openBtn = document.getElementById("mobileMenuOpen");
    const closeBtn = document.getElementById("mobileMenuClose");
    const overlay = document.getElementById("mobileMenuOverlay");
    const links = document.querySelectorAll(".mobile-menu__link");

    if (!menu || !openBtn || !closeBtn || !overlay) return;

    const openMenu = () => {
        menu.classList.add("mobile-menu--open");
        document.body.style.overflow = "hidden"; // Deshabilita scroll de fondo
    };

    const closeMenu = () => {
        menu.classList.remove("mobile-menu--open");
        document.body.style.overflow = ""; // Restablece scroll
    };

    // Listeners para abrir y cerrar
    openBtn.addEventListener("click", openMenu);
    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);

    // Cerrar el menú al hacer click en cualquier enlace interno
    links.forEach(link => {
        link.addEventListener("click", closeMenu);
    });
}

/**
 * 3. Revelado de Secciones al Desplazarse (Scroll Reveal)
 * Utiliza Intersection Observer para animar las secciones al entrar al viewport.
 */
function initScrollReveal() {
    const sections = document.querySelectorAll("section");
    if (!sections.length) return;

    // Configuración del observador
    const observerOptions = {
        root: null, // viewport relativo al navegador
        rootMargin: "0px",
        threshold: 0.15 // Se activa cuando el 15% de la sección es visible
    };

    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                // Una vez revelado, dejamos de observarlo para rendimiento
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(revealCallback, observerOptions);

    sections.forEach(section => {
        // La sección Hero ya tiene hero-reveal, para el resto añadimos la clase de revelado base
        if (!section.classList.contains("hero")) {
            section.classList.add("section-reveal");
        }
        observer.observe(section);
    });
}

/**
 * 4. Controladores de Formularios (Contacto y Newsletter)
 * Proporciona retroalimentación interactiva al enviar los formularios.
 */
function initFormHandlers() {
    // Formulario de Contacto
    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const nameInput = document.getElementById("name");
            const name = nameInput ? nameInput.value.trim() : "cliente";

            // Mensaje de éxito estilizado nativo (alert moderno)
            alert(`¡Muchas gracias, ${name}! Hemos recibido tu consulta con éxito. Un representante de nuestro equipo te contactará por email o teléfono a la brevedad.`);
            
            contactForm.reset();
        });
    }

    // Formulario de Newsletter
    const newsletterForm = document.getElementById("newsletterForm");
    if (newsletterForm) {
        newsletterForm.addEventListener("submit", (e) => {
            e.preventDefault();

            alert("¡Gracias por suscribirte! Te mantendremos informada con nuestras últimas novedades y beneficios exclusivos.");
            
            newsletterForm.reset();
        });
    }
}

/**
 * 5. Sistema de Modales Reutilizables (AppModal) — Bootstrap 5.3
 */
function initAuthModals() {
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const logoutConfirmModal = document.getElementById('logoutConfirmModal');
    const genericConfirmModal = document.getElementById('genericConfirmModal');
    const successModal = document.getElementById('successModal');
    const errorModal = document.getElementById('errorModal');

    // Wrappers para la API de Bootstrap Modal
    const openModal = (el) => {
        if (!el) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(el);
        bsModal.show();
    };

    const closeModal = (el) => {
        if (!el) return;
        const bsModal = bootstrap.Modal.getInstance(el);
        if (bsModal) bsModal.hide();
    };

    // Helper global para invocar modales desde cualquier lugar
    window.AppModal = {
        open: (modalId) => {
            const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
            openModal(el);
        },
        close: (modalId) => {
            const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
            closeModal(el);
        },
        confirm: ({ title = '¿Confirmar acción?', message = '¿Estás seguro de que deseas continuar?', icon = 'help', acceptText = 'Confirmar', cancelText = 'Cancelar', onAccept }) => {
            if (!genericConfirmModal) return;
            const titleEl = document.getElementById('genericConfirmTitle');
            const msgEl = document.getElementById('genericConfirmMessage');
            const iconEl = document.getElementById('genericConfirmIcon');
            const acceptBtn = document.getElementById('genericConfirmAccept');
            const cancelBtn = document.getElementById('genericConfirmCancel');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;
            if (iconEl) iconEl.innerText = icon;
            if (acceptBtn) acceptBtn.innerText = acceptText;
            if (cancelBtn) cancelBtn.innerText = cancelText;

            // Limpiar listener anterior clonando el botón
            const newAcceptBtn = acceptBtn.cloneNode(true);
            acceptBtn.parentNode.replaceChild(newAcceptBtn, acceptBtn);

            newAcceptBtn.addEventListener('click', () => {
                closeModal(genericConfirmModal);
                if (typeof onAccept === 'function') onAccept();
            });

            openModal(genericConfirmModal);
        },
        success: ({ title = '¡Acción Exitosa!', message = 'La operación se completó con éxito.', btnText = 'Aceptar', onOk } = {}) => {
            if (!successModal) return;
            const titleEl = document.getElementById('successModalTitle');
            const msgEl = document.getElementById('successModalMessage');
            const btn = document.getElementById('successModalBtn');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;
            if (btn) {
                btn.innerText = btnText;
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', () => {
                    closeModal(successModal);
                    if (typeof onOk === 'function') onOk();
                });
            }

            openModal(successModal);
        },
        error: ({ title = 'Hubo un problema', message = 'Ocurrió un error al procesar tu solicitud.', btnText = 'Entendido', onOk } = {}) => {
            if (!errorModal) return;
            const titleEl = document.getElementById('errorModalTitle');
            const msgEl = document.getElementById('errorModalMessage');
            const btn = document.getElementById('errorModalBtn');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;
            if (btn) {
                btn.innerText = btnText;
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', () => {
                    closeModal(errorModal);
                    if (typeof onOk === 'function') onOk();
                });
            }

            openModal(errorModal);
        }
    };

    // Helper para obtener la URL relativa de la API según la ubicación actual
    const getApiUrl = (endpoint) => {
        const path = window.location.pathname;
        let base = '';

        if (path.includes('/public/')) {
            base = path.substring(0, path.indexOf('/public/') + 7);
        } else if (path.includes('/public')) {
            base = path.substring(0, path.indexOf('/public') + 7);
        } else {
            base = path.replace(/\/index\.php\/?$/, '').replace(/\/+$/, '');
        }

        return `${base}/api/${endpoint}`;
    };



    // Interceptar clicks de cerrar sesión para abrir modal de confirmación
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        // Ignorar el botón de confirmación dentro del modal
        if (link.id === 'confirmLogoutBtn') return;
        link.addEventListener('click', (e) => {
            if (logoutConfirmModal) {
                e.preventDefault();
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenu) mobileMenu.classList.remove('mobile-menu--open');
                document.body.style.overflow = '';
                openModal(logoutConfirmModal);
            }
        });
    });

    // Cambiar entre Login y Registro
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    if (switchToRegister) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(loginModal);
            // Esperar que Bootstrap cierre el modal antes de abrir el otro
            if (loginModal) {
                loginModal.addEventListener('hidden.bs.modal', function handler() {
                    loginModal.removeEventListener('hidden.bs.modal', handler);
                    openModal(registerModal);
                });
            } else {
                openModal(registerModal);
            }
        });
    }
    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(registerModal);
            if (registerModal) {
                registerModal.addEventListener('hidden.bs.modal', function handler() {
                    registerModal.removeEventListener('hidden.bs.modal', handler);
                    openModal(loginModal);
                });
            } else {
                openModal(loginModal);
            }
        });
    }

    // Login Form Submit
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerText : 'Ingresar';

            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Ingresando...';
            }

            try {
                const url = getApiUrl('login');
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ email, password })
                });

                const contentType = response.headers.get('content-type') || '';
                let result;
                if (contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Respuesta no JSON del servidor:', text);
                    throw new Error(`Error en el servidor (HTTP ${response.status})`);
                }

                if (result.success) {
                    closeModal(loginModal);
                    window.AppModal.success({
                        title: '¡Bienvenido/a!',
                        message: 'Inicio de sesión exitoso.',
                        onOk: () => window.location.reload()
                    });
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.AppModal.error({
                        title: 'Error de Autenticación',
                        message: result.message || 'Correo o contraseña incorrectos.'
                    });
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    }
                }
            } catch (error) {
                console.error('Error login:', error);
                window.AppModal.error({
                    title: 'Error de Conexión',
                    message: error.message || 'Ocurrió un error en la conexión. Por favor intentá nuevamente.'
                });
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalBtnText;
                }
            }
        });
    }

    // Register Form Submit
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerText : 'Registrarse';

            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const password = document.getElementById('regPassword').value;

            if (password.length < 8) {
                window.AppModal.error({
                    title: 'Contraseña muy corta',
                    message: 'La contraseña debe contener al menos 8 caracteres.'
                });
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Registrando...';
            }

            try {
                const url = getApiUrl('register');
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name, email, password })
                });

                const contentType = response.headers.get('content-type') || '';
                let result;
                if (contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Respuesta no JSON del servidor:', text);
                    throw new Error(`Error en el servidor (HTTP ${response.status})`);
                }

                if (result.success) {
                    closeModal(registerModal);
                    window.AppModal.success({
                        title: '¡Cuenta Creada!',
                        message: 'Tu cuenta ha sido creada exitosamente. Iniciando sesión...',
                        onOk: () => window.location.reload()
                    });
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.AppModal.error({
                        title: 'Error en el Registro',
                        message: result.message || 'No se pudo completar el registro.'
                    });
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    }
                }
            } catch (error) {
                console.error('Error registro:', error);
                window.AppModal.error({
                    title: 'Error de Conexión',
                    message: error.message || 'Ocurrió un error en la conexión. Por favor intentá nuevamente.'
                });
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalBtnText;
                }
            }
        });
    }
}
