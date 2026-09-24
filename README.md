# Her Beauty Center

Sistema de gestión web para centro de estética desarrollado en **CodeIgniter 4**. Permite la administración de servicios, profesionales, clientes, agendamiento de turnos, y actúa como Landing Page pública.

## Características Principales
- **Catálogo de Servicios:** Gestión de tratamientos de belleza con imágenes y precios.
- **Gestión de Profesionales:** Administración del personal del centro, sus biografías y redes sociales.
- **Reserva de Turnos (Agenda):** Sistema de turnos y bloques de horarios por profesional.
- **Panel de Administración (Backend):** Interfaz segura para controlar métricas, perfiles y reservas.
- **Landing Page (Frontend):** Interfaz pública integrada al catálogo.

## Tecnologías Utilizadas
- **Backend:** PHP 8.4, CodeIgniter 4.7.4 (MVC).
- **Base de Datos:** MySQL (InnoDB).
- **Frontend:** HTML5, CSS, JS (Fetch API/AJAX).

## Instalación y Configuración

1. **Clonar el proyecto y Configurar (.env):**
   - Renombra el archivo env a .env.
   - Modifica las variables CI_ENVIRONMENT = development y los datos de database.default.

2. **Crear Base de Datos:**
   Ejecuta en consola: php app/Database/createDatabase.php

3. **Semilla Inicial (Seeder):**
   Ejecuta: php spark db:seed AdminSeeder

## 📂 Arquitectura Principal (MVC)
El sistema sigue el patrón Modelo-Vista-Controlador. Las carpetas principales dentro de `app/` son:

- **`Controllers/`**: Reciben las peticiones del usuario. Tenemos Controladores tradicionales que devuelven HTML (como `Home.php` y `Admin.php`) y Controladores en la subcarpeta `Api/` que devuelven datos en formato JSON para darle dinamismo a la web sin recargar la página.
- **`Models/`**: Interactúan con la base de datos. Cada archivo representa una tabla (ej. `ServicioModel.php`, `UsuarioModel.php`). Gestionan las reglas de validación y la seguridad de los datos antes de guardarlos.
- **`Views/`**: Son las pantallas y plantillas visuales (UI). Contiene la web pública (`index.php`), los modales emergentes (`modals/`) y todas las pantallas protegidas del panel de control (`profesional/`).
- **`Database/`**: Contiene todo lo necesario para inicializar la base de datos desde cero, incluyendo el script maestro `createDatabase.php` y los Sembradores (`Seeds/`) para inyectar datos por defecto.

Además de `app/`, la otra carpeta vital es **`public/`**:
Es la única carpeta accesible públicamente a través de internet por motivos de seguridad. 
- **`index.php`**: El punto de entrada (Front Controller) de CodeIgniter.
- **`.htaccess`**: Reglas de Apache que eliminan el "index.php" de tus URLs para que sean más limpias (ej. `/admin` en lugar de `/index.php/admin`).
- **`assets/`**: Almacena todos los recursos estáticos del sitio:
  - `css/`: Hojas de estilo puras (Vanilla CSS).
  - `js/`: Lógica asíncrona (Fetch API) e interactividad para el Frontend y Panel de Administración.
  - `media/`: Imágenes del sistema (Fotos de Servicios, Avatares subidos por usuarios).
  - `modals/`: Scripts de inicialización específicos para ventanas emergentes.
