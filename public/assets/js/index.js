/**
 * BOUTIQUE - INTERACTIVIDAD Y EFECTOS VISUALES (JS)
 * ==========================================================================
 */

document.addEventListener("DOMContentLoaded", () => {
    initHeaderScroll();
    initMobileMenu();
    initScrollReveal();
    initFormHandlers();
    initAuthModals();
});

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
 * 5. Controladores de Modales de Autenticación
 */
function initAuthModals() {
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    const closeBtns = document.querySelectorAll('[data-close-modal]');

    if (!loginBtn || !loginModal || !registerModal) return;

    const openModal = (modal) => {
        modal.classList.add('modal--open');
        document.body.style.overflow = "hidden";
    };

    const closeModal = (modal) => {
        modal.classList.remove('modal--open');
        document.body.style.overflow = "";
    };

    // Abrir Login desde el header
    loginBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openModal(loginModal);
    });

    // Cambiar a Registro
    switchToRegister.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal(loginModal);
        // Pequeño timeout para animación fluida
        setTimeout(() => openModal(registerModal), 300);
    });

    // Cambiar a Login
    switchToLogin.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal(registerModal);
        setTimeout(() => openModal(loginModal), 300);
    });

    // Botones y Overlay de cerrar
    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Si el click fue en el overlay y no en su contenido, o si fue en el botón X
            if(e.target === btn || btn.classList.contains('modal__close') || btn.closest('.modal__close')) {
                const modal = btn.closest('.modal');
                if (modal) closeModal(modal);
            }
        });
    });
}
