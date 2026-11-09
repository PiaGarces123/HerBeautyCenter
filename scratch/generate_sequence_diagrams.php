<?php
/**
 * Generador de Diagramas de Secuencia en Ultra Alta Resolución (PNG)
 * Her Beauty Center — Capítulo 3: Modelos Dinámicos y de Comportamiento
 */

$fontRegular = 'C:/Windows/Fonts/segoeui.ttf';
$fontBold    = 'C:/Windows/Fonts/segoeuib.ttf';
$fontMono    = 'C:/Windows/Fonts/consola.ttf';
$fontMonoB   = 'C:/Windows/Fonts/consolab.ttf';

$outDir = 'c:/wamp64/www/HerBeautyCenter/docs/diagramas';
$outDirArtifact = 'C:/Users/skyla/.gemini/antigravity/brain/93136297-1cfc-4c9d-bb6c-25fdcc24b42d';

if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

function fillRoundedRect($im, $x1, $y1, $x2, $y2, $radius, $color) {
    imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

function drawRoundedRect($im, $x1, $y1, $x2, $y2, $radius, $color, $thickness = 1) {
    imagesetthickness($im, $thickness);
    imageline($im, $x1 + $radius, $y1, $x2 - $radius, $y1, $color);
    imageline($im, $x1 + $radius, $y2, $x2 - $radius, $y2, $color);
    imageline($im, $x1, $y1 + $radius, $x1, $y2 - $radius, $color);
    imageline($im, $x2, $y1 + $radius, $x2, $y2 - $radius, $color);
    imagearc($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, 180, 270, $color);
    imagearc($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, 270, 360, $color);
    imagearc($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, 90, 180, $color);
    imagearc($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, 0, 90, $color);
    imagesetthickness($im, 1);
}

function drawDashedVLine($im, $x, $y1, $y2, $color) {
    $dash = 8;
    $space = 6;
    for ($y = $y1; $y < $y2; $y += $dash + $space) {
        $end = min($y + $dash, $y2);
        imageline($im, $x, $y, $x, $end, $color);
    }
}

function drawArrow($im, $x1, $x2, $y, $color, $isReturn = false) {
    imagesetthickness($im, 2);
    if ($isReturn) {
        $dash = 6;
        $space = 4;
        $minX = min($x1, $x2);
        $maxX = max($x1, $x2);
        for ($x = $minX; $x < $maxX; $x += $dash + $space) {
            $end = min($x + $dash, $maxX);
            imageline($im, $x, $y, $end, $y, $color);
        }
    } else {
        imageline($im, $x1, $y, $x2, $y, $color);
    }

    $dir = ($x2 > $x1) ? 1 : -1;
    $arrowSize = 9;
    $pts = [
        $x2, $y,
        $x2 - $dir * $arrowSize, $y - 6,
        $x2 - $dir * $arrowSize, $y + 6
    ];
    if ($isReturn) {
        imageline($im, $x2, $y, $x2 - $dir * $arrowSize, $y - 6, $color);
        imageline($im, $x2, $y, $x2 - $dir * $arrowSize, $y + 6, $color);
    } else {
        imagefilledpolygon($im, $pts, $color);
    }
    imagesetthickness($im, 1);
}

// ============================================================
// 1. DIAGRAMA: CU04 - CONFIRMACIÓN DE RESERVA ATÓMICA
// ============================================================
function generateDiagramCU04($fontR, $fontB, $fontM, $fontMB, $outFiles) {
    $W = 1680;
    $H = 960;
    $im = imagecreatetruecolor($W, $H);

    $bg = imagecolorallocate($im, 253, 252, 250);
    $white = imagecolorallocate($im, 255, 255, 255);
    $brand = imagecolorallocate($im, 74, 44, 29);
    $textDark = imagecolorallocate($im, 35, 30, 28);
    $textMuted = imagecolorallocate($im, 110, 95, 88);
    $lineCol = imagecolorallocate($im, 215, 205, 198);
    $arrowCall = imagecolorallocate($im, 74, 44, 29);
    $arrowRet = imagecolorallocate($im, 40, 135, 80);
    $blockBg = imagecolorallocate($im, 246, 250, 254);
    $blockBorder = imagecolorallocate($im, 150, 185, 225);
    $blockTitleCol = imagecolorallocate($im, 30, 90, 160);

    imagefill($im, 0, 0, $bg);

    // Header
    fillRoundedRect($im, 40, 30, $W - 40, 100, 8, $white);
    drawRoundedRect($im, 40, 30, $W - 40, 100, 8, $lineCol, 1);
    imagettftext($im, 17, 0, 70, 65, $brand, $fontB, "DIAGRAMA DE SECUENCIA 1: CONFIRMACIÓN DE RESERVA ONLINE (CU04)");
    imagettftext($im, 11, 0, 70, 88, $textMuted, $fontR, "Prevención atómica de colisiones mediante transacciones ACID (InnoDB: BEGIN -> SELECT FOR UPDATE -> INSERT -> COMMIT)");

    $participants = [
        ['name' => "Clienta\nAutenticada", 'x' => 120],
        ['name' => "Frontend Modal\n(reservar.php)", 'x' => 380],
        ['name' => "AuthFilter\n(auth:cliente)", 'x' => 660],
        ['name' => "ReservasApi\n(Controlador)", 'x' => 960],
        ['name' => "HorarioModel &\nTurnoModel", 'x' => 1260],
        ['name' => "MySQL Database\n(InnoDB Engine)", 'x' => 1530],
    ];

    $topY = 130;
    $botY = $H - 70;

    // Recuadro de Transacción Atómica
    fillRoundedRect($im, 910, 430, 1630, 685, 8, $blockBg);
    drawRoundedRect($im, 910, 430, 1630, 685, 8, $blockBorder, 2);
    fillRoundedRect($im, 925, 437, 1330, 463, 4, $white);
    drawRoundedRect($im, 925, 437, 1330, 463, 4, $blockBorder, 1);
    imagettftext($im, 10, 0, 938, 455, $blockTitleCol, $fontB, "TRANSACCIÓN ATÓMICA ACID (MySQL InnoDB Engine)");

    foreach ($participants as $p) {
        $x = $p['x'];
        fillRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $white);
        drawRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $brand, 2);

        $lines = explode("\n", $p['name']);
        imagettftext($im, 11, 0, $x - 80, $topY + 24, $brand, $fontB, $lines[0]);
        if (isset($lines[1])) {
            imagettftext($im, 10, 0, $x - 80, $topY + 43, $textMuted, $fontR, $lines[1]);
        }

        drawDashedVLine($im, $x, $topY + 54, $botY, $lineCol);

        fillRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $white);
        drawRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $lineCol, 1);
        imagettftext($im, 10, 0, $x - 75, $botY + 24, $textMuted, $fontB, $lines[0]);
    }

    $steps = [
        [
            'from' => 0, 'to' => 1, 'y' => 220, 'num' => '1',
            'title' => 'Selecciona bloque libre y presiona "Confirmar Reserva"',
            'sub' => 'Datos: horario_id, consideraciones opcionales del servicio',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 1, 'to' => 2, 'y' => 275, 'num' => '2',
            'title' => 'POST /api/cliente/reservar',
            'sub' => 'Payload JSON + Headers: X-Requested-With, Cookie de Sesión HttpOnly',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 2, 'to' => 3, 'y' => 330, 'num' => '3',
            'title' => 'Valida sesión activa de clienta autenticada (Protección Anti-IDOR)',
            'sub' => 'Verifica role == "cliente" y extrae cliente_id desde sesión inmutable del servidor',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 3, 'to' => 4, 'y' => 375, 'num' => '4',
            'title' => 'Inicia proceso de reserva atómica ($db->transBegin())',
            'sub' => 'Establece nivel de aislamiento REPEATABLE READ en la conexión',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 4, 'to' => 5, 'y' => 490, 'num' => '5',
            'title' => 'SELECT * FROM horario WHERE h_id = ? FOR UPDATE',
            'sub' => 'Bloqueo exclusivo a nivel de fila (Row-Level Locking anti sobreventa)',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 5, 'to' => 4, 'y' => 545, 'num' => '6',
            'title' => 'Verifica disponibilidad (sin turno activo en Solicitado o Confirmado)',
            'sub' => 'Retorna registro libre del bloque horario solicitado',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 4, 'to' => 5, 'y' => 600, 'num' => '7',
            'title' => 'INSERT INTO turno (t_hId, t_pId, t_cId, estado, abonado, monto)',
            'sub' => 'Valores: estado="Solicitado", abonado=0, monto=s_precio (Clave única uq_turno_horario)',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 5, 'to' => 4, 'y' => 655, 'num' => '8',
            'title' => 'Persistencia exitosa en tabla turno + $db->transCommit()',
            'sub' => 'Se consolida la transacción y se libera el bloqueo de fila',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 4, 'to' => 3, 'y' => 705, 'num' => '9',
            'title' => 'Retorna identificador del turno generado (#ID: 142) y estado "Solicitado"',
            'sub' => 'Objeto de respuesta estructurado para el cliente',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 3, 'to' => 1, 'y' => 765, 'num' => '10',
            'title' => 'HTTP 201 Created -> { status: "ok", message: "Turno solicitado con éxito", ... }',
            'sub' => 'Payload JSON con ID de reserva, datos del turno y enlace de contacto WhatsApp',
            'isRet' => true, 'code' => true
        ],
        [
            'from' => 1, 'to' => 0, 'y' => 835, 'num' => '11',
            'title' => 'Cierra modal de reserva y despliega alerta SweetAlert2 de éxito',
            'sub' => 'Ofrece botones: "Ver en Mis Turnos" o "Contactar por WhatsApp"',
            'isRet' => true, 'code' => false
        ],
    ];

    foreach ($steps as $s) {
        $x1 = $participants[$s['from']]['x'];
        $x2 = $participants[$s['to']]['x'];
        $y = $s['y'];
        $col = $s['isRet'] ? $arrowRet : $arrowCall;

        drawArrow($im, $x1, $x2, $y, $col, $s['isRet']);

        $midX = min($x1, $x2) + 15;
        $numStr = "[" . $s['num'] . "] ";
        imagettftext($im, 10, 0, $midX, $y - 7, $brand, $fontB, $numStr . $s['title']);
        imagettftext($im, 9, 0, $midX + 24, $y + 16, $textMuted, $s['code'] ? $fontM : $fontR, $s['sub']);
    }

    foreach ($outFiles as $file) {
        imagepng($im, $file);
    }
    imagedestroy($im);
}

// ============================================================
// 2. DIAGRAMA: CU05 - CANCELACIÓN AUTÓNOMA DE TURNO
// ============================================================
function generateDiagramCU05($fontR, $fontB, $fontM, $fontMB, $outFiles) {
    $W = 1680;
    $H = 920;
    $im = imagecreatetruecolor($W, $H);

    $bg = imagecolorallocate($im, 253, 252, 250);
    $white = imagecolorallocate($im, 255, 255, 255);
    $brand = imagecolorallocate($im, 74, 44, 29);
    $textDark = imagecolorallocate($im, 35, 30, 28);
    $textMuted = imagecolorallocate($im, 110, 95, 88);
    $lineCol = imagecolorallocate($im, 215, 205, 198);
    $arrowCall = imagecolorallocate($im, 74, 44, 29);
    $arrowRet = imagecolorallocate($im, 40, 135, 80);

    imagefill($im, 0, 0, $bg);

    fillRoundedRect($im, 40, 30, $W - 40, 100, 8, $white);
    drawRoundedRect($im, 40, 30, $W - 40, 100, 8, $lineCol, 1);
    imagettftext($im, 17, 0, 70, 65, $brand, $fontB, "DIAGRAMA DE SECUENCIA 2: CANCELACIÓN AUTÓNOMA DE TURNO (CU05)");
    imagettftext($im, 11, 0, 70, 88, $textMuted, $fontR, "Aislamiento de titularidad anti-IDOR, verificación de estados mutables y liberación del cupo horario (RN19, RN21, RN22)");

    $participants = [
        ['name' => "Clienta\nAutenticada", 'x' => 130],
        ['name' => "Vista Mis Turnos\n(mis_turnos.php)", 'x' => 420],
        ['name' => "AuthFilter &\nSession Context", 'x' => 740],
        ['name' => "ReservasApi\n(cancelarTurno)", 'x' => 1070],
        ['name' => "TurnoModel &\nMySQL InnoDB", 'x' => 1480],
    ];

    $topY = 130;
    $botY = $H - 70;

    foreach ($participants as $p) {
        $x = $p['x'];
        fillRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $white);
        drawRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $brand, 2);

        $lines = explode("\n", $p['name']);
        imagettftext($im, 11, 0, $x - 80, $topY + 24, $brand, $fontB, $lines[0]);
        if (isset($lines[1])) {
            imagettftext($im, 10, 0, $x - 80, $topY + 43, $textMuted, $fontR, $lines[1]);
        }

        drawDashedVLine($im, $x, $topY + 54, $botY, $lineCol);

        fillRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $white);
        drawRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $lineCol, 1);
        imagettftext($im, 10, 0, $x - 75, $botY + 24, $textMuted, $fontB, $lines[0]);
    }

    $steps = [
        [
            'from' => 0, 'to' => 1, 'y' => 220, 'num' => '1',
            'title' => 'Presiona "Cancelar Turno" e ingresa motivo en modal SweetAlert2',
            'sub' => 'Confirma acción irreversible de cancelación',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 1, 'to' => 2, 'y' => 285, 'num' => '2',
            'title' => 'POST /api/cliente/turnos/cancelar/{id}',
            'sub' => 'Payload: { motivo: "Imprevisto personal" } + Cookie HttpOnly',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 2, 'to' => 3, 'y' => 350, 'num' => '3',
            'title' => 'Verifica autenticación y asocia session("cliente_id")',
            'sub' => 'Garantiza que la petición procede del titular verificado de la sesión',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 3, 'to' => 4, 'y' => 420, 'num' => '4',
            'title' => 'SELECT * FROM turno WHERE t_id = ? AND t_cId = ?',
            'sub' => 'Control Anti-IDOR: Impide acceder o manipular turnos de otros clientes',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 4, 'to' => 3, 'y' => 485, 'num' => '5',
            'title' => 'Retorna registro del turno si existe y pertenece a la clienta',
            'sub' => 'Si t_cId no coincide, la consulta retorna vacío (HTTP 403 Forbidden)',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 3, 'to' => 3, 'y' => 550, 'num' => '6',
            'title' => 'Valida RN22: estado actual in ["Solicitado", "Confirmado"]',
            'sub' => 'Rechaza la operación si el turno ya estuviera en estado terminal "Realizado" o "Cancelado"',
            'isRet' => false, 'code' => false, 'self' => true
        ],
        [
            'from' => 3, 'to' => 4, 'y' => 625, 'num' => '7',
            'title' => 'UPDATE turno SET estado = "Cancelado", motivo_cancelacion = ? WHERE t_id = ?',
            'sub' => 'Transición a estado terminal; el bloque horario (h_id) queda disponible para nuevas reservas (RN19)',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 4, 'to' => 3, 'y' => 690, 'num' => '8',
            'title' => 'Confirmación de persistencia y actualización de estado',
            'sub' => 'Fila actualizada exitosamente en base de datos',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 3, 'to' => 1, 'y' => 755, 'num' => '9',
            'title' => 'HTTP 200 OK -> { status: "ok", message: "Turno cancelado correctamente" }',
            'sub' => 'Payload JSON con mensaje de confirmación',
            'isRet' => true, 'code' => true
        ],
        [
            'from' => 1, 'to' => 0, 'y' => 825, 'num' => '10',
            'title' => 'Actualiza badge a "Cancelado", deshabilita botón y muestra SweetAlert2',
            'sub' => 'La grilla de Mis Turnos refleja el nuevo estado terminal',
            'isRet' => true, 'code' => false
        ],
    ];

    foreach ($steps as $s) {
        $x1 = $participants[$s['from']]['x'];
        $x2 = $participants[$s['to']]['x'];
        $y = $s['y'];
        $col = $s['isRet'] ? $arrowRet : $arrowCall;

        if (isset($s['self']) && $s['self']) {
            imagesetthickness($im, 2);
            imageline($im, $x1, $y - 10, $x1 + 45, $y - 10, $col);
            imageline($im, $x1 + 45, $y - 10, $x1 + 45, $y + 12, $col);
            imageline($im, $x1 + 45, $y + 12, $x1, $y + 12, $col);
            $pts = [$x1, $y + 12, $x1 + 8, $y + 7, $x1 + 8, $y + 17];
            imagefilledpolygon($im, $pts, $col);
            imagesetthickness($im, 1);
            $midX = $x1 + 55;
        } else {
            drawArrow($im, $x1, $x2, $y, $col, $s['isRet']);
            $midX = min($x1, $x2) + 15;
        }

        $numStr = "[" . $s['num'] . "] ";
        imagettftext($im, 10, 0, $midX, $y - 7, $brand, $fontB, $numStr . $s['title']);
        imagettftext($im, 9, 0, $midX + 24, $y + 16, $textMuted, $s['code'] ? $fontM : $fontR, $s['sub']);
    }

    foreach ($outFiles as $file) {
        imagepng($im, $file);
    }
    imagedestroy($im);
}

// ============================================================
// 3. DIAGRAMA: RFC 5545 - GOOGLE CALENDAR ZERO-TOKEN
// ============================================================
function generateDiagramRFC5545($fontR, $fontB, $fontM, $fontMB, $outFiles) {
    $W = 1680;
    $H = 880;
    $im = imagecreatetruecolor($W, $H);

    $bg = imagecolorallocate($im, 253, 252, 250);
    $white = imagecolorallocate($im, 255, 255, 255);
    $brand = imagecolorallocate($im, 74, 44, 29);
    $textDark = imagecolorallocate($im, 35, 30, 28);
    $textMuted = imagecolorallocate($im, 110, 95, 88);
    $lineCol = imagecolorallocate($im, 215, 205, 198);
    $arrowCall = imagecolorallocate($im, 74, 44, 29);
    $arrowRet = imagecolorallocate($im, 40, 135, 80);
    $arrowExt = imagecolorallocate($im, 26, 115, 232);

    imagefill($im, 0, 0, $bg);

    fillRoundedRect($im, 40, 30, $W - 40, 100, 8, $white);
    drawRoundedRect($im, 40, 30, $W - 40, 100, 8, $lineCol, 1);
    imagettftext($im, 17, 0, 70, 65, $brand, $fontB, "DIAGRAMA DE SECUENCIA 3: EXPORTACIÓN RFC 5545 A GOOGLE CALENDAR");
    imagettftext($im, 11, 0, 70, 88, $textMuted, $fontR, "Enfoque Zero-Token sin almacenamiento de credenciales ni tokens OAuth 2.0 en el servidor");

    $participants = [
        ['name' => "Usuaria / Profesional\n(Navegador Web)", 'type' => 'actor', 'x' => 140],
        ['name' => "Vista Detalle Turno\n(Modal / Ficha)", 'type' => 'ui', 'x' => 460],
        ['name' => "TurnosApi / Perfil\n(Servidor Backend)", 'type' => 'ctrl', 'x' => 840],
        ['name' => "MySQL Database\n(Tabla turno + horario)", 'type' => 'db', 'x' => 1210],
        ['name' => "Google Calendar\n(calendar.google.com)", 'type' => 'ext', 'x' => 1520],
    ];

    $topY = 130;
    $botY = $H - 70;

    foreach ($participants as $p) {
        $x = $p['x'];
        fillRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $white);
        drawRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, ($p['type'] === 'ext') ? $arrowExt : $brand, 2);

        $lines = explode("\n", $p['name']);
        imagettftext($im, 11, 0, $x - 80, $topY + 24, ($p['type'] === 'ext') ? $arrowExt : $brand, $fontB, $lines[0]);
        if (isset($lines[1])) {
            imagettftext($im, 10, 0, $x - 80, $topY + 43, $textMuted, $fontR, $lines[1]);
        }

        drawDashedVLine($im, $x, $topY + 54, $botY, $lineCol);

        fillRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $white);
        drawRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $lineCol, 1);
        imagettftext($im, 10, 0, $x - 75, $botY + 24, $textMuted, $fontB, $lines[0]);
    }

    $steps = [
        [
            'from' => 0, 'to' => 1, 'y' => 220, 'num' => '1',
            'title' => 'Clic en botón "Sincronizar con Google Calendar"',
            'sub' => 'Disponible para turnos confirmados (Profesional asignada o Clienta titular)',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 1, 'to' => 2, 'y' => 290, 'num' => '2',
            'title' => 'GET /api/turnos/google-calendar/{id}',
            'sub' => 'Petición asíncrona mediante fetch() con sesión autenticada',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 2, 'to' => 3, 'y' => 360, 'num' => '3',
            'title' => 'SELECT t.*, h.*, s.s_nbre, u.u_nbreCompleto FROM turno t ...',
            'sub' => 'Recupera fecha, rango horario (h_horaDesde - h_horaHasta), servicio y contacto',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 3, 'to' => 2, 'y' => 430, 'num' => '4',
            'title' => 'Retorna registro con metadatos del evento',
            'sub' => 'Datos para formateo según especificación estándar RFC 5545',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 2, 'to' => 2, 'y' => 505, 'num' => '5',
            'title' => 'Construcción de URL estructurada RFC 5545 en memoria',
            'sub' => 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=...&dates=YYYYMMDDTHHMMSSZ/...',
            'isRet' => false, 'code' => true, 'self' => true
        ],
        [
            'from' => 2, 'to' => 1, 'y' => 580, 'num' => '6',
            'title' => 'HTTP 200 OK -> { status: "ok", url: "https://calendar.google.com/..." }',
            'sub' => 'Payload JSON sin almacenar ni transferir tokens OAuth 2.0 (Zero-Token Approach)',
            'isRet' => true, 'code' => true
        ],
        [
            'from' => 1, 'to' => 4, 'y' => 660, 'num' => '7',
            'title' => 'window.open(url, "_blank") -> Apertura de Google Calendar oficial',
            'sub' => 'El navegador de la usuaria se autentica directamente en su cuenta Google personal',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 4, 'to' => 0, 'y' => 745, 'num' => '8',
            'title' => 'Google Calendar pre-completa título, horario, salón y notas del tratamiento',
            'sub' => 'La usuaria pulsa "Guardar" y la cita queda registrada en su calendario personal',
            'isRet' => true, 'code' => false
        ],
    ];

    foreach ($steps as $s) {
        $x1 = $participants[$s['from']]['x'];
        $x2 = $participants[$s['to']]['x'];
        $y = $s['y'];
        $col = ($s['to'] === 4 || $s['from'] === 4) ? $arrowExt : ($s['isRet'] ? $arrowRet : $arrowCall);

        if (isset($s['self']) && $s['self']) {
            imagesetthickness($im, 2);
            imageline($im, $x1, $y - 10, $x1 + 45, $y - 10, $col);
            imageline($im, $x1 + 45, $y - 10, $x1 + 45, $y + 12, $col);
            imageline($im, $x1 + 45, $y + 12, $x1, $y + 12, $col);
            $pts = [$x1, $y + 12, $x1 + 8, $y + 7, $x1 + 8, $y + 17];
            imagefilledpolygon($im, $pts, $col);
            imagesetthickness($im, 1);
            $midX = $x1 + 55;
        } else {
            drawArrow($im, $x1, $x2, $y, $col, $s['isRet']);
            $midX = min($x1, $x2) + 15;
        }

        $numStr = "[" . $s['num'] . "] ";
        imagettftext($im, 10, 0, $midX, $y - 7, ($s['to'] === 4 || $s['from'] === 4) ? $arrowExt : $brand, $fontB, $numStr . $s['title']);
        imagettftext($im, 9, 0, $midX + 24, $y + 16, $textMuted, $s['code'] ? $fontM : $fontR, $s['sub']);
    }

    foreach ($outFiles as $file) {
        imagepng($im, $file);
    }
    imagedestroy($im);
}

// ============================================================
// 4. DIAGRAMA: API RESTFUL INTERNA DE EVENTOS FULLCALENDAR V6
// ============================================================
function generateDiagramFullCalendarAPI($fontR, $fontB, $fontM, $fontMB, $outFiles) {
    $W = 1680;
    $H = 880;
    $im = imagecreatetruecolor($W, $H);

    $bg = imagecolorallocate($im, 253, 252, 250);
    $white = imagecolorallocate($im, 255, 255, 255);
    $brand = imagecolorallocate($im, 74, 44, 29);
    $textDark = imagecolorallocate($im, 35, 30, 28);
    $textMuted = imagecolorallocate($im, 110, 95, 88);
    $lineCol = imagecolorallocate($im, 215, 205, 198);
    $arrowCall = imagecolorallocate($im, 74, 44, 29);
    $arrowRet = imagecolorallocate($im, 40, 135, 80);

    imagefill($im, 0, 0, $bg);

    fillRoundedRect($im, 40, 30, $W - 40, 100, 8, $white);
    drawRoundedRect($im, 40, 30, $W - 40, 100, 8, $lineCol, 1);
    imagettftext($im, 17, 0, 70, 65, $brand, $fontB, "DIAGRAMA DE SECUENCIA 4: API RESTFUL DE EVENTOS Y FULLCALENDAR V6");
    imagettftext($im, 11, 0, 70, 88, $textMuted, $fontR, "Alimentación asíncrona de eventos temporales por rango de fechas (start, end) y codificación JSON");

    $participants = [
        ['name' => "Profesional / Admin\n(Navegador Web)", 'type' => 'actor', 'x' => 140],
        ['name' => "FullCalendar v6\n(agenda.js)", 'type' => 'ui', 'x' => 460],
        ['name' => "AuthFilter\n(auth:profesional)", 'type' => 'filter', 'x' => 810],
        ['name' => "HorariosApi\n(getEventos)", 'type' => 'ctrl', 'x' => 1160],
        ['name' => "HorarioModel &\nMySQL InnoDB", 'type' => 'db', 'x' => 1510],
    ];

    $topY = 130;
    $botY = $H - 70;

    foreach ($participants as $p) {
        $x = $p['x'];
        fillRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $white);
        drawRoundedRect($im, $x - 95, $topY, $x + 95, $topY + 54, 6, $brand, 2);

        $lines = explode("\n", $p['name']);
        imagettftext($im, 11, 0, $x - 80, $topY + 24, $brand, $fontB, $lines[0]);
        if (isset($lines[1])) {
            imagettftext($im, 10, 0, $x - 80, $topY + 43, $textMuted, $fontR, $lines[1]);
        }

        drawDashedVLine($im, $x, $topY + 54, $botY, $lineCol);

        fillRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $white);
        drawRoundedRect($im, $x - 90, $botY, $x + 90, $botY + 38, 4, $lineCol, 1);
        imagettftext($im, 10, 0, $x - 75, $botY + 24, $textMuted, $fontB, $lines[0]);
    }

    $steps = [
        [
            'from' => 0, 'to' => 1, 'y' => 220, 'num' => '1',
            'title' => 'Accede a la vista de Agenda / Cambia de mes o semana',
            'sub' => 'Dispara automáticamente el hook asíncrono events() de FullCalendar v6',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 1, 'to' => 2, 'y' => 290, 'num' => '2',
            'title' => 'GET /api/horarios/eventos?start=2026-11-01&end=2026-12-01',
            'sub' => 'Petición AJAX asíncrona enviando los parámetros de intervalo visible',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 2, 'to' => 3, 'y' => 360, 'num' => '3',
            'title' => 'Pasa petición con contexto de profesional autenticada',
            'sub' => 'Session::get("profesional_id") previene acceso a agendas de otros miembros del staff',
            'isRet' => false, 'code' => false
        ],
        [
            'from' => 3, 'to' => 4, 'y' => 430, 'num' => '4',
            'title' => 'SELECT h.*, s.s_nbre, t.estado FROM horario h LEFT JOIN turno t ...',
            'sub' => 'Filtra por h_pId = ? AND h_fecha BETWEEN start AND end',
            'isRet' => false, 'code' => true
        ],
        [
            'from' => 4, 'to' => 3, 'y' => 500, 'num' => '5',
            'title' => 'Retorna array de franjas horarias y turnos vinculados',
            'sub' => 'Registros conteniendo fecha, intervalo horario y estado del ciclo de vida',
            'isRet' => true, 'code' => false
        ],
        [
            'from' => 3, 'to' => 3, 'y' => 575, 'num' => '6',
            'title' => 'Mapea a estructura estándar de Event Object de FullCalendar',
            'sub' => 'Transforma a: { id, title, start: "Y-m-d H:i", end, color, extendedProps }',
            'isRet' => false, 'code' => true, 'self' => true
        ],
        [
            'from' => 3, 'to' => 1, 'y' => 655, 'num' => '7',
            'title' => 'HTTP 200 OK -> Array JSON con eventos coloreados por estado',
            'sub' => 'Verde: Disponible | Amarillo: Solicitado | Terracota: Confirmado | Gris: Realizado',
            'isRet' => true, 'code' => true
        ],
        [
            'from' => 1, 'to' => 0, 'y' => 735, 'num' => '8',
            'title' => 'FullCalendar renderiza bloques interactivos en la grilla temporal',
            'sub' => 'Permite interacción táctil o clics para ver detalle, editar o gestionar el turno',
            'isRet' => true, 'code' => false
        ],
    ];

    foreach ($steps as $s) {
        $x1 = $participants[$s['from']]['x'];
        $x2 = $participants[$s['to']]['x'];
        $y = $s['y'];
        $col = $s['isRet'] ? $arrowRet : $arrowCall;

        if (isset($s['self']) && $s['self']) {
            imagesetthickness($im, 2);
            imageline($im, $x1, $y - 10, $x1 + 45, $y - 10, $col);
            imageline($im, $x1 + 45, $y - 10, $x1 + 45, $y + 12, $col);
            imageline($im, $x1 + 45, $y + 12, $x1, $y + 12, $col);
            $pts = [$x1, $y + 12, $x1 + 8, $y + 7, $x1 + 8, $y + 17];
            imagefilledpolygon($im, $pts, $col);
            imagesetthickness($im, 1);
            $midX = $x1 + 55;
        } else {
            drawArrow($im, $x1, $x2, $y, $col, $s['isRet']);
            $midX = min($x1, $x2) + 15;
        }

        $numStr = "[" . $s['num'] . "] ";
        imagettftext($im, 10, 0, $midX, $y - 7, $brand, $fontB, $numStr . $s['title']);
        imagettftext($im, 9, 0, $midX + 24, $y + 16, $textMuted, $s['code'] ? $fontM : $fontR, $s['sub']);
    }

    foreach ($outFiles as $file) {
        imagepng($im, $file);
    }
    imagedestroy($im);
}

// Regenerar todo
echo "Regenerando Diagramas con espaciado perfecto...\n";
generateDiagramCU04($fontRegular, $fontBold, $fontMono, $fontMonoB, [
    "$outDir/Secuencia_CU04_Reserva_Online.png",
    "$outDirArtifact/Secuencia_CU04_Reserva_Online.png"
]);

generateDiagramCU05($fontRegular, $fontBold, $fontMono, $fontMonoB, [
    "$outDir/Secuencia_CU05_Cancelacion_Turno.png",
    "$outDirArtifact/Secuencia_CU05_Cancelacion_Turno.png"
]);

generateDiagramRFC5545($fontRegular, $fontBold, $fontMono, $fontMonoB, [
    "$outDir/Secuencia_RFC5545_GoogleCalendar.png",
    "$outDirArtifact/Secuencia_RFC5545_GoogleCalendar.png"
]);

generateDiagramFullCalendarAPI($fontRegular, $fontBold, $fontMono, $fontMonoB, [
    "$outDir/Secuencia_FullCalendar_API.png",
    "$outDirArtifact/Secuencia_FullCalendar_API.png"
]);

echo "LISTO.\n";
