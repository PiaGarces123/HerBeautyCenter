<?php
/**
 * Migración: Incremento N° 2
 * Her Beauty Center
 *
 * Agrega la columna motivo_cancelacion a la tabla turno,
 * y actualiza el ENUM de estados para incluir Cancelado.
 *
 * Ejecutar una sola vez desde phpMyAdmin o CLI.
 */
$host = "localhost"; $user = "root"; $pass = ""; $db = "her_beauty_center";
$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

// 1. Agregar estado Cancelado al ENUM
$sql1 = "ALTER TABLE turno MODIFY COLUMN estado ENUM('Disponible','Solicitado','Confirmado','Realizado','Cancelado') NOT NULL DEFAULT 'Disponible'";
if ($conn->query($sql1)) echo "<p>✓ Estado Cancelado agregado al ENUM.</p>";
else echo "<p>✗ Error: {$conn->error}</p>";

// 2. Agregar columna motivo_cancelacion si no existe
$check = $conn->query("SHOW COLUMNS FROM turno LIKE 'motivo_cancelacion'");
if ($check->num_rows === 0) {
    $sql2 = "ALTER TABLE turno ADD COLUMN motivo_cancelacion TEXT NULL AFTER abonado";
    if ($conn->query($sql2)) echo "<p>✓ Columna motivo_cancelacion agregada.</p>";
    else echo "<p>✗ Error: {$conn->error}</p>";
} else {
    echo "<p>✓ Columna motivo_cancelacion ya existe.</p>";
}

// 3. Renombrar columna 'monto' a 't_monto' si está como 'monto' (compatibilidad con documentación)
// En este proyecto la columna se llama 'monto' (createDatabase.php), lo dejamos así.
echo "<p>✓ Columna monto: sin cambios (ya es 'monto').</p>";

echo "<hr><h3>✓ Migración completada.</h3>";
$conn->close();
