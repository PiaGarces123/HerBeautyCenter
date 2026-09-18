<?php
// Funciones de compatibilidad por si se accede al archivo fuera de CodeIgniter
if (!function_exists('base_url')) {
    function base_url($path = '') { return $path; }
}
if (!function_exists('esc')) {
    function esc($data) { return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="description"
        content="Descubrí Her Beauty Center, un espacio diseñado para tu bienestar y estética. Servicios de cejas, pestañas, tratamientos faciales y manicura en San Luis." />
    <title>Her Beauty Center | Centro de Estética & Bienestar</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&amp;family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;display=swap"
        rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/assets/css/styles.css" />
</head>

<body class="site-body">

    <!-- Header / Navigation Bar -->
    <header class="main-header" id="mainHeader">
        <div class="header-container">
            <!-- Mobile Hamburger Button -->
            <button aria-label="Menu" class="header-menu-btn" id="mobileMenuOpen">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <!-- Brand Logo -->
            <a class="header-logo" href="#">
                <img src="public/assets/media/logoText.jpeg" alt="Logo Her Beauty Center" class="header-logo__img" />
            </a>

            <!-- Desktop Nav -->
            <nav class="header-nav">
                <a class="header-link" href="#servicios">Servicios</a>
                <a class="header-link" href="#profesionales">Profesionales</a>
                <a class="header-link" href="#nosotros">Nosotros</a>
                <a class="header-link" href="#contacto">Contacto</a>
            </nav>

            <!-- Trailing Profile Icon -->
            <button aria-label="Perfil" class="header-profile-btn" id="loginBtn">
                <span class="material-symbols-outlined">person</span>
            </button>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu__overlay" id="mobileMenuOverlay"></div>
        <nav class="mobile-menu__content">
            <button aria-label="Cerrar menú" class="mobile-menu__close-btn" id="mobileMenuClose">
                <span class="material-symbols-outlined">close</span>
            </button>
            <a class="mobile-menu__logo" href="#">Her Beauty Center</a>
            <ul class="mobile-menu__links">
                <li><a class="mobile-menu__link" href="#servicios">Servicios</a></li>
                <li><a class="mobile-menu__link" href="#profesionales">Profesionales</a></li>
                <li><a class="mobile-menu__link" href="#nosotros">Nosotros</a></li>
                <li><a class="mobile-menu__link" href="#contacto">Contacto</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content Area -->
    <main>
        <!-- Hero Section -->
        <section class="hero hero-reveal" id="hero">
            <div class="hero__content">
                <h1 class="hero__title">
                    Tu belleza, <br />
                    <span class="hero__title--italic">nuestro cuidado</span>
                </h1>
                <p class="hero__text">
                    Descubrí un espacio diseñado para tu bienestar. En Her Beauty Center, combinamos técnicas de
                    vanguardia con
                    un enfoque editorial y personalizado para resaltar tu belleza natural en un ambiente de absoluta
                    relajación.
                </p>
                <div class="hero__actions">
                    <a class="btn btn--primary" href="#servicios">Ver servicios</a>
                    <a class="btn btn--outline" href="#profesionales">Conocé a nuestras profesionales</a>
                </div>
            </div>
            <div class="hero__visual">
                <div class="hero__bg-shape"></div>
                <img alt="Tratamiento de belleza relajante" class="hero__image" data-alt="Tratamieinto."
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuDUFfwp7IBYpnjNLKWuizwg1i3OsG5L9WDosG0v1ApmzlstbeUVl1nN3qzd6_vmK45cVuSe2fEJ9z6er0Z5hXysky6SgNPSGnIf7eJvqKm3n85D58DbEV2uwg7tYkiXc3qW7_xdWz3ydRnXmHzHfEzjFLRDSg4rB2MoGy7XqtvGiPLRXRlrSh48zmV6B6WbHyV6zlRdz3qw2Gcsp5aDDdh6DEuhztjoH3s6ZEokvkZiSW7AGTK_8yaI" />
            </div>
        </section>

        <!-- Spacer Divider -->
        <div class="section-divider"></div>

        <!-- Services Section -->
        <section class="services" id="servicios">
            <div class="section-header">
                <h2 class="section-title">Nuestros servicios</h2>
                <p class="section-description">Experiencias diseñadas meticulosamente para realzar cada detalle de tu
                    estética personal.</p>
            </div>

            <!-- Bento Grid -->
                <?php $isFirstService = true; ?>
                <?php if (!empty($servicios)): ?>
                    <?php foreach($servicios as $servicio): ?>
                        <?php 
                            $cardClass = $isFirstService ? 'service-card--large' : 'service-card--medium'; 
                            $isFirstService = false;
                            $imagen = !empty($servicio['imagen_ruta']) ? base_url($servicio['imagen_ruta']) : base_url('public/assets/media/servicio_default.jpeg');
                        ?>
                        <div class="service-card <?= $cardClass ?>">
                            <img alt="<?= esc($servicio['nombre']) ?>" class="service-card__image" data-alt="<?= esc($servicio['nombre']) ?>"
                                src="<?= esc($imagen) ?>" />
                            <div class="service-card__overlay"></div>
                            <div class="service-card__content">
                                <h3 class="service-card__title"><?= esc($servicio['nombre']) ?></h3>
                                <?php if ($cardClass == 'service-card--large'): ?>
                                    <p class="service-card__text"><?= esc($servicio['descripcion']) ?></p>
                                <?php endif; ?>
                                <a class="service-card__link" href="#contacto">
                                    Ver servicio <span class="material-symbols-outlined">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay servicios disponibles por el momento.</p>
                <?php endif; ?>
            </div>
        </section>


        <!-- Spacer Divider -->
        <div class="section-divider"></div>

        <!-- Professionals Section -->
        <section class="professionals" id="profesionales">

            <div class="section-header">
                <h2 class="section-title">Conocé a nuestras profesionales</h2>
                <p class="section-description">Un equipo de especialistas dedicadas a realzar tu belleza natural con
                    técnica, cuidado y un enfoque personalizado.</p>
            </div>

                <?php if (!empty($profesionales)): ?>
                    <?php foreach($profesionales as $prof): ?>
                        <?php 
                            $avatar = !empty($prof['avatar']) ? base_url($prof['avatar']) : base_url('public/assets/media/default_avatar.jpg');
                        ?>
                        <div class="pro-card">
                            <img alt="<?= esc($prof['nombre_completo']) ?> - <?= esc($prof['titulo']) ?>" class="pro-card__image"
                                src="<?= esc($avatar) ?>" />
                            <h3 class="pro-card__name"><?= esc($prof['nombre_completo']) ?></h3>
                            <p class="pro-card__role"><?= esc($prof['titulo']) ?></p>
                            <p class="pro-card__bio"><?= esc($prof['descripcion']) ?></p>
                            <div class="pro-card__socials">
                                <?php if(!empty($prof['redes'])): ?>
                                    <?php foreach($prof['redes'] as $red): ?>
                                        <a aria-label="<?= esc($red['tipo']) ?>" class="footer__simple-social-icon" href="<?= esc($red['link']) ?>" target="_blank"
                                            rel="noopener noreferrer">
                                            <?php if(strtolower($red['tipo']) == 'instagram'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                                </svg>
                                            <?php elseif(strtolower($red['tipo']) == 'facebook'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                                </svg>
                                            <?php elseif(strtolower($red['tipo']) == 'tiktok'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                                                </svg>
                                            <?php else: ?>
                                                <!-- Link genérico -->
                                                <span class="material-symbols-outlined" style="font-size: 18px;">link</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn--outline pro-card__btn">Reservar turno</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay profesionales disponibles por el momento.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Banner/CTA Section -->
        <section class="cta-banner">
            <div class="cta-banner__content">
                <h2 class="cta-banner__title">Renová tu energía</h2>
                <p class="cta-banner__text">Regalate un momento de desconexión.
                    <br> Agendá tu cita hoy y dejate consentir en
                    <strong> Her Beauty Center</strong>
                </p>
                <a class="btn btn--dark-solid cta-banner__btn" href="#contacto">Solicitar turno</a>
            </div>
        </section>

        <!-- Philosophy Section (Nosotros) -->
        <section class="philosophy" id="nosotros">
            <div class="philosophy__image-wrapper">
                <img alt="Interior del salón " class="philosophy__image" src="public/assets/media/logo.jpeg" />
            </div>
            <div class="philosophy__content">
                <h2 class="philosophy__title">Nuestra filosofía</h2>
                <p class="philosophy__text">
                    En Her Beauty Center creemos que la belleza exterior es un reflejo del equilibrio interior. Nuestro
                    espacio
                    fue concebido como un santuario urbano donde podés escapar de la rutina y reconectar con vos misma.
                </p>
                <p class="philosophy__text">
                    Utilizamos productos premium y técnicas innovadoras, siempre con un trato cálido y personalizado.
                    Porque tu bienestar es nuestra prioridad.
                </p>
                <div class="philosophy__action">
                    <a class="philosophy__link" href="#contacto">
                        Conocé más sobre nosotros <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </section>


        <!-- Spacer Divider -->
        <div class="section-divider"></div>

        <!-- Contact Section -->
        <section class="contact" id="contacto">
            <div class="contact__container">
                <div class="section-header">
                    <h2 class="section-title">Contacto</h2>
                    <p class="section-description">Estamos para asesorarte. Escribinos o visitanos en nuestro salón.</p>
                </div>

                <div class="contact__layout">
                    <!-- Contact Info Column -->
                    <div class="contact__info">
                        <h3 class="contact__subtitle">Información</h3>

                        <div class="contact__item">
                            <span class="material-symbols-outlined contact__icon">location_on</span>
                            <div class="contact__item-content">
                                <p class="contact__label">Dirección</p>
                                <p class="contact__value">San Luis, Argentina</p>

                            </div>
                        </div>

                        <!-- Embedded Google Maps -->
                        <div class="contact__map-container">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3334.78301708746!2d-66.3358812243156!3d-33.29833917345016!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95d439007adc689f%3A0xce11097090a24a75!2sHer%20Beauty%20Center!5e0!3m2!1sen!2sar!4v1787751775301!5m2!1sen!2sar"
                                width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>



                        <div class="contact__socials-section">
                            <h4 class="contact__socials-title">Seguinos</h4>
                            <div class="contact__socials">
                                <div class="footer__simple-socials-row">
                                    <a aria-label="Instagram" class="footer__simple-social-icon" href="#"
                                        target="_blank" rel="noopener noreferrer">
                                        <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18"
                                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                        </svg>
                                    </a>
                                    <a aria-label="Facebook" class="footer__simple-social-icon" href="#" target="_blank"
                                        rel="noopener noreferrer">
                                        <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18"
                                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a aria-label="TikTok" class="footer__simple-social-icon" href="#" target="_blank"
                                        rel="noopener noreferrer">
                                        <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18"
                                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="footer__simple-socials-row">
                                    <a aria-label="WhatsApp" class="footer__simple-social-icon"
                                        href="https://wa.me/5491112345678" target="_blank" rel="noopener noreferrer">
                                        <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18"
                                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a aria-label="Email" class="footer__simple-social-icon"
                                        href="mailto:info@herbeautycenter.com">
                                        <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18"
                                            stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                            </path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Column -->
                    <div class="contact__form-container">
                        <form class="contact__form" id="contactForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="name">Nombre completo</label>
                                    <input class="form-input" id="name" name="name" placeholder="Tu nombre" required
                                        type="text" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone">Teléfono</label>
                                    <input class="form-input" id="phone" name="phone" placeholder="Tu teléfono"
                                        type="tel" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-input" id="email" name="email" placeholder="tu@email.com" required
                                    type="email" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="service">Servicio de interés</label>
                                <select class="form-select" id="service" name="service" required>
                                    <option disabled selected value="">Seleccioná un servicio</option>
                                    <option value="cejas">Diseño de Cejas</option>
                                    <option value="pestanas">Pestañas</option>
                                    <option value="faciales">Tratamientos Faciales</option>
                                    <option value="unas">Uñas</option>
                                    <option value="depilacion">Depilación</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="message">Consulta</label>
                                <textarea class="form-textarea" id="message" name="message"
                                    placeholder="¿En qué te podemos ayudar?" required rows="4"></textarea>
                            </div>
                            <button class="btn btn--primary form-btn" type="submit">Enviar consulta</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Redesigned Footer -->
    <footer class="footer">
        <div class="footer__simple-container">
            <!-- Left Logo -->
            <div class="footer__simple-logo">
                <a href="#">
                    <img src="public/assets/media/logoText.jpeg" alt="Logo Her Beauty Center" class="footer__logo-img" />
                </a>
            </div>

            <!-- Middle Nav Links (Horizontal Row) -->
            <div class="footer__simple-nav">
                <a class="footer__simple-link" href="#servicios">Servicios</a>
                <a class="footer__simple-link" href="#profesionales">Profesionales</a>
                <a class="footer__simple-link" href="#nosotros">Nosotros</a>
                <a class="footer__simple-link" href="#contacto">Contacto</a>
            </div>

            <!-- Right Social Icons (2 Rows: 3 and 2) -->
            <div class="footer__simple-socials">
                <div class="footer__socials-inner-wrap">
                    <div class="footer__simple-socials-row">
                        <a aria-label="Instagram" class="footer__simple-social-icon" href="#" target="_blank"
                            rel="noopener noreferrer">
                            <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <a aria-label="Facebook" class="footer__simple-social-icon" href="#" target="_blank"
                            rel="noopener noreferrer">
                            <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a aria-label="TikTok" class="footer__simple-social-icon" href="#" target="_blank"
                            rel="noopener noreferrer">
                            <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="footer__simple-socials-row">
                        <a aria-label="WhatsApp" class="footer__simple-social-icon" href="https://wa.me/5491112345678"
                            target="_blank" rel="noopener noreferrer">
                            <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                                </path>
                            </svg>
                        </a>
                        <a aria-label="Email" class="footer__simple-social-icon" href="mailto:info@herbeautycenter.com">
                            <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="footer__bottom">
            <div class="footer__bottom-container">
                <p class="footer__copyright">© 2026 Her Beauty Center - Diseñado por Studio Web Space. Todos los
                    derechos reservados.</p>
                <div class="footer__bottom-links">
                    <a class="footer__bottom-link" href="#">Política de Privacidad</a>
                    <a class="footer__bottom-link" href="#">Términos de Servicio</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Auth Modals -->
    <div class="modal" id="loginModal">
        <div class="modal__overlay" data-close-modal></div>
        <div class="modal__content">
            <button class="modal__close" data-close-modal aria-label="Cerrar"><span class="material-symbols-outlined">close</span></button>
            <h2 class="modal__title">Iniciar Sesión</h2>
            <form class="modal__form" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email</label>
                    <input class="form-input" id="loginEmail" type="email" required placeholder="tu@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="loginPassword">Contraseña</label>
                    <input class="form-input" id="loginPassword" type="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn--primary modal__btn">Ingresar</button>
                <p class="modal__switch">¿No tienes cuenta? <a href="#" id="switchToRegister">Regístrate</a></p>
            </form>
        </div>
    </div>

    <div class="modal" id="registerModal">
        <div class="modal__overlay" data-close-modal></div>
        <div class="modal__content">
            <button class="modal__close" data-close-modal aria-label="Cerrar"><span class="material-symbols-outlined">close</span></button>
            <h2 class="modal__title">Crear Cuenta</h2>
            <form class="modal__form" id="registerForm">
                <div class="form-group">
                    <label class="form-label" for="regName">Nombre completo</label>
                    <input class="form-input" id="regName" type="text" required placeholder="Tu nombre">
                </div>
                <div class="form-group">
                    <label class="form-label" for="regEmail">Email</label>
                    <input class="form-input" id="regEmail" type="email" required placeholder="tu@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="regPassword">Contraseña</label>
                    <input class="form-input" id="regPassword" type="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn--primary modal__btn">Registrarse</button>
                <p class="modal__switch">¿Ya tienes cuenta? <a href="#" id="switchToLogin">Inicia sesión</a></p>
            </form>
        </div>
    </div>

    <!-- Custom JS -->
    <script src="public/assets/js/index.js" defer></script>
</body>

</html>