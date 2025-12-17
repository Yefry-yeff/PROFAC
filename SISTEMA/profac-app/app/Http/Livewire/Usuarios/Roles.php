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
        \Log::info('=== ACTUALIZAR ROL INICIO ===');
        \Log::info('Request data', ['data' => $request->all()]);
        \Log::info('Rol ID', ['id' => $id]);
        
        try {
            $request->validate([
                'nombre' => 'required|string|max:255|unique:rol,nombre,' . $id,
                'estado_id' => 'required|integer|exists:estado,id'
            ]);

            \Log::info('Validación exitosa');

            DB::beginTransaction();

            $rol = Rol::findOrFail($id);
            \Log::info('Rol encontrado', ['rol_id' => $rol->id, 'nombre' => $rol->nombre]);
            
            $rol->update([
                'nombre' => $request->nombre,
                'estado_id' => $request->estado_id
            ]);

            \Log::info('Rol actualizado');

            // Procesar cambios de usuarios
            if ($request->has('usuarios_agregar') && is_array($request->usuarios_agregar)) {
                foreach ($request->usuarios_agregar as $usuarioId) {
                    DB::table('users')->where('id', $usuarioId)->update(['rol_id' => $id]);
                    \Log::info('Usuario agregado al rol', ['usuario_id' => $usuarioId, 'rol_id' => $id]);
                }
            }

            if ($request->has('usuarios_quitar') && is_array($request->usuarios_quitar)) {
                foreach ($request->usuarios_quitar as $usuarioId) {
                    DB::table('users')->where('id', $usuarioId)->where('rol_id', $id)->update(['rol_id' => null]);
                    \Log::info('Usuario quitado del rol', ['usuario_id' => $usuarioId]);
                }
            }

            // Procesar cambios de permisos
            if ($request->has('permisos_agregar') && is_array($request->permisos_agregar)) {
                foreach ($request->permisos_agregar as $submenuId) {
                    DB::table('rol_submenu')->insertOrIgnore([
                        'rol_id' => $id,
                        'sub_menu_id' => $submenuId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Log::info('Permiso agregado al rol', ['submenu_id' => $submenuId, 'rol_id' => $id]);
                }
            }

            if ($request->has('permisos_quitar') && is_array($request->permisos_quitar)) {
                foreach ($request->permisos_quitar as $submenuId) {
                    DB::table('rol_submenu')
                        ->where('rol_id', $id)
                        ->where('sub_menu_id', $submenuId)
                        ->delete();
                    \Log::info('Permiso quitado del rol', ['submenu_id' => $submenuId]);
                }
            }

            DB::commit();

            \Log::info('=== ACTUALIZAR ROL FIN ===');

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol actualizado correctamente',
                'data' => $rol
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar rol', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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

    /**
     * Obtener usuarios de un rol
     */
    public function obtenerUsuariosDelRol($rolId)
    {
        try {
            $usuarios = DB::table('users')
                ->leftJoin('rol as rol_anterior', 'users.rol_id', '=', 'rol_anterior.id')
                ->where('users.rol_id', $rolId)
                ->select(
                    'users.id', 
                    'users.name', 
                    'users.email', 
                    'users.rol_id',
                    'rol_anterior.nombre as rol_anterior_nombre'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar usuario al rol
     */
    public function agregarUsuarioAlRol(Request $request, $rolId)
    {
        \Log::info('=== AGREGAR USUARIO AL ROL INICIO ===');
        \Log::info('Request data', ['data' => $request->all()]);
        \Log::info('Rol ID', ['id' => $rolId]);
        
        try {
            $request->validate([
                'usuario_id' => 'required|integer|exists:users,id'
            ]);

            \Log::info('Validación exitosa');

            DB::beginTransaction();

            $usuario = DB::table('users')->where('id', $request->usuario_id)->first();
            \Log::info('Usuario encontrado', ['usuario_id' => $usuario->id, 'rol_anterior' => $usuario->rol_id]);
            
            $rolAnterior = $usuario->rol_id;

            // Actualizar el rol del usuario
            DB::table('users')
                ->where('id', $request->usuario_id)
                ->update(['rol_id' => $rolId]);

            \Log::info('Rol actualizado para usuario', ['usuario_id' => $request->usuario_id, 'nuevo_rol' => $rolId]);

            DB::commit();

            $mensaje = $rolAnterior 
                ? 'Usuario agregado correctamente. Rol anterior actualizado.'
                : 'Usuario agregado correctamente al rol.';

            \Log::info('=== AGREGAR USUARIO AL ROL FIN ===');

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al agregar usuario', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al agregar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quitar usuario del rol
     */
    public function quitarUsuarioDelRol(Request $request, $rolId)
    {
        \Log::info('=== QUITAR USUARIO DEL ROL INICIO ===');
        \Log::info('Request data', ['data' => $request->all()]);
        \Log::info('Rol ID', ['id' => $rolId]);
        
        try {
            $request->validate([
                'usuario_id' => 'required|integer|exists:users,id'
            ]);

            \Log::info('Validación exitosa');

            DB::beginTransaction();

            // Poner el rol en NULL
            $affected = DB::table('users')
                ->where('id', $request->usuario_id)
                ->where('rol_id', $rolId)
                ->update(['rol_id' => null]);

            \Log::info('Filas afectadas', ['affected' => $affected]);

            DB::commit();

            \Log::info('=== QUITAR USUARIO DEL ROL FIN ===');

            return response()->json([
                'success' => true,
                'mensaje' => 'Usuario removido del rol correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al quitar usuario', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al quitar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los usuarios
     */
    public function listarTodosUsuarios()
    {
        try {
            $usuarios = DB::table('users')
                ->select('id', 'name', 'email', 'rol_id')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $usuarios
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener rol anterior de un usuario
     */
    public function obtenerRolAnteriorUsuario($usuarioId)
    {
        try {
            $usuario = DB::table('users')
                ->leftJoin('rol', 'users.rol_id', '=', 'rol.id')
                ->where('users.id', $usuarioId)
                ->select('users.rol_id as rol_anterior_id', 'rol.nombre as rol_anterior_nombre')
                ->first();

            return response()->json([
                'success' => true,
                'rol_anterior_id' => $usuario->rol_anterior_id,
                'rol_anterior_nombre' => $usuario->rol_anterior_nombre
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener rol anterior: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener permisos (submenus) del rol
     */
    public function obtenerPermisosDelRol($rolId)
    {
        \Log::info('=== OBTENER PERMISOS DEL ROL INICIO ===');
        \Log::info('Rol ID', ['id' => $rolId]);

        try {
            $permisos = DB::table('rol_submenu as rs')
                ->join('sub_menu as sm', 'rs.sub_menu_id', '=', 'sm.id')
                ->leftJoin('menu as m', 'sm.menu_id', '=', 'm.id')
                ->where('rs.rol_id', $rolId)
                ->select(
                    'sm.id',
                    'sm.nombre as submenu_nombre',
                    'sm.url as ruta',
                    'm.nombre_menu as menu_nombre'
                )
                ->orderBy('m.nombre_menu')
                ->orderBy('sm.nombre')
                ->get();

            \Log::info('Permisos encontrados', ['count' => $permisos->count()]);
            \Log::info('=== OBTENER PERMISOS DEL ROL FIN ===');

            return response()->json([
                'success' => true,
                'data' => $permisos
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al obtener permisos del rol', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todos los submenus disponibles
     */
    public function listarTodosSubmenus()
    {
        \Log::info('=== LISTAR TODOS LOS SUBMENUS INICIO ===');

        try {
            $submenus = DB::table('sub_menu as sm')
                ->leftJoin('menu as m', 'sm.menu_id', '=', 'm.id')
                ->select(
                    'sm.id',
                    'sm.nombre',
                    'sm.url as ruta',
                    'm.nombre_menu as menu_nombre'
                )
                ->orderBy('m.nombre_menu')
                ->orderBy('sm.nombre')
                ->get();

            \Log::info('Submenus encontrados', ['count' => $submenus->count()]);
            \Log::info('=== LISTAR TODOS LOS SUBMENUS FIN ===');

            return response()->json([
                'success' => true,
                'data' => $submenus
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al listar submenus', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar submenus: ' . $e->getMessage()
            ], 500);
        }
    }
}
