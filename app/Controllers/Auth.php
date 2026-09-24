<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function login()
    {
        $request = $this->request->getJSON();
        
        if (!$request || !isset($request->email) || !isset($request->u_pass)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }
        
        $email = $request->email;
        $password = $request->u_pass;
        
        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('u_correo', $email)
            ->where('u_activo', 1)
            ->get()->getRowArray();
            
        if (!$usuario || !password_verify($password, $usuario['u_pass'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
        
        // Verificar si es profesional
        $profesional = $db->table('profesional')
            ->where('u_id', $usuario['u_id'])
            ->get()->getRowArray();
            
        $rol = 'cliente';
        $id_profesional = null;
        
        if ($profesional) {
            $rol = 'profesional';
            $id_profesional = $profesional['p_id'];
            
            // Verificar si además de profesional, es administrador
            $admin = $db->table('administrador')
                ->where('p_id', $id_profesional)
                ->get()->getRowArray();
                
            if ($admin) {
                $rol = 'admin';
            }
        }
            
        session()->set([
            'usuario_id'     => $usuario['u_id'],
            'usuario_nombre' => $usuario['u_nbreCompleto'],
            'usuario_correo' => $usuario['u_correo'],
            'usuario_avatar' => $usuario['u_avatar'],
            'rol'            => $rol,
            'p_id' => $id_profesional,
        ]);
        return $this->response->setJSON(['success' => true, 'message' => 'Login exitoso.', 'rol' => $rol]);
    }
    
    public function register()
    {
        $request = $this->request->getJSON();
        
        if (!$request || !isset($request->name) || !isset($request->email) || !isset($request->phone) || !isset($request->u_pass)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }
        
        $usuarioModel = new UsuarioModel();
        
        $data = [
            'u_nbreCompleto' => $request->name,
            'u_correo'          => $request->email,
            'u_tel'        => $request->phone,
            'u_pass'        => $request->u_pass,
            'u_activo'          => 1
        ];
        $db = \Config\Database::connect();
        
        try {
            $db->transException(true)->transStart();
            
            if (!$usuarioModel->insert($data)) {
                $errors = $usuarioModel->errors();
                $errorMsg = empty($errors) ? 'Error al registrar usuario.' : implode(' ', $errors);
                return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
            }
            
            $userId = $usuarioModel->getInsertID();
            
            // Crear el registro de cliente asociado
            $db->table('cliente')->insert([
                'u_id' => $userId
            ]);
            
            $db->transComplete();
            
            // Iniciar sesión automáticamente
            session()->set([
                'usuario_id'     => $userId,
                'usuario_nombre' => $request->name,
                'usuario_correo' => $request->email,
                'usuario_avatar' => null,
                'rol'            => 'cliente',
            ]);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Registro exitoso.']);
            
        } catch (\Exception $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }
    
    public function logout()
    {
        session()->destroy();

        if ($this->request->isAJAX() || strpos($this->request->getHeaderLine('accept'), 'application/json') !== false) {
            return $this->response->setJSON(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
        }

        return redirect()->to(base_url());
    }
}
