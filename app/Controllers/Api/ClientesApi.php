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

        $data = $this->getRequestData();

        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');
        $password = $data['password'] ?? $data['u_pass'] ?? $data['pass'] ?? '';

        if (
            empty($name) ||
            empty($email) ||
            empty($phone) ||
            empty($password)
        ) {
            return $this->fail('Datos incompletos.');
        }

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

        // 1. Evitar auto-eliminación si la usuaria en sesión es la misma
        if ((int) $cliente->c_uId === (int) $session->get('usuario_id')) {
            return $this->fail('No podés eliminar tu propia cuenta mientras estés en sesión.');
        }

        // 2. Control de turnos activos pendientes (Solicitado o Confirmado)
        $turnosActivos = $db->table('turno')
            ->where('t_cId', $id)
            ->whereIn('estado', ['Solicitado', 'Confirmado'])
            ->countAllResults();

        if ($turnosActivos > 0) {
            return $this->fail('No se puede eliminar la clienta porque posee turnos pendientes por asistir (Solicitados o Confirmados). Debes cancelarlos primero o desactivar la cuenta.');
        }

        $db->transStart();

        // En turnos pasados o cancelados, desvincular t_cId a NULL (ON DELETE SET NULL / Ley 25.326)
        $db->table('turno')->where('t_cId', $id)->update(['t_cId' => null]);

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

        $data = $this->getRequestData();

        $name = trim($data['name'] ?? $data['u_nbreCompleto'] ?? $data['nombre'] ?? '');
        $email = trim($data['email'] ?? $data['u_correo'] ?? $data['correo'] ?? '');
        $phone = trim($data['phone'] ?? $data['u_tel'] ?? $data['tel'] ?? $data['telefono'] ?? '');

        if (
            empty($name) ||
            empty($email) ||
            empty($phone)
        ) {
            return $this->fail('Datos incompletos.');
        }

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

        if (isset($data['u_activo']) || isset($data['activo'])) {
            $nuevoActivo = intval($data['u_activo'] ?? $data['activo']);
            if ((int)$cliente->c_uId !== (int)$session->get('usuario_id') || $nuevoActivo === 1) {
                $usuarioData['u_activo'] = $nuevoActivo;
            }
        }

        if (!$db->table('usuario')->where('u_id', $cliente->c_uId)->update($usuarioData)) {
            return $this->failServerError('Error al actualizar el cliente.');
        }

        if (array_key_exists('consideraciones', $data) || array_key_exists('c_consideraciones', $data)) {
            $consideraciones = trim($data['consideraciones'] ?? $data['c_consideraciones'] ?? '');
            $db->table('cliente')->where('c_id', $id)->update(['c_consideraciones' => $consideraciones]);
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

        $data = $this->getRequestData();

        $password = trim($data['password'] ?? $data['u_pass'] ?? $data['new_password'] ?? '');
        if (empty($password)) {
            return $this->fail('Contraseña no proporcionada.');
        }

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

        $data = $this->getRequestData();

        $title = trim($data['title'] ?? $data['p_titulo'] ?? $data['titulo'] ?? '');
        $year = isset($data['year']) ? intval($data['year']) : (isset($data['p_anioInicioAct']) ? intval($data['p_anioInicioAct']) : date('Y'));

        if (empty($title)) {
            return $this->fail('Datos incompletos.');
        }

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

    public function toggleEstado($id)
    {
        $session = session();
        if (!$session->get('usuario_id') || $session->get('rol') !== 'admin') {
            return $this->failUnauthorized('Acceso denegado');
        }

        if (!$id) {
            return $this->fail('ID de cliente no proporcionado.');
        }

        $db = \Config\Database::connect();
        $cliente = $db->table('cliente')->where('c_id', intval($id))->get()->getRow();
        if (!$cliente) {
            return $this->failNotFound('Cliente no encontrado.');
        }

        if ($cliente->c_uId == $session->get('usuario_id')) {
            return $this->fail('No puedes cambiar el estado de tu propia cuenta mientras estás en sesión.');
        }

        $usuario = $db->table('usuario')->where('u_id', $cliente->c_uId)->get()->getRow();
        if (!$usuario) {
            return $this->failNotFound('Usuario no encontrado.');
        }

        $nuevoEstado = $usuario->u_activo ? 0 : 1;
        $db->table('usuario')->where('u_id', $cliente->c_uId)->update(['u_activo' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'activado' : 'dado de baja (inactivo)';
        return $this->respond([
            'success' => true,
            'message' => "Cliente {$accion} exitosamente.",
            'nuevo_estado' => $nuevoEstado
        ]);
    }
}
