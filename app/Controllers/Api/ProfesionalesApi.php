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
            !isset($request->u_pass) || empty(trim($request->u_pass)) ||
            !isset($request->title) || empty(trim($request->title))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $name = trim($request->name);
        $email = trim($request->email);
        $phone = trim($request->phone);
        $password = $request->u_pass;
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
        $existing = $db->table('usuario')->where('u_correo', trim($request->email))->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe una cuenta con este correo.');
        }

        $db->transStart();

        // 1. Insert into usuario
        $usuarioData = [
            'u_nbreCompleto' => trim($request->name),
            'u_correo' => trim($request->email),
            'u_tel' => isset($request->phone) ? trim($request->phone) : null,
            'u_pass' => password_hash($request->u_pass, PASSWORD_DEFAULT),
            'u_activo' => 1,
            'u_fRegistro' => date('Y-m-d H:i:s')
        ];

        $db->table('usuario')->insert($usuarioData);
        $userId = $db->insertID();

        // 2. Insert into profesional
        $profesionalData = [
            'c_uId' => $userId,
            'p_titulo' => trim($request->title),
            'p_anioInicioAct' => isset($request->year) ? intval($request->year) : date('Y')
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
        $db->table('profesional_servicio')->where('p_id', $profesional_id)->delete();

        // 2. Insertar los nuevos servicios seleccionados
        if (!empty($servicios)) {
            $insertData = [];
            foreach ($servicios as $id_servicio) {
                $insertData[] = [
                    'p_id' => $profesional_id,
                    'p_sId' => intval($id_servicio)
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
        $profesional = $db->table('profesional')->where('p_id', $id)->get()->getRow();

        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        $db->transStart();

        // Eliminar administrador si lo fuera
        $db->table('administrador')->where('p_id', $id)->delete();
        // Eliminar relaciones de servicios primero
        $db->table('profesional_servicio')->where('p_id', $id)->delete();
        // Eliminar turnos asignados
        $db->table('turno')->where('p_id', $id)->delete();
        // Eliminar horarios configurados
        $db->table('horario')->where('p_id', $id)->delete();
        // Eliminar profesional
        $db->table('profesional')->where('p_id', $id)->delete();
        // Eliminar usuario
        $db->table('usuario')->where('u_id', $profesional->u_id)->delete();

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
        
        $profesional = $db->table('profesional')->where('p_id', $id)->get()->getRow();
        if (!$profesional) {
            return $this->failNotFound('Profesional no encontrada.');
        }

        $existing = $db->table('usuario')->where('u_correo', $email)->where('u_id !=', $profesional->u_id)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe otra cuenta con este correo.');
        }

        $db->transStart();

        $usuarioData = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => $phone
        ];
        $db->table('usuario')->where('u_id', $profesional->u_id)->update($usuarioData);

        $profesionalData = [
            'p_titulo' => $title,
            'p_anioInicioAct' => $year
        ];
        $db->table('profesional')->where('p_id', $id)->update($profesionalData);

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
        if (!isset($request->u_pass) || empty(trim($request->u_pass))) {
            return $this->fail('Contraseña no proporcionada.');
        }

        $password = trim($request->u_pass);

        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        $db = \Config\Database::connect();
        
        $usuario = $db->table('usuario')->where('u_id', $id_usuario)->get()->getRow();
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        $db->table('usuario')->where('u_id', $id_usuario)->update([
            'u_pass' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return $this->respond(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    }
}
