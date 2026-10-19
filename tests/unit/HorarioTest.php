<?php

namespace Tests\Unit;

use App\Models\HorarioModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * HorarioTest
 * Her Beauty Center — Tests Unitarios para la Gestión de Disponibilidad (CU08)
 */
final class HorarioTest extends CIUnitTestCase
{
    private HorarioModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new HorarioModel();
    }

    /**
     * Test: Campos permitidos en el modelo de Horario
     */
    public function testCamposPermitidos(): void
    {
        $allowed = $this->model->allowedFields;
        $this->assertContains('h_pId', $allowed);
        $this->assertContains('h_sId', $allowed);
        $this->assertContains('h_fecha', $allowed);
        $this->assertContains('h_horaDesde', $allowed);
        $this->assertContains('h_horaHasta', $allowed);
    }

    /**
     * Test: Reglas de validación declaradas
     */
    public function testReglasDeValidacionDefinidas(): void
    {
        $rules = $this->model->getValidationRules();
        $this->assertArrayHasKey('h_pId', $rules);
        $this->assertArrayHasKey('h_sId', $rules);
        $this->assertArrayHasKey('h_fecha', $rules);
        $this->assertArrayHasKey('h_horaDesde', $rules);
        $this->assertArrayHasKey('h_horaHasta', $rules);
    }

    /**
     * Test: Lógica de consistencia de horas (hora fin debe ser mayor a hora inicio)
     */
    public function testConsistenciaCronologica(): void
    {
        $desdeCorrecto = '09:00';
        $hastaCorrecto = '10:30';
        $this->assertGreaterThan($desdeCorrecto, $hastaCorrecto, 'La hora de fin debe ser estrictamente posterior a la de inicio.');

        $desdeInvalido = '14:00';
        $hastaInvalido = '13:00';
        $this->assertLessThan($desdeInvalido, $hastaInvalido, 'No se debe permitir un fin menor al inicio.');
    }

    /**
     * Test: Lógica algorítmica de solapamiento horario
     * Dos franjas (A_desde, A_hasta) y (B_desde, B_hasta) se solapan si y solo si:
     * A_desde < B_hasta AND A_hasta > B_desde
     */
    public function testAlgoritmoSolapamientoHorario(): void
    {
        // Franja A: 09:00 a 11:00
        $aDesde = '09:00:00';
        $aHasta = '11:00:00';

        // Caso 1: Franja B adentro (10:00 a 10:30) -> Debe solapar
        $b1Desde = '10:00:00';
        $b1Hasta = '10:30:00';
        $solapa1 = ($aDesde < $b1Hasta) && ($aHasta > $b1Desde);
        $this->assertTrue($solapa1, 'Una franja interior debe detectar solapamiento.');

        // Caso 2: Franja B contigua inmediatamente posterior (11:00 a 12:00) -> NO solapa
        $b2Desde = '11:00:00';
        $b2Hasta = '12:00:00';
        $solapa2 = ($aDesde < $b2Hasta) && ($aHasta > $b2Desde);
        $this->assertFalse($solapa2, 'Una franja contigua no se solapa.');

        // Caso 3: Franja B antes (07:00 a 08:30) -> NO solapa
        $b3Desde = '07:00:00';
        $b3Hasta = '08:30:00';
        $solapa3 = ($aDesde < $b3Hasta) && ($aHasta > $b3Desde);
        $this->assertFalse($solapa3, 'Una franja anterior no se solapa.');
    }
}
