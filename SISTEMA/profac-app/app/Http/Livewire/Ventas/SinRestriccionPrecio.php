<?php

namespace App\Http\Livewire\Ventas;

use Livewire\Component;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DataTables;
use Auth;
use Validator;
use PDF;
use Luecano\NumeroALetras\NumeroALetras;

use App\Models\ModelFactura;
use App\Models\ModelCAI;
use App\Models\ModelRecibirBodega;
use App\Models\ModelVentaProducto;
use App\Models\ModelLogTranslados;
use App\Models\ModelParametro;
use App\Models\ModelLista;
use App\Models\ModelCliente;
use App\Models\logCredito;
use App\Models\User;
use App\Models\ModelCodigoAutorizacion;
use App\Models\ConfiguracionCodigoAutorizacion;
use Mail;


class SinRestriccionPrecio extends Component
{
    // Nota: Este componente solo se usa como controlador API.
    // El render() no se invoca desde ninguna ruta de página.
    public function render()
    {
        return view('livewire.ventas.facturacion-unificada');
    }

    public function listarClientes(Request $request)
    {
        try {


                $listaClientes = DB::SELECT("
                select
                    id,
                    nombre as text
                from cliente
                    where estado_cliente_id = 1
                    and  (id LIKE '%" . $request->search . "%' or nombre Like '%" . $request->search . "%') limit 15
                        ");

            return response()->json([
                "results" => $listaClientes,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e
            ], 402);
        }
    }

    public function enviarCodigo(Request $request){

        $config   = ConfiguracionCodigoAutorizacion::obtener();
        $codigo   = rand(1000, 9999);
        $flujoId  = $request->input('flujo_id') ?: null;

        $autorizacion = new ModelCodigoAutorizacion;
        $autorizacion->codigo           = $codigo;
        $autorizacion->users_id         = Auth::user()->id;
        $autorizacion->estado_id        = 1;
        $autorizacion->flujo_id         = $flujoId ? (int) $flujoId : null;
        $autorizacion->tipo_tramite     = 'facturacion';
        $autorizacion->estado_codigo_id = 1; // Pendiente
        $autorizacion->fecha_expiracion = $config->expiracion_activa
            ? now()->addMinutes($config->tiempo_expiracion_minutos)
            : null;
        $autorizacion->save();

        $productos    = $request->input('productos', []);
        $usuario      = Auth::user()->name ?? 'N/A';
        $flujoId      = $request->input('flujo_id', '');
        $numeroVenta  = $request->input('numero_venta', '');

        $viewData = [
            'codigo'       => $codigo,
            'productos'    => $productos,
            'usuario'      => $usuario,
            'flujoId'      => $flujoId,
            'numeroVenta'  => $numeroVenta,
        ];

        // Preview del correo en el log (para debug)
        try {
            $emailHtml = view('email/solicitud', $viewData)->render();
            \Illuminate\Support\Facades\Log::info("=== EMAIL PREVIEW solicitud SR ===\n" . strip_tags($emailHtml, '<table><tr><td><th><b><strong>'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo previsualizar email: ' . $e->getMessage());
        }

        $subject = "Solicitud de autorización SR";
        $for = ['autorizaciones@distribucionesvalencia.hn'];

        Mail::send('email/solicitud', $viewData, function($msj) use($subject,$for){
            $msj->from(env('MAIL_FROM_ADRESS'),"Soporte Técnico Distribuciones Valencia ");
            $msj->subject($subject);
            $msj->to($for);
        });
        return response()->json(["message"=>"exito"],200);

    }

    public function verificarCodigo(Request $request){

        $flujoId      = $request->input('flujo_id') ? (int) $request->input('flujo_id') : null;
        $tipoTramite  = $request->input('tipo_tramite', 'facturacion');

        $autorizacion = ModelCodigoAutorizacion::where('estado_id', 1)
            ->where('estado_codigo_id', 1) // Pendiente
            ->where('codigo', (int) $request->codigo)
            ->first();

        if (!$autorizacion || !$autorizacion->esValido($flujoId, $tipoTramite)) {
            return response()->json([
                'message'        => 'El código de autorización no es válido o ha expirado.',
                'estado'         => 2,
                'idAutorizacion' => '',
            ], 200);
        }

        return response()->json([
            'message'        => 'valor correcto',
            'estado'         => 1,
            'idAutorizacion' => $autorizacion->id,
        ], 200);

    }

    public function desactivarCodigo(Request $request){
        $autorizacion = ModelCodigoAutorizacion::find($request->idAutorizacion);
        if ($autorizacion) {
            $autorizacion->marcarUtilizado();
        }

        return response()->json(['message' => 'exito'], 200);

    }
}
