<?php

namespace App\Http\Livewire\BoletaCompra;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class EditarBoletaCompra extends Component
{
    public int $boletaId;
    protected $boleta;
    protected $detalles;

    public function mount($id)
    {
        $this->boletaId = (int)$id;

        $this->boleta = DB::SELECTONE("
            SELECT b.*, u.name AS registrado_por
            FROM boleta_compra b
            INNER JOIN users u ON b.users_id = u.id
            WHERE b.id = " . $this->boletaId . " AND b.estado = 1");

        if (!$this->boleta) {
            abort(404, 'Boleta no encontrada.');
        }

        $this->detalles = DB::SELECT("
            SELECT id, linea, descripcion,
                   precio   + 0 AS precio,
                   cantidad + 0 AS cantidad,
                   importe  + 0 AS importe
            FROM boleta_compra_detalle
            WHERE boleta_compra_id = " . $this->boletaId . "
            ORDER BY linea ASC");
    }

    public function render()
    {
        return view('livewire.boletacompra.editarboletacompra', [
            'boleta'   => $this->boleta,
            'detalles' => $this->detalles,
        ])->layout('layouts.app', ['title' => 'Editar Boleta de Compra']);
    }
}
