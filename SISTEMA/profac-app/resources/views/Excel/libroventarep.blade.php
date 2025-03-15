<!DOCTYPE html>
<html>
<head>
    <title>Libro de Ventas</title>
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
    <h1>Libro de Ventas</h1>
    <p>Desde: {{ $fechaInicio }} Hasta: {{ $fechaFinal }}</p>
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
                    <td>{{ number_format((float) $row['EXONERADO'], 2) }}</td>
                    <td>{{ number_format((float) $row['GRAVADO'], 2) }}</td>
                    <td>{{ number_format((float) $row['EXCENTO'], 2) }}</td>
                    <td>{{ number_format((float) $row['SUBTOTAL'], 2) }}</td>
                    <td>{{ number_format((float) $row['ISV'], 2) }}</td>
                    <td>{{ number_format((float) $row['TOTAL'], 2) }}</td>
                    <td>{{ $row['FECHA COMPRA'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: center;">Totales:</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['EXONERADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['GRAVADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['EXCENTO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['SUBTOTAL']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['ISV']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['TOTAL']), 2) }}</th>
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
            <p>GERENCIA GENERAL: ING. DANILO MORALES</p>
        </div>
        <div class="signature">
            <p>___________________________________</p>
            <p>GERENTE ADMINISTRATIVO: LIC. EILEEN RODRIGUEZ</p>
        </div>
    </div>
</body>
</html>