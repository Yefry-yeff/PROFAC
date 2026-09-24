<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    @include('pdf.partials.legacy-layout')
    <style>
        @page { margin: 28px; }
        p { font-size: 10px; }
        body { margin: 0; padding: 0; width: 100%; }
        .carta-logo { display:block; width:100%; margin-bottom:8px; }
        .carta-ancho { width:100% !important; margin-left:0 !important; }

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
    <img src="{{ public_path('img/membrete/Logo3.png') }}" class="carta-logo" alt="">

    <!-- Encabezado de la carta -->
    <div class="card border border-dark carta-ancho">
        <div class="card-header">
            <b>CARTA DE ENTREGA</b>
            <b style="position:absolute; right:10px;">Distribución #{{ $distribucion->id }}</b>
        </div>
        <div class="card-body" style="padding:4px 10px;">
            <table style="border:none; font-size:10px;"><tr><td style="border:none; padding:1px 0;"><b>Registro tributario: 08011986138652</b></td><td style="border:none; padding:1px 0; text-align:right;"><b>Fecha generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</b></td></tr></table>
        </div>
    </div>

    <!-- Información de la distribución -->
    <div class="card border border-dark carta-ancho" style="margin-top:4px;">
        <div class="card-body" style="padding:4px 10px;">
            <table style="border:none; font-size:10px;"><tr><td style="width:65%; border:none; padding:0;"><p style="margin:0 0 2px;"><b>Equipo de entrega:</b> {{ $distribucion->equipo->nombre_equipo }}</p><p style="margin:0 0 2px;"><b>Fecha programada:</b> {{ \Carbon\Carbon::parse($distribucion->fecha_programada)->format('d/m/Y') }}</p><p style="margin:0 0 2px;"><b>Observaciones:</b> {{ $distribucion->observaciones ?: 'Ninguna' }}</p><p style="margin:0;"><b>Coordinado por:</b> {{ $distribucion->creador->name }}</p></td><td style="width:35%; border:none; padding:0; vertical-align:top;"><p style="margin:0 0 2px;"><b>Total facturas:</b> {{ count($clientes) > 0 ? array_sum(array_map(fn($c) => count($c['facturas']), $clientes)) : 0 }}</p><p style="margin:0;"><b>Total clientes:</b> {{ count($clientes) }}</p></td></tr></table>
        </div>
    </div>

    <!-- Productos agrupados por cliente y factura -->
    <div class="carta-ancho" style="margin-top:8px;">

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
    <div class="carta-ancho" style="margin-top:30px;">
        <table style="border:none; font-size:11px;"><tr><td style="width:50%; border:none; padding-right:20px;"><p style="margin:0; border-top:1px solid #000; padding-top:4px;"><b>Coordinado por:</b></p><p style="margin:2px 0;">{{ strtoupper($distribucion->creador->name) }}</p></td><td style="width:50%; border:none; padding-left:20px;"><p style="margin:0; border-top:1px solid #000; padding-top:4px;"><b>Recibido por:</b></p><p style="margin:2px 0;">Nombre y sello</p></td></tr></table>
    </div>

</body>
</html>
