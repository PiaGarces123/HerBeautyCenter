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

        $nombre = $this->request->getPost('nombre_completo');
        $telefono = $this->request->getPost('telefono');
        $correo = $this->request->getPost('correo');

        $db = \Config\Database::connect();

        // Verificar si el correo ya existe en otro usuario
        if ($correo) {
            $exist = $db->table('usuario')
                ->where('correo', $correo)
                ->where('id_usuario !=', $usuario_id)
                ->countAllResults();
            if ($exist > 0) {
                return $this->fail('El correo ya está en uso por otro usuario.');
            }
        }

        $data = [
            'nombre_completo' => trim($nombre),
            'telefono' => trim($telefono)
        ];
        if (!empty($correo)) {
            $data['correo'] = trim($correo);
        }

        $db->table('usuario')->where('id_usuario', $usuario_id)->update($data);

        // Update professional title if applicable
        $profesional = $db->table('profesional')->where('id_usuario', $usuario_id)->get()->getRowArray();
        if ($profesional) {
            $titulo = $this->request->getPost('titulo');
            if (empty(trim($titulo))) {
                return $this->fail('El título profesional es obligatorio.');
            }
            if (strlen(trim($titulo)) < 3) {
                return $this->fail('El título profesional debe tener al menos 3 caracteres.');
            }
            $db->table('profesional')->where('id_profesional', $profesional['id_profesional'])->update(['titulo' => trim($titulo)]);
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

        $password_antigua = $this->request->getPost('password_antigua');
        $password_nueva = $this->request->getPost('password_nueva');
        $password_confirmar = $this->request->getPost('password_confirmar');

        if (empty($password_antigua) || empty($password_nueva) || empty($password_confirmar)) {
            return $this->fail('Todos los campos son obligatorios.');
        }

        if ($password_nueva !== $password_confirmar) {
            return $this->fail('La nueva contraseña y la confirmación no coinciden.');
        }

        $db = \Config\Database::connect();
        $user = $db->table('usuario')->where('id_usuario', $usuario_id)->get()->getRowArray();

        if (!password_verify($password_antigua, $user['password'])) {
            return $this->fail('La contraseña actual es incorrecta.');
        }

        $hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $db->table('usuario')->where('id_usuario', $usuario_id)->update(['password' => $hash]);

        return $this->respondUpdated(['message' => 'Contraseña actualizada correctamente']);
    }

    public function avatar()
    {
        $usuario_id = $this->getUserId();
        if (!$usuario_id) return $this->failUnauthorized('No autorizado');

        $file = $this->request->getFile('avatar');

        if (!$file || !$file->isValid()) {
            return $this->fail('No se ha enviado ningún archivo válido.');
        }

        // Validate type and size
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return $this->fail('El formato del archivo debe ser JPG, PNG o WEBP.');
        }

        if ($file->getSizeByUnit('mb') > 5) {
            return $this->fail('El archivo excede el tamaño máximo de 5MB.');
        }

        $uploadPath = FCPATH . 'assets/media/Avatares';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        $ruta = 'public/assets/media/Avatares/' . $newName;

        $db = \Config\Database::connect();
        $db->table('usuario')->where('id_usuario', $usuario_id)->update(['avatar' => $ruta]);

        // Update session
        session()->set('usuario_avatar', base_url($ruta));

        return $this->respondUpdated([
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
        $profesional = $db->table('profesional')->where('id_usuario', $usuario_id)->get()->getRowArray();
        if (!$profesional) {
            return $this->fail('El usuario no es un profesional.');
        }

        $redesData = $this->request->getPost('redes'); // Array of elements: ['tipo' => 'instagram', 'link' => 'https://...']
        
        $db->transStart();
        
        // Clean previous networks
        $db->table('red_social')->where('id_profesional', $profesional['id_profesional'])->delete();

        if (!empty($redesData) && is_array($redesData)) {
            $insertData = [];
            foreach ($redesData as $red) {
                if (empty($red['tipo']) || empty($red['link'])) continue;

                $tipo = trim(strtolower($red['tipo']));
                $link = trim($red['link']);

                // Cybersecurity: Validate scheme and domain
                if (strpos($link, 'https://') !== 0) {
                    $db->transRollback();
                    return $this->fail("El enlace para $tipo debe comenzar con 'https://'");
                }

                // Verify domain matches the network
                $valid = false;
                switch ($tipo) {
                    case 'instagram': $valid = strpos($link, 'instagram.com') !== false; break;
                    case 'facebook': $valid = strpos($link, 'facebook.com') !== false; break;
                    case 'tiktok': $valid = strpos($link, 'tiktok.com') !== false; break;
                    case 'x': $valid = (strpos($link, 'x.com') !== false || strpos($link, 'twitter.com') !== false); break;
                    case 'linkedin': $valid = strpos($link, 'linkedin.com') !== false; break;
                    case 'whatsapp': $valid = (strpos($link, 'wa.me') !== false || strpos($link, 'whatsapp.com') !== false); break;
                }

                if (!$valid) {
                    $db->transRollback();
                    return $this->fail("El enlace ingresado no corresponde a la plataforma de $tipo.");
                }

                $insertData[] = [
                    'id_profesional' => $profesional['id_profesional'],
                    'tipo' => $tipo,
                    'link' => esc($link) // Prevent XSS
                ];
            }

            if (!empty($insertData)) {
                $db->table('red_social')->insertBatch($insertData);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Ocurrió un error al guardar las redes.');
        }

        return $this->respondUpdated(['message' => 'Redes sociales guardadas correctamente.']);
    }
}
