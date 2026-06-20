<!DOCTYPE html>
<html>
<head>
    <title>Proyección Comisiones</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .resultado { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .status { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Proyección Comisiones - Mayo 2026</h1>
    
    @if(isset($error))
        <div class="status error">❌ Error: {{ $error }}</div>
    @else
        <div class="status success">✓ Proyección completada</div>
        
        <div class="resultado">
            <h2>📋 RESULTADO 1: Resumen por Empleado</h2>
            @if(count($resultado1) > 0)
                <table>
                    <tr>
                        <th>Período</th>
                        <th>Capacidad</th>
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th>Facturas</th>
                        <th>Comisión Bruta</th>
                        <th>Retención</th>
                        <th>Comisión Neta</th>
                    </tr>
                    @foreach($resultado1 as $row)
                        <tr>
                            <td>{{ $row->periodo_pago }}</td>
                            <td>{{ $row->capacidad }}</td>
                            <td>{{ $row->empleado }}</td>
                            <td>{{ $row->rol_nombre }}</td>
                            <td>{{ $row->facturas_proyectadas }}</td>
                            <td>${{ number_format($row->comision_bruta_total, 2) }}</td>
                            <td>${{ number_format($row->retencion_mora_total, 2) }}</td>
                            <td>${{ number_format($row->comision_neta_mora_total, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="error">Sin datos para mayo 2026</p>
            @endif
        </div>

        <div class="resultado">
            <h2>📑 RESULTADO 2: Detalle por Factura (primeras 20)</h2>
            @if(count($resultado2) > 0)
                <table>
                    <tr>
                        <th>CAI</th>
                        <th>Factura</th>
                        <th>Capacidad</th>
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th>Comisión Bruta</th>
                        <th>Comisión Neta</th>
                    </tr>
                    @foreach($resultado2 as $row)
                        <tr>
                            <td>{{ $row->cai }}</td>
                            <td>{{ $row->factura_id }}</td>
                            <td>{{ $row->capacidad }}</td>
                            <td>{{ $row->empleado }}</td>
                            <td>{{ $row->rol_nombre }}</td>
                            <td>${{ number_format($row->comision_bruta, 2) }}</td>
                            <td>${{ number_format($row->comision_neta, 2) }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p class="error">Sin datos para mayo 2026</p>
            @endif
        </div>

        <div class="resultado">
            <h2>💰 RESULTADO 3: Totales</h2>
            @if($resultado3)
                <table>
                    <tr>
                        <th>Total Facturas</th>
                        <th>Comisión Total</th>
                    </tr>
                    <tr>
                        <td>{{ $resultado3->total_facturas }}</td>
                        <td>${{ number_format($resultado3->comision_total, 2) }}</td>
                    </tr>
                </table>
            @endif
        </div>
    @endif
</div>
</body>
</html>
