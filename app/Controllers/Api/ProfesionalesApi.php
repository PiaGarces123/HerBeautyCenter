<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class ProfesionalesApi extends BaseController
{
    use ResponseTrait;

    public function crear()
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $request = $this->request->getJSON();

        if (
            !isset($request->name) || empty(trim($request->name)) ||
            !isset($request->email) || empty(trim($request->email)) ||
            !isset($request->phone) || empty(trim($request->phone)) ||
            !isset($request->password) || empty(trim($request->password)) ||
            !isset($request->title) || empty(trim($request->title))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $name = trim($request->name);
        $email = trim($request->email);
        $phone = trim($request->phone);
        $password = $request->password;
        $title = trim($request->title);

        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{7,}$/u', $name)) {
            return $this->fail('El nombre debe tener al menos 7 letras y no contener signos extraños.');
        }
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        // Password strength: 2 uppercase, 2 lowercase, 2 numbers, 2 symbols
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }
        // At least 3 letters in the title
        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        $db = \Config\Database::connect();

        // Check if user already exists
        $existing = $db->table('usuario')->where('correo', trim($request->email))->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe una cuenta con este correo.');
        }

        $db->transStart();

        // 1. Insert into usuario
        $usuarioData = [
            'nombre_completo' => trim($request->name),
            'correo' => trim($request->email),
            'telefono' => isset($request->phone) ? trim($request->phone) : null,
            'password' => password_hash($request->password, PASSWORD_DEFAULT),
            'activo' => 1,
            'fecha_registro' => date('Y-m-d H:i:s')
        ];

        $db->table('usuario')->insert($usuarioData);
        $userId = $db->insertID();

        // 2. Insert into profesional
        $profesionalData = [
            'id_usuario' => $userId,
            'titulo' => trim($request->title),
            'anio_inicio_actividades' => isset($request->year) ? intval($request->year) : date('Y')
        ];

        $db->table('profesional')->insert($profesionalData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al crear la profesional.');
        }

        return $this->respondCreated(['success' => true, 'message' => 'Profesional creada exitosamente.']);
    }

    public function servicios()
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        $request = $this->request->getJSON();

        if (!isset($request->profesional_id) || empty($request->profesional_id)) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        $profesional_id = intval($request->profesional_id);
        $servicios = isset($request->servicios) && is_array($request->servicios) ? $request->servicios : [];

        $db = \Config\Database::connect();

        $db->transStart();

        // 1. Eliminar los servicios actuales de la profesional
        $db->table('profesional_servicio')->where('id_profesional', $profesional_id)->delete();

        // 2. Insertar los nuevos servicios seleccionados
        if (!empty($servicios)) {
            $insertData = [];
            foreach ($servicios as $id_servicio) {
                $insertData[] = [
                    'id_profesional' => $profesional_id,
                    'id_servicio' => intval($id_servicio)
                ];
            }
            $db->table('profesional_servicio')->insertBatch($insertData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al asignar los servicios.');
        }

        return $this->respond(['success' => true, 'message' => 'Servicios asignados correctamente.']);
    }

    public function eliminar($id)
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        $db = \Config\Database::connect();
        $profesional = $db->table('profesional')->where('id_profesional', $id)->get()->getRow();

        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        $db->transStart();

        // Eliminar relaciones de servicios primero
        $db->table('profesional_servicio')->where('id_profesional', $id)->delete();
        // Eliminar profesional
        $db->table('profesional')->where('id_profesional', $id)->delete();
        // Eliminar usuario
        $db->table('usuario')->where('id_usuario', $profesional->id_usuario)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar la profesional.');
        }

        return $this->respondDeleted(['success' => true, 'message' => 'Profesional eliminada correctamente.']);
    }

    public function editar($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de profesional no proporcionado.');
        }

        $request = $this->request->getJSON();

        if (
            !isset($request->name) || empty(trim($request->name)) ||
            !isset($request->email) || empty(trim($request->email)) ||
            !isset($request->phone) || empty(trim($request->phone)) ||
            !isset($request->title) || empty(trim($request->title))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $name = trim($request->name);
        $email = trim($request->email);
        $phone = trim($request->phone);
        $title = trim($request->title);
        $year = isset($request->year) ? intval($request->year) : date('Y');

        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }
        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        $db = \Config\Database::connect();
        
        $profesional = $db->table('profesional')->where('id_profesional', $id)->get()->getRow();
        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        $existing = $db->table('usuario')->where('correo', $email)->where('id_usuario !=', $profesional->id_usuario)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe otra cuenta con este correo.');
        }

        $db->transStart();

        $usuarioData = [
            'nombre_completo' => $name,
            'correo' => $email,
            'telefono' => $phone
        ];
        $db->table('usuario')->where('id_usuario', $profesional->id_usuario)->update($usuarioData);

        $profesionalData = [
            'titulo' => $title,
            'anio_inicio_actividades' => $year
        ];
        $db->table('profesional')->where('id_profesional', $id)->update($profesionalData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al actualizar la profesional.');
        }

        return $this->respond(['success' => true, 'message' => 'Profesional actualizada exitosamente.']);
    }

    public function password($id_usuario)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id_usuario) {
            return $this->fail('ID de usuario no proporcionado.');
        }

        $request = $this->request->getJSON();
        if (!isset($request->password) || empty(trim($request->password))) {
            return $this->fail('Contraseña no proporcionada.');
        }

        $password = trim($request->password);

        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        $db = \Config\Database::connect();
        
        $usuario = $db->table('usuario')->where('id_usuario', $id_usuario)->get()->getRow();
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        $db->table('usuario')->where('id_usuario', $id_usuario)->update([
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return $this->respond(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }
}
