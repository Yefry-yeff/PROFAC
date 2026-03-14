<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
    $altura  = 20;
    $altura2 = 320;
@endphp

<div class="pruebaFondo">
    <img src="img/membrete/Logo3.png" width="800rem"
         style="margin-left:3%; margin-top:25px; position:absolute;" alt="">

    <b style="position:absolute; right:100px; top:50px;">*Copia*</b>

    {{-- Encabezado de la boleta --}}
    <div class="card border border-dark"
         style="margin-left:44px; margin-top:150px; width:45rem; height:4rem;">
        <div class="card-header">
            <b>Boleta de Compra No. {{ $boleta->numero_boleta }}</b>
            <b style="position:absolute; right:10px;">
                Fecha: {{ \Carbon\Carbon::parse($boleta->fecha)->format('d/m/Y') }}
            </b>
        </div>
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:40px;">
                <b>Registro tributario: 08011986138652</b>
            </p>
        </div>
    </div>

    {{-- Datos del cliente --}}
    <div class="card border border-dark"
         style="margin-left:44px; margin-top:10px; width:45rem; height:5rem;">
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:10px;">
                <b>Cliente: </b>{{ $boleta->cliente }}
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:30px; font-size:10px; max-width:550px;">
                <b>Dirección:</b> {{ $boleta->direccion }}
            </p>
            <p class="card-text" style="position:absolute; left:500px; top:10px;">
                <b>Registrado por:</b> {{ $boleta->registrado_por }}
            </p>
        </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="card border border-dark"
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
        @php $altura = 170; $altura2 = 530; @endphp
        <div style="page-break-after: always"></div>
    @else
        @php $altura = 20; @endphp
    @endif

    {{-- Totales --}}
    <div style="position:relative; margin-left:44px;">

        <div class="card border border-dark"
             style="position:absolute; left:0px; margin-top:{{ $altura }}px; width:26rem; height:10rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute; left:10px; top:5px; font-size:14px;">
                    <b>Registrado por: </b>{{ $boleta->registrado_por }}
                </p>

                @if($flagCentavos == false)
                <p class="card-text"
                   style="position:absolute; left:35px; top:130px; font-size:12px;">
                    "{{ $numeroLetras . ' CON CERO CENTAVOS' }}"
                </p>
                @else
                <p class="card-text"
                   style="position:absolute; left:35px; top:130px; font-size:12px;">
                    "{{ $numeroLetras }}"
                </p>
                @endif
            </div>
        </div>

        <div class="card border border-dark"
             style="position:absolute; left:430px; margin-top:{{ $altura }}px; width:18rem; height:10rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute; left:10px; top:10px; font-size:14px;">Sub Total:</p>
                <p class="card-text" style="position:absolute; right:10px; top:10px; font-size:14px;">
                    L. {{ number_format($boleta->sub_total, 2) }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:72px; font-size:16px;"><b>Total a Pagar:</b></p>
                <p class="card-text" style="position:absolute; right:10px; top:72px; font-size:16px;">
                    <b>L. {{ number_format($boleta->total, 2) }}</b>
                </p>
            </div>
        </div>

        <div style="position:absolute; left:0px; margin-top:{{ $altura2 }}px; width:45rem;">
            <p class="card-text" style="position:absolute; left:20px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute; left:450px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute; left:80px; top:25px;">
                {{ strtoupper($boleta->cliente) }}
            </p>
            <p class="card-text" style="position:absolute; left:495px; top:25px;">DISTRIBUCIONES VALENCIA</p>
        </div>
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
