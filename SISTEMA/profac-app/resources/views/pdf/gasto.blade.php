<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Gasto G-{{ $gasto->id }}</title>
    <style>
        @page { size:letter portrait; margin:230px 28px 38px; }
        body { font-family:DejaVu Sans,sans-serif; font-size:9px; color:#263238; }
        header { position:fixed; top:-210px; left:0; right:0; }
        .logo { width:100%; height:70px; object-fit:contain; }
        h1 { font-size:16px; margin:5px 0; }
        p { margin:3px 0; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th,td { padding:5px 3px; border-bottom:1px solid #dfe5e7; overflow-wrap:anywhere; word-wrap:break-word; }
        th { font-size:7px; background:#edf3f1; }
        thead { display:table-header-group; }
        tr { page-break-inside:avoid; }
        .number { text-align:right; font-size:8px; }
        .totals { margin-top:12px; width:52%; margin-left:48%; }
        .totals td { font-size:10px; }
        .grand { background:#edf3f1; font-weight:bold; }
        footer { position:fixed; bottom:-24px; left:0; right:0; font-size:7px; color:#607d8b; }
        .cancelled { color:#b71c1c; font-weight:bold; }
    </style>
</head>
<body>
    <header>
        <img class="logo" src="{{ public_path('img/membrete/Logo3.png') }}" alt="Membrete">
        <h1>COMPROBANTE DE GASTO G-{{ $gasto->id }}</h1>
        <p><strong>Factura del proveedor:</strong> {{ $gasto->numero_factura }} | <strong>CAI:</strong> {{ $gasto->cai ?: 'No indicado' }}</p>
        <p><strong>Proveedor:</strong> {{ $gasto->proveedor }} | <strong>RTN:</strong> {{ $gasto->rtn }}</p>
        <p><strong>Dirección:</strong> {{ $gasto->direccion }}</p>
        <p><strong>Emisión:</strong> {{ $gasto->fecha_emision }} | <strong>Recibido:</strong> {{ $gasto->fecha_recepcion }} | <strong>Vencimiento:</strong> {{ $gasto->fecha_vencimiento }}</p>
        <p><strong>Reporte:</strong> {{ $gasto->incluir_reporte_compras ? 'Compras' : 'Gastos' }} | <strong>Registrado por:</strong> {{ $gasto->usuario }}</p>
        @if ($gasto->estado === 'anulado') <p class="cancelled">GASTO ANULADO — {{ $gasto->motivo_anulacion }}</p> @endif
    </header>
    <table>
        <thead><tr>
            <th style="width:23%">Producto / concepto</th><th style="width:9%">Unidad</th><th style="width:6%">Cant.</th>
            <th style="width:11%">Precio</th><th style="width:7%">Desc. %</th><th style="width:11%">Descuento</th>
            <th style="width:11%">Subtotal neto</th><th style="width:5%">ISV %</th><th style="width:8%">ISV</th><th style="width:9%">Total</th>
        </tr></thead>
        <tbody>@foreach ($lineas as $linea)
            <tr>
                <td>{{ $linea->nombre }}</td><td>{{ $linea->unidad_medida ?? 'No indicada' }}</td><td class="number">{{ $linea->cantidad }}</td>
                <td class="number">{{ number_format($linea->precio, 2) }}</td><td class="number">{{ number_format($linea->descuento_porcentaje, 2) }}%</td>
                <td class="number">{{ number_format($linea->descuento_monto, 2) }}</td><td class="number">{{ number_format($linea->subtotal, 2) }}</td>
                <td class="number">{{ $linea->isv_porcentaje }}%</td><td class="number">{{ number_format($linea->isv, 2) }}</td><td class="number">{{ number_format($linea->total, 2) }}</td>
            </tr>
        @endforeach</tbody>
    </table>
    <table class="totals">
        <tr><td>Subtotal bruto</td><td class="number">L. {{ number_format($gasto->subtotal + $lineas->sum('descuento_monto'), 2) }}</td></tr>
        <tr><td>Descuentos</td><td class="number">L. {{ number_format($lineas->sum('descuento_monto'), 2) }}</td></tr>
        <tr><td>Subtotal neto</td><td class="number">L. {{ number_format($gasto->subtotal, 2) }}</td></tr>
        <tr><td>ISV</td><td class="number">L. {{ number_format($gasto->isv, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL DEL GASTO</td><td class="number">L. {{ number_format($gasto->total, 2) }}</td></tr>
    </table>
    <p>Documento de registro de gasto. No sustituye la factura fiscal del proveedor.</p>
    <footer>Impreso por {{ auth()->user()?->name }} el {{ now()->format('d/m/Y H:i') }} | Registrado: {{ $gasto->created_at }}</footer>
</body>
</html>
