<?php

namespace App\Http\Livewire\Expo;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReporteDeExpo extends Component
{
    public $titulo = 'Reporte de Expo';
    public $expoId;
    public $expo = [];
    public $flujos = [];
    public $filtro = '';
    public $busquedaExpo = '';

    public function mount(): void
    {
        $this->expoId = request()->integer('expo_id');
        if ($this->expoId) {
            $this->seleccionarExpo($this->expoId);
        }
    }

    public function seleccionarExpo(int $expoId): void
    {
        $this->cargarReporte($expoId);
        $this->filtro = '';
    }

    public function cambiarExpo(): void
    {
        $this->expoId = null;
        $this->expo = [];
        $this->flujos = [];
        $this->filtro = '';
    }

    private function cargarReporte(int $expoId): void
    {
        $this->expoId = $expoId;

        $expo = DB::table('expo')
            ->where('id', $expoId)
            ->first(['id', 'nombre', 'estado', 'fecha_inicio', 'fecha_fin']);
        abort_unless($expo, 404);

        $ofertas = DB::table('expo_cotizacion as ec')
            ->join('cotizacion as c', 'c.id', '=', 'ec.cotizacion_id')
            ->leftJoin('users as asesor_oferta', 'asesor_oferta.id', '=', 'c.vendedor')
            ->leftJoin('users as teleasesor_oferta', 'teleasesor_oferta.id', '=', 'c.users_id')
            ->where('ec.expo_id', $expoId)
            ->orderByDesc('ec.id')
            ->get([
                'ec.cotizacion_id', 'ec.flujo_id', 'ec.estado', 'ec.created_at',
                'c.nombre_cliente', 'asesor_oferta.name as asesor_oferta',
                'teleasesor_oferta.name as teleasesor_oferta',
            ])->map(function ($oferta) {
                $oferta->flujo_resuelto_id = $this->resolverFlujoId((int) $oferta->cotizacion_id, $oferta->flujo_id);
                return $oferta;
            });

        $flujoIds = $ofertas->pluck('flujo_resuelto_id')->filter()->unique()->values()->all();
        $facturasPorFlujo = empty($flujoIds)
            ? collect()
            : DB::table('historico_flujo as hf')
                ->join('factura as f', 'f.id', '=', 'hf.tramite_id')
                ->leftJoin('users as asesor', 'asesor.id', '=', 'f.vendedor')
                ->leftJoin('users as teleasesor', 'teleasesor.id', '=', 'f.users_id')
                ->leftJoin('users as gestor', 'gestor.id', '=', 'f.gestor_entrega')
                ->whereIn('hf.flujo_id', $flujoIds)
                ->whereIn('hf.tipo_tramite_id', [3, 5])
                ->where('hf.estado_id', '<>', 7)
                ->where('f.estado_venta_id', 1)
                ->get([
                    'hf.flujo_id', 'f.id', 'f.cai', 'asesor.name as asesor',
                    'teleasesor.name as teleasesor', 'gestor.name as gestor',
                ])->unique('id')->groupBy('flujo_id');

        $this->expo = (array) $expo;
        $this->flujos = $ofertas->map(function ($oferta) use ($facturasPorFlujo) {
            $facturas = $oferta->flujo_resuelto_id
                ? $facturasPorFlujo->get($oferta->flujo_resuelto_id, collect())
                : collect();

            return [
                'flujo_id' => $oferta->flujo_resuelto_id,
                'cotizacion_id' => (int) $oferta->cotizacion_id,
                'cliente' => $oferta->nombre_cliente ?: 'Sin cliente',
                'estado' => $oferta->estado,
                'estado_etiqueta' => $this->etiquetaEstado((string) $oferta->estado),
                'facturas' => $facturas->pluck('cai')->filter()->values()->all(),
                'total_facturas' => $facturas->count(),
                'asesores' => $this->nombresResponsables($facturas->pluck('asesor'), $oferta->asesor_oferta),
                'teleasesores' => $this->nombresResponsables($facturas->pluck('teleasesor'), $oferta->teleasesor_oferta),
                'gestores' => $this->nombresResponsables($facturas->pluck('gestor')),
                'fecha' => $oferta->created_at,
            ];
        })->values()->all();
    }

    public function render()
    {
        $busquedaExpo = mb_strtolower(trim($this->busquedaExpo));
        $expos = DB::table('expo as e')
            ->leftJoin('expo_cotizacion as ec', 'ec.expo_id', '=', 'e.id')
            ->when($busquedaExpo !== '', function ($query) use ($busquedaExpo) {
                $query->where(function ($subquery) use ($busquedaExpo) {
                    $subquery->whereRaw('LOWER(e.nombre) LIKE ?', ['%' . $busquedaExpo . '%'])
                        ->orWhereRaw('LOWER(e.estado) LIKE ?', ['%' . $busquedaExpo . '%'])
                        ->orWhere('e.id', 'like', '%' . $busquedaExpo . '%');
                });
            })
            ->groupBy('e.id', 'e.nombre', 'e.estado', 'e.fecha_inicio', 'e.fecha_fin')
            ->orderByDesc('e.id')
            ->get([
                'e.id', 'e.nombre', 'e.estado', 'e.fecha_inicio', 'e.fecha_fin',
                DB::raw('COUNT(ec.id) as total_flujos'),
            ]);

        $filtro = mb_strtolower(trim($this->filtro));
        $flujosFiltrados = collect($this->flujos)->filter(function (array $flujo) use ($filtro) {
            if ($filtro === '') {
                return true;
            }

            return str_contains(mb_strtolower(implode(' ', [
                $flujo['flujo_id'] ?? '', $flujo['cotizacion_id'], $flujo['cliente'],
                $flujo['estado_etiqueta'], $flujo['asesores'], $flujo['teleasesores'],
                $flujo['gestores'], implode(' ', $flujo['facturas']),
            ])), $filtro);
        })->values();

        return view('livewire.expo.reportedeexpo', [
            'expos' => $expos,
            'flujosFiltrados' => $flujosFiltrados,
            'totalFlujos' => count($this->flujos),
            'sinFacturar' => collect($this->flujos)->where('estado', 'PENDIENTE_FACTURACION')->count(),
            'facturaParcial' => collect($this->flujos)->where('estado', 'FACTURACION_PARCIAL')->count(),
            'finalizados' => collect($this->flujos)->whereIn('estado', ['PENDIENTE_LIQUIDACION', 'LIQUIDADA'])->count(),
        ]);
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

    private function nombresResponsables($valores, ?string $respaldo = null): string
    {
        $nombres = collect($valores)->filter()
            ->when($respaldo, fn ($items) => $items->push($respaldo))
            ->unique()->values();

        return $nombres->isEmpty() ? 'Sin asignar' : $nombres->implode(', ');
    }

    private function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'PENDIENTE_FACTURACION' => 'Sin facturar',
            'FACTURACION_PARCIAL' => 'Factura parcial',
            'PENDIENTE_LIQUIDACION' => 'Pendiente de liquidación',
            'LIQUIDADA' => 'Liquidada',
            default => str_replace('_', ' ', ucfirst(strtolower($estado))),
        };
    }
}