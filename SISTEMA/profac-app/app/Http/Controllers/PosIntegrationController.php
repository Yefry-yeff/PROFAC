<?php

namespace App\Http\Controllers;

use App\Services\PosIntegration\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PosIntegrationController extends Controller
{
    public function health()
    {
        try {
            $seen = \Illuminate\Support\Facades\Cache::get('pos_integration.worker_last_seen_at');
        } catch (\Throwable $e) {
            $seen = null;
        }
        $runtime = Schema::hasTable('integration_runtime') ? DB::table('integration_runtime')->where('id', 1)->first() : null;
        return response()->json(['schema_version' => 1, 'source' => 'profac',
            'enabled' => (bool) config('pos_integration.enabled'), 'worker_last_seen_at' => $seen,
            'instance_id' => $runtime->instance_id ?? null,
            'coordinator_online' => \App\Services\PosIntegration\Coordinator::online($runtime),
            'worker_state' => \App\Services\PosIntegration\Coordinator::state($runtime),
            'prepared' => true]);
    }

    public function event(Request $request, Inbox $inbox)
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return response()->json(['error' => 'Invalid JSON'], 422);
        }
        $rules = [
            'schema_version' => 'required|integer|in:1',
            'event_id' => 'required|uuid',
            'source' => 'required|in:pos',
            'type' => ['required', Rule::in(['transfer.dispatched', 'transfer.received', 'transfer.rejected', 'transfer.returned', 'inventory.changed'])],
            'occurred_at' => 'required|date',
            'data' => 'required|array',
        ];
        switch ($data['type'] ?? '') {
            case 'transfer.dispatched':
                $rules += [
                    'data.transfer_uuid' => 'required|uuid',
                    'data.reference' => 'required|string|max:255',
                    'data.source_store_id' => 'nullable|integer|min:1',
                    'data.destination_store_id' => 'nullable|integer|min:1',
                    'data.comment' => 'nullable|string',
                    'data.actor_id' => 'nullable|integer|min:1',
                    'data.lines' => 'required|array|min:1',
                    'data.lines.*.line_uuid' => 'required|uuid|distinct',
                    'data.lines.*.product_profac_id' => 'required|integer|min:1',
                    'data.lines.*.product_pos_id' => 'nullable|integer|min:1',
                    'data.lines.*.name' => 'required|string|max:1000',
                    'data.lines.*.quantity_base' => 'required|integer|min:1',
                    'data.lines.*.source_factor' => 'required|integer|min:1',
                    'data.lines.*.unit_profac_id' => 'required|integer|min:1',
                    'data.lines.*.presentation_profac_id' => 'nullable|integer|min:1',
                    'data.lines.*.source_lot_id' => 'required|integer|min:1',
                    'data.lines.*.expires_at' => 'nullable|date_format:Y-m-d',
                    'data.lines.*.cost_base' => ['required', 'string', 'regex:/^\d+(\.\d+)?$/D'],
                ];
                break;
            case 'inventory.changed':
                $rules += [
                    'data.store_id' => 'required|integer|min:1',
                    'data.product_pos_id' => 'required|integer|min:1',
                    'data.product_profac_id' => 'nullable|integer|min:1',
                    'data.name' => 'required|string|max:1000',
                    'data.version' => 'required|integer|min:1',
                    'data.physical_base' => 'present|nullable|integer|min:0',
                    'data.known_physical_base' => 'required|integer|min:0',
                    'data.unresolved_lots' => 'required|integer|min:0',
                    'data.available_base' => 'present|nullable|integer|min:0',
                    'data.reserved_base' => 'present|nullable|integer|min:0',
                    'data.availability_status' => 'required|in:known,reservation_unit_pending,presentation_unit_pending',
                    'data.pending_base' => 'required|integer|min:0',
                    'data.reason' => 'required|string',
                    'data.document_type' => 'nullable|string',
                    'data.document_id' => 'nullable|integer|min:1',
                    'data.presentations' => 'present|array',
                    'data.presentations.*.id' => 'required|integer|min:1',
                    'data.presentations.*.unit_id' => 'required|integer|min:1',
                    'data.presentations.*.name' => 'required|string',
                    'data.presentations.*.factor' => 'required|integer|min:1',
                    'data.presentations.*.quantity' => 'required|integer|min:0',
                    'data.presentations.*.quantity_base' => 'required|integer|min:0',
                    'data.movements' => 'sometimes|array',
                ];
                break;
            case 'transfer.received':
                $rules += [
                    'data.complete' => 'required_with:data.receipt_version|boolean',
                    'data.receipt_version' => 'required_with:data.complete|integer|min:1|max:200',
                    'data.transfer_uuid' => 'required|uuid',
                    'data.actor_id' => 'nullable|integer|min:1',
                    'data.allocations' => 'required|array|min:1',
                    'data.allocations.*.line_uuid' => 'required|uuid',
                    'data.allocations.*.lot_id' => 'required|integer|min:1',
                    'data.allocations.*.presentation_id' => 'required|integer|min:1',
                    'data.allocations.*.factor' => 'required|integer|min:1',
                    'data.allocations.*.quantity_base' => 'required|integer|min:1',
                    'data.allocations.*.quantity_stock' => 'required_with:data.allocations.*.quantity_base_pos|integer|min:1|max:2147483647',
                    'data.allocations.*.quantity_base_pos' => 'required_with:data.allocations.*.quantity_stock|integer|min:1|max:2147483647',
                    'data.allocations.*.seccion_id' => 'required|integer|min:1',
                ];
                break;
            case 'transfer.rejected':
                $rules += [
                    'data.transfer_uuid' => 'required|uuid',
                    'data.actor_id' => 'nullable|integer|min:1',
                    'data.reason' => 'required|string|min:5|max:400',
                ];
                break;
            case 'transfer.returned':
                $rules += [
                    'data.transfer_uuid' => 'required|uuid',
                    'data.actor_id' => 'nullable|integer|min:1',
                ];
                break;
        }
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid event', 'fields' => $validator->errors()], 422);
        }
        try {
            return $inbox->receive($data, $body);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Throwable $e) {
            \Log::error('Integración POS: error de procesamiento', ['exception' => get_class($e)]);
            return response()->json(['error' => 'Event processing failed'], 500);
        }
    }

    public function productImage(int $product)
    {
        foreach (DB::table('img_producto')->where('producto_id', $product)->orderBy('id')->pluck('url_img') as $name) {
            if (!is_string($name) || !preg_match('/\A[^\/\\\\\x00-\x1f]+\.(?:jpe?g|png|gif|webp)\z/iD', $name)) {
                \Illuminate\Support\Facades\Log::warning('Ruta de imagen de producto Profac invalida.', ['product_id' => $product]);
                continue;
            }
            $file = public_path('catalogo'.DIRECTORY_SEPARATOR.$name);
            if (is_file($file) && in_array((new \finfo(FILEINFO_MIME_TYPE))->file($file), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
                return response()->json(['filename' => $name]);
            }
        }

        return response()->json(['error' => 'Imagen no encontrada'], 404);
    }

    public function catalog(Request $request, string $entity)
    {
        $entities = [
            'units' => ['unidad_medida', ['id', 'unidad', 'nombre', 'simbolo']],
            'brands' => ['marca', ['id', 'nombre']],
            'categories' => ['categoria_producto', ['id', 'descripcion']],
            'subcategories' => ['sub_categoria', ['id', 'descripcion', 'categoria_producto_id']],
            'products' => ['producto', ['id', 'nombre', 'descripcion', 'isv', 'marca_id', 'sub_categoria_id', 'codigo_barra', 'codigo_estatal', 'estado_producto_id', 'precio_base', 'unidad_medida_compra_id']],
            'presentations' => ['unidad_medida_venta', ['id', 'producto_id', 'unidad_medida_id', 'unidad_venta', 'estado_id', 'unidad_venta_defecto']],
        ];
        abort_unless(isset($entities[$entity]), 404);
        $input = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:500',
            'updated_since' => 'sometimes|date',
        ]);
        [$table, $columns] = $entities[$entity];
        $query = DB::table($table)->select($columns)->orderBy('id');
        if (!empty($input['updated_since']) && Schema::hasColumn($table, 'updated_at')) {
            $query->where(function ($q) use ($input) {
                $q->where('updated_at', '>=', $input['updated_since'])->orWhereNull('updated_at');
            });
        }
        $page = (int) ($input['page'] ?? 1);
        $perPage = (int) ($input['per_page'] ?? 100);
        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage + 1)->get();
        return response()->json(['data' => $rows->take($perPage)->values(), 'next_page' => $rows->count() > $perPage ? $page + 1 : null]);
    }
}
