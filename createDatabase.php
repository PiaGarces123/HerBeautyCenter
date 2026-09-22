<?php

/**
 * ============================================================
 * HER BEAUTY CENTER
 * Script de creación de Base de Datos
 * ============================================================
 *
 * Base de datos: her_beauty_center
 * Motor: MySQL
 * Conexión: MySQLi
 *
 * Este archivo crea:
 *
 * 1. usuario
 * 2. profesional
 * 3. cliente
 * 4. administrador
 * 5. servicio
 * 6. profesional_servicio
 * 7. red_social
 * 8. imagen
 * 9. horario
 * 10. turno
 *
 * ============================================================
 */

// ------------------------------------------------------------
// CONFIGURACIÓN DE CONEXIÓN
// ------------------------------------------------------------

$host = "localhost";
$username = "root";
$password = "";
$database = "her_beauty_center";


// ------------------------------------------------------------
// CONEXIÓN CON MYSQL
// ------------------------------------------------------------

$conn = new mysqli($host, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die(
        "Error de conexión con MySQL: " .
        $conn->connect_error
    );
}

// Utilizar UTF-8
$conn->set_charset("utf8mb4");

echo "<h2>Her Beauty Center - Creación de Base de Datos</h2>";


// ------------------------------------------------------------
// CREAR BASE DE DATOS
// ------------------------------------------------------------

$sql = "
    CREATE DATABASE IF NOT EXISTS `$database`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Base de datos <strong>$database</strong> creada o ya existente.</p>";
} else {
    die(
        "<p>✗ Error al crear la base de datos: " .
        $conn->error .
        "</p>"
    );
}


// ------------------------------------------------------------
// SELECCIONAR BASE DE DATOS
// ------------------------------------------------------------

if (!$conn->select_db($database)) {
    die(
        "<p>✗ No se pudo seleccionar la base de datos.</p>"
    );
}

echo "<p>✓ Base de datos seleccionada correctamente.</p>";


// ============================================================
// 1. TABLA USUARIO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS usuario (

    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre_completo VARCHAR(150) NOT NULL,

    correo VARCHAR(150) NOT NULL UNIQUE,

    telefono VARCHAR(30) NOT NULL,

    avatar VARCHAR(255),

    activo TINYINT(1) NOT NULL DEFAULT 1,

    password VARCHAR(255) NOT NULL,

    fecha_registro DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>usuario</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla usuario: {$conn->error}</p>";
}


// ============================================================
// 2. TABLA PROFESIONAL
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS profesional (

    id_profesional INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT UNSIGNED NOT NULL UNIQUE,

    titulo VARCHAR(100),

    descripcion TEXT,

    anio_inicio_actividades YEAR,

    CONSTRAINT fk_profesional_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>profesional</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla profesional: {$conn->error}</p>";
}


// ============================================================
// 3. TABLA CLIENTE
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS cliente (

    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT UNSIGNED NOT NULL UNIQUE,

    CONSTRAINT fk_cliente_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>cliente</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla cliente: {$conn->error}</p>";
}


// ============================================================
// 4. TABLA ADMINISTRADOR
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS administrador (

    id_administrador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_profesional INT UNSIGNED NOT NULL UNIQUE,

    CONSTRAINT fk_administrador_profesional
        FOREIGN KEY (id_profesional)
        REFERENCES profesional(id_profesional)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>administrador</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla administrador: {$conn->error}</p>";
}


// ============================================================
// 5. TABLA CATEGORIA
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS categoria (

    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL UNIQUE,

    descripcion TEXT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>categoria</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla categoria: {$conn->error}</p>";
}


// ============================================================
// 5.1. TABLA SERVICIO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS servicio (

    id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_categoria INT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NOT NULL,

    descripcion TEXT,

    duracion_minutos INT UNSIGNED NOT NULL,

    precio DECIMAL(10,2) NOT NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    orden INT NOT NULL DEFAULT 0,

    CONSTRAINT fk_servicio_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT chk_servicio_duracion
        CHECK (duracion_minutos > 0),

    CONSTRAINT chk_servicio_precio
        CHECK (precio >= 0)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>servicio</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla servicio: {$conn->error}</p>";
}


// ============================================================
// 6. TABLA PROFESIONAL_SERVICIO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS profesional_servicio (

    id_profesional INT UNSIGNED NOT NULL,

    id_servicio INT UNSIGNED NOT NULL,

    PRIMARY KEY (
        id_profesional,
        id_servicio
    ),

    CONSTRAINT fk_ps_profesional
        FOREIGN KEY (id_profesional)
        REFERENCES profesional(id_profesional)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ps_servicio
        FOREIGN KEY (id_servicio)
        REFERENCES servicio(id_servicio)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>profesional_servicio</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla profesional_servicio: {$conn->error}</p>";
}


// ============================================================
// 7. TABLA RED_SOCIAL
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS red_social (

    id_red_social INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_profesional INT UNSIGNED NOT NULL,

    tipo VARCHAR(50) NOT NULL,

    link VARCHAR(255) NOT NULL,

    CONSTRAINT fk_red_social_profesional
        FOREIGN KEY (id_profesional)
        REFERENCES profesional(id_profesional)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>red_social</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla red_social: {$conn->error}</p>";
}


// ============================================================
// 8. TABLA IMAGEN
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS imagen (

    id_imagen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_servicio INT UNSIGNED NOT NULL,

    ruta VARCHAR(255) NOT NULL,

    CONSTRAINT fk_imagen_servicio
        FOREIGN KEY (id_servicio)
        REFERENCES servicio(id_servicio)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>imagen</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla imagen: {$conn->error}</p>";
}


// ============================================================
// 9. TABLA HORARIO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS horario (

    id_horario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_profesional INT UNSIGNED NOT NULL,

    id_servicio INT UNSIGNED NOT NULL,

    fecha DATE NOT NULL,

    hora_desde TIME NOT NULL,

    hora_hasta TIME NOT NULL,

    CONSTRAINT fk_horario_profesional
        FOREIGN KEY (id_profesional)
        REFERENCES profesional(id_profesional)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_horario_servicio
        FOREIGN KEY (id_servicio)
        REFERENCES servicio(id_servicio)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_horario_horas
        CHECK (hora_hasta > hora_desde)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>horario</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla horario: {$conn->error}</p>";
}


// ============================================================
// 10. TABLA TURNO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS turno (

    id_turno INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_horario INT UNSIGNED NOT NULL UNIQUE,

    id_profesional INT UNSIGNED NOT NULL,

    id_cliente INT UNSIGNED NULL,

    fecha DATE NOT NULL,

    hora_desde TIME NOT NULL,

    hora_hasta TIME NOT NULL,

    estado ENUM(
        'Disponible',
        'Solicitado',
        'Confirmado',
        'Realizado'
    ) NOT NULL DEFAULT 'Disponible',

    abonado TINYINT(1) NOT NULL DEFAULT 0,

    monto DECIMAL(10,2) NOT NULL DEFAULT 0,

    CONSTRAINT fk_turno_horario
        FOREIGN KEY (id_horario)
        REFERENCES horario(id_horario)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_turno_profesional
        FOREIGN KEY (id_profesional)
        REFERENCES profesional(id_profesional)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_turno_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_turno_monto
        CHECK (monto >= 0),

    CONSTRAINT chk_turno_horas
        CHECK (hora_hasta > hora_desde)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "<p>✓ Tabla <strong>turno</strong> creada.</p>";
} else {
    echo "<p>✗ Error en tabla turno: {$conn->error}</p>";
}


// ============================================================
// FINALIZACIÓN
// ============================================================

echo "<hr>";

echo "<h3>✓ Proceso finalizado</h3>";

echo "
<p>
La base de datos <strong>$database</strong> fue configurada
correctamente.
</p>

<p>
Se crearon las tablas correspondientes al modelo de dominio
de <strong>Her Beauty Center</strong>.
</p>
";


// ------------------------------------------------------------
// CERRAR CONEXIÓN
// ------------------------------------------------------------

$conn->close();

?>