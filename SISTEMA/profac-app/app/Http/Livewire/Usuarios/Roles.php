<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Estado;
use Yajra\DataTables\Facades\DataTables;

class Roles extends Component
{
    public $titulo = 'Gestión de Roles';
    public $roles;
    public $estados;

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->roles = Rol::with(['estado', 'usuarios'])->orderBy('nombre')->get();
        $this->estados = Estado::all();
    }

    public function render()
    {
        return view('livewire.usuarios.roles');
    }

    /**
     * Listar roles para DataTables
     */
    public function listarRoles()
    {
        try {
            $roles = DB::select("
                SELECT 
                    r.id,
                    r.nombre,
                    r.estado_id,
                    e.descripcion as estado,
                    COUNT(DISTINCT u.id) as total_usuarios,
                    COUNT(DISTINCT rs.sub_menu_id) as total_permisos,
                    r.created_at
                FROM rol r
                LEFT JOIN estado e ON e.id = r.estado_id
                LEFT JOIN users u ON u.rol_id = r.id
                LEFT JOIN rol_submenu rs ON rs.rol_id = r.id
                GROUP BY r.id, r.nombre, r.estado_id, e.descripcion, r.created_at
                ORDER BY r.nombre
            ");

            return DataTables::of($roles)
                ->addColumn('estado_badge', function ($rol) {
                    if ($rol->estado_id == 1) {
                        return '<span class="badge badge-success">Activo</span>';
                    }
                    return '<span class="badge badge-danger">Inactivo</span>';
                })
                ->addColumn('fecha', function ($rol) {
                    return date('d/m/Y', strtotime($rol->created_at));
                })
                ->addColumn('opciones', function ($rol) {
                    $btnEditar = '<button class="btn btn-warning btn-xs" onclick="editarRol(' . $rol->id . ')" title="Editar">
                                    <i class="fa fa-edit"></i>
                                  </button>';
                    
                    $btnEstado = '<button class="btn btn-' . ($rol->estado_id == 1 ? 'danger' : 'success') . ' btn-xs" 
                                    onclick="cambiarEstadoRol(' . $rol->id . ', ' . $rol->estado_id . ')" 
                                    title="' . ($rol->estado_id == 1 ? 'Desactivar' : 'Activar') . '">
                                    <i class="fa fa-' . ($rol->estado_id == 1 ? 'times' : 'check') . '"></i>
                                  </button>';
                    
                    // Solo permitir eliminar si no tiene usuarios asignados
                    $btnEliminar = '';
                    if ($rol->total_usuarios == 0) {
                        $btnEliminar = '<button class="btn btn-danger btn-xs" onclick="eliminarRol(' . $rol->id . ')" title="Eliminar">
                                          <i class="fa fa-trash"></i>
                                        </button>';
                    }
                    
                    return '<div class="btn-group">' . $btnEditar . ' ' . $btnEstado . ' ' . $btnEliminar . '</div>';
                })
                ->rawColumns(['estado_badge', 'opciones'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar nuevo rol
     */
    public function guardarRol(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255|unique:rol,nombre',
                'estado_id' => 'required|integer|exists:estado,id'
            ]);

            DB::beginTransaction();

            $rol = Rol::create([
                'nombre' => $request->nombre,
                'estado_id' => $request->estado_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol creado correctamente',
                'data' => $rol
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al crear rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un rol
     */
    public function obtenerRol($id)
    {
        try {
            $rol = Rol::with(['usuarios', 'submenus'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $rol
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Rol no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar rol
     */
    public function actualizarRol(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255|unique:rol,nombre,' . $id,
                'estado_id' => 'required|integer|exists:estado,id'
            ]);

            DB::beginTransaction();

            $rol = Rol::findOrFail($id);
            $rol->update([
                'nombre' => $request->nombre,
                'estado_id' => $request->estado_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol actualizado correctamente',
                'data' => $rol
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al actualizar rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del rol
     */
    public function cambiarEstadoRol($id)
    {
        try {
            DB::beginTransaction();

            $rol = Rol::findOrFail($id);
            $nuevoEstado = $rol->estado_id == 1 ? 2 : 1;
            
            $rol->update(['estado_id' => $nuevoEstado]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Estado del rol actualizado correctamente',
                'estado' => $nuevoEstado
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar rol
     */
    public function eliminarRol($id)
    {
        try {
            DB::beginTransaction();

            $rol = Rol::findOrFail($id);

            // Verificar si tiene usuarios asignados
            if ($rol->usuarios()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se puede eliminar el rol porque tiene usuarios asignados'
                ], 400);
            }

            // Eliminar relaciones con submenus
            $rol->submenus()->detach();

            // Eliminar el rol
            $rol->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al eliminar rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de estados
     */
    public function listarEstados()
    {
        try {
            $estados = Estado::all();
            
            return response()->json([
                'success' => true,
                'data' => $estados
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar estados: ' . $e->getMessage()
            ], 500);
        }
    }
}
