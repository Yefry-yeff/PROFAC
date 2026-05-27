@php echo '<?' . 'xml version="1.0" encoding="UTF-8"?>'; @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Catálogo de Productos – Oferta #{{ $oferta->id }}</title>
@php
    ini_set('memory_limit', '512M');
    set_time_limit(180);

    // Pre-resolver rutas absolutas de imágenes (1 por producto)
    $imgReady = [];
    foreach ($imagenes as $pid => $urls) {
        foreach (array_slice($urls, 0, 1) as $url) {
            $abs = public_path('catalogo/' . $url);
            if (file_exists($abs)) { $imgReady[$pid] = $abs; break; }
        }
    }

    // Sin chunking: 1 producto por fila para layout imagen-izquierda/info-derecha
@endphp
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: "DejaVu Sans", sans-serif;
        font-size: 8.5px;
        color: #1a1a2e;
        background: #fff;
        padding: 0 22px 22px;
    }

    /* ── BANNER ──────────────────────────────────────────── */
    .banner { margin: 0 -22px 0; }
    .banner img { width: 100%; display: block; }

    /* ── TITLE BAR ───────────────────────────────────────── */
    .title-bar { background:#e65100; color:#fff; padding:7px 14px; margin:0 -22px 12px; }
    .title-bar table { width:100%; border-collapse:collapse; }
    .tb-title { font-size:13px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; vertical-align:middle; }
    .tb-meta  { text-align:right; font-size:7.5px; color:rgba(255,255,255,.8); vertical-align:middle; line-height:1.6; }

    /* ── OFFER BAND ──────────────────────────────────────── */
    .offer-band { border:1px solid #e8d5c8; border-left:4px solid #e65100; background:#fdf5f0; padding:5px 10px; margin-bottom:12px; }
    .offer-band table { width:100%; border-collapse:collapse; }
    .offer-band td { padding:2px 6px; font-size:8px; vertical-align:top; }
    .ob-lbl { font-weight:700; color:#c0420a; white-space:nowrap; width:72px; }

    /* ── SECTION HEADER ──────────────────────────────────── */
    .sec-head { background:#1a1a2e; color:#fff; padding:5px 10px; font-size:8px; font-weight:700;
                letter-spacing:.4px; text-transform:uppercase; margin-bottom:10px; }
    .sec-count { background:#e65100; border-radius:10px; padding:1px 7px; font-size:8px; margin-left:6px; }

    /* ── PRODUCT CARD (imagen izquierda · info derecha) ─── */
    .card { width:100%; border-collapse:collapse; border:1px solid #dde3ee;
            margin-bottom:10px; page-break-inside:avoid; }

    /* Celda imagen (izquierda) */
    .card-img-cell { width:220px; vertical-align:top; padding:0;
                     border-right:3px solid #e65100; background:#f0f2f8; }
    .card-img-cell img { width:220px; height:190px; display:block; }
    .card-no-img-cell { width:220px; height:190px; background:#f0f2f8;
                        text-align:center; padding-top:70px;
                        color:#bbb; font-size:8px; vertical-align:top; }

    /* Celda info (derecha) */
    .card-info-cell { vertical-align:top; padding:0; }

    /* Nombre del producto */
    .card-name { background:#1a1a2e; color:#fff; padding:6px 10px;
                 font-size:8.5px; font-weight:700; line-height:1.3; }
    .card-code { color:#f9a826; font-size:7px; font-weight:400; display:block; margin-top:2px; }

    /* Cuerpo info */
    .card-body { padding:6px 10px 0; }
    .card-desc { font-size:7.5px; color:#777; font-style:italic;
                 border-bottom:1px dashed #e8eaf0; padding-bottom:5px;
                 margin-bottom:5px; line-height:1.5; }
    .card-specs { width:100%; border-collapse:collapse; }
    .card-specs td { font-size:8px; padding:2px 4px; color:#333; }
    .card-specs .sl { font-weight:700; color:#777; white-space:nowrap; width:62px; }

    /* Franja precio */
    .card-price { background:#fdf5f0; border-top:1px solid #e8d5c8;
                  margin-top:6px; padding:5px 10px; }
    .card-price table { width:100%; border-collapse:collapse; }
    .card-price td { text-align:center; font-size:8px; vertical-align:top; }
    .cp-lbl { font-size:7px; color:#999; display:block; }
    .cp-val { font-weight:700; color:#1a1a2e; }
    .cp-total .cp-val { color:#e65100; font-size:10px; }
    .cp-sep { border-right:1px dashed #ddd; }

    /* ── TOTALS ──────────────────────────────────────────── */
    .totals-wrap { margin-top:14px; page-break-inside:avoid; }
    .totals-table { width:240px; margin-left:auto; border-collapse:collapse; border:1px solid #e8eaf0; }
    .totals-table td { padding:3px 10px; font-size:8.5px; }
    .tl { color:#555; text-align:right; padding-right:12px; }
    .tv { text-align:right; font-weight:700; width:80px; }
    .tot-row td { background:#e65100; color:#fff; font-size:10px; font-weight:700; padding:5px 10px; }
    .alt-row { background:#fdf5f0; }

    /* ── FOOTER ──────────────────────────────────────────── */
    .page-footer { margin-top:14px; border-top:1px solid #dde3ee; padding-top:5px;
                   font-size:7px; color:#aaa; text-align:center; }
</style>
</head>
<body>

{{-- ══ BANNER ═══════════════════════════════════════════════════════════ --}}
<div class="banner">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Valencia">
</div>

{{-- ══ TITLE BAR ════════════════════════════════════════════════════════ --}}
<div class="title-bar">
    <table>
        <tr>
            <td class="tb-title">Catálogo de Productos</td>
            <td class="tb-meta">
                Oferta #{{ $oferta->id }} &nbsp;&bull;&nbsp; Generado {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                Creado por: <strong>{{ $oferta->registrado_por ?? '&#8212;' }}</strong>
                &nbsp;&bull;&nbsp;
                Descargado por: <strong>{{ $descargadoPor }}</strong>
            </td>
        </tr>
    </table>
</div>

{{-- ══ OFERTA INFO ══════════════════════════════════════════════════════ --}}
<div class="offer-band">
    <table>
        <tr>
            <td class="ob-lbl">Cliente:</td>
            <td>{{ $oferta->nombre_cliente }}</td>
            <td class="ob-lbl">RTN:</td>
            <td>{{ $oferta->RTN ?? '&#8212;' }}</td>
            @if (!empty($oferta->registrado_por))
            <td class="ob-lbl">Asesor:</td>
            <td>{{ $oferta->registrado_por }}</td>
            @endif
        </tr>
        <tr>
            <td class="ob-lbl">Emisi&oacute;n:</td>
            <td>{{ !empty($oferta->fecha_emision) ? \Carbon\Carbon::parse($oferta->fecha_emision)->format('d/m/Y') : '&#8212;' }}</td>
            <td class="ob-lbl">Vencimiento:</td>
            <td>{{ !empty($oferta->fecha_vencimiento) ? \Carbon\Carbon::parse($oferta->fecha_vencimiento)->format('d/m/Y') : '&#8212;' }}</td>
            <td colspan="2"></td>
        </tr>
    </table>
</div>

{{-- ══ SECCIÓN PRODUCTOS ════════════════════════════════════════════════ --}}
<div class="sec-head">
    Productos incluidos
    <span class="sec-count">{{ count($productos) }}</span>
</div>

@foreach ($productos as $prod)
@php $imgPath = $imgReady[$prod->id] ?? null; @endphp
<table class="card">
    <tr>
        {{-- IMAGEN (izquierda, protagonista) --}}
        @if ($imgPath)
        <td class="card-img-cell">
            <img src="{{ $imgPath }}" alt="">
        </td>
        @else
        <td class="card-no-img-cell">Sin imagen</td>
        @endif

        {{-- INFO (derecha) --}}
        <td class="card-info-cell">
            <div class="card-name">
                {{ $prod->nombre }}
                <span class="card-code">C&oacute;d: {{ $prod->codigo_estatal ?? $prod->codigo_barra ?? '&#8212;' }}</span>
            </div>
            <div class="card-body">
                @if (!empty($prod->descripcion))
                <div class="card-desc">{{ \Str::limit($prod->descripcion, 220) }}</div>
                @endif
                <table class="card-specs">
                    <tr>
                        <td class="sl">Marca:</td>
                        <td>{{ $prod->marca ?? '&#8212;' }}</td>
                        <td class="sl">Categor&iacute;a:</td>
                        <td>{{ $prod->categoria ?? '&#8212;' }}</td>
                    </tr>
                    <tr>
                        <td class="sl">Unidad:</td>
                        <td>{{ $prod->medida ?? '&#8212;' }}</td>
                        <td class="sl">ISV:</td>
                        <td>{{ $prod->tipo_isv ?? '&#8212;' }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-price">
                <table>
                    <tr>
                        <td class="cp-sep">
                            <span class="cp-lbl">Cantidad</span>
                            <span class="cp-val">{{ $prod->cantidad }}</span>
                        </td>
                        <td class="cp-sep">
                            <span class="cp-lbl">Precio Unit.</span>
                            <span class="cp-val">L {{ $prod->precio }}</span>
                        </td>
                        <td class="cp-total">
                            <span class="cp-lbl">Total l&iacute;nea</span>
                            <span class="cp-val">L {{ $prod->sub_total }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
@endforeach

{{-- ══ TOTALES ══════════════════════════════════════════════════════════ --}}
<div class="totals-wrap">
    <table class="totals-table">
        <tr class="alt-row">
            <td class="tl">Subtotal:</td>
            <td class="tv">L {{ number_format($oferta->sub_total ?? 0, 2) }}</td>
        </tr>
        @if (($oferta->monto_descuento ?? 0) > 0)
        <tr>
            <td class="tl">Descuento ({{ $oferta->porc_descuento }}%):</td>
            <td class="tv" style="color:#c0392b;">- L {{ number_format($oferta->monto_descuento, 2) }}</td>
        </tr>
        @endif
        <tr class="alt-row">
            <td class="tl">ISV:</td>
            <td class="tv">L {{ number_format($oferta->isv ?? 0, 2) }}</td>
        </tr>
        <tr class="tot-row">
            <td class="tl">TOTAL:</td>
            <td class="tv">L {{ number_format($oferta->total ?? 0, 2) }}</td>
        </tr>
    </table>
</div>

{{-- ══ FOOTER ═══════════════════════════════════════════════════════════ --}}
<div class="page-footer">
    Cat&aacute;logo generado autom&aacute;ticamente &nbsp;&bull;&nbsp; Oferta #{{ $oferta->id }} &nbsp;&bull;&nbsp; {{ $oferta->nombre_cliente }}
</div>

</body>
</html>
