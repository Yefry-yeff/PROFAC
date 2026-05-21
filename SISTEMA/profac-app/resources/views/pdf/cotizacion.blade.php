<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        /* ── Márgenes de página ── */
        @page {
            margin-top: 230px;
            margin-bottom: 35px;
            margin-left: 0;
            margin-right: 0;
        }

        /* ── Header fijo (repite en todas las páginas) ── */
        #pdf-header {
            position: fixed;
            top: -230px;
            left: 0;
            right: 0;
            width: 100%;
        }
        #pdf-header img { width: 100%; display: block; }
        /* Mismos márgenes laterales que el body */
        #pdf-header .hdr-cards { margin: 3px 22px 0 22px; }

        /* ── Número de página ── */
        #pdf-footer {
            position: fixed;
            bottom: -28px;
            right: 22px;
            font-size: 8px;
            color: #555;
        }
        #pdf-footer .pagenum:before { content: "Página "; }
        #pdf-footer .pagenum:after  { content: counter(page) " / " counter(pages); }

        p { font-size: 10px; margin: 0; }

        /* Body con el mismo margen lateral que .hdr-cards */
        body {
            margin: 0;
            padding: 0 22px 50px 22px;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
        }
        th, td { text-align: left; padding: 2px; }
        thead { background-color: #f2f2f2; }
        .letra { font-weight: 800; }
    </style>
    <title>OFERTA</title>
</head>

<body>

{{-- ── Header fijo: logo + oferta + datos cliente ── --}}
<div id="pdf-header">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Distribuciones Valencia">
    <div class="hdr-cards">
        {{-- Oferta No. --}}
        <div class="card border border-dark" style="margin-bottom:3px;">
            <div class="card-header" style="position:relative; padding:5px 10px;">
                <b>Oferta No. {{ $datos->codigo }}</b>
                <b style="position:absolute; right:10px; top:5px;">Oferta de: {{ ucfirst($datos->tipo_pago ?? 'contado') }}</b>
            </div>
            <div class="card-body" style="padding:4px 10px;">
                <p><b>Registro tributario: 08011986138652</b></p>
            </div>
        </div>
        {{-- Datos del cliente --}}
        <div class="card border border-dark">
            <div class="card-body" style="padding:4px 10px;">
                <table style="border:none; font-size:10px;">
                    <tr>
                        <td style="width:58%; vertical-align:top; border:none; padding:0;">
                            <p style="margin:0 0 2px;"><b>Cliente:</b> {{ $datos->nombre }} - ({{ $datos->clienteId }})</p>
                            <p style="margin:0 0 2px;"><b>Dirección:</b> {{ $datos->direccion }}</p>
                            <p style="margin:0 0 2px;"><b>Correo:</b> {{ $datos->correo }} &nbsp;&nbsp; <b>Teléfono:</b> {{ $datos->telefono_empresa }}</p>
                            <p style="margin:0;"><b>Notas:</b> {{ $datos->nota }}</p>
                        </td>
                        <td style="width:42%; vertical-align:top; border:none; border-left:1px solid #ccc; padding:0 0 0 10px;">
                            <p style="margin:0 0 2px;"><b>Fecha:</b> {{ $datos->fecha_emision }}</p>
                            <p style="margin:0 0 2px;"><b>Hora:</b> {{ $datos->hora }}</p>
                            <p style="margin:0 0 2px;"><b>Vence:</b> {{ $datos->fecha_vencimiento }}</p>
                            <p style="margin:0;"><b>RTN:</b> {{ $datos->rtn }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Número de página ── --}}
<div id="pdf-footer"><span class="pagenum"></span></div>

{{-- ── Productos ── --}}
<div style="margin-top:4px;">
    <table style="font-size:10px;">
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Medida</th>
                <th>Precio</th>
                <th>Exento</th>
                <th>Cantidad</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
            <tr>
                <td>{{ $producto->codigo }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->medida }}</td>
                <td>{{ $producto->precio }}</td>
                <td>{{ $producto->excento }}</td>
                <td>{{ $producto->cantidad }}</td>
                <td>{{ $producto->importe }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ── Notas + Totales ── --}}
<table style="border:none; margin-top:6px; font-size:10px;">
    <tr>
        <td style="width:57%; vertical-align:top; border:none; padding:0 4px 0 0;">
            <div class="card border border-dark">
                <div class="card-body" style="padding:4px 8px;">
                    <p style="margin:0 0 1px; font-size:10px;"><b>Vendedor:</b> {{ $datos->vendedor }} &nbsp; <b>Cotizador:</b> {{ $datos->cotizador }}</p>
                    <p style="margin:0 0 2px; font-size:10px;"><b>Trámite #{{ $datos->flujo_id ?? '—' }}</b></p>
                    <hr style="margin:2px 0; border-top:1px solid #999;">
                    @if(!empty($datos->nota))
                    <p style="margin:0; font-size:9px;">{{ $datos->nota }}</p>
                    @endif
                </div>
            </div>
        </td>
        <td style="width:43%; vertical-align:top; border:none; padding:0 0 0 4px;">
            <div class="card border border-dark">
                <div class="card-body" style="padding:4px 8px;">
                    <table style="border:none; font-size:10px;">
                        <tr>
                            <td style="border:none; padding:1px 0;">Importe exonerado:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Importe Gravado 15%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total_grabado }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Importe Gravado 18%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Importe Exento:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total_excento }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Desc. y Rebajas {{ $importes->porc_descuento }}%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->monto_descuento }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Sub Total:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Impuesto sobre venta 15%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->isv }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:1px 0;">Impuesto sobre venta 18%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:3px 0 1px; border-top:1px solid #999;"><b>Total a Pagar:</b></td>
                            <td style="border:none; padding:3px 0 1px; text-align:right; border-top:1px solid #999;"><b>L. {{ $importesConCentavos->total }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border:none; padding:2px 0 0; font-size:8px;">
                                <b>@if ($flagCentavos == false)"{{ $numeroLetras . ' CON CERO CENTAVOS' }}"@else"{{ $numeroLetras }}"@endif</b>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- ── Firmas ── --}}
<p style="margin:4px 0 0; font-size:8px; text-align:right;">Original: Cliente, Copia obligado tributario emisor.</p>

<div style="margin-top:70px;">
    <table style="border:none; font-size:9px;">
        <tr>
            <td style="width:50%; border:none; vertical-align:top; padding:0 20px 0 0;">
                <p style="margin:0; border-top:1px solid #000; padding-top:3px; word-wrap:break-word;">Cliente: {{ strtoupper($datos->nombre) }}</p>
                <p style="margin:4px 0 0;">Recibido por: _______________________</p>
                <p style="margin:4px 0 0;">DNI: ________________________________</p>
                <p style="margin:4px 0 0;">Cargo: ______________________________</p>
                <p style="margin:4px 0 0;">Telefono: ___________________________</p>
                <p style="margin:5px 0 0;"><b>*Se requiere firma y sello de recibido.*</b></p>
            </td>
            <td style="width:50%; border:none; vertical-align:top; padding:0 0 0 20px; text-align:center;">
                <p style="margin:0; border-top:1px solid #000; padding-top:3px; text-align:center;">DISTRIBUCIONES VALENCIA</p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

        /* ── Header fijo: logo + oferta + cliente (repite en cada página) ── */
        #pdf-header {
            position: fixed;
            top: -270px;
            left: 0;
            right: 0;
            width: 100%;
        }
        #pdf-header img { width: 100%; display: block; }

        /* ── Número de página en el pie ── */
        #pdf-footer {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 8px;
            color: #555;
        }
        #pdf-footer .pagenum:before { content: "Página "; }
        #pdf-footer .pagenum:after  { content: counter(page) " / " counter(pages); }

        .color-red { color: red; }
        p { font-size: 10px; }
        body {
            margin-left: -95px;
            padding: 0 50px 50px 50px;
            width: 45rem;
            height: 3rem;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
        }
        th, td {
            text-align: left;
            padding: 2px;
        }
        thead { background-color: #f2f2f2 }
        .letra { font-weight: 800; }
    </style>
    <title>OFERTA</title>
</head>

<body>

{{-- ── Header fijo: logo + oferta + datos cliente (repite en cada página) ── --}}
<div id="pdf-header">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" alt="Distribuciones Valencia">
    {{-- Oferta No. --}}
    <div class="card border border-dark" style="margin-left:44px; margin-top:3px; width:45rem;">
        <div class="card-header">
            <b>Oferta No. {{ $datos->codigo }}</b>
            <b style="position:absolute;right:10px">Oferta de: {{ ucfirst($datos->tipo_pago ?? 'contado') }}</b>
        </div>
        <div class="card-body">
            <p style="position:absolute;left:20px; top:50px;"><b>Registro tributario: 08011986138652</b></p>
        </div>
    </div>
    {{-- Datos del cliente --}}
    <div class="card border border-dark" style="margin-left:44px; margin-top:3px; width:45rem;">
        <div class="card-body" style="padding:4px 10px;">
            <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                <tr>
                    <td style="width:58%; vertical-align:top; padding:0; border:none;">
                        <p style="margin:0 0 2px;"><b>Cliente:</b> {{ $datos->nombre }} - ({{ $datos->clienteId }})</p>
                        <p style="margin:0 0 2px;"><b>Dirección:</b> {{ $datos->direccion }}</p>
                        <p style="margin:0 0 2px;"><b>Correo:</b> {{ $datos->correo }} &nbsp;&nbsp; <b>Teléfono:</b> {{ $datos->telefono_empresa }}</p>
                        <p style="margin:0;"><b>Notas:</b> {{ $datos->nota }}</p>
                    </td>
                    <td style="width:42%; vertical-align:top; padding:0 0 0 10px; border:none; border-left:1px solid #ccc;">
                        <p style="margin:0 0 2px;"><b>Fecha:</b> {{ $datos->fecha_emision }}</p>
                        <p style="margin:0 0 2px;"><b>Hora:</b> {{ $datos->hora }}</p>
                        <p style="margin:0 0 2px;"><b>Vence:</b> {{ $datos->fecha_vencimiento }}</p>
                        <p style="margin:0;"><b>RTN:</b> {{ $datos->rtn }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- ── Número de página fijo en el pie ── --}}
<div id="pdf-footer">
    <span class="pagenum"></span>
</div>

@php
    $altura = 200;
    $altura2 = 320;
    $contadorFilas = 0;
    $contPe = 0;
    $p1 = 24;
    $p2 = 30;
    $vueltasTabla = 0;
@endphp

<div class="pruebaFondo">

    {{-- ── Productos ───────────────────────────────────────────────── --}}
    @php $cant = count($productos); @endphp
    <div style="position: relative; margin-left:44px; margin-top:4px; width:45rem;">
        <div>
            <table style="font-size: 10px;">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Medida</th>
                        <th>Precio</th>
                        <th>Exento</th>
                        <th>Cantidad</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr>
                            <td>{{ $producto->codigo }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->medida }}</td>
                            <td>{{ $producto->precio }}</td>
                            <td>{{ $producto->excento }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>{{ $producto->importe }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Notas + Totales ─────────────────────────────────────────── --}}
    <div style="position: relative; margin-left:44px; margin-top:6px; width:26rem; height:14rem;">

        <div class="card border border-dark" style="position:absolute;left:0px; width:26rem;">
            <div class="card-body" style="padding:4px 8px;">
                <p style="margin:0 0 1px; font-size:10px;"><b>Vendedor:</b> {{ $datos->vendedor }} &nbsp; <b>Cotizador:</b> {{ $datos->cotizador }}</p>
                <p style="margin:0 0 2px; font-size:10px;"><b>Trámite #{{ $datos->flujo_id ?? '—' }}</b></p>
                <hr style="margin:2px 0; border-top:1px solid #999;">
                @if(!empty($datos->nota))
                <p style="margin:0; font-size:9px;">{{ $datos->nota }}</p>
                @endif
            </div>
        </div>

        <div class="card border border-dark" style="position:absolute;left:430px; width:18rem;">
            <div class="card-body" style="padding:4px 8px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr>
                        <td style="border:none; padding:1px 0;">Importe exonerado:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Importe Gravado 15%:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total_grabado }}</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Importe Gravado 18%:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Importe Exento:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total_excento }}</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Desc. y Rebajas {{ $importes->porc_descuento }}%:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->monto_descuento }}</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Sub Total:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->sub_total }}</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Impuesto sobre venta 15%:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. {{ $importesConCentavos->isv }}</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:1px 0;">Impuesto sobre venta 18%:</td>
                        <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                    </tr>
                    <tr>
                        <td style="border:none; padding:3px 0 1px; border-top:1px solid #999;"><b>Total a Pagar:</b></td>
                        <td style="border:none; padding:3px 0 1px; text-align:right; border-top:1px solid #999;"><b>L. {{ $importesConCentavos->total }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border:none; padding:2px 0 0; font-size:8px;">
                            <b>@if ($flagCentavos == false)"{{ $numeroLetras . ' CON CERO CENTAVOS' }}"@else"{{ $numeroLetras }}"@endif</b>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    {{-- ── Firmas ──────────────────────────────────────────────────── --}}
    <p style="margin:4px 44px 0; font-size:8px; text-align:right;">Original: Cliente, Copia obligado tributario emisor.</p>

    <div style="margin-left:44px; margin-top:70px; width:45rem;">
        <table style="width:100%; border:none; border-collapse:collapse; font-size:9px;">
            <tr>
                <td style="width:50%; border:none; vertical-align:top; padding:0 20px 0 0;">
                    <p style="margin:0; border-top:1px solid #000; padding-top:3px; word-wrap:break-word; overflow-wrap:break-word;">Cliente: {{ strtoupper($datos->nombre) }}</p>
                    <p style="margin:4px 0 0;">Recibido por: _______________________</p>
                    <p style="margin:4px 0 0;">DNI: ________________________________</p>
                    <p style="margin:4px 0 0;">Cargo: ______________________________</p>
                    <p style="margin:4px 0 0;">Telefono: ___________________________</p>
                    <p style="margin:5px 0 0;"><b>*Se requiere firma y sello de recibido.*</b></p>
                </td>
                <td style="width:50%; border:none; vertical-align:top; padding:0 0 0 20px; text-align:center;">
                    <p style="margin:0; border-top:1px solid #000; padding-top:3px; text-align:center;">DISTRIBUCIONES VALENCIA</p>
                </td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
