<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Estado;
use App\Models\NivelRol;
use App\Models\Area;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Usuarios\ReporteAccesosRolExport;
use App\Exports\Usuarios\ReporteUsuariosPorRolExport;

class Roles extends Component
{
    public $titulo = 'Gestión de Roles';
    public $roles;
    public $estados;
    public $niveles;
    public $areas;

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->roles   = Rol::with(['estado', 'usuarios', 'nivel', 'area'])->orderBy('nombre')->get();
        $this->estados = Estado::all();
        $this->niveles = NivelRol::activos()->get();
        $this->areas   = Area::activas()->get();
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
                    r.nivel_id,
                    r.area_id,
                    e.descripcion          AS estado,
                    nr.nombre              AS nivel_nombre,
                    nr.orden               AS nivel_orden,
                    a.nombre               AS area_nombre,
                    COUNT(DISTINCT u.id)   AS total_usuarios,
                    COUNT(DISTINCT rs.sub_menu_id) AS total_permisos,
                    r.created_at
                FROM rol r
                LEFT JOIN estado      e  ON e.id  = r.estado_id
                LEFT JOIN nivel_rol   nr ON nr.id = r.nivel_id
                LEFT JOIN area        a  ON a.id  = r.area_id
                LEFT JOIN users       u  ON u.rol_id = r.id
                LEFT JOIN rol_submenu rs ON rs.rol_id = r.id
                GROUP BY r.id, r.nombre, r.estado_id, r.nivel_id, r.area_id,
                         e.descripcion, nr.nombre, nr.orden, a.nombre, r.created_at
                ORDER BY COALESCE(nr.orden, 9999), a.nombre, r.nombre
            ");

            return DataTables::of($roles)
                ->addColumn('estado_badge', function ($rol) {
                    if ($rol->estado_id == 1) {
                        return '<span class="badge badge-success px-2 py-1" style="border-radius:5px;font-size:.76rem">Activo</span>';
                    }
                    return '<span class="badge badge-danger px-2 py-1" style="border-radius:5px;font-size:.76rem">Inactivo</span>';
                })
                ->addColumn('nivel_badge', function ($rol) {
                    if (!$rol->nivel_nombre) {
                        return '<span class="badge-none">—</span>';
                    }
                    $orden = (int) $rol->nivel_orden;
                    $cls   = 'badge-nivel-' . min($orden, 4);
                    return '<span class="badge ' . $cls . ' px-2 py-1" style="border-radius:5px;font-size:.76rem">'
                         . e($rol->nivel_nombre) . '</span>';
                })
                ->addColumn('area_badge', function ($rol) {
                    if (!$rol->area_nombre) {
                        return '<span class="badge-none">—</span>';
                    }
                    return '<span class="badge badge-area px-2 py-1" style="border-radius:5px;font-size:.76rem">'
                         . e($rol->area_nombre) . '</span>';
                })
                ->addColumn('jerarquia_badge', function ($rol) {
                    $faltaNivel = !$rol->nivel_id;
                    $faltaArea  = !$rol->area_id;

                    if ($rol->estado_id != 1) {
                        return '<span class="text-muted" title="Rol inactivo">—</span>';
                    }

                    if (!$faltaNivel && !$faltaArea) {
                        return '<span class="badge badge-success px-2 py-1" style="border-radius:5px;font-size:.76rem;"><i class="fa fa-check mr-1"></i>Completa</span>';
                    }

                    $falta = [];
                    if ($faltaNivel) $falta[] = 'nivel';
                    if ($faltaArea)  $falta[] = 'área';
                    $tooltip = 'Falta: ' . implode(' y ', $falta);

                    return '<span class="badge badge-warning px-2 py-1" style="border-radius:5px;font-size:.76rem;cursor:pointer;" title="' . $tooltip . '"
                                  onclick="editarRol(' . $rol->id . ')" data-toggle="tooltip">
                                <i class="fa fa-exclamation-triangle mr-1"></i>Incompleta
                            </span>';
                })
                ->addColumn('fecha', function ($rol) {
                    return date('d/m/Y', strtotime($rol->created_at));
                })
                ->addColumn('opciones', function ($rol) {
                    $id        = $rol->id;
                    $esActivo  = $rol->estado_id == 1;

                    $itemEstado = $esActivo
                        ? '<a class="dropdown-item text-warning" href="#" onclick="event.preventDefault();cambiarEstadoRol(' . $id . ',1)">
                               <i class="fa fa-ban fa-fw mr-2"></i>Desactivar
                           </a>'
                        : '<a class="dropdown-item text-success" href="#" onclick="event.preventDefault();cambiarEstadoRol(' . $id . ',2)">
                               <i class="fa fa-check fa-fw mr-2"></i>Activar
                           </a>';

                    $itemEliminar = '';
                    if ($rol->total_usuarios == 0) {
                        $itemEliminar = '
                           <div class="dropdown-divider"></div>
                           <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault();eliminarRol(' . $id . ')">
                               <i class="fa fa-trash-o fa-fw mr-2"></i>Eliminar
                           </a>';
                    }

                    return '
                        <div class="dropdown rol-dropdown">
                            <button class="btn-rol-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                <a class="dropdown-item" href="#" onclick="event.preventDefault();editarRol(' . $id . ')">
                                    <i class="fa fa-pencil fa-fw mr-2 text-primary"></i>Editar
                                </a>
                                ' . $itemEstado . '
                                ' . $itemEliminar . '
                            </div>
                        </div>';
                })
                ->rawColumns(['estado_badge', 'nivel_badge', 'area_badge', 'jerarquia_badge', 'opciones'])
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
                'nombre'   => 'required|string|max:255|unique:rol,nombre',
                'estado_id'=> 'required|integer|exists:estado,id',
                'nivel_id' => 'nullable|integer|exists:nivel_rol,id',
                'area_id'  => 'nullable|integer|exists:area,id',
            ]);

            DB::beginTransaction();

            $rol = Rol::create([
                'nombre'   => $request->nombre,
                'estado_id'=> $request->estado_id,
                'nivel_id' => $request->nivel_id ?: null,
                'area_id'  => $request->area_id  ?: null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol creado correctamente',
                'data'    => $rol
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
                'nombre'   => 'required|string|max:255|unique:rol,nombre,' . $id,
                'estado_id'=> 'required|integer|exists:estado,id',
                'nivel_id' => 'nullable|integer|exists:nivel_rol,id',
                'area_id'  => 'nullable|integer|exists:area,id',
            ]);

            DB::beginTransaction();

            $rol = Rol::findOrFail($id);

            $rol->update([
                'nombre'   => $request->nombre,
                'estado_id'=> $request->estado_id,
                'nivel_id' => $request->nivel_id ?: null,
                'area_id'  => $request->area_id  ?: null,
            ]);

            // Procesar cambios de usuarios
            if ($request->has('usuarios_agregar') && is_array($request->usuarios_agregar)) {
                foreach ($request->usuarios_agregar as $usuarioId) {
                    DB::table('users')->where('id', $usuarioId)->update(['rol_id' => $id]);
                }
            }

            if ($request->has('usuarios_quitar') && is_array($request->usuarios_quitar)) {
                foreach ($request->usuarios_quitar as $usuarioId) {
                    DB::table('users')->where('id', $usuarioId)->where('rol_id', $id)->update(['rol_id' => null]);
                }
            }

            // Procesar cambios de permisos
            if ($request->has('permisos_agregar') && is_array($request->permisos_agregar)) {
                foreach ($request->permisos_agregar as $submenuId) {
                    DB::table('rol_submenu')->insertOrIgnore([
                        'rol_id'      => $id,
                        'sub_menu_id' => $submenuId,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            if ($request->has('permisos_quitar') && is_array($request->permisos_quitar)) {
                foreach ($request->permisos_quitar as $submenuId) {
                    DB::table('rol_submenu')
                        ->where('rol_id', $id)
                        ->where('sub_menu_id', $submenuId)
                        ->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Rol actualizado correctamente',
                'data'    => $rol
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
        try {
            $request->validate([
                'usuario_id' => 'required|integer|exists:users,id'
            ]);

            DB::beginTransaction();

            $usuario   = DB::table('users')->where('id', $request->usuario_id)->first();
            $rolAnterior = $usuario->rol_id;

            DB::table('users')
                ->where('id', $request->usuario_id)
                ->update(['rol_id' => $rolId]);

            DB::commit();

            $mensaje = $rolAnterior
                ? 'Usuario agregado correctamente. Rol anterior actualizado.'
                : 'Usuario agregado correctamente al rol.';

            return response()->json([
                'success' => true,
                'mensaje' => $mensaje
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al agregar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function quitarUsuarioDelRol(Request $request, $rolId)
    {
        try {
            $request->validate([
                'usuario_id' => 'required|integer|exists:users,id'
            ]);

            DB::beginTransaction();

            DB::table('users')
                ->where('id', $request->usuario_id)
                ->where('rol_id', $rolId)
                ->update(['rol_id' => null]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Usuario removido del rol correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al quitar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarTodosUsuarios()
    {
        try {
            $usuarios = DB::table('users')
                ->select('id', 'name', 'email', 'rol_id')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $usuarios
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerRolAnteriorUsuario($usuarioId)
    {
        try {
            $usuario = DB::table('users')
                ->leftJoin('rol', 'users.rol_id', '=', 'rol.id')
                ->where('users.id', $usuarioId)
                ->select('users.rol_id as rol_anterior_id', 'rol.nombre as rol_anterior_nombre')
                ->first();

            return response()->json([
                'success'             => true,
                'rol_anterior_id'     => $usuario->rol_anterior_id,
                'rol_anterior_nombre' => $usuario->rol_anterior_nombre
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener rol anterior: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPermisosDelRol($rolId)
    {
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

            return response()->json([
                'success' => true,
                'data'    => $permisos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarTodosSubmenus()
    {
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

            return response()->json([
                'success' => true,
                'data'    => $submenus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar submenus: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── Catálogos: Niveles y Áreas ───────────────────────────────────────

    public function listarNiveles()
    {
        try {
            $niveles = NivelRol::activos()->get(['id', 'nombre', 'descripcion', 'orden']);

            return response()->json([
                'success' => true,
                'data'    => $niveles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarAreas()
    {
        try {
            $areas = Area::activas()->get(['id', 'nombre', 'descripcion']);

            return response()->json([
                'success' => true,
                'data'    => $areas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al listar áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar reporte de accesos en Excel
     */
    public function descargarReporteAccesos()
    {
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(new ReporteAccesosRolExport(), "reporte_accesos_roles_{$fecha}.xlsx");
    }

    /**
     * Reporte de usuarios activos por rol (JSON)
     */
    public function reporteUsuariosPorRol()
    {
        try {
            $rows = DB::table('users as u')
                ->join('rol as r', 'r.id', '=', 'u.rol_id')
                ->where('u.estado_id', 1)
                ->where('r.estado_id', 1)
                ->select([
                    'r.id as rol_id',
                    'r.nombre as rol_nombre',
                    'u.id as usuario_id',
                    'u.name as usuario_nombre',
                    'u.email',
                    'u.identidad',
                    'u.telefono',
                    'u.created_at',
                ])
                ->orderBy('r.nombre')
                ->orderBy('u.name')
                ->get();

            return response()->json(['success' => true, 'data' => $rows], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar reporte de usuarios por rol en Excel
     */
    public function descargarUsuariosPorRol()
    {
        $fecha = now()->format('Y-m-d_His');
        return Excel::download(new ReporteUsuariosPorRolExport(), "reporte_usuarios_por_rol_{$fecha}.xlsx");
    }

    /**
     * Reporte de todos los accesos disponibles por rol (JSON)
     */
    public function reporteAccesos()
    {
        try {
            $rows = DB::select("
                SELECT
                    r.id           AS rol_id,
                    r.nombre       AS rol_nombre,
                    m.id           AS menu_id,
                    m.nombre_menu,
                    m.icon         AS menu_icon,
                    m.orden        AS menu_orden,
                    sm.id          AS submenu_id,
                    sm.nombre      AS submenu_nombre,
                    sm.url         AS submenu_url,
                    sm.orden       AS submenu_orden
                FROM rol r
                LEFT JOIN rol_submenu rs ON rs.rol_id    = r.id
                LEFT JOIN sub_menu    sm ON sm.id        = rs.sub_menu_id AND sm.estado_id = 1
                LEFT JOIN menu        m  ON m.id         = sm.menu_id     AND m.estado_id  = 1
                WHERE r.estado_id = 1
                ORDER BY r.nombre, m.orden, m.nombre_menu, sm.orden, sm.nombre
            ");

            return response()->json(['success' => true, 'data' => $rows], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}

