<!DOCTYPE html>
<html>

<head>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .color-red {
            color: red;
        }

        p {
            font-size: 12px;
        }

        body {
            margin: -45px;
            padding: 0px;
            /* background-image: url('img/membrete/membrete2.jpg'); */

            background-size: 200% 200%;
            background-size: cover;

            width: 115% !important;

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
    $altura =20;
    $altura2 = 320;
    $contadorFilas = 0;
@endphp


    <div class="pruebaFondo">
        <img src="img/membrete/Logo3.png" width="800rem"
        style="margin-left:3%; margin-top:25px; position:absolute;"
         alt="">
        <div class="card border border-dark" style="margin-left:44px;  margin-top:150px; width:45rem; height:5.5rem;">
            <div class="card-header">
                <b>Comprobante de Entrega No. {{$datos->numero_comprovante}}</b>
                <p style="position:absolute;left:630px; font-size:15px; "><b>*Copia*</b></p>
            </div>
            {{--  <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:50px;"><b>Número de Factura: {{$datos->cai}}</b></p>
            </div>  --}}
        </div>

        <div class="card border border-dark"   style="margin-left:44px; margin-top:10px; width:45rem; height:6.5rem;">
            <div class="card-body" >
                <p class="card-text" style="position:absolute;left:20px;  top:10px; "><b>Cliente:</b> {{$datos->nombre_cliente}}</p>
                <p class="card-text" style="position:absolute;left:20px;  top:29px; "><b>Dirección:</b> {{$datos->direccion}} </p>

                <p class="card-text" style="position:absolute;left:20px;  top:47px; "><b>Correo:</b> {{$datos->correo}}</p>






                <p class="card-text" style="position:absolute;left:520px;  top:10px;"><b>Fecha:</b> {{$datos->fecha}}  </p>
                <p class="card-text" style="position:absolute;left:520px;  top:25px;"><b>Hora:</b> {{$datos->hora}}</p>
                <p class="card-text" style="position:absolute;left:520px;  top:57px;"><b>RTN:</b> {{$datos->RTN}}</p>

                </p>



                <p class="card-text" style="position:absolute;left:270px;  top:45px;"><b>Teléfono:</b> {{$datos->telefono_empresa}}
                </p>
            </div>
        </div>

        <div class="card border border-dark" style="position: relative; margin-left:44px; margin-top:10px; width:45rem;">
            <div >


                <table  class="" style="font-size: 11px;">
                    <thead>
                        <tr>
                          <th>Código</th>
                          <th>Descripción</th>
                          <th>Medida</th>
                          <th>Cantidad</th>
                          <th>Precio </th>
                          <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                        <tr>
                            <td>{{$producto->producto_id}}</td>
                            <td>{{$producto->nombre}}</td>
                            <td>{{$producto->unidad}}</td>
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
            @if($contadorFilas>4 and $contadorFilas<24)
                @php
                    $altura = 170;
                    $altura2 = 530;
                @endphp
                <div style="page-break-after: always"></div>
            @else
                @php
                $altura = 20;
                @endphp
            @endif



        <div style=" position: relative; margin-left:44px;">
            <div class="card border border-dark" style="position:absolute;left:0px; margin-top:{{$altura}}px;   width:26rem; height:15rem;">
                <div class="card-body">

                    <p class="card-text" style="position:absolute;left:10px;  top:2px; font-size:14px;"><b>Registrado por:</b> {{$datos->registrado_por}} </p>



                        @if($datos->estado_factura_id)

                            @if($datos->estado_factura_id==1)
                            <span style = "font-size: 10px;position:absolute;left:10px">N{{$datos->numero_factura}}-CF11</span></p>
                            @else
                            <span style = "font-size: 10px;position:absolute;left:10px">N{{$datos->numero_factura}}-CF12</span></p>
                            @endif

                        @endif




                    <p class="card-text" style="position:absolute;left:0px;  top:28px; font-size:11px;">
                        ____________________________________________________________________</p>



                        <p class="card-text" style="position:absolute;left:10px;  top:70px; font-size:12px; max-width:420px"><b>Nota: </b>
                            {{ $datos->comentario }}</p>

                    @if($flagCentavos == false)
                    <p class="card-text" style="position:absolute;left:10px;  top:240px; font-size:12px;">"{{$numeroLetras." CON CERO CENTAVOS"}}"</p>

                    @else
                    <p class="card-text" style="position:absolute;left:10px;  top:240px; font-size:12px;">"{{$numeroLetras }}"</p>
                    @endif
                </div>
            </div>

            <div class="card border border-dark"
                style="position:absolute;left:430px; margin-top:{{$altura}}px;   width:18rem; height:15rem;">
                <div class="card-body">
                    <div>
                        <p class="card-text " style="position:absolute; left:10px;  top:10px; font-size:14px;">Importe Exonerado:</p>
                        <p class="card-text" style="position:absolute;  right:10px;  top:10px; font-size:14px;">L. 0.00</p>
                    </div>
                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:28px; font-size:14px;">Importe Gravado 15%: </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:28px; font-size:14px;">L. {{$importes->sub_total_grabado}}</p>
                    </div>
                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:46px; font-size:14px;">Importe Gravado 18%: </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:46px; font-size:14px;">L. 0.00</p>
                    </div>

                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:64px; font-size:14px;">Importe Exento:  </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:64px; font-size:14px;">L. {{$importes->sub_total_excento}}</p>
                    </div>


                    {{-- <p class="card-text" style="position:absolute; left:10px;  top:65px; font-size:16px;">Total Importe:
                    </p>
                    <p class="card-text" style="position:absolute; left:200px;  top:65px; font-size:16px;">1200.00</p> --}}

                    <p class="card-text" style="position:absolute; left:10px;  top:85px; font-size:14px;">Desc. y Rebajas: {{$importes->porc_descuento}}%
                    </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:85px; font-size:14px;">L. {{$importes->monto_descuento}}</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:105px; font-size:14px;">Sub Total:</p>
                    <p class="card-text" style="position:absolute; right:10px;  top:105px; font-size:14px;">L. {{$importes->sub_total}}</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:130px; font-size:14px;">Impuesto sobre
                        venta 15%: </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:130px; font-size:14px;"> L. {{$importes->isv}}</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:148px; font-size:14px;">Impuesto sobre
                        venta 18%: </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:148px; font-size:14px;"> L. 0.00</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:185px; font-size:16px;"><b>Total a
                            Pagar: </b></p>
                    <p class="card-text" style="position:absolute; right:10px;  top:185px; font-size:16px;"><b>L. {{$importes->total}}</b>
                    </p>
                </div>
            </div>

            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:80px;  top:25px; ">{{$datos->nombre_cliente}}</p>
                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
                <p class="card-text" style="position:absolute;left:460px;  top:-60px;">Original: Cliente, Copia obligado tributario emisor. </p>
            </div>



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
