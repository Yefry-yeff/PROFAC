<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; margin: 0; padding: 8px; color: #222; }
        .header { display: table; width: 100%; margin-bottom: 10px; }
        .header-logo, .header-text { display: table-cell; vertical-align: middle; }
        .header-logo { width: 190px; }
        .header-logo img { width: 175px; height: auto; }
        .header-text { text-align: center; padding-right: 190px; }
        .header-text h1 { color: #7d3f00; font-size: 15px; margin: 0 0 3px; }
        .header-text p { margin: 1px 0; font-size: 8px; }
        .filters { background: #fdf4e7; border: 1px solid #f2d49a; padding: 5px 7px; margin-bottom: 8px; }
        .filters strong { color: #7d3f00; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e67e22; color: #fff; border: .5px solid #b85e0b; padding: 4px 2px; font-size: 7px; }
        td { border: .5px solid #ccc; padding: 3px 2px; font-size: 7px; text-align: center; }
        tbody tr:nth-child(even) td { background: #fff8ee; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        tfoot td { background: #fdf4e7; font-weight: bold; border-top: 1px solid #e67e22; }
        .footer { position: fixed; bottom: -2px; left: 8px; right: 8px; color: #666; font-size: 7px; }
        .footer-right { float: right; }
    </style>
    <title>Facturas Anuladas</title>
</head>
<body>
    @php
        $numero = fn ($valor) => (float) str_replace(',', '', (string) $valor);
        $fecha = fn ($valor) => $valor ? date('d/m/Y', strtotime($valor)) : '-';
        $partesFiltro = [];
        if ($filtros['desde']) $partesFiltro[] = 'Desde: '.$fecha($filtros['desde']);
        if ($filtros['hasta']) $partesFiltro[] = 'Hasta: '.$fecha($filtros['hasta']);
        if ($filtros['cai']) $partesFiltro[] = 'CAI: '.$filtros['cai'];
        if ($filtros['cliente']) $partesFiltro[] = 'Cliente: '.$filtros['cliente'];
        if ($filtros['vendedor']) $partesFiltro[] = 'Vendedor: '.$filtros['vendedor'];
        if ($filtros['facturador']) $partesFiltro[] = 'Facturador: '.$filtros['facturador'];
    @endphp

    <div class="header">
        <div class="header-logo"><img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Logo"></div>
        <div class="header-text">
            <h1>DISTRIBUCIONES VALENCIA</h1>
            <p>RTN: 08011986138652</p>
            <p><strong>REPORTE DE FACTURAS ANULADAS - {{ strtoupper($nombreTipo) }}</strong></p>
            <p>Filtrado por fecha de creación de la factura</p>
        </div>
    </div>

    <div class="filters">
        <strong>Filtros:</strong> {{ $partesFiltro ? implode(' | ', $partesFiltro) : 'Sin filtros adicionales' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>FACTURA</th>
                <th>FECHA CREACIÓN</th>
                <th>FECHA EMISIÓN</th>
                <th>CLIENTE</th>
                <th>TIPO PAGO</th>
                <th>SUBTOTAL</th>
                <th>ISV</th>
                <th>TOTAL</th>
                <th>VENDEDOR</th>
                <th>FACTURADOR</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facturas as $factura)
                <tr>
                    <td>{{ $factura->id }}</td>
                    <td>{{ $factura->cai }}</td>
                    <td>{{ $fecha($factura->fecha_registro) }}</td>
                    <td>{{ $fecha($factura->fecha_emision) }}</td>
                    <td class="text-left">{{ $factura->nombre }}</td>
                    <td>{{ $factura->descripcion }}</td>
                    <td class="text-right">L {{ number_format($numero($factura->sub_total), 2) }}</td>
                    <td class="text-right">L {{ number_format($numero($factura->isv), 2) }}</td>
                    <td class="text-right">L {{ number_format($numero($factura->total), 2) }}</td>
                    <td class="text-left">{{ $factura->vendedor }}</td>
                    <td class="text-left">{{ $factura->facturador }}</td>
                </tr>
            @empty
                <tr><td colspan="11">No se encontraron facturas anuladas con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
        @if($facturas)
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">TOTALES ({{ count($facturas) }} facturas)</td>
                    <td class="text-right">L {{ number_format(collect($facturas)->sum(fn ($f) => $numero($f->sub_total)), 2) }}</td>
                    <td class="text-right">L {{ number_format(collect($facturas)->sum(fn ($f) => $numero($f->isv)), 2) }}</td>
                    <td class="text-right">L {{ number_format(collect($facturas)->sum(fn ($f) => $numero($f->total)), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Generado: {{ now()->format('d/m/Y H:i') }} | Descargado por: {{ optional(Auth::user())->name ?? 'Sistema' }}
        <span class="footer-right">Facturas anuladas por fecha de creación</span>
    </div>
</body>
</html>