/**
 * turnos.js — Her Beauty Center
 * Incremento N° 2 (CU07 - Gestionar Turnos)
 * Vista tabular: confirmar / cancelar / marcar realizado / Google Calendar
 *
 * Depende de: Bootstrap 5 (modals), SweetAlert2
 * Ubicación: public/assets/js/turnos.js
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.HBC_TUR;
    if (!cfg) return;

    let tIdActivo      = null;
    let tIdParaCancel  = null;

    const modalDetalleEl = document.getElementById('modalTurnoDetalle');
    const modalCancelEl  = document.getElementById('modalCancelarTurno');
    let bsModalDetalle   = modalDetalleEl ? new bootstrap.Modal(modalDetalleEl) : null;
    let bsModalCancel    = modalCancelEl  ? new bootstrap.Modal(modalCancelEl)  : null;

    const estadoBadgeClass = {
        'Disponible': 'badge--blue',
        'Solicitado': 'badge--orange',
        'Confirmado': 'badge--green',
        'Realizado':  'badge--gray',
        'Cancelado':  'badge--rose',
    };

    // -------------------------------------------------------
    // Abrir modal detalle
    // -------------------------------------------------------
    document.querySelectorAll('.btn-detalle-turno').forEach(btn => {
        btn.addEventListener('click', () => abrirDetalle(btn.dataset.tid));
    });

    async function abrirDetalle(tId) {
        tIdActivo = tId;
        const body    = document.getElementById('modalTurnoBody');
        const footer  = document.getElementById('modalTurnoFooter');
        const badgeEl = document.getElementById('modalTurnoBadge');

        body.innerHTML   = '<div class="cal-loading"><div class="cal-spinner"></div>Cargando...</div>';
        footer.innerHTML = '';
        bsModalDetalle?.show();

        try {
            const resp = await fetch(`${cfg.apiDetalle}/${tId}`, { credentials: 'same-origin' });
            const data = await resp.json();

            if (!resp.ok || !data.turno) {
                body.innerHTML = '<p class="text-danger p-3">No se pudo cargar el turno.</p>';
                return;
            }
            const t = data.turno;
            const estado = t.estado;

            // Badge
            badgeEl.textContent = estado;
            badgeEl.className   = `badge ${estadoBadgeClass[estado] ?? ''}`;

            // Body
            body.innerHTML = renderDetalleBody(t);

            // Footer
            footer.innerHTML = renderDetalleFooter(t);
            bindDetalleEvents(t);

        } catch {
            body.innerHTML = '<p class="text-danger p-3">Error de red al cargar el turno.</p>';
        }
    }

    function renderDetalleBody(t) {
        const fmtMonto = m => `$${parseFloat(m || 0).toLocaleString('es-AR')}`;
        const esc = s => s ? String(s).replace(/</g,'&lt;').replace(/>/g,'&gt;') : '—';

        return `
        <div class="cal-detalle-grid">
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Fecha</span>
                <span class="cal-detalle-value">${new Date(t.fecha + 'T00:00:00').toLocaleDateString('es-AR', {weekday:'long',day:'2-digit',month:'long',year:'numeric'})}</span>
            </div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Horario</span>
                <span class="cal-detalle-value">${t.h_horaDesde?.slice(0,5)} – ${t.h_horaHasta?.slice(0,5)}</span>
            </div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Servicio</span>
                <span class="cal-detalle-value">${esc(t.nombre_servicio)}</span>
            </div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Cliente</span>
                <span class="cal-detalle-value">${esc(t.nombre_cliente)}</span>
            </div>
            ${t.correo_cliente ? `
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Email cliente</span>
                <span class="cal-detalle-value">${esc(t.correo_cliente)}</span>
            </div>` : ''}
            ${t.tel_cliente ? `
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Tel. cliente</span>
                <span class="cal-detalle-value">${esc(t.tel_cliente)}</span>
            </div>` : ''}
            <div class="cal-detalle-divider"></div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Precio base</span>
                <span class="cal-detalle-value">${fmtMonto(t.precio_servicio)}</span>
            </div>
            <div class="cal-detalle-item">
                <span class="cal-detalle-label">Estado de pago</span>
                <span class="cal-detalle-value">${t.abonado == 1 ? '<span class="badge badge--green">Abonado</span>' : '<span class="badge badge--rose">Pendiente</span>'}</span>
            </div>
            <div class="cal-detalle-item cal-detalle-item--full">
                <span class="cal-detalle-label">Gestión de pago</span>
                <div class="cal-pago-toggle">
                    <label class="cal-pago-toggle__label" for="detPagoCheck">Marcar como abonado</label>
                    <input type="checkbox" id="detPagoCheck" ${t.abonado == 1 ? 'checked' : ''}>
                </div>
                <input type="number" id="detMontoInput" class="cal-monto-input"
                       value="${parseFloat(t.monto || 0).toFixed(2)}" min="0" step="0.01"
                       placeholder="Monto cobrado">
            </div>
            ${t.motivo_cancelacion ? `
            <div class="cal-detalle-item cal-detalle-item--full">
                <span class="cal-detalle-label">Motivo cancelación</span>
                <span class="cal-detalle-value cal-detalle-value--muted">${esc(t.motivo_cancelacion)}</span>
            </div>` : ''}
        </div>`;
    }

    function renderDetalleFooter(t) {
        let btns = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;

        if (t.estado === 'Solicitado' || t.estado === 'Confirmado') {
            btns += `<button type="button" class="btn btn--primary" id="detBtnGuardarPago">
                <span class="material-symbols-outlined" style="font-size:1rem">payments</span> Guardar Pago
            </button>`;
        }
        if (t.estado === 'Solicitado') {
            btns += `<button type="button" class="btn btn-success" id="detBtnConfirmar">
                <span class="material-symbols-outlined" style="font-size:1rem">check_circle</span> Confirmar
            </button>`;
        }
        if (t.estado === 'Confirmado') {
            btns += `
            <button type="button" class="btn btn-success" id="detBtnRealizado">
                <span class="material-symbols-outlined" style="font-size:1rem">task_alt</span> Marcar Realizado
            </button>
            <button type="button" class="btn" style="background:#4285F4;color:#fff" id="detBtnGCal">
                <span class="material-symbols-outlined" style="font-size:1rem">event_available</span> Google Cal
            </button>`;
        }
        if (t.estado === 'Solicitado' || t.estado === 'Confirmado') {
            btns += `<button type="button" class="btn btn-danger" id="detBtnCancelar">
                <span class="material-symbols-outlined" style="font-size:1rem">cancel</span> Cancelar
            </button>`;
        }
        return btns;
    }

    function bindDetalleEvents(t) {
        document.getElementById('detBtnGuardarPago')?.addEventListener('click', () => guardarPago(t.t_id));
        document.getElementById('detBtnConfirmar')?.addEventListener('click',  () => cambiarEstado(t.t_id, 'Confirmado'));
        document.getElementById('detBtnRealizado')?.addEventListener('click',  () => cambiarEstado(t.t_id, 'Realizado'));
        document.getElementById('detBtnGCal')?.addEventListener('click',       () => exportarGCal(t.t_id));
        document.getElementById('detBtnCancelar')?.addEventListener('click',   () => {
            tIdParaCancel = t.t_id;
            bsModalDetalle?.hide();
            bsModalCancel?.show();
        });
    }

    // -------------------------------------------------------
    // Acciones inline desde la tabla
    // -------------------------------------------------------
    document.querySelectorAll('.btn-confirmar-turno').forEach(btn => {
        btn.addEventListener('click', () => cambiarEstado(btn.dataset.tid, 'Confirmado'));
    });

    document.querySelectorAll('.btn-cancelar-turno').forEach(btn => {
        btn.addEventListener('click', () => {
            tIdParaCancel = btn.dataset.tid;
            bsModalCancel?.show();
        });
    });

    document.querySelectorAll('.btn-gcal-turno').forEach(btn => {
        btn.addEventListener('click', () => exportarGCal(btn.dataset.tid));
    });

    // -------------------------------------------------------
    // Confirmar cancelación
    // -------------------------------------------------------
    document.getElementById('btnConfirmarCancelTurno')?.addEventListener('click', async () => {
        const motivo = document.getElementById('motivoCancelTurno')?.value?.trim();
        if (!motivo) {
            Swal.fire({ icon: 'warning', title: 'Motivo requerido', text: 'Ingresá el motivo de la cancelación.' });
            return;
        }
        await cambiarEstado(tIdParaCancel, 'Cancelado', motivo);
        bsModalCancel?.hide();
    });

    // -------------------------------------------------------
    // Funciones auxiliares
    // -------------------------------------------------------
    async function cambiarEstado(tId, estadoNuevo, motivo = null) {
        const body = { estado: estadoNuevo };
        if (motivo) body.motivo_cancelacion = motivo;

        try {
            const resp = await fetch(`${cfg.apiEstado}/${tId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                Swal.fire({ icon: 'success', title: '¡Actualizado!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        }
    }

    async function guardarPago(tId) {
        const abonado = document.getElementById('detPagoCheck')?.checked ? 1 : 0;
        const monto   = parseFloat(document.getElementById('detMontoInput')?.value ?? 0);
        if (isNaN(monto) || monto < 0) {
            Swal.fire({ icon: 'warning', title: 'Monto inválido', text: 'El monto debe ser positivo.' }); return;
        }
        try {
            const resp = await fetch(`${cfg.apiPago}/${tId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ abonado, monto }),
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                Swal.fire({ icon: 'success', title: 'Pago actualizado', text: data.message, timer: 1800, showConfirmButton: false })
                    .then(() => { bsModalDetalle?.hide(); location.reload(); });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.messages?.error ?? data.message ?? 'Error.' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar.' });
        }
    }

    async function exportarGCal(tId) {
        try {
            const resp = await fetch(`${cfg.apiGoogleCal}/${tId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                window.open(data.google_link, '_blank', 'noopener,noreferrer');
                Swal.fire({ icon: 'success', title: 'Google Calendar', text: 'Se abrió el enlace de Google Calendar.', timer: 2500, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.messages?.error ?? data.message ?? 'Error.' });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar.' });
        }
    }
});
