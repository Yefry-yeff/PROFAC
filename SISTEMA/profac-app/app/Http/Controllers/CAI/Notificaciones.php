<?php

namespace App\Http\Controllers\CAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Database\QueryException;
use DataTables;
use App\Models\ModelCAI;
use Mail;
class Notificaciones extends Controller
{
   public function validarAlertaCAI($numeroFinal,$numeroActual, $idParametro){



        // $numDisponibles = $numeroFinal - $numeroActual;

        // $parametrosMail = DB::SELECTONE("
        // SELECT
        // asunto,
        // mensaje,
        // numero_alerta
        // FROM   profac_app.parametros_cai_notificaion
        // WHERE  id = ".$idParametro."
        // ORDER  BY created_at DESC
        // LIMIT  1"
        // );





        // if($numDisponibles <= $parametrosMail->numero_alerta){
        // //   $correosDB = DB::SELECT("select email from users where rol_id = 1 or rol_id = 5");
        //     $correosDB = DB::SELECT("select email from users where id in(5,10,55)");
        //         $correos =[];




        //         foreach ($correosDB as $correo) {
        //             array_push( $correos,$correo->email);
        //         }


        //         $subject = $parametrosMail->asunto;

        //         $for =  $correos;

        //         $result = Mail::send('email/notificacionCAI',['cuerpo' => $parametrosMail->mensaje], function($msj) use($subject,$for){
        //             $msj->from(env('MAIL_FROM_ADRESS'),"Soporte Técnico Distribuciones Valencia ");
        //             $msj->subject($subject);
        //             $msj->to($for);
        //         });



        // }


    return true;
   }




}
