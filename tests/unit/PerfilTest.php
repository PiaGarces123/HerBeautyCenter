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

    /**
     * Test: Validación de redes sociales (HTTPS, dominios oficiales y perfiles válidos)
     */
    public function testValidacionRedesSociales(): void
    {
        $plataformas = [
            'instagram' => ['hosts' => ['instagram.com', 'www.instagram.com']],
            'facebook'  => ['hosts' => ['facebook.com', 'www.facebook.com', 'fb.com', 'www.fb.com', 'm.facebook.com']],
            'tiktok'    => ['hosts' => ['tiktok.com', 'www.tiktok.com']],
            'x'         => ['hosts' => ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com']],
            'linkedin'  => ['hosts' => ['linkedin.com', 'www.linkedin.com']],
            'whatsapp'  => ['hosts' => ['wa.me', 'api.whatsapp.com', 'chat.whatsapp.com', 'web.whatsapp.com', 'whatsapp.com', 'www.whatsapp.com']],
        ];

        // Validar enlaces correctos y seguros
        $validUrls = [
            'instagram' => 'https://instagram.com/herbeautycenter',
            'facebook'  => 'https://facebook.com/herbeautycenter',
            'tiktok'    => 'https://tiktok.com/@herbeautycenter',
            'x'         => 'https://x.com/herbeauty',
            'linkedin'  => 'https://linkedin.com/in/herbeauty',
            'whatsapp'  => 'https://wa.me/5492664159744',
        ];

        foreach ($validUrls as $tipo => $url) {
            $parsed = parse_url($url);
            $this->assertSame('https', $parsed['scheme'], "El enlace de {$tipo} debe ser HTTPS");
            $this->assertContains($parsed['host'], $plataformas[$tipo]['hosts'], "El host de {$tipo} debe pertenecer a los dominios oficiales");
            $this->assertNotEmpty(trim($parsed['path'] ?? '', '/'), "El enlace de {$tipo} debe contener el perfil de la cuenta");
        }

        // Validar enlaces inseguros (HTTP) o dominios no permitidos
        $this->assertFalse(str_starts_with('http://instagram.com/usuario', 'https://'), 'HTTP no debe ser aceptado');
        $parsedFake = parse_url('https://phishing.com/instagram.com');
        $this->assertNotContains($parsedFake['host'], $plataformas['instagram']['hosts'], 'Dominios falsos no deben ser aceptados');
    }
}
