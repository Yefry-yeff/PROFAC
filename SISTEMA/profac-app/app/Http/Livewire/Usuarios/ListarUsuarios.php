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
            users.created_at as fecha_registro

            FROM users inner join rol
            on users.rol_id = rol.id
            cross join (select @i := 0) r


            ");

            return Datatables::of($listaUsuarios)
            ->addColumn('opciones', function ($nota) {

                return

                '

                <div class="btn-group">
                <button data-toggle="dropdown" class="btn btn-warning dropdown-toggle" aria-expanded="false">Ver
                    más</button>
                <ul class="dropdown-menu" x-placement="bottom-start"
                    style="position: absolute; top: 33px; left: 0px; will-change: top, left;">
                    <li><a class="dropdown-item" href="#" "> <i class="fa fa-pencil m-r-5 text-warning"></i>
                            Editar Rol</a></li>
                    <li><a class="dropdown-item" href="#" "> <i class="fa fa-times text-danger" aria-hidden="true"></i>
                            Dar de baja </a></li>

                </ul>
            </div>


                ';
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

    public function editarRol(Request $request){


    }
    public function infoUsuario(){


    }
}
