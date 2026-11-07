<?php
$s      = $servicio ?? [];
$imgs   = $imagenes ?? [];
$profs  = $profesionales ?? [];
$rol    = session()->get('usuario_id') ? session()->get('rol') : null;
$esCliente = $rol === 'cliente';
$esStaff   = in_array($rol, ['admin', 'profesional'], true);

$defaultImg = base_url('public/assets/media/servicio_default.jpeg');
$defaultAvatar = base_url('public/assets/media/íconoPerfil.png');
$urlReserva = base_url('reservar/' . (int) $s['s_id']);

// Normaliza rutas absolutas o relativas de imágenes
$srcImg = static fn (string $ruta): string => preg_match('#^https?://#', $ruta) ? $ruta : base_url($ruta);
?>
<?= view('cliente/_head', ['pageTitle' => $s['s_nbre'] ?? 'Servicio']) ?>

    <main class="cl-main">
        <div class="cl-wrap">

            <article class="cl-detail">

                <!-- Galería / Carrusel Bootstrap -->
                <div class="cl-detail__gallery cl-rise">
                    <div class="cl-gallery">
                        <?php if (count($imgs) > 1): ?>
                            <div id="servicioCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000" data-bs-pause="hover">
                                <div class="carousel-indicators">
                                    <?php foreach ($imgs as $i => $img): ?>
                                        <button type="button" data-bs-target="#servicioCarousel" data-bs-slide-to="<?= $i ?>"
                                            class="<?= $i === 0 ? 'active' : '' ?>" <?= $i === 0 ? 'aria-current="true"' : '' ?>
                                            aria-label="Foto <?= $i + 1 ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="carousel-inner">
                                    <?php foreach ($imgs as $i => $img): ?>
                                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>" data-bs-interval="2000">
                                            <img src="<?= esc($srcImg($img['img_ruta'] ?? '')) ?>" class="cl-gallery__img"
                                                alt="<?= esc($s['s_nbre']) ?> — foto <?= $i + 1 ?>"
                                                <?= $i > 0 ? 'loading="lazy"' : '' ?> />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#servicioCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#servicioCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            </div>
                        <?php else: ?>
                            <img src="<?= esc(!empty($imgs) ? $srcImg($imgs[0]['img_ruta'] ?? '') : $defaultImg) ?>"
                                class="cl-gallery__img" alt="<?= esc($s['s_nbre'] ?? 'Servicio') ?>" />
                        <?php endif; ?>

                        <span class="cl-pill cl-pill--brown cl-gallery__chip">
                            <span class="material-symbols-outlined">schedule</span>
                            <?= (int) ($s['s_duracionMinutos'] ?? 60) ?> min
                        </span>
                    </div>
                </div>

                <!-- Información del servicio -->
                <div class="cl-card cl-rise cl-rise--2">
                    <span class="cl-eyebrow">Experiencia personalizada</span>
                    <h1 class="cl-title"><?= esc($s['s_nbre'] ?? '') ?></h1>

                    <div class="cl-pills">
                        <?php if (!empty($s['s_precio']) && $s['s_precio'] > 0): ?>
                            <span class="cl-pill cl-pill--rose">
                                <span class="material-symbols-outlined">payments</span>
                                Desde $<?= number_format((float) $s['s_precio'], 0, ',', '.') ?>
                            </span>
                        <?php endif; ?>
                        <span class="cl-pill">
                            <span class="material-symbols-outlined">event_available</span>
                            <?= (int) ($totalHorarios ?? 0) ?> horarios disponibles
                        </span>
                    </div>

                    <p class="cl-detail__desc">
                        <?= nl2br(esc($s['s_desc'] ?? '')) ?>
                    </p>

                    <?php if (!empty($profs)): ?>
                        <h2 class="cl-section-label">Especialistas que lo realizan</h2>
                        <div class="cl-pros">
                            <?php foreach ($profs as $prof):
                                $pAvatar = !empty($prof['u_avatar']) ? base_url($prof['u_avatar']) : $defaultAvatar;
                                $urlProf = $urlReserva . '?p_id=' . (int) $prof['p_id'];
                            ?>
                                <div class="cl-pro">
                                    <img src="<?= esc($pAvatar) ?>" alt="" class="cl-avatar" loading="lazy" />
                                    <div class="cl-pro__info">
                                        <p class="cl-pro__name"><?= esc($prof['u_nbreCompleto']) ?></p>
                                        <p class="cl-pro__role"><?= esc($prof['p_titulo'] ?? 'Especialista') ?></p>
                                    </div>
                                    <?php if ($esCliente): ?>
                                        <a href="<?= esc($urlProf) ?>" class="cl-pro__link"
                                            aria-label="Reservar con <?= esc($prof['u_nbreCompleto']) ?>">
                                            <span class="material-symbols-outlined">arrow_forward</span>
                                        </a>
                                    <?php elseif (!$esStaff): ?>
                                        <button type="button" class="cl-pro__link" data-bs-toggle="modal" data-bs-target="#loginModal"
                                            data-redirect-after-login="<?= esc($urlProf) ?>"
                                            aria-label="Reservar con <?= esc($prof['u_nbreCompleto']) ?>">
                                            <span class="material-symbols-outlined">arrow_forward</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="cl-cta">
                        <?php if ($esCliente): ?>
                            <a href="<?= esc($urlReserva) ?>" class="btn btn--primary w-100">
                                <span class="material-symbols-outlined">calendar_month</span>
                                Reservar turno
                            </a>
                            <p class="cl-muted">Elegís fecha, horario y especialista en el próximo paso.</p>
                        <?php elseif ($esStaff): ?>
                            <a href="<?= base_url('admin') ?>" class="btn btn--outline w-100">
                                <span class="material-symbols-outlined">dashboard</span>
                                Ir a mi panel
                            </a>
                            <p class="cl-muted">La reserva online está disponible para cuentas de clientas.</p>
                        <?php else: ?>
                            <button type="button" class="btn btn--primary w-100" data-bs-toggle="modal"
                                data-bs-target="#loginModal" data-redirect-after-login="<?= esc($urlReserva) ?>">
                                <span class="material-symbols-outlined">login</span>
                                Iniciá sesión para reservar
                            </button>
                            <p class="cl-muted">¿No tenés cuenta? Registrate gratis desde la misma ventana.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <?php if (!empty($otrosServicios)): ?>
                <section class="cl-related" aria-labelledby="relatedTitle">
                    <div class="cl-head--split">
                        <div>
                            <span class="cl-eyebrow">Seguí explorando</span>
                            <h2 class="cl-subtitle" id="relatedTitle">Otros servicios que te pueden interesar</h2>
                            <p class="cl-lead">Explorá más tratamientos diseñados para vos.</p>
                        </div>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--outline cl-btn-sm">Ver catálogo</a>
                    </div>

                    <div class="srv-grid">
                        <?php foreach ($otrosServicios as $otro):
                            $otroImg = !empty($otro['imagen_ruta']) ? $srcImg($otro['imagen_ruta']) : $defaultImg;
                            $urlOtro = base_url('servicio/' . (int) $otro['s_id']);
                        ?>
                            <a href="<?= esc($urlOtro) ?>" class="srv-card text-decoration-none">
                                <div class="srv-card__img-wrap">
                                    <img src="<?= esc($otroImg) ?>" alt="<?= esc($otro['s_nbre']) ?>" class="srv-card__img" loading="lazy" />
                                </div>
                                <div class="srv-card__badge">
                                    <span class="material-symbols-outlined">schedule</span>
                                    <?= (int) $otro['s_duracionMinutos'] ?> min
                                </div>
                                <div class="srv-card__body">
                                    <h3 class="srv-card__title"><?= esc($otro['s_nbre']) ?></h3>
                                    <p class="srv-card__desc"><?= esc(mb_strimwidth($otro['s_desc'] ?? '', 0, 100, '...')) ?></p>
                                    <?php if (!empty($otro['s_precio']) && $otro['s_precio'] > 0): ?>
                                        <p class="srv-card__price">Desde $<?= number_format((float) $otro['s_precio'], 0, ',', '.') ?></p>
                                    <?php endif; ?>
                                    <span class="srv-card__btn">
                                        Ver detalle
                                        <span class="material-symbols-outlined">arrow_forward</span>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('servicioCarousel');
    if (el && typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
        const carousel = bootstrap.Carousel.getOrCreateInstance(el, {
            interval: 2000,
            ride: 'carousel',
            pause: 'hover',
            wrap: true
        });
        carousel.cycle();
    }
});
</script>

<?= view('cliente/_scripts') ?>
</body>

</html>
