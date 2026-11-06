<?php
$s = $servicio ?? [];
$profs = $profesionales ?? [];
$pSel = (int) ($pIdPreseleccionado ?? 0);
?>
<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Reservá tu Turno — <?= esc($s['s_nbre'] ?? 'Servicio') ?> | Her Beauty Center</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>" />

    <!-- Estilos del Sistema -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/calendario.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/cliente.css?v=' . time()) ?>" />

    <!-- FullCalendar v6 -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>

    <main class="client-page-main">
        <div class="client-container">

            <!-- Breadcrumb -->
            <nav aria-label="Ruta de navegación" class="client-breadcrumb">
                <a href="<?= base_url('/') ?>">Inicio</a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <a href="<?= base_url('servicio/' . ($s['s_id'] ?? 0)) ?>"><?= esc($s['s_nbre'] ?? 'Servicio') ?></a>
                <span class="material-symbols-outlined fs-6">chevron_right</span>
                <span class="fw-semibold text-dark">Reservá tu Turno</span>
            </nav>

            <!-- Stepper de Progreso (CU04) -->
            <div class="booking-stepper">
                <a href="<?= base_url('servicio/' . ($s['s_id'] ?? 0)) ?>" class="booking-step booking-step--done text-decoration-none">
                    <span class="booking-step__num">✓</span>
                    <span>1. Servicio elegido</span>
                </a>
                <div class="booking-stepper__line"></div>
                <div class="booking-step booking-step--active">
                    <span class="booking-step__num">2</span>
                    <span>2. Fecha y Horario</span>
                </div>
                <div class="booking-stepper__line"></div>
                <div class="booking-step">
                    <span class="booking-step__num">3</span>
                    <span>3. Confirmar Datos</span>
                </div>
            </div>

            <!-- Encabezado de la Vista -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="mb-1" style="font-size: clamp(1.85rem, 3vw, 2.5rem);">Reservá tu Turno</h1>
                    <p class="text-muted mb-0">
                        Servicio seleccionado: <strong class="text-dark"><?= esc($s['s_nbre'] ?? '') ?></strong>
                        (<?= esc($s['s_duracionMinutos'] ?? 60) ?> min · Desde $<?= number_format((float) ($s['s_precio'] ?? 0), 0, ',', '.') ?>)
                    </p>
                </div>
                <a href="<?= base_url('servicio/' . ($s['s_id'] ?? 0)) ?>" class="btn btn--outline py-2 px-3" style="font-size: 0.85rem;">
                    <span class="material-symbols-outlined fs-6">arrow_back</span>
                    Volver al servicio
                </a>
            </div>

            <!-- Guía rápida para la clienta -->
            <div class="booking-guide-banner">
                <div class="d-flex align-items-start gap-3">
                    <span class="material-symbols-outlined fs-3" style="color: var(--color-primary);">tips_and_updates</span>
                    <div>
                        <p class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">¿Cómo reservar tu cita?</p>
                        <p class="mb-0 text-muted" style="font-size: 0.875rem; line-height: 1.55;">
                            1. Si tenés preferencia por una especialista, un día de la semana o un turno (mañana/tarde), usá los <strong>filtros superiores</strong>.<br />
                            2. Hacé clic sobre una <strong>fecha destacada en el calendario</strong> (o de la lista rápida) para abrir la ventana con los horarios libres.<br />
                            3. Elegí la tarjeta con la profesional y horario que prefieras y presioná <strong>Guardar</strong> para completar tus datos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros de Búsqueda (Profesional, Día de la semana, Franja horaria y Fecha exacta) -->
            <div class="booking-filters-card">
                <div class="row g-3 align-items-end">
                    <!-- Filtro 1: Profesional (<select>) -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="filterProfesional" class="form-label mb-1">Especialista</label>
                        <select id="filterProfesional" class="form-select">
                            <option value="0">Todas las profesionales</option>
                            <?php foreach ($profs as $p): ?>
                                <option value="<?= (int) $p['p_id'] ?>" <?= $pSel === (int) $p['p_id'] ? 'selected' : '' ?>>
                                    <?= esc($p['u_nbreCompleto']) ?> — <?= esc($p['p_titulo'] ?? 'Especialista') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filtro 2: Día de la semana -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="filterDiaSemana" class="form-label mb-1">Filtrar por día</label>
                        <select id="filterDiaSemana" class="form-select">
                            <option value="0">Todos los días (Lun a Sáb)</option>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                        </select>
                    </div>

                    <!-- Filtro 3: Franja horaria -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="filterFranja" class="form-label mb-1">Franja horaria</label>
                        <select id="filterFranja" class="form-select">
                            <option value="">Cualquier horario</option>
                            <option value="manana">Mañana (antes de las 13:00 hs)</option>
                            <option value="tarde">Tarde (desde las 13:00 hs)</option>
                        </select>
                    </div>

                    <!-- Filtro 4: Ir a fecha específica + Limpiar -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="filterFechaEspecifica" class="form-label mb-1">Buscar fecha exacta</label>
                        <div class="d-flex gap-2">
                            <input type="date" id="filterFechaEspecifica" class="form-input py-2" min="<?= date('Y-m-d') ?>" />
                            <button type="button" id="btnResetFiltros" class="btn btn--outline px-3 py-2" title="Limpiar filtros">
                                <span class="material-symbols-outlined fs-5">restart_alt</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grilla Principal: Calendario Interactivo + Lista Rápida de Fechas Disponibles -->
            <div class="booking-calendar-layout">

                <!-- Calendario FullCalendar v6 -->
                <div class="booking-calendar-box">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h2 class="h5 mb-0 fw-bold">Seleccioná una fecha en el calendario</h2>
                        <span id="badgeTotalTurnos" class="srv-detail-pill">
                            Cargando horarios...
                        </span>
                    </div>
                    <div id="clienteCalendario"></div>
                </div>

                <!-- Columna Lateral: Próximas Fechas Disponibles (acceso rápido táctil) -->
                <div class="booking-quickdates-box">
                    <h2 class="h5 mb-1 fw-bold">Próximas fechas libres</h2>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">
                        Tocá cualquier fecha para ver y elegir tu horario disponible.
                    </p>
                    <div id="quickDatesList" class="d-flex flex-column gap-2" style="max-height: 460px; overflow-y: auto;">
                        <p class="text-muted small py-3 text-center mb-0">Buscando horarios disponibles...</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ======================================================================
         MODAL REUTILIZABLE DE HORARIOS DISPONIBLES (CU04 Paso 2)
         Muestra las tarjetas:
         ---------------
         | Profesional |
         | 14:30 hs    |
         ---------------
         ====================================================================== -->
    <div class="modal fade" id="horariosDisponiblesModal" tabindex="-1" aria-labelledby="horariosDisponiblesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-xl);">
                <div class="modal-header border-bottom px-4 pt-4 pb-3" style="border-color: var(--color-border) !important;">
                    <div>
                        <span class="text-uppercase fw-bold small" style="color: var(--color-primary); letter-spacing: 0.06em;">
                            <?= esc($s['s_nbre'] ?? 'Servicio') ?>
                        </span>
                        <h3 class="modal-title h4 fw-bold mb-0" id="horariosDisponiblesLabel">
                            Horarios disponibles
                        </h3>
                        <p class="text-muted mb-0 small" id="modalFechaSubtitulo"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <p class="text-muted small mb-3">
                        Seleccioná el horario y la profesional de tu preferencia y luego presioná <strong>Guardar</strong>:
                    </p>

                    <div id="slotsGridContainer" class="slots-grid">
                        <!-- Se inyectan las tarjetas [ Profesional | 14:30 hs ] -->
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center" style="border-color: var(--color-border) !important;">
                    <button type="button" class="btn btn--outline py-2 px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" id="btnGuardarHorario" class="btn btn--primary py-2 px-4" disabled>
                        <span>Guardar</span>
                        <span class="material-symbols-outlined fs-5">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
        const sId = <?= (int) ($s['s_id'] ?? 0) ?>;
        const apiDisponibilidadUrl = '<?= base_url('api/reservas/disponibilidad') ?>';
        const baseConfirmarUrl = '<?= base_url('reservar/confirmar') ?>';

        const filterProfesional     = document.getElementById('filterProfesional');
        const filterDiaSemana       = document.getElementById('filterDiaSemana');
        const filterFranja          = document.getElementById('filterFranja');
        const filterFechaEspecifica = document.getElementById('filterFechaEspecifica');
        const btnResetFiltros       = document.getElementById('btnResetFiltros');
        const badgeTotalTurnos      = document.getElementById('badgeTotalTurnos');
        const quickDatesList        = document.getElementById('quickDatesList');

        const modalEl              = document.getElementById('horariosDisponiblesModal');
        const modalFechaSubtitulo  = document.getElementById('modalFechaSubtitulo');
        const slotsGridContainer   = document.getElementById('slotsGridContainer');
        const btnGuardarHorario    = document.getElementById('btnGuardarHorario');

        let porFechaMap = {};
        let selectedHorarioId = null;

        // Si fue redirigido porque el turno acababa de ocuparse
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('ocupado') === '1') {
            Swal.fire({
                title: 'Horario no disponible',
                text: 'El horario que habías elegido ya no se encuentra disponible. Por favor, seleccioná otro horario libre.',
                icon: 'info',
                confirmButtonColor: '#d6858e'
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        const formatearFechaLarga = (fechaYmd) => {
            const [y, m, d] = fechaYmd.split('-').map(Number);
            const dateObj = new Date(y, m - 1, d);
            return dateObj.toLocaleDateString('es-AR', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        };

        // Abre el modal de horarios disponibles para una fecha específica
        const abrirModalFecha = (fechaYmd) => {
            const slots = porFechaMap[fechaYmd] || [];
            if (!slots.length) {
                Swal.fire({
                    title: 'Sin horarios en esta fecha',
                    text: 'No hay franjas disponibles para la fecha y filtros seleccionados. Probá eligiendo un día resaltado en rosa.',
                    icon: 'info',
                    confirmButtonColor: '#d6858e'
                });
                return;
            }

            selectedHorarioId = null;
            btnGuardarHorario.disabled = true;
            modalFechaSubtitulo.textContent = formatearFechaLarga(fechaYmd);
            slotsGridContainer.innerHTML = '';

            slots.forEach(slot => {
                const card = document.createElement('button');
                card.type = 'button';
                card.className = 'slot-option-card';
                card.setAttribute('data-hid', slot.h_id);
                card.innerHTML = `
                    <span class="slot-option-card__prof">${slot.nombre_profesional}</span>
                    <span class="slot-option-card__role">${slot.titulo_profesional || 'Especialista'}</span>
                    <span class="slot-option-card__time">${slot.h_horaDesde} hs</span>
                `;

                card.addEventListener('click', () => {
                    slotsGridContainer.querySelectorAll('.slot-option-card').forEach(c => {
                        c.classList.remove('slot-option-card--selected');
                    });
                    card.classList.add('slot-option-card--selected');
                    selectedHorarioId = slot.h_id;
                    btnGuardarHorario.disabled = false;
                });

                slotsGridContainer.appendChild(card);
            });

            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        };

        // Botón "Guardar" en el modal → Lleva a la vista de pre-confirmación (CU04 Paso 3)
        btnGuardarHorario.addEventListener('click', () => {
            if (!selectedHorarioId) return;
            window.location.href = `${baseConfirmarUrl}/${selectedHorarioId}`;
        });

        // Renderizar columna de próximas fechas libres
        const renderQuickDates = () => {
            const fechas = Object.keys(porFechaMap).sort();
            if (!fechas.length) {
                quickDatesList.innerHTML = `
                    <div class="text-center py-4 px-2">
                        <span class="material-symbols-outlined fs-1 text-muted mb-2">event_busy</span>
                        <p class="fw-semibold text-dark mb-1">No encontramos horarios con esos filtros</p>
                        <p class="text-muted small mb-0">Probá seleccionando "Todas las profesionales" o limpiando los filtros de día y franja.</p>
                    </div>
                `;
                return;
            }

            quickDatesList.innerHTML = '';
            fechas.slice(0, 12).forEach(fecha => {
                const cant = porFechaMap[fecha].length;
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'quick-date-item';
                item.innerHTML = `
                    <div>
                        <div class="fw-bold text-dark text-capitalize" style="font-size: 0.9rem;">
                            ${formatearFechaLarga(fecha)}
                        </div>
                        <div class="text-muted" style="font-size: 0.78rem;">
                            Desde las ${porFechaMap[fecha][0].h_horaDesde} hs
                        </div>
                    </div>
                    <span class="srv-detail-pill srv-detail-pill--price py-1 px-2" style="font-size: 0.78rem;">
                        ${cant} ${cant === 1 ? 'turno' : 'turnos'}
                    </span>
                `;
                item.addEventListener('click', () => abrirModalFecha(fecha));
                quickDatesList.appendChild(item);
            });
        };

        // Inicializar FullCalendar v6
        const calendarEl = document.getElementById('clienteCalendario');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            firstDay: 1,
            height: 'auto',
            validRange: {
                start: '<?= date('Y-m-d') ?>'
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            buttonText: {
                today: 'Hoy'
            },
            dateClick: (info) => {
                if (porFechaMap[info.dateStr] && porFechaMap[info.dateStr].length > 0) {
                    abrirModalFecha(info.dateStr);
                }
            },
            eventClick: (info) => {
                const fecha = info.event.startStr.substring(0, 10);
                abrirModalFecha(fecha);
            }
        });

        calendar.render();

        // Consultar disponibilidad según los filtros activos
        const cargarDisponibilidad = async (abrirFechaSiCoincide = null) => {
            const params = new URLSearchParams({
                s_id: sId,
                p_id: filterProfesional.value || 0,
                dia_semana: filterDiaSemana.value || 0,
                franja: filterFranja.value || ''
            });

            try {
                const res = await fetch(`${apiDisponibilidadUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                porFechaMap = data.por_fecha || {};
                const total = data.total || 0;

                badgeTotalTurnos.innerHTML = `
                    <span class="material-symbols-outlined fs-6" style="color: var(--color-primary);">event_available</span>
                    ${total} ${total === 1 ? 'horario libre' : 'horarios libres'}
                `;

                // Construir eventos resumen por fecha para el calendario
                const eventos = Object.keys(porFechaMap).map(fecha => {
                    const cant = porFechaMap[fecha].length;
                    return {
                        id: 'disp-' + fecha,
                        title: `${cant} ${cant === 1 ? 'horario' : 'horarios'}`,
                        start: fecha,
                        allDay: true,
                        backgroundColor: '#d6858e',
                        borderColor: '#c5747d',
                        textColor: '#ffffff',
                        classNames: ['fc-avail-event']
                    };
                });

                calendar.removeAllEvents();
                calendar.addEventSource(eventos);

                // Resaltar celdas con disponibilidad
                document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
                    const f = cell.getAttribute('data-date');
                    if (f && porFechaMap[f] && porFechaMap[f].length > 0) {
                        cell.classList.add('fc-day-available');
                    } else {
                        cell.classList.remove('fc-day-available');
                    }
                });

                renderQuickDates();

                if (abrirFechaSiCoincide) {
                    calendar.gotoDate(abrirFechaSiCoincide);
                    abrirModalFecha(abrirFechaSiCoincide);
                }
            } catch (err) {
                console.error('Error al cargar disponibilidad:', err);
            }
        };

        // Listeners de filtros
        filterProfesional.addEventListener('change', () => cargarDisponibilidad());
        filterDiaSemana.addEventListener('change', () => cargarDisponibilidad());
        filterFranja.addEventListener('change', () => cargarDisponibilidad());

        filterFechaEspecifica.addEventListener('change', () => {
            const val = filterFechaEspecifica.value;
            if (val) {
                cargarDisponibilidad(val);
            }
        });

        btnResetFiltros.addEventListener('click', () => {
            filterProfesional.value = '0';
            filterDiaSemana.value = '0';
            filterFranja.value = '';
            filterFechaEspecifica.value = '';
            cargarDisponibilidad();
        });

        cargarDisponibilidad();
    });
    </script>
</body>

</html>

