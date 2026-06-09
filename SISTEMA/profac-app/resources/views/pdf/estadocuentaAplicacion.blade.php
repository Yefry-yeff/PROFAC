<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 28px 32px 20px 32px;
        }

        /* ── Encabezado ── */
        .header-wrap {
            width: 100%;
            margin-bottom: 18px;
        }
        .header-logo img {
            max-height: 70px;
        }

        /* ── Cuadro título ── */
        .title-box {
            border: 1px solid #333;
            border-radius: 3px;
            padding: 8px 16px;
            margin-bottom: 10px;
        }
        .title-box .title-center {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .title-box .title-meta {
            display: table;
            width: 100%;
        }
        .title-box .title-meta .meta-left {
            display: table-cell;
            font-size: 11px;
            font-weight: bold;
        }
        .title-box .title-meta .meta-right {
            display: table-cell;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        /* ── Tabla de datos ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }
        thead tr {
            background-color: #f0f0f0;
        }
        th {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            padding: 6px 5px;
            border: 1px solid #333;
            white-space: nowrap;
        }
        td {
            font-size: 10px;
            padding: 5px 6px;
            border: 1px solid #888;
            vertical-align: middle;
        }
        td.center { text-align: center; }
        td.num    { text-align: right;  white-space: nowrap; }
        tr:nth-child(even) td { background-color: #fafafa; }

        /* ── Firmas ── */
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .sig-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        .sig-table td {
            border: none;
            padding: 0;
            width: 50%;
            text-align: center;
            font-size: 10px;
        }
        .sig-line {
            display: block;
            border-top: 1px solid #333;
            width: 70%;
            margin: 0 auto 4px auto;
        }
        .sig-name {
            display: block;
            font-weight: bold;
            font-size: 10px;
            max-width: 240px;
            margin: 0 auto;
        }
    </style>
    <title>ESTADO DE CUENTA</title>
</head>
<body>
@php
    $fecha_actual = date('d/m/Y');
@endphp

{{-- Logo --}}
<div class="header-wrap">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" style="max-width:100%; max-height:75px;" alt="Logo">
</div>

{{-- Cuadro título + cliente + fecha --}}
<div class="title-box">
    <div class="title-center">ESTADO DE CUENTA</div>
    <div class="title-meta">
        <span class="meta-left">Cliente: {{ $nombreCliente }}</span>
        <span class="meta-right">Fecha: {{ $fecha_actual }}</span>
    </div>
</div>

@if($sinMovimientos)
<div style="text-align:center; padding:40px; font-size:14px; color:#555;">
    Este cliente no tiene facturas pendientes en el estado de cuenta.
</div>
@else
<table>
    <thead>
        <tr>
            <th>Factura</th>
            <th>No. Compra</th>
            <th>Fecha<br>Emisión</th>
            <th>Fecha<br>Vencimiento</th>
            <th>Cargo</th>
            <th>Crédito</th>
            <th>Extras</th>
            <th>Débitos</th>
            <th>Notas<br>Crédito</th>
            <th>Notas<br>Débito</th>
            <th>Saldo</th>
            <th>Acumulado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($estadoCuenta as $valor)
        <tr>
            <td class="center">{{ $valor->correlativo }}</td>
            <td class="center">{{ $valor->numOrden }}</td>
            <td class="center">{{ $valor->fecha_emision }}</td>
            <td class="center">{{ $valor->fecha_vencimiento }}</td>
            <td class="num">L. {{ number_format($valor->cargo,      2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->credito,    2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->extra,      2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->debita,     2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->notaCredito,2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->notaDebito, 2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->saldo,      2, '.', ',') }}</td>
            <td class="num">L. {{ number_format($valor->acumulado,  2, '.', ',') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Firmas --}}
<div class="signatures">
    <table class="sig-table">
        <tr>
            <td>
                <span class="sig-line"></span>
                <span class="sig-name">{{ $nombreCliente }}</span>
            </td>
            <td>
                <span class="sig-line"></span>
                <span class="sig-name">DISTRIBUCIONES VALENCIA</span>
            </td>
        </tr>
    </table>
</div>
@endif

</body>
</html>
