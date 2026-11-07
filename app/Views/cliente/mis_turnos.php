<?php
$prox   = $proximoTurno ?? null;
$lista  = $turnos ?? [];
$cnt    = $contadores ?? [];
$fEst   = $filtroEstado ?? '';
$fPer   = $filtroPeriodo ?? 'todos';
$hoy    = date('Y-m-d');
$meses  = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
$defaultAvatar = base_url('public/assets/media/íconoPerfil.png');
$nombre = explode(' ', (string) session()->get('usuario_nombre'))[0] ?: 'Clienta';
?>
<?= view('cliente/_head', ['pageTitle' => 'Mis Turnos']) ?>

    <main class="cl-main">
        <div class="cl-wrap">

            <nav class="cl-tabs" aria-label="Panel del cliente">
                <a href="<?= base_url('mis-turnos') ?>" class="cl-tab cl-tab--active" aria-current="page">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Turnos
                </a>
                <a href="<?= base_url('mi-perfil') ?>" class="cl-tab">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="<?= base_url('/#servicios') ?>" class="cl-tab cl-tab--end">
                    <span class="material-symbols-outlined">add</span> Nuevo turno
                </a>
            </nav>

            <header class="cl-head cl-rise">
                <span class="cl-eyebrow">Mi panel</span>
                <h1 class="cl-title">Hola, <em><?= esc($nombre) ?></em></h1>
                <p class="cl-lead">Consultá el estado de tus reservas, agendalas en tu calendario o gestioná tus citas.</p>
            </header>

            <!-- «Mi próximo turno» -->
            <section class="cl-card cl-card--rose cl-next cl-rise cl-rise--2" aria-labelledby="proxTitle">
                <div class="cl-next__top">
                    <span class="cl-eyebrow m-0" id="proxTitle">Mi próximo turno</span>
                    <?php if ($prox): ?>
                        <span class="cl-status cl-status--<?= esc($prox['estado']) ?>"><?= esc($prox['estado']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($prox):
                    $ts = strtotime($prox['fecha']);
                    $pDesde = substr($prox['h_horaDesde'], 0, 5);
                    $pHasta = substr($prox['h_horaHasta'], 0, 5);
                    $pFecha = date('d/m/Y', $ts);
                    $pAvatar = !empty($prox['avatar_profesional']) ? base_url($prox['avatar_profesional']) : $defaultAvatar;
                ?>
                    <div class="cl-next__grid">
                        <div>
                            <h2 class="cl-next__service"><?= esc($prox['nombre_servicio'] ?? 'Servicio') ?></h2>
                            <div class="cl-person">
                                <img src="<?= esc($pAvatar) ?>" alt="" class="cl-avatar" />
                                <div>
                                    <p class="cl-person__name"><?= esc($prox['nombre_profesional'] ?? '') ?></p>
                                    <span class="cl-muted"><?= esc($prox['titulo_profesional'] ?? 'Especialista') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="cl-datebox">
                            <div class="cl-datebox__day">
                                <span class="cl-datebox__num"><?= date('d', $ts) ?></span>
                                <span class="cl-datebox__month"><?= $meses[(int) date('n', $ts)] ?></span>
                            </div>
                            <div>
                                <strong class="d-block"><?= esc($pFecha) ?></strong>
                                <span class="cl-muted"><?= esc($pDesde) ?> a <?= esc($pHasta) ?> hs</span>
                            </div>
                        </div>

                        <div class="cl-next__actions">
                            <button type="button" class="btn btn--primary cl-btn-sm btn-gcal-client" data-tid="<?= (int) $prox['t_id'] ?>">
                                <span class="material-symbols-outlined">event</span> Google Calendar
                            </button>
                            <button type="button" class="btn cl-btn-danger cl-btn-sm btn-cancelar-turno"
                                data-tid="<?= (int) $prox['t_id'] ?>"
                                data-servicio="<?= esc($prox['nombre_servicio'] ?? '') ?>"
                                data-fecha="<?= esc($pFecha) ?>" data-hora="<?= esc($pDesde) ?>">
                                <span class="material-symbols-outlined">close</span> Cancelar turno
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cl-next__empty">
                        <div>
                            <h2 class="cl-subtitle">No tenés próximos turnos</h2>
                            <p class="cl-lead">¿Te regalás un momento de bienestar? Elegí un servicio y el horario que mejor te quede.</p>
                        </div>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--primary">
                            <span class="material-symbols-outlined">spa</span> Solicitar turno
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Listado -->
            <section aria-labelledby="listaTitle">
                <div class="cl-head cl-head--split">
                    <div>
                        <h2 class="cl-subtitle" id="listaTitle">Mis turnos</h2>
                        <p class="cl-muted m-0">Filtrá por período o estado.</p>
                    </div>
                    <form method="get" action="<?= base_url('mis-turnos') ?>" class="cl-filterbar">
                        <label class="visually-hidden" for="fPeriodo">Período</label>
                        <select id="fPeriodo" name="periodo" class="form-select" onchange="this.form.submit()">
                            <option value="todos" <?= $fPer === 'todos' ? 'selected' : '' ?>>Todos (<?= (int) ($cnt['todos'] ?? 0) ?>)</option>
                            <option value="proximos" <?= $fPer === 'proximos' ? 'selected' : '' ?>>Próximos (<?= (int) ($cnt['proximos'] ?? 0) ?>)</option>
                            <option value="historial" <?= $fPer === 'historial' ? 'selected' : '' ?>>Historial (<?= (int) ($cnt['historial'] ?? 0) ?>)</option>
                        </select>
                        <label class="visually-hidden" for="fEstado">Estado</label>
                        <select id="fEstado" name="estado" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <?php foreach (['Solicitado', 'Confirmado', 'Realizado', 'Cancelado'] as $e): ?>
                                <option value="<?= $e ?>" <?= $fEst === $e ? 'selected' : '' ?>><?= $e ?> (<?= (int) ($cnt[$e] ?? 0) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn--outline cl-btn-sm">Filtrar</button></noscript>
                    </form>
                </div>

                <?php if (!empty($lista)): ?>
                    <div class="cl-turnos">
                        <?php foreach ($lista as $i => $t):
                            $est = $t['estado'] ?? 'Solicitado';
                            $tFecha = date('d/m/Y', strtotime($t['fecha']));
                            $tDesde = substr($t['h_horaDesde'], 0, 5);
                            $activo = in_array($est, ['Solicitado', 'Confirmado'], true) && $t['fecha'] >= $hoy;
                        ?>
                            <article class="cl-turno cl-turno--<?= esc($est) ?> cl-rise" style="animation-delay: <?= min($i, 8) * 0.05 ?>s">
                                <div class="cl-turno__head">
                                    <div>
                                        <h3 class="cl-turno__title"><?= esc($t['nombre_servicio'] ?? 'Servicio') ?></h3>
                                        <span class="cl-muted">Con <?= esc($t['nombre_profesional'] ?? '—') ?></span>
                                    </div>
                                    <span class="cl-status cl-status--<?= esc($est) ?>"><?= esc($est) ?></span>
                                </div>

                                <div class="cl-turno__meta">
                                    <span><span class="material-symbols-outlined">calendar_today</span><?= esc($tFecha) ?></span>
                                    <span><span class="material-symbols-outlined">schedule</span><?= esc($tDesde) ?> a <?= esc(substr($t['h_horaHasta'], 0, 5)) ?> hs</span>
                                    <?php if (!empty($t['monto']) && $t['monto'] > 0): ?>
                                        <span><span class="material-symbols-outlined">payments</span>$<?= number_format((float) $t['monto'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($t['motivo_cancelacion'])): ?>
                                    <p class="cl-turno__note"><?= esc($t['motivo_cancelacion']) ?></p>
                                <?php endif; ?>

                                <div class="cl-turno__foot">
                                    <?php if ($activo): ?>
                                        <button type="button" class="btn btn--outline cl-btn-sm btn-gcal-client" data-tid="<?= (int) $t['t_id'] ?>">
                                            <span class="material-symbols-outlined">event</span> Calendar
                                        </button>
                                        <button type="button" class="btn cl-btn-danger cl-btn-sm btn-cancelar-turno"
                                            data-tid="<?= (int) $t['t_id'] ?>"
                                            data-servicio="<?= esc($t['nombre_servicio'] ?? '') ?>"
                                            data-fecha="<?= esc($tFecha) ?>" data-hora="<?= esc($tDesde) ?>">
                                            Cancelar
                                        </button>
                                    <?php else: ?>
                                        <span class="cl-muted">Turno #<?= (int) $t['t_id'] ?></span>
                                        <?php if (!empty($t['servicio_id'])): ?>
                                            <a href="<?= base_url('servicio/' . (int) $t['servicio_id']) ?>" class="btn btn--outline cl-btn-sm">Volver a reservar</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="cl-card cl-empty">
                        <span class="material-symbols-outlined">event_note</span>
                        <p class="cl-subtitle">Sin turnos para este filtro</p>
                        <p class="cl-muted">Cambiá los criterios o reservá un nuevo turno.</p>
                        <a href="<?= base_url('/#servicios') ?>" class="btn btn--primary cl-btn-sm">Ver servicios</a>
                    </div>
                <?php endif; ?>
            </section>

        </div>
    </main>

<?= view('cliente/_scripts') ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const BRAND = window.APP_CONFIG.brand;
        const GCAL_URL = '<?= base_url('api/cliente/turnos/google-calendar') ?>';
        const CANCEL_URL = '<?= base_url('api/cliente/turnos/cancelar') ?>';
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
        const fail = (msg) => Swal.fire({ title: 'Atención', text: msg, icon: 'error', confirmButtonColor: BRAND });

        // Delegación de eventos: un solo listener para todas las acciones
        document.addEventListener('click', async (e) => {
            const gcal = e.target.closest('.btn-gcal-client');
            const cancel = e.target.closest('.btn-cancelar-turno');

            if (gcal) {
                // Se abre la pestaña antes del fetch para evitar el bloqueo de pop-ups
                const win = window.open('', '_blank');
                try {
                    const res = await fetch(`${GCAL_URL}/${gcal.dataset.tid}`, { method: 'POST', headers });
                    const data = await res.json();
                    if (res.ok && data.google_sync_url) {
                        win.opener = null;
                        win.location.href = data.google_sync_url;
                    } else {
                        win.close();
                        fail(data.messages?.error || 'No se pudo generar el enlace.');
                    }
                } catch (err) {
                    win.close();
                    fail('Error de conexión.');
                }
            }

            if (cancel) {
                const { tid, servicio, fecha, hora } = cancel.dataset;
                const { isConfirmed, value } = await Swal.fire({
                    title: '¿Cancelar tu turno?',
                    text: `${servicio} — ${fecha} a las ${hora} hs.`,
                    input: 'text',
                    inputPlaceholder: 'Motivo (opcional)',
                    inputAttributes: { maxlength: 200 },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: BRAND,
                    cancelButtonColor: '#82756a',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Mantener',
                    reverseButtons: true
                });
                if (!isConfirmed) return;

                try {
                    const res = await fetch(`${CANCEL_URL}/${tid}`, { method: 'PUT', headers, body: JSON.stringify({ motivo_cancelacion: (value || '').trim() }) });
                    const data = await res.json();
                    if (res.ok) {
                        await Swal.fire({ title: 'Turno cancelado', text: data.message, icon: 'success', confirmButtonColor: BRAND });
                        location.reload();
                    } else {
                        fail(data.messages?.error || 'No se pudo cancelar el turno.');
                    }
                } catch (err) {
                    fail('Error de conexión.');
                }
            }
        });
    });
    </script>
</body>

</html>
