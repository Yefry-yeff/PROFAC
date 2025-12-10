<?php

namespace App\Http\Livewire\Logistica;

use Livewire\Component;
use App\Models\Logistica\EquipoEntrega;
use App\Models\Logistica\EquipoEntregaMiembro;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;

class EquiposEntrega extends Component
{
    public function render()
    {
        $usuarios = User::all();
        return view('livewire.logistica.equipos-entrega', compact('usuarios'));
    }

    /**
     * Guardar nuevo equipo de entrega
     */
    public function guardarEquipo(Request $request)
    {
        try {
            // Decodificar el JSON de miembros
            $miembros = json_decode($request->miembros, true);
            
            if (empty($miembros)) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Debe agregar al menos un miembro al equipo',
                ], 422);
            }

            $request->validate([
                'nombre_equipo' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
            ], [
                'nombre_equipo.required' => 'El nombre del equipo es obligatorio',
            ]);

            // Validar que la suma de porcentajes no exceda 100
            $totalPorcentaje = collect($miembros)->sum('porcentaje');
            if ($totalPorcentaje > 100) {
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Error de validación',
                    'text' => "La suma de porcentajes ({$totalPorcentaje}%) excede el 100%. Por favor ajuste los valores.",
                ], 422);
            }

            DB::beginTransaction();

            // Crear equipo
            $equipo = EquipoEntrega::create([
                'nombre_equipo' => trim($request->nombre_equipo),
                'descripcion' => trim($request->descripcion),
                'estado_id' => 1,
                'users_id_creador' => Auth::id(),
            ]);

            // Agregar miembros
            foreach ($miembros as $miembro) {
                EquipoEntregaMiembro::create([
                    'equipo_entrega_id' => $equipo->id,
                    'user_id' => $miembro['user_id'],
                    'porcentaje_comision' => $miembro['porcentaje'],
                    'estado_id' => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => 'Equipo de entrega creado correctamente',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
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
            $datos = DB::select("
                SELECT 
                    e.id,
                    e.nombre_equipo,
                    e.descripcion,
                    e.estado_id,
                    u.name AS creador,
                    e.created_at,
                    (SELECT COUNT(*) FROM equipos_entrega_miembros WHERE equipo_entrega_id = e.id AND estado_id = 1) as total_miembros,
                    (SELECT SUM(porcentaje_comision) FROM equipos_entrega_miembros WHERE equipo_entrega_id = e.id AND estado_id = 1) as total_porcentaje
                FROM equipos_entrega e
                INNER JOIN users u ON e.users_id_creador = u.id
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
                ->addColumn('porcentaje', function ($datos) {
                    $porcentaje = $datos->total_porcentaje ?? 0;
                    $color = $porcentaje == 100 ? 'success' : ($porcentaje > 100 ? 'danger' : 'warning');
                    return "<span class='badge badge-{$color}'>{$porcentaje}%</span>";
                })
                ->addColumn('miembros', function ($datos) {
                    return "<span class='badge badge-info'>{$datos->total_miembros} miembro(s)</span>";
                })
                ->addColumn('opciones', function ($datos) {
                    if ($datos->estado_id == 1) {
                        return '
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-info" onclick="verMiembros(' . $datos->id . ')" title="Ver miembros">
                                    <i class="fa fa-users"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="editarEquipo(' . $datos->id . ')" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="desactivarEquipo(' . $datos->id . ')" title="Desactivar">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        ';
                    } else {
                        return '<span class="badge badge-secondary">Sin acciones</span>';
                    }
                })
                ->rawColumns(['estado', 'porcentaje', 'miembros', 'opciones'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar equipos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener miembros de un equipo
     */
    public function obtenerMiembros($equipoId)
    {
        try {
            $miembros = DB::select("
                SELECT 
                    m.id,
                    m.user_id,
                    u.name AS nombre_usuario,
                    u.email,
                    m.porcentaje_comision,
                    m.estado_id,
                    m.created_at
                FROM equipos_entrega_miembros m
                INNER JOIN users u ON m.user_id = u.id
                WHERE m.equipo_entrega_id = ?
                AND m.estado_id = 1
                ORDER BY m.porcentaje_comision DESC
            ", [$equipoId]);

            return response()->json([
                'success' => true,
                'miembros' => $miembros
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener miembros',
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
            $equipo->nombre_equipo = $request->nombre_equipo;
            $equipo->descripcion = $request->descripcion;
            $equipo->save();

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
     * Agregar miembro a equipo existente
     */
    public function agregarMiembro(Request $request)
    {
        try {
            $request->validate([
                'equipo_id' => 'required|exists:equipos_entrega,id',
                'user_id' => 'required|exists:users,id',
                'porcentaje' => 'required|numeric|min:0|max:100',
            ]);

            $equipo = EquipoEntrega::findOrFail($request->equipo_id);
            
            // Verificar que no exceda el 100%
            if (!$equipo->tieneCupoParaPorcentaje($request->porcentaje)) {
                $disponible = 100 - $equipo->total_porcentajes;
                return response()->json([
                    'icon' => 'error',
                    'title' => 'Porcentaje excedido',
                    'text' => "Solo hay {$disponible}% disponible. El equipo ya tiene {$equipo->total_porcentajes}% asignado.",
                ], 422);
            }

            // Verificar que el usuario no esté ya en el equipo
            $existe = EquipoEntregaMiembro::where('equipo_entrega_id', $request->equipo_id)
                ->where('user_id', $request->user_id)
                ->where('estado_id', 1)
                ->exists();

            if ($existe) {
                return response()->json([
                    'icon' => 'warning',
                    'title' => 'Usuario duplicado',
                    'text' => 'Este usuario ya es miembro del equipo',
                ], 422);
            }

            EquipoEntregaMiembro::create([
                'equipo_entrega_id' => $request->equipo_id,
                'user_id' => $request->user_id,
                'porcentaje_comision' => $request->porcentaje,
                'estado_id' => 1,
            ]);

            return response()->json([
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Miembro agregado correctamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al agregar miembro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remover miembro del equipo
     */
    public function removerMiembro($miembroId)
    {
        try {
            $miembro = EquipoEntregaMiembro::findOrFail($miembroId);
            $miembro->estado_id = 2;
            $miembro->save();

            return response()->json([
                'icon' => 'success',
                'title' => 'Éxito',
                'text' => 'Miembro removido del equipo',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Error al remover miembro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
