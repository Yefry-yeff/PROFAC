<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oferta {{ $oferta['id'] }} - {{ $oferta['expo'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font: 12px/1.35 "Segoe UI", sans-serif; background: #eef1f5; }
        .sheet { width: min(1380px, calc(100% - 32px)); margin: 24px auto; padding: 30px; background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .toolbar { width: min(1380px, calc(100% - 32px)); margin: 18px auto 0; text-align: right; }
        button { padding: 8px 14px; border: 0; color: #fff; background: #1f5e8c; cursor: pointer; font-weight: 600; }
        header { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 16px; border-bottom: 3px solid #1f5e8c; }
        h1 { margin: 0; font-size: 24px; }
        h2 { margin: 24px 0 8px; font-size: 16px; }
        .muted { color: #667085; }
        .meta { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px 18px; margin-top: 18px; }
        .meta div { min-width: 0; }
        .meta strong { display: block; font-size: 10px; color: #667085; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px; border: 1px solid #d9dee8; vertical-align: top; }
        th { color: #fff; background: #27364b; text-align: left; font-size: 10px; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        .summary { width: 430px; margin: 18px 0 0 auto; }
        .summary td:first-child { font-weight: 600; }
        .result { font-size: 14px; font-weight: 700; }
        .profit { color: #147a42; }
        .loss { color: #b42318; }
        @media (max-width: 800px) {
            .sheet { width: 100%; margin: 0; padding: 14px; }
            .meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .products { display: block; overflow-x: auto; }
        }
        @media print {
            @page { size: landscape; margin: 9mm; }
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { width: 100%; margin: 0; padding: 0; box-shadow: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Imprimir</button></div>
    <main class="sheet">
        <header>
            <div>
                <div class="muted">REPORTE BI DE EXPO</div>
                <h1>Oferta #{{ $oferta['id'] }}</h1>
                <div>{{ $oferta['expo'] }}</div>
            </div>
            <div style="text-align:right">
                <strong>{{ $oferta['estado'] }}</strong><br>
                <span class="muted">Facturación: {{ str_replace('_', ' ', $resumen['estado_facturacion']) }}</span>
            </div>
        </header>

        <section class="meta">
            <div><strong>Cliente</strong>{{ $oferta['cliente'] }}</div>
            <div><strong>RTN</strong>{{ $oferta['rtn'] ?: 'No registrado' }}</div>
            <div><strong>Flujo</strong>{{ $oferta['flujo_id'] ?: 'Sin flujo' }}</div>
            <div><strong>Fecha y hora</strong>{{ $oferta['fecha'] }} {{ $oferta['hora'] }}</div>
            <div><strong>Asesor comercial</strong>{{ $oferta['asesor'] }}</div>
            <div><strong>Teleasesor</strong>{{ $oferta['teleasesor'] }}</div>
            <div><strong>Tipo de venta</strong>{{ $oferta['tipo_venta'] }}</div>
            <div><strong>Condición de pago</strong>{{ $oferta['condicion_pago'] }}</div>
        </section>

        <h2>Productos</h2>
        <div class="products">
            <table>
                <thead>
                    <tr>
                        <th>Código / Producto</th><th>Marca</th><th>Escala</th>
                        <th class="num">Cantidad</th><th class="num">Precio original</th>
                        <th class="num">Descuento</th><th class="num">Precio final</th>
                        <th class="num">Subtotal</th><th class="num">ISV</th>
                        <th class="num">Total</th><th class="num">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto['codigo'] }}<br><strong>{{ $producto['producto'] }}</strong></td>
                            <td>{{ $producto['marca'] }}</td>
                            <td>{{ $producto['escala'] }}</td>
                            <td class="num">{{ number_format($producto['cantidad'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['precio_antes_descuento'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['descuento'], 2) }}<br>{{ number_format($producto['descuento_pct'], 2) }}%</td>
                            <td class="num">L {{ number_format($producto['precio_final'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['subtotal_final'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['isv'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['total'], 2) }}</td>
                            <td class="num">L {{ number_format($producto['costo_total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <table class="summary">
            <tr><td>Subtotal original</td><td class="num">L {{ number_format($resumen['subtotal_original'], 2) }}</td></tr>
            <tr><td>Descuento otorgado</td><td class="num">- L {{ number_format($resumen['descuento'], 2) }}</td></tr>
            <tr><td>Subtotal final</td><td class="num">L {{ number_format($resumen['subtotal_final'], 2) }}</td></tr>
            <tr><td>ISV</td><td class="num">L {{ number_format($resumen['isv'], 2) }}</td></tr>
            <tr><td>Total con impuesto</td><td class="num">L {{ number_format($resumen['total'], 2) }}</td></tr>
            <tr><td>Costo</td><td class="num">L {{ number_format($resumen['costo'], 2) }}</td></tr>
            <tr class="result {{ $resumen['utilidad'] >= 0 ? 'profit' : 'loss' }}">
                <td>{{ $resumen['utilidad'] >= 0 ? 'Ganancia' : 'Pérdida' }}</td>
                <td class="num">{{ $resumen['utilidad'] >= 0 ? '+' : '-' }} L {{ number_format(abs($resumen['utilidad']), 2) }}</td>
            </tr>
            <tr><td>Margen</td><td class="num">{{ $resumen['margen_pct'] === null ? 'N/D' : number_format($resumen['margen_pct'], 2) . '%' }}</td></tr>
        </table>
    </main>
</body>
</html>