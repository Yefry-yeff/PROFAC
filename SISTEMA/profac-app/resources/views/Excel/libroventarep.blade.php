<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Libro de Ventas</title>
</head>
<body>
    <h2>DISTRIBUCIONES VALENCIA, S.A. &nbsp;&nbsp;&nbsp; RTN: 08011986138652</h2>
    <p>LIBRO GENERAL DE VENTAS DE GOBIERNO</p>
    @php use Carbon\Carbon; @endphp
    <p>Período: {{ Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ Carbon::parse($fechaFinal)->format('d/m/Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>FACTURA</th>
                <th>EXONERADO</th>
                <th>GRAVADO</th>
                <th>EXCENTO</th>
                <th>SUBTOTAL</th>
                <th>ISV</th>
                <th>TOTAL</th>
                <th>FECHA COMPRA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['VENDEDOR'] }}</td>
                <td>{{ $row['CLIENTE'] }}</td>
                <td>{{ $row['FACTURA'] }}</td>
                <td>{{ number_format((float) $row['EXONERADO'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $row['GRAVADO'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $row['EXCENTO'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $row['SUBTOTAL'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $row['ISV'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $row['TOTAL'], 2, '.', '') }}</td>
                <td>{{ $row['FECHA COMPRA'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">TOTALES:</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['EXONERADO']), 2, '.', '') }}</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['GRAVADO']), 2, '.', '') }}</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['EXCENTO']), 2, '.', '') }}</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['SUBTOTAL']), 2, '.', '') }}</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['ISV']), 2, '.', '') }}</th>
                <th>{{ number_format(collect($data)->sum(fn($r) => (float) $r['TOTAL']), 2, '.', '') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
