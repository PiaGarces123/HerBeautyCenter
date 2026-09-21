<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UsuarioModel;
use App\Models\ProfesionalModel;
use App\Models\AdministradorModel;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $usuarioModel = new UsuarioModel();
        $profesionalModel = new ProfesionalModel();
        $administradorModel = new AdministradorModel();

        // Verificar si ya existe el usuario
        $existe = $usuarioModel->where('correo', 'garcesbrocalmaru@gmail.com')->first();
        if ($existe) {
            echo "El usuario ya existe en la base de datos (ID: " . $existe['id_usuario'] . ").\n";
            return;
        }

        // 1. Crear Usuario
        $usuarioId = $usuarioModel->insert([
            'nombre_completo' => 'Admin (Maru)',
            'correo'          => 'garcesbrocalmaru@gmail.com',
            'telefono'        => '1100000000',
            'password'        => 'HH22oo..',
            'activo'          => 1
        ]);

        if (!$usuarioId) {
            echo "Error creando usuario:\n";
            print_r($usuarioModel->errors());
            return;
        }

        // 2. Crear Profesional
        $profesionalId = $profesionalModel->insert([
            'id_usuario' => $usuarioId,
            'titulo' => 'Administradora'
        ]);

        if (!$profesionalId) {
            echo "Error creando profesional:\n";
            print_r($profesionalModel->errors());
            return;
        }

        // 3. Crear Administrador
        $adminId = $administradorModel->insert([
            'id_profesional' => $profesionalId
        ]);

        if (!$adminId) {
            echo "Error creando administrador:\n";
            print_r($administradorModel->errors());
            return;
        }

        echo "Administrador creado exitosamente! ID Usuario: $usuarioId\n";
    }
}
