<?php

namespace App\Http\Livewire\Flujo;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Ventas extends Component
{
    public $titulo = 'Ventas';
    public $step = 'select'; // 'select' | 'factura_options'

    public function render()
    {
        return view('livewire.flujo.ventas');
    }

    public function selectPedido()
    {
        return $this->redirect(route('flujo.pedido'));
    }

    public function selectCotizacion()
    {
        return $this->redirect('/proforma/cotizacion/2?from=flujo');
    }

    public function selectFactura()
    {
        $this->step = 'factura_options';
    }

    public function selectFacturaSubtype($subtype)
    {
        switch ($subtype) {
            case 'clientes_a':    return $this->redirect('/ventas/estatal?from=flujo');
            case 'clientes_b':    return $this->redirect('/ventas/coporativo?from=flujo');
            case 'sr_clientes_a': return $this->redirect('/ventas/sin/restriccion/gobierno?from=flujo');
            case 'sr_clientes_b': return $this->redirect('/ventas/sin/restriccion/precio?from=flujo');
            case 'exonerada':     return $this->redirect('/ventas/exonerado/factura?from=flujo');
        }
    }

    public function goBack()
    {
        $this->step = 'select';
    }

    /**
     * Ejemplo: Guardar registro
     */
    public function guardar($request)
    {
        try {
            DB::beginTransaction();
            
            // TODO: Implementar lógica de guardado
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'mensaje' => 'Registro guardado correctamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
}
