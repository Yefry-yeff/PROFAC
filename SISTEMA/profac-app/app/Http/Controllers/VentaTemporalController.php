<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaTemporalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['tipo' => 'required|in:oferta,factura']);
        $this->limpiarVencidos();

        $temporales = DB::table('venta_temporal')
            ->where('usuario_id', Auth::id())
            ->where('tipo', $request->tipo)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get(['id', 'tipo', 'codigo_tipo', 'titulo', 'url_reanudacion', 'expira_at', 'created_at', 'updated_at'])
            ->when($request->tipo === 'factura', function ($registros) {
                return $registros->unique(function ($temporal) {
                    $titulo = mb_strtolower(trim((string) $temporal->titulo));
                    return $titulo !== '' ? $titulo : 'temporal-' . $temporal->id;
                })->values();
            });

        return response()->json(['data' => $temporales]);
    }

    public function show(int $id): JsonResponse
    {
        $this->limpiarVencidos();
        $temporal = $this->propio($id);

        return response()->json([
            'data' => array_merge((array) $temporal, [
                'contenido' => json_decode($temporal->contenido, true),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'tipo' => 'required|in:oferta,factura',
            'codigo_tipo' => 'required|string|max:80',
            'titulo' => 'nullable|string|max:180',
            'url_reanudacion' => ['required', 'string', 'max:2000', 'regex:/^\/(?!\/)/'],
            'contenido' => 'required|array',
        ]);
        $this->limpiarVencidos();

        $values = [
            'usuario_id' => Auth::id(),
            'tipo' => $data['tipo'],
            'codigo_tipo' => $data['codigo_tipo'],
            'titulo' => $data['titulo'] ?: null,
            'url_reanudacion' => $data['url_reanudacion'],
            'contenido' => json_encode($data['contenido'], JSON_UNESCAPED_UNICODE),
            'expira_at' => now()->addHours(24),
            'updated_at' => now(),
        ];

        if (!empty($data['id'])) {
            $this->propio((int) $data['id']);
            DB::table('venta_temporal')->where('id', (int) $data['id'])->update($values);
            $id = (int) $data['id'];
        } else {
            $values['created_at'] = now();
            $id = DB::table('venta_temporal')->insertGetId($values);
        }

        return response()->json(['id' => $id, 'expira_at' => $values['expira_at']]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->propio($id);
        DB::table('venta_temporal')->where('id', $id)->delete();

        return response()->json(['deleted' => true]);
    }

    private function propio(int $id): object
    {
        $temporal = DB::table('venta_temporal')
            ->where('id', $id)
            ->where('usuario_id', Auth::id())
            ->where('expira_at', '>', now())
            ->first();

        abort_unless($temporal, 404);
        return $temporal;
    }

    private function limpiarVencidos(): void
    {
        DB::table('venta_temporal')->where('expira_at', '<=', now())->delete();
    }
}