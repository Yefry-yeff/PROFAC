<!DOCTYPE html>
<html>

<head>


        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    @include('pdf.partials.legacy-layout')
    <style>
        @page { margin: 28px; }
        .color-red { color: red; }
        p { font-size: 10px; }
        body { margin: 0; padding: 0; width: 100%; }
        .nd-logo { display:block; width:100%; margin-bottom:8px; }
        .nd-ancho { width:100% !important; margin-left:0 !important; }
        .nd-totales { width:41%; margin:8px 0 0 auto; }
        .nd-firmas { margin-top:65px; width:100%; }

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
    <title>Nota de débito</title>
</head>

<body>

@php
    $altura =20;
    $altura2 = 320;
    $contadorFilas = 0;
@endphp

       {{--   @if($cliente->estado_factura_id==1)
        <span style = "font-size: 10px; position:absolute;left:500px;  top:105px;">Documento: N{{$cliente->numero_factura}}-CF11</span></p>
        @else
        <span style = "font-size: 10px; position:absolute;left:500px;  top:105px;">Documento: N{{$cliente->numero_factura}}-CF12</span></p>
        @endif  --}}
    <div class="pruebaFondo">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" class="nd-logo" alt="">

        <b style="display:block; text-align:right; margin-bottom:8px;" >ORIGINAL</b>
        <div class="card border border-dark nd-ancho">

            <div >
                <table  class="" style="font-size: 12px;">
                    <thead>
                        <tr>
                          <th>Nota de débito No. {{$notaDebito->numeroCai}}</th>
                          <th style=" text-align: right; ">Factura No. {{ $cliente->cai }} </th>
                        </tr>
                    </thead>
                        <tr>
                            <td>Registro tributario:
                                08011986138652 </td>
                            <td>CAI: {{$cai->cai}}</td>
                        </tr>
                        <tr>
                            <td>Fecha límite de emisión: {{$cai->fecha_limite_emision}}</td>
                            <td>Rango autorizado: {{$cai->numero_inicial}} - {{$cai->numero_final}}</td>
                        </tr>
                </table>
            </div>


        </div>

        <div class="card border border-dark nd-ancho" style="margin-top:4px;">
            <div class="card-body" style="padding:4px 10px;"><table style="border:none; font-size:10px;"><tr><td style="width:65%; border:none; padding:0;"><p style="margin:0 0 2px;"><b>Cliente:</b> {{ $cliente->nombre_cliente }}</p><p style="margin:0 0 2px;"><b>Dirección:</b> {{ $cliente->direccion }}</p><p style="margin:0;"><b>Correo:</b> {{ $cliente->correo }}</p></td><td style="width:35%; border:none; padding:0 0 0 10px; border-left:1px solid #ccc;"><p style="margin:0 0 2px;"><b>Fecha:</b> {{ $notaDebito->fechaEmision }}</p><p style="margin:0 0 2px;"><b>RTN:</b> {{ $cliente->rtn }}</p><p style="margin:0;"><b>Teléfono:</b> {{ $cliente->telefono_empresa }}</p></td></tr></table></div>
        </div>


        <br>
        <br><br>

        <div class="card border border-dark nd-ancho" style="position: relative; margin-top:8px;">
            <div >
                <table  class="" style="font-size: 11px;">
                    <thead>
                        <tr>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Valor unitario</th>
                          <th>Total</th>
                        </tr>
                    </thead>
                        <tr>
                            <td>{{ $notaDebito->motivoDescripcion }}</td>
                            <td>1</td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                        </tr>
                        {{-- <tr>
                            <td>RECARGO APLICADO</td>
                            <td>L - </td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                        </tr> --}}
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>


        <div class="card border border-dark nd-totales"><div class="card-body" style="padding:4px 10px;"><table style="border:none; font-size:10px;"><tr><td style="border:none;">Sub Total:</td><td style="border:none; text-align:right;">L. {{ $montoConCentavos->total }}</td></tr><tr><td style="border:none;">Impuesto sobre la venta 15%:</td><td style="border:none; text-align:right;">L. 0.00</td></tr><tr><td style="border:none; padding-top:3px; border-top:1px solid #999;"><b>Total:</b></td><td style="border:none; text-align:right; padding-top:3px; border-top:1px solid #999;"><b>L. {{ $montoConCentavos->total }}</b></td></tr></table></div></div>
        <p class="card-text" style="margin-top:10px;">VALOR EN LETRAS: {{ $numeroLetras }} EXACTOS</p>

        <div style=" position: relative; margin-left:44px;">

            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:60px;  top:25px; ">{{ $cliente->nombre_cliente }}</p>
                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
            </div>



        </div>

        <p class="card-text" style="position:absolute;left:30px;  top:800px;">ORIGINAL: CLIENTE</p>
        <p class="card-text" style="position:absolute;left:30px;  top:815px;">COPIA: EMISOR</p>
        @if($notaDebito->estado_id==2)
        <div style="position:fixed; top:30%; left:0; width:100%; text-align:center; transform:rotate(-45deg); opacity:0.18; z-index:9999;">
            <p style="font-size:90px; font-weight:900; color:#cc0000; letter-spacing:8px; margin:0;">NOTA DE DEBITO ANULADA</p>
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
