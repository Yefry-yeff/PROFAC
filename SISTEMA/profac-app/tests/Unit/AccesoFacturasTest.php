<?php

namespace Tests\Unit;

use App\Support\AccesoFacturas;
use PHPUnit\Framework\TestCase;

class AccesoFacturasTest extends TestCase
{
    public function test_permite_historial_completo_solo_al_usuario_autorizado(): void
    {
        $this->assertTrue(AccesoFacturas::permiteHistorialCompleto(
            (object) ['email' => 'yefryyo@gmail.com']
        ));
        $this->assertTrue(AccesoFacturas::permiteHistorialCompleto(
            (object) ['email' => 'YEFRYYO@GMAIL.COM']
        ));
        $this->assertFalse(AccesoFacturas::permiteHistorialCompleto(
            (object) ['email' => 'otro@example.com']
        ));
        $this->assertFalse(AccesoFacturas::permiteHistorialCompleto(null));
    }
}
