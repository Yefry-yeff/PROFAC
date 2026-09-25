<?php

namespace App\Livewire\Logistica;

use Livewire\Component;
use App\Models\Logistica\EquipoEntrega;
use App\Models\Logistica\EquipoEntregaAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class EquiposEntrega extends Component
{
    private const EQUIPOS_OCULTOS = [1, 2, 3, 4, 5, 6, 8, 11, 12, 16, 20];

    public function render()
    {
        return view('livewire.logistica.equipos-entrega');
    }

    /**
     * Inactiva equipos que deben quedar ocultos en esta pantalla.
     */
    private function sincronizarEquiposOcultos(): void
    {
        DB::table('equipos_entrega')
            ->whereIn('id', self::EQUIPOS_OCULTOS)
            ->where('estado_id', 1)
            ->update([
                'estado_id' => 2,
                'updated_at' => now(),
            ]);
    }

    /**
     * Registra una entrada en la bitácora de auditoría del equipo.
     */
    private function registrarAuditoria($equipoId, string $action, $oldData, $newData): void
    {
        EquipoEntregaAudit::create([
            'equipo_entrega_id' => $equipoId,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Guardar nuevo equipo de entrega
     */
    public function guardarEquipo(Request $request)
    {
        try {
            $request->validate([
                'nombre_equipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
            ], [
                'nombre_equipo.required' => 'El nombre del equipo es obligatorio',
            ]);

            $equipo = EquipoEntrega::create([
                'nombre_equipo' => trim($request->nombre_equipo),
                'descripcion' => trim($request->descripcion),
                'estado_id' => 1,
                'users_id_creador' => Auth::id(),
            ]);

            $this->registrarAuditoria($equipo->id, 'CREATE', null, [
                'nombre_equipo' => $equipo->nombre_equipo,
                'descripcion' => $equipo->descripcion,
            ]);

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Equipo de entrega creado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Ha ocurrido un error al crear el equipo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar equipos de entrega
     */
    public function listarEquipos()
    {
        try {
            $this->sincronizarEquiposOcultos();

            $equiposOcultos = implode(',', self::EQUIPOS_OCULTOS);
            $datos = DB::select("
                SELECT 
                    e.id,
                    e.nombre_equipo,
                    e.descripcion,
                    e.estado_id,
                    u.name AS creador,
                    e.created_at
                FROM equipos_entrega e
                INNER JOIN users u ON e.users_id_creador = u.id
                WHERE e.id NOT IN ({$equiposOcultos})
                ORDER BY e.id DESC
            ");

            return Datatables::of($datos)
                ->addColumn('estado', function ($datos) {
                    if ($datos->estado_id == 1) {
                        return '<span class="badge badge-success">ACTIVO</span>';
                    } else {
                        return '<span class="badge badge-danger">INACTIVO</span>';
                    }
                })
                ->addColumn('opciones', function ($datos) {
                    $historial = '
                        <button type="button" class="btn btn-sm btn-info" onclick="verHistorialEquipo(' . $datos->id . ')" title="Historial">
                            <i class="fa fa-history"></i>
                        </button>
                    ';
                    if ($datos->estado_id == 1) {
                        return '
                            <div class="btn-group">
                                ' . $historial . '
                                <button type="button" class="btn btn-sm btn-warning" onclick="editarEquipo(' . $datos->id . ')" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="desactivarEquipo(' . $datos->id . ')" title="Desactivar">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        ';
                    } else {
                        return '<div class="btn-group">' . $historial . '</div>';
                    }
                })
                ->rawColumns(['estado', 'opciones'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar equipos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un equipo para edición
     */
    public function obtenerEquipo($equipoId)
    {
        try {
            $equipo = EquipoEntrega::findOrFail($equipoId);
            
            return response()->json([
                'success' => true,
                'equipo' => [
                    'id' => $equipo->id,
                    'nombre_equipo' => $equipo->nombre_equipo,
                    'descripcion' => $equipo->descripcion,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener equipo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar equipo de entrega
     */
    public function actualizarEquipo(Request $request)
    {
        try {
            $request->validate([
                'equipo_id' => 'required|exists:equipos_entrega,id',
                'nombre_equipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
            ], [
                'nombre_equipo.required' => 'El nombre del equipo es obligatorio',
            ]);

            $equipo = EquipoEntrega::findOrFail($request->equipo_id);
            $oldData = [
                'nombre_equipo' => $equipo->nombre_equipo,
                'descripcion' => $equipo->descripcion,
            ];
            $equipo->nombre_equipo = $request->nombre_equipo;
            $equipo->descripcion = $request->descripcion;
            $equipo->save();

            $this->registrarAuditoria($equipo->id, 'UPDATE', $oldData, [
                'nombre_equipo' => $equipo->nombre_equipo,
                'descripcion' => $equipo->descripcion,
            ]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Equipo actualizado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al actualizar equipo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Desactivar equipo
     */
    public function desactivarEquipo($equipoId)
    {
        try {
            // Verificar si tiene distribuciones activas
            $distribucionesActivas = DB::table('distribuciones_entrega')
                ->where('equipo_entrega_id', $equipoId)
                ->whereIn('estado_id', [1, 2]) // Pendiente o En proceso
                ->count();

            if ($distribucionesActivas > 0) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'No se puede desactivar',
                    'text' => "El equipo tiene {$distribucionesActivas} distribución(es) activa(s). Debe completarlas o cancelarlas primero.",
                ], 422);
            }

            $equipo = EquipoEntrega::findOrFail($equipoId);
            $equipo->estado_id = 2;
            $equipo->save();

            $this->registrarAuditoria($equipo->id, 'UPDATE', ['estado_id' => 1], ['estado_id' => 2]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Equipo desactivado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al desactivar equipo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Historial de auditoría de un equipo (creación y todas sus actualizaciones).
     */
    public function obtenerHistorialEquipo($equipoId)
    {
        try {
            $historial = EquipoEntregaAudit::with('usuario')
                ->where('equipo_entrega_id', $equipoId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($h) {
                    return [
                        'action' => $h->action,
                        'old_data' => $h->old_data,
                        'new_data' => $h->new_data,
                        'usuario' => $h->usuario->name ?? '-',
                        'fecha' => $h->created_at->format('d/m/Y h:i A'),
                    ];
                });

            return response()->json([
                'success' => true,
                'historial' => $historial,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
