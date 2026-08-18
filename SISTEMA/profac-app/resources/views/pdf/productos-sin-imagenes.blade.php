<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Productos sin imágenes</title>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        body{font-family: DejaVu Sans, sans-serif; font-size:12px; color:#1f2937;}
        .header{margin-bottom:14px;}
        .title{font-size:22px; font-weight:800; margin:0;}
        .subtitle{font-size:12px; color:#6b7280; margin-top:4px;}
        .meta{margin:10px 0 16px; padding:10px 12px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;}
        table{width:100%; border-collapse:collapse;}
        th, td{border:1px solid #d1d5db; padding:6px 8px;}
        th{background:#0f172a; color:#fff; font-size:11px; text-transform:uppercase;}
        .text-right{text-align:right;}
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Productos sin imágenes</p>
        <p class="subtitle">Listado de productos que no tienen registros en img_producto.</p>
    </div>

    <div class="meta">
        <strong>Total:</strong> {{ number_format($total) }}
        <span style="margin-left:16px;"><strong>Categoría:</strong> {{ $filtros['categoria'] ?? 'Todas' }}</span>
        <span style="margin-left:16px;"><strong>Marca:</strong> {{ $filtros['marca'] ?? 'Todas' }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Subcategoría</th>
                <th>Marca</th>
                <th class="text-right">Precio base</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row['codigo_referencia'] }}</td>
                    <td>{{ $row['producto'] }}</td>
                    <td>{{ $row['categoria'] }}</td>
                    <td>{{ $row['sub_categoria'] }}</td>
                    <td>{{ $row['marca'] }}</td>
                    <td class="text-right">L {{ number_format((float) $row['precio_base'], 2) }}</td>
                    <td>{{ $row['estado'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No se encontraron productos sin imágenes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>