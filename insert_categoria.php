<?php
$mysqli = new mysqli('localhost', 'root', '', 'her_beauty_center');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if (!$mysqli->query("INSERT IGNORE INTO categoria (id_categoria, nombre, descripcion) VALUES (1, 'General', 'Categoría general')")) {
    echo "Error: " . $mysqli->error;
} else {
    echo "OK";
}
$mysqli->close();
?>
