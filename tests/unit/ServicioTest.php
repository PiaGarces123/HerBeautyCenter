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
}
