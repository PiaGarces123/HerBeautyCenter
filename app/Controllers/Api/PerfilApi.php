<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class PerfilApi extends BaseController
{
    use ResponseTrait;

    private function getUserId()
    {
        return session()->get('usuario_id');
    }

    public function actualizar()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id) return $this->failUnauthorized('No autorizado');

        $req = $this->getRequestData();

        $nombre = trim($req['nombre_completo'] ?? $req['u_nbreCompleto'] ?? $req['nombre'] ?? $req['name'] ?? '');
        $telefono = trim($req['telefono'] ?? $req['u_tel'] ?? $req['tel'] ?? $req['phone'] ?? '');
        $correo = trim($req['correo'] ?? $req['u_correo'] ?? $req['email'] ?? '');
        $titulo = trim($req['titulo'] ?? $req['p_titulo'] ?? $req['title'] ?? '');

        $errors = [];
        if (empty($nombre)) {
            $errors['nombre_completo'] = 'El nombre completo es obligatorio.';
        } elseif (mb_strlen($nombre) < 3) {
            $errors['nombre_completo'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if (empty($correo)) {
            $errors['correo'] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'Ingresá un correo electrónico válido.';
        }

        if (!empty($telefono)) {
            $digits = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($digits) < 8) {
                $errors['telefono'] = 'El teléfono debe contener al menos 8 dígitos.';
            }
        }

        $db = \Config\Database::connect();

        // Verificar si el correo ya existe en otro usuario
        if ($correo && empty($errors['correo'])) {
            $exist = $db->table('usuario')
                ->where('u_correo', $correo)
                ->where('u_id !=', $usuario_id)
                ->countAllResults();
            if ($exist > 0) {
                $errors['correo'] = 'El correo ya está en uso por otro usuario.';
            }
        }

        // Validar título profesional si corresponde
        $profesional = $db->table('profesional')->where('u_id', $usuario_id)->get()->getRowArray();
        if ($profesional && (isset($req['titulo']) || isset($req['p_titulo']) || isset($req['title']))) {
            if (empty($titulo)) {
                $errors['titulo'] = 'El título profesional es obligatorio.';
            } elseif (mb_strlen($titulo) < 3) {
                $errors['titulo'] = 'El título profesional debe tener al menos 3 caracteres.';
            }
        }

        if (!empty($errors)) {
            return $this->respond([
                'status'   => 400,
                'error'    => 400,
                'success'  => false,
                'errors'   => $errors,
                'messages' => $errors,
                'message'  => reset($errors)
            ], 400);
        }

        $data = [
            'u_nbreCompleto' => $nombre,
            'u_tel' => $telefono
        ];
        if (!empty($correo)) {
            $data['u_correo'] = $correo;
        }

        $db->table('usuario')->where('u_id', $usuario_id)->update($data);

        // Update professional title if applicable
        if ($profesional && !empty($titulo)) {
            $db->table('profesional')->where('p_id', $profesional['p_id'])->update(['p_titulo' => $titulo]);
        }

        // Actualizar consideraciones especiales si el usuario es cliente
        if (array_key_exists('consideraciones', $req) || array_key_exists('c_consideraciones', $req)) {
            $consideraciones = trim($req['consideraciones'] ?? $req['c_consideraciones'] ?? '');
            $cliente = $db->table('cliente')->where('c_uId', $usuario_id)->get()->getRowArray();
            if ($cliente) {
                $db->table('cliente')->where('c_id', $cliente['c_id'])->update(['c_consideraciones' => $consideraciones]);
            } else {
                $db->table('cliente')->insert([
                    'c_uId'             => $usuario_id,
                    'c_consideraciones' => $consideraciones
                ]);
            }
        }

        // Update session if name changed
        session()->set('usuario_nombre', trim($nombre));
        if (!empty($correo)) {
            session()->set('usuario_correo', trim($correo));
        }

        return $this->respondUpdated(['message' => 'Perfil actualizado correctamente']);
    }

    public function password()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id) return $this->failUnauthorized('No autorizado');

        $req = $this->getRequestData();
        $password_antigua = $req['password_antigua'] ?? '';
        $password_nueva = $req['password_nueva'] ?? '';
        $password_confirmar = $req['password_confirmar'] ?? '';

        $errors = [];
        if (empty($password_antigua)) {
            $errors['password_antigua'] = 'La contraseña actual es obligatoria.';
        }
        if (empty($password_nueva)) {
            $errors['password_nueva'] = 'La nueva contraseña es obligatoria.';
        } elseif (strlen($password_nueva) < 8) {
            $errors['password_nueva'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if (empty($password_confirmar)) {
            $errors['password_confirmar'] = 'Debes confirmar la nueva contraseña.';
        } elseif ($password_nueva !== $password_confirmar) {
            $errors['password_confirmar'] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            return $this->respond([
                'status'   => 400,
                'error'    => 400,
                'success'  => false,
                'errors'   => $errors,
                'messages' => $errors,
                'message'  => reset($errors)
            ], 400);
        }

        $db = \Config\Database::connect();
        $user = $db->table('usuario')->where('u_id', $usuario_id)->get()->getRowArray();

        if (!password_verify($password_antigua, $user['u_pass'])) {
            return $this->respond([
                'status'   => 400,
                'error'    => 400,
                'success'  => false,
                'errors'   => ['password_antigua' => 'La contraseña actual es incorrecta.'],
                'messages' => ['password_antigua' => 'La contraseña actual es incorrecta.'],
                'message'  => 'La contraseña actual es incorrecta.'
            ], 400);
        }

        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $db->table('usuario')->where('u_id', $usuario_id)->update(['u_pass' => $hash]);

        return $this->respondUpdated(['message' => 'Contraseña actualizada correctamente']);
    }

    public function avatar()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id) return $this->failUnauthorized('No autorizado');

        // Accept any alias: avatar, u_avatar, foto, image, file or the first file in upload
        $file = $this->request->getFile('avatar')
             ?? $this->request->getFile('u_avatar')
             ?? $this->request->getFile('foto')
             ?? $this->request->getFile('image')
             ?? $this->request->getFile('file');

        if (!$file) {
            $files = $this->request->getFiles();
            if (!empty($files)) {
                $file = reset($files);
            }
        }

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->fail('No se ha enviado ningún archivo válido.');
        }

        // Validate type and size
        $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->guessExtension() ?? $file->getClientExtension());

        if (!in_array($file->getMimeType(), $allowedMimes) && !in_array($ext, $allowedExts)) {
            return $this->fail('El formato del archivo debe ser JPG, PNG o WEBP.');
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return $this->fail('El archivo excede el tamaño máximo de 5MB.');
        }

        $uploadPath = FCPATH . 'assets/media/Avatares';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $db = \Config\Database::connect();
        
        // Retrieve previous avatar to optionally remove old file
        $currentUser = $db->table('usuario')->select('u_avatar')->where('u_id', $usuario_id)->get()->getRowArray();
        if (!empty($currentUser['u_avatar'])) {
            $oldRelative = str_replace(base_url(), '', $currentUser['u_avatar']);
            $oldPath = FCPATH . ltrim($oldRelative, '/\\');
            if (is_file($oldPath) && str_contains($oldPath, 'Avatares')) {
                @unlink($oldPath);
            }
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $ruta = 'assets/media/Avatares/' . $newName;

        $db->table('usuario')->where('u_id', $usuario_id)->update(['u_avatar' => $ruta]);

        // Update session
        session()->set('usuario_avatar', base_url($ruta));

        return $this->respondUpdated([
            'status' => 200,
            'message' => 'Avatar actualizado correctamente.',
            'avatar_url' => base_url($ruta)
        ]);
    }

    public function redes()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id) return $this->failUnauthorized('No autorizado');

        $db = \Config\Database::connect();

        // Get profesional ID
        $profesional = $db->table('profesional')->where('u_id', $usuario_id)->get()->getRowArray();
        if (!$profesional) {
            return $this->fail('El usuario no es un profesional.');
        }

        $data = $this->getRequestData();
        $redesData = $data['redes'] ?? $this->request->getPost('redes');

        $plataformas = [
            'instagram' => [
                'nombre' => 'Instagram',
                'hosts'  => ['instagram.com', 'www.instagram.com'],
                'ejemplo'=> 'https://instagram.com/tu_usuario',
            ],
            'facebook' => [
                'nombre' => 'Facebook',
                'hosts'  => ['facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com', 'm.facebook.com'],
                'ejemplo'=> 'https://facebook.com/tu_pagina',
            ],
            'tiktok' => [
                'nombre' => 'TikTok',
                'hosts'  => ['tiktok.com', 'www.tiktok.com'],
                'ejemplo'=> 'https://tiktok.com/@tu_usuario',
            ],
            'x' => [
                'nombre' => 'X (Twitter)',
                'hosts'  => ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'],
                'ejemplo'=> 'https://x.com/tu_usuario',
            ],
            'linkedin' => [
                'nombre' => 'LinkedIn',
                'hosts'  => ['linkedin.com', 'www.linkedin.com'],
                'ejemplo'=> 'https://linkedin.com/in/tu_usuario',
            ],
            'whatsapp' => [
                'nombre' => 'WhatsApp',
                'hosts'  => ['wa.me', 'api.whatsapp.com', 'chat.whatsapp.com', 'web.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com'],
                'ejemplo'=> 'https://wa.me/54911xxxxxxxx',
            ],
        ];

        $insertData = [];
        $tiposUsados = [];

        if (!empty($redesData) && is_array($redesData)) {
            foreach ($redesData as $red) {
                $tipo = trim(strtolower($red['tipo'] ?? $red['rs_tipo'] ?? ''));
                $link = trim($red['link'] ?? $red['rs_link'] ?? '');

                // 1. Control de fila completamente vacía
                if ($tipo === '' && $link === '') {
                    return $this->fail('Hay una fila de red social vacía. Completa todos los campos o elimina la fila.');
                }

                // 2. Control de tipo seleccionado pero link vacío
                if ($tipo !== '' && $link === '') {
                    $nombrePlat = $plataformas[$tipo]['nombre'] ?? ucfirst($tipo);
                    return $this->fail("El enlace para {$nombrePlat} no puede estar vacío.");
                }

                // 3. Control de link ingresado pero tipo sin seleccionar
                if ($tipo === '' && $link !== '') {
                    return $this->fail('Debes seleccionar a qué red social corresponde el enlace ingresado.');
                }

                // 4. Control de plataforma soportada
                if (!array_key_exists($tipo, $plataformas)) {
                    return $this->fail("La red social '{$tipo}' seleccionada no es válida.");
                }

                $platInfo = $plataformas[$tipo];

                // 5. Control de duplicados
                if (in_array($tipo, $tiposUsados, true)) {
                    return $this->fail("No puedes agregar la red social {$platInfo['nombre']} más de una vez.");
                }
                $tiposUsados[] = $tipo;

                // 6. Rechazar explícitamente HTTP inseguro
                if (str_starts_with(strtolower($link), 'http://')) {
                    return $this->fail("El enlace de {$platInfo['nombre']} debe usar un protocolo seguro HTTPS (https://) en lugar de HTTP.");
                }

                // 7. Normalización controlada si el usuario colocó solo el @usuario o número
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
                    $cleanNum = preg_replace('/[^0-9]/', '', $link);
                    if (empty($cleanNum)) {
                        return $this->fail("El número de WhatsApp no es válido. Ejemplo: {$platInfo['ejemplo']}");
                    }
                    $link = 'https://wa.me/' . $cleanNum;
                }

                // Asegurar prefijo https://
                if (!str_starts_with(strtolower($link), 'https://')) {
                    $link = 'https://' . ltrim($link, '/');
                }

                // 8. Control de formato URL válido
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    return $this->fail("El enlace ingresado para {$platInfo['nombre']} no es una URL válida. Debe ser del formato: {$platInfo['ejemplo']}");
                }

                // 9. Control de protocolo seguro y dominio oficial
                $parsed = parse_url($link);
                $scheme = strtolower($parsed['scheme'] ?? '');
                $host   = strtolower($parsed['host'] ?? '');
                $path   = trim($parsed['path'] ?? '', '/');

                if ($scheme !== 'https') {
                    return $this->fail("El enlace de {$platInfo['nombre']} debe usar estrictamente protocolo seguro HTTPS.");
                }

                if (!in_array($host, $platInfo['hosts'], true)) {
                    return $this->fail("El enlace ingresado no corresponde al dominio oficial de {$platInfo['nombre']}. Debe ser del formato: {$platInfo['ejemplo']}");
                }

                // 10. Control de que sea un perfil/cuenta real y no la página de inicio vacía
                if ($tipo === 'whatsapp') {
                    $query = $parsed['query'] ?? '';
                    if (empty($path) && !str_contains($query, 'phone=')) {
                        return $this->fail("Debes incluir tu número telefónico con código de país en el enlace de WhatsApp. Ejemplo: {$platInfo['ejemplo']}");
                    }
                } else {
                    if (empty($path) || $path === '') {
                        return $this->fail("Debes incluir tu usuario o enlace a tu perfil en {$platInfo['nombre']}, no solo la página principal ({$platInfo['ejemplo']}).");
                    }
                }

                $insertData[] = [
                    'rs_pId'  => (int)$profesional['p_id'],
                    'rs_tipo' => $tipo,
                    'rs_link' => esc($link),
                ];
            }
        }

        $db->transStart();
        
        // Limpiar redes anteriores del profesional
        $db->table('red_social')->where('rs_pId', $profesional['p_id'])->delete();

        if (!empty($insertData)) {
            $db->table('red_social')->insertBatch($insertData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Ocurrió un error al guardar las redes.');
        }

        return $this->respondUpdated(['message' => 'Redes sociales guardadas correctamente.']);
    }
}
