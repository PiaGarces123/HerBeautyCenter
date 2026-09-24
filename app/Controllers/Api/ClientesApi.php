<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class ClientesApi extends BaseController
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
            !isset($request->u_pass) || empty(trim($request->u_pass))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $name = trim($request->name);
        $email = trim($request->email);
        $phone = trim($request->phone);
        $password = $request->u_pass;

        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $numCount = preg_match_all('/[0-9]/', $password);
        $symCount = preg_match_all('/[^a-zA-Z0-9]/', $password);

        if (strlen($password) < 8 || $upperCount < 2 || $lowerCount < 2 || $numCount < 2 || $symCount < 2) {
            return $this->fail('La contraseña debe contener al menos 2 mayúsculas, 2 minúsculas, 2 números y 2 símbolos.');
        }

        $db = \Config\Database::connect();

        $existing = $db->table('usuario')->where('u_correo', $email)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe una cuenta con este correo.');
        }

        $db->transException(true)->transStart();

        try {
            $usuarioData = [
                'u_nbreCompleto' => $name,
                'u_correo' => $email,
                'u_tel' => $phone,
                'u_pass' => password_hash($password, PASSWORD_DEFAULT),
                'u_activo' => 1,
                'u_fRegistro' => date('Y-m-d H:i:s')
            ];

            $db->table('usuario')->insert($usuarioData);
            $userId = $db->insertID();

            $db->table('cliente')->insert([
                'c_uId' => $userId
            ]);

            $db->transComplete();

            return $this->respondCreated(['success' => true, 'message' => 'Cliente creado exitosamente.']);
        } catch (\Exception $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return $this->failServerError('Error en base de datos: ' . $e->getMessage());
        }
    }

    public function eliminar($id)
    {
        // Verificar que sea admin
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')->where('c_id', $id)->get()->getRow();

        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }
        
        $db->transStart();

        // Eliminar turnos u otras dependencias
        $db->table('turno')->where('t_cId', $id)->delete();
        
        // Eliminar cliente
        $db->table('cliente')->where('c_id', $id)->delete();
        // Eliminar usuario
        $db->table('usuario')->where('u_id', $cliente->c_uId)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Error al eliminar el cliente.');
        }

        return $this->respondDeleted(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
    }

    public function editar($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        $request = $this->request->getJSON();

        if (
            !isset($request->name) || empty(trim($request->name)) ||
            !isset($request->email) || empty(trim($request->email)) ||
            !isset($request->phone) || empty(trim($request->phone))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $name = trim($request->name);
        $email = trim($request->email);
        $phone = trim($request->phone);

        if (strlen($name) < 7) {
            return $this->fail('El nombre debe tener al menos 7 caracteres.');
        }
        if (!preg_match('/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/', $email)) {
            return $this->fail('El correo electrónico no es válido.');
        }
        if (strlen($phone) < 8) {
            return $this->fail('El teléfono debe tener al menos 8 caracteres.');
        }

        $db = \Config\Database::connect();
        
        $cliente = $db->table('cliente')->where('c_id', $id)->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        $existing = $db->table('usuario')->where('u_correo', $email)->where('u_id !=', $cliente->c_uId)->get()->getRow();
        if ($existing) {
            return $this->fail('Ya existe otra cuenta con este correo.');
        }

        $usuarioData = [
            'u_nbreCompleto' => $name,
            'u_correo' => $email,
            'u_tel' => $phone
        ];
        
        if (!$db->table('usuario')->where('u_id', $cliente->c_uId)->update($usuarioData)) {
            return $this->failServerError('Error al actualizar el cliente.');
        }

        return $this->respond(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
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

    public function convertir($id_cliente)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id_cliente) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        $request = $this->request->getJSON();
        
        if (
            !isset($request->title) || empty(trim($request->title))
        ) {
            return $this->fail('Datos incompletos.');
        }

        $title = trim($request->title);
        $year = isset($request->year) ? intval($request->year) : date('Y');

        if (preg_match_all('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/u', $title) < 3) {
            return $this->fail('El título debe contener al menos 3 letras.');
        }

        $db = \Config\Database::connect();
        
        $cliente = $db->table('cliente')->where('c_id', $id_cliente)->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }
        
        // Verificar que no sea profesional ya
        $profesional = $db->table('profesional')->where('u_id', $cliente->c_uId)->get()->getRow();
        if ($profesional) {
            return $this->fail('Este usuario ya es un profesional.');
        }

        $profesionalData = [
            'u_id' => $cliente->c_uId,
            'p_titulo' => $title,
            'p_anioInicioAct' => $year
        ];

        if (!$db->table('profesional')->insert($profesionalData)) {
            return $this->failServerError('Error al convertir el cliente a profesional.');
        }

        return $this->respond(['success' => true, 'message' => 'Cliente convertido a profesional exitosamente.']);
    }
}
