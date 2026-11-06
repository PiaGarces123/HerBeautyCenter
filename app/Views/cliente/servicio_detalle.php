<?php
$s = $servicio ?? [];
$imgs = $imagenes ?? [];
$profs = $profesionales ?? [];
$logueado = (bool) (function_exists('session') && session()->get('usuario_id'));
$defaultImg = base_url('public/assets/media/servicio_default.jpeg');
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="description" content="<?= esc($s['s_nbre'] ?? 'Servicio') ?> — Her Beauty Center" />
    <title><?= esc($s['s_nbre'] ?? 'Detalle del Servicio') ?> | Her Beauty Center</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />

    <!-- Estilos del Sistema -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/cliente.css?v=' . time()) ?>" />
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>

    <main class="client-page-main">
        <div class="client-container">

            <!-- Breadcrumb -->
            <nav aria-label="Ruta de navegación" class="client-breadcrumb">
                <a href="<?= base_url('/') ?>">Inicio</a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <a href="<?= base_url('/#servicios') ?>">Servicios</a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <span class="fw-semibold text-dark"><?= esc($s['s_nbre'] ?? '') ?></span>
            </nav>

            <!-- Grilla Principal del Servicio: Carrusel + Detalle -->
            <section class="srv-detail-grid">

                <!-- Columna Izquierda: Carrusel de Fotos Bootstrap 5 -->
                <div class="srv-detail-carousel-card">
                    <?php if (!empty($imgs)): ?>
                        <div id="servicioCarousel" class="carousel slide" data-bs-ride="carousel">
                            <?php if (count($imgs) > 1): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($imgs as $idx => $img): ?>
                                        <button type="button" data-bs-target="#servicioCarousel" data-bs-slide-to="<?= $idx ?>"
                                            class="<?= $idx === 0 ? 'active' : '' ?>"
                                            aria-current="<?= $idx === 0 ? 'true' : 'false' ?>"
                                            aria-label="Foto <?= $idx + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="carousel-inner">
                                <?php foreach ($imgs as $idx => $img): ?>
                                    <?php
                                    $ruta = $img['img_ruta'] ?? '';
                                    $imgSrc = (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://'))
                                        ? $ruta
                                        : base_url($ruta);
                                    ?>
                                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                                        <img src="<?= esc($imgSrc) ?>" class="srv-detail-carousel__img"
                                            alt="<?= esc($s['s_nbre']) ?> - Imagen <?= $idx + 1 ?>" />
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($imgs) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#servicioCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#servicioCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <img src="<?= esc($defaultImg) ?>" class="srv-detail-carousel__img" alt="<?= esc($s['s_nbre'] ?? 'Servicio') ?>" />
                    <?php endif; ?>
                </div>

                <!-- Columna Derecha: Descripción, Información y Botón Condicional -->
                <div class="srv-detail-info-card">
                    <span class="text-uppercase fw-bold small" style="color: var(--color-primary); letter-spacing: 0.08em;">
                        Experiencia Personalizada
                    </span>
                    <h1 class="mt-1 mb-2" style="font-size: clamp(1.85rem, 3vw, 2.5rem);">
                        <?= esc($s['s_nbre'] ?? '') ?>
                    </h1>

                    <!-- Píldoras de Duración, Precio Base y Disponibilidad -->
                    <div class="srv-detail-meta">
                        <span class="srv-detail-pill">
                            <span class="material-symbols-outlined fs-5">schedule</span>
                            Duración estimada: <?= esc($s['s_duracionMinutos'] ?? 60) ?> min
                        </span>
                        <?php if (!empty($s['s_precio']) && $s['s_precio'] > 0): ?>
                            <span class="srv-detail-pill srv-detail-pill--price">
                                <span class="material-symbols-outlined fs-5">payments</span>
                                Desde $<?= number_format((float) $s['s_precio'], 0, ',', '.') ?>
                            </span>
                        <?php endif; ?>
                        <span class="srv-detail-pill">
                            <span class="material-symbols-outlined fs-5" style="color: var(--color-primary);">event_available</span>
                            <?= (int) ($totalHorarios ?? 0) ?> horarios disponibles
                        </span>
                    </div>

                    <!-- Descripción completa del servicio -->
                    <p class="text-muted mb-4" style="line-height: 1.75; font-size: 0.98rem;">
                        <?= nl2br(esc($s['s_desc'] ?? 'Servicio integral de estética y cuidado personal realizado con productos de primera línea.')) ?>
                    </p>

                    <!-- Especialistas que realizan este servicio -->
                    <?php if (!empty($profs)): ?>
                        <div class="mb-4">
                            <h2 class="h6 fw-bold text-uppercase mb-2" style="letter-spacing: 0.05em; color: var(--color-text-muted);">
                                Especialistas que realizan este servicio
                            </h2>
                            <div class="srv-detail-pro-list">
                                <?php foreach ($profs as $prof): ?>
                                    <?php
                                    $pAvatar = !empty($prof['u_avatar'])
                                        ? base_url($prof['u_avatar'])
                                        : base_url('public/assets/media/íconoPerfil.png');
                                    $urlReservaProf = base_url('reservar/' . $s['s_id'] . '?p_id=' . $prof['p_id']);
                                    ?>
                                    <div class="srv-detail-pro-item">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= esc($pAvatar) ?>" alt="<?= esc($prof['u_nbreCompleto']) ?>" class="srv-detail-pro-avatar" />
                                            <div>
                                                <p class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                                                    <?= esc($prof['u_nbreCompleto']) ?>
                                                </p>
                                                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                                    <?= esc($prof['p_titulo'] ?? 'Especialista') ?>
                                                </p>
                                            </div>
                                        </div>
                                        <?php if ($logueado): ?>
                                            <a href="<?= esc($urlReservaProf) ?>" class="btn btn--outline btn-sm py-1 px-3" style="font-size: 0.8rem;">
                                                Elegir especialista
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="btn btn--outline btn-sm py-1 px-3" style="font-size: 0.8rem;"
                                                data-bs-toggle="modal" data-bs-target="#loginModal"
                                                data-redirect-after-login="<?= esc($urlReservaProf) ?>">
                                                Elegir especialista
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CTA Principal Condicional (Invitado vs Cliente Autenticado) -->
                    <div class="pt-2 border-top" style="border-color: var(--color-border) !important;">
                        <?php if ($logueado): ?>
                            <a href="<?= base_url('reservar/' . $s['s_id']) ?>" class="btn btn--primary w-100 py-3 fs-6">
                                <span class="material-symbols-outlined">calendar_month</span>
                                Reservar Turno
                            </a>
                            <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.82rem;">
                                Podrás elegir la fecha, el horario y tu profesional preferida en el próximo paso.
                            </p>
                        <?php else: ?>
                            <button type="button" class="btn btn--primary w-100 py-3 fs-6"
                                data-bs-toggle="modal" data-bs-target="#loginModal"
                                data-redirect-after-login="<?= base_url('reservar/' . $s['s_id']) ?>">
                                <span class="material-symbols-outlined">login</span>
                                Iniciá sesión para reservar tu turno
                            </button>
                            <p class="text-muted text-center mt-2 mb-0" style="font-size: 0.82rem;">
                                ¿Todavía no tenés cuenta? Podés registrarte gratis en segundos desde la misma ventana.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Otros Servicios Sugeridos -->
            <?php if (!empty($otrosServicios)): ?>
                <section class="mt-5 pt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h2 class="h3 mb-1">Otros servicios que te pueden interesar</h2>
                            <p class="text-muted mb-0" style="font-size: 0.92rem;">Explorá más tratamientos diseñados para vos.</p>
                        </div>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--outline">
                            Ver catálogo completo
                        </a>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($otrosServicios as $otro): ?>
                            <?php
                            $otroImg = !empty($otro['imagen_ruta'])
                                ? ((str_starts_with($otro['imagen_ruta'], 'http')) ? $otro['imagen_ruta'] : base_url($otro['imagen_ruta']))
                                : $defaultImg;
                            ?>
                            <div class="col-12 col-md-4">
                                <article class="srv-card h-100">
                                    <a href="<?= base_url('servicio/' . $otro['s_id']) ?>" class="srv-card__img-wrap d-block text-decoration-none">
                                        <img src="<?= esc($otroImg) ?>" alt="<?= esc($otro['s_nbre']) ?>" class="srv-card__img" loading="lazy" />
                                        <div class="srv-card__badge">
                                            <span class="material-symbols-outlined">schedule</span>
                                            <?= esc($otro['s_duracionMinutos']) ?> min
                                        </div>
                                    </a>
                                    <div class="srv-card__body">
                                        <h3 class="srv-card__title"><?= esc($otro['s_nbre']) ?></h3>
                                        <p class="srv-card__desc"><?= esc(mb_strimwidth($otro['s_desc'] ?? '', 0, 100, '...')) ?></p>
                                        <?php if (!empty($otro['s_precio']) && $otro['s_precio'] > 0): ?>
                                            <p class="srv-card__price">Desde $<?= number_format((float) $otro['s_precio'], 0, ',', '.') ?></p>
                                        <?php endif; ?>
                                        <a href="<?= base_url('servicio/' . $otro['s_id']) ?>" class="srv-card__btn">
                                            Ver detalle y reservar
                                            <span class="material-symbols-outlined">arrow_forward</span>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </main>

    <?= $this->include('modals/footer') ?>
    <?= $this->include('modals/modalsBase') ?>

    <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            apiUrl: '<?= rtrim(base_url('api'), '/') ?>'
        };
    </script>
    <script src="<?= base_url('public/assets/js/index.js?v=' . time()) ?>" defer></script>
</body>

</html>

