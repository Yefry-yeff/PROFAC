<?php

namespace App\Http\Livewire\FlujoDeVenta;

use App\Services\Expo\GestorAumentoExpo;
use App\Services\Expo\LiquidacionOfertaExpo;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
    public $descuentosMarca = [];
    public $mostrarModalDescuentoMarca = false;
    public $marcaDescuentoSeleccionada = '';
    public $escalonesMarcaModal = [];
    public $busquedaDescuentoMarca = '';
    public $ordenDescuentoMarca = 'marca';
    public $direccionDescuentoMarca = 'asc';
    public $mostrarFormulario = false;
    public $expoDetalle = [];
    public $mostrarDetalle = false;
    public $motivoCierre = '';
    public $motivoReapertura = '';

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
        $this->descuentosMarca = $this->cargarDescuentosMarca($id);
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
        $this->descuentosMarca = $this->cargarDescuentosMarca($id);
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

    public function abrirModalDescuentoMarca(): void
    {
        $this->marcaDescuentoSeleccionada = '';
        $this->escalonesMarcaModal = [['venta_minima' => '', 'porcentaje_descuento' => '']];
        $this->mostrarModalDescuentoMarca = true;
        $this->resetValidation(['marcaDescuentoSeleccionada', 'escalonesMarcaModal']);
    }

    public function cerrarModalDescuentoMarca(): void
    {
        $this->mostrarModalDescuentoMarca = false;
        $this->marcaDescuentoSeleccionada = '';
        $this->escalonesMarcaModal = [];
        $this->resetValidation(['marcaDescuentoSeleccionada', 'escalonesMarcaModal']);
    }

    public function agregarEscalonMarcaModal(): void
    {
        $this->escalonesMarcaModal[] = ['venta_minima' => '', 'porcentaje_descuento' => ''];
    }

    public function eliminarEscalonMarcaModal(int $indice): void
    {
        if (count($this->escalonesMarcaModal) <= 1) {
            return;
        }

        unset($this->escalonesMarcaModal[$indice]);
        $this->escalonesMarcaModal = array_values($this->escalonesMarcaModal);
    }

    public function guardarDescuentoMarcaModal(): void
    {
        foreach ($this->escalonesMarcaModal as &$escalon) {
            $escalon['venta_minima'] = str_replace(',', '', (string) ($escalon['venta_minima'] ?? ''));
        }
        unset($escalon);

        $this->validate([
            'marcaDescuentoSeleccionada' => 'required|integer|exists:marca,id',
            'escalonesMarcaModal' => 'required|array|min:1',
            'escalonesMarcaModal.*.venta_minima' => 'required|numeric|min:0',
            'escalonesMarcaModal.*.porcentaje_descuento' => 'required|numeric|min:0|max:100',
        ], [
            'marcaDescuentoSeleccionada.required' => 'Seleccione una marca.',
            'escalonesMarcaModal.*.venta_minima.required' => 'Ingrese la venta mínima.',
            'escalonesMarcaModal.*.porcentaje_descuento.required' => 'Ingrese el porcentaje.',
        ]);

        $marcaId = (int) $this->marcaDescuentoSeleccionada;
        $minimosExistentes = collect($this->descuentosMarca)
            ->where('marca_id', $marcaId)
            ->pluck('venta_minima')
            ->map(fn ($valor) => (string) (float) str_replace(',', '', (string) $valor));
        $minimosNuevos = collect($this->escalonesMarcaModal)
            ->pluck('venta_minima')
            ->map(fn ($valor) => (string) (float) $valor);

        if ($minimosNuevos->duplicates()->isNotEmpty() || $minimosNuevos->intersect($minimosExistentes)->isNotEmpty()) {
            $this->addError('escalonesMarcaModal', 'La marca no puede repetir el mismo monto mínimo en dos escalones.');
            return;
        }

        foreach ($this->escalonesMarcaModal as $escalon) {
            $this->descuentosMarca[] = [
                'marca_id' => (string) $marcaId,
                'venta_minima' => (string) $escalon['venta_minima'],
                'porcentaje_descuento' => (string) $escalon['porcentaje_descuento'],
            ];
        }

        $this->cerrarModalDescuentoMarca();
    }

    public function eliminarDescuentoMarca(int $indice): void
    {
        if (isset($this->descuentosMarca[$indice])) {
            unset($this->descuentosMarca[$indice]);
            $this->descuentosMarca = array_values($this->descuentosMarca);
        }
    }

    public function ordenarDescuentosMarca(string $columna): void
    {
        if (!in_array($columna, ['marca', 'venta_minima', 'porcentaje_descuento'], true)) {
            return;
        }

        if ($this->ordenDescuentoMarca === $columna) {
            $this->direccionDescuentoMarca = $this->direccionDescuentoMarca === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->ordenDescuentoMarca = $columna;
        $this->direccionDescuentoMarca = 'asc';
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
        foreach ($this->descuentosMarca as &$regla) {
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
            'descuentosMarca' => 'array',
            'descuentosMarca.*.marca_id' => 'required|integer|exists:marca,id',
            'descuentosMarca.*.venta_minima' => 'required|numeric|min:0',
            'descuentosMarca.*.porcentaje_descuento' => 'required|numeric|min:0|max:100',
        ], [
            'bodegasSeleccionadas.required' => 'Seleccione al menos una bodega.',
            'escalasSeleccionadas.required' => 'Seleccione al menos una escala.',
            'usuariosSeleccionados.required' => 'Agregue al menos un usuario autorizado.',
            'fechaFin.after' => 'La fecha final debe ser posterior a la fecha de inicio.',
        ]);

        $escalonesMarca = collect($this->descuentosMarca)
            ->groupBy(fn ($regla) => (int) ($regla['marca_id'] ?? 0));
        foreach ($escalonesMarca as $reglas) {
            $minimos = $reglas->pluck('venta_minima')->map(fn ($valor) => (string) (float) $valor);
            if ($minimos->duplicates()->isNotEmpty()) {
                $this->addError('descuentosMarca', 'Una marca no puede repetir el mismo monto mínimo en dos escalones.');
                return;
            }
        }

        try {
            DB::beginTransaction();

            $this->sincronizarExposVencidas();

            $expoExistente = $this->expoEditandoId
                ? DB::table('expo')->where('id', $this->expoEditandoId)->lockForUpdate()->first()
                : null;
            $snapshotAnterior = $expoExistente ? $this->snapshotExpo((int) $expoExistente->id) : null;

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
                DB::table('expo_descuento_marca')->where('expo_id', $expoId)->delete();
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

            foreach (array_values($this->descuentosMarca) as $orden => $regla) {
                DB::table('expo_descuento_marca')->insert([
                    'expo_id' => $expoId,
                    'marca_id' => (int) $regla['marca_id'],
                    'venta_minima' => $regla['venta_minima'],
                    'porcentaje_descuento' => $regla['porcentaje_descuento'],
                    'orden' => $orden + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $snapshotNuevo = $this->snapshotExpo($expoId);
            $this->registrarHistorial(
                $expoId,
                $expoExistente ? 'ACTUALIZACION' : 'CREACION',
                $expoExistente ? $this->resumirCambios($snapshotAnterior, $snapshotNuevo) : 'Se creó la configuración de la Expo.',
                $snapshotAnterior,
                $snapshotNuevo,
                (int) Auth::id()
            );

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

        $columnasFlujo = ['ec.id', 'ec.cotizacion_id'];
        foreach (['flujo_id', 'estado', 'aumento_aplicado', 'reapertura_autorizada'] as $columna) {
            if (Schema::hasColumn('expo_cotizacion', $columna)) {
                $columnasFlujo[] = 'ec.' . $columna;
            }
        }

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
            'descuentos_marca' => DB::table('expo_descuento_marca as edm')
                ->join('marca as m', 'm.id', '=', 'edm.marca_id')
                ->where('edm.expo_id', $id)
                ->orderBy('edm.orden')
                ->get(['m.nombre as marca', 'edm.venta_minima', 'edm.porcentaje_descuento'])
                ->map(fn ($regla) => (array) $regla)->all(),
            'historial_cambios' => DB::table('expo_historial_cambios as ehc')
                ->join('users as u', 'u.id', '=', 'ehc.user_id')
                ->where('ehc.expo_id', $id)
                ->orderByDesc('ehc.created_at')
                ->orderByDesc('ehc.id')
                ->get(['ehc.accion', 'ehc.detalle', 'ehc.created_at', 'u.name as usuario'])
                ->map(fn ($cambio) => (array) $cambio)->all(),
            'flujos' => DB::table('expo_cotizacion as ec')
                ->where('ec.expo_id', $id)
                ->orderByDesc('ec.id')
                ->get($columnasFlujo)
                ->map(fn ($flujo) => array_merge([
                    'flujo_id' => null,
                    'estado' => 'PENDIENTE_FACTURACION',
                    'aumento_aplicado' => 0,
                    'reapertura_autorizada' => false,
                ], (array) $flujo))->all(),
        ];
        $this->mostrarDetalle = true;
    }

    public function cerrarDetalle(): void
    {
        $this->mostrarDetalle = false;
        $this->expoDetalle = [];
    }

    public function cerrarExpo(int $expoId): void
    {
        $this->validate(['motivoCierre' => 'required|string|min:5|max:500']);

        try {
            DB::transaction(fn () => $this->cerrarExpoInternamente(
                $expoId,
                trim($this->motivoCierre),
                (int) Auth::id()
            ), 3);

            $this->motivoCierre = '';
            $this->cerrarDetalle();
            session()->flash('success', 'La Expo fue cerrada y sus flujos incompletos fueron liquidados.');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo cerrar la Expo: ' . $e->getMessage());
        }
    }

    public function reabrirExpo(int $expoId): void
    {
        $this->validate(['motivoReapertura' => 'required|string|min:5|max:500']);

        try {
            DB::transaction(function () use ($expoId) {
                $expo = DB::table('expo')->where('id', $expoId)->lockForUpdate()->first();
                abort_unless($expo, 404);
                $snapshotAnterior = $this->snapshotExpo($expoId);
                if (DB::table('expo')->where('estado', 'Activo')->where('id', '<>', $expoId)->exists()) {
                    throw new \RuntimeException('Existe otra Expo activa. Ciérrela antes de reabrir esta Expo.');
                }

                foreach (DB::table('expo_cotizacion')->where('expo_id', $expoId)->lockForUpdate()->get() as $oferta) {
                    $this->reabrirOferta($oferta, trim($this->motivoReapertura));
                }

                DB::table('expo')->where('id', $expoId)->update([
                    'estado' => 'Activo',
                    'cerrada_por' => null,
                    'cerrada_at' => null,
                    'motivo_cierre' => null,
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);
                $snapshotNuevo = $this->snapshotExpo($expoId);
                $this->registrarHistorial(
                    $expoId,
                    'REAPERTURA',
                    'Se reabrió la Expo completa y se revirtieron sus aumentos mediante disminuciones.',
                    $snapshotAnterior,
                    $snapshotNuevo,
                    (int) Auth::id()
                );
            }, 3);

            $this->motivoReapertura = '';
            $this->cerrarDetalle();
            session()->flash('success', 'La Expo fue reabierta y sus aumentos fueron revertidos mediante disminuciones.');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo reabrir la Expo: ' . $e->getMessage());
        }
    }

    public function reabrirFlujo(int $expoCotizacionId): void
    {
        $this->validate(['motivoReapertura' => 'required|string|min:5|max:500']);
        $expoId = (int) ($this->expoDetalle['expo']['id'] ?? 0);

        try {
            DB::transaction(function () use ($expoCotizacionId) {
                $oferta = DB::table('expo_cotizacion')->where('id', $expoCotizacionId)->lockForUpdate()->first();
                abort_unless($oferta, 404);
                $snapshotAnterior = $this->snapshotExpo((int) $oferta->expo_id);
                $this->reabrirOferta($oferta, trim($this->motivoReapertura));
                $this->registrarHistorial(
                    (int) $oferta->expo_id,
                    'REAPERTURA_FLUJO',
                    "Se reabrió únicamente el flujo #{$oferta->flujo_id} de la oferta #{$oferta->cotizacion_id}.",
                    $snapshotAnterior,
                    $this->snapshotExpo((int) $oferta->expo_id),
                    (int) Auth::id()
                );
            }, 3);

            $this->motivoReapertura = '';
            $this->verDetalle($expoId);
            session()->flash('success', 'El flujo fue reabierto y su aumento fue revertido mediante una disminución.');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo reabrir el flujo: ' . $e->getMessage());
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'expoEditandoId', 'expoDuplicandoId', 'nombre', 'descripcion', 'fechaInicio', 'fechaFin',
            'bodegasSeleccionadas', 'escalasSeleccionadas', 'usuariosSeleccionados',
            'busquedaUsuario', 'descuentos', 'descuentosMarca', 'mostrarFormulario',
            'mostrarModalDescuentoMarca', 'marcaDescuentoSeleccionada', 'escalonesMarcaModal',
            'busquedaDescuentoMarca', 'ordenDescuentoMarca', 'direccionDescuentoMarca',
        ]);
        $this->estado = 'Inactivo';
        $this->ordenDescuentoMarca = 'marca';
        $this->direccionDescuentoMarca = 'asc';
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
        $marcas = DB::table('marca')->orderBy('nombre')->get(['id', 'nombre']);
        $nombresMarca = $marcas->pluck('nombre', 'id');
        $busquedaMarca = mb_strtolower(trim($this->busquedaDescuentoMarca));
        $descuentosMarcaTabla = collect($this->descuentosMarca)
            ->map(function ($regla, $indice) use ($nombresMarca) {
                return [
                    'indice' => (int) $indice,
                    'marca_id' => (int) ($regla['marca_id'] ?? 0),
                    'marca' => (string) ($nombresMarca[(int) ($regla['marca_id'] ?? 0)] ?? ('Marca #' . ($regla['marca_id'] ?? ''))),
                    'venta_minima' => (float) str_replace(',', '', (string) ($regla['venta_minima'] ?? 0)),
                    'porcentaje_descuento' => (float) ($regla['porcentaje_descuento'] ?? 0),
                ];
            })
            ->when($busquedaMarca !== '', fn ($reglas) => $reglas->filter(
                fn ($regla) => str_contains(mb_strtolower($regla['marca']), $busquedaMarca)
            ));
        $descuentosMarcaTabla = $this->direccionDescuentoMarca === 'desc'
            ? $descuentosMarcaTabla->sortByDesc($this->ordenDescuentoMarca)
            : $descuentosMarcaTabla->sortBy($this->ordenDescuentoMarca);

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
            'expos', 'bodegas', 'escalas', 'marcas', 'descuentosMarcaTabla',
            'usuariosAgregados', 'usuariosEncontrados'
        ));
    }

    private function cargarDescuentosMarca(int $expoId): array
    {
        return DB::table('expo_descuento_marca')
            ->where('expo_id', $expoId)
            ->orderBy('orden')
            ->get(['marca_id', 'venta_minima', 'porcentaje_descuento'])
            ->map(fn ($regla) => [
                'marca_id' => (string) $regla->marca_id,
                'venta_minima' => (string) $regla->venta_minima,
                'porcentaje_descuento' => (string) $regla->porcentaje_descuento,
            ])->all();
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
        $vencidas = DB::table('expo')
            ->where('estado', 'Activo')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<=', now())
            ->get(['id', 'updated_by']);

        foreach ($vencidas as $expo) {
            $usuarioId = (int) (Auth::id() ?: $expo->updated_by);
            DB::transaction(fn () => $this->cerrarExpoInternamente(
                (int) $expo->id,
                'Cierre automático por vencimiento del plazo de facturación.',
                $usuarioId
            ), 3);
        }
    }

    private function estaFinalizada(object $expo): bool
    {
        return $expo->fecha_fin && Carbon::parse($expo->fecha_fin)->lte(now());
    }

    private function resolverFlujoId(int $cotizacionId, ?int $flujoId): ?int
    {
        if ($flujoId) {
            return (int) $flujoId;
        }

        $id = DB::table('historico_flujo')
            ->where('tramite_id', $cotizacionId)
            ->where('tipo_tramite_id', 2)
            ->value('flujo_id');

        return $id ? (int) $id : null;
    }

    private function reabrirOferta(object $oferta, string $motivo): void
    {
        app(GestorAumentoExpo::class)->revertir((int) $oferta->id, (int) Auth::id());
        $flujoId = $this->resolverFlujoId((int) $oferta->cotizacion_id, $oferta->flujo_id);
        $tieneFacturas = $flujoId && DB::table('historico_flujo as hf')
            ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
            ->where('hf.flujo_id', $flujoId)
            ->whereIn('hf.tipo_tramite_id', [3, 5])
            ->where('hf.estado_id', '<>', 7)
            ->where('f.estado_venta_id', 1)
            ->exists();

        DB::table('expo_cotizacion')->where('id', $oferta->id)->update([
            'estado' => $tieneFacturas ? 'FACTURACION_PARCIAL' : 'PENDIENTE_FACTURACION',
            'reapertura_autorizada' => true,
            'motivo_reapertura' => $motivo,
            'reabierto_por' => Auth::id(),
            'reabierto_at' => now(),
            'aumento_aplicado' => 0,
            'liquidado_por' => null,
            'liquidado_at' => null,
        ]);
    }

    private function cerrarExpoInternamente(int $expoId, string $motivo, int $usuarioId): void
    {
        $expo = DB::table('expo')->where('id', $expoId)->lockForUpdate()->first();
        abort_unless($expo, 404);
        if ($expo->estado !== 'Activo') {
            return;
        }
        $snapshotAnterior = $this->snapshotExpo($expoId);

        DB::table('expo')->where('id', $expoId)->update([
            'estado' => 'Cerrada',
            'cerrada_por' => $usuarioId,
            'cerrada_at' => now(),
            'motivo_cierre' => $motivo,
            'updated_by' => $usuarioId,
            'updated_at' => now(),
        ]);

        $ofertas = DB::table('expo_cotizacion')
            ->where('expo_id', $expoId)
            ->whereIn('estado', ['PENDIENTE_FACTURACION', 'FACTURACION_PARCIAL'])
            ->lockForUpdate()
            ->get();
        foreach ($ofertas as $oferta) {
            $flujoId = $this->resolverFlujoId((int) $oferta->cotizacion_id, $oferta->flujo_id);
            if ($flujoId) {
                app(LiquidacionOfertaExpo::class)->procesar(
                    (int) $oferta->cotizacion_id,
                    $flujoId,
                    null,
                    true,
                    $motivo,
                    $usuarioId
                );
            }
        }

        $this->registrarHistorial(
            $expoId,
            str_contains($motivo, 'vencimiento') ? 'CIERRE_AUTOMATICO' : 'CIERRE',
            $motivo,
            $snapshotAnterior,
            $this->snapshotExpo($expoId),
            $usuarioId
        );
    }

    private function snapshotExpo(int $expoId): array
    {
        $expo = DB::table('expo')->where('id', $expoId)->first();

        return [
            'nombre' => $expo->nombre,
            'descripcion' => $expo->descripcion,
            'estado' => $expo->estado,
            'fecha_inicio' => $expo->fecha_inicio,
            'fecha_fin' => $expo->fecha_fin,
            'bodegas' => DB::table('expo_bodega')->where('expo_id', $expoId)->orderBy('bodega_id')->pluck('bodega_id')->all(),
            'escalas' => DB::table('expo_escala')->where('expo_id', $expoId)->orderBy('escala_id')->pluck('escala_id')->all(),
            'usuarios' => DB::table('expo_usuario')->where('expo_id', $expoId)->orderBy('usuario_id')->pluck('usuario_id')->all(),
            'descuentos_totales' => DB::table('expo_descuento')->where('expo_id', $expoId)->orderBy('orden')
                ->get(['venta_minima', 'porcentaje_descuento'])->map(fn ($regla) => (array) $regla)->all(),
            'descuentos_marcas' => DB::table('expo_descuento_marca')->where('expo_id', $expoId)->orderBy('orden')
                ->get(['marca_id', 'venta_minima', 'porcentaje_descuento'])->map(fn ($regla) => (array) $regla)->all(),
        ];
    }

    private function resumirCambios(?array $anterior, array $nuevo): string
    {
        if (!$anterior) {
            return 'Se creó la configuración de la Expo.';
        }

        $etiquetas = [
            'descripcion' => 'descripción',
            'estado' => 'estado',
            'fecha_fin' => 'fecha final',
            'bodegas' => 'bodegas',
            'escalas' => 'escalas',
            'usuarios' => 'usuarios autorizados',
            'descuentos_totales' => 'descuentos por total',
            'descuentos_marcas' => 'descuentos por marca',
        ];
        $cambios = [];
        foreach ($etiquetas as $campo => $etiqueta) {
            if (($anterior[$campo] ?? null) != ($nuevo[$campo] ?? null)) {
                $cambios[] = $etiqueta;
            }
        }

        return $cambios
            ? 'Se modificó: ' . implode(', ', $cambios) . '.'
            : 'Se guardó la configuración sin cambios funcionales.';
    }

    private function registrarHistorial(
        int $expoId,
        string $accion,
        string $detalle,
        ?array $anterior,
        ?array $nuevo,
        int $usuarioId
    ): void {
        DB::table('expo_historial_cambios')->insert([
            'expo_id' => $expoId,
            'accion' => $accion,
            'detalle' => $detalle,
            'datos_anteriores' => $anterior ? json_encode($anterior) : null,
            'datos_nuevos' => $nuevo ? json_encode($nuevo) : null,
            'user_id' => $usuarioId,
            'created_at' => now(),
        ]);
    }
}
