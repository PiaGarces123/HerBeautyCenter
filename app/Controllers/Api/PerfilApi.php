<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * PerfilApi
 * Her Beauty Center
 *
 * Administra las operaciones de consulta y actualización de los datos
 * personales y profesionales del usuario autenticado.
 *
 * Sus responsabilidades principales son:
 * 1. Obtener el identificador del usuario desde la sesión activa.
 * 2. Actualizar los datos personales y profesionales del perfil.
 * 3. Modificar la contraseña verificando previamente la contraseña actual.
 * 4. Subir y actualizar la fotografía de perfil (avatar).
 * 5. Registrar, modificar y eliminar las redes sociales de un profesional.
 *
 * Tablas involucradas:
 * - usuario: datos personales, correo, teléfono, contraseña y avatar.
 * - profesional: información complementaria del perfil profesional.
 * - cliente: consideraciones especiales del cliente.
 * - red_social: enlaces a las redes sociales del profesional.
 *
 * Ubicación: app/Controllers/Api/PerfilApi.php
 */
class PerfilApi extends BaseController
{
    use ResponseTrait;

    private function getUserId()
    {
        // Obtiene usuario_id de la sesión actual.
        // Este identificador permite relacionar las operaciones
        // del perfil con el usuario que inició sesión.
        return session()->get('usuario_id');
    }

    public function actualizar()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id)
            return $this->failUnauthorized('No autorizado');

        // Obtiene los datos enviados por el frontend.
        // getRequestData() permite recibir información mediante POST o JSON.
        $req = $this->getRequestData();

        // Recupera los datos del formulario.
        $nombre = trim($req['nombre_completo'] ?? $req['u_nbreCompleto'] ?? $req['nombre'] ?? $req['name'] ?? '');
        $telefono = trim($req['telefono'] ?? $req['u_tel'] ?? $req['tel'] ?? $req['phone'] ?? '');
        $correo = trim($req['correo'] ?? $req['u_correo'] ?? $req['email'] ?? '');
        $titulo = trim($req['titulo'] ?? $req['p_titulo'] ?? $req['title'] ?? '');

        // Array donde se acumulan los errores de validación.
        // Cada error se identifica con el campo correspondiente.
        $errors = [];

        // Comprueba que el nombre no esté vacío.
        if (empty($nombre)) {
            $errors['nombre_completo'] = 'El nombre completo es obligatorio.';
        } elseif (mb_strlen($nombre) < 3) {
            $errors['nombre_completo'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        //Comprueba correo adecuado
        if (empty($correo)) {
            $errors['correo'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Ingresá un correo electrónico válido.';
        }

        //Comprueba teléfono
        if (!empty($telefono)) {
            $digits = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($digits) < 8) {
                $errors['telefono'] = 'El teléfono debe contener al menos 8 dígitos.';
            }
        }

        $db = \Config\Database::connect();

        // -- Verificar si el correo ya existe en otro usuario --

        // Comprueba la disponibilidad del correo solamente si se recibió
        // y no presenta errores de formato.
        if ($correo && empty($errors['correo'])) {
            $exist = $db->table('usuario')
                ->where('u_correo', $correo)
                ->where('u_id !=', $usuario_id)
                ->countAllResults();
            if ($exist > 0) {
                $errors['correo'] = 'El correo ya está en uso por otro usuario.';
            }
        }

        // -- Validar título profesional si corresponde --

        // Busca si el usuario tiene un perfil registrado en la tabla profesional.
        $profesional = $db->table('profesional')->where('u_id', $usuario_id)->get()->getRowArray();

        // La validación del título se realiza únicamente si:
        // 1. El usuario tiene un perfil profesional.
        // 2. Se recibió alguno de los nombres posibles para ese campo.
        if ($profesional && (isset($req['titulo']) || isset($req['p_titulo']) || isset($req['title']))) {
            if (empty($titulo)) {
                $errors['titulo'] = 'El título profesional es obligatorio.';
            } elseif (mb_strlen($titulo) < 3) {
                $errors['titulo'] = 'El título profesional debe tener al menos 3 caracteres.';
            }
        }

        // -- Devolver errores de validación --

        // Si existe al menos un error, se devuelve una respuesta HTTP 400
        // con los mensajes asociados a cada campo.
        if (!empty($errors)) {
            return $this->respond([
                'status' => 400,
                'error' => 400,
                'success' => false,
                'errors' => $errors,
                'messages' => $errors,
                'message' => reset($errors)
            ], 400);
        }

        // -- Preparar actualización de datos personales --

        // Se construye el array con los campos que se actualizarán
        // en la tabla usuario.
        $data = [
            'u_nbreCompleto' => $nombre,
            'u_tel' => $telefono
        ];

        // El correo se incorpora si no está vacío.
        if (!empty($correo)) {
            $data['u_correo'] = $correo;
        }

        $db->table('usuario')->where('u_id', $usuario_id)->update($data);

        // -- Actualizar título profesional --

        // Si el usuario tiene perfil profesional y se recibió un título,
        // actualiza el campo p_titulo del registro correspondiente.
        if ($profesional && !empty($titulo)) {
            $db->table('profesional')->where('p_id', $profesional['p_id'])->update(['p_titulo' => $titulo]);
        }

        // -- Actualizar consideraciones especiales del cliente --

        // Comprueba si el request incluye el campo de consideraciones,
        // utilizando cualquiera de los dos nombres admitidos.
        if (array_key_exists('consideraciones', $req) || array_key_exists('c_consideraciones', $req)) {

            // Recupera el contenido y elimina espacios al comienzo y al final.
            $consideraciones = trim($req['consideraciones'] ?? $req['c_consideraciones'] ?? '');

            // Busca el perfil de cliente asociado al usuario.
            $cliente = $db->table('cliente')->where('c_uId', $usuario_id)->get()->getRowArray();
            if ($cliente) {
                // Si el perfil ya existe, actualiza sus consideraciones.
                $db->table('cliente')->where('c_id', $cliente['c_id'])->update(['c_consideraciones' => $consideraciones]);
            } else {
                // Si todavía no existe un perfil de cliente,
                // crea uno asociado al usuario actual.
                $db->table('cliente')->insert([
                    'c_uId' => $usuario_id,
                    'c_consideraciones' => $consideraciones
                ]);
            }
        }

        // -- Actualizar datos almacenados en la sesión --

        // Actualiza el nombre mostrado en la sesión para que el cambio
        // se refleje inmediatamente en la interfaz.
        session()->set('usuario_nombre', trim($nombre));

        // Si se informó un correo, también actualiza ese dato en la sesión.
        if (!empty($correo)) {
            session()->set('usuario_correo', trim($correo));
        }

        // Devuelve una respuesta HTTP 200 indicando que el perfil
        // fue actualizado correctamente.
        return $this->respondUpdated(['message' => 'Perfil actualizado correctamente']);
    }

    public function password()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id)
            return $this->failUnauthorized('No autorizado');

        // Obtiene los datos enviados por el formulario.
        $req = $this->getRequestData();

        // Recupera la contraseña actual, la nueva y su confirmación.
        $password_antigua = $req['password_antigua'] ?? '';
        $password_nueva = $req['password_nueva'] ?? '';
        $password_confirmar = $req['password_confirmar'] ?? '';

        $errors = [];

        // -- Validación de la contraseña actual -
        if (empty($password_antigua)) {
            $errors['password_antigua'] = 'La contraseña actual es obligatoria.';
        }

        // -- Validación de la nueva contraseña --
        if (empty($password_nueva)) {
            $errors['password_nueva'] = 'La nueva contraseña es obligatoria.';
        } elseif (strlen($password_nueva) < 8) {
            $errors['password_nueva'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }

        // Comprueba que la nueva contraseña tenga al menos ocho caracteres.
        if (empty($password_confirmar)) {
            $errors['password_confirmar'] = 'Debes confirmar la nueva contraseña.';
        } elseif ($password_nueva !== $password_confirmar) {
            $errors['password_confirmar'] = 'Las contraseñas no coinciden.';
        }

        // -- Validación de la confirmación --
        if (!empty($errors)) {
            return $this->respond([
                'status' => 400,
                'error' => 400,
                'success' => false,
                'errors' => $errors,
                'messages' => $errors,
                'message' => reset($errors)
            ], 400);
        }

        $db = \Config\Database::connect();
        // Busca el registro del usuario autenticado.
        $user = $db->table('usuario')->where('u_id', $usuario_id)->get()->getRowArray();

        // Verifica la contraseña ingresada contra el hash almacenado.
        // password_verify() permite comprobar una contraseña sin
        // necesidad de recuperar la contraseña original.
        if (!password_verify($password_antigua, $user['u_pass'])) {
            return $this->respond([
                'status' => 400,
                'error' => 400,
                'success' => false,
                'errors' => ['password_antigua' => 'La contraseña actual es incorrecta.'],
                'messages' => ['password_antigua' => 'La contraseña actual es incorrecta.'],
                'message' => 'La contraseña actual es incorrecta.'
            ], 400);
        }

        // Genera un nuevo hash seguro para la contraseña.
        // PASSWORD_DEFAULT permite que PHP utilice el algoritmo
        // de hashing recomendado por defecto.
        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);

        // Actualiza únicamente el campo de contraseña del usuario.
        $db->table('usuario')->where('u_id', $usuario_id)->update(['u_pass' => $hash]);

        // Informa que la contraseña fue actualizada correctamente.
        return $this->respondUpdated(['message' => 'Contraseña actualizada correctamente']);
    }

    public function avatar()
    {
        // Recupera el identificador del usuario autenticado.
        $usuario_id = $this->getUserId();

        if (!$usuario_id)
            return $this->failUnauthorized('No autorizado');

        // -- Recuperar archivo enviado --

        // Acepta distintos nombres de campo para el archivo,
        // según cómo haya sido enviado desde el frontend.
        $file = $this->request->getFile('avatar')
            ?? $this->request->getFile('u_avatar')
            ?? $this->request->getFile('foto')
            ?? $this->request->getFile('image')
            ?? $this->request->getFile('file');

        // Si no se encontró un archivo con esos nombres,
        // intenta recuperar el primero de los archivos recibidos.
        if (!$file) {
            $files = $this->request->getFiles();

            if (!empty($files)) {
                $file = reset($files);
            }
        }

        // Comprueba que se haya recibido un archivo,
        // que la carga haya sido válida y que todavía no se haya movido.
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->fail('No se ha enviado ningún archivo válido.');
        }

        // -- Validar tipo y tamaño del archivo --

        // Lista de tipos MIME y extensiones admitidos.
        // Se permiten imágenes JPG, JPEG, PNG y WEBP.
        $allowedMimes = [
            'image/jpeg',
            'image/pjpeg',
            'image/jpg',
            'image/png',
            'image/x-png',
            'image/webp'
        ];

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        // Obtiene una extensión estimada a partir del contenido del archivo.
        // Si no se puede determinar, utiliza la extensión informada por el cliente.
        $ext = strtolower($file->guessExtension() ?? $file->getClientExtension());

        // Rechaza el archivo únicamente si no coincide ni el MIME
        // ni la extensión con los formatos permitidos.
        if (
            !in_array($file->getMimeType(), $allowedMimes) &&
            !in_array($ext, $allowedExts)
        ) {
            return $this->fail('El formato del archivo debe ser JPG, PNG o WEBP.');
        }

        // Comprueba que el archivo no supere los cinco megabytes.
        if ($file->getSizeByUnit('mb') > 5) {
            return $this->fail('El archivo excede el tamaño máximo de 5MB.');
        }

        // -- Preparar directorio de almacenamiento --

        // FCPATH representa la ruta absoluta al directorio público
        // de la aplicación.
        // Dentro de él se encuentra la carpeta donde se almacenan los avatares.
        $uploadPath = FCPATH . 'assets/media/Avatares';

        // Si el directorio todavía no existe, lo crea junto con
        // los directorios intermedios necesarios.
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // -- Recuperar y eliminar avatar anterior --

        // Consulta la ruta del avatar que actualmente tiene el usuario.
        $currentUser = $db->table('usuario')
            ->select('u_avatar')
            ->where('u_id', $usuario_id)
            ->get()
            ->getRowArray();

        // Si existe una ruta anterior, intenta eliminar el archivo físico.
        if (!empty($currentUser['u_avatar'])) {

            // Convierte la URL almacenada en una ruta relativa,
            // quitando el dominio de la aplicación.
            $oldRelative = str_replace(base_url(), '', $currentUser['u_avatar']);

            // Construye la ruta absoluta del archivo anterior.
            $oldPath = FCPATH . ltrim($oldRelative, '/\\');

            // Comprueba que el archivo exista y que su ruta contenga
            // el directorio de avatares antes de intentar eliminarlo.
            if (is_file($oldPath) && str_contains($oldPath, 'Avatares')) {

                // unlink() elimina el archivo del sistema de archivos.
                // El operador @ suprime los mensajes de error de esta operación.
                @unlink($oldPath);
            }
        }

        // -- Guardar nuevo avatar --

        // Genera un nombre aleatorio para evitar conflictos
        // con otros archivos existentes.
        $newName = $file->getRandomName();

        // Mueve el archivo temporal recibido al directorio definitivo.
        $file->move($uploadPath, $newName);

        // Construye la ruta relativa que se guardará en la base de datos.
        $ruta = 'assets/media/Avatares/' . $newName;

        // Actualiza el campo u_avatar del usuario autenticado.
        $db->table('usuario')
            ->where('u_id', $usuario_id)
            ->update(['u_avatar' => $ruta]);

        // -- Actualizar sesión --

        // Guarda la URL pública del nuevo avatar en la sesión,
        // para que la interfaz pueda mostrarlo sin volver a consultar la base de datos.
        session()->set('usuario_avatar', base_url($ruta));

        // Devuelve una respuesta con la URL del nuevo avatar.
        return $this->respondUpdated([
            'status' => 200,
            'message' => 'Avatar actualizado correctamente.',
            'avatar_url' => base_url($ruta)
        ]);
    }

    // -------------------------------------------------------
    // PUT /api/perfil/redes
    // -------------------------------------------------------

    /**
     * Actualiza las redes sociales asociadas al perfil profesional.
     *
     * Valida las plataformas admitidas, controla duplicados,
     * normaliza determinados formatos de enlaces y comprueba
     * que las URL pertenezcan a los dominios oficiales.
     *
     * La operación reemplaza las redes anteriores por el nuevo listado:
     * primero elimina los registros existentes y luego inserta los recibidos.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface Respuesta JSON.
     */
    public function redes()
    {
        // Recupera el identificador del usuario autenticado.
        $usuario_id = $this->getUserId();

        if (!$usuario_id)
            return $this->failUnauthorized('No autorizado');

        // Establece la conexión con la base de datos.
        $db = \Config\Database::connect();

        // -- Verificar perfil profesional --

        // Busca el perfil profesional asociado al usuario.
        $profesional = $db->table('profesional')
            ->where('u_id', $usuario_id)
            ->get()
            ->getRowArray();

        // Si no tiene perfil profesional, no puede administrar redes sociales
        // vinculadas a un profesional.
        if (!$profesional) {
            return $this->fail('El usuario no es un profesional.');
        }

        // -- Obtener redes enviadas --

        // Recupera los datos del request.
        $data = $this->getRequestData();

        // Obtiene el listado de redes sociales.
        // Se acepta tanto el contenido recibido en getRequestData()
        // como el campo POST llamado "redes".
        $redesData = $data['redes'] ?? $this->request->getPost('redes');

        // -- Plataformas admitidas --

        // Define las redes sociales que el sistema permite registrar.
        // Para cada una se especifican:
        // - nombre: nombre visible de la plataforma.
        // - hosts: dominios oficiales aceptados.
        // - ejemplo: formato de enlace que se muestra en los mensajes de error.
        $plataformas = [
            'instagram' => [
                'nombre' => 'Instagram',
                'hosts' => ['instagram.com', 'www.instagram.com'],
                'ejemplo' => 'https://instagram.com/tu_usuario',
            ],
            'facebook' => [
                'nombre' => 'Facebook',
                'hosts' => ['facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com', 'm.facebook.com'],
                'ejemplo' => 'https://facebook.com/tu_pagina',
            ],
            'tiktok' => [
                'nombre' => 'TikTok',
                'hosts' => ['tiktok.com', 'www.tiktok.com'],
                'ejemplo' => 'https://tiktok.com/@tu_usuario',
            ],
            'x' => [
                'nombre' => 'X (Twitter)',
                'hosts' => ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'],
                'ejemplo' => 'https://x.com/tu_usuario',
            ],
            'linkedin' => [
                'nombre' => 'LinkedIn',
                'hosts' => ['linkedin.com', 'www.linkedin.com'],
                'ejemplo' => 'https://linkedin.com/in/tu_usuario',
            ],
            'whatsapp' => [
                'nombre' => 'WhatsApp',
                'hosts' => ['wa.me', 'api.whatsapp.com', 'chat.whatsapp.com', 'web.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com'],
                'ejemplo' => 'https://wa.me/54911xxxxxxxx',
            ],
        ];

        // Array que contendrá los registros preparados para insertar
        // en la tabla red_social.
        $insertData = [];

        // Array auxiliar para controlar que no se repita una plataforma.
        $tiposUsados = [];

        // Procesa el listado únicamente si se recibió y es un array.
        if (!empty($redesData) && is_array($redesData)) {

            // Recorre cada red social enviada desde el frontend.
            foreach ($redesData as $red) {

                // Recupera el tipo de red y el enlace.
                // Se admiten los nombres tipo/rs_tipo y link/rs_link.
                // strtolower() convierte el tipo a minúsculas.
                $tipo = trim(strtolower($red['tipo'] ?? $red['rs_tipo'] ?? ''));
                $link = trim($red['link'] ?? $red['rs_link'] ?? '');

                // 1. Control de fila completamente vacía

                // Si ambos campos están vacíos, se considera que existe
                // una fila incompleta en el formulario.
                if ($tipo === '' && $link === '') {
                    return $this->fail('Hay una fila de red social vacía. Completa todos los campos o elimina la fila.');
                }

                // 2. Control de tipo seleccionado pero link vacío

                // Si se seleccionó una plataforma, pero no se ingresó un enlace,
                // se informa cuál es el campo que falta completar.
                if ($tipo !== '' && $link === '') {
                    $nombrePlat = $plataformas[$tipo]['nombre'] ?? ucfirst($tipo);
                    return $this->fail("El enlace para {$nombrePlat} no puede estar vacío.");
                }

                // 3. Control de link ingresado pero tipo sin seleccionar

                // Si se ingresó un enlace sin seleccionar su plataforma,
                // se solicita completar el tipo de red social.
                if ($tipo === '' && $link !== '') {
                    return $this->fail('Debes seleccionar a qué red social corresponde el enlace ingresado.');
                }

                // 4. Control de plataforma soportada

                // Comprueba que el tipo recibido exista dentro del listado
                // de plataformas admitidas.
                if (!array_key_exists($tipo, $plataformas)) {
                    return $this->fail("La red social '{$tipo}' seleccionada no es válida.");
                }

                // Recupera la configuración de la plataforma seleccionada.
                $platInfo = $plataformas[$tipo];

                // 5. Control de duplicados

                // Evita que se registre más de una vez la misma plataforma.
                // El tercer parámetro true activa la comparación estricta.
                if (in_array($tipo, $tiposUsados, true)) {
                    return $this->fail("No puedes agregar la red social {$platInfo['nombre']} más de una vez.");
                }

                // Registra el tipo para detectar posibles repeticiones posteriores.
                $tiposUsados[] = $tipo;

                // 6. Rechazar explícitamente HTTP inseguro

                // No permite enlaces que comiencen con http://.
                // Se exige el uso de HTTPS para las redes sociales.
                if (str_starts_with(strtolower($link), 'http://')) {
                    return $this->fail("El enlace de {$platInfo['nombre']} debe usar un protocolo seguro HTTPS (https://) en lugar de HTTP.");
                }

                // 7. Normalización controlada si el usuario colocó solo el @usuario o número

                // Si el usuario ingresa solamente su identificador,
                // se construye una URL completa según la plataforma seleccionada.

                if ($tipo === 'instagram' && !str_contains($link, '/')) {
                    $link = 'https://instagram.com/' . ltrim($link, '@/ ');

                } elseif ($tipo === 'facebook' && !str_contains($link, '/')) {
                    $link = 'https://facebook.com/' . ltrim($link, '@/ ');

                } elseif ($tipo === 'tiktok' && !str_contains($link, '/')) {
                    $link = 'https://tiktok.com/@' . ltrim($link, '@/ ');

                } elseif ($tipo === 'x' && !str_contains($link, '/')) {
                    $link = 'https://x.com/' . ltrim($link, '@/ ');

                } elseif ($tipo === 'linkedin' && !str_contains($link, '/')) {
                    $link = 'https://linkedin.com/in/' . ltrim($link, '@/ ');

                } elseif ($tipo === 'whatsapp' && !str_contains($link, '/')) {

                    // Para WhatsApp, elimina todos los caracteres que no sean números.
                    $cleanNum = preg_replace('/[^0-9]/', '', $link);

                    // Si no quedó ningún dígito, el número no es válido.
                    if (empty($cleanNum)) {
                        return $this->fail("El número de WhatsApp no es válido. Ejemplo: {$platInfo['ejemplo']}");
                    }

                    // Construye el enlace directo de WhatsApp.
                    $link = 'https://wa.me/' . $cleanNum;
                }

                // Asegurar prefijo https://

                // Si el enlace todavía no comienza con HTTPS,
                // agrega el protocolo seguro al comienzo.
                if (!str_starts_with(strtolower($link), 'https://')) {
                    $link = 'https://' . ltrim($link, '/');
                }

                // 8. Control de formato URL válido

                // FILTER_VALIDATE_URL comprueba que el enlace tenga
                // una estructura válida de URL.
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    return $this->fail("El enlace ingresado para {$platInfo['nombre']} no es una URL válida. Debe ser del formato: {$platInfo['ejemplo']}");
                }

                // 9. Control de protocolo seguro y dominio oficial

                // parse_url() separa la URL en sus componentes:
                // protocolo, dominio, ruta, parámetros, etc.
                $parsed = parse_url($link);

                // Recupera el protocolo y el dominio en minúsculas.
                $scheme = strtolower($parsed['scheme'] ?? '');
                $host = strtolower($parsed['host'] ?? '');

                // Recupera la ruta y elimina las barras de sus extremos.
                $path = trim($parsed['path'] ?? '', '/');

                // Comprueba que el protocolo sea HTTPS.
                if ($scheme !== 'https') {
                    return $this->fail("El enlace de {$platInfo['nombre']} debe usar estrictamente protocolo seguro HTTPS.");
                }

                // Comprueba que el dominio coincida exactamente con alguno
                // de los dominios oficiales permitidos para esa plataforma.
                if (!in_array($host, $platInfo['hosts'], true)) {
                    return $this->fail("El enlace ingresado no corresponde al dominio oficial de {$platInfo['nombre']}. Debe ser del formato: {$platInfo['ejemplo']}");
                }

                // 10. Control de que sea un perfil/cuenta real y no la página de inicio vacía

                if ($tipo === 'whatsapp') {

                    // Recupera los parámetros de la URL, si existen.
                    $query = $parsed['query'] ?? '';

                    // Exige que el enlace incluya una ruta o el parámetro phone=.
                    if (empty($path) && !str_contains($query, 'phone=')) {
                        return $this->fail("Debes incluir tu número telefónico con código de país en el enlace de WhatsApp. Ejemplo: {$platInfo['ejemplo']}");
                    }

                } else {

                    // Para las demás plataformas, exige que exista una ruta
                    // que identifique un perfil, cuenta o página.
                    if (empty($path) || $path === '') {
                        return $this->fail("Debes incluir tu usuario o enlace a tu perfil en {$platInfo['nombre']}, no solo la página principal ({$platInfo['ejemplo']}).");
                    }
                }

                // Prepara el registro para insertarlo en la base de datos.
                // Se vincula con el profesional mediante rs_pId.
                $insertData[] = [
                    'rs_pId' => (int) $profesional['p_id'],
                    'rs_tipo' => $tipo,

                    // esc() aplica escape HTML al enlace antes de almacenarlo.
                    'rs_link' => esc($link),
                ];
            }
        }

        // -- Guardar las redes sociales --

        // Inicia una transacción para agrupar la eliminación de las redes
        // anteriores y la inserción del nuevo listado.
        $db->transStart();

        // Elimina todos los enlaces anteriores asociados al profesional.
        $db->table('red_social')
            ->where('rs_pId', $profesional['p_id'])
            ->delete();

        // Si se recibieron nuevas redes, las inserta en bloque.
        // insertBatch() permite insertar varios registros en una sola operación.
        if (!empty($insertData)) {
            $db->table('red_social')->insertBatch($insertData);
        }

        // Finaliza la transacción y solicita a CodeIgniter que compruebe
        // si las operaciones se ejecutaron correctamente.
        $db->transComplete();

        // Si alguna operación provocó un fallo en la transacción,
        // devuelve un error interno del servidor.
        if ($db->transStatus() === false) {
            return $this->failServerError('Ocurrió un error al guardar las redes.');
        }

        // Informa que el listado de redes sociales se guardó correctamente.
        return $this->respondUpdated(['message' => 'Redes sociales guardadas correctamente.']);
    }
}