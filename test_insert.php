<?php
$mysqli = new mysqli('localhost', 'root', '', 'her_beauty_center');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if (!$mysqli->query("INSERT INTO servicio (id_categoria, nombre, duracion_minutos, precio, activo) VALUES (1, 'Test', 10, 100, 1)")) {
    echo "Error: " . $mysqli->error;
} else {
    echo "OK";
}
$mysqli->close();
?>
