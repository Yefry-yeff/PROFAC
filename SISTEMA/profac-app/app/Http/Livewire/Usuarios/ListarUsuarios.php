<?php

namespace App\Http\Livewire\Usuarios;

use Livewire\Component;


use Illuminate\Http\Request;
use Auth;
use Illuminate\Database\QueryException;
use Throwable;
use App\Models\usuario;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use DataTables;



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

            return Datatables::of($listaUsuarios)
            ->addColumn('opciones', function ($nota) {
                $opciones = '
                    <div class="btn-group">
                        <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver más</button>
                        <ul class="dropdown-menu" x-placement="bottom-start"
                            style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                            <li><a class="dropdown-item" onclick="infoUsuario('.$nota->id.')"> 
                                <i class="fa fa-pencil m-r-5 text-warning"></i>Editar Rol</a></li>';
                
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
                'identidad_user' => 'required',
                'nombre_usuario' => 'required',
                'email_user' => 'required',
                'pass_user' => 'required',
                'rol_user' => 'required',
            ], [
                'identidad_user' => 'La identidad es requerida',
                'nombre_usuario' => 'El nombre es requerido',
                'email_user' => 'Correo requerido',
                'pass_user' => 'contraseña requerida',
                'rol_user' => 'Rol de acceso requerido',

            ]);


            if ($validator->fails()) {
                return response()->json([
                    'icon'=>'error',
                    'title'=>'Error',
                    'text'=>'Ha ocurrido un error, todos los campos son obligatorios.',
                    'errors' => $validator->errors()
                ], 402);
            }



            $usuario = new usuario;
            $usuario->identidad = $request->identidad_user;
            $usuario->name = $request->nombre_usuario;
            $usuario->email = $request->email_user;
            $usuario->password = $request->pass_user;
            $usuario->rol_id = $request->rol_user;
            $usuario->estado_id = 1; // Por defecto, usuario activo
            $usuario->save();




            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Usuario creado con exito.'
            ],200);

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

    public function actualizarUsuarios(Request $request){
        try {
            //dd($request);


            $usuario = usuario::find($request->id_usuario);
            $usuario->identidad = $request->identidad_usuario;
            $usuario->name = $request->nombre_usuario;
            $usuario->rol_id = $request->seleccionarRol;
            $usuario->email = $request->correo_usuario;
            $usuario->fecha_nacimiento = $request->fenacimiento_usuario;
            $usuario->save();

            return response()->json([
                 'icon'=>'success',
                 'title'=>'Exito!',
                 'text'=>'Usuario Actualizaron con exito.'
            ],200);

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
}
