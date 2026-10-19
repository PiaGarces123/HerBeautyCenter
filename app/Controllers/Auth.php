<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function login()
    {
        try {
            $request = $this->request->getJSON();
        } catch (\Throwable $e) {
            $request = null;
        }
        $email = $request->email ?? $this->request->getPost('email');
        $password = $request->u_pass ?? $request->password ?? $this->request->getPost('u_pass') ?? $this->request->getPost('password');
        
        if (empty($email) || empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Por favor ingresá email y contraseña.']);
        }
        
        $db = \Config\Database::connect();
        $usuario = $db->table('usuario')
            ->where('u_correo', $email)
            ->where('u_activo', 1)
            ->get()->getRowArray();
            
        if (!$usuario || !password_verify($password, $usuario['u_pass'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
        
        try {
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
                    ->where('a_pId', $id_profesional)
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
                'p_id'           => $id_profesional,
            ]);
            return $this->response->setJSON(['success' => true, 'message' => 'Login exitoso.', 'rol' => $rol]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al iniciar sesión: ' . $e->getMessage()]);
        }
    }
    
    public function register()
    {
        try {
            $request = $this->request->getJSON();
        } catch (\Throwable $e) {
            $request = null;
        }
        $name     = $request->name ?? $this->request->getPost('name');
        $email    = $request->email ?? $this->request->getPost('email');
        $phone    = $request->phone ?? $this->request->getPost('phone');
        $password = $request->u_pass ?? $request->password ?? $this->request->getPost('u_pass') ?? $this->request->getPost('password');
        
        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Por favor completá todos los campos.']);
        }
        
        $usuarioModel = new UsuarioModel();
        
        $data = [
            'u_nbreCompleto' => $name,
            'u_correo'       => $email,
            'u_tel'          => $phone,
            'u_pass'         => $password,
            'u_activo'       => 1
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
            
            // Crear el registro de cliente asociado (la columna es c_uId)
            $db->table('cliente')->insert([
                'c_uId' => $userId
            ]);
            
            $db->transComplete();
            
            // Iniciar sesión automáticamente
            session()->set([
                'usuario_id'     => $userId,
                'usuario_nombre' => $name,
                'usuario_correo' => $email,
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
