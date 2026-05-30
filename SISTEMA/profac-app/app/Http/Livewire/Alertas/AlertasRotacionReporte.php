<?php

namespace App\Http\Livewire\Alertas;

use App\Exports\AlertasRotacionExport;
use App\Models\AlertaRotacionConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AlertasRotacionReporte extends Component
{
    public AlertaRotacionConfig $regla;
    public array  $productos    = [];
    public string $busqueda     = '';
    public string $ordenCampo   = 'producto_nombre';
    public string $ordenDir     = 'asc';
    public bool   $cargando     = false;

    public function mount(int $id): void
    {
        $this->regla     = AlertaRotacionConfig::findOrFail($id);
        $this->cargarProductos();

        // Marcar notificaciones de esta regla como leídas para el usuario actual
        Auth::user()
            ->unreadNotifications()
            ->whereRaw("JSON_EXTRACT(data, '$.regla_id') = ?", [$id])
            ->each(fn ($n) => $n->markAsRead());
    }

    public function cargarProductos(): void
    {
        $this->productos = $this->regla->getProductosAfectados()
            ->map(fn ($p) => (array) $p)
            ->toArray();
    }

    public function ordenar(string $campo): void
    {
        if ($this->ordenCampo === $campo) {
            $this->ordenDir = $this->ordenDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenCampo = $campo;
            $this->ordenDir   = 'asc';
        }
    }

    public function getProductosFiltradosProperty(): array
    {
        $lista = collect($this->productos);

        if ($this->busqueda !== '') {
            $b     = mb_strtolower($this->busqueda);
            $lista = $lista->filter(fn ($p) => str_contains(mb_strtolower((string)($p['producto_nombre'] ?? '')), $b));
        }

        $lista = $lista->sortBy(
            fn ($p) => $p[$this->ordenCampo] ?? '',
            SORT_NATURAL | SORT_FLAG_CASE,
            $this->ordenDir === 'desc'
        );

        return $lista->values()->toArray();
    }

    public function descargarExcel()
    {
        $nombre = 'alerta_' . str_replace(' ', '_', mb_strtolower($this->regla->nombre)) . '_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AlertasRotacionExport($this->regla), $nombre);
    }

    public function render()
    {
        return view('livewire.alertas.alertas-rotacion-reporte', [
            'productosFiltrados' => $this->productosFiltrados,
        ])->layout('layouts.app');
    }
}
