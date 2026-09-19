<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UsuarioModel;

class CheckAuth extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'auth:check';
    protected $description = 'Verifica el flujo completo de registro y login.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        CLI::write("Conectado a la base de datos: " . $db->getDatabase(), 'green');

        $testEmail = 'test_check_' . time() . '@example.com';
        $testPassword = 'Password123!';
        $testName = 'Usuario de Prueba';

        CLI::write("1. Probando inserción con UsuarioModel (Registro)...", 'yellow');
        $usuarioModel = new UsuarioModel();
        
        $data = [
            'nombre_completo' => $testName,
            'correo'          => $testEmail,
            'password'        => $testPassword,
            'activo'          => 1
        ];

        if ($usuarioModel->insert($data)) {
            $userId = $usuarioModel->getInsertID();
            CLI::write("✓ Usuario insertado con ID: $userId", 'green');

            $db->table('cliente')->insert([
                'id_usuario' => $userId
            ]);
            CLI::write("✓ Registro cliente asociado creado.", 'green');

            CLI::write("2. Probando verificación de contraseña (Login)...", 'yellow');
            $usuario = $db->table('usuario')
                ->where('correo', $testEmail)
                ->where('activo', 1)
                ->get()->getRowArray();

            if ($usuario && password_verify($testPassword, $usuario['password'])) {
                CLI::write("✓ Login verificado exitosamente para: " . $usuario['correo'], 'green');
            } else {
                CLI::error("✗ Falló la verificación de contraseña en login.");
            }

            // Cleanup
            $db->table('cliente')->where('id_usuario', $userId)->delete();
            $db->table('usuario')->where('id_usuario', $userId)->delete();
            CLI::write("✓ Limpieza de datos de prueba completada.", 'green');
        } else {
            CLI::error("✗ Error al registrar usuario: " . json_encode($usuarioModel->errors()));
        }
    }
}
