<?php
/**
 * Script de inserción de datos de prueba completos para Her Beauty Center
 * - Servicios variados
 * - Profesionales y Administradores
 * - Clientes
 * - Asignación de servicios
 * - Horarios de disponibilidad (Mi Disponibilidad & Calendario)
 * - Turnos en todos los estados (Mis Turnos & Calendario)
 */

$mysqli = new mysqli('localhost', 'root', '', 'her_beauty_center');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

$defaultPass = password_hash('12345678', PASSWORD_DEFAULT);
$today = date('Y-m-d'); // 2026-10-19

echo "--- INICIANDO INSERCIÓN DE DATOS DE PRUEBA ---\n";

// ============================================================
// 1. SERVICIOS
// ============================================================
$serviciosData = [
    [
        's_nbre' => 'Limpieza Facial Profunda',
        's_desc' => 'Tratamiento facial completo con exfoliación, vapor de ozono, extracción de puntos negros y máscara hidratante.',
        's_duracionMinutos' => 60,
        's_precio' => 15000,
        's_activo' => 1,
        's_orden' => 1
    ],
    [
        's_nbre' => 'Lifting y Tinte de Pestañas',
        's_desc' => 'Curvado natural de pestañas con nutrición de keratina y tinte negro intenso para realzar la mirada.',
        's_duracionMinutos' => 45,
        's_precio' => 12500,
        's_activo' => 1,
        's_orden' => 2
    ],
    [
        's_nbre' => 'Perfilado y Laminado de Cejas',
        's_desc' => 'Diseño personalizado de cejas con cera y pinza, fijación y nutrición semipermanente.',
        's_duracionMinutos' => 40,
        's_precio' => 11000,
        's_activo' => 1,
        's_orden' => 3
    ],
    [
        's_nbre' => 'Masaje Descontracturante',
        's_desc' => 'Terapia manual focalizada en cuello, espalda y hombros para aliviar tensiones musculares y estrés.',
        's_duracionMinutos' => 50,
        's_precio' => 18000,
        's_activo' => 1,
        's_orden' => 4
    ],
    [
        's_nbre' => 'Manicura Rusa con Semipermanente',
        's_desc' => 'Limpieza profunda de cutículas con torno, nivelación y esmaltado de alta duración.',
        's_duracionMinutos' => 75,
        's_precio' => 14000,
        's_activo' => 1,
        's_orden' => 5
    ],
    [
        's_nbre' => 'Peeling Químico Renovador',
        's_desc' => 'Renovación celular cutánea con ácidos suaves para unificar tono y disminuir manchas.',
        's_duracionMinutos' => 45,
        's_precio' => 16500,
        's_activo' => 1,
        's_orden' => 6
    ],
    [
        's_nbre' => 'Drenaje Linfático Manual',
        's_desc' => 'Masaje suave y rítmico para favorecer la circulación linfática y reducir retención de líquidos.',
        's_duracionMinutos' => 60,
        's_precio' => 17500,
        's_activo' => 1,
        's_orden' => 7
    ],
    [
        's_nbre' => 'Spa Facial Anti-Age',
        's_desc' => 'Protocolo antiedad con radiofrecuencia, sérum de ácido hialurónico y velo de colágeno.',
        's_duracionMinutos' => 90,
        's_precio' => 22000,
        's_activo' => 1,
        's_orden' => 8
    ],
    [
        's_nbre' => 'Tratamiento Capilar Keratina (Inactivo)',
        's_desc' => 'Nutrición capilar profunda para reducción de frizz y brillo duradero.',
        's_duracionMinutos' => 90,
        's_precio' => 20000,
        's_activo' => 0,
        's_orden' => 9
    ]
];

$serviciosMap = [];
foreach ($serviciosData as $s) {
    $stmt = $mysqli->prepare("SELECT s_id FROM servicio WHERE s_nbre = ?");
    $stmt->bind_param("s", $s['s_nbre']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $serviciosMap[$s['s_nbre']] = (int)$row['s_id'];
    } else {
        $ins = $mysqli->prepare("INSERT INTO servicio (s_nbre, s_desc, s_duracionMinutos, s_precio, s_activo, s_orden) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("ssiddi", $s['s_nbre'], $s['s_desc'], $s['s_duracionMinutos'], $s['s_precio'], $s['s_activo'], $s['s_orden']);
        $ins->execute();
        $serviciosMap[$s['s_nbre']] = (int)$ins->insert_id;
        echo "Servicio creado: {$s['s_nbre']} (ID: {$serviciosMap[$s['s_nbre']]})\n";
    }
}

// ============================================================
// 2. PROFESIONALES Y ADMINISTRADORES
// ============================================================
$profesionalesData = [
    [
        'nombre' => 'Camila Solís',
        'correo' => 'camila.solis@herbeauty.com',
        'tel'    => '2664112233',
        'titulo' => 'Cosmiatra & Especialista en Cejas',
        'desc'   => 'Especialista certificada en estética facial y diseño de miradas con 5 años de trayectoria.',
        'anio'   => 2019,
        'activo' => 1,
        'es_admin' => 0,
        'servicios' => ['Limpieza Facial Profunda', 'Lifting y Tinte de Pestañas', 'Perfilado y Laminado de Cejas', 'Peeling Químico Renovador']
    ],
    [
        'nombre' => 'Dra. Valeria Méndez',
        'correo' => 'valeria.mendez@herbeauty.com',
        'tel'    => '2664556677',
        'titulo' => 'Dermatoestética & Terapeuta Corporal',
        'desc'   => 'Médica dedicada a la medicina estética y tratamientos corporales no invasivos.',
        'anio'   => 2017,
        'activo' => 1,
        'es_admin' => 1, // También Administradora
        'servicios' => ['Masaje Descontracturante', 'Drenaje Linfático Manual', 'Spa Facial Anti-Age', 'Peeling Químico Renovador']
    ],
    [
        'nombre' => 'Florencia Romero',
        'correo' => 'flor.romero@herbeauty.com',
        'tel'    => '2664889900',
        'titulo' => 'Técnica Nail Artist',
        'desc'   => 'Manicurista especializada en manicura rusa combinada, nail art y kapping gel.',
        'anio'   => 2021,
        'activo' => 1,
        'es_admin' => 0,
        'servicios' => ['Manicura Rusa con Semipermanente']
    ],
    [
        'nombre' => 'Inés Benítez',
        'correo' => 'ines.benitez@herbeauty.com',
        'tel'    => '2664001122',
        'titulo' => 'Esteticista Corporal',
        'desc'   => 'Especialista en masoterapia y drenaje linfático.',
        'anio'   => 2020,
        'activo' => 0, // Inactiva
        'es_admin' => 0,
        'servicios' => ['Drenaje Linfático Manual']
    ]
];

$profesionalesMap = [];

// Obtener o asignar Maru (P_ID 2)
$resMaru = $mysqli->query("SELECT p.p_id FROM profesional p JOIN usuario u ON u.u_id = p.u_id WHERE u.u_correo = 'garcesbrocalmaru@gmail.com'");
if ($rowM = $resMaru->fetch_assoc()) {
    $maruPId = (int)$rowM['p_id'];
    $profesionalesMap['Maru'] = $maruPId;
    // Asignar servicios a Maru
    $maruServicios = ['Limpieza Facial Profunda', 'Spa Facial Anti-Age', 'Masaje Descontracturante', 'Lifting y Tinte de Pestañas'];
    foreach ($maruServicios as $sName) {
        if (isset($serviciosMap[$sName])) {
            $sId = $serviciosMap[$sName];
            $mysqli->query("INSERT IGNORE INTO profesional_servicio (p_id, p_sId) VALUES ($maruPId, $sId)");
        }
    }
}

foreach ($profesionalesData as $pData) {
    // 1. Usuario
    $stmtU = $mysqli->prepare("SELECT u_id FROM usuario WHERE u_correo = ?");
    $stmtU->bind_param("s", $pData['correo']);
    $stmtU->execute();
    $resU = $stmtU->get_result();
    if ($uRow = $resU->fetch_assoc()) {
        $uId = (int)$uRow['u_id'];
    } else {
        $insU = $mysqli->prepare("INSERT INTO usuario (u_nbreCompleto, u_correo, u_tel, u_activo, u_pass) VALUES (?, ?, ?, ?, ?)");
        $insU->bind_param("sssis", $pData['nombre'], $pData['correo'], $pData['tel'], $pData['activo'], $defaultPass);
        $insU->execute();
        $uId = (int)$insU->insert_id;
        echo "Usuario profesional creado: {$pData['nombre']} (U_ID: $uId)\n";
    }

    // 2. Profesional
    $stmtP = $mysqli->prepare("SELECT p_id FROM profesional WHERE u_id = ?");
    $stmtP->bind_param("i", $uId);
    $stmtP->execute();
    $resP = $stmtP->get_result();
    if ($pRow = $resP->fetch_assoc()) {
        $pId = (int)$pRow['p_id'];
    } else {
        $insP = $mysqli->prepare("INSERT INTO profesional (u_id, p_titulo, p_desc, p_anioInicioAct) VALUES (?, ?, ?, ?)");
        $insP->bind_param("issi", $uId, $pData['titulo'], $pData['desc'], $pData['anio']);
        $insP->execute();
        $pId = (int)$insP->insert_id;
        echo "Perfil profesional creado: {$pData['nombre']} (P_ID: $pId)\n";
    }
    $profesionalesMap[$pData['nombre']] = $pId;

    // 3. Administrador (si corresponde)
    if ($pData['es_admin']) {
        $stmtA = $mysqli->prepare("SELECT a_id FROM administrador WHERE a_pId = ?");
        $stmtA->bind_param("i", $pId);
        $stmtA->execute();
        if (!$stmtA->get_result()->fetch_assoc()) {
            $insA = $mysqli->prepare("INSERT INTO administrador (a_pId) VALUES (?)");
            $insA->bind_param("i", $pId);
            $insA->execute();
            echo "Administrador asignado: {$pData['nombre']} (A_ID: {$insA->insert_id})\n";
        }
    }

    // 4. Asignación de servicios
    foreach ($pData['servicios'] as $sName) {
        if (isset($serviciosMap[$sName])) {
            $sId = $serviciosMap[$sName];
            $mysqli->query("INSERT IGNORE INTO profesional_servicio (p_id, p_sId) VALUES ($pId, $sId)");
        }
    }
}

// ============================================================
// 3. CLIENTES
// ============================================================
$clientesData = [
    ['nombre' => 'Lucía Fernández', 'correo' => 'lucia.fernandez@gmail.com', 'tel' => '2664223344'],
    ['nombre' => 'Sofía Martínez', 'correo' => 'sofia.martinez@hotmail.com', 'tel' => '2664334455'],
    ['nombre' => 'Valentina Rossi', 'correo' => 'valen.rossi@yahoo.com', 'tel' => '2664445566'],
    ['nombre' => 'Agustina Gómez', 'correo' => 'agus.gomez@gmail.com', 'tel' => '2664556677'],
    ['nombre' => 'Carolina Herrera', 'correo' => 'caro.herrera@outlook.com', 'tel' => '2664667788'],
    ['nombre' => 'Martina Díaz', 'correo' => 'martina.diaz@gmail.com', 'tel' => '2664778899']
];

$clientesMap = [];

// Obtener cliente prueba existente
$resCliP = $mysqli->query("SELECT c.c_id FROM cliente c JOIN usuario u ON u.u_id = c.c_uId WHERE u.u_correo = 'cliente_prueba_99@gmail.com'");
if ($rowCP = $resCliP->fetch_assoc()) {
    $clientesMap['Cliente Prueba'] = (int)$rowCP['c_id'];
}

foreach ($clientesData as $cData) {
    $stmtU = $mysqli->prepare("SELECT u_id FROM usuario WHERE u_correo = ?");
    $stmtU->bind_param("s", $cData['correo']);
    $stmtU->execute();
    $resU = $stmtU->get_result();
    if ($uRow = $resU->fetch_assoc()) {
        $uId = (int)$uRow['u_id'];
    } else {
        $insU = $mysqli->prepare("INSERT INTO usuario (u_nbreCompleto, u_correo, u_tel, u_activo, u_pass) VALUES (?, ?, ?, 1, ?)");
        $insU->bind_param("ssss", $cData['nombre'], $cData['correo'], $cData['tel'], $defaultPass);
        $insU->execute();
        $uId = (int)$insU->insert_id;
        echo "Usuario cliente creado: {$cData['nombre']} (U_ID: $uId)\n";
    }

    $stmtC = $mysqli->prepare("SELECT c_id FROM cliente WHERE c_uId = ?");
    $stmtC->bind_param("i", $uId);
    $stmtC->execute();
    $resC = $stmtC->get_result();
    if ($cRow = $resC->fetch_assoc()) {
        $cId = (int)$cRow['c_id'];
    } else {
        $insC = $mysqli->prepare("INSERT INTO cliente (c_uId) VALUES (?)");
        $insC->bind_param("i", $uId);
        $insC->execute();
        $cId = (int)$insC->insert_id;
        echo "Perfil cliente creado: {$cData['nombre']} (C_ID: $cId)\n";
    }
    $clientesMap[$cData['nombre']] = $cId;
}

// ============================================================
// 4. HORARIOS Y TURNOS DE PRUEBA
// ============================================================
// Vamos a insertar horarios y turnos para Maru (P_ID 2) y Camila Solís
// Fechas relativas a hoy ($today = 2026-10-19)
$dias = [
    'hoy'       => date('Y-m-d', strtotime('today')),
    'manana'    => date('Y-m-d', strtotime('+1 day')),
    'miercoles' => date('Y-m-d', strtotime('+2 days')),
    'jueves'    => date('Y-m-d', strtotime('+3 days')),
    'viernes'   => date('Y-m-d', strtotime('+4 days')),
    'sabado'    => date('Y-m-d', strtotime('+5 days')),
    'prox_lun'  => date('Y-m-d', strtotime('+7 days')),
    'prox_mie'  => date('Y-m-d', strtotime('+9 days')),
    'ayer'      => date('Y-m-d', strtotime('-1 day')),
    'anteayer'  => date('Y-m-d', strtotime('-2 days'))
];

// Maru P_ID
$targetPId = $profesionalesMap['Maru'] ?? 2;
$camilaPId = $profesionalesMap['Camila Solís'] ?? null;

// Estructura de turnos para Maru:
$turnosConfig = [
    // 1. Turno HOY - Confirmado
    [
        'fecha' => $dias['hoy'],
        'desde' => '09:00:00',
        'hasta' => '10:00:00',
        'servicio' => 'Limpieza Facial Profunda',
        'estado' => 'Confirmado',
        'cliente' => 'Lucía Fernández',
        'abonado' => 1,
        'monto' => 15000,
        'motivo' => null
    ],
    // 2. Turno HOY - Solicitado
    [
        'fecha' => $dias['hoy'],
        'desde' => '11:00:00',
        'hasta' => '12:00:00',
        'servicio' => 'Spa Facial Anti-Age',
        'estado' => 'Solicitado',
        'cliente' => 'Sofía Martínez',
        'abonado' => 0,
        'monto' => 22000,
        'motivo' => null
    ],
    // 3. Disponibilidad pura HOY (SÓLO DISPONIBLE - aparece en Mi Disponibilidad!)
    [
        'fecha' => $dias['hoy'],
        'desde' => '16:00:00',
        'hasta' => '17:00:00',
        'servicio' => 'Lifting y Tinte de Pestañas',
        'estado' => 'Disponible', // Sin turno activo
        'cliente' => null,
        'abonado' => 0,
        'monto' => 12500,
        'motivo' => null
    ],
    // 4. Turno MAÑANA - Confirmado
    [
        'fecha' => $dias['manana'],
        'desde' => '10:00:00',
        'hasta' => '10:50:00',
        'servicio' => 'Masaje Descontracturante',
        'estado' => 'Confirmado',
        'cliente' => 'Valentina Rossi',
        'abonado' => 1,
        'monto' => 18000,
        'motivo' => null
    ],
    // 5. Turno MAÑANA - Cancelado
    [
        'fecha' => $dias['manana'],
        'desde' => '14:30:00',
        'hasta' => '15:30:00',
        'servicio' => 'Limpieza Facial Profunda',
        'estado' => 'Cancelado',
        'cliente' => 'Agustina Gómez',
        'abonado' => 0,
        'monto' => 15000,
        'motivo' => 'Aviso de viaje imprevisto por parte del cliente.'
    ],
    // 6. Disponibilidad pura MAÑANA
    [
        'fecha' => $dias['manana'],
        'desde' => '16:30:00',
        'hasta' => '17:30:00',
        'servicio' => 'Spa Facial Anti-Age',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 22000,
        'motivo' => null
    ],
    // 7. Turno MIÉRCOLES - Solicitado
    [
        'fecha' => $dias['miercoles'],
        'desde' => '09:30:00',
        'hasta' => '10:15:00',
        'servicio' => 'Lifting y Tinte de Pestañas',
        'estado' => 'Solicitado',
        'cliente' => 'Carolina Herrera',
        'abonado' => 0,
        'monto' => 12500,
        'motivo' => null
    ],
    // 8. Disponibilidad pura MIÉRCOLES
    [
        'fecha' => $dias['miercoles'],
        'desde' => '11:00:00',
        'hasta' => '12:00:00',
        'servicio' => 'Masaje Descontracturante',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 18000,
        'motivo' => null
    ],
    // 9. Turno JUEVES - Confirmado
    [
        'fecha' => $dias['jueves'],
        'desde' => '15:00:00',
        'hasta' => '16:00:00',
        'servicio' => 'Limpieza Facial Profunda',
        'estado' => 'Confirmado',
        'cliente' => 'Martina Díaz',
        'abonado' => 1,
        'monto' => 15000,
        'motivo' => null
    ],
    // 10. Turno VIERNES - Solicitado
    [
        'fecha' => $dias['viernes'],
        'desde' => '10:00:00',
        'hasta' => '11:30:00',
        'servicio' => 'Spa Facial Anti-Age',
        'estado' => 'Solicitado',
        'cliente' => 'Cliente Prueba',
        'abonado' => 0,
        'monto' => 22000,
        'motivo' => null
    ],
    // 11. Disponibilidad pura VIERNES
    [
        'fecha' => $dias['viernes'],
        'desde' => '15:00:00',
        'hasta' => '16:00:00',
        'servicio' => 'Limpieza Facial Profunda',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 15000,
        'motivo' => null
    ],
    // 12. Turno PASADO (Ayer) - Realizado
    [
        'fecha' => $dias['ayer'],
        'desde' => '11:00:00',
        'hasta' => '12:00:00',
        'servicio' => 'Limpieza Facial Profunda',
        'estado' => 'Realizado',
        'cliente' => 'Lucía Fernández',
        'abonado' => 1,
        'monto' => 15000,
        'motivo' => null
    ],
    // 13. Turno PASADO (Anteayer) - Realizado
    [
        'fecha' => $dias['anteayer'],
        'desde' => '16:00:00',
        'hasta' => '17:00:00',
        'servicio' => 'Masaje Descontracturante',
        'estado' => 'Realizado',
        'cliente' => 'Sofía Martínez',
        'abonado' => 1,
        'monto' => 18000,
        'motivo' => null
    ],
    // 14. Disponibilidad pura PRÓXIMA SEMANA
    [
        'fecha' => $dias['prox_lun'],
        'desde' => '10:00:00',
        'hasta' => '11:00:00',
        'servicio' => 'Spa Facial Anti-Age',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 22000,
        'motivo' => null
    ],
    // 15. Disponibilidad pura PRÓXIMO MIÉRCOLES
    [
        'fecha' => $dias['prox_mie'],
        'desde' => '14:00:00',
        'hasta' => '15:00:00',
        'servicio' => 'Masaje Descontracturante',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 18000,
        'motivo' => null
    ]
];

foreach ($turnosConfig as $tc) {
    $sId = $serviciosMap[$tc['servicio']] ?? null;
    if (!$sId) continue;

    // Verificar si ya existe este horario para Maru
    $stmtH = $mysqli->prepare("SELECT h_id FROM horario WHERE h_pId = ? AND h_fecha = ? AND h_horaDesde = ?");
    $stmtH->bind_param("iss", $targetPId, $tc['fecha'], $tc['desde']);
    $stmtH->execute();
    $resH = $stmtH->get_result();
    if ($rowH = $resH->fetch_assoc()) {
        $hId = (int)$rowH['h_id'];
    } else {
        $insH = $mysqli->prepare("INSERT INTO horario (h_pId, h_sId, h_fecha, h_horaDesde, h_horaHasta) VALUES (?, ?, ?, ?, ?)");
        $insH->bind_param("iisss", $targetPId, $sId, $tc['fecha'], $tc['desde'], $tc['hasta']);
        $insH->execute();
        $hId = (int)$insH->insert_id;
        echo "Horario publicado: {$tc['fecha']} {$tc['desde']} - {$tc['servicio']} (H_ID: $hId)\n";
    }

    // Si tiene turno asociado:
    if ($tc['estado'] !== 'Disponible' && !empty($tc['cliente'])) {
        $cId = $clientesMap[$tc['cliente']] ?? null;
        if ($cId) {
            // Verificar si ya existe turno para este horario
            $stmtT = $mysqli->prepare("SELECT t_id FROM turno WHERE t_hId = ?");
            $stmtT->bind_param("i", $hId);
            $stmtT->execute();
            if (!$stmtT->get_result()->fetch_assoc()) {
                $insT = $mysqli->prepare("INSERT INTO turno (t_hId, t_pId, t_cId, t_fecha, t_horaDesde, t_horaHasta, estado, abonado, monto, motivo_cancelacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insT->bind_param("iiissssids", $hId, $targetPId, $cId, $tc['fecha'], $tc['desde'], $tc['hasta'], $tc['estado'], $tc['abonado'], $tc['monto'], $tc['motivo']);
                $insT->execute();
                echo "Turno creado: {$tc['estado']} para {$tc['cliente']} ({$tc['fecha']} {$tc['desde']}) (T_ID: {$insT->insert_id})\n";
            }
        }
    }
}

// También agregamos disponibilidades para Camila Solís si existe
if ($camilaPId) {
    $camilaDisponibilidades = [
        ['fecha' => $dias['hoy'], 'desde' => '14:00:00', 'hasta' => '14:45:00', 'servicio' => 'Lifting y Tinte de Pestañas'],
        ['fecha' => $dias['manana'], 'desde' => '09:00:00', 'hasta' => '09:40:00', 'servicio' => 'Perfilado y Laminado de Cejas'],
        ['fecha' => $dias['miercoles'], 'desde' => '16:00:00', 'hasta' => '17:00:00', 'servicio' => 'Limpieza Facial Profunda'],
    ];
    foreach ($camilaDisponibilidades as $cd) {
        $sId = $serviciosMap[$cd['servicio']] ?? null;
        if ($sId) {
            $stmtH = $mysqli->prepare("SELECT h_id FROM horario WHERE h_pId = ? AND h_fecha = ? AND h_horaDesde = ?");
            $stmtH->bind_param("iss", $camilaPId, $cd['fecha'], $cd['desde']);
            $stmtH->execute();
            if (!$stmtH->get_result()->fetch_assoc()) {
                $insH = $mysqli->prepare("INSERT INTO horario (h_pId, h_sId, h_fecha, h_horaDesde, h_horaHasta) VALUES (?, ?, ?, ?, ?)");
                $insH->bind_param("iisss", $camilaPId, $sId, $cd['fecha'], $cd['desde'], $cd['hasta']);
                $insH->execute();
                echo "Horario Camila: {$cd['fecha']} {$cd['desde']} (H_ID: {$insH->insert_id})\n";
            }
        }
    }
}

echo "--- INSERCIÓN FINALIZADA CON ÉXITO ---\n";
