<?php

namespace App\Http\Controllers;

use App\Services\PosIntegration\Receiving;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosIntegrationHistoryController extends Controller
{
    private function runOperation(callable $operation, string $status)
    {
        try {
            $operation();
            return response()->json(['status' => $status]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Throwable $e) {
            \Log::error('Integración POS: operación fallida', ['exception' => get_class($e)]);
            return response()->json(['message' => 'No se pudo completar la operación.'], 500);
        }
    }

    private function authorizeAccess(Request $request, bool $write = false): void
    {
        abort_unless(config('pos_integration.enabled'), 404);
        abort_unless($request->user() && $request->user()->tienePermiso($write ? 'inventario/translado' : 'translados/historial'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $transfers = DB::table('pos_integration_transfers')->where('direction', 'in')->orderByDesc('id')->get();
        $outgoing = DB::table('pos_integration_transfers')->where('direction', 'out')->orderByDesc('id')->get();
        return response()->json(compact('transfers', 'outgoing'));
    }

    public function detail(Request $request, string $uuid)
    {
        $this->authorizeAccess($request);
        $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $uuid)->where('direction', 'in')->first();
        abort_unless($transfer, 404);
        $data = json_decode($transfer->snapshot, true);
        $ids = array_column($data['lines'], 'product_profac_id');
        $presentations = DB::table('unidad_medida_venta as uv')->join('unidad_medida as u', 'u.id', '=', 'uv.unidad_medida_id')
            ->whereIn('uv.producto_id', $ids)->where('uv.estado_id', 1)
            ->get(['uv.id', 'uv.producto_id', 'uv.unidad_venta', 'u.nombre']);
        $locations = DB::table('seccion as s')->join('segmento as g', 'g.id', '=', 's.segmento_id')
            ->join('bodega as b', 'b.id', '=', 'g.bodega_id')->where('s.id', '<>', 433)
            ->get(['s.id as seccion_id', 's.descripcion as seccion', 'g.id as segmento_id', 'g.descripcion as segmento', 'b.id as bodega_id', 'b.nombre as bodega']);
        return response()->json(compact('transfer', 'data', 'presentations', 'locations'));
    }

    public function approve(Request $request, string $uuid, Receiving $receiving)
    {
        $this->authorizeAccess($request, true);
        $data = $request->validate([
            'allocations' => 'required|array|min:1|max:500',
            'allocations.*.line_uuid' => 'required|uuid',
            'allocations.*.presentation_id' => 'required|integer|min:1',
            'allocations.*.quantity' => 'required|integer|min:1',
            'allocations.*.bodega_id' => 'required|integer|min:1',
            'allocations.*.segmento_id' => 'required|integer|min:1',
            'allocations.*.seccion_id' => 'required|integer|min:1',
        ]);
        return $this->runOperation(fn () => $receiving->approve($uuid, $request->user()->id, $data['allocations']), 'received');
    }

    public function reject(Request $request, string $uuid, Receiving $receiving)
    {
        $this->authorizeAccess($request, true);
        $data = $request->validate(['reason' => 'required|string|min:5|max:400']);
        return $this->runOperation(fn () => $receiving->reject($uuid, $request->user()->id, $data['reason']), 'rejected');
    }

    public function confirmReturn(Request $request, string $uuid, Receiving $receiving)
    {
        $this->authorizeAccess($request, true);
        return $this->runOperation(fn () => $receiving->confirmReturn($uuid, $request->user()->id), 'returned');
    }

    public function print(Request $request, string $uuid)
    {
        $this->authorizeAccess($request);
        $transfer = DB::table('pos_integration_transfers')->where('transfer_uuid', $uuid)->first();
        abort_unless($transfer, 404);
        if ($transfer->direction === 'out') {
            abort_unless($transfer->local_transfer_id
                && DB::table('translado')->where('id', $transfer->local_transfer_id)->exists(),
                409, 'Falta el documento original del traslado enviado a Paperland.');
            return redirect('/translado/imprimir/'.$transfer->local_transfer_id);
        }
        $data = json_decode($transfer->snapshot, true, 512, JSON_THROW_ON_ERROR);
        $movements = DB::table('pos_integration_movements')->where('transfer_id', $transfer->id)->orderBy('id')->get();
        return Pdf::loadView('pos-integration.receipt', compact('transfer', 'data', 'movements'))
            ->setPaper('letter')->setOptions(['isRemoteEnabled' => false])
            ->stream('traslado-paperland-'.$transfer->id.'.pdf');
    }
}
