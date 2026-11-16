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
        // Instancia el modelo de usuarios.
        $usuarioModel = new UsuarioModel();
        // Instancia el modelo de profesionales.
        $profesionalModel = new ProfesionalModel();
        // Instancia el modelo de administradores.
        $administradorModel = new AdministradorModel();

        // ================================================================
        // 1. VERIFICAR SI EL ADMINISTRADOR YA EXISTE
        // ================================================================
        $existe = $usuarioModel->where('u_correo', 'garcesbrocalmaru@gmail.com')->first();
        if ($existe) {
            echo "El usuario ya existe en la base de datos (ID: " . $existe['u_id'] . ").\n";
            return;
        }

        // ================================================================
        // 2. CREAR EL USUARIO
        // ================================================================
        $usuarioId = $usuarioModel->insert([
            'u_nbreCompleto' => 'Admin (Maru)',
            'u_correo' => 'garcesbrocalmaru@gmail.com',
            'u_tel' => '1100000000',
            'u_pass' => 'HH22oo..',
            'u_activo' => 1
        ]);

        // Comprueba si la inserción del usuario se realizó correctamente.
        if (!$usuarioId) {
            echo "Error creando usuario:\n";
            print_r($usuarioModel->errors());
            return;
        }

        // ================================================================
        // 3. CREAR EL PERFIL PROFESIONAL
        // ================================================================
        // Vincula el perfil profesional con el usuario creado anteriormente.
        $profesionalId = $profesionalModel->insert([
            'u_id' => $usuarioId,
            'p_titulo' => 'Administradora'
        ]);

        // Comprueba si se pudo crear correctamente el perfil profesional
        if (!$profesionalId) {
            echo "Error creando profesional:\n";
            print_r($profesionalModel->errors());
            return;
        }

        // ================================================================
        // 4. CREAR EL REGISTRO DE ADMINISTRADOR
        // ================================================================
        // Establece la relación entre Administrador y Profesional.
        $adminId = $administradorModel->insert([
            'a_pId' => $profesionalId
        ]);

        // Comprueba si se pudo registrar correctamente al administrador.
        if (!$adminId) {
            echo "Error creando administrador:\n";
            print_r($administradorModel->errors());
            return;
        }

        // ================================================================
        // 5. CONFIRMAR LA CREACIÓN
        // ================================================================
        echo "Administrador creado exitosamente! ID Usuario: $usuarioId\n";
    }
}
