<!DOCTYPE html>
<html>

<head>


        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        .color-red {
            color: red;
        }

        p {
            font-size: 10px;
        }

        body {

           /*  background-size: 100% 100%; */
            margin-left: -95px;
            padding: 50px;
            /* ##background-image: url('img/membrete/Logo1.png'); */


            width: 45rem;
            height: 3rem;


        }


        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
        }

        th,
        td {
            text-align: left;
            padding: 2px;

        }

        thead {
            background-color: #f2f2f2
        }

        /* tr:nth-child(even) {
            background-color: #f2f2f2

        } */

        .letra {
            font-weight: 800;


        }
    </style>
    <title>FACTURA</title>
</head>

<body>

    @php
        $altura = 200;
        $altura2 = 320;
        $contadorFilas = 0;
        $contPe = 0;
        $p1 = 24;
        $p2 = 30;
        $vueltasTabla = 0;
    @endphp


    <div class="pruebaFondo">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem" style="margin-left:3%; margin-top:-25px; position:absolute;"alt="">

        <div class="card border border-dark" style="margin-left:44px;  margin-top:105px; width:45rem; height:5.5rem;">
            <div class="card-header">
                <b>Factura No. {{ $cai->numero_factura }} </b>
                <b style="position:absolute;right: 270px"> *Copia* </b>
                <b style="position:absolute;right: 10px">Factura de: {{ $cai->descripcion }}</b>
            </div>

            <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:50px;"><b>Registro tributario:
                        08011986138652</b></p>
                <p class="card-text" style="position:absolute;left:420px;  top:50px;"><b>CAI:
                        {{ $cai->cai }}</b></p>
                <p class="card-text" style="position:absolute;left:20px;  top:65px;"><b>Fecha límite de emisión:
                        {{ $cai->fecha_limite_emision }}</b></p>
                <p class="card-text" style="position:absolute;left:340px;  top:65px;"><b>Rango autorizado:
                        {{ $cai->numero_inicial }} - {{ $cai->numero_final }}</b></p>
            </div>
        </div>

        <div class="card border border-dark" style="margin-left:44px; margin-top:4px; width:45rem;">
            <div class="card-body" style="padding:4px 10px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr>
                        <td style="width:58%; vertical-align:top; padding:0; border:none;">
                            <p style="margin:0 0 2px;"><b>Cliente:</b> {{ $cliente->nombre }} - ({{ $cliente->clienteId }})</p>
                            <p style="margin:0 0 2px;"><b>Dirección:</b> {{ $cliente->direccion }}</p>
                            <p style="margin:0 0 2px;"><b>Correo:</b> {{ $cliente->correo }} &nbsp;&nbsp; <b>Teléfono:</b> {{ $cliente->telefono_empresa }}</p>
                            <p style="margin:0;"><b>Notas:</b> {{ $cai->comentario }}</p>
                        </td>
                        <td style="width:42%; vertical-align:top; padding:0 0 0 10px; border:none; border-left:1px solid #ccc;">
                            <p style="margin:0 0 2px;"><b>Fecha:</b> {{ $cai->fecha_emision }}</p>
                            <p style="margin:0 0 2px;"><b>Hora:</b> {{ $cai->hora }}</p>
                            <p style="margin:0 0 2px;"><b>Vence:</b> {{ $cai->fecha_vencimiento }}</p>
                            <p style="margin:0 0 2px;"><b>RTN:</b> {{ $cliente->rtn }}</p>
                            <p style="margin:0;"><b>Orden N°:</b> {{ $ordenCompra['numero_orden'] }}</p>
                        </td>
                    </tr>
                </table>
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px; margin-top:3px; border-top:1px solid #ccc;">
                    <tr>
                        <td style="width:33%; border:none; padding:2px 0 1px;"><b>Correlativo de Ord. exenta</b></td>
                        <td style="width:34%; border:none; padding:2px 0 1px; text-align:center;"><b>Constancia de registro exonerado</b></td>
                        <td style="width:33%; border:none; padding:2px 0 1px; text-align:right;"><b>Identificativo del registro de la SAG</b></td>
                    </tr>
                    <tr>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                    </tr>
                </table>
            </div>
        </div>

                 @php
                    $cant = count($productos);
                @endphp
                <div class="" style="position: relative; margin-left:44px; margin-top:4px; width:45rem">

                    <div>

                        <table class="" style="font-size: 10px; ">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Bodega</th>
                                    <th>Seccion</th>
                                    <th>Medida</th>
                                    <th>Exento</th>
                                    <th>Precio </th>
                                    <th>Cantidad</th>
                                    <th>Importe</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($productos as $producto)
                                    <tr>
                                        <td>{{ $producto->codigo }}</td>
                                        <td>{{ $producto->descripcion }}</td>
                                        <td>{{ $producto->bodega }}</td>
                                        <td>{{ $producto->seccion }}</td>
                                         <td>{{ $producto->medida }}</td>
                                        <td>{{ $producto->excento }}</td>
                                        <td>{{ $producto->precio }}</td>
                                        <td>{{ $producto->cantidad }}</td>
                                        <td>{{ $producto->importe }}</td>
                                    </tr>
                                @endforeach


                                @php

                                    $altura = 50;
                                    $altura2 = 450;
                                @endphp

                            </tbody>


                        </table>

                    </div>

                </div>



                <div style="position: relative; margin-left:44px; margin-top:6px; width:26rem; height:14rem;">


                    <div class="card border border-dark" style="position:absolute;left:0px; width:26rem;">
                        <div class="card-body" style="padding:4px 8px;">
                            <p style="margin:0 0 1px; font-size:10px;"><b>Asesor comercial:</b> {{ $cai->vendedor }} &nbsp; <b>Tele asesor:</b> {{ $cai->facturador }} &nbsp; <b>Asesor de entrega:</b> {{ $cai->asesor_entrega ?? '' }}</p>
                            <p style="margin:0 0 2px; font-size:10px;"><b>Trámite #{{ $cai->flujo_id ?? '—' }}</b></p>
                            <hr style="margin:2px 0; border-top:1px solid #999;">
                            <p style="margin:0 0 1px; font-size:9px;">1. Por cada cheque devuelto se cobra 750 lempiras.</p>
                            <p style="margin:0 0 1px; font-size:9px;">2. Toda cuenta vencida pagara el 3.25% de interés mensual.</p>
                            <p style="margin:0 0 1px; font-size:9px;">3. El único comprobante de pago de ésta factura es el emitido por distribuciones valencia.</p>
                            <p style="margin:0 0 1px; font-size:9px;">4. No se aceptan reclamos ni devoluciones después de 10 días.</p>
                            <p style="margin:0 0 1px; font-size:9px;">5. La firma del cliente o representante en la factura, da por hecho que acepta y obliga a este a cumplir con todas las condiciones estipuladas.</p>
                            <p style="margin:0 0 1px; font-size:9px;">6. El cliente debera realizar el pago de la factura a su fecha de vencimiento, en caso de incumplimiento de pago, este se compromete a aceptar otros procesos de cobros a la vez renuncia a su domicilio para efectos legales y somete a la jurisdicción de tegucigalpa municipio del distrito central.</p>
                            <p style="margin:0; font-size:9px;">7. Las entregas y creditos para cuentas con facturas vencidas serán congeladas hasta el pago de las mismas haya sido efectuado en su totalidad.
                                @if ($cai->estado_factura == 1)
                                    N{{ $cai->numero }}-CF11
                                @else
                                    N{{ $cai->numero }}-CF12
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="card border border-dark" style="position:absolute;left:430px; width:18rem;">
                        <div class="card-body" style="padding:4px 8px;">
                            <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                                <tr>
                                    <td style="border:none; padding:1px 0;">Importe exonerado:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Importe Gravado 15%:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total_grabado }}</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Importe Gravado 18%:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Importe Exento:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. @if ($importesConCentavos->subtotal_excentovale>0) {{ $importesConCentavos->subtotal_excentovale }} @else 0.00 @endif</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Desc. y Rebajas {{$importes->porc_descuento}}%:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. {{$importesConCentavos->monto_descuento}}</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Sub Total:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total }}</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Impuesto sobre venta 15%:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->isv }}</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:1px 0;">Impuesto sobre venta 18%:</td>
                                    <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                                </tr>
                                <tr>
                                    <td style="border:none; padding:3px 0 1px; border-top:1px solid #999;"><b>Total a Pagar:</b></td>
                                    <td style="border:none; padding:3px 0 1px; text-align:right; border-top:1px solid #999;"><b>L. {{ $importesConCentavos->total }}</b></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border:none; padding:2px 0 0; font-size:8px;">
                                        <b>@if ($flagCentavos == false)"{{ $numeroLetras . ' CON CERO CENTAVOS' }}"@else"{{ $numeroLetras }}"@endif</b>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if ($cai->estado_venta_id == 2)
                    <div  style="position:absolute;   text-align: center; margin-top:350px;width:45rem">
                        <p style="font-size:50px">
                            --FACTURA ANULADA--</p>
                    </div>
                    @endif

                </div>

                <p style="margin:4px 44px 0; font-size:8px; text-align:right;">Original: Cliente, Copia obligado tributario emisor.</p>

                <div style="margin-left:44px; margin-top:70px; width:45rem;">
                    <table style="width:100%; border:none; border-collapse:collapse; font-size:9px;">
                        <tr>
                            <td style="width:50%; border:none; vertical-align:top; padding:0 20px 0 0;">
                                <p style="margin:0; border-top:1px solid #000; padding-top:3px; word-wrap:break-word; overflow-wrap:break-word;">Cliente: {{ strtoupper($cliente->nombre) }}</p>
                                <p style="margin:4px 0 0;">Recibido por: _______________________</p>
                                <p style="margin:4px 0 0;">DNI: ________________________________</p>
                                <p style="margin:4px 0 0;">Cargo: ______________________________</p>
                                <p style="margin:4px 0 0;">Telefono: ___________________________</p>
                                <p style="margin:5px 0 0;"><b>*Se requiere firma y sello de recibido.*</b></p>
                            </td>
                            <td style="width:50%; border:none; vertical-align:top; padding:0 0 0 20px; text-align:center;">
                                <p style="margin:0; border-top:1px solid #000; padding-top:3px; text-align:center;">DISTRIBUCIONES VALENCIA</p>
                            </td>
                        </tr>
                    </table>
                </div>











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
