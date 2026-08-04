<?php

namespace App\Http\Livewire\FlujoDeVenta;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Expo extends Component
{
    public $titulo = 'Expo';
    public $expoEditandoId;
    public $nombre = '';
    public $descripcion = '';
    public $estado = 'Inactivo';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $bodegasSeleccionadas = [];
    public $escalasSeleccionadas = [];
    public $descuentos = [];
    public $mostrarFormulario = false;

    public function nueva(): void
    {
        $this->resetForm();
        $this->fechaInicio = now()->format('Y-m-d\TH:i');
        $this->descuentos = [['venta_minima' => '', 'porcentaje_descuento' => '']];
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $expo = DB::table('expo')->where('id', $id)->first();
        abort_unless($expo, 404);

        $this->expoEditandoId = (int) $expo->id;
        $this->nombre = $expo->nombre;
        $this->descripcion = $expo->descripcion ?? '';
        $this->estado = $expo->estado;
        $this->fechaInicio = date('Y-m-d\TH:i', strtotime($expo->fecha_inicio));
        $this->fechaFin = $expo->fecha_fin ? date('Y-m-d\TH:i', strtotime($expo->fecha_fin)) : '';
        $this->bodegasSeleccionadas = DB::table('expo_bodega')->where('expo_id', $id)->pluck('bodega_id')->map(fn ($value) => (string) $value)->all();
        $this->escalasSeleccionadas = DB::table('expo_escala')->where('expo_id', $id)->pluck('escala_id')->map(fn ($value) => (string) $value)->all();
        $this->descuentos = DB::table('expo_descuento')->where('expo_id', $id)->orderBy('orden')
            ->get(['venta_minima', 'porcentaje_descuento'])
            ->map(fn ($regla) => [
                'venta_minima' => (string) $regla->venta_minima,
                'porcentaje_descuento' => (string) $regla->porcentaje_descuento,
            ])->all();
        $this->mostrarFormulario = true;
    }

    public function agregarDescuento(): void
    {
        $this->descuentos[] = ['venta_minima' => '', 'porcentaje_descuento' => ''];
    }

    public function eliminarDescuento(int $indice): void
    {
        if (isset($this->descuentos[$indice])) {
            unset($this->descuentos[$indice]);
            $this->descuentos = array_values($this->descuentos);
        }
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:5000',
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
            'fechaInicio' => 'required|date',
            'fechaFin' => 'nullable|date|after:fechaInicio',
            'bodegasSeleccionadas' => 'required|array|min:1',
            'bodegasSeleccionadas.*' => 'integer|exists:bodega,id',
            'escalasSeleccionadas' => 'required|array|min:1',
            'escalasSeleccionadas.*' => 'integer|exists:categoria_precios,id',
            'descuentos' => 'array',
            'descuentos.*.venta_minima' => 'required|numeric|min:0|distinct',
            'descuentos.*.porcentaje_descuento' => 'required|numeric|min:0|max:100',
        ], [
            'bodegasSeleccionadas.required' => 'Seleccione al menos una bodega.',
            'escalasSeleccionadas.required' => 'Seleccione al menos una escala.',
            'fechaFin.after' => 'La fecha final debe ser posterior a la fecha de inicio.',
        ]);

        try {
            DB::beginTransaction();

            DB::table('expo')->where('estado', 'Activo')->whereNotNull('fecha_fin')->where('fecha_fin', '<', now())
                ->update(['estado' => 'Inactivo', 'updated_by' => Auth::id(), 'updated_at' => now()]);

            $original = $this->expoEditandoId
                ? DB::table('expo')->where('id', $this->expoEditandoId)->lockForUpdate()->first()
                : null;

            if ($this->estado === 'Activo') {
                $activa = DB::table('expo')->where('estado', 'Activo')->lockForUpdate()->first();
                if ($activa && (!$original || (int) $activa->id !== (int) $original->id)) {
                    $this->addError('estado', 'Ya existe una Expo activa: ' . $activa->nombre . '.');
                    DB::rollBack();
                    return;
                }
            }

            if ($original && $original->estado === 'Activo') {
                DB::table('expo')->where('id', $original->id)->update([
                    'estado' => 'Inactivo',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
            }

            $expoId = DB::table('expo')->insertGetId([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion ?: null,
                'estado' => $this->estado,
                'fecha_inicio' => $this->fechaInicio,
                'fecha_fin' => $this->fechaFin ?: null,
                'expo_anterior_id' => $original->id ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('expo_bodega')->insert(array_map(fn ($id) => [
                'expo_id' => $expoId, 'bodega_id' => (int) $id, 'created_at' => now(),
            ], array_unique($this->bodegasSeleccionadas)));

            DB::table('expo_escala')->insert(array_map(fn ($id) => [
                'expo_id' => $expoId, 'escala_id' => (int) $id, 'created_at' => now(),
            ], array_unique($this->escalasSeleccionadas)));

            foreach (array_values($this->descuentos) as $orden => $regla) {
                DB::table('expo_descuento')->insert([
                    'expo_id' => $expoId,
                    'venta_minima' => $regla['venta_minima'],
                    'porcentaje_descuento' => $regla['porcentaje_descuento'],
                    'orden' => $orden + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            session()->flash('success', $original ? 'La nueva versión de la Expo fue guardada.' : 'Expo creada correctamente.');
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'No se pudo guardar la Expo: ' . $e->getMessage());
        }
    }

    public function cancelar(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'expoEditandoId', 'nombre', 'descripcion', 'fechaInicio', 'fechaFin',
            'bodegasSeleccionadas', 'escalasSeleccionadas', 'descuentos', 'mostrarFormulario',
        ]);
        $this->estado = 'Inactivo';
        $this->resetValidation();
    }

    public function render()
    {
        $expos = DB::table('expo as e')
            ->join('users as creador', 'creador.id', '=', 'e.created_by')
            ->join('users as editor', 'editor.id', '=', 'e.updated_by')
            ->select('e.*', 'creador.name as creado_por', 'editor.name as modificado_por')
            ->orderByDesc('e.id')->get();

        $bodegas = DB::table('bodega')->where('estado_id', 1)
            ->whereRaw('UPPER(TRIM(nombre)) <> ?', ['SIN EXISTENCIA COTIZACION'])
            ->orderBy('nombre')
            ->get(['id', DB::raw("CONCAT(nombre, ' (#', id, ')') as nombre")]);
        $escalas = DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->where('cp.estado_id', 1)
            ->where('cce.estado_id', 1)
            ->orderBy('cce.nombre_categoria')->orderBy('cp.nombre')
            ->get(['cp.id', DB::raw("CONCAT(cce.nombre_categoria, ' - ', cp.nombre) as nombre")]);

        return view('livewire.flujodeventa.expo', compact('expos', 'bodegas', 'escalas'));
    }
}
