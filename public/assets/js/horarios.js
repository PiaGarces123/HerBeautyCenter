/**
 * horarios.js — Her Beauty Center
 * Incremento N° 2 (CU08 - Gestionar Disponibilidad)
 * Vista tabular: agregar / editar / eliminar disponibilidades
 *
 * Depende de: Bootstrap 5 (modals), SweetAlert2
 * Ubicación: public/assets/js/horarios.js
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.HBC_HOR;
    if (!cfg) return;

    let modoEdicion = false;
    let bsModal     = null;

    const modalEl       = document.getElementById('modalHorario');
    const formEl        = document.getElementById('formHorario');
    const selServicio   = document.getElementById('horServicio');
    const inputFecha    = document.getElementById('horFecha');
    const inputDesde    = document.getElementById('horDesde');
    const inputHasta    = document.getElementById('horHasta');
    const inputHId      = document.getElementById('horHId');
    const btnGuardar    = document.getElementById('btnGuardarHorario');
    const btnGuardarTxt = document.getElementById('btnGuardarHorarioText');
    const btnNuevo      = document.getElementById('btnNuevoHorario');
    const titleText     = document.getElementById('modalHorTitleText');

    if (modalEl) bsModal = new bootstrap.Modal(modalEl);

    // Cargar servicios de la profesional
    async function cargarServicios() {
        try {
            const resp = await fetch(cfg.apiServicios, { credentials: 'same-origin' });
            const data = await resp.json();
            const servicios = data.servicios ?? [];

            selServicio.innerHTML = '<option value="">— Seleccioná un servicio —</option>';
            servicios.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.s_id;
                opt.textContent = `${s.s_nbre} (${s.s_duracionMinutos} min — $${parseFloat(s.s_precio).toLocaleString('es-AR')})`;
                opt.dataset.duracion = s.s_duracionMinutos;
                selServicio.appendChild(opt);
            });
        } catch {
            // Servicio no accesible, se omite
        }
    }

    cargarServicios();

    // Auto-calcular hora fin
    function recalcularHoraFin() {
        const opt    = selServicio?.selectedOptions[0];
        const durMin = parseInt(opt?.dataset?.duracion ?? 0, 10);
        const desde  = inputDesde?.value;
        if (!durMin || !desde) return;
        const [h, m]  = desde.split(':').map(Number);
        const total   = h * 60 + m + durMin;
        const hh = String(Math.floor(total / 60) % 24).padStart(2, '0');
        const mm = String(total % 60).padStart(2, '0');
        if (inputHasta) inputHasta.value = `${hh}:${mm}`;
    }

    selServicio?.addEventListener('change', recalcularHoraFin);
    inputDesde?.addEventListener('change', recalcularHoraFin);

    // Abrir modal NUEVO
    btnNuevo?.addEventListener('click', () => {
        modoEdicion = false;
        formEl?.reset();
        if (inputHId) inputHId.value = '';
        if (titleText) titleText.textContent = 'Agregar Disponibilidad';
        if (btnGuardarTxt) btnGuardarTxt.textContent = 'Guardar';
        bsModal?.show();
    });

    // Abrir modal EDITAR
    document.querySelectorAll('.btn-editar-horario').forEach(btn => {
        btn.addEventListener('click', () => {
            modoEdicion = true;
            const { hid, sid, fecha, desde, hasta } = btn.dataset;
            if (inputHId)   inputHId.value   = hid;
            if (selServicio) selServicio.value = sid;
            if (inputFecha) inputFecha.value  = fecha;
            if (inputDesde) inputDesde.value  = desde;
            if (inputHasta) inputHasta.value  = hasta;
            if (titleText)  titleText.textContent = 'Editar Disponibilidad';
            if (btnGuardarTxt) btnGuardarTxt.textContent = 'Guardar Cambios';
            bsModal?.show();
        });
    });

    // Guardar (crear o editar)
    btnGuardar?.addEventListener('click', async () => {
        const hId   = inputHId?.value;
        const sId   = selServicio?.value;
        const fecha = inputFecha?.value;
        const desde = inputDesde?.value;
        const hasta = inputHasta?.value;

        if (!sId || !fecha || !desde || !hasta) {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Completá todos los campos obligatorios.' });
            return;
        }
        if (hasta <= desde) {
            Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'La hora de fin debe ser posterior a la de inicio.' });
            return;
        }
        if (fecha < cfg.hoy) {
            Swal.fire({ icon: 'warning', title: 'Fecha inválida', text: 'No podés crear disponibilidad en fechas pasadas.' });
            return;
        }

        const body   = JSON.stringify({ h_sId: sId, h_fecha: fecha, h_horaDesde: desde, h_horaHasta: hasta });
        let url      = cfg.apiCrear;
        let method   = 'POST';
        if (modoEdicion && hId) { url = `${cfg.apiEditar}/${hId}`; method = 'PUT'; }

        btnGuardar.disabled = true;
        if (btnGuardarTxt) btnGuardarTxt.textContent = 'Guardando...';

        try {
            const resp = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body,
            });
            const data = await resp.json();

            if (resp.ok && data.success) {
                bsModal?.hide();
                Swal.fire({
                    icon: 'success', title: '¡Listo!', text: data.message,
                    timer: 2000, showConfirmButton: false
                }).then(() => location.reload());
            } else {
                const msg = data.messages?.error ?? data.message ?? 'Error al guardar.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
        } finally {
            btnGuardar.disabled = false;
            if (btnGuardarTxt) btnGuardarTxt.textContent = modoEdicion ? 'Guardar Cambios' : 'Guardar';
        }
    });

    // Eliminar horario
    document.querySelectorAll('.btn-eliminar-horario').forEach(btn => {
        btn.addEventListener('click', async () => {
            const hId = btn.dataset.hid;
            const confirm = await Swal.fire({
                icon: 'warning',
                title: '¿Eliminar disponibilidad?',
                text: 'Esta acción no se puede deshacer.',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            });
            if (!confirm.isConfirmed) return;

            try {
                const resp = await fetch(`${cfg.apiEliminar}/${hId}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const data = await resp.json();

                if (resp.ok && data.success) {
                    Swal.fire({ icon: 'success', title: 'Eliminado', text: data.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    const msg = data.messages?.error ?? data.message ?? 'Error.';
                    Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: msg });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.' });
            }
        });
    });
});
