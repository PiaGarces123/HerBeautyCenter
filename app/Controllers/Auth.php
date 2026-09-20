<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function login()
    {
        $request = $this->request->getJSON();
        
        if (!$request || !isset($request->email) || !isset($request->password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }
        
        $email = $request->email;
        $password = $request->password;
        
        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('correo', $email)
            ->where('activo', 1)
            ->get()->getRowArray();
            
        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
        
        // Verificar si es administrador
        $admin = $db->table('administrador')
            ->join('profesional', 'profesional.id_profesional = administrador.id_profesional')
            ->where('profesional.id_usuario', $usuario['id_usuario'])
            ->get()->getRowArray();
            
        session()->set([
            'usuario_id'     => $usuario['id_usuario'],
            'usuario_nombre' => $usuario['nombre_completo'],
            'usuario_correo' => $usuario['correo'],
            'usuario_avatar' => $usuario['avatar'],
            'rol'            => $admin ? 'admin' : 'cliente',
        ]);
        
        return $this->response->setJSON(['success' => true, 'message' => 'Login exitoso.']);
    }
    
    public function register()
    {
        $request = $this->request->getJSON();
        
        if (!$request || !isset($request->name) || !isset($request->email) || !isset($request->password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }
        
        $usuarioModel = new UsuarioModel();
        
        $data = [
            'nombre_completo' => $request->name,
            'correo'          => $request->email,
            'password'        => $request->password,
            'activo'          => 1
        ];
        
        if ($usuarioModel->insert($data)) {
            $userId = $usuarioModel->getInsertID();
            
            // Crear el registro de cliente asociado
            $db = \Config\Database::connect();
            $db->table('cliente')->insert([
                'id_usuario' => $userId
            ]);
            
            // Iniciar sesión automáticamente
            session()->set([
                'usuario_id'     => $userId,
                'usuario_nombre' => $request->name,
                'usuario_correo' => $request->email,
                'usuario_avatar' => null,
                'rol'            => 'cliente',
            ]);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Registro exitoso.']);
        } else {
            $errors = $usuarioModel->errors();
            $errorMsg = empty($errors) ? 'Error al registrar usuario.' : implode(' ', $errors);
            return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
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
