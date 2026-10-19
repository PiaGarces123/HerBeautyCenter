<?php

namespace Tests\Unit;

use App\Models\UsuarioModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * PerfilTest
 * Her Beauty Center — Tests Unitarios para la Gestión de Usuarios y Perfil
 */
final class PerfilTest extends CIUnitTestCase
{
    private UsuarioModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UsuarioModel();
    }

    /**
     * Test: Hasheo seguro de contraseña (RN03)
     */
    public function testHashSeguroDePassword(): void
    {
        $plainPassword = 'PasswordSeguro123!';
        
        // Simular callback de hasheo del modelo
        $data = ['data' => ['u_pass' => $plainPassword]];
        
        // El callback hashPassword debe generar un hash bcrypt válido
        $reflection = new \ReflectionClass($this->model);
        $method = $reflection->getMethod('hashPassword');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->model, $data);
        $hashed = $result['data']['u_pass'];

        $this->assertNotSame($plainPassword, $hashed, 'La contraseña nunca debe almacenarse en texto plano.');
        $this->assertTrue(password_verify($plainPassword, $hashed), 'El hash generado debe ser verificable con password_verify().');
        $this->assertFalse(password_verify('PasswordIncorrecto', $hashed), 'Una contraseña incorrecta debe fallar la verificación.');
    }

    /**
     * Test: Validación de formato de correo electrónico
     */
    public function testValidacionEmail(): void
    {
        $validation = service('validation');

        // Emails válidos
        $this->assertTrue($validation->check('maru@herbeauty.com', 'valid_email'));
        $this->assertTrue($validation->check('cliente.nuevo@gmail.com', 'valid_email'));

        // Emails inválidos
        $this->assertFalse($validation->check('correo-invalido', 'valid_email'));
        $this->assertFalse($validation->check('@sinusuario.com', 'valid_email'));
        $this->assertFalse($validation->check('espacios en@correo.com', 'valid_email'));
    }

    /**
     * Test: Reglas de longitud mínima de contraseña
     */
    public function testLongitudMinimaPassword(): void
    {
        $validation = service('validation');

        $this->assertTrue($validation->check('12345678', 'min_length[8]'));
        $this->assertFalse($validation->check('1234567', 'min_length[8]'));
        $this->assertFalse($validation->check('', 'min_length[8]'));
    }

    /**
     * Test: Protección contra Mass Assignment (campos permitidos)
     */
    public function testCamposPermitidosYProtegidos(): void
    {
        $allowed = $this->model->allowedFields;

        $this->assertContains('u_nbreCompleto', $allowed);
        $this->assertContains('u_correo', $allowed);
        $this->assertContains('u_tel', $allowed);
        $this->assertContains('u_avatar', $allowed);
        $this->assertContains('u_activo', $allowed);
        $this->assertContains('u_pass', $allowed);

        // Campos sensibles o llaves no deben estar en allowedFields
        $this->assertNotContains('u_id', $allowed);
        $this->assertNotContains('u_fRegistro', $allowed);
    }
}
