<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de traslado {{ $transfer->reference }}</title>
    <style>
        @page { margin: 28px; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #243247; }
        .letterhead { width: 100%; height: auto; margin-bottom: 18px; }
        h1 { margin: 0 0 5px; font-size: 19px; color: #172b50; }
        h2 { margin: 20px 0 8px; font-size: 12px; color: #172b50; }
        .subtitle { margin: 0; font-size: 12px; color: #64748b; }
        .header { border-bottom: 3px solid #ef8c23; padding-bottom: 14px; }
        .status { display: inline-block; padding: 5px 10px; margin-top: 10px; border-radius: 6px; background: #edf2f7; font-weight: bold; }
        .status-pending { background: #fff3d7; color: #925600; }
        .status-received { background: #e4f5eb; color: #166534; }
        .status-rejected { background: #fee8e8; color: #991b1b; }
        .metadata { width: 100%; border-collapse: collapse; margin: 14px 0; }
        .metadata td { padding: 5px 0; vertical-align: top; width: 50%; }
        .identifier { font-size: 8px; color: #64748b; overflow-wrap: break-word; }
        .products { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .products th { padding: 8px 6px; background: #172b50; color: #fff; text-align: left; font-size: 9px; }
        .products td { padding: 9px 6px; border-bottom: 1px solid #dbe3ec; vertical-align: top; overflow-wrap: break-word; }
        .products tbody tr:nth-child(even) { background: #f7f9fc; }
        .code { width: 12%; }
        .quantity { width: 12%; text-align: right; }
        .lot { width: 12%; }
        .expiration { width: 15%; }
        .summary { padding: 10px; margin-top: 12px; background: #f1f5f9; text-align: right; font-weight: bold; }
        .observation { margin-top: 14px; padding: 10px; border: 1px solid #dbe3ec; }
        .signatures { width: 100%; margin-top: 45px; table-layout: fixed; }
        .signatures td { width: 50%; padding: 0 22px; text-align: center; }
        .signature-line { border-top: 1px solid #94a3b8; padding-top: 7px; }
        .note { margin-top: 20px; color: #64748b; font-size: 9px; text-align: center; }
        tr, .signatures { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php
        $labels =         ['pending' => 'Pendiente de recepción', 'dispatched' => 'Enviado', 'partial' => 'Parcialmente recibido', 'received' => 'Recibido',
            'rejected' => 'Rechazado', 'returned' => 'Devuelto'];
        $linesByUuid = collect($data['lines'])->keyBy('line_uuid');
    @endphp
    <img src="{{ public_path('img/membrete/Logo3.png') }}" class="letterhead" alt="Distribuciones Valencia">
    <div class="header">
        <h1>Comprobante de traslado</h1>
        <p class="subtitle">Paperland → Distribuciones Valencia</p>
        <span class="status {{ $transfer->status === 'pending' ? 'status-pending' : ($transfer->status === 'received' ? 'status-received' : ($transfer->status === 'rejected' ? 'status-rejected' : '')) }}">{{ $labels[$transfer->status] ?? $transfer->status }}</span>
    </div>
    <table class="metadata">
        <tr><td colspan="2"><strong>Referencia:</strong> {{ $transfer->reference }}</td></tr>
        <tr><td><strong>Origen:</strong> Paperland</td><td><strong>Destino:</strong> Distribuciones Valencia</td></tr>
        <tr><td><strong>Fecha de registro:</strong> {{ \Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i') }}</td><td><strong>Productos:</strong> {{ count($data['lines']) }}</td></tr>
        <tr><td colspan="2" class="identifier"><strong>UUID:</strong> {{ $transfer->transfer_uuid }}</td></tr>
    </table>
    <table class="products">
        <thead><tr><th class="code">Cód. Paperland</th><th class="code">Cód. Distribuciones Valencia</th><th>Producto</th><th class="quantity">Unidades base</th><th class="lot">Lote origen</th><th class="expiration">Vencimiento</th></tr></thead>
        <tbody>
            @foreach($data['lines'] as $line)
                <tr>
                    <td>{{ $line['product_pos_id'] ?? 'No informado' }}</td>
                    <td>{{ $line['product_profac_id'] }}</td>
                    <td>{{ $line['name'] }}<div class="identifier">Factor de origen: {{ $line['source_factor'] ?? 'No informado' }}</div></td>
                    <td class="quantity">{{ $line['quantity_base'] }}</td>
                    <td>{{ $line['source_lot_id'] }}</td>
                    <td>{{ !empty($line['expires_at']) ? \Carbon\Carbon::parse($line['expires_at'])->format('d/m/Y') : 'Sin vencimiento' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="summary">Total unidades base: {{ collect($data['lines'])->sum('quantity_base') }}</div>
    @if(!empty($data['comment']))<div class="observation"><strong>Comentario:</strong> {{ $data['comment'] }}</div>@endif
    @foreach($movements as $movement)
        @if($movement->type === 'transfer.received')
            <h2>Distribución recibida en Distribuciones Valencia</h2>
            <table class="products">
                <thead><tr><th>Producto</th><th>Lote destino</th><th>Presentación</th><th>Factor</th><th>Unidades base</th><th>Sección</th></tr></thead>
                <tbody>
                    @foreach(json_decode($movement->snapshot, true, 512, JSON_THROW_ON_ERROR)['allocations'] as $a)
                        <tr>
                            <td>{{ $linesByUuid->get($a['line_uuid'])['name'] ?? $a['line_uuid'] }}</td>
                            <td>{{ $a['lot_id'] }}</td><td>{{ $a['presentation_id'] }}</td><td>{{ $a['factor'] }}</td>
                            <td class="quantity">{{ $a['quantity_base'] }}</td><td>{{ $a['seccion_id'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
    @if($transfer->reason)<div class="observation"><strong>Motivo del rechazo:</strong> {{ $transfer->reason }}</div>@endif
    <table class="signatures"><tr><td><div class="signature-line">Entrega · Paperland</div></td><td><div class="signature-line">Recepción · Distribuciones Valencia</div></td></tr></table>
    <p class="note">Documento de movimiento de inventario. No es una factura de compra o venta.<br>
        La impresión no confirma la recepción ni modifica existencias.</p>
</body>
</html>
