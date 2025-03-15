<!DOCTYPE html>
<html>
<head>
    <title>Cierre Diario de Caja</title>
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
    <h1>DITRIBUCIONES VALENCIA</h1>
    <p>CIERRE DE CAJA</p>
    <p>Desde: {{ $fechaInicio }} Hasta: {{ $fechaFinal }}</p>
    <table>
        <thead>
            <tr>
                <th>FECHA DE CIERRE</th>
                <th>REGISTRADO POR</th>
                <th>ESTADO DE CAJA</th>
                <th>FACTURA</th>
                <th>CLIENTE</th>
                <th>VENDEDOR</th>
                <th>SUBTOTAL FACTURADO</th>
                <th>ISV FACTURADO</th>
                <th>TOTAL FACTURADO</th>
                <th>CALIDAD FACTURA</th>
                <th>TIPO CLIENTE</th>
                <th>PAGO POR</th>
                <th>BANCO</th>
                <th>FECHA PAGO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['FECHA DE CIERRE'] }}</td>
                    <td>{{ $row['REGISTRADO POR'] }}</td>
                    <td>{{ $row['ESTADO DE CAJA'] }}</td>
                    <td>{{ $row['FACTURA'] }}</td>
                    <td>{{ $row['CLIENTE'] }}</td>
                    <td>{{ $row['VENDEDOR'] }}</td>
                    <td>{{ number_format((float) $row['SUBTOTAL FACTURADO'], 2) }}</td>
                    <td>{{ number_format((float) $row['ISV FACTURADO'], 2) }}</td>
                    <td>{{ number_format((float) $row['TOTAL FACTURADO'], 2) }}</td>
                    <td>{{ $row['CALIDAD DE FACTURA'] }}</td>
                    <td>{{ $row['TIPO DE CLIENTE'] }}</td>
                    <td>{{ $row['PAGO POR'] }}</td>
                    <td>{{ $row['BANCO'] }}</td>
                    <td>{{ $row['FECHA DE PAGO'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" style="text-align: center;">Totales:</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['SUBTOTAL FACTURADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['ISV FACTURADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['TOTAL FACTURADO']), 2) }}</th>
                <th colspan="4"></th>
               <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="signature">
            <p>___________________________________</p>
            <p>CREDITOS Y COBROS: JOSSELINE ZEPEDA</p>
        </div>
        <div class="signature">
            <p>___________________________________</p>
            <p>GERENTE ADMINISTRATIVO: LIC. EILEEN RODRIGUEZ</p>
        </div>
    </div>
</body>
</html>
