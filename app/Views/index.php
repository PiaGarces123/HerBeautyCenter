<?php
// Funciones de compatibilidad por si se accede al archivo fuera de CodeIgniter
if (!function_exists('base_url')) {
    function base_url($path = '')
    {
        return $path;
    }
}
if (!function_exists('esc')) {
    function esc($data)
    {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
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

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('public/assets/media/logoText.ico?v=' . time()) ?>" />
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('public/assets/media/logoText.ico?v=' . time()) ?>" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('public/assets/media/favicon-32x32.png?v=' . time()) ?>" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('public/assets/media/favicon-16x16.png?v=' . time()) ?>" />
    <link rel="apple-touch-icon" href="<?= base_url('public/assets/media/apple-touch-icon.png?v=' . time()) ?>" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />

    <!-- Custom CSS (overrides Bootstrap) -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />

    <!-- Estilos específicos de la página principal pública -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />
</head>

<body class="site-body">

    <!-- 
      |======================================================
      | INCLUSIÓN DEL HEADER Y MENÚ MÓVIL
      | Llama a la vista 'app/Views/modals/header.php' e inserta 
      | la barra de navegación superior (Navbar) y el drawer móvil.
      |====================================================== 
    -->
    <?= $this->include('modals/header') ?>

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
                <img alt="Servicios de estética, spa y belleza en Her Beauty Center" class="hero__image" data-alt="Servicios Her Beauty Center"
                    width="1815" height="1064"
                    width="1815" height="1064"
                    src="<?= base_url('public/assets/media/banner_hero.jpeg?v=' . time()) ?>" />
            </div>
        </section>

        <!-- Spacer Divider -->
        <div class="section-divider"></div>

        <!-- 
          |======================================================
          | SECCIÓN DE SERVICIOS
          | Aquí se muestra la grilla de servicios disponibles. 
          | Recibe la variable $servicios desde el controlador (Home.php).
          | Si hay servicios cargados en la BD, los itera (foreach).
          |====================================================== 
        -->
        <section class="services" id="servicios">
            <div class="section-header">
                <h2 class="section-title">Nuestros servicios</h2>
                <p class="section-description">Experiencias diseñadas meticulosamente para realzar cada detalle de tu
                    estética personal.</p>
            </div>

            <?php if (!empty($servicios)): ?>
                <div class="srv-grid">
                    <?php
                    $cardIndex = 0;
                    foreach ($servicios as $servicio):
                        // La ruta ya es URL absoluta guardada en la BD
                        $imagen = !empty($servicio['imagen_ruta'])
                            ? esc($servicio['imagen_ruta'])
                            : esc(base_url('public/assets/media/servicio_default.jpeg'));

                        // Patron bento box (la primera de cada 5 es ancha)
                        $isWide = ($cardIndex % 5 === 0) ? 'srv-card--wide' : '';
                        $cardIndex++;
                        ?>
                        <a href="<?= base_url('servicio/' . (int) $servicio['s_id']) ?>" class="srv-card <?= $isWide ?> text-decoration-none">
                            <div class="srv-card__img-wrap">
                                <img src="<?= $imagen ?>" alt="<?= esc($servicio['s_nbre']) ?>" class="srv-card__img"
                                    loading="lazy" />
                            </div>
                            <div class="srv-card__badge">
                                <span class="material-symbols-outlined">schedule</span>
                                <?= esc($servicio['s_duracionMinutos']) ?> min
                            </div>
                            <div class="srv-card__body">
                                <h3 class="srv-card__title">
                                    <?= esc($servicio['s_nbre']) ?>
                                </h3>
                                <p class="srv-card__desc"><?= esc(mb_strimwidth($servicio['s_desc'] ?? '', 0, 120, '...')) ?></p>
                                <?php if (!empty($servicio['s_precio']) && $servicio['s_precio'] > 0): ?>
                                    <p class="srv-card__price">Desde $<?= number_format($servicio['s_precio'], 0, ',', '.') ?></p>
                                <?php endif; ?>
                                <span class="srv-card__btn">
                                    Reservar Turno
                                    <span class="material-symbols-outlined">arrow_forward</span>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="section-empty-msg">No hay servicios disponibles por el momento.</p>
            <?php endif; ?>
        </section>


        <!-- Spacer Divider -->
        <div class="section-divider"></div>

        <!-- 
          |======================================================
          | SECCIÓN DE PROFESIONALES
          | Recibe la variable $profesionales desde el controlador (Home.php).
          | Muestra a cada profesional con su foto, descripción y redes sociales.
          |====================================================== 
        -->
        <section class="professionals" id="profesionales">

            <div class="section-header">
                <h2 class="section-title">Conocé a nuestras profesionales</h2>
                <p class="section-description">Un equipo de especialistas dedicadas a realzar tu belleza natural con
                    técnica, cuidado y un enfoque personalizado.</p>
            </div>

            <?php if (!empty($profesionales)): ?>
                <div class="professionals__grid">
                    <?php foreach ($profesionales as $prof): ?>
                        <?php
                        $avatar = !empty($prof['u_avatar']) ? base_url($prof['u_avatar']) : base_url('public/assets/media/íconoPerfil.png');
                        ?>
                        <div class="pro-card">
                            <img alt="<?= esc($prof['u_nbreCompleto']) ?> - <?= esc($prof['p_titulo']) ?>"
                                class="pro-card__image" src="<?= esc($avatar) ?>" />
                            <h3 class="pro-card__name"><?= esc($prof['u_nbreCompleto']) ?></h3>
                            <p class="pro-card__role"><?= esc($prof['p_titulo']) ?></p>
                            <p class="pro-card__bio"><?= esc($prof['p_desc']) ?></p>
                            <div class="pro-card__socials">
                                <?php if (!empty($prof['redes'])): ?>
                                    <?php foreach ($prof['redes'] as $red): 
                                        $rTipo = strtolower($red['rs_tipo'] ?? $red['tipo'] ?? '');
                                        $rLink = $red['rs_link'] ?? $red['link'] ?? '#';
                                    ?>
                                        <a aria-label="<?= esc($rTipo) ?>" class="pro-card__social-link"
                                            href="<?= esc($rLink) ?>" target="_blank" rel="noopener noreferrer">
                                            <?php if ($rTipo === 'instagram'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                                </svg>
                                            <?php elseif ($rTipo === 'facebook'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                                </svg>
                                            <?php elseif ($rTipo === 'tiktok'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                                                </svg>
                                            <?php elseif ($rTipo === 'whatsapp'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                                </svg>
                                            <?php elseif ($rTipo === 'linkedin'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                                    <rect x="2" y="9" width="4" height="12"></rect>
                                                    <circle cx="4" cy="4" r="2"></circle>
                                                </svg>
                                            <?php elseif ($rTipo === 'x'): ?>
                                                <svg class="svg-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 4l16 16M4 20L20 4"></path>
                                                </svg>
                                            <?php else: ?>
                                                <span class="material-symbols-outlined pro-card__social-icon-generic">link</span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <a href="#servicios" class="pro-card__btn text-decoration-none text-center">Reservar turno</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="section-empty-msg">No hay profesionales disponibles por el momento.</p>
            <?php endif; ?>
        </section>

        <!-- Banner/CTA Section -->
        <section class="cta-banner">
            <div class="cta-banner__content">
                <h2 class="cta-banner__title">Renová tu energía</h2>
                <p class="cta-banner__text">Regalate un momento de desconexión.
                    <br> Agendá tu cita hoy y dejate consentir en
                    <strong> Her Beauty Center</strong>
                </p>
                <a class="btn btn-shimmer cta-banner__btn" href="#servicios">
                    <span>Solicitar turno</span>
                    <span class="material-symbols-outlined cta-banner__btn-icon">arrow_forward</span>
                </a>
            </div>
        </section>

        <!-- Philosophy Section (Nosotros) -->
        <section class="philosophy" id="nosotros">
            <div class="philosophy__image-wrapper">
                <img alt="Interior del salón " class="philosophy__image" src="public/assets/media/filosofia.jpeg" />
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
                        Conocé más sobre nosotras <span class="material-symbols-outlined">arrow_forward</span>
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
                                width="600" height="450" allowfullscreen="" loading="lazy"
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
                        <form class="contact__form needs-validation" id="contactForm" novalidate>
                            <div class="form-row">
                                <div class="form-group position-relative">
                                    <label class="form-label" for="name">Nombre completo</label>
                                    <input class="form-input" id="name" name="name" placeholder="Tu nombre" required
                                        type="text" pattern="^[a-zA-ZáéíóúÁ�?Í�" �sñ�'\s]{3,}$" />
                                    <div class="invalid-feedback contact__invalid-feedback">Debe contener al menos 3
                                        letras.</div>
                                </div>
                                <div class="form-group position-relative">
                                    <label class="form-label" for="email">Email</label>
                                    <input class="form-input" id="email" name="email"
                                        placeholder="ejemplo@gmail.com (Opcional)" type="email"
                                        pattern="^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$" />
                                    <div class="invalid-feedback contact__invalid-feedback">Ingresa un correo válido.
                                    </div>
                                </div>
                            </div>

                            <div class="form-row contact__form-row--spaced">
                                <div class="form-group position-relative">
                                    <label class="form-label" for="service">Servicio de interés</label>
                                    <select class="form-select" id="service" name="service" required>
                                        <option disabled selected value="">Seleccioná un servicio</option>
                                        <?php if (!empty($servicios)): ?>
                                            <?php foreach ($servicios as $s): ?>
                                                <option value="<?= esc($s['s_id']) ?>"><?= esc($s['s_nbre']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <option value="otro">Otro</option>
                                    </select>
                                    <div class="invalid-feedback contact__invalid-feedback">Selecciona un servicio.
                                    </div>
                                </div>
                                <div class="form-group position-relative">
                                    <label class="form-label" for="message">Consulta</label>
                                    <textarea class="form-textarea" id="message" name="message"
                                        placeholder="¿En qué te podemos ayudar?" required rows="4"
                                        minlength="5"></textarea>
                                    <div class="invalid-feedback contact__invalid-feedback">El mensaje debe tener al
                                        menos 5 caracteres.</div>
                                </div>
                            </div>

                            <button class="btn btn--primary form-btn contact__submit-btn" type="submit">Enviar
                                consulta</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- 
      |======================================================
      | PIE DE PÁGINA (FOOTER) Y MODALES GLOBALES
      | - footer.php: Contiene la estructura final de la página (links, copy).
      | - modalsBase.php: Contiene el HTML oculto de los modales de Login y Registro.
      |====================================================== 
    -->
    <?= $this->include('modals/footer') ?>
    <?= $this->include('modals/modalsBase') ?>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="public/assets/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            apiUrl: '<?= rtrim(base_url('api'), '/') ?>'
        };

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('login') === '1') {
                const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();

                // Clean the URL to avoid opening modal on refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
    <script src="public/assets/js/index.js?v=<?= time() ?>" defer></script>
</body>

</html>