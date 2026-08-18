<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ListarUsuarios extends Component
{
    public function render()
    {
        return view('livewire.usuarios.listar-usuarios');
    }

    public function listarUsuarios(){

        try {

            $listaUsuarios = DB::SELECT("

            SELECT
            @i := @i + 1 as contador,
            users.id as id,
            name as nombre,
            telefono,
            email,
            identidad,
            fecha_nacimiento,
            rol.nombre as tipo_usuario,
            estado.id as estado_id,
            estado.descripcion as estado,
            users.created_at as fecha_registro

            FROM users 
            INNER JOIN rol ON users.rol_id = rol.id
            INNER JOIN estado ON users.estado_id = estado.id
            CROSS JOIN (SELECT @i := 0) r


            ");

            return DataTables::of($listaUsuarios)
            ->addColumn('opciones', function ($nota) {
                $opciones = '
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start"
                            style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" onclick="infoUsuario('.$nota->id.')"> 
                                <i class="fa fa-pencil m-r-5 text-warning"></i>Editar Usuario</a></li>
                            <li><a class="dropdown-item" onclick="abrirModalContrasena('.$nota->id.')"> 
                                <i class="fa fa-key m-r-5 text-primary"></i>Contraseña</a></li>';
                
                // Mostrar opción según el estado
                if ($nota->estado_id == 1) {
                    // Usuario activo - mostrar opción de dar de baja
                    $opciones .= '
                            <li><a class="dropdown-item" onclick="baja('.$nota->id.')"> 
                                <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                Dar de baja</a></li>';
                } else {
                    // Usuario inactivo - mostrar opción de activar
                    $opciones .= '
                            <li><a class="dropdown-item" onclick="activar('.$nota->id.')"> 
                                <i class="fa fa-check text-success" aria-hidden="true"></i>
                                Activar</a></li>';
                }
                
                $opciones .= '
                        </ul>
                    </div>
                ';
                
                return $opciones;
            })->rawColumns(['opciones'])

            ->make(true);

        } catch (QueryException $e) {

            return response()->json([
                "message" => "Ha ocurrido un error al listar los usuarios.",
                "error" => $e
            ]);
        }

    }

    public function guardarUsuarios(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'nombre_usuario' => 'required',
                'email_user'     => 'required|email|unique:users,email',
                'pass_user'      => 'required|min:8',
                'confirmar_pass' => 'required|same:pass_user',
                'rol_user'       => 'required',
            ], [
                'nombre_usuario.required' => 'El nombre es requerido',
                'email_user.required'     => 'El correo es requerido',
                'email_user.email'        => 'Ingrese un correo válido',
                'email_user.unique'       => 'Ya existe un usuario con este correo. Si está inactivo, use la opción de activarlo en lugar de crear uno nuevo.',
                'pass_user.required'      => 'La contraseña es requerida',
                'pass_user.min'           => 'La contraseña debe tener al menos 8 caracteres',
                'confirmar_pass.required' => 'Debe confirmar la contraseña',
                'confirmar_pass.same'     => 'Las contraseñas no coinciden',
                'rol_user.required'       => 'El rol de acceso es requerido',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'icon'   => 'error',
                    'title'  => 'Error',
                    'text'   => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $usuario = new usuario;
            $usuario->identidad         = $request->identidad_user ?? null;
            $usuario->name              = $request->nombre_usuario;
            $usuario->email             = $request->email_user;
            $usuario->password          = Hash::make($request->pass_user);
            $usuario->telefono          = $request->telefono_user ?? null;
            $usuario->rol_id            = $request->rol_user;
            $usuario->estado_id         = 1;
            $usuario->must_change_password = 1; // Obligar cambio en primer ingreso
            $usuario->save();

            return response()->json([
                'icon'  => 'success',
                'title' => 'Exito!',
                'text'  => 'Usuario creado con éxito. El usuario deberá cambiar su contraseña al iniciar sesión.'
            ], 200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }

    public function cambiarContrasenaUsuario(Request $request){
        try {
            if (empty($request->nueva_contrasena) || strlen($request->nueva_contrasena) < 8) {
                return response()->json(['icon'=>'error','title'=>'Error!','text'=>'La contraseña debe tener al menos 8 caracteres.'], 422);
            }
            if ($request->nueva_contrasena !== $request->confirmar_contrasena) {
                return response()->json(['icon'=>'error','title'=>'Error!','text'=>'Las contraseñas no coinciden.'], 422);
            }

            $usuario = usuario::findOrFail($request->id_usuario);
            $usuario->password             = Hash::make($request->nueva_contrasena);
            $usuario->must_change_password = 1;
            $usuario->save();

            return response()->json(['icon'=>'success','title'=>'Éxito!','text'=>'Contraseña actualizada. El usuario deberá cambiarla al iniciar sesión.'], 200);
        } catch (\Exception $e) {
            return response()->json(['icon'=>'error','title'=>'Error!','text'=>$e->getMessage()], 500);
        }
    }

    public function actualizarUsuarios(Request $request){
        try {
            // Validar que las contraseñas coincidan si se proporciona una nueva
            if (!empty($request->nueva_contrasena)) {
                if ($request->nueva_contrasena !== $request->confirmar_contrasena) {
                    return response()->json([
                        'icon'=>'error',
                        'title'=>'Error!',
                        'text'=>'Las contraseñas no coinciden.'
                    ], 422);
                }
                
                if (strlen($request->nueva_contrasena) < 8) {
                    return response()->json([
                        'icon'=>'error',
                        'title'=>'Error!',
                        'text'=>'La contraseña debe tener al menos 8 caracteres.'
                    ], 422);
                }
            }

            $usuario = usuario::find($request->id_usuario);
            $usuario->identidad        = $request->identidad_usuario ?? null;
            $usuario->name             = $request->nombre_usuario;
            $usuario->rol_id           = $request->seleccionarRol;
            $usuario->email            = $request->correo_usuario;
            $usuario->fecha_nacimiento = $request->fenacimiento_usuario;
            $usuario->telefono         = $request->telefono_usuario ?? null;
            
            // Actualizar contraseña solo si se proporciona una nueva
            if (!empty($request->nueva_contrasena)) {
                $usuario->password             = Hash::make($request->nueva_contrasena);
                $usuario->must_change_password = 1; // Forzar cambio en próximo ingreso
            }
            
            $usuario->save();

            $mensaje = 'Usuario actualizado con éxito.';
            if (!empty($request->nueva_contrasena)) {
                $mensaje = 'Usuario y contraseña actualizados. El usuario deberá cambiar su contraseña al iniciar sesión.';
            }

            return response()->json([
                 'icon'  => 'success',
                 'title' => 'Exito!',
                 'text'  => $mensaje
            ], 200);

        } catch (QueryException $e) {

        return response()->json([
         'icon'=>'error',
         'title'=>'Error!',
         'text'=>'Ha ocurrido un error, intente de nuevo.',
         'message' => 'Ha ocurrido un error',
         'error' => $e
        ],402);
        }
    }


    public function selectRoles($idRol){

        $infoRoles = DB::SELECT("

            SELECT
                id, nombre
            FROM rol
            WHERE id not in (".$idRol.")");
       return $infoRoles;
    }
    public function infoUsuario($idUsuario){

        //dd($idUsuario);
        $infoUsuario = DB::SELECT("

             SELECT
                a.id,
                a.name,
                a.identidad,
                a.email,
                a.telefono,
                a.rol_id,
                b.nombre as rol,
                a.fecha_nacimiento
            FROM users as a
            left join rol b on b.id = a.rol_id
            WHERE a.id = ".$idUsuario);
            return $infoUsuario;

    }

    public function baja($idUsuario){
        $usuario = usuario::find($idUsuario);
        // Cambiar el estado a 2 (Inactivo)
        $usuario->estado_id = 2;
        $usuario->save();

        return response()->json([
             'icon'=>'success',
             'title'=>'Exito!',
             'text'=>'Usuario dado de baja con éxito.'
        ],200);
    }
    
    /**
     * Método para activar un usuario
     */
    public function activar($idUsuario){
        $usuario = usuario::find($idUsuario);
        // Cambiar el estado a 1 (Activo)
        $usuario->estado_id = 1;
        $usuario->save();

        return response()->json([
             'icon'=>'success',
             'title'=>'Exito!',
             'text'=>'Usuario activado con éxito.'
        ],200);
    }

    public function getAllRoles(){
        $roles = DB::SELECT("SELECT id, nombre FROM rol");
        return $roles;
    }

    // ==================================================================
    // ROLES ADICIONALES (multi-rol) — NO afecta el rol principal (rol_id)
    // ==================================================================

    /**
     * Lista los roles adicionales (no el principal) ya asignados a un usuario.
     */
    public function obtenerRolesAdicionales($idUsuario)
    {
        $roles = DB::table('usuario_rol as ur')
            ->join('rol as r', 'r.id', '=', 'ur.rol_id')
            ->where('ur.usuario_id', $idUsuario)
            ->select('r.id', 'r.nombre')
            ->orderBy('r.nombre')
            ->get();

        return response()->json(['data' => $roles]);
    }

    /**
     * Búsqueda (select2 ajax) de roles que se puedan agregar como
     * adicionales a un usuario: excluye su rol principal y los que ya
     * tiene asignados como adicionales.
     */
    public function buscarRolesAdicionalesDisponibles(Request $request, $idUsuario)
    {
        $texto = trim((string) $request->get('q', ''));

        $usuario = usuario::find($idUsuario);
        if (!$usuario) {
            return response()->json(['results' => []]);
        }

        $yaAsignados = DB::table('usuario_rol')
            ->where('usuario_id', $idUsuario)
            ->pluck('rol_id')
            ->toArray();

        $excluir = array_merge($yaAsignados, [$usuario->rol_id]);

        $query = DB::table('rol')->where('estado_id', 1);
        if (!empty($excluir)) {
            $query->whereNotIn('id', $excluir);
        }
        if ($texto !== '') {
            $query->where('nombre', 'like', '%' . $texto . '%');
        }

        $roles = $query->orderBy('nombre')->limit(20)->get(['id', 'nombre']);

        return response()->json([
            'results' => $roles->map(function ($r) {
                return ['id' => $r->id, 'text' => $r->nombre];
            }),
        ]);
    }

    /**
     * Agrega un rol adicional a un usuario (no modifica su rol principal).
     */
    public function agregarRolAdicional(Request $request, $idUsuario)
    {
        $request->validate(['rol_id' => 'required|integer']);

        $usuario = usuario::find($idUsuario);
        if (!$usuario) {
            return response()->json(['icon' => 'error', 'title' => 'Error', 'text' => 'Usuario no encontrado.'], 404);
        }

        if ((int) $usuario->rol_id === (int) $request->rol_id) {
            return response()->json([
                'icon' => 'error', 'title' => 'Error',
                'text' => 'Ese rol ya es el rol principal del usuario.',
            ], 422);
        }

        DB::table('usuario_rol')->insertOrIgnore([
            'usuario_id' => $idUsuario,
            'rol_id'     => $request->rol_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['icon' => 'success', 'title' => 'Éxito!', 'text' => 'Rol adicional agregado correctamente.']);
    }

    /**
     * Quita un rol adicional de un usuario (no modifica su rol principal).
     */
    public function quitarRolAdicional(Request $request, $idUsuario)
    {
        $request->validate(['rol_id' => 'required|integer']);

        DB::table('usuario_rol')
            ->where('usuario_id', $idUsuario)
            ->where('rol_id', $request->rol_id)
            ->delete();

        return response()->json(['icon' => 'success', 'title' => 'Éxito!', 'text' => 'Rol adicional removido correctamente.']);
    }

    public function forzarCambioContrasena(Request $request)
    {
        $request->validate([
            'nueva_contrasena'    => 'required|min:8',
            'confirmar_contrasena' => 'required|same:nueva_contrasena',
        ], [
            'nueva_contrasena.required'     => 'La contraseña es requerida',
            'nueva_contrasena.min'          => 'La contraseña debe tener al menos 8 caracteres',
            'confirmar_contrasena.required' => 'Debe confirmar la contraseña',
            'confirmar_contrasena.same'     => 'Las contraseñas no coinciden',
        ]);

        $usuario = usuario::find(Auth::id());
        $usuario->password             = Hash::make($request->nueva_contrasena);
        $usuario->must_change_password = 0;
        $usuario->save();

        return redirect('/dashboard')->with('success', 'Contraseña actualizada exitosamente.');
    }
}
