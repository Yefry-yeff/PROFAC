<!DOCTYPE html>
<html>
<head>
        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        p { font-size: 12px; }

        body {
            margin: -45px;
            padding: 0;
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
            padding: 3px 5px;
        }

        thead { background-color: #f2f2f2; }

        .letra { font-weight: 800; }
    </style>
    <title>Boleta de Compra (Copia)</title>
</head>
<body>

@php
    $contadorFilas = 0;
    $altura = 20;
@endphp

<div class="pruebaFondo">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem"
         style="margin-left:3%; margin-top:25px; position:absolute;" alt="">

    <b style="position:absolute; right:100px; top:50px;">*Copia*</b>

    {{-- Encabezado CAI --}}
    <div class="border card border-dark"
         style="margin-left:44px; margin-top:150px; width:45rem; height:6.5rem;">
        <div class="card-header">
            <b>Boleta de Compra No. {{ $boleta->numero_boleta }}</b>
            <b style="position:absolute; right:10px;">
                Fecha: {{ \Carbon\Carbon::parse($boleta->fecha)->format('d/m/Y') }}
            </b>
        </div>
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:50px;">
                <b>Registro tributario: 08011986138652</b>
            </p>
            @if($caiBoleta)
            <p class="card-text" style="position:absolute; left:420px; top:50px;">
                <b>CAI: {{ $caiBoleta->cai }}</b>
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:65px;">
                <b>Fecha límite de emisión: {{ $caiBoleta->fecha_limite_emision }}</b>
            </p>
            <p class="card-text" style="position:absolute; left:340px; top:65px;">
                <b>Rango autorizado: {{ $caiBoleta->numero_inicial }} - {{ $caiBoleta->numero_final }}</b>
            </p>
           
            @endif
        </div>
    </div>

    {{-- Datos del cliente: Fila 1: Nombre | RTN/DNI | Dirección — Fila 2: Teléfono --}}
    <div class="border card border-dark"
         style="margin-left:44px; margin-top:10px; width:45rem;">
        <div class="card-body" style="padding:8px 12px;">
            <table style="width:100%; font-size:11px; border:none;" cellspacing="0" cellpadding="3">
                <tr>
                    <td style="width:35%; border:none;"><b>Cliente:</b> {{ $boleta->cliente }}</td>
                    <td style="width:25%; border:none;"><b>RTN/DNI:</b> {{ $boleta->rtn_dni ?? '' }}</td>
                    <td style="width:40%; border:none;"><b>Dirección:</b> {{ $boleta->direccion }}</td>
                </tr>
                @if(!empty($boleta->telefono))
                <tr>
                    <td colspan="3" style="border:none;"><b>Teléfono:</b> {{ $boleta->telefono }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="border card border-dark"
         style="position:relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside:auto;">
        <div>
            <table style="font-size:11px;">
                <thead>
                    <tr>
                        <th style="width:50px;">Línea</th>
                        <th>Descripción</th>
                        <th style="width:110px;">Precio</th>
                        <th style="width:90px;">Cantidad</th>
                        <th style="width:110px;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->linea }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td>{{ $detalle->precio }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>{{ $detalle->importe }}</td>
                    </tr>
                    @php $contadorFilas++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($contadorFilas > 4 && $contadorFilas < 24)
        @php $altura = 170; @endphp
        <div style="page-break-after: always"></div>
    @else
        @php $altura = 20; @endphp
    @endif

    {{-- Sección inferior --}}
    <div style="position:relative; margin-left:44px;">

        {{-- Tarjeta izquierda: Comentario + Registrado por --}}
        <div class="border card border-dark"
             style="position:absolute; left:0px; margin-top:{{ $altura }}px; width:26rem; height:14rem;">
            <div class="card-body">
                @if(!empty($boleta->comentario))
                <p class="card-text" style="position:absolute; left:10px; top:5px; font-size:11px; max-width:390px;">
                    <b>Comentario:</b> {{ $boleta->comentario }}
                </p>
                @endif
                <p class="card-text" style="position:absolute; left:10px; bottom:8px; font-size:12px;">
                    <b>Registrado por: </b>{{ $boleta->registrado_por }}
                </p>
            </div>
        </div>

        {{-- Tarjeta derecha: Totales --}}
        <div class="border card border-dark"
             style="position:absolute; left:430px; margin-top:{{ $altura }}px; width:18rem; height:14rem;">
            <div class="card-body" style="padding:6px 10px;">
                <table style="width:100%; font-size:10px; border:none;" cellspacing="0" cellpadding="2">
                    <tr>
                        <td style="border:none;">Importe exonerado:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Importe Gravado 15%:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Importe Gravado 18%:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Importe Exento:</td>
                        <td style="border:none; text-align:right;">L. {{ number_format($boleta->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Desc. y Rebajas:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding-top:4px;"><b>Sub Total:</b></td>
                        <td style="border:none; text-align:right; padding-top:4px;"><b>L. {{ number_format($boleta->sub_total, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td style="border:none;">Impuesto sobre venta 15%:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none;">Impuesto sobre venta 18%:</td>
                        <td style="border:none; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr style="font-size:12px;">
                        <td style="border:none; padding-top:4px;"><b>Total a Pagar:</b></td>
                        <td style="border:none; text-align:right; padding-top:4px;"><b>L. {{ number_format($boleta->total, 2) }}</b></td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Valor en letras (debajo de los dos recuadros) --}}
        <div style="position:absolute; left:0px; margin-top:{{ $altura + 234 }}px; width:45rem;">
            <p class="card-text" style="font-size:11px; padding:4px 10px;">
                @if($flagCentavos == false)
                    <b>"{{ $numeroLetras . ' CON CERO CENTAVOS' }}"</b>
                @else
                    <b>"{{ $numeroLetras }}"</b>
                @endif
            </p>
        </div>

    </div>

    {{-- Firmas: fijas en la parte inferior de la página --}}
    <div style="position:fixed; bottom:50px; left:44px; width:45rem;">
        <p class="card-text" style="position:absolute; left:20px; top:10px;">
            _______________________________________
        </p>
        <p class="card-text" style="position:absolute; left:450px; top:10px;">
            _______________________________________
        </p>
        <p class="card-text" style="position:absolute; left:22px; top:28px; font-size:10px;">
            <b>Comprador:</b> {{ strtoupper($boleta->cliente) }}
            @if(!empty($boleta->rtn_dni)) | RTN/DNI: {{ $boleta->rtn_dni }} @endif
            @if(!empty($boleta->telefono)) | Tel: {{ $boleta->telefono }} @endif
        </p>
        <p class="card-text" style="position:absolute; left:470px; top:28px;">DISTRIBUCIONES VALENCIA</p>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
</script>
</body>
</html>

