<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#263238; font-size:10px; }
        h1 { margin:0 0 5px; font-size:20px; }
        .meta { margin-bottom:18px; color:#546e7a; }
        table { width:100%; border-collapse:collapse; }
        th { background:#e65100; color:#fff; padding:7px; text-align:left; }
        td { border:1px solid #dfe3e8; padding:6px; }
        tr:nth-child(even) td { background:#f7f8fa; }
    </style>
</head>
<body>
    <h1>Lista de Asistencia: {{ $expo->nombre }}</h1>
    <div class="meta">
        Inicio: {{ date('d/m/Y H:i', strtotime($expo->fecha_inicio)) }} ·
        Fin: {{ $expo->fecha_fin ? date('d/m/Y H:i', strtotime($expo->fecha_fin)) : 'Sin fecha de cierre' }} ·
        Total: {{ $asistentes->count() }} cliente(s)
    </div>
    <table>
        <thead><tr><th>ID</th><th>Cliente</th><th>RTN</th><th>Teléfono</th><th>Correo</th><th>Registrado</th><th>Usuario</th></tr></thead>
        <tbody>
            @forelse($asistentes as $cliente)
                <tr><td>{{ $cliente->id }}</td><td>{{ $cliente->nombre }}</td><td>{{ $cliente->rtn }}</td><td>{{ $cliente->telefono_empresa }}</td><td>{{ $cliente->correo }}</td><td>{{ date('d/m/Y H:i', strtotime($cliente->registrado_at)) }}</td><td>{{ $cliente->registrado_por }}</td></tr>
            @empty
                <tr><td colspan="7">No hay clientes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>