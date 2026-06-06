<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Configuración global del Flujo de Venta.
 * Acceso restringido — solo Administrador.
 */
class ConfiguracionFlujo extends Component
{
    // ── Revisión de Inventario ────────────────────────────────────────────
    public bool $revisionInventarioActiva = false;

    // ── Mensajes ──────────────────────────────────────────────────────────
    public string $mensajeExito = '';
    public string $mensajeError = '';

    // ─────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->cargar();
    }

    private function cargar(): void
    {
        $config = DB::table('configuracion_revision_inventario')->first();
        $this->revisionInventarioActiva = $config ? (bool) $config->activo : false;
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCIONES
    // ─────────────────────────────────────────────────────────────────────

    public function toggleRevisionInventario(): void
    {
        $nuevo = $this->revisionInventarioActiva ? 0 : 1;

        DB::table('configuracion_revision_inventario')
            ->where('id', 1)
            ->update([
                'activo'     => $nuevo,
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

        $this->revisionInventarioActiva = (bool) $nuevo;

        $this->mensajeExito = $nuevo
            ? 'Revisión de inventario ACTIVADA. El flujo pasará por esta etapa antes de Prefactura.'
            : 'Revisión de inventario DESACTIVADA. Las ofertas ganadoras pasarán directamente a Prefactura.';
        $this->mensajeError = '';
    }

    // ─────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.flujo.configuracion-flujo');
    }
}
