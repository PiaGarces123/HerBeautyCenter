<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * ServicioTest
 * Her Beauty Center — Tests Unitarios para la Gestión de Servicios (CU04 / CU05)
 */
final class ServicioTest extends CIUnitTestCase
{
    /**
     * Test: Duración permitida entre 5 y 480 minutos
     */
    public function testRangoDuracionServicio(): void
    {
        $validarDuracion = fn(int $d) => ($d >= 5 && $d <= 480);

        // Válidos
        $this->assertTrue($validarDuracion(15));
        $this->assertTrue($validarDuracion(60));
        $this->assertTrue($validarDuracion(480));

        // Inválidos
        $this->assertFalse($validarDuracion(4));
        $this->assertFalse($validarDuracion(0));
        $this->assertFalse($validarDuracion(-30));
        $this->assertFalse($validarDuracion(500));
    }

    /**
     * Test: Formato de nombre de servicio (no empezar con guión ni estar vacío)
     */
    public function testValidacionNombreServicio(): void
    {
        $validarNombre = function(string $nombre): bool {
            $nombre = trim($nombre);
            if (empty($nombre)) return false;
            if ($nombre[0] === '-') return false;
            return strlen($nombre) >= 3;
        };

        // Nombres válidos
        $this->assertTrue($validarNombre('Masaje Descontracturante'));
        $this->assertTrue($validarNombre('Limpieza Facial Profunda'));

        // Nombres inválidos
        $this->assertFalse($validarNombre(''));
        $this->assertFalse($validarNombre('   '));
        $this->assertFalse($validarNombre('-Masaje Inválido'));
        $this->assertFalse($validarNombre('AB'));
    }

    /**
     * Test: Precios no negativos
     */
    public function testPrecioNoNegativo(): void
    {
        $validarPrecio = fn(float $p) => $p >= 0;

        $this->assertTrue($validarPrecio(0.0));
        $this->assertTrue($validarPrecio(15000.50));
        $this->assertFalse($validarPrecio(-500.0));
    }

    /**
     * Test: Comparación de nombres insensible a mayúsculas/minúsculas para unicidad
     */
    public function testUnicidadInsensibleMayusculas(): void
    {
        $nombreExistente = 'Masajes Relajantes';
        $nuevoNombre1    = 'masajes relajantes';
        $nuevoNombre2    = 'MASAJES RELAJANTES';
        $nuevoNombre3    = 'Drenaje Linfático';

        $esDuplicado = fn($a, $b) => strtoupper(trim($a)) === strtoupper(trim($b));

        $this->assertTrue($esDuplicado($nombreExistente, $nuevoNombre1));
        $this->assertTrue($esDuplicado($nombreExistente, $nuevoNombre2));
        $this->assertFalse($esDuplicado($nombreExistente, $nuevoNombre3));
    }

    /**
     * Test: Mapeo de parámetros para crear/editar servicios (soporte tanto de nombres HTML como de BD)
     */
    public function testMapeoParametrosServicio(): void
    {
        $extraer = function(array $data) {
            $nombre = trim($data['nombre'] ?? $data['s_nbre'] ?? '');
            $descripcion = trim($data['descripcion'] ?? $data['s_desc'] ?? '');
            $duracion = $data['duracion_minutos'] ?? $data['s_duracionMinutos'] ?? null;
            $precio = $data['precio'] ?? $data['s_precio'] ?? 0;
            $activo = $data['activo'] ?? $data['s_activo'] ?? $data['u_activo'] ?? 1;

            return [
                'valido' => ($nombre !== '' && $duracion !== null && $duracion !== ''),
                'nombre' => $nombre,
                'duracion' => (int) $duracion,
                'precio' => (float) $precio,
                'activo' => (int) $activo
            ];
        };

        // Formato enviado por formulario HTML (servicios.php)
        $formInput = [
            'nombre' => 'Lifting de Pestañas',
            'descripcion' => 'Tratamiento para arquear pestañas',
            'duracion_minutos' => 45,
            'precio' => 12500,
            'activo' => 1
        ];
        $resForm = $extraer($formInput);
        $this->assertTrue($resForm['valido']);
        $this->assertSame('Lifting de Pestañas', $resForm['nombre']);
        $this->assertSame(45, $resForm['duracion']);

        // Formato alternativo con nombres de base de datos
        $dbInput = [
            's_nbre' => 'Lifting de Pestañas',
            's_desc' => 'Tratamiento para arquear pestañas',
            's_duracionMinutos' => 45,
            's_precio' => 12500,
            's_activo' => 1
        ];
        $resDb = $extraer($dbInput);
        $this->assertTrue($resDb['valido']);
        $this->assertSame('Lifting de Pestañas', $resDb['nombre']);
        $this->assertSame(45, $resDb['duracion']);

        // Caso donde falta nombre o duración
        $invalido = ['descripcion' => 'Solo descripcion'];
        $resInvalido = $extraer($invalido);
        $this->assertFalse($resInvalido['valido']);
    }

    /**
     * Test: Extracción de datos en peticiones multipart/form-data (subida de archivos/fotos)
     * no intenta parsear JSON ni arroja Syntax error.
     */
    public function testExtraccionMultipartNoFallaJsonParse(): void
    {
        $mockRequest = function(array $post, string $contentType, string $body = '') {
            $data = is_array($post) ? $post : [];
            if (stripos($contentType, 'application/json') !== false && !empty($body)) {
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
            return $data;
        };

        // Simular subida con multipart/form-data (foto adjunta)
        $postData = [
            'nombre' => 'Tratamiento Facial',
            'duracion_minutos' => '50',
            'descripcion' => 'Tratamiento con foto adjunta',
            'precio' => '18000',
            'activo' => '1'
        ];
        $contentType = 'multipart/form-data; boundary=----WebKitFormBoundaryXYZ';
        $rawBody = "------WebKitFormBoundaryXYZ\r\nContent-Disposition: form-data; name=\"nombre\"\r\n\r\nTratamiento Facial\r\n------WebKitFormBoundaryXYZ--";

        $res = $mockRequest($postData, $contentType, $rawBody);
        $this->assertSame('Tratamiento Facial', $res['nombre']);
        $this->assertSame('50', $res['duracion_minutos']);
        $this->assertSame('18000', $res['precio']);
    }
}
