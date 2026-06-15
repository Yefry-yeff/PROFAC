<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; margin: 0; padding: 6px; }

        /* ── Header ─────────────────────── */
        .header { display: flex; align-items: center; margin-bottom: 8px; }
        .header img { height: 55px; width: auto; }
        .header-text { flex: 1; text-align: center; }
        .header-text h1 { font-size: 12px; margin: 0 0 2px; color: #1a3a5c; }
        .header-text p  { margin: 1px 0; font-size: 8px; color: #333; }

        /* ── Agrupación mes ─────────────── */
        .mes-titulo {
            background: #1a3a5c; color: #fff;
            font-size: 9px; font-weight: bold;
            padding: 3px 6px; margin: 10px 0 3px;
            text-transform: uppercase;
        }

        /* ── Tabla ──────────────────────── */
        table  { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th {
            background: #1ab394; color: #fff;
            border: 0.5px solid #aaa;
            padding: 2px 2px; text-align: center;
            font-size: 6px; white-space: nowrap;
        }
        td {
            border: 0.5px solid #ccc;
            padding: 1px 2px; font-size: 6px;
            text-align: center;
        }
        tr:nth-child(even) td { background: #f4fcfa; }
        .text-left  { text-align: left !important; }
        .text-right { text-align: right !important; }

        /* ── Estado crédito badges ───────── */
        .badge-vigente   { color: #1ab394; font-weight: bold; }
        .badge-vencida   { color: #c0392b; font-weight: bold; }
        .badge-cancelada { color: #27ae60; font-weight: bold; }
        .badge-contado   { color: #2980b9; font-weight: bold; }

        /* ── Columnas solo lectura ───────── */
        .readonly { background: #f0f0f0 !important; color: #555; font-style: italic; }
    </style>
    <title>Reporte de Ventas y Cobros</title>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Logo">
        <div class="header-text">
            <h1>DISTRIBUCIONES VALENCIA</h1>
            <p>RTN: 08011986138652</p>
            <p><strong>REPORTE DE VENTAS Y COBROS</strong></p>
            <p>Generado: {{ now()->translatedFormat('d \d\e F \d\e Y H:i') }}</p>
        </div>
    </div>

    @php
    function estadoBadge($estado) {
        return match($estado) {
            'Vencida'   => '<span class="badge-vencida">Vencida</span>',
            'Cancelada' => '<span class="badge-cancelada">Cancelada</span>',
            'Contado'   => '<span class="badge-contado">Contado</span>',
            default     => '<span class="badge-vigente">Vigente</span>',
        };
    }
    function lps($v) {
        return $v != 0 ? 'L ' . number_format($v, 2) : '-';
    }
    function fdate($d) {
        return $d ? date('d/m/Y', strtotime($d)) : '-';
    }
    @endphp

    @php
    /* Agrupar por mes/año */
    $grupos = [];
    foreach ($rows as $r) {
        $clave = strtoupper($r->mes . ' ' . $r->anio);
        $grupos[$clave][] = $r;
    }
    @endphp

    @foreach($grupos as $titulo => $facturas)
    <div class="mes-titulo">{{ $titulo }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>FACTURA</th>
                <th>OBSERVACIÓN</th>
                <th>ORD. COMPRA</th>
                <th>MODO PAGO</th>
                <th>ESTADO F01</th>
                <th>EXONERADO</th>
                <th>GRAVADO</th>
                <th>EXENTO</th>
                <th>ABONOS</th>
                <th>DETALLE ABONOS</th>
                <th>SUBTOTAL</th>
                <th>ISV</th>
                <th>TOTAL</th>
                <th class="readonly">SALDO PEND.</th>
                <th>MONTO PAG.</th>
                <th>MONTO RET.</th>
                <th>NRO RET.</th>
                <th>F. VENTA</th>
                <th>F. VCTO.</th>
                <th class="readonly">DÍAS VCTOS.</th>
                <th>ESTADO CRED.</th>
                <th>F. PAGO</th>
                <th>FORMA PAGO</th>
                <th>CUENTA/BANCO</th>
                <th>F. ENTREGA</th>
                <th>RECIBO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $r)
            <tr>
                <td>{{ $r->item }}</td>
                <td class="text-left">{{ $r->vendedor }}</td>
                <td class="text-left">{{ $r->cliente }}</td>
                <td>{{ $r->numero_secuencia_cai }}</td>
                <td class="text-left">{{ $r->observacion }}</td>
                <td>{{ $r->orden_compra }}</td>
                <td>{{ $r->modo_pago }}</td>
                <td>{{ $r->estado_f01 }}</td>
                <td class="text-right">{{ $r->exonerado > 0 ? lps($r->exonerado) : '-' }}</td>
                <td class="text-right">{{ $r->gravado   > 0 ? lps($r->gravado)   : '-' }}</td>
                <td class="text-right">{{ $r->exento    > 0 ? lps($r->exento)    : '-' }}</td>
                <td class="text-right">{{ $r->abonos    > 0 ? lps($r->abonos)    : '-' }}</td>
                <td class="text-left">{{ $r->detalle_abonos ?? 'No aplica' }}</td>
                <td class="text-right">{{ lps($r->sub_total) }}</td>
                <td class="text-right">{{ lps($r->isv) }}</td>
                <td class="text-right">{{ lps($r->total) }}</td>
                <td class="text-right readonly">{{ lps($r->saldo_pendiente) }}</td>
                <td class="text-right">{{ $r->monto_pagado > 0 ? lps($r->monto_pagado) : '-' }}</td>
                <td class="text-right">{{ lps($r->monto_retencion ?? 0) }}</td>
                <td class="text-left">{{ $r->numero_retencion ?? 'No aplica' }}</td>
                <td>{{ fdate($r->fecha_venta) }}</td>
                <td>{{ fdate($r->fecha_vencimiento) }}</td>
                <td class="readonly">{{ $r->dias_vencidos }} días</td>
                <td>{!! estadoBadge($r->creditos_vencidos) !!}</td>
                <td>{{ fdate($r->fecha_pago) }}</td>
                <td class="text-left">{{ $r->forma_pago }}</td>
                <td class="text-left">{{ $r->cuenta_banco }}</td>
                <td>{{ fdate($r->fecha_entrega) }}</td>
                <td>{{ $r->recibo }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

</body>
</html>
