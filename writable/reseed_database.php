<?php
/**
 * Script de reinicio y resembrado completo de servicios, imagenes, disponibilidades y turnos.
 * Cumple con restricciones: CERO EMOJIS, datos consistentes y relaciones FK íntegras.
 */

$mysqli = new mysqli('localhost', 'root', '', 'herbeautycenter_db');
if ($mysqli->connect_error) {
    die("Error de conexion: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

echo "=== INICIANDO RESEED DE BASE DE DATOS ===\n";

// 1. Eliminar archivos de prueba basura en public/assets/media/Servicios/
$mediaDir = __DIR__ . '/../public/assets/media/Servicios/';
if (is_dir($mediaDir)) {
    $junkFiles = glob($mediaDir . 'servicioServicioPrueba*.*');
    foreach ($junkFiles as $junk) {
        if (is_file($junk)) {
            unlink($junk);
            echo "Archivo de prueba eliminado: " . basename($junk) . "\n";
        }
    }
}

// 2. Limpieza de tablas dependientes de servicios
echo "Limpiando tablas: turno, horario, profesional_servicio, imagen, servicio...\n";
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
$mysqli->query("TRUNCATE TABLE turno");
$mysqli->query("TRUNCATE TABLE horario");
$mysqli->query("TRUNCATE TABLE profesional_servicio");
$mysqli->query("TRUNCATE TABLE imagen");
$mysqli->query("TRUNCATE TABLE servicio");
$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

// 3. Obtener identificadores de profesionales existentes
$profesionales = [];
$resP = $mysqli->query("SELECT p.p_id, u.u_nbreCompleto, u.u_correo FROM profesional p JOIN usuario u ON u.u_id = p.u_id");
while ($row = $resP->fetch_assoc()) {
    $profesionales[$row['u_nbreCompleto']] = (int)$row['p_id'];
    if (strpos($row['u_nbreCompleto'], 'Maru') !== false || $row['u_correo'] === 'garcesbrocalmaru@gmail.com') {
        $profesionales['Maru'] = (int)$row['p_id'];
    }
}

// 4. Actualizar consideraciones especiales en clientes
$clientesConsideraciones = [
    'Lucía Fernández'  => 'Piel sensible y reactiva a fragancias fuertes. Preferencia por emulsiones neutras.',
    'Sofía Martínez'   => 'Tendencia a rosácea leve en pómulos. Evitar exfoliantes mecánicos abrasivos.',
    'Valentina Rossi'  => 'Utiliza lentes de contacto descartables. Preferencia por guantes de nitrilo.',
    'Agustina Gómez'   => 'Piel mixta a grasa con comedones. Preferencia por productos livianos no comedogénicos.',
    'Carolina Herrera' => 'Piel muy deshidratada. Intolerancia a productos con base de alcohol o astringentes.',
    'Martina Díaz'     => 'Sensibilidad en la zona periocular. Emplear adhesivo hipoalergénico para lifting.',
    'Cliente Prueba'   => 'Piel sensible. Preferencia por concentraciones suaves de principios activos.'
];

$clientesMap = [];
$resC = $mysqli->query("SELECT c.c_id, u.u_nbreCompleto, u.u_correo FROM cliente c JOIN usuario u ON u.u_id = c.c_uId");
while ($row = $resC->fetch_assoc()) {
    $cId = (int)$row['c_id'];
    $nombre = trim($row['u_nbreCompleto']);
    $clientesMap[$nombre] = $cId;
    $clientesMap[mb_strtolower($nombre)] = $cId;

    $consideracion = $clientesConsideraciones[$nombre] ?? 'Sin consideraciones particulares declaradas.';
    
    $stmtUpd = $mysqli->prepare("UPDATE cliente SET c_consideraciones = ? WHERE c_id = ?");
    $stmtUpd->bind_param("si", $consideracion, $cId);
    $stmtUpd->execute();
    echo "Cliente actualizado: {$nombre} (C_ID: {$cId}) con consideraciones.\n";
}

// 5. Insercion de Servicios actualizados
$serviciosData = [
    [
        's_nbre' => 'Limpieza Facial Profunda',
        's_desc' => 'Tratamiento facial completo y purificante: diagnostico cutaneo, higiene profunda, exfoliacion, vapor de ozono, extraccion minuciosa de comedones, mascara descongestiva e hidratacion final con proteccion solar.',
        's_duracionMinutos' => 60,
        's_precio' => 16500.00,
        's_activo' => 1,
        's_orden' => 1,
        'imagenes' => [
            'servicioLimpiezaFacialProfunda_6ad6cb2a97a55.webp',
            'servicioPeelingQumicoRenovador_6ad6cd274279d.webp'
        ],
        'profesionales' => ['Maru', 'Camila Solís']
    ],
    [
        's_nbre' => 'Lifting y Tinte de Pestañas',
        's_desc' => 'Curvado natural de pestañas desde la raiz con nutricion intensiva de keratina y pigmento negro azabache de larga duracion. Mirada abierta y definida sin necesidad de arqueador por 6 a 8 semanas.',
        's_duracionMinutos' => 45,
        's_precio' => 13500.00,
        's_activo' => 1,
        's_orden' => 2,
        'imagenes' => [
            'servicioLiftingyTintedePestaas_6ad6cc4c84700.webp',
            'servicioLiftingcoreano_6ab28f8b359c0.webp',
            'servicioLiftingpestaastradicional_6ab28f42cf2fa.webp'
        ],
        'profesionales' => ['Maru', 'Camila Solís']
    ],
    [
        's_nbre' => 'Perfilado y Laminado de Cejas',
        's_desc' => 'Diseño y visagismo personalizado de cejas con depilacion precisa con pinza y cera tibia, peinado fijador semipermanente y nutricion botox capilar para lograr volumen y alineacion perfecta.',
        's_duracionMinutos' => 40,
        's_precio' => 12000.00,
        's_activo' => 1,
        's_orden' => 3,
        'imagenes' => [
            'servicioPerfiladoyLaminadodeCejas_6ad6cc74f3117.webp',
            'servicioLaminadodecejas_6ab28fb834f30.webp'
        ],
        'profesionales' => ['Camila Solís']
    ],
    [
        's_nbre' => 'Masaje Descontracturante',
        's_desc' => 'Terapia manual profunda y focalizada sobre espalda, cuello y hombros. Libera contracturas, disminuye dolores tensionales y mejora la postura corporal mediante presiones y estiramientos terapeuticos.',
        's_duracionMinutos' => 50,
        's_precio' => 19000.00,
        's_activo' => 1,
        's_orden' => 4,
        'imagenes' => [
            'servicioMasajeDescontracturante_6ad6cc920db83.webp',
            'servicioMasajescuerpocompleto_6ab2901e7f836.webp',
            'servicioMasajescuerpocompleto_6ab2904f378cf.webp'
        ],
        'profesionales' => ['Maru', 'Dra. Valeria Méndez']
    ],
    [
        's_nbre' => 'Manicura Rusa con Semipermanente',
        's_desc' => 'Tecnica de manicura combinada en seco con torno diamantado, corte y pulido de cuticulas, nivelacion de placa ungueal con base builder y esmaltado semipermanente de alta duracion con brillo espejo.',
        's_duracionMinutos' => 75,
        's_precio' => 15500.00,
        's_activo' => 1,
        's_orden' => 5,
        'imagenes' => [
            'servicioManicuraRusaconSemipermanente_6ad6ccfab4a51.webp',
            'servicioEsmaltadosemipermanente_6ab19cbd37503.webp',
            'servicioSoftgel_6ab28c25a0f1e.webp'
        ],
        'profesionales' => ['Florencia Romero']
    ],
    [
        's_nbre' => 'Peeling Químico Renovador',
        's_desc' => 'Tratamiento de renovacion celular epidermica con acidos dermo-cosmeticos (glicolico, mandelico y salicilico) segun fototipo. Atenua manchas superficiales, lineas finas y unifica el tono y la textura de la piel.',
        's_duracionMinutos' => 45,
        's_precio' => 18000.00,
        's_activo' => 1,
        's_orden' => 6,
        'imagenes' => [
            'servicioPeelingQumicoRenovador_6ad6cd274279d.webp',
            'servicioLimpiezaFacialProfunda_6ad6cb2a97a55.webp'
        ],
        'profesionales' => ['Dra. Valeria Méndez', 'Camila Solís']
    ],
    [
        's_nbre' => 'Drenaje Linfático Manual',
        's_desc' => 'Masaje de maniobras suaves, lentas y repetitivas que activa la microcirculacion y el sistema linfatico. Ideal para retencion de liquidos, piernas pesadas, descanso post-quirurgico o bienestar general.',
        's_duracionMinutos' => 60,
        's_precio' => 18500.00,
        's_activo' => 1,
        's_orden' => 7,
        'imagenes' => [
            'servicioDrenajeLinfticoManual_6ad6cd41b813f.webp',
            'servicioMasajescuerpocompleto_6ab2906693494.webp'
        ],
        'profesionales' => ['Dra. Valeria Méndez', 'Inés Benítez']
    ],
    [
        's_nbre' => 'Spa Facial Anti-Age',
        's_desc' => 'Protocolo facial premium rejuvenecedor con radiofrecuencia tripolar estimuladora de colageno, activos tensores, serum de acido hialuronico liposomado y velo oclusivo de colageno puro para luminosidad inmediata.',
        's_duracionMinutos' => 90,
        's_precio' => 24000.00,
        's_activo' => 1,
        's_orden' => 8,
        'imagenes' => [
            'servicioSpaFacialAntiAge_6ad6cd5dce82f.webp',
            'servicioLimpiezaFacialProfunda_6ad6cb2a97a55.webp'
        ],
        'profesionales' => ['Maru', 'Dra. Valeria Méndez']
    ],
    [
        's_nbre' => 'Tratamiento Capilar Keratina (Inactivo)',
        's_desc' => 'Cauterizacion y nutricion capilar profunda para reduccion de encrespamiento y sellado de cuticula capilar.',
        's_duracionMinutos' => 90,
        's_precio' => 22000.00,
        's_activo' => 0,
        's_orden' => 9,
        'imagenes' => [
            'servicioTratamientoCapilarKeratinaInactivo_6ad6cd78a7641.webp'
        ],
        'profesionales' => ['Maru']
    ]
];

$serviciosMap = [];
$baseUrl = 'http://localhost/HerBeautyCenter/';

foreach ($serviciosData as $s) {
    $insS = $mysqli->prepare("INSERT INTO servicio (s_nbre, s_desc, s_duracionMinutos, s_precio, s_activo, s_orden) VALUES (?, ?, ?, ?, ?, ?)");
    $insS->bind_param("ssiddi", $s['s_nbre'], $s['s_desc'], $s['s_duracionMinutos'], $s['s_precio'], $s['s_activo'], $s['s_orden']);
    $insS->execute();
    $sId = (int)$insS->insert_id;
    $serviciosMap[$s['s_nbre']] = $sId;
    echo "Servicio insertado: {$s['s_nbre']} (ID: {$sId})\n";

    // Insertar imagenes
    $ordenImg = 1;
    foreach ($s['imagenes'] as $imgFile) {
        $imgPath = $baseUrl . 'assets/media/Servicios/' . $imgFile;
        $insImg = $mysqli->prepare("INSERT INTO imagen (img_sId, img_orden, img_ruta) VALUES (?, ?, ?)");
        $insImg->bind_param("iis", $sId, $ordenImg, $imgPath);
        $insImg->execute();
        $ordenImg++;
    }
    echo "  -> " . count($s['imagenes']) . " imagenes asociadas al servicio {$s['s_nbre']}\n";

    // Asignar profesionales
    foreach ($s['profesionales'] as $profName) {
        $pId = $profesionales[$profName] ?? null;
        if ($pId) {
            $mysqli->query("INSERT IGNORE INTO profesional_servicio (p_id, p_sId) VALUES ($pId, $sId)");
            echo "  -> Asignado a profesional {$profName} (P_ID: {$pId})\n";
        }
    }
}

// 6. Generacion de Horarios (Disponibilidades) y Turnos
echo "\n--- GENERANDO HORARIOS Y TURNOS ---\n";

$dias = [
    'hace_5d'  => date('Y-m-d', strtotime('-5 days')),
    'hace_3d'  => date('Y-m-d', strtotime('-3 days')),
    'anteayer' => date('Y-m-d', strtotime('-2 days')),
    'ayer'     => date('Y-m-d', strtotime('-1 day')),
    'hoy'      => date('Y-m-d', strtotime('today')),
    'manana'   => date('Y-m-d', strtotime('+1 day')),
    'dia_2'    => date('Y-m-d', strtotime('+2 days')),
    'dia_3'    => date('Y-m-d', strtotime('+3 days')),
    'dia_4'    => date('Y-m-d', strtotime('+4 days')),
    'dia_5'    => date('Y-m-d', strtotime('+5 days')),
    'dia_6'    => date('Y-m-d', strtotime('+6 days')),
    'dia_7'    => date('Y-m-d', strtotime('+7 days')),
];

$maruPId   = $profesionales['Maru'] ?? 2;
$camilaPId = $profesionales['Camila Solís'] ?? 3;
$valeriaPId= $profesionales['Dra. Valeria Méndez'] ?? 4;
$florPId   = $profesionales['Florencia Romero'] ?? 5;

// Definición de agenda completa con todos los estados del ciclo de vida
$agenda = [
    // --- TURNOS PASADOS (REALIZADOS) ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['hace_5d'],
        'desde' => '09:00:00',
        'hasta' => '10:00:00',
        'estado' => 'Realizado',
        'cliente' => 'Lucía Fernández',
        'abonado' => 1,
        'monto' => 16500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['hace_3d'],
        'desde' => '15:00:00',
        'hasta' => '16:30:00',
        'estado' => 'Realizado',
        'cliente' => 'Sofía Martínez',
        'abonado' => 1,
        'monto' => 24000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $camilaPId,
        'servicio' => 'Lifting y Tinte de Pestañas',
        'fecha' => $dias['anteayer'],
        'desde' => '11:00:00',
        'hasta' => '11:45:00',
        'estado' => 'Realizado',
        'cliente' => 'Valentina Rossi',
        'abonado' => 1,
        'monto' => 13500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['ayer'],
        'desde' => '16:00:00',
        'hasta' => '16:50:00',
        'estado' => 'Realizado',
        'cliente' => 'Agustina Gómez',
        'abonado' => 1,
        'monto' => 19000.00,
        'motivo' => null
    ],

    // --- HOY ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['hoy'],
        'desde' => '09:00:00',
        'hasta' => '10:00:00',
        'estado' => 'Confirmado',
        'cliente' => 'Carolina Herrera',
        'abonado' => 1,
        'monto' => 16500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['hoy'],
        'desde' => '11:30:00',
        'hasta' => '13:00:00',
        'estado' => 'Solicitado',
        'cliente' => 'Martina Díaz',
        'abonado' => 0,
        'monto' => 24000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['hoy'],
        'desde' => '16:00:00',
        'hasta' => '16:50:00',
        'estado' => 'Disponible', // Horario libre publicado
        'cliente' => null,
        'abonado' => 0,
        'monto' => 19000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Lifting y Tinte de Pestañas',
        'fecha' => $dias['hoy'],
        'desde' => '17:30:00',
        'hasta' => '18:15:00',
        'estado' => 'Disponible', // Horario libre publicado
        'cliente' => null,
        'abonado' => 0,
        'monto' => 13500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $camilaPId,
        'servicio' => 'Perfilado y Laminado de Cejas',
        'fecha' => $dias['hoy'],
        'desde' => '14:00:00',
        'hasta' => '14:40:00',
        'estado' => 'Confirmado',
        'cliente' => 'Lucía Fernández',
        'abonado' => 1,
        'monto' => 12000.00,
        'motivo' => null
    ],

    // --- MAÑANA ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['manana'],
        'desde' => '10:00:00',
        'hasta' => '10:50:00',
        'estado' => 'Confirmado',
        'cliente' => 'Valentina Rossi',
        'abonado' => 1,
        'monto' => 19000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['manana'],
        'desde' => '14:30:00',
        'hasta' => '15:30:00',
        'estado' => 'Cancelado',
        'cliente' => 'Agustina Gómez',
        'abonado' => 0,
        'monto' => 16500.00,
        'motivo' => 'Viaje imprevisto de la paciente fuera de la provincia.'
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['manana'],
        'desde' => '16:30:00',
        'hasta' => '18:00:00',
        'estado' => 'Disponible', // Horario libre
        'cliente' => null,
        'abonado' => 0,
        'monto' => 24000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $valeriaPId,
        'servicio' => 'Drenaje Linfático Manual',
        'fecha' => $dias['manana'],
        'desde' => '09:00:00',
        'hasta' => '10:00:00',
        'estado' => 'Solicitado',
        'cliente' => 'Sofía Martínez',
        'abonado' => 0,
        'monto' => 18500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $florPId,
        'servicio' => 'Manicura Rusa con Semipermanente',
        'fecha' => $dias['manana'],
        'desde' => '15:00:00',
        'hasta' => '16:15:00',
        'estado' => 'Confirmado',
        'cliente' => 'Lucía Fernández',
        'abonado' => 1,
        'monto' => 15500.00,
        'motivo' => null
    ],

    // --- DIA +2 ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Lifting y Tinte de Pestañas',
        'fecha' => $dias['dia_2'],
        'desde' => '09:30:00',
        'hasta' => '10:15:00',
        'estado' => 'Solicitado',
        'cliente' => 'Carolina Herrera',
        'abonado' => 0,
        'monto' => 13500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['dia_2'],
        'desde' => '11:00:00',
        'hasta' => '11:50:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 19000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['dia_2'],
        'desde' => '15:00:00',
        'hasta' => '16:00:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 16500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $camilaPId,
        'servicio' => 'Perfilado y Laminado de Cejas',
        'fecha' => $dias['dia_2'],
        'desde' => '16:30:00',
        'hasta' => '17:10:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 12000.00,
        'motivo' => null
    ],

    // --- DIA +3 ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['dia_3'],
        'desde' => '15:00:00',
        'hasta' => '16:00:00',
        'estado' => 'Confirmado',
        'cliente' => 'Martina Díaz',
        'abonado' => 1,
        'monto' => 16500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['dia_3'],
        'desde' => '17:00:00',
        'hasta' => '18:30:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 24000.00,
        'motivo' => null
    ],

    // --- DIA +4 ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['dia_4'],
        'desde' => '10:00:00',
        'hasta' => '11:30:00',
        'estado' => 'Solicitado',
        'cliente' => 'Cliente Prueba',
        'abonado' => 0,
        'monto' => 24000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['dia_4'],
        'desde' => '14:30:00',
        'hasta' => '15:20:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 19000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $florPId,
        'servicio' => 'Manicura Rusa con Semipermanente',
        'fecha' => $dias['dia_4'],
        'desde' => '11:00:00',
        'hasta' => '12:15:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 15500.00,
        'motivo' => null
    ],

    // --- DIA +5 ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Limpieza Facial Profunda',
        'fecha' => $dias['dia_5'],
        'desde' => '10:00:00',
        'hasta' => '11:00:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 16500.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Lifting y Tinte de Pestañas',
        'fecha' => $dias['dia_5'],
        'desde' => '11:30:00',
        'hasta' => '12:15:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 13500.00,
        'motivo' => null
    ],

    // --- DIA +6 y +7 ---
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Masaje Descontracturante',
        'fecha' => $dias['dia_6'],
        'desde' => '15:30:00',
        'hasta' => '16:20:00',
        'estado' => 'Confirmado',
        'cliente' => 'Sofía Martínez',
        'abonado' => 1,
        'monto' => 19000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $maruPId,
        'servicio' => 'Spa Facial Anti-Age',
        'fecha' => $dias['dia_7'],
        'desde' => '10:00:00',
        'hasta' => '11:30:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 24000.00,
        'motivo' => null
    ],
    [
        'profesional_id' => $valeriaPId,
        'servicio' => 'Peeling Químico Renovador',
        'fecha' => $dias['dia_7'],
        'desde' => '16:00:00',
        'hasta' => '16:45:00',
        'estado' => 'Disponible',
        'cliente' => null,
        'abonado' => 0,
        'monto' => 18000.00,
        'motivo' => null
    ]
];

// Helper para encontrar C_ID
function findClientId($name, $map) {
    if (!$name) return null;
    if (isset($map[$name])) return $map[$name];
    $lower = mb_strtolower(trim($name));
    if (isset($map[$lower])) return $map[$lower];
    return null;
}

$horariosCount = 0;
$turnosCount = 0;

foreach ($agenda as $item) {
    $sId = $serviciosMap[$item['servicio']] ?? null;
    if (!$sId) {
        echo "Servicio no encontrado: {$item['servicio']}\n";
        continue;
    }

    $pId = $item['profesional_id'];

    // 1. Crear el registro en tabla horario
    $insH = $mysqli->prepare("INSERT INTO horario (h_pId, h_sId, h_fecha, h_horaDesde, h_horaHasta) VALUES (?, ?, ?, ?, ?)");
    $insH->bind_param("iisss", $pId, $sId, $item['fecha'], $item['desde'], $item['hasta']);
    if (!$insH->execute()) {
        echo "Error insertando horario: " . $mysqli->error . "\n";
        continue;
    }
    $hId = (int)$insH->insert_id;
    $horariosCount++;

    // 2. Si el item tiene un turno asociado (Solicitado, Confirmado, Realizado, Cancelado)
    if ($item['estado'] !== 'Disponible' && !empty($item['cliente'])) {
        $cId = findClientId($item['cliente'], $clientesMap);
        if ($cId) {
            $insT = $mysqli->prepare("INSERT INTO turno (t_hId, t_pId, t_cId, t_fecha, t_horaDesde, t_horaHasta, estado, abonado, monto, motivo_cancelacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insT->bind_param("iiissssids", $hId, $pId, $cId, $item['fecha'], $item['desde'], $item['hasta'], $item['estado'], $item['abonado'], $item['monto'], $item['motivo']);
            if ($insT->execute()) {
                $turnosCount++;
                echo "Turno creado: [{$item['estado']}] {$item['servicio']} - {$item['fecha']} {$item['desde']} ({$item['cliente']})\n";
            } else {
                echo "Error insertando turno: " . $mysqli->error . "\n";
            }
        } else {
            echo "No se encontro ID cliente para: {$item['cliente']}\n";
        }
    } else {
        echo "Disponibilidad libre publicada: {$item['servicio']} - {$item['fecha']} {$item['desde']}-{$item['hasta']}\n";
    }
}

echo "\n=== RESEED FINALIZADO CON EXITO ===\n";
echo "Total de servicios creados: " . count($serviciosMap) . "\n";
echo "Total de horarios (bloques) creados: " . $horariosCount . "\n";
echo "Total de turnos creados: " . $turnosCount . "\n";

$conteoTurnosEstado = $mysqli->query("SELECT estado, COUNT(*) as cant FROM turno GROUP BY estado");
echo "\nDistribucion de turnos por estado:\n";
while ($r = $conteoTurnosEstado->fetch_assoc()) {
    echo " - {$r['estado']}: {$r['cant']}\n";
}

$mysqli->close();
