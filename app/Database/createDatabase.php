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

    u_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    u_nbreCompleto VARCHAR(150) NOT NULL,

    u_correo VARCHAR(150) NOT NULL UNIQUE,

    u_tel VARCHAR(30),

    u_avatar VARCHAR(255),

    u_activo TINYINT(1) NOT NULL DEFAULT 1,

    u_pass VARCHAR(255) NOT NULL,

    u_fRegistro DATETIME NOT NULL
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

    p_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    u_id INT UNSIGNED NOT NULL UNIQUE,

    p_titulo VARCHAR(100),

    p_desc TEXT,

    p_anioInicioAct YEAR,

    CONSTRAINT fk_profesional_usuario
        FOREIGN KEY (u_id)
        REFERENCES usuario(u_id)
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

    c_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    c_uId INT UNSIGNED NOT NULL UNIQUE,

    CONSTRAINT fk_cliente_usuario
        FOREIGN KEY (c_uId)
        REFERENCES usuario(u_id)
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

    a_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    a_pId INT UNSIGNED NOT NULL UNIQUE,

    CONSTRAINT fk_administrador_profesional
        FOREIGN KEY (a_pId)
        REFERENCES profesional(p_id)
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
// 5.1. TABLA SERVICIO
// ============================================================

$sql = "
CREATE TABLE IF NOT EXISTS servicio (

    s_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    s_nbre VARCHAR(100) NOT NULL,

    s_desc TEXT,

    s_duracionMinutos INT UNSIGNED NOT NULL,

    s_precio DECIMAL(10,2) NOT NULL,

    s_activo TINYINT(1) NOT NULL DEFAULT 1,

    s_orden INT NOT NULL DEFAULT 0,

    CONSTRAINT chk_servicio_duracion
        CHECK (s_duracionMinutos > 0),

    CONSTRAINT chk_servicio_precio
        CHECK (s_precio >= 0)

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

    p_id INT UNSIGNED NOT NULL,

    p_sId INT UNSIGNED NOT NULL,

    PRIMARY KEY (
        p_id,
        p_sId
    ),

    CONSTRAINT fk_ps_profesional
        FOREIGN KEY (p_id)
        REFERENCES profesional(p_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_ps_servicio
        FOREIGN KEY (p_sId)
        REFERENCES servicio(s_id)
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

    rs_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    rs_pId INT UNSIGNED NOT NULL,

    rs_tipo VARCHAR(50) NOT NULL,

    rs_link VARCHAR(255) NOT NULL,

    CONSTRAINT fk_red_social_profesional
        FOREIGN KEY (rs_pId)
        REFERENCES profesional(p_id)
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

    img_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    img_sId INT UNSIGNED NOT NULL,

    img_ruta VARCHAR(255) NOT NULL,

    CONSTRAINT fk_imagen_servicio
        FOREIGN KEY (img_sId)
        REFERENCES servicio(s_id)
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

    h_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    h_pId INT UNSIGNED NOT NULL,

    h_sId INT UNSIGNED NOT NULL,

    h_fecha DATE NOT NULL,

    h_horaDesde TIME NOT NULL,

    h_horaHasta TIME NOT NULL,

    CONSTRAINT fk_horario_profesional
        FOREIGN KEY (h_pId)
        REFERENCES profesional(p_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_horario_servicio
        FOREIGN KEY (h_sId)
        REFERENCES servicio(s_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT chk_horario_horas
        CHECK (h_horaHasta > h_horaDesde)

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

    t_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    t_hId INT UNSIGNED NOT NULL UNIQUE,

    t_pId INT UNSIGNED NOT NULL,

    t_cId INT UNSIGNED NULL,

    t_fecha DATE NOT NULL,

    t_horaDesde TIME NOT NULL,

    t_horaHasta TIME NOT NULL,

    estado ENUM(
        'Disponible',
        'Solicitado',
        'Confirmado',
        'Realizado'
    ) NOT NULL DEFAULT 'Disponible',

    abonado TINYINT(1) NOT NULL DEFAULT 0,

    monto DECIMAL(10,2) NOT NULL DEFAULT 0,

    CONSTRAINT fk_turno_horario
        FOREIGN KEY (t_hId)
        REFERENCES horario(h_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_turno_profesional
        FOREIGN KEY (t_pId)
        REFERENCES profesional(p_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_turno_cliente
        FOREIGN KEY (t_cId)
        REFERENCES cliente(c_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT chk_turno_monto
        CHECK (monto >= 0),

    CONSTRAINT chk_turno_horas
        CHECK (t_horaHasta > t_horaDesde)

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
