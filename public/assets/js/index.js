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
        // Agrega la clase main-header--scrolled al header cuando el usuario se desplaza más de 50px
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

    // Si no se encuentra el menú o los botones, no se ejecuta el código
    if (!menu || !openBtn || !closeBtn || !overlay) return;

    // Abre el menú y deshabilita el scroll de fondo
    const openMenu = () => {
        menu.classList.add("mobile-menu--open");
        document.body.style.overflow = "hidden"; // Deshabilita scroll de fondo
    };

    // Cierra el menú y restablece el scroll de fondo
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

    // Función que se ejecuta cuando el observador detecta un cambio
    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            // Si la entrada es visible
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                // Una vez revelado, dejamos de observarlo para rendimiento
                observer.unobserve(entry.target);
            }
        });
    };

    // Crea el observador
    const observer = new IntersectionObserver(revealCallback, observerOptions);

    // Observa cada sección
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
        // Agrega un event listener para el evento submit
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();

            // Valida el formulario
            if (!contactForm.checkValidity()) {
                e.stopPropagation();
                contactForm.classList.add('was-validated');
                return;
            }
            contactForm.classList.add('was-validated');

            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const serviceSelect = document.getElementById("service");
            const serviceText = serviceSelect.options[serviceSelect.selectedIndex].text;
            const message = document.getElementById("message").value.trim();

            // Construye el mensaje para WhatsApp
            let wpText = `Hola! Soy ${name}. `;
            if (email) {
                wpText += `Mi correo es ${email}. `;
            }
            wpText += `Me interesa el servicio ${serviceText}, ${message}`;

            // Construye la URL de WhatsApp
            const phone = "5492664905442";
            const url = `https://wa.me/${phone}?text=${encodeURIComponent(wpText)}`;

            // Redirige a WhatsApp
            window.location.href = url;

            // Limpia el formulario
            contactForm.reset();
            contactForm.classList.remove('was-validated');
        });
    }

    // Formulario de Newsletter
    const newsletterForm = document.getElementById("newsletterForm");
    if (newsletterForm) {
        // Agrega un event listener para el evento submit
        newsletterForm.addEventListener("submit", (e) => {
            e.preventDefault();

            // Muestra un mensaje de agradecimiento
            alert("¡Gracias por suscribirte! Te mantendremos informada con nuestras últimas novedades y beneficios exclusivos.");

            // Limpia el formulario
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

    // Wrappers para la API de Bootstrap Modal
    // Abre el modal
    const openModal = (el) => {
        if (!el) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(el);
        bsModal.show();
    };

    // Cierra el modal
    const closeModal = (el) => {
        if (!el) return;
        const bsModal = bootstrap.Modal.getInstance(el);
        if (bsModal) bsModal.hide();
    };

    // Helper global para invocar modales desde cualquier lugar
    window.AppModal = {
        // Abre el modal
        open: (modalId) => {
            const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
            openModal(el);
        },
        // Cierra el modal
        close: (modalId) => {
            const el = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
            closeModal(el);
        },
        // Confirma una acción
        confirm: ({ title = '¿Confirmar acción?', message = '¿Estás seguro de que deseas continuar?', icon = 'warning', acceptText = 'Confirmar', cancelText = 'Cancelar', onAccept }) => {
            Swal.fire({
                title: title,
                text: message,
                icon: icon === 'help' ? 'question' : icon,
                showCancelButton: true,
                confirmButtonColor: '#d6858e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: acceptText,
                cancelButtonText: cancelText
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof onAccept === 'function') onAccept();
                }
            });
        },
        // Muestra un mensaje de éxito
        success: ({ title = '¡Acción Exitosa!', message = 'La operación se completó con éxito.', btnText = 'Aceptar', onOk } = {}) => {
            Swal.fire({
                title: title,
                text: message,
                icon: 'success',
                confirmButtonColor: '#d6858e',
                confirmButtonText: btnText
            }).then(() => {
                if (typeof onOk === 'function') onOk();
            });
        },
        //Muestra un mensaje de error
        error: ({ title = 'Hubo un problema', message = 'Ocurrió un error al procesar tu solicitud.', btnText = 'Entendido', onOk } = {}) => {
            Swal.fire({
                title: title,
                text: message,
                icon: 'error',
                confirmButtonColor: '#d6858e',
                confirmButtonText: btnText
            }).then(() => {
                if (typeof onOk === 'function') onOk();
            });
        }
    };

    // Helper para obtener la URL relativa de la API según la ubicación actual
    const getApiUrl = (endpoint) => {
        // Obtiene la URL relativa de la API según la ubicación actual
        const path = window.location.pathname;
        let base = '';

        // Si la ruta contiene /public/, extrae la base
        if (path.includes('/public/')) {
            base = path.substring(0, path.indexOf('/public/') + 7);
        }
        // Si la ruta contiene /public, extrae la base
        else if (path.includes('/public')) {
            base = path.substring(0, path.indexOf('/public') + 7);
        }
        // Si la ruta contiene /index.php, extrae la base
        else {
            base = path.replace(/\/index\.php\/?$/, '').replace(/\/+$/, '');
        }
        // Retorna la URL relativa de la API
        return `${base}/api/${endpoint}`;
    };



    // Interceptar clicks de cerrar sesión para abrir modal de confirmación
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        // Ignorar el botón de confirmación dentro del modal
        if (link.id === 'confirmLogoutBtn') return;
        // Agrega un event listener para el evento click
        link.addEventListener('click', (e) => {
            e.preventDefault();
            // Cierra el menú móvil si está abierto
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu) mobileMenu.classList.remove('mobile-menu--open');
            document.body.style.overflow = '';
            // Abre el modal de confirmación
            window.AppModal.confirm({
                title: '¿Cerrar Sesión?',
                message: 'Estás a punto de salir de tu cuenta.',
                acceptText: 'Sí, salir',
                onAccept: () => {
                    window.location.href = link.href;
                }
            });
        });
    });

    // Cambiar entre Login y Registro
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    // Agrega un event listener para el evento click
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
    // Agrega un event listener para el evento click
    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(registerModal);

            if (registerModal) {
                registerModal.addEventListener('hidden.bs.modal', function handler() {
                    registerModal.removeEventListener('hidden.bs.modal', handler);
                    openModal(loginModal);
                });
                // Si el modal no existe, abre el modal directamente
            } else {
                openModal(loginModal);
            }
        });
    }

    // Login Form Submit
    const loginForm = document.getElementById('loginForm');
    // Agrega un event listener para el evento submit
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            // Valida el formulario
            if (!loginForm.checkValidity()) {
                e.stopPropagation();
                loginForm.classList.add('was-validated');
                return;
            }
            loginForm.classList.add('was-validated');

            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerText : 'Ingresar';

            const email = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            // Deshabilita el botón de envío y cambia el texto
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Ingresando...';
            }
            // Envía la solicitud de inicio de sesión
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

                // Obtiene el tipo de contenido de la respuesta
                const contentType = response.headers.get('content-type') || '';
                let result;
                // Si el tipo de contenido es JSON, convierte la respuesta a JSON
                if (contentType.includes('application/json')) {
                    result = await response.json();
                }
                // Si el tipo de contenido no es JSON, convierte la respuesta a texto
                else {
                    const text = await response.text();
                    console.error('Respuesta no JSON del servidor:', text);
                    throw new Error(`Error en el servidor (HTTP ${response.status})`);
                }

                // Si el inicio de sesión es exitoso
                if (result.success) {
                    closeModal(loginModal);
                    // Define la URL de redirección
                    const redirectUrl = (result.rol === 'admin' || result.rol === 'profesional')
                        ? window.APP_CONFIG.baseUrl + '/admin'
                        : window.location.href;

                    // Muestra un mensaje de éxito
                    window.AppModal.success({
                        title: '¡Bienvenido/a!',
                        message: 'Inicio de sesión exitoso.',
                        onOk: () => {
                            // Si el rol es admin o profesional, redirige al admin
                            if (result.rol === 'admin' || result.rol === 'profesional') {
                                window.location.href = redirectUrl;
                            } else {
                                window.location.reload();
                            }
                        }
                    });
                    // Redirige al usuario después de 1.5 segundos
                    setTimeout(() => {
                        if (result.rol === 'admin' || result.rol === 'profesional') {
                            window.location.href = redirectUrl;
                        } else {
                            window.location.reload();
                        }
                    }, 1500);
                } else {
                    // Si el inicio de sesión no es exitoso
                    window.AppModal.error({
                        title: 'Error de Autenticación',
                        message: result.message || 'Correo o contraseña incorrectos.'
                    });
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    }
                }
                // Captura cualquier error que ocurra durante el proceso
            } catch (error) {
                console.error('Error login:', error);
                window.AppModal.error({
                    title: 'Error de Conexión',
                    message: error.message || 'Ocurrió un error en la conexión. Por favor intentá nuevamente.'
                });
                // Restablece el botón de envío
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalBtnText;
                }
            }
        });
    }

    // Register Form Submit
    const registerForm = document.getElementById('registerForm');
    // Agrega un event listener para el evento submit
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            // Valida el formulario
            if (!registerForm.checkValidity()) {
                e.stopPropagation();
                registerForm.classList.add('was-validated');
                return;
            }
            registerForm.classList.add('was-validated');

            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.innerText : 'Registrarse';

            const name = document.getElementById('regName').value.trim();
            const email = document.getElementById('regEmail').value.trim();
            const phone = document.getElementById('regPhone').value.trim();
            const password = document.getElementById('regPassword').value;
            const passwordConfirm = document.getElementById('regPasswordConfirm').value;

            const errorDiv = document.getElementById('registerError');
            const successDiv = document.getElementById('registerSuccess');
            // Oculta los mensajes de error y éxito
            if (errorDiv) errorDiv.classList.add('d-none');
            if (successDiv) successDiv.classList.add('d-none');
            // Valida que las contraseñas sean iguales
            if (password !== passwordConfirm) {
                if (errorDiv) {
                    errorDiv.innerText = 'Las contraseñas ingresadas no son iguales.';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            // Valida que la contraseña tenga al menos 8 caracteres
            if (password.length < 8) {
                if (errorDiv) {
                    errorDiv.innerText = 'La contraseña debe contener al menos 8 caracteres.';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            // Deshabilita el botón de envío y cambia el texto
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Registrando...';
            }

            // Maneja la respuesta del servidor
            try {
                const url = getApiUrl('register');
                // Realiza la petición al servidor
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ name, email, phone, password })
                });
                // Obtiene el tipo de contenido de la respuesta
                const contentType = response.headers.get('content-type') || '';
                let result;
                // Si el tipo de contenido es JSON, convierte la respuesta a JSON
                if (contentType.includes('application/json')) {
                    result = await response.json();
                }
                // Si el tipo de contenido no es JSON, convierte la respuesta a texto
                else {
                    const text = await response.text();
                    console.error('Respuesta no JSON del servidor:', text);
                    throw new Error(`Error en el servidor (HTTP ${response.status})`);
                }

                if (result.success) {
                    // Cierra el modal de registro
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                    if (bsModal) bsModal.hide();
                    // Muestra un mensaje de éxito
                    window.AppModal.success({
                        title: '¡Cuenta Creada!',
                        message: 'Tu cuenta ha sido creada exitosamente. Iniciando sesión...',
                        onOk: () => window.location.reload()
                    });


                }
                // Si el registro no es exitoso
                else {
                    // Muestra un mensaje de error
                    if (errorDiv) {
                        errorDiv.innerText = result.message || 'No se pudo completar el registro.';
                        errorDiv.classList.remove('d-none');
                    }
                    // Restablece el botón de envío
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    }
                }
                // Si ocurre un error durante el proceso
            } catch (error) {
                // Captura cualquier error que ocurra durante el proceso
                console.error('Error registro:', error);
                // Muestra un mensaje de error
                if (errorDiv) {
                    errorDiv.innerText = error.message || 'Ocurrió un error en la conexión. Por favor intentá nuevamente.';
                    errorDiv.classList.remove('d-none');
                }
                // Restablece el botón de envío
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalBtnText;
                }
            }
        });
    }
}
