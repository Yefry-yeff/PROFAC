<!DOCTYPE html>
<html>

<head>


        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        @page { margin: 28px; }
        .color-red { color: red; }
        p { font-size: 10px; }
        body { margin: 0; padding: 0; width: 100%; }
        .pruebaFondo { width: 100%; }
        .nota-logo { display:block; width:100%; margin-bottom:12px; }
        .nota-ancho { width:100% !important; margin-left:0 !important; }
        .nota-resumen { position:relative !important; margin-left:0 !important; width:100% !important; min-height:250px; }
        .nota-resumen-notas { left:0 !important; width:57% !important; }
        .nota-resumen-totales { left:auto !important; right:0 !important; width:41% !important; }
        .nota-firmas { left:0 !important; width:100% !important; }

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
    <title>Nota De Credito</title>
</head>

<body>

@php
    $altura =20;
    $altura2 = 320;
    $contadorFilas = 0;
@endphp


    <div class="pruebaFondo">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" class="nota-logo" alt="">

         <b style="display:block; text-align:right; margin-bottom:8px;" >*Original*</b>
        <div class="card border border-dark nota-ancho">
            <div class="card-header">
                <b>Nota de Credito No. {{$cai->nota_credito_cai}}</b>
                <b style="position:absolute;right: 10px" >Factura No. {{$cai->factura}}</b>
            </div>

            <div class="card-body" style="padding:4px 10px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr><td style="border:none; padding:1px 0;"><b>Registro tributario: 08011986138652</b></td><td style="border:none; padding:1px 0; text-align:right;"><b>CAI: {{ $cai->cai }}</b></td></tr>
                    <tr><td style="border:none; padding:1px 0;"><b>Fecha límite de emisión: {{ $cai->fecha_limite_emision }}</b></td><td style="border:none; padding:1px 0; text-align:right;"><b>Rango autorizado: {{ $cai->numero_inicial }} - {{ $cai->numero_final }}</b></td></tr>
                </table>
            </div>
        </div>

        <div class="card border border-dark nota-ancho" style="margin-top:4px;">
            <div class="card-body" style="padding:4px 10px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr><td style="width:58%; vertical-align:top; padding:0; border:none;"><p style="margin:0 0 2px;"><b>Cliente:</b> {{ $cliente->nombre }}</p><p style="margin:0 0 2px;"><b>Dirección:</b> {{ $cliente->direccion }}</p><p style="margin:0 0 2px;"><b>Correo:</b> {{ $cliente->correo }} &nbsp;&nbsp; <b>Teléfono:</b> {{ $cliente->telefono_empresa }}</p><p style="margin:0;"><b>Notas:</b> {{ $notas }}</p></td><td style="width:42%; vertical-align:top; padding:0 0 0 10px; border:none; border-left:1px solid #ccc;"><p style="margin:0 0 2px;"><b>Fecha:</b> {{ $cai->fecha_emision }}</p><p style="margin:0 0 2px;"><b>Hora:</b> {{ $cai->hora }}</p><p style="margin:0 0 2px;"><b>Vence:</b> {{ $cai->fecha_vencimiento }}</p><p style="margin:0;"><b>RTN:</b> {{ $cliente->rtn }}</p></td></tr>
                </table>
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px; margin-top:3px; border-top:1px solid #ccc;"><tr><td style="width:33%; border:none; padding:2px 0 1px;"><b>Correlativo de Ord. exenta</b></td><td style="width:34%; border:none; padding:2px 0 1px; text-align:center;"><b>Constancia de registro exonerado</b></td><td style="width:33%; border:none; padding:2px 0 1px; text-align:right;"><b>Identificativo del registro de la SAG</b></td></tr><tr><td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td><td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td><td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td></tr></table>
            </div>
        </div>

        <div class="card border border-dark nota-ancho" style="position: relative; margin-top:8px; page-break-inside: auto;">
            <div >


                <table  class="" style="font-size: 11px; ">
                    <thead>
                    <tr>
                      <th>Código</th>
                      <th>Descripción</th>
                      <th>Bodega</th>
                      <th>Seccion</th>
                      <th>Medida</th>
                      <th>Precio </th>
                      <th>Cantidad</th>
                      <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                    <tr>
                        <td>{{$producto->codigo}}</td>
                        <td>{{$producto->descripcion}}</td>
                        <td>{{$producto->bodega}}</td>
                        <td>{{$producto->seccion}}</td>
                        <td>{{$producto->medida}}</td>
                        <td>{{$producto->precio}}</td>
                        <td>{{$producto->cantidad}}</td>
                        <td>{{$producto->sub_total}}</td>
                    </tr>
                    @php
                    $contadorFilas++;
                    @endphp

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





        <div class="nota-resumen">
            <div class="card border border-dark nota-resumen-notas" style="position:absolute; margin-top:{{$altura}}px; height:15rem;">
                <div class="card-body">

                    <div style="position:absolute;left:10px;top:8px;width:390px;font-size:14px;line-height:16px;">
                        <b>Vendedor: </b>{{ $cai->name }}
                    </div>

                    @if(!empty($movimientosCredito))
                        <div style="position:absolute;left:10px;top:36px;width:390px;font-size:10px;line-height:14px;">
                            <div style="font-weight:bold;margin-bottom:3px;">Aplicación del crédito:</div>
                            @foreach($movimientosCredito as $movimiento)
                                <div style="margin:0 0 2px 0;">
                                    @if($movimiento->tipo === 'aplicacion')
                                        Aplicado a factura {{ $movimiento->factura }}: L. {{ number_format((float) $movimiento->monto, 2) }}
                                    @else
                                        Reembolso {{ strtolower($movimiento->metodo_reembolso ?: 'registrado') }}: L. {{ number_format((float) $movimiento->monto, 2) }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div style="position:absolute;left:10px;top:210px;font-size:10px;line-height:12px;">
                        @if($cai->estado_factura==1)
                            N{{ $cai->numero_factura }}-CF11
                        @else
                            N{{ $cai->numero_factura }}-CF12
                        @endif
                    </div>

                    @if($flagCentavos == false)
                    <p class="card-text" style="position:absolute;left:35px;  top:240px; font-size:12px;">"{{$numeroLetras." CON CERO CENTAVOS"}}"</p>

                    @else
                    <p class="card-text" style="position:absolute;left:35px;  top:240px; font-size:12px;">"{{$numeroLetras }}"</p>
                    @endif

                </div>
            </div>

            <div class="card border border-dark nota-resumen-totales"
                style="position:absolute; margin-top:{{$altura}}px; height:15rem;">
                <div class="card-body">
                    <div>
                        <p class="card-text " style="position:absolute; left:10px;  top:10px; font-size:14px;">Importe
                            exonerado:</p>
                        <p class="card-text" style="position:absolute;  right:10px;  top:10px; font-size:14px;"> L. 0.00</p>
                    </div>
                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:28px; font-size:14px;">Importe Gravado 15%: </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:28px; font-size:14px;">L. {{$importesConCentavos->sub_total_grabado}}</p>
                    </div>
                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:46px; font-size:14px;">Importe Gravado 18%: </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:46px; font-size:14px;">L. 0.00</p>
                    </div>

                    <div>
                        <p class="card-text" style="position:absolute; left:10px;  top:64px; font-size:14px;">Importe Exento:  </p>
                        <p class="card-text" style="position:absolute; right:10px;  top:64px; font-size:14px;">L. {{$importesConCentavos->sub_total_excento}}</p>
                    </div>

                    <p class="card-text" style="position:absolute; left:10px;  top:85px; font-size:14px;">Desc. y Rebajas:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:85px; font-size:14px;">L. 0.00</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:105px; font-size:14px;">Sub Total:</p>
                    <p class="card-text" style="position:absolute; right:10px;  top:105px; font-size:14px;">L. {{$importesConCentavos->sub_total}}</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:130px; font-size:14px;">Impuesto sobre
                        venta 15%: </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:130px; font-size:14px;"> L. {{$importesConCentavos->isv}}</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:148px; font-size:14px;">Impuesto sobre
                        venta 18%: </p>
                    <p class="card-text" style="position:absolute; right:10px;  top:148px; font-size:14px;"> L. 0.00</p>

                    <p class="card-text" style="position:absolute; left:10px;  top:185px; font-size:16px;"><b>Total a
                            Pagar: </b></p>
                    <p class="card-text" style="position:absolute; right:10px;  top:185px; font-size:16px;"><b>L. {{$importesConCentavos->total}}</b>
                    </p>
                </div>
            </div>

            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:80px;  top:25px; ">{{ strtoupper($cliente->nombre) }}</p>
                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
            </div>


        </div>

        @if($cai->estado_venta_id==2)
        <div>
            <p class="" style="position:absolute; margin-top:{{$altura2 + 40}}px;  left:80px;   font-size:50px;">--FACTURA ANULADA--</p>
        </div>
        @endif



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
