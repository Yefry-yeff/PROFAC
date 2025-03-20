<!DOCTYPE html>
<html>
<head>
    <title>Facturas Anuladas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        p {
            text-align: center;
            margin: 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
            border: 1px solid #ddd;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }

        table thead {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
        }

        .footer .signature {
            display: inline-block;
            width: 45%;
            margin: 0 2.5%;
            text-align: center;
            vertical-align: top;
        }

        .signature p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Listado de Facturas Anuladas</h1>
    <p>Desde: {{ $fechaInicio }} Hasta: {{ $fechaFinal }}</p>
    <table>
        <thead>
            <tr>
                <th>FECHA DE CREACION</th>
                <th>NUMERO FACTURA</th>
                <th>NOMBRE CLIENTE</th>
                <th>SUBTOTAL</th>
                <th>ISV</th>
                <th>TOTAL</th>
                <th>TIPO CLIENTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['FECHA DE CREACION'] }}</td>
                    <td>{{ $row['NUMERO FACTURA'] }}</td>
                    <td>{{ $row['NOMBRE CLIENTE'] }}</td>
                    <td>{{ number_format((float) $row['SUBTOTAL'], 2) }}</td>
                    <td>{{ number_format((float) $row['ISV'], 2) }}</td>
                    <td>{{ number_format((float) $row['TOTAL'], 2) }}</td>
                    <td>{{ $row['TIPO CLIENTE'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: center;">Totales:</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['SUBTOTAL']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['ISV']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['TOTAL']), 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">

        <div class="signature">
            <tr></tr>
            <tr></tr>
        </div>
        <div class="signature">
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align: center;">___________________________________</th>
                    <th colspan="5" style="text-align: center;">___________________________________</th>
                </tr>
            </tfoot>
        </div>
        <div class="signature">
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align: center;">CREDITOS Y COBROS: {{ strtoupper(Auth::user()->name) }}</</th>
                    <th colspan="5" style="text-align: center;">GERENTE ADMINISTRATIVO: LIC. EILEEN RODRIGUEZ</th>
                </tr>
            </tfoot>
        </div>
    </div>
</body>
</html>
