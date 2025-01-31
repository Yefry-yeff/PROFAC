<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        body {
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .header img {
            width: 800px; /* Tamaño más grande para el logo */
            height: auto;
        }

        .header-text {
            text-align: center;
            flex-grow: 1;
            margin-left: 10px;
        }

        .header-text h1 {
            margin: 0;
            font-size: 20px; /* Tamaño del título ajustado */
        }

        .header-text p {
            margin: 0;
            font-size: 14px; /* Legibilidad mejorada */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0; /* Reducido para ocupar menos espacio */
            margin-top:10px;
            font-size: 10px; /* Tamaño de fuente más pequeño */
            border: 1px solid #ddd;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 1px; /* Menor espacio entre celdas */
            text-align: center;
        }

        table thead {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 200px;
            text-align: center;
        }

        .footer .signature {
            display: inline-block; /* Asegura que las firmas estén en línea */
            margin: 0 20px; /* Espacio entre las firmas */
        }

        .signature p {
            margin: 5px 0;
        }
    </style>
    <title>Facturas Anuladas</title>
</head>
<body>
    <div class="header">
        <img src="img/membrete/Logo3.png" style="margin-left:10%; margin-top:-25px; position:absolute;"alt="">
        <div class="header-text"style="margin-left:10%;  margin-top:60px; width:45rem; height:5.5rem;">
            <p>RTN: 08011986138652</p>
            <p>LISTADO DE FACTURAS ANULADAS</p>
            @php
                use Carbon\Carbon;
            @endphp
            <p>{{ Carbon::parse($fechaInicio)->translatedFormat('d F Y') }}   al   {{ Carbon::parse($fechaFinal)->translatedFormat('d F Y') }}</p>
        </div>
    </div>

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
                <td>{{ $row['SUBTOTAL'] }}</td>
                <td>{{ $row['ISV'] }}</td>
                <td>{{ $row['TOTAL'] }}</td>
                <td>{{ $row['TIPO CLIENTE'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>___________________________________</p>
            <p>CREDITOS Y COBROS: JOSSELINE ZEPEDA</p>
        </div>
        <div class="signature">
        </div>
        <div class="signature">
            <p>___________________________________</p>
            <p>GERENTE ADMINISTRATIVO: LIC. EILEEN RODRIGUEZ</p>
        </div>
    </div>
</body>
</html>
