<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\MenuHelper;

class AnalisisDeNegocio extends Component
{
    public string $titulo = 'Analisis de Negocio';

    public const MODULOS = [
        'logistica' => ['nombre' => 'Logistica', 'titulo' => 'Análisis de Logistica', 'ruta' => 'logistica/reporte_logistica'],
        'cartera' => ['nombre' => 'Cartera de Cobros', 'titulo' => 'Cartera de Cobros', 'ruta' => 'reportes/cartera_de_cobros'],
        'ventas' => ['nombre' => 'Ventas', 'titulo' => 'Dashboard BI Ventas', 'ruta' => 'reporte/dashboard-ventas'],
        'clientes' => ['nombre' => 'Clientes', 'titulo' => 'Evaluación de clientes por nivel de facturación', 'ruta' => 'reportes/evaluacion_de_clientes_por_nivel_de_facturacion'],
        'productos' => ['nombre' => 'Productos', 'titulo' => 'Analitica de producto', 'ruta' => 'reportes/analitica_de_productos'],
        'expo' => ['nombre' => 'Expo', 'titulo' => 'Reporte Expo', 'ruta' => 'reportes/reporte_expo'],
    ];

    public function boot(): void
    {
        abort_unless(auth()->check() && MenuHelper::tieneAcceso('reportes/analisis_de_negocio'), 403);
    }

    public static function esModuloIntegrado(): bool
    {
        return request()->boolean('analisis_embed')
            && in_array(request()->path(), array_column(self::MODULOS, 'ruta'), true);
    }

    public function render(): View
    {
        $modulos = collect(self::MODULOS)->map(fn ($modulo, $id) => $modulo + [
            'id' => $id,
            'url' => url($modulo['ruta']) . '?analisis_embed=1',
            'permitido' => MenuHelper::tieneAcceso($modulo['ruta']),
        ])->values();

        return view('livewire.reportes.analisisdenegocio', compact('modulos'));
    }
}
