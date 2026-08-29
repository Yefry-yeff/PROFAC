<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 8px; }
        .header { display: table; width: 100%; margin-bottom: 8px; }
        .logo, .title { display: table-cell; vertical-align: middle; }
        .logo { width: 150px; }
        .logo img { width: 130px; }
        .title { text-align: center; padding-right: 150px; }
        .title h1 { margin: 0; color: #1f6f50; font-size: 16px; }
        .title p { margin: 2px 0; }
        .filters { padding: 6px 8px; background: #eef6f2; border: 1px solid #bdd8ca; margin-bottom: 10px; }
        .invoice { margin-bottom: 13px; page-break-inside: avoid; }
        .invoice-header { background: #1f6f50; color: #fff; padding: 6px 8px; }
        .invoice-meta { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
        .invoice-meta td { border: 1px solid #cfd8dc; padding: 4px 6px; background: #f8faf9; }
        table.products { width: 100%; border-collapse: collapse; }
        .products th { background: #e67e22; color: #fff; border: 1px solid #bf650f; padding: 4px; }
        .products td { border: 1px solid #cfd8dc; padding: 4px; }
        .products tr:nth-child(even) td { background: #fff8ee; }
        .left { text-align: left; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals td { font-weight: bold; background: #eef6f2 !important; }
        .empty { text-align: center; padding: 18px; border: 1px solid #cfd8dc; }
        .footer { position: fixed; bottom: -18px; left: 0; right: 0; color: #607d8b; font-size: 7px; }
        .page-number:after { content: counter(page); }
    </style>
    <title>Detalle de facturas por producto</title>
</head>
<body>
    @php
        $fecha = fn ($valor) => $valor ? date('d/m/Y', strtotime($valor)) : '-';
        $partes = [];
        if ($filtros['desde']) $partes[] = 'Desde: '.$fecha($filtros['desde']);
        if ($filtros['hasta']) $partes[] = 'Hasta: '.$fecha($filtros['hasta']);
        if ($filtros['cai']) $partes[] = 'N. factura: '.$filtros['cai'];
        if ($filtros['cliente']) $partes[] = 'Cliente: '.$filtros['cliente'];
        if ($filtros['vendedor']) $partes[] = 'Vendedor: '.$filtros['vendedor'];
        if ($filtros['facturador']) $partes[] = 'Facturador: '.$filtros['facturador'];
    @endphp

    <div class="header">
        <div class="logo"><img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Logo"></div>
        <div class="title">
            <h1>DISTRIBUCIONES VALENCIA</h1>
            <p><strong>{{ strtoupper($tipoLabel) }} - DETALLE DE FACTURAS POR PRODUCTO</strong></p>
            <p>{{ count($facturas) }} factura(s) | Generado {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    <div class="filters"><strong>Filtros:</strong> {{ $partes ? implode(' | ', $partes) : 'Sin filtros adicionales' }}</div>

    @forelse($facturas as $factura)
        @php $productos = $detalles->get($factura->id, collect()); @endphp
        <div class="invoice">
            <div class="invoice-header"><strong>FACTURA {{ $factura->numero_factura ?: $factura->cai }}</strong></div>
            <table class="invoice-meta">
                <tr>
                    <td><strong>Fecha emisión:</strong> {{ $fecha($factura->fecha_emision) }}</td>
                    <td><strong>Cliente:</strong> {{ $factura->nombre_cliente }}</td>
                    <td><strong>Pago:</strong> {{ $factura->tipo_pago }}</td>
                    <td><strong>Vendedor:</strong> {{ $factura->vendedor }}</td>
                    <td><strong>Facturador:</strong> {{ $factura->facturador ?: '-' }}</td>
                </tr>
            </table>
            <table class="products">
                <thead>
                    <tr>
                        <th style="width:8%">Código</th><th>Producto</th><th style="width:10%">Unidad</th>
                        <th style="width:9%">Cantidad</th><th style="width:11%">Precio unit.</th>
                        <th style="width:11%">Subtotal</th><th style="width:10%">ISV</th><th style="width:11%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                        <tr>
                            <td class="center">{{ $producto->producto_id }}</td>
                            <td class="left">{{ $producto->producto }}</td>
                            <td class="center">{{ $producto->unidad }}</td>
                            <td class="right">{{ number_format((float) $producto->cantidad, 2) }}</td>
                            <td class="right">L {{ number_format((float) $producto->precio_unidad, 2) }}</td>
                            <td class="right">L {{ number_format((float) $producto->sub_total, 2) }}</td>
                            <td class="right">L {{ number_format((float) $producto->isv, 2) }}</td>
                            <td class="right">L {{ number_format((float) $producto->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="center">Esta factura no tiene productos registrados.</td></tr>
                    @endforelse
                    <tr class="totals">
                        <td colspan="5" class="right">TOTALES DE FACTURA</td>
                        <td class="right">L {{ number_format((float) $factura->sub_total, 2) }}</td>
                        <td class="right">L {{ number_format((float) $factura->isv, 2) }}</td>
                        <td class="right">L {{ number_format((float) $factura->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if(!$loop->last)<div style="page-break-after: always;"></div>@endif
    @empty
        <div class="empty">No se encontraron facturas con los filtros seleccionados.</div>
    @endforelse

    <div class="footer">
        Descargado por: {{ optional(Auth::user())->name ?? 'Sistema' }}
        <span style="float:right">Página <span class="page-number"></span></span>
    </div>
</body>
</html>