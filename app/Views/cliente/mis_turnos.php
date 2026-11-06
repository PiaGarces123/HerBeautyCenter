<?php
$prox = $proximoTurno ?? null;
$listaTurnos = $turnos ?? [];
$cnt = $contadores ?? [];
$fEstado = $filtroEstado ?? '';
$fPeriodo = $filtroPeriodo ?? 'todos';

$mesesCortos = [
    1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
    5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
    9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC',
];
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Mis Turnos — Panel del Cliente | Her Beauty Center</title>

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

            <!-- Pestañas de Navegación del Panel del Cliente -->
            <nav class="client-panel-tabs" aria-label="Navegación del panel del cliente">
                <a href="<?= base_url('mis-turnos') ?>" class="client-panel-tab client-panel-tab--active">
                    <span class="material-symbols-outlined fs-5">calendar_month</span>
                    <span>Mis Turnos</span>
                </a>
                <a href="<?= base_url('mi-perfil') ?>" class="client-panel-tab">
                    <span class="material-symbols-outlined fs-5">person</span>
                    <span>Mi Perfil</span>
                </a>
                <a href="<?= base_url('/#servicios') ?>" class="client-panel-tab ms-md-auto">
                    <span class="material-symbols-outlined fs-5">add_circle</span>
                    <span>Reservar Nuevo Turno</span>
                </a>
            </nav>

            <!-- Encabezado de Bienvenida -->
            <div class="mb-4">
                <h1 class="mb-1" style="font-size: clamp(1.85rem, 3vw, 2.4rem);">
                    Hola, <?= esc(explode(' ', session()->get('usuario_nombre') ?? 'Clienta')[0]) ?>
                </h1>
                <p class="text-muted mb-0">
                    Desde tu panel personal podés consultar el estado de tus reservas, agendarlas en tu calendario o gestionar tus citas.
                </p>
            </div>

            <!-- ==================================================================
                 TARJETA DESTACADA: «MI PRÓXIMO TURNO» (Prioridad Visual CU05)
                 ================================================================== -->
            <section class="next-appointment-card" aria-label="Mi próximo turno">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="next-appointment-card__eyebrow">
                        <span class="material-symbols-outlined fs-6">auto_awesome</span>
                        Mi próximo turno
                    </span>
                    <?php if ($prox): ?>
                        <span class="status-badge status-badge--<?= esc($prox['estado']) ?>">
                            <span class="material-symbols-outlined fs-6">
                                <?= $prox['estado'] === 'Confirmado' ? 'verified' : 'schedule' ?>
                            </span>
                            <?= esc($prox['estado']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($prox): ?>
                    <?php
                    $tsProx = strtotime($prox['fecha']);
                    $diaNum = date('d', $tsProx);
                    $mesTxt = $mesesCortos[(int) date('n', $tsProx)] ?? '';
                    $fechaProxFmt = date('d/m/Y', $tsProx);
                    $horaDesdeProx = substr($prox['h_horaDesde'], 0, 5);
                    $horaHastaProx = substr($prox['h_horaHasta'], 0, 5);
                    $avatarProx = !empty($prox['avatar_profesional'])
                        ? base_url($prox['avatar_profesional'])
                        : base_url('public/assets/media/íconoPerfil.png');
                    ?>
                    <div class="next-appointment-card__grid mt-1">
                        <!-- Servicio y Profesional -->
                        <div>
                            <h2 class="h3 mb-2 text-dark"><?= esc($prox['nombre_servicio'] ?? 'Servicio') ?></h2>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= esc($avatarProx) ?>" alt="<?= esc($prox['nombre_profesional'] ?? '') ?>"
                                    class="srv-detail-pro-avatar" />
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        Profesional: <?= esc($prox['nombre_profesional'] ?? '—') ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= esc($prox['titulo_profesional'] ?? 'Especialista') ?>
                                        <?php if (!empty($prox['monto']) && $prox['monto'] > 0): ?>
                                            · Base $<?= number_format((float) $prox['monto'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fecha y Horario -->
                        <div class="next-appointment-datebox">
                            <div class="next-appointment-datebox__day">
                                <span class="fw-bold fs-5"><?= esc($diaNum) ?></span>
                                <span style="font-size: 0.68rem; letter-spacing: 0.06em;"><?= esc($mesTxt) ?></span>
                            </div>
                            <div>
                                <div class="fw-bold text-dark"><?= esc($fechaProxFmt) ?></div>
                                <div class="text-muted small d-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined fs-6" style="color: var(--color-primary);">schedule</span>
                                    <?= esc($horaDesdeProx) ?> hs a <?= esc($horaHastaProx) ?> hs
                                </div>
                            </div>
                        </div>

                        <!-- Acciones disponibles -->
                        <div class="d-flex flex-wrap flex-lg-column gap-2 justify-content-end">
                            <button type="button" class="btn btn--primary py-2 px-3 btn-gcal-client"
                                data-tid="<?= (int) $prox['t_id'] ?>" style="font-size: 0.85rem;">
                                <span class="material-symbols-outlined fs-5">event</span>
                                Google Calendar
                            </button>
                            <button type="button" class="btn btn--outline py-2 px-3 btn-cancelar-turno"
                                data-tid="<?= (int) $prox['t_id'] ?>"
                                data-servicio="<?= esc($prox['nombre_servicio'] ?? 'turno') ?>"
                                data-fecha="<?= esc($fechaProxFmt) ?>"
                                data-hora="<?= esc($horaDesdeProx) ?>"
                                style="font-size: 0.85rem; border-color: #dc3545; color: #dc3545;">
                                <span class="material-symbols-outlined fs-5">cancel</span>
                                Cancelar Turno
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Estado vacío agradable si no tiene próximos turnos -->
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pt-1">
                        <div>
                            <h2 class="h4 mb-1 text-dark">No tenés próximos turnos programados</h2>
                            <p class="text-muted mb-0" style="font-size: 0.92rem;">
                                ¿Querés regalarte un momento de bienestar? Explorá nuestros servicios y elegí el horario que mejor se adapte a vos.
                            </p>
                        </div>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--primary py-2 px-4 flex-shrink-0">
                            <span class="material-symbols-outlined fs-5">spa</span>
                            Solicitar un Turno
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ==================================================================
                 FILTROS DE BÚSQUEDA Y LISTADO DE "MIS TURNOS" (CU05)
                 ================================================================== -->
            <section aria-label="Listado de mis turnos">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="h4 mb-0">Historial y Seguimiento de Turnos</h2>
                        <p class="text-muted small mb-0">Filtrá tus reservas por período o estado.</p>
                    </div>

                    <!-- Controles de Filtro (Período y Estado) -->
                    <form method="get" action="<?= base_url('mis-turnos') ?>" class="d-flex flex-wrap align-items-center gap-2">
                        <select name="periodo" class="form-select py-2 px-3" style="width: auto; min-width: 175px; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="todos" <?= $fPeriodo === 'todos' ? 'selected' : '' ?>>
                                Todos (<?= (int) ($cnt['todos'] ?? 0) ?>)
                            </option>
                            <option value="proximos" <?= $fPeriodo === 'proximos' ? 'selected' : '' ?>>
                                Próximos (<?= (int) ($cnt['proximos'] ?? 0) ?>)
                            </option>
                            <option value="historial" <?= $fPeriodo === 'historial' ? 'selected' : '' ?>>
                                Historial (<?= (int) ($cnt['historial'] ?? 0) ?>)
                            </option>
                        </select>

                        <select name="estado" class="form-select py-2 px-3" style="width: auto; min-width: 185px; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="" <?= $fEstado === '' ? 'selected' : '' ?>>Todos los estados</option>
                            <option value="Solicitado" <?= $fEstado === 'Solicitado' ? 'selected' : '' ?>>
                                Solicitado (<?= (int) ($cnt['Solicitado'] ?? 0) ?>)
                            </option>
                            <option value="Confirmado" <?= $fEstado === 'Confirmado' ? 'selected' : '' ?>>
                                Confirmado (<?= (int) ($cnt['Confirmado'] ?? 0) ?>)
                            </option>
                            <option value="Realizado" <?= $fEstado === 'Realizado' ? 'selected' : '' ?>>
                                Realizado (<?= (int) ($cnt['Realizado'] ?? 0) ?>)
                            </option>
                            <option value="Cancelado" <?= $fEstado === 'Cancelado' ? 'selected' : '' ?>>
                                Cancelado (<?= (int) ($cnt['Cancelado'] ?? 0) ?>)
                            </option>
                        </select>

                        <?php if ($fEstado !== '' || $fPeriodo !== 'todos'): ?>
                            <a href="<?= base_url('mis-turnos') ?>" class="btn btn--outline py-2 px-3" style="font-size: 0.85rem;">
                                Limpiar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (!empty($listaTurnos)): ?>
                    <div class="client-turnos-grid">
                        <?php foreach ($listaTurnos as $t): ?>
                            <?php
                            $fTurno = date('d/m/Y', strtotime($t['fecha']));
                            $hDesde = substr($t['h_horaDesde'], 0, 5);
                            $hHasta = substr($t['h_horaHasta'], 0, 5);
                            $estado = $t['estado'] ?? 'Solicitado';
                            $esActivo = in_array($estado, ['Solicitado', 'Confirmado'], true) && $t['fecha'] >= date('Y-m-d');
                            ?>
                            <article class="client-turno-card">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h3 class="h5 mb-0 fw-bold text-dark">
                                                <?= esc($t['nombre_servicio'] ?? 'Servicio') ?>
                                            </h3>
                                            <span class="text-muted small">
                                                Con <?= esc($t['nombre_profesional'] ?? '—') ?> (<?= esc($t['titulo_profesional'] ?? 'Especialista') ?>)
                                            </span>
                                        </div>
                                        <span class="status-badge status-badge--<?= esc($estado) ?>">
                                            <?= esc($estado) ?>
                                        </span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 mt-3 pt-2 border-top" style="border-color: var(--color-border) !important; font-size: 0.88rem;">
                                        <span class="d-inline-flex align-items-center gap-1 text-dark fw-semibold">
                                            <span class="material-symbols-outlined fs-6" style="color: var(--color-primary);">calendar_today</span>
                                            <?= esc($fTurno) ?>
                                        </span>
                                        <span class="d-inline-flex align-items-center gap-1 text-dark fw-semibold">
                                            <span class="material-symbols-outlined fs-6" style="color: var(--color-primary);">schedule</span>
                                            <?= esc($hDesde) ?> a <?= esc($hHasta) ?> hs
                                        </span>
                                        <?php if (!empty($t['monto']) && $t['monto'] > 0): ?>
                                            <span class="d-inline-flex align-items-center gap-1 text-muted">
                                                <span class="material-symbols-outlined fs-6">payments</span>
                                                $<?= number_format((float) $t['monto'], 0, ',', '.') ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($t['motivo_cancelacion'])): ?>
                                        <p class="text-muted small mt-2 mb-0 p-2 rounded" style="background: var(--color-bg-light);">
                                            <?= esc($t['motivo_cancelacion']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top" style="border-color: var(--color-border) !important;">
                                    <?php if ($esActivo): ?>
                                        <button type="button" class="btn btn--outline py-1 px-3 btn-gcal-client"
                                            data-tid="<?= (int) $t['t_id'] ?>" style="font-size: 0.8rem;">
                                            <span class="material-symbols-outlined fs-6">event</span>
                                            Google Calendar
                                        </button>
                                        <button type="button" class="btn btn--outline py-1 px-3 btn-cancelar-turno"
                                            data-tid="<?= (int) $t['t_id'] ?>"
                                            data-servicio="<?= esc($t['nombre_servicio'] ?? 'turno') ?>"
                                            data-fecha="<?= esc($fTurno) ?>"
                                            data-hora="<?= esc($hDesde) ?>"
                                            style="font-size: 0.8rem; border-color: #dc3545; color: #dc3545;">
                                            <span class="material-symbols-outlined fs-6">cancel</span>
                                            Cancelar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small">Turno #<?= (int) $t['t_id'] ?></span>
                                        <?php if (!empty($t['servicio_id'])): ?>
                                            <a href="<?= base_url('servicio/' . (int) $t['servicio_id']) ?>" class="btn btn--outline py-1 px-3" style="font-size: 0.8rem;">
                                                Volver a reservar
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="confirm-summary-card text-center py-5">
                        <span class="material-symbols-outlined fs-1 mb-2" style="color: var(--color-primary);">event_note</span>
                        <h3 class="h5 mb-1">No se encontraron turnos para el filtro seleccionado</h3>
                        <p class="text-muted small mb-3">Podés cambiar los criterios de búsqueda o reservar un nuevo turno.</p>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--primary py-2 px-4">
                            Ver Servicios Disponibles
                        </a>
                    </div>
                <?php endif; ?>
            </section>

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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Exportar turno a Google Calendar (RFC 5545)
        document.querySelectorAll('.btn-gcal-client').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tId = btn.getAttribute('data-tid');
                if (!tId) return;

                try {
                    const res = await fetch(`<?= base_url('api/cliente/turnos/google-calendar') ?>/${tId}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.google_sync_url) {
                        window.open(data.google_sync_url, '_blank', 'noopener,noreferrer');
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.messages?.error || data.message || 'No se pudo generar el enlace de Google Calendar.',
                            icon: 'error',
                            confirmButtonColor: '#d6858e'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo comunicar con el servidor.',
                        icon: 'error',
                        confirmButtonColor: '#d6858e'
                    });
                }
            });
        });

        // Cancelar turno propio con confirmación SweetAlert2
        document.querySelectorAll('.btn-cancelar-turno').forEach(btn => {
            btn.addEventListener('click', () => {
                const tId      = btn.getAttribute('data-tid');
                const servicio = btn.getAttribute('data-servicio');
                const fecha    = btn.getAttribute('data-fecha');
                const hora     = btn.getAttribute('data-hora');

                Swal.fire({
                    title: '¿Cancelar tu turno?',
                    html: `Estás por cancelar tu turno de <strong>${servicio}</strong> del <strong>${fecha} a las ${hora} hs</strong>.`,
                    input: 'text',
                    inputPlaceholder: 'Motivo de cancelación (opcional)',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, cancelar turno',
                    cancelButtonText: 'Mantener turno',
                    reverseButtons: true
                }).then(async (result) => {
                    if (!result.isConfirmed) return;

                    try {
                        const res = await fetch(`<?= base_url('api/cliente/turnos/cancelar') ?>/${tId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                motivo_cancelacion: (result.value || '').trim()
                            })
                        });

                        const data = await res.json();
                        if (res.ok) {
                            Swal.fire({
                                title: 'Turno Cancelado',
                                text: data.message || 'Tu turno fue cancelado correctamente.',
                                icon: 'success',
                                confirmButtonColor: '#d6858e'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'No se pudo cancelar',
                                text: data.messages?.error || data.message || 'Ocurrió un error al cancelar el turno.',
                                icon: 'error',
                                confirmButtonColor: '#d6858e'
                            });
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'Error de conexión',
                            text: 'No se pudo comunicar con el servidor.',
                            icon: 'error',
                            confirmButtonColor: '#d6858e'
                        });
                    }
                });
            });
        });
    });
    </script>
</body>

</html>

