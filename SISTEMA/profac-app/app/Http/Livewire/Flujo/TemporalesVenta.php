<?php

namespace App\Http\Livewire\Flujo;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TemporalesVenta extends Component
{
    public string $tipo = 'oferta';

    public function mount(string $tipo): void
    {
        abort_unless(in_array($tipo, ['oferta', 'factura'], true), 404);
        $this->tipo = $tipo;
        $this->limpiarVencidos();
    }

    public function reanudar(int $id)
    {
        $temporal = $this->queryPropios()->where('id', $id)->first();
        abort_unless($temporal, 404);
        abort_unless(str_starts_with($temporal->url_reanudacion, '/') && !str_starts_with($temporal->url_reanudacion, '//'), 422);

        $separador = str_contains($temporal->url_reanudacion, '?') ? '&' : '?';
        return $this->redirect($temporal->url_reanudacion . $separador . 'temporal_id=' . $temporal->id);
    }

    public function eliminar(int $id): void
    {
        $this->queryPropios()->where('id', $id)->delete();
        session()->flash('temporal_success', 'Registro temporal eliminado.');
    }

    public function render()
    {
        $this->limpiarVencidos();
        $temporales = $this->queryPropios()->orderByDesc('updated_at')->get();

        return view('livewire.flujo.temporales-venta', compact('temporales'));
    }

    private function queryPropios()
    {
        return DB::table('venta_temporal')
            ->where('usuario_id', Auth::id())
            ->where('tipo', $this->tipo)
            ->where('expira_at', '>', now());
    }

    private function limpiarVencidos(): void
    {
        DB::table('venta_temporal')->where('expira_at', '<=', now())->delete();
    }
}
