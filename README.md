# Her Beauty Center - Sistema de Gestión y Reserva de Turnos

Plataforma web integral para la administración, publicación de catálogo y reserva de turnos en centros de estética multidisciplinarios. Proyecto desarrollado en el marco de la asignatura **Práctica Técnica Supervisada** de la **Tecnicatura Universitaria en Web (TUW)**, Facultad de Ciencias Físico Matemáticas y Naturales (FCFMN), Universidad Nacional de San Luis (UNSL).

---

## 1. Requisitos del Entorno de Ejecución

Para desplegar y evaluar el sistema en un entorno local (WampServer, XAMPP, Laragon o servidor LAMP/LEMP), se requiere:

* **PHP:** Versión 8.2 o superior.
  * Extensiones PHP requeridas y activas: `mysqli`, `intl`, `mbstring`, `gd`, `json`.
* **Base de Datos:** MySQL 8.0 o superior (o MariaDB 10.4+) con soporte para el motor de almacenamiento transaccional **InnoDB**.
* **Servidor Web:** Apache 2.4 con el módulo `mod_rewrite` habilitado (permite URLs amigables vía `.htaccess`).
* **Gestor de Dependencias:** Composer (opcional si la carpeta `vendor/` ya se encuentra provista en la entrega).

---

## 2. Instalación y Puesta en Marcha (Paso a Paso)

### Paso 1: Ubicación del Repositorio
Copiar o clonar la carpeta del proyecto dentro del directorio raíz de su servidor web local:
* **WampServer:** `C:\wamp64\www\HerBeautyCenter`
* **XAMPP:** `C:\xampp\htdocs\HerBeautyCenter`
* **Laragon:** `C:\laragon\www\HerBeautyCenter`

*(Si la carpeta `vendor/` no estuviera incluida, abrir una terminal en la raíz del proyecto y ejecutar `composer install`).*

---

### Paso 2: Configuración de Variables de Entorno (.env)
El proyecto incluye un archivo `.env` preconfigurado para entornos locales de desarrollo con la base de datos `herbeautycenter_db`. Si es necesario ajustar credenciales personalizadas de MySQL, editar los parámetros en el archivo `.env`:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/HerBeautyCenter/'

database.default.hostname = localhost
database.default.database = herbeautycenter_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3306
```

---

### Paso 3: Creación de la Base de Datos y Esquema Relacional
El script `createDatabase.php` se encarga de crear de manera desatendida la base de datos `herbeautycenter_db` y las **10 tablas relacionales** con sus correspondientes claves foráneas protegidas (`ON DELETE CASCADE`, `ON DELETE RESTRICT`, `ON DELETE SET NULL`) y restricciones semánticas `CHECK`.

Puede ejecutarse mediante cualquiera de las siguientes dos opciones:

* **Opción A (Desde Terminal / Consola):**
  ```bash
  php app/Database/createDatabase.php
  ```

* **Opción B (Desde el Navegador Web):**
  Abrir la siguiente URL en el navegador con el servidor local encendido:
  ```text
  http://localhost/HerBeautyCenter/app/Database/createDatabase.php
  ```
  El script mostrará un reporte confirmando la creación exitosa de las tablas del dominio.

---

### Paso 4: Inyección de Datos Iniciales (Seeder)
Para dar de alta a la **Administradora General** del establecimiento y generar sus credenciales de acceso seguras con hash Bcrypt, ejecutar en la terminal desde la raíz del proyecto:

```bash
php spark db:seed AdminSeeder
```

El seeder registrará automáticamente el usuario base, su perfil profesional y su asignación con privilegios de Administrador.

---

## 3. Credenciales de Acceso para Pruebas

Una vez ejecutado el seeder, el profesor o evaluador puede ingresar de inmediato con las siguientes credenciales predeterminadas:

* **Rol de Acceso:** Administradora General / Profesional
* **Correo Electrónico:** `garcesbrocalmaru@gmail.com`
* **Contraseña:** `HH22oo..`
* **Acceso Directo al Login:** `http://localhost/HerBeautyCenter/?login=1`
* **Panel de Control Administrativo:** `http://localhost/HerBeautyCenter/admin`

*(Para probar el flujo de **Clienta**, se puede registrar un nuevo usuario en menos de 10 segundos desde el modal público de "Iniciar Sesión / Registrarse" o utilizar el asistente de reserva en línea).*

---

## 4. Guía de Navegación y Prueba de Incrementos

El sistema articula la totalidad de los requisitos funcionales organizados en tres incrementos:

### Incremento N.° 1: Configuración y Administración del Establecimiento
* **Gestión de Servicios (`/admin/servicios`):** Alta, edición, precios, duración y galería fotográfica de prestaciones estéticas.
* **Gestión de Profesionales (`/admin/profesionales`):** Administración del equipo técnico, perfiles y asignación matricial de servicios (relación N:M).
* **Padrón de Clientes (`/admin/clientes`):** Supervisión de clientas registradas e historial de atención.
* **Mi Perfil (`/admin/perfil`):** Actualización de biografía, redes sociales y encuadre interactivo de avatar circular (Canvas API nativa).

### Incremento N.° 2: Disponibilidad, Agenda y Turnos Internos
* **Mi Disponibilidad (`/admin/disponibilidad`):** Parametrización de bloques horarios por fecha y servicio con algoritmo de prevención de colisiones.
* **Agenda Interactiva FullCalendar v6 (`/admin/agenda` o `/admin/calendario`):** Visualización reactiva por mes, semana y día con eventos clasificados por estado mediante código de colores.
* **Gestión de Turnos (`/admin/turnos`):** Ciclo de vida del turno (`Solicitado` -> `Confirmado` -> `Realizado` / `Cancelado`), control de abonos monetarios y sincronización con Google Calendar bajo el estándar RFC 5545 (Zero-Token approach).

### Incremento N.° 3: Portal Público, Reservas Online y Autogestión
* **Landing Page y Catálogo Público (`/`):** Exploración editorial de tratamientos con carruseles fotográficos, perfiles del staff y canales de contacto.
* **Asistente Guiado de Reservas (Wizard):** Selección de tratamiento -> filtrado por especialista -> selección de fecha y franja horaria -> formulario de datos con consideraciones de atención en gabinete. Prevención de colisiones concurrentes bajo transacciones ACID en MySQL InnoDB.
* **Autogestión de Citas ("Mis Turnos" - `/mis-turnos`):** Panel privado para que la clienta consulte el estado de sus reservas, cancele turnos autónomamente si surge un imprevisto (con aislamiento estricto Anti-IDOR) o agregue la cita a su Google Calendar personal.
* **Marco Legal y Privacidad (`/privacidad`):** Términos del servicio y política de privacidad conforme a la Ley Nacional N.° 25.326 de Protección de Datos Personales (sin tratamiento de datos médicos ni alergias sensibles).

---

## 5. Ejecución de Pruebas Automatizadas (Testing con PHPUnit)

El software cuenta con una suite completa de pruebas unitarias y de integración que certifica la consistencia de las reglas de negocio, la seguridad de los filtros y la integridad transaccional de los datos:

* **Total de pruebas:** 47 tests automatizados.
* **Total de aserciones:** 221 assertions.
* **Tasa de éxito:** 100% (0 fallos, 0 errores).

Para ejecutar la batería de pruebas desde la terminal en la raíz del proyecto:

```bash
vendor\bin\phpunit
```

*O mediante PHP directo:*
```bash
php vendor/phpunit/phpunit/phpunit
```

### Suites de Prueba Evaluadas:
1. `HealthTest.php`: Inicialización del framework y conectividad con la base de datos.
2. `AuthFilterTest.php`: Intercepción de rutas protegidas, autenticación y control de acceso por roles (RBAC).
3. `HorarioTest.php`: Validación de intervalos cronológicos y algoritmos de prevención de solapamientos horarios.
4. `TurnoTest.php`: Máquina de estados del turno, inmutabilidad de estados terminales y formateo RFC 5545.
5. `ServicioTest.php`: Restricciones comerciales de duración y precios no negativos.
6. `PerfilTest.php`: Cifrado seguro Bcrypt, validación de formatos de correo y enlaces HTTPS de redes sociales.
7. `ReservaClienteTest.php`: Prevención de colisiones de reserva concurrentes (HTTP 409 Conflict) y transacciones atómicas.
8. `MisTurnosTest.php`: Aislamiento estricto de citas por identidad de sesión (mitigación de vulnerabilidades Anti-IDOR).

---

## 6. Arquitectura del Repositorio

```text
HerBeautyCenter/
├── app/
│   ├── Config/              # Centralización de rutas, filtros y base de datos
│   ├── Controllers/         # Controladores MVC de vistas HTML (Home, Auth, Cliente, Admin)
│   │   └── Api/             # Endpoints RESTful asíncronos (ReservasApi, TurnosApi, etc.)
│   ├── Database/            # DDL maestro (createDatabase.php) y Seeds (AdminSeeder.php)
│   ├── Filters/             # Filtro de autenticación y autorización (AuthFilter.php)
│   ├── Models/              # Modelos de datos relacionales y transacciones ACID
│   └── Views/               # Vistas desacopladas, layouts y componentes modales
├── docs/                    # Documentación final, DER y diagramas de secuencia en alta resolución
│   └── diagramas/           # Diagramas PNG a 300 DPI listos para inspección
├── public/                  # DocumentRoot expuesto al servidor web
│   ├── assets/              # Hojas de estilo CSS, scripts JS y multimedia
│   ├── index.php            # Front Controller de CodeIgniter 4
│   └── .htaccess            # Reescritura mod_rewrite para URLs limpias
├── tests/                   # Batería de pruebas automatizadas con PHPUnit
├── writable/                # Logs, sesiones y almacenamiento temporal
├── .env                     # Variables de entorno seguras del proyecto
├── composer.json            # Dependencias del framework y librerías auxiliares
├── phpunit.dist.xml         # Configuración del entorno de testing
└── README.md                # Guía de instalación y evaluación del sistema
```

---

## 7. Autora del Proyecto

* **Alumna:** María Pía Garces Brocal
* **Carrera:** Tecnicatura Universitaria en Web (TUW)
* **Institución:** Facultad de Ciencias Físico Matemáticas y Naturales (FCFMN) - Universidad Nacional de San Luis (UNSL)
* **Asignatura:** Práctica Técnica Supervisada (Trabajo Final de Graduación)
