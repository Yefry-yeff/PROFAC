<!DOCTYPE html>
<html lang="es">
<head>
    <style>
        @page {
            size: letter landscape;
            margin: 18pt;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7pt;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12pt;
        }

        .header img {
            width: 540pt;
            max-width: 100%;
            height: auto;
        }

        .header-text {
            text-align: center;
            margin-top: 6pt;
        }

        .header-text h1 {
            margin: 0;
            font-size: 18px; /* Tamaño del título ajustado */
        }

        .header-text p {
            margin: 2pt 0;
            font-size: 9pt;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 5px 0; /* Reducido para ocupar menos espacio */
            margin-top: 10px;
            font-size: 5.5pt;
            border: 1px solid #ddd;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 2pt 1pt;
            text-align: center;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
            line-height: 1.2;
        }

        table thead {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 100px; /* Espacio reducido */
            text-align: center;
        }

        .footer .signature {
            display: inline-block; /* Asegura que las firmas estén en línea */
            margin: 0 10px; /* Espacio entre las firmas */
        }

        .signature p {
            margin: 5px 0;
        }
    </style>
    <title>Reporte de Libro de Cobros</title>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="">
        <div class="header-text">
            <p>RTN: 08011986138652</p>
            <p>LIBRO GENERAL DE COBROS GOBIERNO</p>
            @php
            use Carbon\Carbon;
            @endphp
            <p>{{ Carbon::parse($fechaInicio)->translatedFormat('d F Y') }} al {{ Carbon::parse($fechaFinal)->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    @if(!empty($tipo) && intval($tipo) === 3)
    {{-- ── Formato tipo 3: agrupado por banco/cuenta ── --}}
    @php
        $fmtL = fn($v) => 'L ' . number_format((float)$v, 2);
        $fmtF = function($d) {
            if (!$d) return '—';
            $p = explode('-', $d);
            return count($p) === 3 ? $p[2].'/'.$p[1].'/'.$p[0] : $d;
        };
        $grandCobrado = $grandExon = $grandGrav = $grandExen = $grandIsv = $grandFact = 0.0;
    @endphp

    @foreach($grouped as $gKey => $group)
        @php $gCobrado = $gExon = $gGrav = $gExen = $gIsv = $gFact = 0.0; @endphp
        <table style="margin-bottom:0;">
            <colgroup>
                @foreach([5, 5, 5, 12, 7, 7, 8, 8, 6, 12, 5, 5, 5, 5, 5] as $ancho)
                    <col style="width:{{ $ancho }}%;">
                @endforeach
            </colgroup>
            <thead>
                <tr>
                    <th colspan="15" style="background:#1F3864;color:#fff;text-align:left;padding:4px 8px;font-size:8px;font-weight:bold;">
                        BANCO: {{ strtoupper($group['banco']) }}   |   CUENTA: {{ strtoupper($group['cuenta']) }}
                    </th>
                </tr>
                <tr>
                    <th>FECHA VENTA</th>
                    <th>FECHA VCTO.</th>
                    <th>FECHA PAGO</th>
                    <th>CLIENTE</th>
                    <th>ASESOR COMERCIAL</th>
                    <th>TELEASESOR</th>
                    <th>N° FACTURA</th>
                    <th>MONTO MOVIMIENTO</th>
                    <th>ESTADO</th>
                    <th>DETALLE DEL MOVIMIENTO</th>
                    <th>EXONERADO</th>
                    <th>GRAVADO</th>
                    <th>EXENTO</th>
                    <th>ISV</th>
                    <th>TOTAL FACTURA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group['rows'] as $row)
                    @php
                        $gCobrado += (float)($row['monto_cobrado'] ?? 0);
                        $gExon    += (float)($row['exonerado']     ?? 0);
                        $gGrav    += (float)($row['gravado']       ?? 0);
                        $gExen    += (float)($row['excento']       ?? 0);
                        $gIsv     += (float)($row['isv']           ?? 0);
                        $gFact    += (float)($row['total_factura'] ?? 0);
                    @endphp
                    <tr>
                        <td>{{ isset($row['fecha_venta']) ? $fmtF($row['fecha_venta']) : '' }}</td>
                        <td>{{ isset($row['fecha_vencimiento']) ? $fmtF($row['fecha_vencimiento']) : '' }}</td>
                        <td>{{ $fmtF($row['fecha_pago'] ?? '') }}</td>
                        <td style="text-align:left;">{{ $row['cliente'] ?? '' }}</td>
                        <td style="text-align:left;">{{ $row['asesor_comercial'] ?? '' }}</td>
                        <td style="text-align:left;">{{ $row['teleasesor'] ?? '' }}</td>
                        <td>{{ $row['factura'] ?? '' }}</td>
                        <td style="text-align:right;">{{ $fmtL($row['monto_cobrado'] ?? 0) }}</td>
                        <td>{{ ($row['estado_factura'] ?? '') === 'PAGADA' ? 'COMPLETO' : ($row['estado_factura'] ?? '') }}</td>
                        <td style="text-align:left;">{{ $row['observaciones'] ?? '' }}</td>
                        <td style="text-align:right;">{{ isset($row['exonerado']) ? $fmtL($row['exonerado']) : '' }}</td>
                        <td style="text-align:right;">{{ isset($row['gravado']) ? $fmtL($row['gravado']) : '' }}</td>
                        <td style="text-align:right;">{{ isset($row['excento']) ? $fmtL($row['excento']) : '' }}</td>
                        <td style="text-align:right;">{{ isset($row['isv']) ? $fmtL($row['isv']) : '' }}</td>
                        <td style="text-align:right;font-weight:bold;">{{ isset($row['total_factura']) ? $fmtL($row['total_factura']) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $grandCobrado += $gCobrado;
                    $grandExon    += $gExon;
                    $grandGrav    += $gGrav;
                    $grandExen    += $gExen;
                    $grandIsv     += $gIsv;
                    $grandFact    += $gFact;
                @endphp
                <tr style="background:#FFF3E0;font-weight:bold;">
                    <td colspan="7" style="text-align:right;font-weight:bold;">Subtotal {{ strtoupper($group['banco']) }}:</td>
                    <td style="text-align:right;">{{ $fmtL($gCobrado) }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;">{{ $fmtL($gExon) }}</td>
                    <td style="text-align:right;">{{ $fmtL($gGrav) }}</td>
                    <td style="text-align:right;">{{ $fmtL($gExen) }}</td>
                    <td style="text-align:right;">{{ $fmtL($gIsv) }}</td>
                    <td style="text-align:right;">{{ $fmtL($gFact) }}</td>
                </tr>
            </tfoot>
        </table>
        <div style="height:8px;"></div>
    @endforeach

    <table>
        <colgroup>
            @foreach([5, 5, 5, 12, 7, 7, 8, 8, 6, 12, 5, 5, 5, 5, 5] as $ancho)
                <col style="width:{{ $ancho }}%;">
            @endforeach
        </colgroup>
        <tfoot>
            <tr style="background:#c05000;color:#fff;font-weight:bold;">
                <td colspan="7" style="text-align:right;font-weight:bold;">TOTAL GENERAL:</td>
                <td style="text-align:right;">{{ $fmtL($grandCobrado) }}</td>
                <td></td>
                <td></td>
                <td style="text-align:right;">{{ $fmtL($grandExon) }}</td>
                <td style="text-align:right;">{{ $fmtL($grandGrav) }}</td>
                <td style="text-align:right;">{{ $fmtL($grandExen) }}</td>
                <td style="text-align:right;">{{ $fmtL($grandIsv) }}</td>
                <td style="text-align:right;">{{ $fmtL($grandFact) }}</td>
            </tr>
        </tfoot>
    </table>

    @else
    {{-- ── Formato legado (SP data) ── --}}
    <table>
        <thead>
            <tr>
                <th>ASESOR COMERCIAL</th>
                <th>CLIENTE</th>
                <th>FACTURA</th>
                <th>EXONERADO</th>
                <th>GRAVADO</th>
                <th>EXCENTO</th>
                <th>ABONO</th>
                <th>SUBTOTAL</th>
                <th>ISV</th>
                <th>TOTAL</th>
                <th>RETENCIÓN</th>
                <th>TOTAL PAGADO</th>
                <th>FECHA COMPRA</th>
                <th>FECHA VENCIMIENTO</th>
                <th>FECHA PAGO</th>
                <th>BANCO</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['VENDEDOR'] }}</td>
                <td>{{ $row['CLIENTE'] }}</td>
                <td>{{ $row['FACTURA'] }}</td>
                <td>{{ number_format((float) $row['EXONERADO'], 2) }}</td>
                <td>{{ $row['GRAVADO'] }}</td>
                <td>{{ number_format((float) $row['EXCENTO'], 2) }}</td>
                <td>{{ number_format((float) $row['ABONO'], 2) }}</td>
                <td>{{ number_format((float) $row['SUBTOTAL'], 2) }}</td>
                <td>{{ number_format((float) $row['ISV'], 2) }}</td>
                <td>{{ number_format((float) $row['TOTAL'], 2) }}</td>
                <td>{{ number_format((float) $row['RETENCION'], 2) }}</td>
                <td>{{ number_format((float) $row['TOTAL PAGADO'], 2) }}</td>
                <td>{{ $row['FECHA DE COMPRA'] }}</td>
                <td>{{ $row['FECHA DE VENCIMIENTO'] }}</td>
                <td>{{ $row['FECHA DE PAGO'] }}</td>
                <td>{{ $row['BANCO'] }}</td>
                <td>{{ $row['OBSERVACIONES'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: center;">Totales:</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['EXONERADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['GRAVADO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['EXCENTO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['ABONO']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['SUBTOTAL']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['ISV']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['TOTAL']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['RETENCION']), 2) }}</th>
                <th style="text-align: center;">{{ number_format((float) collect($data)->sum(fn($row) => (float) $row['TOTAL PAGADO']), 2) }}</th>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        <div class="signature">
            <p>___________________________________</p>
            <p>CREDITOS Y COBROS: {{ strtoupper(Auth::user()->name) }}</p>
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
