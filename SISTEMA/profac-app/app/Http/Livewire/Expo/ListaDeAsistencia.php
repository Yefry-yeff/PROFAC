<?php

namespace App\Http\Livewire\Expo;

use App\Exports\ArrayExport;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ListaDeAsistencia extends Component
{
    public $titulo = 'Lista de Asistencia';
    public $expoId = '';
    public $busquedaCliente = '';
    public $busquedaAsistente = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $clienteDescuentoId = null;

    public function mount(): void
    {
        $expoId = $this->exposActivas()->pluck('id')->first();
        $this->expoId = $expoId ? (string) $expoId : '';
    }

    public function render()
    {
        $expos = $this->exposActivas();
        $expo = $expos->firstWhere('id', (int) $this->expoId);
        $asistentes = collect();
        $clientesEncontrados = collect();
        $categoriasDescuento = collect();

        if ($expo) {
            $categoriasDescuento = $this->categoriasDescuento((int) $expo->id);
            $asistentes = $this->cargarAsistentes((int) $expo->id);
            $busqueda = trim($this->busquedaCliente);
            if (mb_strlen($busqueda) >= 2) {
                $clientesEncontrados = DB::table('cliente as c')
                    ->where('c.estado_cliente_id', 1)
                    ->where('c.id', '<>', 1)
                    ->whereNotExists(function ($query) use ($expo) {
                        $query->selectRaw('1')->from('expo_asistencia as ea')
                            ->whereColumn('ea.cliente_id', 'c.id')
                            ->where('ea.expo_id', $expo->id);
                    })
                    ->where(function ($query) use ($busqueda) {
                        $query->where('c.nombre', 'like', "%{$busqueda}%")
                            ->orWhere('c.rtn', 'like', "%{$busqueda}%")
                            ->orWhere('c.id', 'like', "%{$busqueda}%");
                    })
                    ->orderBy('c.nombre')->limit(15)
                    ->get(['c.id', 'c.nombre', 'c.rtn', 'c.telefono_empresa']);
            }
        }

        $clienteDescuento = $asistentes->firstWhere('id', (int) $this->clienteDescuentoId);

        return view('livewire.expo.listadeasistencia', compact(
            'expos', 'expo', 'asistentes', 'clientesEncontrados', 'categoriasDescuento',
            'clienteDescuento'
        ));
    }

    public function updatedExpoId(): void
    {
        $this->busquedaCliente = '';
        $this->clienteDescuentoId = null;
        $this->limpiarFiltros();
        $this->resetValidation();
    }

    public function limpiarFiltros(): void
    {
        $this->busquedaAsistente = '';
        $this->fechaDesde = '';
        $this->fechaHasta = '';
    }

    public function agregarCliente(int $clienteId): void
    {
        $expo = $this->expoActivaSeleccionada();
        $clienteValido = DB::table('cliente')->where('id', $clienteId)
            ->where('estado_cliente_id', 1)->where('id', '<>', 1)->exists();
        if (!$clienteValido) {
            $this->addError('busquedaCliente', 'El cliente no está disponible.');
            return;
        }

        DB::table('expo_asistencia')->insertOrIgnore([
            'expo_id' => $expo->id,
            'cliente_id' => $clienteId,
            'registrado_por' => Auth::id(),
            'tickets' => 0,
            'recibio_regalo' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->busquedaCliente = '';
        session()->flash('success', 'Cliente agregado a la lista de asistencia.');
    }

    public function eliminarCliente(int $clienteId): void
    {
        $expo = $this->expoActivaSeleccionada();
        DB::table('expo_asistencia')->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)->delete();
        if ((int) $this->clienteDescuentoId === $clienteId) {
            $this->clienteDescuentoId = null;
        }
        session()->flash('success', 'Cliente eliminado de la lista de asistencia.');
    }

    public function actualizarTickets(int $clienteId, $tickets): void
    {
        if (filter_var($tickets, FILTER_VALIDATE_INT) === false || (int) $tickets < 0) {
            session()->flash('error', 'La cantidad de tickets debe ser un número entero igual o mayor que cero.');
            return;
        }

        $this->actualizarAsistencia($clienteId, ['tickets' => (int) $tickets]);
    }

    public function actualizarRegalo(int $clienteId): void
    {
        $expo = $this->expoActivaSeleccionada();
        $asistencia = DB::table('expo_asistencia')
            ->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)
            ->first(['recibio_regalo']);
        abort_unless($asistencia, 404, 'El cliente no está registrado en esta Expo.');

        $this->actualizarAsistencia($clienteId, [
            'recibio_regalo' => !(bool) $asistencia->recibio_regalo,
        ]);
    }

    public function actualizarComentario(int $clienteId, $comentario): void
    {
        $comentario = trim((string) $comentario);
        if (mb_strlen($comentario) > 1000) {
            session()->flash('error', 'El comentario no puede superar los 1,000 caracteres.');
            return;
        }

        $this->actualizarAsistencia($clienteId, [
            'comentario' => $comentario !== '' ? $comentario : null,
        ]);
    }

    public function abrirDescuentos(int $clienteId): void
    {
        $expo = $this->expoActivaSeleccionada();
        abort_unless(DB::table('expo_asistencia')->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)->exists(), 404, 'El cliente no está registrado en esta Expo.');
        $this->clienteDescuentoId = $clienteId;
    }

    public function cerrarDescuentos(): void
    {
        $this->clienteDescuentoId = null;
    }

    public function actualizarMaximoGeneral(int $clienteId, bool $activo): void
    {
        $expo = $this->expoActivaSeleccionada();
        $asistencia = DB::table('expo_asistencia')
            ->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)
            ->first(['id']);
        abort_unless($asistencia, 404, 'El cliente no está registrado en esta Expo.');

        $escalaIds = DB::table('expo_descuento_escala')
            ->where('expo_id', $expo->id)
            ->distinct()
            ->pluck('escala_id');
        abort_if($escalaIds->isEmpty(), 422, 'La Expo no tiene descuentos por categoría configurados.');

        DB::transaction(function () use ($asistencia, $escalaIds, $activo) {
            DB::table('expo_asistencia')->where('id', $asistencia->id)->update([
                'descuento_modo' => 'automatico',
                'descuento_escalon' => null,
                'descuento_asignado_por' => null,
                'descuento_asignado_at' => null,
                'updated_at' => now(),
            ]);

            DB::table('expo_asistencia_descuento_escala')
                ->where('expo_asistencia_id', $asistencia->id)
                ->delete();

            if ($activo) {
                DB::table('expo_asistencia_descuento_escala')->insert(
                    $escalaIds->map(fn ($escalaId) => [
                        'expo_asistencia_id' => $asistencia->id,
                        'escala_id' => (int) $escalaId,
                        'descuento_modo' => 'maximo',
                        'descuento_escalon' => null,
                        'asignado_por' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all()
                );
            }
        });

        session()->flash('success', $activo
            ? 'Se asignó el descuento máximo en todas las categorías de precio.'
            : 'Todas las categorías volvieron al cálculo automático.');
    }

    public function actualizarDescuentoEscala(int $clienteId, int $escalaId, string $seleccion): void
    {
        $expo = $this->expoActivaSeleccionada();
        $asistencia = DB::table('expo_asistencia')
            ->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)
            ->first(['id']);
        abort_unless($asistencia, 404, 'El cliente no está registrado en esta Expo.');

        $reglas = DB::table('expo_descuento_escala')
            ->where('expo_id', $expo->id)
            ->where('escala_id', $escalaId)
            ->orderBy('venta_minima')
            ->orderBy('orden')
            ->get(['venta_minima', 'porcentaje_descuento']);
        abort_unless($reglas->isNotEmpty(), 404, 'La categoría de precio no está configurada en esta Expo.');

        $modo = 'automatico';
        $escalon = null;
        if ($seleccion === 'maximo') {
            $modo = 'maximo';
        } elseif (preg_match('/^escalon:(\d+)$/', $seleccion, $coincidencia)) {
            $modo = 'escalon';
            $escalon = (int) $coincidencia[1];
            if ($escalon < 1 || $escalon > $reglas->count()) {
                session()->flash('error', 'El escalón seleccionado no está disponible para esta categoría.');
                return;
            }
        } elseif ($seleccion !== 'automatico') {
            session()->flash('error', 'La opción de descuento seleccionada no es válida.');
            return;
        }

        DB::table('expo_asistencia')->where('id', $asistencia->id)->update([
            'descuento_modo' => 'automatico',
            'descuento_escalon' => null,
            'descuento_asignado_por' => null,
            'descuento_asignado_at' => null,
            'updated_at' => now(),
        ]);

        if ($modo === 'automatico') {
            DB::table('expo_asistencia_descuento_escala')
                ->where('expo_asistencia_id', $asistencia->id)
                ->where('escala_id', $escalaId)
                ->delete();
        } else {
            DB::table('expo_asistencia_descuento_escala')->updateOrInsert([
                'expo_asistencia_id' => $asistencia->id,
                'escala_id' => $escalaId,
            ], [
                'descuento_modo' => $modo,
                'descuento_escalon' => $escalon,
                'asignado_por' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $categoria = DB::table('categoria_precios')->where('id', $escalaId)->value('nombre');
        session()->flash('success', 'Descuento de ' . ($categoria ?: 'la categoría') . ' actualizado.');
    }

    public function descargarExcel()
    {
        $expo = $this->expoActivaSeleccionada();
        $filas = $this->cargarAsistentes((int) $expo->id)->map(fn ($cliente) => [
            $expo->nombre,
            $expo->fecha_inicio,
            $expo->fecha_fin ?: 'Sin fecha de cierre',
            $cliente->id,
            $cliente->nombre,
            $cliente->rtn,
            $cliente->telefono_empresa,
            $cliente->correo,
            $cliente->registrado_at,
            $cliente->tickets,
            $cliente->recibio_regalo ? 'Sí' : 'No',
            $cliente->comentario,
            $cliente->descuento_resumen,
            $cliente->registrado_por,
        ])->all();

        return Excel::download(new ArrayExport([
            'Exposición', 'Inicio', 'Fin', 'ID cliente', 'Cliente', 'RTN', 'Teléfono',
            'Correo', 'Fecha de asistencia', 'Tickets', 'Recibió regalo', 'Comentario',
            'Descuento Expo', 'Registrado por',
        ], $filas), 'asistencia_expo_' . $expo->id . '.xlsx');
    }

    public function descargarPdf()
    {
        $expo = $this->expoActivaSeleccionada();
        $asistentes = $this->cargarAsistentes((int) $expo->id);
        return PDF::loadView('pdf.expo-asistencia', compact('expo', 'asistentes'))
            ->setPaper('letter', 'landscape')
            ->download('asistencia_expo_' . $expo->id . '.pdf');
    }

    private function exposActivas()
    {
        return DB::table('expo')->where('estado', 'Activo')
            ->where('fecha_inicio', '<=', now())
            ->where(fn ($query) => $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()))
            ->orderByDesc('fecha_inicio')->get();
    }

    private function expoActivaSeleccionada(): object
    {
        $expo = $this->exposActivas()->firstWhere('id', (int) $this->expoId);
        abort_unless($expo, 404, 'La exposición no está activa.');
        return $expo;
    }

    private function actualizarAsistencia(int $clienteId, array $valores): void
    {
        $expo = $this->expoActivaSeleccionada();
        $actualizados = DB::table('expo_asistencia')
            ->where('expo_id', $expo->id)
            ->where('cliente_id', $clienteId)
            ->update(array_merge($valores, ['updated_at' => now()]));
        abort_unless($actualizados, 404, 'El cliente no está registrado en esta Expo.');
    }

    private function consultaAsistentes(int $expoId)
    {
        $busqueda = trim($this->busquedaAsistente);
        return DB::table('expo_asistencia as ea')
            ->join('cliente as c', 'c.id', '=', 'ea.cliente_id')
            ->join('users as u', 'u.id', '=', 'ea.registrado_por')
            ->where('ea.expo_id', $expoId)
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($filtro) use ($busqueda) {
                    $filtro->where('c.nombre', 'like', "%{$busqueda}%")
                        ->orWhere('c.rtn', 'like', "%{$busqueda}%")
                        ->orWhere('c.telefono_empresa', 'like', "%{$busqueda}%")
                        ->orWhere('c.correo', 'like', "%{$busqueda}%")
                        ->orWhere('c.id', 'like', "%{$busqueda}%");
                });
            })
            ->when($this->fechaDesde, fn ($query) => $query->whereDate('ea.created_at', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($query) => $query->whereDate('ea.created_at', '<=', $this->fechaHasta))
            ->orderBy('c.nombre')
            ->select('c.id', 'c.nombre', 'c.rtn', 'c.telefono_empresa', 'c.correo',
                'ea.id as asistencia_id', 'ea.created_at as registrado_at', 'ea.tickets', 'ea.recibio_regalo',
                'ea.comentario',
                'u.name as registrado_por');
    }

    private function cargarAsistentes(int $expoId)
    {
        $asistentes = $this->consultaAsistentes($expoId)->get();
        $descuentos = DB::table('expo_asistencia_descuento_escala as eade')
            ->join('categoria_precios as cp', 'cp.id', '=', 'eade.escala_id')
            ->whereIn('eade.expo_asistencia_id', $asistentes->pluck('asistencia_id'))
            ->get(['eade.expo_asistencia_id', 'eade.escala_id', 'cp.nombre as escala',
                'eade.descuento_modo', 'eade.descuento_escalon'])
            ->groupBy('expo_asistencia_id');

        return $asistentes->map(function ($cliente) use ($descuentos) {
            $cliente->descuentos_escala = collect($descuentos->get($cliente->asistencia_id, []))
                ->mapWithKeys(fn ($descuento) => [(int) $descuento->escala_id => [
                    'descuento_modo' => $descuento->descuento_modo,
                    'descuento_escalon' => $descuento->descuento_escalon !== null
                        ? (int) $descuento->descuento_escalon
                        : null,
                    'escala' => $descuento->escala,
                ]])->all();
            $cliente->descuento_resumen = empty($cliente->descuentos_escala)
                ? 'Automático en todas las categorías'
                : collect($cliente->descuentos_escala)->map(function ($descuento) {
                    $seleccion = $descuento['descuento_modo'] === 'maximo'
                        ? 'Máximo'
                        : 'Escalón ' . $descuento['descuento_escalon'];
                    return $descuento['escala'] . ': ' . $seleccion;
                })->implode(' | ');

            return $cliente;
        });
    }

    private function categoriasDescuento(int $expoId)
    {
        return DB::table('expo_descuento_escala as ede')
            ->join('categoria_precios as cp', 'cp.id', '=', 'ede.escala_id')
            ->where('ede.expo_id', $expoId)
            ->orderBy('cp.nombre')
            ->orderBy('ede.venta_minima')
            ->orderBy('ede.orden')
            ->get(['ede.escala_id', 'cp.nombre as escala', 'ede.venta_minima',
                'ede.porcentaje_descuento', 'ede.orden'])
            ->groupBy('escala_id')
            ->map(function ($reglas) {
                return (object) [
                    'id' => (int) $reglas->first()->escala_id,
                    'nombre' => $reglas->first()->escala,
                    'escalones' => $reglas->values()->map(fn ($regla, $indice) => (object) [
                        'numero' => $indice + 1,
                        'venta_minima' => (float) $regla->venta_minima,
                        'porcentaje' => (float) $regla->porcentaje_descuento,
                    ]),
                ];
            })->values();
    }
}
