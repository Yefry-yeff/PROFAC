<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        p { font-size: 12px; }

        body {
            margin-left: -95px;
            padding: 50px;
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
        }

        thead { background-color: #f2f2f2; }

        .letra { font-weight: 800; }

        .seccion-cliente {
            margin-top: 12px;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }

        .encabezado-cliente {
            background-color: #1a5276;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 6px;
        }

        .encabezado-factura {
            background-color: #2471a3;
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
        }

        .tabla-productos th {
            background-color: #d6eaf8;
            color: #1a5276;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 4px;
            border: 1px solid #aed6f1;
        }

        .tabla-productos td {
            font-size: 10px;
            padding: 2px 4px;
            border: 1px solid #d4e6f1;
        }

        .tabla-productos tr:nth-child(even) td {
            background-color: #eaf4fb;
        }

        .total-factura {
            text-align: right;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 4px;
            background-color: #d6eaf8;
        }
    </style>
    <title>Carta de Entrega</title>
</head>
<body>

    <!-- Logo y membrete (igual que factura corporativa) -->
    <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem"
         style="margin-left:3%; margin-top:-25px; position:absolute;" alt="">

    <!-- Encabezado de la carta -->
    <div class="card border border-dark" style="margin-left:44px; margin-top:105px; width:45rem; height:5rem;">
        <div class="card-header">
            <b>CARTA DE ENTREGA</b>
            <b style="position:absolute; right:10px;">Distribución #{{ $distribucion->id }}</b>
        </div>
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:50px;">
                <b>Registro tributario: 08011986138652</b>
            </p>
            <p class="card-text" style="position:absolute; left:380px; top:50px;">
                <b>Fecha generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</b>
            </p>
        </div>
    </div>

    <!-- Información de la distribución -->
    <div class="card border border-dark" style="margin-left:44px; margin-top:10px; width:45rem; height:7rem;">
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:10px;">
                <b>Equipo de entrega:</b> {{ $distribucion->equipo->nombre_equipo }}
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:28px;">
                <b>Fecha programada:</b> {{ \Carbon\Carbon::parse($distribucion->fecha_programada)->format('d/m/Y') }}
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:46px;">
                <b>Observaciones:</b> {{ $distribucion->observaciones ?: 'Ninguna' }}
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:65px;">
                <b>Coordinado por:</b> {{ $distribucion->creador->name }}
            </p>
            <p class="card-text" style="position:absolute; left:380px; top:10px;">
                <b>Total facturas:</b> {{ count($clientes) > 0 ? array_sum(array_map(fn($c) => count($c['facturas']), $clientes)) : 0 }}
            </p>
            <p class="card-text" style="position:absolute; left:380px; top:28px;">
                <b>Total clientes:</b> {{ count($clientes) }}
            </p>
        </div>
    </div>

    <!-- Productos agrupados por cliente y factura -->
    <div style="margin-left:44px; margin-top:12px; width:45rem;">

        @foreach($clientes as $cliente)
        <div class="seccion-cliente">
            <!-- Encabezado del cliente -->
            <div class="encabezado-cliente">
                <i>Cliente:</i> {{ strtoupper($cliente['nombre']) }}
                @if($cliente['direccion'])
                    &nbsp;&nbsp;|&nbsp;&nbsp; {{ $cliente['direccion'] }}
                @endif
            </div>

            @foreach($cliente['facturas'] as $factura)
            <!-- Encabezado de la factura -->
            <div class="encabezado-factura">
                Factura #{{ $factura['numero'] }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Orden de entrega: {{ $factura['orden_entrega'] }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Fecha: {{ $factura['fecha'] }}
            </div>

            <!-- Tabla de productos -->
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th width="60">Código</th>
                        <th>Producto</th>
                        <th width="70">Medida</th>
                        <th width="60" style="text-align:right">Cant.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factura['productos'] as $prod)
                    <tr>
                        <td>{{ $prod->codigo }}</td>
                        <td>{{ $prod->descripcion }}</td>
                        <td>{{ $prod->medida }}</td>
                        <td style="text-align:right">{{ $prod->cantidad }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#888;">Sin productos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @endforeach
        </div>
        @endforeach

    </div>

    <!-- Firmas -->
    <div style="margin-left:44px; margin-top:30px; width:45rem;">
        <div style="position: relative; height:6rem; width:45rem;">
            <p style="position:absolute; left:20px; top:0px;">
                _______________________________________
            </p>
            <p style="position:absolute; left:450px; top:0px;">
                _______________________________________
            </p>
            <p style="position:absolute; left:20px; top:18px; font-size:11px;">
                <b>Coordinado por:</b>
            </p>
            <p style="position:absolute; left:20px; top:32px; font-size:11px;">
                {{ strtoupper($distribucion->creador->name) }}
            </p>
            <p style="position:absolute; left:450px; top:18px; font-size:11px;">
                <b>Recibido por:</b>
            </p>
            <p style="position:absolute; left:450px; top:32px; font-size:11px;">
                Nombre y sello
            </p>
        </div>
    </div>

</body>
</html>
