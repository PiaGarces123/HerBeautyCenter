<?php
$s     = $servicio ?? [];
$profs = $profesionales ?? [];
$pSel  = (int) ($pIdPreseleccionado ?? 0);
$sId   = (int) ($s['s_id'] ?? 0);
?>
<?= view('cliente/_head', ['pageTitle' => 'Reservá tu Turno — ' . ($s['s_nbre'] ?? '')]) ?>

    <main class="cl-main">
        <div class="cl-wrap">

            <ol class="cl-stepper" aria-label="Progreso de la reserva">
                <li class="cl-step cl-step--done"><span class="cl-step__num">✓</span><span class="cl-step__label">Servicio</span></li>
                <li class="cl-step__line" aria-hidden="true"></li>
                <li class="cl-step cl-step--active" aria-current="step"><span class="cl-step__num">2</span><span class="cl-step__label">Fecha y horario</span></li>
                <li class="cl-step__line" aria-hidden="true"></li>
                <li class="cl-step"><span class="cl-step__num">3</span><span class="cl-step__label">Tus datos</span></li>
            </ol>

            <header class="cl-head cl-head--split cl-rise">
                <div>
                    <span class="cl-eyebrow"><?= esc($s['s_nbre'] ?? '') ?></span>
                    <h1 class="cl-title">Reservá tu <em>Turno</em></h1>
                    <div class="cl-pills">
                        <span class="cl-pill"><span class="material-symbols-outlined">schedule</span><?= (int) ($s['s_duracionMinutos'] ?? 60) ?> min</span>
                        <?php if (!empty($s['s_precio']) && $s['s_precio'] > 0): ?>
                            <span class="cl-pill cl-pill--rose"><span class="material-symbols-outlined">payments</span>Desde $<?= number_format((float) $s['s_precio'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= base_url('servicio/' . $sId) ?>" class="btn btn--outline cl-btn-sm">
                    <span class="material-symbols-outlined">arrow_back</span> Volver al servicio
                </a>
            </header>

            <div class="cl-guide cl-rise cl-rise--2">
                <span class="material-symbols-outlined">tips_and_updates</span>
                <ol>
                    <li>Si tenés preferencias, usá los <strong>filtros</strong> (especialista, día o franja horaria).</li>
                    <li>Tocá una <strong>fecha destacada</strong> en el calendario o en la lista de fechas libres.</li>
                    <li>Elegí la tarjeta con la profesional y el horario, y presioná <strong>Guardar</strong>.</li>
                </ol>
            </div>

            <!-- Filtros -->
            <div class="cl-filters cl-rise cl-rise--3">
                <div class="form-group">
                    <label for="filterProfesional" class="form-label">Especialista</label>
                    <select id="filterProfesional" class="form-select">
                        <option value="0">Todas las profesionales</option>
                        <?php foreach ($profs as $p): ?>
                            <option value="<?= (int) $p['p_id'] ?>" <?= $pSel === (int) $p['p_id'] ? 'selected' : '' ?>>
                                <?= esc($p['u_nbreCompleto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filterDiaSemana" class="form-label">Día</label>
                    <select id="filterDiaSemana" class="form-select">
                        <option value="0">Todos los días</option>
                        <option value="1">Lunes</option>
                        <option value="2">Martes</option>
                        <option value="3">Miércoles</option>
                        <option value="4">Jueves</option>
                        <option value="5">Viernes</option>
                        <option value="6">Sábado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filterFranja" class="form-label">Franja horaria</label>
                    <select id="filterFranja" class="form-select">
                        <option value="">Cualquier horario</option>
                        <option value="manana">Mañana (antes de 13 hs)</option>
                        <option value="tarde">Tarde (desde 13 hs)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filterFechaEspecifica" class="form-label">Fecha exacta</label>
                    <div class="cl-inline">
                        <input type="date" id="filterFechaEspecifica" class="form-input" min="<?= date('Y-m-d') ?>" />
                        <button type="button" id="btnResetFiltros" class="cl-icon-btn" title="Limpiar filtros" aria-label="Limpiar filtros">
                            <span class="material-symbols-outlined">restart_alt</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="cl-booking">
                <section class="cl-card cl-calendar" aria-labelledby="calTitle">
                    <div class="cl-booking__bar">
                        <h2 class="cl-subtitle m-0" id="calTitle">Elegí una fecha</h2>
                        <span id="badgeTotalTurnos" class="cl-pill" aria-live="polite">Cargando…</span>
                    </div>
                    <div id="clienteCalendario"></div>
                </section>

                <aside class="cl-card" aria-labelledby="datesTitle">
                    <h2 class="cl-subtitle" id="datesTitle">Próximas fechas libres</h2>
                    <p class="cl-muted mb-3">Tocá una fecha para ver los horarios.</p>
                    <div id="quickDatesList" class="cl-dates">
                        <p class="cl-empty m-0">Buscando horarios…</p>
                    </div>
                </aside>
            </div>

        </div>
    </main>

    <!-- Modal de horarios disponibles -->
    <div class="modal fade cl-modal" id="horariosDisponiblesModal" tabindex="-1" aria-labelledby="horariosDisponiblesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="cl-eyebrow"><?= esc($s['s_nbre'] ?? '') ?></span>
                        <h3 class="cl-subtitle m-0" id="horariosDisponiblesLabel">Horarios disponibles</h3>
                        <p class="cl-muted m-0 text-capitalize" id="modalFechaSubtitulo"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="slotsGridContainer" class="cl-slots" role="listbox" aria-label="Horarios"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn--outline cl-btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarHorario" class="btn btn--primary cl-btn-sm" disabled>
                        Guardar <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<?= view('cliente/_scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const API_URL = '<?= base_url('api/reservas/disponibilidad') ?>';
        const CONFIRM_URL = '<?= base_url('reservar/confirmar') ?>';
        const S_ID = <?= $sId ?>;
        const BRAND = window.APP_CONFIG.brand;

        const $ = (id) => document.getElementById(id);
        const fProf = $('filterProfesional'), fDia = $('filterDiaSemana'), fFranja = $('filterFranja'), fFecha = $('filterFechaEspecifica');
        const badge = $('badgeTotalTurnos'), quickList = $('quickDatesList');
        const modalEl = $('horariosDisponiblesModal'), slotsBox = $('slotsGridContainer'), btnGuardar = $('btnGuardarHorario');

        let porFecha = {};
        let selectedId = null;

        if (new URLSearchParams(location.search).get('ocupado') === '1') {
            Swal.fire({ title: 'Horario no disponible', text: 'Ese horario acaba de ocuparse. Elegí otro, por favor.', icon: 'info', confirmButtonColor: BRAND });
            history.replaceState({}, document.title, location.pathname);
        }

        const fechaLarga = (ymd) => {
            const [y, m, d] = ymd.split('-').map(Number);
            return new Date(y, m - 1, d).toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' });
        };

        // Crea un elemento con texto seguro (sin innerHTML con datos de la BD)
        const el = (tag, cls, text) => {
            const n = document.createElement(tag);
            if (cls) n.className = cls;
            if (text !== undefined) n.textContent = text;
            return n;
        };

        const abrirModal = (ymd) => {
            const slots = porFecha[ymd] || [];
            if (!slots.length) return;

            selectedId = null;
            btnGuardar.disabled = true;
            $('modalFechaSubtitulo').textContent = fechaLarga(ymd);
            slotsBox.replaceChildren();

            slots.forEach((slot, i) => {
                const card = el('button', 'cl-slot');
                card.type = 'button';
                card.setAttribute('role', 'option');
                card.style.animationDelay = (i * 0.04) + 's';
                card.append(
                    el('span', 'cl-slot__name', slot.nombre_profesional),
                    el('span', 'cl-slot__role', slot.titulo_profesional || 'Especialista'),
                    el('span', 'cl-slot__time', slot.h_horaDesde + ' hs')
                );
                card.addEventListener('click', () => {
                    slotsBox.querySelectorAll('.cl-slot--selected').forEach(c => c.classList.remove('cl-slot--selected'));
                    card.classList.add('cl-slot--selected');
                    selectedId = slot.h_id;
                    btnGuardar.disabled = false;
                });
                slotsBox.appendChild(card);
            });

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        };

        btnGuardar.addEventListener('click', () => {
            if (selectedId) location.href = `${CONFIRM_URL}/${selectedId}`;
        });

        const renderLista = () => {
            const fechas = Object.keys(porFecha).sort();
            quickList.replaceChildren();

            if (!fechas.length) {
                const empty = el('div', 'cl-empty');
                empty.append(
                    el('span', 'material-symbols-outlined', 'event_busy'),
                    el('p', 'fw-semibold mb-1', 'No hay horarios con esos filtros'),
                    el('p', 'cl-muted m-0', 'Probá con "Todas las profesionales" o limpiá los filtros.')
                );
                quickList.appendChild(empty);
                return;
            }

            fechas.slice(0, 12).forEach(f => {
                const cant = porFecha[f].length;
                const item = el('button', 'cl-date');
                item.type = 'button';
                const info = el('span');
                info.append(el('span', 'cl-date__day', fechaLarga(f)), el('span', 'cl-muted', 'Desde las ' + porFecha[f][0].h_horaDesde + ' hs'));
                item.append(info, el('span', 'cl-pill cl-pill--rose', cant + (cant === 1 ? ' turno' : ' turnos')));
                item.addEventListener('click', () => abrirModal(f));
                quickList.appendChild(item);
            });
        };

        const marcarDias = () => {
            document.querySelectorAll('#clienteCalendario .fc-daygrid-day').forEach(cell => {
                cell.classList.toggle('fc-day-available', !!porFecha[cell.dataset.date]);
            });
        };

        const calendar = new FullCalendar.Calendar($('clienteCalendario'), {
            locale: 'es',
            initialView: 'dayGridMonth',
            firstDay: 1,
            height: 'auto',
            fixedWeekCount: false,
            validRange: { start: '<?= date('Y-m-d') ?>' },
            headerToolbar: { left: 'prev,next', center: 'title', right: 'today' },
            buttonText: { today: 'Hoy' },
            eventClassNames: ['cl-fc-event'],
            dateClick: (info) => abrirModal(info.dateStr),
            eventClick: (info) => abrirModal(info.event.startStr.substring(0, 10)),
            datesSet: marcarDias
        });
        calendar.render();

        const cargar = async (fechaAbrir = null) => {
            const params = new URLSearchParams({ s_id: S_ID, p_id: fProf.value, dia_semana: fDia.value, franja: fFranja.value });
            try {
                const res = await fetch(`${API_URL}?${params}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                porFecha = data.por_fecha || {};
                const total = data.total || 0;
                badge.textContent = total + (total === 1 ? ' horario libre' : ' horarios libres');

                calendar.removeAllEvents();
                Object.keys(porFecha).forEach(f => {
                    const n = porFecha[f].length;
                    calendar.addEvent({ title: n + (n === 1 ? ' horario' : ' horarios'), start: f, allDay: true });
                });

                marcarDias();
                renderLista();

                if (fechaAbrir) {
                    calendar.gotoDate(fechaAbrir);
                    if (porFecha[fechaAbrir]) {
                        abrirModal(fechaAbrir);
                    } else {
                        Swal.fire({ title: 'Sin horarios ese día', text: 'Probá con otra fecha de las destacadas.', icon: 'info', confirmButtonColor: BRAND });
                    }
                }
            } catch (e) {
                badge.textContent = 'Error al cargar';
            }
        };

        [fProf, fDia, fFranja].forEach(f => f.addEventListener('change', () => cargar()));
        fFecha.addEventListener('change', () => fFecha.value && cargar(fFecha.value));
        $('btnResetFiltros').addEventListener('click', () => {
            fProf.value = '0'; fDia.value = '0'; fFranja.value = ''; fFecha.value = '';
            cargar();
        });

        cargar();
    });
    </script>
</body>

</html>
