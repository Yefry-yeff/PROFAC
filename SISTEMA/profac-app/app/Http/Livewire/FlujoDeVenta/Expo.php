<?php

namespace App\Http\Livewire\FlujoDeVenta;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Expo extends Component
{
    public $titulo = 'Expo';
    public $expoEditandoId;
    public $expoDuplicandoId;
    public $nombre = '';
    public $descripcion = '';
    public $estado = 'Inactivo';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $bodegasSeleccionadas = [];
    public $escalasSeleccionadas = [];
    public $usuariosSeleccionados = [];
    public $busquedaUsuario = '';
    public $descuentos = [];
    public $mostrarFormulario = false;
    public $expoDetalle = [];
    public $mostrarDetalle = false;

    public function nueva(): void
    {
        $this->resetForm();
        $this->fechaInicio = now()->format('Y-m-d\TH:i');
        $this->descuentos = [['venta_minima' => '', 'porcentaje_descuento' => '']];
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $this->sincronizarExposVencidas();
        $expo = DB::table('expo')->where('id', $id)->first();
        abort_unless($expo, 404);

        if ($this->estaFinalizada($expo)) {
            session()->flash('error', 'La Expo ya finalizó y no puede editarse. Puede duplicarla para crear una nueva.');
            return;
        }

        $this->expoEditandoId = (int) $expo->id;
        $this->expoDuplicandoId = null;
        $this->nombre = $expo->nombre;
        $this->descripcion = $expo->descripcion ?? '';
        $this->estado = $expo->estado;
        $this->fechaInicio = date('Y-m-d\TH:i', strtotime($expo->fecha_inicio));
        $this->fechaFin = $expo->fecha_fin ? date('Y-m-d\TH:i', strtotime($expo->fecha_fin)) : '';
        $this->bodegasSeleccionadas = DB::table('expo_bodega')->where('expo_id', $id)->pluck('bodega_id')->map(fn ($value) => (string) $value)->all();
        $this->escalasSeleccionadas = DB::table('expo_escala')->where('expo_id', $id)->pluck('escala_id')->map(fn ($value) => (string) $value)->all();
        $this->usuariosSeleccionados = DB::table('expo_usuario')->where('expo_id', $id)->pluck('usuario_id')->map(fn ($value) => (string) $value)->all();
        $this->descuentos = DB::table('expo_descuento')->where('expo_id', $id)->orderBy('orden')
            ->get(['venta_minima', 'porcentaje_descuento'])
            ->map(fn ($regla) => [
                'venta_minima' => (string) $regla->venta_minima,
                'porcentaje_descuento' => (string) $regla->porcentaje_descuento,
            ])->all();
        $this->mostrarFormulario = true;
    }

    public function duplicar(int $id): void
    {
        $expo = DB::table('expo')->where('id', $id)->first();
        abort_unless($expo, 404);

        $this->resetForm();
        $this->expoDuplicandoId = (int) $expo->id;
        $this->nombre = 'Copia de ' . $expo->nombre;
        $this->descripcion = $expo->descripcion ?? '';
        $this->estado = 'Inactivo';
        $this->fechaInicio = now()->format('Y-m-d\TH:i');
        $this->fechaFin = '';
        $this->bodegasSeleccionadas = DB::table('expo_bodega')->where('expo_id', $id)->pluck('bodega_id')->map(fn ($value) => (string) $value)->all();
        $this->escalasSeleccionadas = DB::table('expo_escala')->where('expo_id', $id)->pluck('escala_id')->map(fn ($value) => (string) $value)->all();
        $this->usuariosSeleccionados = DB::table('expo_usuario')->where('expo_id', $id)->pluck('usuario_id')->map(fn ($value) => (string) $value)->all();
        $this->descuentos = DB::table('expo_descuento')->where('expo_id', $id)->orderBy('orden')
            ->get(['venta_minima', 'porcentaje_descuento'])
            ->map(fn ($regla) => [
                'venta_minima' => (string) $regla->venta_minima,
                'porcentaje_descuento' => (string) $regla->porcentaje_descuento,
            ])->all();
        $this->mostrarFormulario = true;
    }

    public function alternarTodasBodegas(): void
    {
        $disponibles = $this->idsBodegasDisponibles();
        $this->bodegasSeleccionadas = empty(array_diff($disponibles, array_map('strval', $this->bodegasSeleccionadas)))
            ? []
            : $disponibles;
    }

    public function alternarTodasEscalas(): void
    {
        $disponibles = $this->idsEscalasDisponibles();
        $this->escalasSeleccionadas = empty(array_diff($disponibles, array_map('strval', $this->escalasSeleccionadas)))
            ? []
            : $disponibles;
    }

    public function agregarUsuario(int $usuarioId): void
    {
        $existe = DB::table('users')->where('id', $usuarioId)->where('estado_id', 1)->exists();
        if (!$existe) {
            return;
        }

        $usuarioId = (string) $usuarioId;
        if (!in_array($usuarioId, array_map('strval', $this->usuariosSeleccionados), true)) {
            $this->usuariosSeleccionados[] = $usuarioId;
        }
        $this->busquedaUsuario = '';
        $this->resetValidation('usuariosSeleccionados');
    }

    public function eliminarUsuario(int $usuarioId): void
    {
        $this->usuariosSeleccionados = array_values(array_filter(
            $this->usuariosSeleccionados,
            fn ($id) => (int) $id !== $usuarioId
        ));
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
        $expoExistente = $this->expoEditandoId
            ? DB::table('expo')->where('id', $this->expoEditandoId)->first()
            : null;
        abort_if($this->expoEditandoId && !$expoExistente, 404);

        foreach ($this->descuentos as &$regla) {
            $regla['venta_minima'] = str_replace(',', '', (string) ($regla['venta_minima'] ?? ''));
        }
        unset($regla);

        if ($expoExistente) {
            if ($this->estaFinalizada($expoExistente)) {
                $this->resetForm();
                session()->flash('error', 'La Expo ya finalizó y no puede editarse. Puede duplicarla para crear una nueva.');
                return;
            }
            $this->nombre = $expoExistente->nombre;
            $this->fechaInicio = date('Y-m-d\TH:i', strtotime($expoExistente->fecha_inicio));
        }

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
            'usuariosSeleccionados' => 'required|array|min:1',
            'usuariosSeleccionados.*' => 'integer|distinct|exists:users,id',
            'descuentos' => 'array',
            'descuentos.*.venta_minima' => 'required|numeric|min:0|distinct',
            'descuentos.*.porcentaje_descuento' => 'required|numeric|min:0|max:100',
        ], [
            'bodegasSeleccionadas.required' => 'Seleccione al menos una bodega.',
            'escalasSeleccionadas.required' => 'Seleccione al menos una escala.',
            'usuariosSeleccionados.required' => 'Agregue al menos un usuario autorizado.',
            'fechaFin.after' => 'La fecha final debe ser posterior a la fecha de inicio.',
        ]);

        try {
            DB::beginTransaction();

            $this->sincronizarExposVencidas();

            $expoExistente = $this->expoEditandoId
                ? DB::table('expo')->where('id', $this->expoEditandoId)->lockForUpdate()->first()
                : null;

            if ($this->estado === 'Activo') {
                $activa = DB::table('expo')->where('estado', 'Activo')
                    ->when($expoExistente, fn ($query) => $query->where('id', '<>', $expoExistente->id))
                    ->lockForUpdate()
                    ->first();
                if ($activa) {
                    $this->addError('estado', 'Ya existe una Expo activa: ' . $activa->nombre . '.');
                    DB::rollBack();
                    return;
                }
            }

            if ($expoExistente) {
                DB::table('expo')->where('id', $expoExistente->id)->update([
                    'descripcion' => $this->descripcion ?: null,
                    'estado' => $this->estado,
                    'fecha_fin' => $this->fechaFin ?: null,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
                $expoId = (int) $expoExistente->id;

                DB::table('expo_bodega')->where('expo_id', $expoId)->delete();
                DB::table('expo_escala')->where('expo_id', $expoId)->delete();
                DB::table('expo_usuario')->where('expo_id', $expoId)->delete();
                DB::table('expo_descuento')->where('expo_id', $expoId)->delete();
            } else {
                $expoId = DB::table('expo')->insertGetId([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion ?: null,
                    'estado' => $this->estado,
                    'fecha_inicio' => $this->fechaInicio,
                    'fecha_fin' => $this->fechaFin ?: null,
                    'expo_anterior_id' => $this->expoDuplicandoId ?: null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('expo_bodega')->insert(array_map(fn ($id) => [
                'expo_id' => $expoId, 'bodega_id' => (int) $id, 'created_at' => now(),
            ], array_unique($this->bodegasSeleccionadas)));

            DB::table('expo_escala')->insert(array_map(fn ($id) => [
                'expo_id' => $expoId, 'escala_id' => (int) $id, 'created_at' => now(),
            ], array_unique($this->escalasSeleccionadas)));

            DB::table('expo_usuario')->insert(array_map(fn ($id) => [
                'expo_id' => $expoId, 'usuario_id' => (int) $id, 'created_at' => now(),
            ], array_unique($this->usuariosSeleccionados)));

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
            session()->flash('success', $expoExistente ? 'Expo actualizada correctamente.' : 'Expo creada correctamente.');
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

    public function verDetalle(int $id): void
    {
        $expo = DB::table('expo as e')
            ->join('users as creador', 'creador.id', '=', 'e.created_by')
            ->join('users as editor', 'editor.id', '=', 'e.updated_by')
            ->where('e.id', $id)
            ->select('e.*', 'creador.name as creado_por', 'editor.name as modificado_por')
            ->first();
        abort_unless($expo, 404);

        $this->expoDetalle = [
            'expo' => (array) $expo,
            'bodegas' => DB::table('expo_bodega as eb')->join('bodega as b', 'b.id', '=', 'eb.bodega_id')
                ->where('eb.expo_id', $id)->orderBy('b.nombre')->pluck('b.nombre')->all(),
            'escalas' => DB::table('expo_escala as ee')
                ->join('categoria_precios as cp', 'cp.id', '=', 'ee.escala_id')
                ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
                ->where('ee.expo_id', $id)->orderBy('cce.nombre_categoria')->orderBy('cp.nombre')
                ->selectRaw("CONCAT(cce.nombre_categoria, ' - ', cp.nombre) as nombre")->pluck('nombre')->all(),
            'usuarios' => DB::table('expo_usuario as eu')->join('users as u', 'u.id', '=', 'eu.usuario_id')
                ->where('eu.expo_id', $id)->orderBy('u.name')->get(['u.name', 'u.email'])->map(fn ($usuario) => (array) $usuario)->all(),
            'descuentos' => DB::table('expo_descuento')->where('expo_id', $id)->orderBy('orden')
                ->get(['venta_minima', 'porcentaje_descuento'])->map(fn ($regla) => (array) $regla)->all(),
        ];
        $this->mostrarDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->mostrarDetalle = false;
        $this->expoDetalle = [];
    }

    private function resetForm(): void
    {
        $this->reset([
            'expoEditandoId', 'expoDuplicandoId', 'nombre', 'descripcion', 'fechaInicio', 'fechaFin',
            'bodegasSeleccionadas', 'escalasSeleccionadas', 'usuariosSeleccionados',
            'busquedaUsuario', 'descuentos', 'mostrarFormulario',
        ]);
        $this->estado = 'Inactivo';
        $this->resetValidation();
    }

    public function render()
    {
        $this->sincronizarExposVencidas();

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

        $usuariosAgregados = DB::table('users')
            ->whereIn('id', array_map('intval', $this->usuariosSeleccionados))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $usuariosEncontrados = collect();
        $busqueda = trim($this->busquedaUsuario);
        if (mb_strlen($busqueda) >= 2) {
            $usuariosEncontrados = DB::table('users')
                ->where('estado_id', 1)
                ->whereNotIn('id', array_map('intval', $this->usuariosSeleccionados))
                ->where(function ($query) use ($busqueda) {
                    $query->where('name', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%");
                })
                ->orderBy('name')
                ->limit(12)
                ->get(['id', 'name', 'email']);
        }

        return view('livewire.flujodeventa.expo', compact(
            'expos', 'bodegas', 'escalas', 'usuariosAgregados', 'usuariosEncontrados'
        ));
    }

    private function idsBodegasDisponibles(): array
    {
        return DB::table('bodega')->where('estado_id', 1)
            ->whereRaw('UPPER(TRIM(nombre)) <> ?', ['SIN EXISTENCIA COTIZACION'])
            ->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    private function idsEscalasDisponibles(): array
    {
        return DB::table('categoria_precios as cp')
            ->join('cliente_categoria_escala as cce', 'cce.id', '=', 'cp.cliente_categoria_escala_id')
            ->where('cp.estado_id', 1)
            ->where('cce.estado_id', 1)
            ->pluck('cp.id')->map(fn ($id) => (string) $id)->all();
    }

    private function sincronizarExposVencidas(): void
    {
        DB::table('expo')
            ->where('estado', 'Activo')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<=', now())
            ->update([
                'estado' => 'Inactivo',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    private function estaFinalizada(object $expo): bool
    {
        return $expo->fecha_fin && Carbon::parse($expo->fecha_fin)->lte(now());
    }
}
