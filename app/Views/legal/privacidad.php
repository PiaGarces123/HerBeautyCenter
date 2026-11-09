<!DOCTYPE html>
<html class="scroll-smooth" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="description"
        content="Conocé las pautas que rigen nuestros servicios de estética y bienestar, así como nuestro compromiso estricto de protección de tus datos personales conforme a la legislación de la República Argentina." />
    <title><?= esc($pageTitle ?? 'Términos y Privacidad') ?> | Her Beauty Center</title>

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

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/styles.css?v=' . time()) ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/index-page.css?v=' . time()) ?>" />

    <style>
        .legal-page {
            padding: calc(var(--header-height, 4.5rem) + 2rem) 1.25rem 4rem;
            max-width: 58rem;
            margin: 0 auto;
        }

        .legal-header {
            text-align: center;
            margin-bottom: 2.75rem;
        }

        .legal-title {
            font-family: var(--font-serif, 'EB Garamond', serif);
            font-size: clamp(2.2rem, 4.5vw, 3.25rem);
            color: var(--color-primary, #4a2c1d);
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .legal-subtitle {
            font-size: 1.05rem;
            color: var(--color-on-surface-variant, #6c584c);
            max-width: 44rem;
            margin: 0 auto;
            line-height: 1.65;
        }

        .legal-actions-wrap {
            margin-bottom: 2.75rem;
            display: flex;
            justify-content: center;
        }

        .legal-btn-group {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            justify-content: center;
            padding: 0;
            margin: 0;
            list-style: none;
            border: none;
        }

        .legal-btn-group .nav-item {
            display: flex;
        }

        .legal-tab-btn {
            background: #ffffff;
            border: 1.5px solid var(--color-outline-variant, #e6ccb2);
            border-radius: 0.85rem;
            padding: 0.9rem 1.85rem;
            color: var(--color-primary, #4a2c1d);
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            box-shadow: 0 2px 8px rgba(74, 44, 29, 0.04);
            transition: all 0.25s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .legal-tab-btn .material-symbols-outlined {
            color: var(--color-secondary, #9c6644);
            font-size: 1.35rem;
            transition: color 0.25s ease;
        }

        .legal-tab-btn:hover {
            border-color: var(--color-secondary, #9c6644);
            color: var(--color-primary, #4a2c1d);
            background: var(--color-surface-container, #fdfaf7);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(74, 44, 29, 0.08);
        }

        .legal-tab-btn.active {
            background: var(--color-primary, #4a2c1d) !important;
            border-color: var(--color-primary, #4a2c1d) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 20px rgba(74, 44, 29, 0.25);
            transform: translateY(-2px);
        }

        .legal-tab-btn.active .material-symbols-outlined {
            color: #ffffff !important;
        }

        .legal-card {
            background: #ffffff;
            border-radius: 1.5rem;
            border: 1px solid var(--color-outline-variant, #e6ccb2);
            box-shadow: 0 4px 20px rgba(74, 44, 29, 0.05);
            padding: 2.25rem 2rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .legal-card:hover {
            box-shadow: 0 8px 24px rgba(74, 44, 29, 0.09);
        }

        .legal-card__header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .legal-card__num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 50%;
            background: var(--color-bento-peach, #faedcd);
            color: var(--color-primary, #4a2c1d);
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
            border: 1px solid rgba(74, 44, 29, 0.15);
        }

        .legal-card__title {
            font-family: var(--font-serif, 'EB Garamond', serif);
            font-size: 1.4rem;
            color: var(--color-primary, #4a2c1d);
            margin: 0;
            line-height: 1.25;
            font-weight: 600;
        }

        .legal-card__body {
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--color-on-surface-variant, #4f4742);
        }

        .legal-card__body p:last-child {
            margin-bottom: 0;
        }

        .legal-card__body ul {
            padding-left: 1.25rem;
            margin-bottom: 1rem;
        }

        .legal-card__body li {
            margin-bottom: 0.5rem;
        }

        .legal-highlight-box {
            background: var(--color-bento-rose, #f5ebe0);
            border-left: 4px solid var(--color-secondary, #9c6644);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: var(--color-primary, #4a2c1d);
        }

        .legal-meta-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--color-surface-container, #f5ebe0) 0%, var(--color-bento-peach, #faedcd) 100%);
            border: 1px solid var(--color-outline-variant, #e6ccb2);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .legal-meta-box__title {
            font-weight: 700;
            color: var(--color-primary, #4a2c1d);
            font-size: 0.95rem;
        }

        .legal-meta-box__tag {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            background: #ffffff;
            color: var(--color-secondary, #9c6644);
            border: 1px solid var(--color-outline-variant, #e6ccb2);
        }

        .legal-agency-card {
            background: #fdfaf7;
            border: 1px dashed var(--color-secondary, #9c6644);
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            font-size: 0.88rem;
            line-height: 1.6;
            color: var(--color-on-surface-variant, #6c584c);
            margin-top: 2rem;
            text-align: center;
        }

        .legal-contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .legal-contact-item {
            background: #ffffff;
            border: 1px solid var(--color-outline-variant, #e6ccb2);
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .legal-contact-item .material-symbols-outlined {
            color: var(--color-secondary, #9c6644);
            font-size: 1.3rem;
        }

        @media (max-width: 576px) {
            .legal-page {
                padding-top: calc(var(--header-height, 4rem) + 1.25rem);
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .legal-card {
                padding: 1.5rem 1.25rem;
                border-radius: 1.25rem;
            }

            .legal-btn-group {
                flex-direction: column;
                width: 100%;
                gap: 0.85rem;
            }

            .legal-btn-group .nav-item {
                width: 100%;
            }

            .legal-tab-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body class="site-body">

    <?= $this->include('modals/header') ?>

    <main class="legal-page">

        <!-- Encabezado Principal -->
        <header class="legal-header">
            <h1 class="legal-title">Términos, Condiciones y Privacidad</h1>
            <p class="legal-subtitle">
                Conocé las pautas que rigen nuestros servicios de estética y bienestar, así como nuestro compromiso estricto de protección de tus datos personales conforme a la legislación de la República Argentina.
            </p>
        </header>

        <!-- Botones de Navegación Independientes -->
        <div class="legal-actions-wrap">
            <ul class="nav legal-btn-group" id="legalTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link legal-tab-btn active" id="tab-terminos-btn" data-bs-toggle="pill" data-bs-target="#tab-terminos" type="button" role="tab" aria-controls="tab-terminos" aria-selected="true">
                        <span class="material-symbols-outlined">gavel</span>
                        <span>Términos y Condiciones</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link legal-tab-btn" id="tab-privacidad-btn" data-bs-toggle="pill" data-bs-target="#tab-privacidad" type="button" role="tab" aria-controls="tab-privacidad" aria-selected="false">
                        <span class="material-symbols-outlined">verified_user</span>
                        <span>Política de Privacidad (Ley 25.326)</span>
                    </button>
                </li>
            </ul>
        </div>

        <!-- Contenido de las Pestañas -->
        <div class="tab-content" id="legalTabContent">

            <!-- ============================================================ -->
            <!-- PESTAÑA 1: TÉRMINOS Y CONDICIONES DEL SERVICIO -->
            <!-- ============================================================ -->
            <div class="tab-pane fade show active" id="tab-terminos" role="tabpanel" aria-labelledby="tab-terminos-btn">
                
                <div class="legal-meta-box">
                    <div>
                        <div class="legal-meta-box__title">Condiciones Generales del Servicio</div>
                        <div class="text-muted admin-text-sm">Her Beauty Center — Centro de Estética Integral y Bienestar</div>
                    </div>
                    <span class="legal-meta-box__tag">Vigencia: 2026</span>
                </div>

                <!-- Punto 1 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">1</span>
                        <h2 class="legal-card__title">Ámbito de Aplicación y Aceptación</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Los presentes Términos y Condiciones regulan el acceso, navegación y utilización de la plataforma web de <strong>Her Beauty Center</strong>, establecimiento especializado en servicios de estética facial, corporal, pestañas, cejas, masoterapia y manicura, con sede operativa en la ciudad de San Luis, Argentina.
                        </p>
                        <p>
                            Al navegar por este sitio web, registrar una cuenta o solicitar un turno para cualquiera de nuestras prestaciones, la usuaria o cliente declara haber leído, comprendido y aceptado de manera íntegra y sin reservas las cláusulas aquí especificadas.
                        </p>
                    </div>
                </div>

                <!-- Punto 2 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">2</span>
                        <h2 class="legal-card__title">Catálogo de Servicios y Solicitud de Turnos</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Nuestro portal exhibe el catálogo oficial de servicios vigentes, informando sus características técnicas, duraciones estimadas de sesión y precios base orientativos expresados en moneda de curso legal (Pesos Argentinos - ARS).
                        </p>
                        <p>
                            Toda reserva iniciada a través de la plataforma web nace en estado <strong>Solicitado</strong>. Dicha acción constituye una solicitud formal de franja horaria que bloquea transitoriamente el horario seleccionado para que ninguna otra persona pueda tomarlo en simultáneo.
                        </p>
                    </div>
                </div>

                <!-- Punto 3 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">3</span>
                        <h2 class="legal-card__title">Coordinación Personalizada de Diseño, Monto y Duración</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            En función de la naturaleza personalizada de las prestaciones estéticas (variaciones de diseño en nail art, curvaturas específicas en lifting de pestañas, estado biológico de la piel en peelings, etc.), una vez generada la solicitud:
                        </p>
                        <div class="legal-highlight-box">
                            <strong>Atención personalizada:</strong> La profesional asignada se pondrá en contacto directo con la clienta mediante los canales de contacto provistos (WhatsApp o llamada telefónica) para dialogar sobre el diseño, acordar el monto definitivo y precisar la duración exacta de la sesión antes de su confirmación formal.
                        </div>
                    </div>
                </div>

                <!-- Punto 4 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">4</span>
                        <h2 class="legal-card__title">Medios de Pago y Seguridad en las Transacciones</h2>
                    </div>
                    <div class="legal-card__body">
                        <div class="legal-highlight-box">
                            <strong>Cero almacenamiento de datos bancarios:</strong> Por razones estrictas de seguridad y privacidad, Her Beauty Center no solicita, no procesa ni almacena números de tarjetas de crédito o débito ni claves bancarias en este servidor web.
                        </div>
                        <p>
                            El abono de las sesiones se coordina directamente a través de canales oficiales mediante las siguientes modalidades habilitadas:
                        </p>
                        <ul>
                            <li><strong>Efectivo:</strong> Al presentarse a la sesión en las instalaciones del centro.</li>
                            <li><strong>Transferencia Bancaria:</strong> A través de CBU/CVU y alias informado oficialmente por la administradora.</li>
                            <li><strong>Mercado Pago:</strong> Mediante enlace de pago o código QR oficial verificado del centro.</li>
                        </ul>
                    </div>
                </div>

                <!-- Punto 5 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">5</span>
                        <h2 class="legal-card__title">Política de Asistencia, Puntualidad y Cancelaciones</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Para asegurar el óptimo funcionamiento de la agenda y el respeto por el tiempo de las demás clientas y especialistas:
                        </p>
                        <ul>
                            <li><strong>Cancelación autónoma:</strong> La clienta puede cancelar su turno en cualquier momento desde su panel personal en <em>Mis Turnos</em> si el turno se encuentra en estado <em>Solicitado</em> o <em>Confirmado</em>. Al realizarse la cancelación, el sistema libera automáticamente la disponibilidad para que otra persona pueda aprovechar la franja horaria.</li>
                            <li><strong>Aviso anticipado:</strong> Agradecemos informar cualquier imprevisto con al menos 24 horas de antelación para permitir la reprogramación armónica de la cita.</li>
                            <li><strong>Puntualidad:</strong> Se contempla una tolerancia máxima de 10 a 15 minutos en el horario de inicio. Superado dicho margen, la profesional podrá adecuar el tratamiento al tiempo remanente o reprogramar el turno si compromete la atención de la siguiente clienta.</li>
                        </ul>
                    </div>
                </div>

                <!-- Punto 6 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">6</span>
                        <h2 class="legal-card__title">Preferencias de Atención y Consideraciones de la Clienta</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Es responsabilidad de la clienta comunicar de manera oportuna en el campo de consideraciones o durante la consulta previa sus preferencias de atención, sensibilidades cosméticas o inquietudes particulares respecto a los tratamientos a realizarse. Dicha información es de índole colaborativa para optimizar la experiencia en el salón y permitir a la profesional adecuar las técnicas, tiempos y productos utilizados.
                        </p>
                    </div>
                </div>

                <!-- Punto 7 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">7</span>
                        <h2 class="legal-card__title">Propiedad Intelectual</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Todas las marcas, logotipos, imágenes fotográficas de tratamientos y código de diseño del sitio pertenecen a <strong>Her Beauty Center</strong> o se utilizan bajo licencia legítima. Queda prohibida su reproducción o explotación comercial sin autorización previa y por escrito.
                        </p>
                    </div>
                </div>

            </div>

            <!-- ============================================================ -->
            <!-- PESTAÑA 2: POLÍTICA DE PRIVACIDAD (LEY 25.326) -->
            <!-- ============================================================ -->
            <div class="tab-pane fade" id="tab-privacidad" role="tabpanel" aria-labelledby="tab-privacidad-btn">
                
                <div class="legal-meta-box">
                    <div>
                        <div class="legal-meta-box__title">Protección de Datos Personales</div>
                        <div class="text-muted admin-text-sm">Conforme a la Ley Nacional N° 25.326 de la República Argentina</div>
                    </div>
                    <span class="legal-meta-box__tag">Cumplimiento Normativo</span>
                </div>

                <div class="legal-card">
                    <div class="legal-card__body">
                        <p>
                            La presente Política de Privacidad describe de qué manera <strong>Her Beauty Center</strong> recopila, utiliza, resguarda y gestiona la información de sus usuarias y clientas, en estricto cumplimiento con la <strong>Ley de Protección de Datos Personales N° 25.326</strong>, su Decreto Reglamentario 1558/2001 y disposiciones complementarias emitidas por la Agencia de Acceso a la Información Pública (AAIP).
                        </p>
                    </div>
                </div>

                <!-- Punto 1 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">1</span>
                        <h2 class="legal-card__title">Datos Recopilados (Principio de Minimización)</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            La navegación informativa por nuestro catálogo público es completamente libre y no requiere proporcionar datos personales. Al momento de registrar una cuenta o confirmar una solicitud de turno, recolectamos únicamente la información estrictamente necesaria:
                        </p>
                        <ul>
                            <li><strong>Nombre completo:</strong> Para la correcta identificación y personalización de la atención.</li>
                            <li><strong>Teléfono / WhatsApp:</strong> Canal indispensable para coordinar detalles de diseño, duración y recordatorios.</li>
                            <li><strong>Correo electrónico:</strong> Para autenticación segura, recuperación de credenciales y avisos del estado de tus turnos.</li>
                            <li><strong>Consideraciones especiales:</strong> Campo facultativo y opcional donde la clienta puede asentar preferencias personales o indicaciones de confort para su cita.</li>
                        </ul>
                        <div class="legal-highlight-box">
                            <strong>Ausencia de datos sensibles:</strong> En estricto apego al Artículo 2° de la Ley N° 25.326, Her Beauty Center no solicita, no recolecta ni almacena datos sensibles relativos a la salud, historias clínicas, diagnósticos médicos ni datos financieros o bancarios.
                        </div>
                    </div>
                </div>

                <!-- Punto 2 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">2</span>
                        <h2 class="legal-card__title">Finalidad del Tratamiento</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Los datos recolectados se emplean exclusivamente para los siguientes fines debidamente comunicados:
                        </p>
                        <ul>
                            <li>Gestionar, agendar y confirmar las solicitudes de reserva de turnos en nuestra plataforma.</li>
                            <li>Permitir que la profesional asignada se comunique con la clienta para acordar pautas técnicas del servicio.</li>
                            <li>Tomar en cuenta las consideraciones y preferencias manifestadas por la clienta para personalizar la sesión de belleza.</li>
                            <li>Permitir el seguimiento personal de citas a través del panel <em>Mis Turnos</em> y la exportación voluntaria a Google Calendar bajo el estándar abierto RFC 5545 (sin recopilar tokens de cuentas externas).</li>
                        </ul>
                    </div>
                </div>

                <!-- Punto 3 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">3</span>
                        <h2 class="legal-card__title">Acceso y Confidencialidad de la Información</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            El acceso a los datos personales está estrictamente limitado a la administradora y a las profesionales asignadas al tratamiento solicitado.
                        </p>
                        <div class="legal-highlight-box">
                            <strong>Garantía de no comercialización:</strong> Her Beauty Center jamás venderá, alquilará, cederá ni compartirá los datos personales de sus clientas con terceras empresas u organismos para fines publicitarios o comerciales ajenos al servicio.
                        </div>
                    </div>
                </div>

                <!-- Punto 4 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">4</span>
                        <h2 class="legal-card__title">Plazo de Conservación de los Datos</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Los datos de contacto y el historial de turnos se conservan de forma segura mientras la usuaria mantenga su cuenta activa en la plataforma, facilitando la continuidad del cuidado estético y el control administrativo del establecimiento. La información se conservará hasta que la titular solicite expresamente su supresión o eliminación definitiva de nuestras bases de datos.
                        </p>
                    </div>
                </div>

                <!-- Punto 5 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">5</span>
                        <h2 class="legal-card__title">Ejercicio de los Derechos ARCO</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            De acuerdo con los artículos 14, 15 y 16 de la Ley Nacional N° 25.326, la clienta es la titular exclusiva de sus datos personales y tiene derecho a ejercer en cualquier momento y de forma gratuita sus facultades de:
                        </p>
                        <ul>
                            <li><strong>Acceso:</strong> Conocer qué datos personales propios se encuentran registrados en el sistema.</li>
                            <li><strong>Rectificación:</strong> Actualizar o corregir información inexacta o desactualizada (también disponible directamente desde la sección <em>Mi Perfil</em>).</li>
                            <li><strong>Cancelación (Supresión):</strong> Solicitar la eliminación total de sus datos de nuestros registros.</li>
                            <li><strong>Oposición:</strong> Oponerse al tratamiento de sus datos para determinados fines específicos.</li>
                        </ul>

                        <p class="mt-3 mb-2 fw-bold text-primary">Canales habilitados para ejercer tus derechos:</p>
                        <div class="legal-contact-grid">
                            <div class="legal-contact-item">
                                <span class="material-symbols-outlined">mail</span>
                                <div>
                                    <div class="admin-text-sm text-muted">Correo electrónico</div>
                                    <a href="mailto:garcesbrocalmaru@gmail.com" class="fw-bold text-dark text-decoration-none">garcesbrocalmaru@gmail.com</a>
                                </div>
                            </div>
                            <div class="legal-contact-item">
                                <span class="material-symbols-outlined">chat</span>
                                <div>
                                    <div class="admin-text-sm text-muted">WhatsApp Oficial</div>
                                    <a href="https://wa.me/5492664159744" target="_blank" rel="noopener noreferrer" class="fw-bold text-dark text-decoration-none">+54 9 2664 159744</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Punto 6 -->
                <div class="legal-card">
                    <div class="legal-card__header">
                        <span class="legal-card__num">6</span>
                        <h2 class="legal-card__title">Medidas de Seguridad Técnicas y Organizativas</h2>
                    </div>
                    <div class="legal-card__body">
                        <p>
                            Implementamos medidas de seguridad de estándar profesional para resguardar la confidencialidad de la información y prevenir accesos no autorizados:
                        </p>
                        <ul>
                            <li><strong>Cifrado en tránsito (HTTPS):</strong> Toda la comunicación entre el navegador de la clienta y el servidor se realiza bajo certificados SSL/TLS con encriptación activa de extremo a extremo.</li>
                            <li><strong>Almacenamiento seguro de credenciales:</strong> Las contraseñas se almacenan mediante hashes criptográficos unidireccionales con algoritmo Bcrypt (<code>password_hash</code>). Ningún miembro del personal ni terceros pueden visualizar las contraseñas en texto plano.</li>
                            <li><strong>Control de acceso por roles (RBAC):</strong> Separación lógica estricta entre cuentas de clientas, profesionales y administradoras, impidiendo el acceso a paneles o agendas ajenas.</li>
                            <li><strong>Protección contra acceso cruzado (Anti-IDOR):</strong> Cada consulta en el panel <em>Mis Turnos</em> valida en el servidor que el turno pertenezca de forma unívoca a la clienta autenticada en sesión.</li>
                        </ul>
                    </div>
                </div>

                <!-- Cláusula del Órgano de Control -->
                <div class="legal-agency-card">
                    <strong>Información Legal Requerida por la Dirección Nacional de Protección de Datos Personales:</strong><br />
                    «La Agencia de Acceso a la Información Pública, en su carácter de Órgano de Control de la Ley N° 25.326, tiene la atribución de atender las denuncias y reclamos que interpongan quienes resulten afectados en sus derechos por incumplimiento de las normas vigentes en materia de protección de datos personales.»
                </div>

            </div>

        </div>

    </main>

    <?= $this->include('modals/footer') ?>
    <?= $this->include('modals/modalsBase') ?>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Custom JS -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            apiUrl: '<?= rtrim(base_url('api'), '/') ?>'
        };

        // Soporte para abrir pestaña específica si viene hash en la URL (#privacidad o #terminos)
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash;
            if (hash === '#privacidad' || hash === '#tab-privacidad') {
                const triggerEl = document.getElementById('tab-privacidad-btn');
                if (triggerEl) {
                    bootstrap.Tab.getOrCreateInstance(triggerEl).show();
                }
            } else if (hash === '#terminos' || hash === '#tab-terminos') {
                const triggerEl = document.getElementById('tab-terminos-btn');
                if (triggerEl) {
                    bootstrap.Tab.getOrCreateInstance(triggerEl).show();
                }
            }
        });
    </script>
</body>

</html>

