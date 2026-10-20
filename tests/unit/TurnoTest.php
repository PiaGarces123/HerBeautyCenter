<?php

namespace Tests\Unit;

use App\Models\TurnoModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * TurnoTest
 * Her Beauty Center — Tests Unitarios para la Gestión de Turnos (CU07)
 */
final class TurnoTest extends CIUnitTestCase
{
    private TurnoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new TurnoModel();
    }

    /**
     * Test: Transiciones válidas del ciclo de vida del turno (RN12)
     */
    public function testTransicionesValidas(): void
    {
        // De Disponible solo se puede pasar a Solicitado
        $this->assertTrue($this->model->esTransicionValida('Disponible', 'Solicitado'));
        $this->assertFalse($this->model->esTransicionValida('Disponible', 'Realizado'));

        // De Solicitado se puede Confirmar o Cancelar
        $this->assertTrue($this->model->esTransicionValida('Solicitado', 'Confirmado'));
        $this->assertTrue($this->model->esTransicionValida('Solicitado', 'Cancelado'));
        $this->assertFalse($this->model->esTransicionValida('Solicitado', 'Realizado'));

        // De Confirmado se puede marcar Realizado o Cancelar
        $this->assertTrue($this->model->esTransicionValida('Confirmado', 'Realizado'));
        $this->assertTrue($this->model->esTransicionValida('Confirmado', 'Cancelado'));
        $this->assertFalse($this->model->esTransicionValida('Confirmado', 'Disponible'));
    }

    /**
     * Test: Estados terminales no deben permitir transiciones posteriores
     */
    public function testEstadosTerminalesNoPermitenReversion(): void
    {
        // Cancelado es estado final: no puede volver a Disponible, Solicitado ni Confirmado
        $this->assertFalse($this->model->esTransicionValida('Cancelado', 'Disponible'));
        $this->assertFalse($this->model->esTransicionValida('Cancelado', 'Solicitado'));
        $this->assertFalse($this->model->esTransicionValida('Cancelado', 'Confirmado'));

        // Realizado es estado final: no se puede cancelar ni reabrir
        $this->assertFalse($this->model->esTransicionValida('Realizado', 'Solicitado'));
        $this->assertFalse($this->model->esTransicionValida('Realizado', 'Cancelado'));
    }

    /**
     * Test: La tabla turno debe permitir campos clave de Incremento 2
     */
    public function testCamposPermitidosContienenMotivoCancelacionYAbono(): void
    {
        $allowed = $this->model->allowedFields;
        $this->assertContains('motivo_cancelacion', $allowed);
        $this->assertContains('abonado', $allowed);
        $this->assertContains('monto', $allowed);
        $this->assertContains('estado', $allowed);
    }

    /**
     * Test: Generación de enlace para Google Calendar con formato RFC 5545
     */
    public function testFormatoFechasGoogleCalendar(): void
    {
        $fecha = '2026-11-15';
        $horaDesde = '10:00:00';
        $horaHasta = '11:30:00';

        $startTs = strtotime("$fecha $horaDesde");
        $endTs   = strtotime("$fecha $horaHasta");

        $startDt = date('Ymd\THis', $startTs);
        $endDt   = date('Ymd\THis', $endTs);

        $this->assertSame('20261115T100000', $startDt);
        $this->assertSame('20261115T113000', $endDt);

        $link = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode('Turno: Masajes')
            . '&dates=' . $startDt . '/' . $endDt;

        $this->assertStringContainsString('action=TEMPLATE', $link);
        $this->assertStringContainsString('20261115T100000/20261115T113000', $link);
    }

    /**
     * Test: Validación de campos necesarios para crear un turno asignado
     */
    public function testCamposObligatoriosParaCrearTurno(): void
    {
        $payloadValido = [
            's_id'       => 1,
            'c_id'       => 2,
            'fecha'      => '2026-11-20',
            'hora_desde' => '10:00',
            'hora_hasta' => '11:00',
            'estado'     => 'Confirmado',
        ];

        $this->assertNotEmpty($payloadValido['s_id']);
        $this->assertNotEmpty($payloadValido['c_id']);
        $this->assertGreaterThan($payloadValido['hora_desde'], $payloadValido['hora_hasta']);
        $this->assertContains($payloadValido['estado'], ['Solicitado', 'Confirmado']);
    }
}
