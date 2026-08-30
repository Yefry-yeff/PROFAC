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

        if ($expo) {
            $asistentes = $this->consultaAsistentes((int) $expo->id)->get();
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

        return view('livewire.expo.listadeasistencia', compact(
            'expos', 'expo', 'asistentes', 'clientesEncontrados'
        ));
    }

    public function updatedExpoId(): void
    {
        $this->busquedaCliente = '';
        $this->resetValidation();
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

    public function descargarExcel()
    {
        $expo = $this->expoActivaSeleccionada();
        $filas = $this->consultaAsistentes((int) $expo->id)->get()->map(fn ($cliente) => [
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
            $cliente->registrado_por,
        ])->all();

        return Excel::download(new ArrayExport([
            'Exposición', 'Inicio', 'Fin', 'ID cliente', 'Cliente', 'RTN', 'Teléfono',
            'Correo', 'Fecha de asistencia', 'Tickets', 'Recibió regalo', 'Registrado por',
        ], $filas), 'asistencia_expo_' . $expo->id . '.xlsx');
    }

    public function descargarPdf()
    {
        $expo = $this->expoActivaSeleccionada();
        $asistentes = $this->consultaAsistentes((int) $expo->id)->get();
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
        return DB::table('expo_asistencia as ea')
            ->join('cliente as c', 'c.id', '=', 'ea.cliente_id')
            ->join('users as u', 'u.id', '=', 'ea.registrado_por')
            ->where('ea.expo_id', $expoId)->orderBy('c.nombre')
            ->select('c.id', 'c.nombre', 'c.rtn', 'c.telefono_empresa', 'c.correo',
                'ea.created_at as registrado_at', 'ea.tickets', 'ea.recibio_regalo',
                'u.name as registrado_por');
    }
}
