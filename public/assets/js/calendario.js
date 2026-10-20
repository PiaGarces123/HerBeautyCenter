/**
 * calendario.js — Her Beauty Center
 * Incremento N° 2: CU07 (Gestionar Turnos) + CU08 (Gestionar Disponibilidad)
 *
 * Dependencias: FullCalendar v6, SweetAlert2
 * Ubicación: public/assets/js/calendario.js
 *
 * ARQUITECTURA:
 *   - HBC_CAL (global): URLs y contexto inyectados por la vista PHP
 *   - CalendarioApp: objeto principal con toda la lógica
 */

'use strict';

/* ============================================================
   OBJETO PRINCIPAL
   ============================================================ */
const CalendarioApp = (() => {

    /* ----------------------------------------------------------
       Estado interno
       ---------------------------------------------------------- */
    let calendar       = null;
    let modoEdicion    = false;   // true = editando disponibilidad existente
    let hIdActivo      = null;    // h_id del horario seleccionado (modal detalle)
    let tIdActivo      = null;    // t_id del turno seleccionado
    let tIdParaCancelar = null;   // t_id del turno a cancelar

    /* ----------------------------------------------------------
       Selectores DOM (cacheados al init)
       ---------------------------------------------------------- */
    let dom = {};

    /* ----------------------------------------------------------
       Colores por estado
       ---------------------------------------------------------- */
    const COLORES = {
        Disponible: '#7d562d',
        Solicitado: '#d97706',
        Confirmado: '#059669',
        Realizado:  '#6b7280',
        Cancelado:  '#dc2626',
    };

    /* ==========================================================
       1. INICIALIZACIÓN
       ========================================================== */
    function init() {
        dom = {
            calEl:              document.getElementById('herCalendar'),
            // Modal disponibilidad
            overlayDisp:        document.getElementById('modalDisponibilidadOverlay'),
            formDisp:           document.getElementById('formDisponibilidad'),
            inputHId:           document.getElementById('dispHId'),
            selServicio:        document.getElementById('dispServicio'),
            inputFecha:         document.getElementById('dispFecha'),
            inputDesde:         document.getElementById('dispDesde'),
            inputHasta:         document.getElementById('dispHasta'),
            btnGuardar:         document.getElementById('btnGuardarDisp'),
            btnGuardarText:     document.getElementById('btnGuardarDispText'),
            btnAgregar:         document.getElementById('btnAgregarDisponibilidad'),
            btnCerrarDisp:      document.getElementById('btnCerrarModalDisp'),
            btnCancelarDisp:    document.getElementById('btnCancelarDisp'),
            titleDispText:      document.getElementById('modalDispTitleText'),
            // Modal detalle
            overlayDet:         document.getElementById('modalDetalleOverlay'),
            detBody:            document.getElementById('modalDetBody'),
            detFooter:          document.getElementById('modalDetFooter'),
            detTitleText:       document.getElementById('modalDetTitleText'),
            detBadge:           document.getElementById('modalDetBadge'),
            detIcon:            document.getElementById('modalDetIcon'),
            btnCerrarDet:       document.getElementById('btnCerrarModalDet'),
            // Modal cancelación
            overlayCancel:      document.getElementById('modalCancelOverlay'),
            motivoInput:        document.getElementById('motivoCancelacion'),
            btnCerrarCancel:    document.getElementById('btnCerrarModalCancel'),
            btnCerrarCancelX:   document.getElementById('btnCerrarModalCancel'),
            btnConfirmarCancel: document.getElementById('btnConfirmarCancel'),
        };

        if (!dom.calEl) return; // Vista sin calendario

        initCalendar();
        bindEvents();
    }

    /* ==========================================================
       2. FULLCALENDAR
       ========================================================== */
    function initCalendar() {
        const isSmallScreen = window.innerWidth < 768;

        calendar = new FullCalendar.Calendar(dom.calEl, {
            locale:           'es',
            initialView:      isSmallScreen ? 'timeGridDay' : 'dayGridMonth',
            height:           'auto',
            firstDay:         1,       // Lunes primero
            nowIndicator:     true,
            allDaySlot:       false,   // Elimina la fila de 'all-day'
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6], // Lunes a Sábado horario de atención
                startTime: '08:00',
                endTime: '20:00',
            },
            slotMinTime:      '07:00:00',
            slotMaxTime:      '21:00:00',
            slotDuration:     '00:30:00',
            navLinks:         true,
            editable:         false,
            eventDisplay:     'block',

            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek,timeGridDay',
            },

            buttonText: {
                today:        'Hoy',
                month:        'Mes',
                week:         'Semana',
                day:          'Día',
            },

            // Carga de eventos via API y filtrado por checkboxes
            events: function (info, successCallback, failureCallback) {
                const start = info.startStr.slice(0, 10);
                const end   = info.endStr.slice(0, 10);
                const url   = `${window.HBC_CAL.apiEventos}?start=${start}&end=${end}`;

                fetch(url, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(data => {
                        const rawEvents = data.eventos || [];
                        const checked = Array.from(document.querySelectorAll('.cal-filter-checkbox:checked')).map(cb => cb.value);
                        const filtered = rawEvents.filter(ev => {
                            const est = ev.extendedProps?.estado ?? 'Disponible';
                            return checked.includes(est);
                        });
                        successCallback(filtered);
                    })
                    .catch(() => {
                        failureCallback();
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los eventos del calendario.' });
                    });
            },

            // Click en un evento
            eventClick: function (info) {
                const ev   = info.event;
                const props = ev.extendedProps;
                hIdActivo  = ev.extendedProps.h_id ?? null;
                tIdActivo  = ev.extendedProps.t_id ?? null;
                abrirModalDetalle(ev.title, props);
            },

            // Click en una fecha (crear disponibilidad)
            dateClick: function (info) {
                const fechaClick = info.dateStr.slice(0, 10);
                if (fechaClick >= window.HBC_CAL.hoy) {
                    abrirModalDisponibilidad(fechaClick);
                }
            },

            // Loading indicator
            loading: function (isLoading) {
                if (isLoading) {
                    dom.calEl.classList.add('cal-is-loading');
                } else {
                    dom.calEl.classList.remove('cal-is-loading');
                }
            },

            // Hook al cambiar de fecha o vista
            datesSet: function () {
                actualizarHeaderHora();
            }
        });

        calendar.render();
    }

    /* ----------------------------------------------------------
       Helpers: estética y formato del calendario
       ---------------------------------------------------------- */
    function actualizarHeaderHora() {
        if (!dom.calEl) return;
        // En vistas de semana y día, verificar celda de eje
        const axisHeaders = dom.calEl.querySelectorAll('.fc-col-header-cell.fc-timegrid-axis .fc-timegrid-axis-frame, .fc-col-header-cell.fc-timegrid-axis .fc-timegrid-axis-cushion');
        axisHeaders.forEach(el => {
            if (!el.textContent || el.textContent.trim() === '') {
                el.textContent = 'Hora';
            }
        });
    }

    /* ==========================================================
       3. EVENTOS DOM
       ========================================================== */
    function bindEvents() {
        // Filtro interactivo por checkboxes de estado en la leyenda
        document.querySelectorAll('.cal-filter-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                if (calendar) {
                    calendar.refetchEvents();
                }
            });
        });

        // Botón "Agregar Disponibilidad"
        if (dom.btnAgregar) {
            dom.btnAgregar.addEventListener('click', () => abrirModalDisponibilidad());
        }

        // Cerrar modal disponibilidad
        [dom.btnCerrarDisp, dom.btnCancelarDisp].forEach(btn => {
            if (btn) btn.addEventListener('click', cerrarModalDisponibilidad);
        });
        dom.overlayDisp?.addEventListener('click', (e) => {
            if (e.target === dom.overlayDisp) cerrarModalDisponibilidad();
        });

        // Guardar disponibilidad
        dom.btnGuardar?.addEventListener('click', guardarDisponibilidad);

        // Auto-calcular hora fin al seleccionar servicio / hora inicio
        dom.selServicio?.addEventListener('change', recalcularHoraFin);
        dom.inputDesde?.addEventListener('change', recalcularHoraFin);

        // Cerrar modal detalle
        dom.btnCerrarDet?.addEventListener('click', cerrarModalDetalle);
        dom.overlayDet?.addEventListener('click', (e) => {
            if (e.target === dom.overlayDet) cerrarModalDetalle();
        });

        // Cerrar modal cancelación
        dom.btnCerrarCancelX?.addEventListener('click', cerrarModalCancelar);
        document.getElementById('btnCerrarModalCancel')?.addEventListener('click', cerrarModalCancelar);
        dom.overlayCancel?.addEventListener('click', (e) => {
            if (e.target === dom.overlayCancel) cerrarModalCancelar();
        });

        // Confirmar cancelación
        dom.btnConfirmarCancel?.addEventListener('click', confirmarCancelacion);

        // ESC para cerrar modales
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                cerrarModalDisponibilidad();
                cerrarModalDetalle();
                cerrarModalCancelar();
            }
        });

        // -------------------------------------------------------
        // Modal Crear Turno (CU07)
        // -------------------------------------------------------
        const modalCrearEl = document.getElementById('modalCrearTurno');

        if (modalCrearEl) {
            modalCrearEl.addEventListener('show.bs.modal', () => {
                document.getElementById('formCrearTurno')?.reset();
                const radExist = document.getElementById('tipoClienteExistente');
                if (radExist) radExist.checked = true;
                const grpExist = document.getElementById('grupoClienteExistente');
                const grpNuevo = document.getElementById('grupoClienteNuevo');
                if (grpExist) grpExist.style.display = 'block';
                if (grpNuevo) grpNuevo.style.display = 'none';
                const fInput = document.getElementById('turnoFecha');
                if (fInput) fInput.value = new Date().toISOString().slice(0, 10);
            });
        }

        document.querySelectorAll('input[name="tipoClienteRadio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const esNuevo = e.target.value === 'nuevo';
                const grpExist = document.getElementById('grupoClienteExistente');
                const grpNuevo = document.getElementById('grupoClienteNuevo');
                if (grpExist) grpExist.style.display = esNuevo ? 'none' : 'block';
                if (grpNuevo) grpNuevo.style.display = esNuevo ? 'block' : 'none';
            });
        });

        const selSrvCrear = document.getElementById('turnoServicioId');
        const inputHoraDesdeCrear = document.getElementById('turnoHoraDesde');
        const inputHoraHastaCrear = document.getElementById('turnoHoraHasta');
        const inputMontoCrear = document.getElementById('turnoMonto');

        function recalcularTurnoFin() {
            const opt = selSrvCrear?.selectedOptions[0];
            const durMin = parseInt(opt?.dataset?.duracion ?? 0, 10);
            const precio = opt?.dataset?.precio;
            if (precio !== undefined && inputMontoCrear) {
                inputMontoCrear.value = precio;
            }
            const desde = inputHoraDesdeCrear?.value;
            if (!durMin || !desde || !inputHoraHastaCrear) return;
            const [h, m] = desde.split(':').map(Number);
            const total = h * 60 + m + durMin;
            const hh = String(Math.floor(total / 60) % 24).padStart(2, '0');
            const mm = String(total % 60).padStart(2, '0');
            inputHoraHastaCrear.value = `${hh}:${mm}`;
        }

        selSrvCrear?.addEventListener('change', recalcularTurnoFin);
        inputHoraDesdeCrear?.addEventListener('change', recalcularTurnoFin);

        document.getElementById('btnGuardarNuevoTurno')?.addEventListener('click', async () => {
            const esNuevoCliente = document.getElementById('tipoClienteNuevo')?.checked;
            const cId = document.getElementById('turnoClienteId')?.value;
            const cliNombre = document.getElementById('nuevoCliNombre')?.value.trim();
            const cliTel = document.getElementById('nuevoCliTel')?.value.trim();
            const cliEmail = document.getElementById('nuevoCliEmail')?.value.trim();
            const sId = selSrvCrear?.value;
            const fecha = document.getElementById('turnoFecha')?.value;
            const horaDesde = inputHoraDesdeCrear?.value;
            const horaHasta = inputHoraHastaCrear?.value;
            const monto = inputMontoCrear?.value;
            const estado = document.getElementById('turnoEstado')?.value || 'Confirmado';
            const abonado = document.getElementById('turnoAbonado')?.checked ? 1 : 0;

            if (esNuevoCliente) {
                if (!cliNombre || cliNombre.length < 3) {
                    Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Ingresá el nombre completo del cliente (mínimo 3 letras).' });
                    return;
                }
                if (!cliTel || cliTel.length < 6) {
                    Swal.fire({ icon: 'warning', title: 'Teléfono requerido', text: 'Ingresá el teléfono del cliente (mínimo 6 dígitos).' });
                    return;
                }
            } else {
                if (!cId) {
                    Swal.fire({ icon: 'warning', title: 'Cliente requerido', text: 'Seleccioná un cliente de la lista o elegí "+ Nuevo cliente".' });
                    return;
                }
            }

            if (!sId) {
                Swal.fire({ icon: 'warning', title: 'Servicio requerido', text: 'Seleccioná el servicio para el turno.' });
                return;
            }
            if (!fecha) {
                Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Seleccioná la fecha del turno.' });
                return;
            }
            if (!horaDesde || !horaHasta) {
                Swal.fire({ icon: 'warning', title: 'Horarios requeridos', text: 'Indicá la hora de inicio y de fin del turno.' });
                return;
            }
            if (horaHasta <= horaDesde) {
                Swal.fire({ icon: 'warning', title: 'Horario inválido', text: 'La hora de fin debe ser posterior a la de inicio.' });
                return;
            }

            const payload = {
                nuevo_cliente: esNuevoCliente,
                c_id: cId ? parseInt(cId, 10) : null,
                cliente_nombre: cliNombre,
                cliente_tel: cliTel,
                cliente_email: cliEmail,
                s_id: parseInt(sId, 10),
                fecha,
                hora_desde: horaDesde,
                hora_hasta: horaHasta,
                monto: monto ? parseFloat(monto) : null,
                estado,
                abonado,
            };

            const btnGuardar = document.getElementById('btnGuardarNuevoTurno');
            if (btnGuardar) btnGuardar.disabled = true;

            try {
                const resp = await fetch(window.HBC_CAL.apiCrearTurno, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });
                const data = await resp.json();

                if (resp.ok && data.success) {
                    Swal.fire({ icon: 'success', title: '¡Turno Creado!', text: data.message, timer: 1800, showConfirmButton: false })
                        .then(() => {
                            if (modalCrearEl && typeof bootstrap !== 'undefined') {
                                bootstrap.Modal.getInstance(modalCrearEl)?.hide();
                            }
                            calendar?.refetchEvents();
                        });
                } else {
                    const msg = data.messages?.error ?? data.message ?? 'No se pudo crear el turno.';
                    Swal.fire({ icon: 'error', title: 'Atención', text: msg });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
            } finally {
                if (btnGuardar) btnGuardar.disabled = false;
            }
        });
    }

    /* ==========================================================
       4. MODAL DISPONIBILIDAD (CU08)
       ========================================================== */
    function abrirModalDisponibilidad(fecha = null, horario = null) {
        modoEdicion = !!horario;

        if (dom.formDisp) dom.formDisp.reset();
        if (dom.inputHId) dom.inputHId.value = '';

        if (modoEdicion) {
            dom.titleDispText.textContent = 'Editar Disponibilidad';
            dom.btnGuardarText.textContent = 'Guardar Cambios';
            dom.inputHId.value         = horario.h_id;
            dom.selServicio.value      = horario.servicio_id;
            dom.inputFecha.value       = horario.fecha;
            dom.inputDesde.value       = horario.horaDesde?.slice(0, 5);
            dom.inputHasta.value       = horario.horaHasta?.slice(0, 5);
        } else {
            dom.titleDispText.textContent = 'Agregar Disponibilidad';
            dom.btnGuardarText.textContent = 'Guardar Disponibilidad';
            if (fecha) dom.inputFecha.value = fecha;
        }

        dom.overlayDisp?.classList.add('is-active');
        dom.overlayDisp?.setAttribute('aria-hidden', 'false');
        setTimeout(() => dom.selServicio?.focus(), 100);
    }

    function cerrarModalDisponibilidad() {
        dom.overlayDisp?.classList.remove('is-active');
        dom.overlayDisp?.setAttribute('aria-hidden', 'true');
        modoEdicion = false;
    }

    async function guardarDisponibilidad() {
        const hId   = dom.inputHId?.value;
        const sId   = dom.selServicio?.value;
        const fecha = dom.inputFecha?.value;
        const desde = dom.inputDesde?.value;
        const hasta = dom.inputHasta?.value;

        // Validación client-side
        if (!sId || !fecha || !desde || !hasta) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Completá todos los campos obligatorios.' });
            return;
        }
        if (hasta <= desde) {
            Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'La hora de fin debe ser posterior a la hora de inicio.' });
            return;
        }
        if (fecha < window.HBC_CAL.hoy) {
            Swal.fire({ icon: 'warning', title: 'Fecha inválida', text: 'No podés crear disponibilidad en fechas pasadas.' });
            return;
        }

        const body = JSON.stringify({ h_sId: sId, h_fecha: fecha, h_horaDesde: desde, h_horaHasta: hasta });

        try {
            dom.btnGuardar.disabled = true;
            dom.btnGuardarText.textContent = 'Guardando...';

            let url    = window.HBC_CAL.apiCrear;
            let method = 'POST';
            if (modoEdicion && hId) {
                url    = `${window.HBC_CAL.apiEditar}/${hId}`;
                method = 'PUT';
            }

            const resp = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body,
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                cerrarModalDisponibilidad();
                calendar.refetchEvents();
                Swal.fire({ icon: 'success', title: '¡Listo!', text: data.message, timer: 2500, showConfirmButton: false });
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al guardar.';
                Swal.fire({ icon: 'error', title: 'No se pudo guardar', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        } finally {
            dom.btnGuardar.disabled = false;
            dom.btnGuardarText.textContent = modoEdicion ? 'Guardar Cambios' : 'Guardar Disponibilidad';
        }
    }

    /* ----------------------------------------------------------
       Auto-calcular hora fin en base a duración del servicio
       ---------------------------------------------------------- */
    function recalcularHoraFin() {
        const opt    = dom.selServicio?.selectedOptions[0];
        const durMin = parseInt(opt?.dataset?.duracion ?? 0, 10);
        const desde  = dom.inputDesde?.value;

        if (!durMin || !desde) return;

        const [h, m]  = desde.split(':').map(Number);
        const totalMin = h * 60 + m + durMin;
        const hh = String(Math.floor(totalMin / 60) % 24).padStart(2, '0');
        const mm = String(totalMin % 60).padStart(2, '0');
        if (dom.inputHasta) dom.inputHasta.value = `${hh}:${mm}`;
    }

    /* ==========================================================
       5. MODAL DETALLE (CU07 / CU08)
       ========================================================== */
    function abrirModalDetalle(titulo, props) {
        const tipo   = props.tipo;
        const estado = props.estado ?? 'Disponible';

        // Actualizar encabezado
        dom.detTitleText.textContent = titulo;
        dom.detIcon.textContent = tipo === 'turno' ? 'event' : 'schedule';

        // Badge de estado
        dom.detBadge.textContent  = estado;
        dom.detBadge.setAttribute('data-estado', estado);

        // Construir body
        dom.detBody.innerHTML = renderDetalleBody(props);

        // Construir footer con acciones según tipo y estado
        dom.detFooter.innerHTML = renderDetalleFooter(tipo, estado, props);

        // Bindear botones del footer
        bindDetalleFooterEvents(tipo, estado, props);

        dom.overlayDet?.classList.add('is-active');
        dom.overlayDet?.setAttribute('aria-hidden', 'false');
    }

    function cerrarModalDetalle() {
        dom.overlayDet?.classList.remove('is-active');
        dom.overlayDet?.setAttribute('aria-hidden', 'true');
        hIdActivo = null;
        tIdActivo = null;
    }

    function renderDetalleBody(props) {
        const fmt = (val) => val ?? '<span class="cal-detalle-value--muted">—</span>';
        const fmtMonto = (m) => m ? `$${parseFloat(m).toLocaleString('es-AR')}` : '$0';

        let html = `<div class="cal-detalle-grid">`;

        html += `
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Servicio</span>
                <span class="cal-detalle-value">${esc(props.nombre_servicio)}</span>
            </div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Estado</span>
                <span class="cal-detalle-value">${esc(props.estado)}</span>
            </div>`;

        if (props.nombre_cliente) {
            html += `
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Cliente</span>
                <span class="cal-detalle-value">${esc(props.nombre_cliente)}</span>
            </div>`;
        }

        // Monto y abono (solo si hay turno)
        if (props.tipo === 'turno') {
            html += `
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Monto</span>
                <span class="cal-detalle-value">${fmtMonto(props.monto)}</span>
            </div>
            <div class="cal-detalle-item cal-detalle-item--full">
                <span class="cal-detalle-label">Pago</span>
                <div class="cal-pago-toggle" id="pagoToggleContainer">
                    <label class="cal-pago-toggle__label" for="pagoAbonado">
                        Marcar como abonado
                    </label>
                    <input type="checkbox" id="pagoAbonado" ${props.abonado ? 'checked' : ''}>
                </div>
                <input type="number" id="montoInput" class="cal-monto-input" 
                       value="${parseFloat(props.monto || 0).toFixed(2)}" min="0" step="0.01"
                       placeholder="Monto del servicio">
            </div>`;
        }

        html += `</div>`;
        return html;
    }

    function renderDetalleFooter(tipo, estado, props) {
        let btns = `<button class="btn-cal-secondary" id="detBtnCerrar">Cerrar</button>`;

        if (tipo === 'disponibilidad') {
            // Acciones para disponibilidad libre
            btns += `
                <button class="btn-cal-primary" id="detBtnEditar">
                    <span class="material-symbols-outlined">edit</span> Editar
                </button>
                <button class="btn-cal-danger" id="detBtnEliminar">
                    <span class="material-symbols-outlined">delete</span> Eliminar
                </button>`;
        } else {
            // Acciones para turno según estado
            if (estado === 'Solicitado') {
                btns += `
                    <button class="btn-cal-success" id="detBtnConfirmar">
                        <span class="material-symbols-outlined">check_circle</span> Confirmar Turno
                    </button>
                    <button class="btn-cal-danger" id="detBtnCancelar">
                        <span class="material-symbols-outlined">cancel</span> Cancelar
                    </button>`;
            }
            if (estado === 'Confirmado') {
                btns += `
                    <button class="btn-cal-success" id="detBtnRealizado">
                        <span class="material-symbols-outlined">task_alt</span> Marcar Realizado
                    </button>
                    <button class="btn-cal-gcal" id="detBtnGCal">
                        <span class="material-symbols-outlined">event_available</span> Google Calendar
                    </button>
                    <button class="btn-cal-danger" id="detBtnCancelar">
                        <span class="material-symbols-outlined">cancel</span> Cancelar
                    </button>`;
            }
            if (estado === 'Solicitado' || estado === 'Confirmado') {
                btns += `
                    <button class="btn-cal-primary" id="detBtnGuardarPago">
                        <span class="material-symbols-outlined">payments</span> Guardar Pago
                    </button>`;
            }
        }

        return btns;
    }

    function bindDetalleFooterEvents(tipo, estado, props) {
        document.getElementById('detBtnCerrar')?.addEventListener('click', cerrarModalDetalle);

        if (tipo === 'disponibilidad') {
            document.getElementById('detBtnEditar')?.addEventListener('click', () => {
                cerrarModalDetalle();
                abrirModalDisponibilidad(null, {
                    h_id:       hIdActivo,
                    servicio_id:props.servicio_id,
                    fecha:      props.fecha,
                    horaDesde:  props.horaDesde,
                    horaHasta:  props.horaHasta,
                });
            });
            document.getElementById('detBtnEliminar')?.addEventListener('click', () => {
                confirmarEliminarDisponibilidad(hIdActivo);
            });
        } else {
            // Acciones de turno
            document.getElementById('detBtnConfirmar')?.addEventListener('click', () => {
                cambiarEstadoTurno(tIdActivo, 'Confirmado');
            });
            document.getElementById('detBtnRealizado')?.addEventListener('click', () => {
                cambiarEstadoTurno(tIdActivo, 'Realizado');
            });
            document.getElementById('detBtnCancelar')?.addEventListener('click', () => {
                tIdParaCancelar = tIdActivo;
                cerrarModalDetalle();
                abrirModalCancelar();
            });
            document.getElementById('detBtnGCal')?.addEventListener('click', () => {
                exportarGoogleCalendar(tIdActivo);
            });
            document.getElementById('detBtnGuardarPago')?.addEventListener('click', () => {
                guardarPago(tIdActivo);
            });
        }
    }

    /* ==========================================================
       6. ACCIONES DE TURNO (CU07)
       ========================================================== */
    async function cambiarEstadoTurno(tId, estadoNuevo, motivo = null) {
        if (!tId) return;

        const body = { estado: estadoNuevo };
        if (motivo) body.motivo_cancelacion = motivo;

        try {
            const resp = await fetch(`${window.HBC_CAL.apiTurnoEstado}/${tId}`, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                cerrarModalDetalle();
                cerrarModalCancelar();
                calendar.refetchEvents();
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: data.message, timer: 2200, showConfirmButton: false });
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al actualizar.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        }
    }

    async function guardarPago(tId) {
        if (!tId) return;

        const abonado = document.getElementById('pagoAbonado')?.checked ? 1 : 0;
        const monto   = parseFloat(document.getElementById('montoInput')?.value ?? 0);

        if (isNaN(monto) || monto < 0) {
            Swal.fire({ icon: 'warning', title: 'Monto inválido', text: 'El monto debe ser un número positivo.' });
            return;
        }

        try {
            const resp = await fetch(`${window.HBC_CAL.apiTurnoPago}/${tId}`, {
                method:  'PUT',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ abonado, monto }),
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                cerrarModalDetalle();
                calendar.refetchEvents();
                Swal.fire({ icon: 'success', title: 'Pago actualizado', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al actualizar.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        }
    }

    async function exportarGoogleCalendar(tId) {
        if (!tId) return;

        try {
            const resp = await fetch(`${window.HBC_CAL.apiGoogleCal}/${tId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                // Abrir en nueva pestaña el enlace de Google Calendar
                window.open(data.google_link, '_blank', 'noopener,noreferrer');
                Swal.fire({
                    icon: 'success',
                    title: '¡Sincronizando!',
                    text: 'Se abrió Google Calendar con los datos del turno.',
                    timer: 3000,
                    showConfirmButton: false,
                });
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al exportar.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        }
    }

    /* ==========================================================
       7. ELIMINAR DISPONIBILIDAD
       ========================================================== */
    async function confirmarEliminarDisponibilidad(hId) {
        const confirm = await Swal.fire({
            icon:              'warning',
            title:             '¿Eliminar disponibilidad?',
            text:              'Esta acción no se puede deshacer.',
            showCancelButton:  true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText:  'No, cancelar',
            confirmButtonColor:'#dc2626',
        });

        if (!confirm.isConfirmed) return;

        try {
            const resp = await fetch(`${window.HBC_CAL.apiEliminar}/${hId}`, {
                method:  'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                cerrarModalDetalle();
                calendar.refetchEvents();
                Swal.fire({ icon: 'success', title: 'Eliminado', text: data.message, timer: 2000, showConfirmButton: false });
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al eliminar.';
                Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        }
    }

    /* ==========================================================
       8. MODAL CANCELACIÓN
       ========================================================== */
    function abrirModalCancelar() {
        if (dom.motivoInput) dom.motivoInput.value = '';
        dom.overlayCancel?.classList.add('is-active');
        dom.overlayCancel?.setAttribute('aria-hidden', 'false');
        setTimeout(() => dom.motivoInput?.focus(), 100);
    }

    function cerrarModalCancelar() {
        dom.overlayCancel?.classList.remove('is-active');
        dom.overlayCancel?.setAttribute('aria-hidden', 'true');
        tIdParaCancelar = null;
    }

    async function confirmarCancelacion() {
        const motivo = dom.motivoInput?.value?.trim();
        if (!motivo) {
            Swal.fire({ icon: 'warning', title: 'Motivo requerido', text: 'Ingresá el motivo de la cancelación.' });
            return;
        }
        await cambiarEstadoTurno(tIdParaCancelar, 'Cancelado', motivo);
    }

    /* ==========================================================
       9. UTILIDADES
       ========================================================== */
    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ==========================================================
       API PÚBLICA
       ========================================================== */
    return { init };

})();

/* ============================================================
   ARRANQUE
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => CalendarioApp.init());
