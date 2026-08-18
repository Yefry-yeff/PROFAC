<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        @page {
            margin: 280px 28px 38px 28px;
        }

        .color-red { color: red; }
        p { font-size: 10px; }
        body { margin: 0; padding: 0; width: 100%; }
        table { border-collapse: collapse; border-spacing: 0; width: 100%; }
        .card-body { position: static !important; }
        th, td { text-align: left; padding: 2px; }
        thead { background-color: #f2f2f2; display: table-header-group; }

        #encabezado-fijo {
            position: fixed;
            top: -272px;
            left: 0;
            right: 0;
            width: 100%;
            font-size: 10px;
        }

        .letra { font-weight: 800; }
        tbody td { border: none; }
        tbody tr { page-break-inside: avoid; }
    </style>
    <title>VALE COPIA</title>
</head>

<body>

    @php
        $usuarioImpresion = auth()->check() ? auth()->user()->name : 'Usuario';
        $fechaImpresion   = now()->format('d/m/Y H:i');
    @endphp

    <div id="encabezado-fijo">

        <img src="{{ public_path('img/membrete/Logo3.png') }}"
             style="display:block; width:100%; margin-bottom:2px;" alt="">

        <div class="card border border-dark" style="margin-left:0; margin-top:2px; width:100%;">
            <div class="card-header" style="padding:3px 8px; display:table; width:100%; box-sizing:border-box;">
                <b style="display:table-cell; text-align:left;">Vale No. {{ $vale->numero_vale }}</b>
                <b style="display:table-cell; text-align:center;"> *Copia* </b>
                <b style="display:table-cell; text-align:right;">Factura N&deg;: {{ $vale->cai }}</b>
            </div>
            <div class="card-body" style="padding:4px 10px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr>
                        <td style="border:none; padding:1px 0;"><b>Registro tributario: 08011986138652</b></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border border-dark" style="margin-left:0; margin-top:4px; width:100%;">
            <div class="card-body" style="padding:4px 10px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr>
                        <td style="width:58%; vertical-align:top; padding:0; border:none;">
                            <p style="margin:0 0 2px;"><b>Cliente:</b> {{ $vale->nombre_cliente }}</p>
                            <p style="margin:0 0 2px;"><b>Direcci&oacute;n:</b> {{ $cliente->direccion }}</p>
                            <p style="margin:0 0 2px;"><b>Correo:</b> {{ $cliente->correo }} &nbsp;&nbsp; <b>Tel&eacute;fono:</b> {{ $cliente->telefono_empresa }}</p>
                            <p style="margin:0;"><b>Notas:</b> Entrega Pendiente</p>
                        </td>
                        <td style="width:42%; vertical-align:top; padding:0 0 0 10px; border:none; border-left:1px solid #ccc;">
                            <p style="margin:0 0 2px;"><b>Fecha:</b> {{ $vale->fecha_emision }}</p>
                            <p style="margin:0 0 2px;"><b>Hora:</b> {{ $vale->hora }}</p>
                            <p style="margin:0 0 2px;"><b>RTN:</b> {{ $cliente->rtn }}</p>
                        </td>
                    </tr>
                </table>
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px; margin-top:3px; border-top:1px solid #ccc;">
                    <tr>
                        <td style="width:33%; border:none; padding:2px 0 1px;"><b>Correlativo de Ord. exenta</b></td>
                        <td style="width:34%; border:none; padding:2px 0 1px; text-align:center;"><b>Constancia de registro exonerado</b></td>
                        <td style="width:33%; border:none; padding:2px 0 1px; text-align:right;"><b>Identificativo del registro de la SAG</b></td>
                    </tr>
                    <tr>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                        <td style="border:none; height:14px; border-bottom:1px solid #aaa;"></td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    <div class="pruebaFondo">
        @php $cant = count($productos); @endphp

        <table style="font-size:9px; width:100%; border-collapse:collapse; border: 1px solid #000; margin-top:8px;">
            <thead>
                <tr>
                    <th>C&oacute;digo</th>
                    <th>Descripci&oacute;n</th>
                    <th>Medida</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->descripcion }}</td>
                        <td>{{ $producto->medida }}</td>
                        <td>{{ $producto->precio }}</td>
                        <td>{{ $producto->cantidad }}</td>
                        <td>{{ $producto->importe }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="position: relative; margin-top:6px; width:100%; min-height:14rem;">

            <div class="card border border-dark" style="position:absolute;left:0; width:57%;">
                <div class="card-body" style="padding:4px 8px;">
                    <p style="margin:0 0 1px; font-size:10px;"><b>Asesor comercial:</b> {{ $vale->name }}</p>
                    <hr style="margin:2px 0; border-top:1px solid #999;">
                    @if ($vale->estado_factura == 1)
                        <p style="margin:0; font-size:9px;">N{{ $vale->numero_factura }}-CF11</p>
                    @else
                        <p style="margin:0; font-size:9px;">N{{ $vale->numero_factura }}-CF12</p>
                    @endif
                    @if ($flagCentavos == false)
                        <p style="margin:4px 0 0; font-size:9px;">"{{ $numeroLetras . ' CON CERO CENTAVOS' }}"</p>
                    @else
                        <p style="margin:4px 0 0; font-size:9px;">"{{ $numeroLetras }}"</p>
                    @endif
                </div>
            </div>

            <div class="card border border-dark" style="position:absolute;right:0; width:41%;">
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
                            <td style="border:none; padding:1px 0;">Desc. y Rebajas {{ $importesConCentavos->porc_descuento }}%:</td>
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
                            <td style="border:none; padding:1px 0;">Impuesto sobre bebida 18%:</td>
                            <td style="border:none; padding:1px 0; text-align:right;">L. 0.00</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding:3px 0 1px; border-top:1px solid #999;"><b>Total a Pagar:</b></td>
                            <td style="border:none; padding:3px 0 1px; text-align:right; border-top:1px solid #999;"><b>L. {{ $importesConCentavos->total }}</b></td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($vale->estado_id_vale == 2)
            <div style="position:fixed; top:30%; left:0; width:100%; text-align:center; transform:rotate(-45deg); opacity:0.18; z-index:9999;">
                <p style="font-size:90px; font-weight:900; color:#cc0000; letter-spacing:8px; margin:0;">VALE ANULADO</p>
            </div>
            @elseif($vale->estado_id_vale == 5)
            <div style="position:fixed; top:30%; left:0; width:100%; text-align:center; transform:rotate(-45deg); opacity:0.18; z-index:9999;">
                <p style="font-size:90px; font-weight:900; color:#cc0000; letter-spacing:8px; margin:0;">VALE ELIMINADO</p>
            </div>
            @endif

        </div>

        <div style="margin-top:70px; width:100%;">
            <table style="width:100%; border:none; border-collapse:collapse; font-size:9px;">
                <tr>
                    <td style="width:50%; border:none; vertical-align:top; padding:0 20px 0 0;">
                        <p style="margin:0; border-top:1px solid #000; padding-top:3px; word-wrap:break-word; overflow-wrap:break-word;">Cliente: {{ strtoupper($vale->nombre_cliente) }}</p>
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

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font('Helvetica', 'normal');
            $size = 7;
            $y    = $pdf->get_height() - 20;
            $pdf->page_text(28, $y, '{{ addslashes($usuarioImpresion) }} {{ $fechaImpresion }}', $font, $size, [0,0,0]);
            $pdf->page_text($pdf->get_width() - 80, $y, 'Página {PAGE_NUM} | {PAGE_COUNT}', $font, $size, [0,0,0]);
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>