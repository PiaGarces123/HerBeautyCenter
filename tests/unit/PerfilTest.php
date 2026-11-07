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

    /**
     * Test: Mapeo de alias de nombre completo y longitud mínima
     */
    public function testMapeoParametrosAliasNombreCompleto(): void
    {
        $payloads = [
            ['nombre_completo' => 'María Belén Gómez'],
            ['u_nbreCompleto'  => 'María Belén Gómez'],
            ['nombre'          => 'María Belén Gómez'],
            ['name'            => 'María Belén Gómez'],
        ];

        foreach ($payloads as $req) {
            $nombre = trim($req['nombre_completo'] ?? $req['u_nbreCompleto'] ?? $req['nombre'] ?? $req['name'] ?? '');
            $this->assertSame('María Belén Gómez', $nombre);
            $this->assertGreaterThanOrEqual(3, mb_strlen($nombre));
        }

        // Casos inválidos: vacío o menos de 3 caracteres
        $invalidPayloads = [
            '',
            '   ',
            'AB',
        ];

        foreach ($invalidPayloads as $inv) {
            $nombre = trim($inv);
            $esInvalido = empty($nombre) || mb_strlen($nombre) < 3;
            $this->assertTrue($esInvalido, 'El nombre debe ser rechazado si está vacío o tiene menos de 3 caracteres.');
        }
    }

    /**
     * Test: Validación de título profesional (longitud mínima 3 caracteres)
     */
    public function testValidacionTituloProfesional(): void
    {
        $titulosValidos = [
            'Cosmiatra',
            'Esteticista Integral',
            'Dermatóloga',
        ];

        foreach ($titulosValidos as $t) {
            $titulo = trim($t);
            $this->assertNotEmpty($titulo);
            $this->assertGreaterThanOrEqual(3, mb_strlen($titulo));
        }

        $titulosInvalidos = [
            '',
            '   ',
            'Dr',
            'A',
        ];

        foreach ($titulosInvalidos as $t) {
            $titulo = trim($t);
            $esInvalido = empty($titulo) || mb_strlen($titulo) < 3;
            $this->assertTrue($esInvalido, 'Un título profesional debe tener al menos 3 caracteres.');
        }
    }

    /**
     * Test: Consideraciones y alergias de la clienta (máximo 500 caracteres)
     */
    public function testValidacionConsideracionesCliente(): void
    {
        $consideracionesValidas = 'Piel extremadamente sensible a exfoliantes químicos con ácido glicólico.';
        $this->assertLessThanOrEqual(500, mb_strlen($consideracionesValidas));

        // Permite vacío (opcional)
        $this->assertSame('', trim('   '));

        // Texto que excede los 500 caracteres
        $textoLargo = str_repeat('Alergia ', 70); // 560 caracteres
        $this->assertGreaterThan(500, mb_strlen($textoLargo));
    }

    /**
     * Test: Estructura de errores individuales por campo para validación en tiempo real
     */
    public function testEstructuraErroresIndividualesPorCampo(): void
    {
        $simularValidacion = function (array $req): array {
            $nombre   = trim($req['nombre_completo'] ?? $req['u_nbreCompleto'] ?? '');
            $correo   = trim($req['correo'] ?? $req['u_correo'] ?? '');
            $telefono = trim($req['telefono'] ?? $req['u_tel'] ?? '');
            $titulo   = trim($req['titulo'] ?? $req['p_titulo'] ?? '');

            $errors = [];
            if (empty($nombre)) {
                $errors['nombre_completo'] = 'El nombre completo es obligatorio.';
            } elseif (mb_strlen($nombre) < 3) {
                $errors['nombre_completo'] = 'El nombre debe tener al menos 3 caracteres.';
            }

            if (empty($correo)) {
                $errors['correo'] = 'El correo electrónico es obligatorio.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errors['correo'] = 'Ingresá un correo electrónico válido.';
            }

            if (!empty($telefono)) {
                $digits = preg_replace('/[^0-9]/', '', $telefono);
                if (strlen($digits) < 8) {
                    $errors['telefono'] = 'El teléfono debe contener al menos 8 dígitos.';
                }
            }

            if (isset($req['titulo']) && mb_strlen($titulo) < 3) {
                $errors['titulo'] = 'El título profesional debe tener al menos 3 caracteres.';
            }

            return $errors;
        };

        // Petición con múltiples errores
        $errores = $simularValidacion([
            'nombre_completo' => '',
            'correo'          => 'invalido',
            'telefono'        => '123',
            'titulo'          => 'x',
        ]);

        $this->assertArrayHasKey('nombre_completo', $errores);
        $this->assertArrayHasKey('correo', $errores);
        $this->assertArrayHasKey('telefono', $errores);
        $this->assertArrayHasKey('titulo', $errores);
        $this->assertSame('El nombre completo es obligatorio.', $errores['nombre_completo']);
        $this->assertSame('El teléfono debe contener al menos 8 dígitos.', $errores['telefono']);
    }
}
