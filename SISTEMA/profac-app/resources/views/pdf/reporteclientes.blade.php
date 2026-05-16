<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; margin: 0; padding: 6px; }

        /* ── Header ─────────────────────────── */
        .header { display: flex; align-items: center; margin-bottom: 8px; }
        .header img { height: 55px; width: auto; }
        .header-text { flex: 1; text-align: center; }
        .header-text h1 { font-size: 12px; margin: 0 0 2px; color: #1a3a5c; }
        .header-text p  { margin: 1px 0; font-size: 8px; color: #333; }

        /* ── Section title ───────────────────── */
        .section-title {
            background: #1a3a5c; color: #fff;
            font-size: 9px; font-weight: bold;
            padding: 3px 6px; margin: 12px 0 4px;
        }

        /* ── Tables ──────────────────────────── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th {
            background: #1ab394; color: #fff;
            border: 0.5px solid #aaa;
            padding: 2px 2px; text-align: center;
            font-size: 6.5px; white-space: nowrap;
        }
        td {
            border: 0.5px solid #ccc;
            padding: 1px 2px; text-align: center;
            font-size: 6.5px;
        }
        tr:nth-child(even) td { background: #f4fcfa; }
        .text-left  { text-align: left !important; }
        .text-right { text-align: right !important; }

        .badge-x   { color: #1ab394; font-weight: bold; }
        .badge-sol { color: #c0392b; }
        .badge-na  { color: #999; }
    </style>
    <title>Reporte de Clientes</title>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Logo">
        <div class="header-text">
            <h1>DISTRIBUCIONES VALENCIA S.A. DE C.V.</h1>
            <p>RTN: 08011986138652</p>
            <p>REPORTE DE CLIENTES</p>
            <p>Generado: {{ now()->translatedFormat('d \d\e F \d\e Y H:i') }}</p>
        </div>
    </div>

    @php
    function docBadge($val) {
        if ($val === 'X')        return '<span class="badge-x">✔</span>';
        if ($val === 'SOLICITAR') return '<span class="badge-sol">SOLICITAR</span>';
        if ($val === 'N/A')      return '<span class="badge-na">N/A</span>';
        return htmlspecialchars($val ?? '');
    }
    @endphp

    {{-- ═══════════════════ HOJA 1 – GENERAL ════════════════════════ --}}
    <div class="section-title">CLIENTES EN GENERAL</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>AÑO ING.</th>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>COD.</th>
                <th>SOL.CRED.</th>
                <th>COND.CRED.</th>
                <th>ESCRITURA</th>
                <th>DNI REP.</th>
                <th>RTN</th>
                <th>PERMISO</th>
                <th>AÑO OP.</th>
                <th>CROQUIS</th>
                <th>R.BANC.</th>
                <th>R.COM.</th>
                <th>REF.</th>
                <th>T.REL.</th>
                <th>T.CRED.</th>
                <th>LÍM.CRED.</th>
                <th>MÉTODO PAGO</th>
                <th>CONFIRMÓ</th>
                <th>OBS.REF.</th>
                <th>F.VAL.REF.</th>
                <th>REALIZÓ</th>
                <th>L.CAMBIO</th>
                <th>AVAL SOL.</th>
                <th>CONTRATO</th>
                <th>FOTOS</th>
                <th>ESTADO</th>
                <th>MONTO CRED.</th>
                <th>PLAZO</th>
                <th>OBSERVACIONES</th>
                <th>AUT. GER.</th>
                <th>F.NOTIF.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($general as $r)
            <tr>
                <td>{{ $r->item }}</td>
                <td>{{ $r->anio_ingreso }}</td>
                <td class="text-left">{{ $r->vendedor }}</td>
                <td class="text-left">{{ $r->cliente }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{!! docBadge($r->solicitud_credito) !!}</td>
                <td>{!! docBadge($r->condiciones_credito) !!}</td>
                <td>{!! docBadge($r->doc_escritura) !!}</td>
                <td>{!! docBadge($r->doc_dni) !!}</td>
                <td>{!! docBadge($r->doc_rtn) !!}</td>
                <td>{!! docBadge($r->doc_permiso) !!}</td>
                <td>{{ $r->anio_operacion }}</td>
                <td>{!! docBadge($r->doc_croquis) !!}</td>
                <td>{!! docBadge($r->ref_bancarias) !!}</td>
                <td>{!! docBadge($r->ref_comerciales) !!}</td>
                <td class="text-left">{{ $r->ref_referencias }}</td>
                <td>{{ $r->ref_tiempo_relacion }}</td>
                <td>{{ $r->ref_tiempo_credito }}</td>
                <td class="text-right">{{ $r->ref_limite_credito ? 'L '.number_format((float)$r->ref_limite_credito,2) : '' }}</td>
                <td>{{ $r->metodo_pago }}</td>
                <td>{{ $r->confirmacion }}</td>
                <td class="text-left">{{ $r->obs_referencias }}</td>
                <td>{{ $r->fecha_validacion_ref ? \Carbon\Carbon::parse($r->fecha_validacion_ref)->format('d/m/Y') : '' }}</td>
                <td>{{ $r->realizo }}</td>
                <td>{!! docBadge($r->letra_cambio) !!}</td>
                <td>{!! docBadge($r->aval_solidario) !!}</td>
                <td>{!! docBadge($r->doc_contrato) !!}</td>
                <td>{!! docBadge($r->doc_fotos) !!}</td>
                <td>{{ $r->estado_cliente }}</td>
                <td class="text-right">{{ $r->monto_credito > 0 ? 'L '.number_format((float)$r->monto_credito,2) : '' }}</td>
                <td>{{ $r->plazo_credito > 0 ? $r->plazo_credito.' días' : '' }}</td>
                <td class="text-left">{{ $r->observaciones }}</td>
                <td class="text-left">{{ $r->autorizado_gerencia }}</td>
                <td>{{ $r->fecha_notif_limite ? \Carbon\Carbon::parse($r->fecha_notif_limite)->format('d/m/Y') : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════════════════ HOJA 2 – SIN CRÉDITO ════════════════════ --}}
    <div class="section-title">CLIENTES SIN CRÉDITO</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>CÓDIGO</th>
                <th>ESTADO</th>
                <th>OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sinCredito as $r)
            <tr>
                <td>{{ $r->item }}</td>
                <td class="text-left">{{ $r->vendedor }}</td>
                <td class="text-left">{{ $r->cliente }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->estado }}</td>
                <td class="text-left">{{ $r->observaciones }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════════════════ HOJA 3 – GOBIERNO ══════════════════════ --}}
    <div class="section-title">CLIENTES GOBIERNO</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>VENDEDOR</th>
                <th>CLIENTE</th>
                <th>CÓDIGO</th>
                <th>PLAZO CRÉDITO</th>
                <th>ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gobierno as $r)
            <tr>
                <td>{{ $r->item }}</td>
                <td class="text-left">{{ $r->vendedor }}</td>
                <td class="text-left">{{ $r->cliente }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->plazo_credito > 0 ? $r->plazo_credito.' días' : '' }}</td>
                <td>{{ $r->estado_cliente }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer logo --}}
    <div style="text-align:center; margin-top:20px;">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" style="height:40px; opacity:.6" alt="">
    </div>

</body>
</html>
