<!DOCTYPE html>
<html>

<head>


        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        .color-red {
            color: red;
        }

        p {
            font-size: 12px;
        }

        @page {
            margin-top: 90px;
            margin-bottom: 30px;
        }

        body {
            margin: -45px;
            padding: 0px;
            /* background-image: url('img/membrete/membrete2.jpg'); */

            background-size: 200% 200%;
            background-size: cover;

            width: 45rem;

        }

        table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        border: 1px solid #ddd;
        }
        th, td {
        text-align: left;
        padding: 2px;
        word-wrap: break-word;
        }

        thead {
            background-color: #f2f2f2
        }

        /* tr:nth-child(even){background-color: #f2f2f2} */

        .letra {
            font-weight: 800;


        }





    </style>
    <title>Comprobante de Entrega</title>
</head>

<body>

@php
    $contadorFilas = 0;
@endphp


    <div class="pruebaFondo">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem"
        style="margin-left:3%; margin-top:25px; position:absolute;"
         alt="">
        <div class="card border border-dark" style="margin-left:44px;  margin-top:150px; width:45rem; height:5.5rem;">
            <div class="card-header">
                <b>Comprobante de Entrega No. {{$datos->numero_comprovante}}</b>
                <p style="position:absolute;left:630px; font-size:15px; "><b>*Original*</b></p>
            </div>
            {{--  <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:50px;"><b>Número de Factura: {{$datos->cai}}</b></p>
            </div>  --}}
        </div>

        <div class="card border border-dark"   style="margin-left:44px; margin-top:10px; width:45rem; height:6.5rem;">
            <div class="card-body" >
                <p class="card-text" style="position:absolute;left:20px;  top:10px; "><b>Cliente:</b> {{$datos->nombre_cliente}}</p>
                <p class="card-text" style="position:absolute;left:20px;  top:29px;font-size: 11px; max-width:500px">
                    <b>Dirección:</b> {{ $datos->direccion }}
                </p>
                <p class="card-text" style="position:absolute;left:20px;  top:60px; "><b>Correo:</b> {{$datos->correo}}</p>






                <p class="card-text" style="position:absolute;left:520px;  top:10px;"><b>Fecha:</b> {{$datos->fecha}}  </p>
                <p class="card-text" style="position:absolute;left:520px;  top:25px;"><b>Hora:</b> {{$datos->hora}}</p>
                <p class="card-text" style="position:absolute;left:520px;  top:57px;"><b>RTN:</b> {{$datos->RTN}}</p>

                </p>



                <p class="card-text" style="position:absolute;left:270px;  top:60px;"><b>Teléfono:</b> {{$datos->telefono_empresa}}
                </p>
            </div>
        </div>

        <div class="card border border-dark" style="position: relative; margin-left:44px; margin-top:10px; width:45rem;">
            <div >


                <table  class="" style="font-size: 11px; table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                          <th style="width:6%">Código</th>
                          <th style="width:36%">Descripción</th>
                          <th style="width:8%">Medida</th>
                          <th style="width:11%">Bodega</th>
                          <th style="width:7%">Seccion</th>
                          <th style="width:9%; white-space:nowrap;">Cantidad</th>
                          <th style="width:11%">Precio </th>
                          <th style="width:12%">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                        <tr>
                            <td>{{$producto->producto_id}}</td>
                            <td style="word-wrap:break-word;">{{$producto->nombre}}</td>
                            <td>{{$producto->unidad}}</td>
                            <td>{{$producto->bodega}}</td>
                            <td>{{$producto->seccion}}</td>
                            <td>{{$producto->cantidad}}</td>
                            <td>{{$producto->precio}}</td>
                            <td>{{$producto->sub_total}}</td>

                        </tr>

                        @php
                        $contadorFilas++;
                        @endphp
{{--
                        @if( fmod($contadorFilas,24)==0.0 )

                        <div style="page-break-before: always;"></div>

                        @endif --}}


                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-left:44px; margin-top:10px; width:45rem;">
            <table style="width:100%; border:none; border-collapse:collapse;">
                <tr>
                    <td style="width:58%; border:none; vertical-align:top; padding:0 8px 0 0;">
                        <div class="card border border-dark" style="height:auto; min-height:12rem;">
                            <div class="card-body" style="padding:8px 10px;">
                                <p class="card-text" style="font-size:12px; margin:0 0 4px 0;"><b>Registrado por:</b> {{$datos->registrado_por}}</p>
                                @if($datos->estado_factura_id)
                                    <p class="card-text" style="font-size:10px; margin:0 0 6px 0;">
                                        @if($datos->estado_factura_id==1)
                                            N{{$datos->numero_factura}}-CF11
                                        @else
                                            N{{$datos->numero_factura}}-CF12
                                        @endif
                                    </p>
                                @endif
                                <hr style="margin:4px 0; border-top:1px solid #999;">
                                <p class="card-text" style="font-size:11px; margin:0 0 6px 0;"><b>Nota:</b> {{ $datos->comentario }}</p>
                                <p class="card-text" style="font-size:11px; margin:0;">
                                    @if($flagCentavos == false)
                                        <b>"{{$numeroLetras." CON CERO CENTAVOS"}}"</b>
                                    @else
                                        <b>"{{$numeroLetras }}"</b>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td style="width:42%; border:none; vertical-align:top; padding:0 0 0 8px;">
                        <div class="card border border-dark" style="height:auto; min-height:12rem;">
                            <div class="card-body" style="padding:6px 10px;">
                                <table style="width:100%; border:none; border-collapse:collapse; font-size:11px;">
                                    <tr><td style="border:none; padding:1px 0;">Importe Exonerado:</td><td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Importe Gravado 15%:</td><td style="border:none; padding:1px 0; text-align:right;">L. {{$importes->sub_total_grabado}}</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Importe Gravado 18%:</td><td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Importe Exento:</td><td style="border:none; padding:1px 0; text-align:right;">L. {{$importes->sub_total_excento}}</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Desc. y Rebajas: {{$importes->porc_descuento}}%</td><td style="border:none; padding:1px 0; text-align:right;">L. {{$importes->monto_descuento}}</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Sub Total:</td><td style="border:none; padding:1px 0; text-align:right;">L. {{$importes->sub_total}}</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Impuesto sobre venta 15%:</td><td style="border:none; padding:1px 0; text-align:right;">L. {{$importes->isv}}</td></tr>
                                    <tr><td style="border:none; padding:1px 0;">Impuesto sobre venta 18%:</td><td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td></tr>
                                    <tr><td style="border:none; padding:3px 0 1px; border-top:1px solid #999;"><b>Total a Pagar:</b></td><td style="border:none; padding:3px 0 1px; text-align:right; border-top:1px solid #999;"><b>L. {{$importes->total}}</b></td></tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p style="margin:6px 44px 0; font-size:9px; text-align:right;">Original: Cliente, Copia obligado tributario emisor.</p>

        <div style="margin-left:44px; margin-top:14px; width:45rem;">
            <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                <tr>
                    <td style="width:50%; border:none; vertical-align:top; padding:0 20px 0 0;">
                        <p style="margin:0; border-top:1px solid #000; padding-top:3px;">Cliente: {{$datos->nombre_cliente}}</p>
                        <p style="margin:4px 0 0;">Recibido por: _______________________</p>
                        <p style="margin:4px 0 0;">DNI: ________________________________</p>
                        <p style="margin:4px 0 0;">Cargo: ______________________________</p>
                        <p style="margin:4px 0 0;">Telefono: ___________________________</p>
                    </td>
                    <td style="width:50%; border:none; vertical-align:top; padding:0 0 0 20px; text-align:center;">
                        <p style="margin:0; border-top:1px solid #000; padding-top:3px; text-align:center;">DISTRIBUCIONES VALENCIA</p>
                    </td>
                </tr>
            </table>
        </div>

        {{-- @if($datosEntrega->estadoVale==2)
        <div>
            <p class="" style="position:absolute; margin-top:{{$altura2 + 85}}px;  left:140px;   font-size:50px;">--VALE ANULADO--</p>
        </div>
        @elseif($datosEntrega->estadoVale==5)
        <div>
            <p class="" style="position:absolute; margin-top:{{$altura2 + 85}}px;  left:140px;   font-size:50px;">--VALE ELIMINADO--</p>
        </div>
        @endif --}}






    </div>




    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>
