<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        @page { margin: 320px 28px 38px 28px; }
        p { font-size: 10px; }
        body { margin: 0; padding: 0; width: 100%; }

        #encabezado-fijo {
            position: fixed;
            top: -300px;
            left: 0;
            right: 0;
            width: 100%;
        }

        .prefactura-logo { display: block; width: 100%; margin-bottom: 2px; }
        .prefactura-card { margin-top: 4px; width: 100%; }
        .prefactura-card .card-body { position: static !important; }
        .prefactura-productos { margin-top: 8px; width: 100%; }
        .prefactura-resumen { position: relative; margin-top: 6px; width: 100%; min-height: 168px; }
        .prefactura-notas { width: 57%; }
        .prefactura-totales { position: absolute; top: 0; right: 0; width: 41%; }
        .prefactura-firmas { margin-top: 70px; width: 100%; }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
        }

        th,
        td {
            text-align: left;
            padding: 2px;
        }

        thead {
            background-color: #f2f2f2;
        }
    </style>
    <title>PREFACURA</title>
</head>

<body>
@php
    $validez = $datos->descripcion_validez ?? (($datos->dias_validez ?? 0) . ' dias');
@endphp

<div>
    <div id="encabezado-fijo">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" class="prefactura-logo" alt="">

    <div class="card border border-dark prefactura-card" style="margin-top:2px;">
        <div class="card-header">
            <b style="font-size:13px;">PRE-FACTURA No. {{ $datos->codigo }}</b>
            <b style="position:absolute;right: 270px"> *Original* </b>
            <b style="position:absolute;right: 10px; font-size:13px;">Factura de: {{ ucfirst($tipoPagoDescripcion ?? 'contado') }}</b>
        </div>

        <div class="card-body" style="padding:4px 10px;">
            <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                <tr>
                    <td style="border:none; padding:1px 0;"><b>Registro tributario: 08011986138652</b></td>
                    <td style="border:none; padding:1px 0; text-align:right;"><b>CAI: N/A</b></td>
                </tr>
                <tr>
                    <td style="border:none; padding:1px 0;"><b>Fecha límite de emisión: N/A</b></td>
                    <td style="border:none; padding:1px 0; text-align:right;"><b>Rango autorizado: N/A</b></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card border border-dark prefactura-card">
        <div class="card-body" style="padding:4px 10px;">
            <table style="width:100%; border:none; border-collapse:collapse; font-size:11px;">
                <tr>
                    <td style="width:58%; vertical-align:top; padding:0; border:none;">
                        <p style="margin:0 0 2px;"><b>Cliente:</b> {{ $datos->nombre }} - ({{ $datos->clienteId }})</p>
                        <p style="margin:0 0 2px;"><b>Direccion:</b> {{ $datos->direccion }}</p>
                        <p style="margin:0 0 2px;"><b>Correo:</b> {{ $datos->correo }} &nbsp;&nbsp; <b>Telefono:</b> {{ $datos->telefono_empresa }}</p>
                        <p style="margin:0;"><b>Notas:</b> {{ $datos->nota }}</p>
                    </td>
                    <td style="width:42%; vertical-align:top; padding:0 0 0 10px; border:none; border-left:1px solid #ccc;">
                        <p style="margin:0 0 2px;"><b>Fecha:</b> {{ $datos->fecha_emision }}</p>
                        <p style="margin:0 0 2px;"><b>Hora:</b> {{ $datos->hora }}</p>
                        <p style="margin:0 0 2px;"><b>Vence:</b> {{ $datos->fecha_vencimiento }}</p>
                        <p style="margin:0 0 2px;"><b>RTN:</b> {{ $datos->rtn }}</p>
                        <p style="margin:0 0 2px;"><b>Orden N°:</b> {{ $ordenCompraPref !== '' ? $ordenCompraPref : 'N/A' }}</p>
                    </td>
                </tr>
            </table>
            <table style="width:100%; border:none; border-collapse:collapse; font-size:11px; margin-top:3px; border-top:1px solid #ccc;">
                <tr>
                    <td style="width:33%; border:none; padding:2px 0 1px;"><b>Correlativo de Ord. exenta</b></td>
                    <td style="width:34%; border:none; padding:2px 0 1px; text-align:center;"><b>Constancia de registro exonerado</b></td>
                    <td style="width:33%; border:none; padding:2px 0 1px; text-align:right;"><b>Identificativo del registro de la SAG</b></td>
                </tr>
                <tr>
                    <td style="border:none; height:14px; border-bottom:1px solid #aaa;">{{ $ordenCompraPref !== '' ? strtoupper($ordenCompraPref) : '' }}</td>
                    <td style="border:none; height:14px; border-bottom:1px solid #aaa; text-align:center;">{{ $constanciaExonerado !== '' ? strtoupper($constanciaExonerado) : '' }}</td>
                    <td style="border:none; height:14px; border-bottom:1px solid #aaa;">{{ $registroSag !== '' ? strtoupper($registroSag) : '' }}</td>
                </tr>
            </table>
        </div>
    </div>
    </div>

    <div class="prefactura-productos">
        <table style="font-size:10px;">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Producto</th>
                    <th>Bodega</th>
                    <th>Seccion</th>
                    <th>Medida</th>
                    <th>Exento</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->codigo }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->bodega }}</td>
                    <td>{{ $producto->seccion }}</td>
                    <td>{{ $producto->medida }}</td>
                    <td>{{ $producto->excento }}</td>
                    <td>{{ $producto->precio }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td>{{ $producto->importe }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="prefactura-resumen">
        <div class="card border border-dark prefactura-notas">
            <div class="card-body" style="padding:4px 8px;">
                <p style="margin:0 0 1px; font-size:10px;"><b>Asesor comercial:</b> {{ $datos->vendedor }} &nbsp; <b>Tele asesor:</b> {{ $datos->cotizador }} &nbsp; <b>Asesor de entrega:</b> </p>
                <p style="margin:0 0 2px; font-size:10px;"><b>Tramite #{{ $datos->flujo_id ?? '—' }}</b></p>
                <hr style="margin:2px 0; border-top:1px solid #999;">
                <p style="margin:0 0 1px; font-size:9px;">1. Documento comercial generado antes de facturar.</p>
                <p style="margin:0 0 1px; font-size:9px;">2. La disponibilidad de inventario y precios pueden cambiar antes de facturar.</p>
                <p style="margin:0 0 1px; font-size:9px;">3. Esta prefactura no constituye documento fiscal.</p>
                <p style="margin:0 0 1px; font-size:9px;">4. Para continuar, debe confirmarse facturacion y condiciones comerciales.</p>
                <p style="margin:0 0 1px; font-size:9px;">5. Tiempo de validacion configurado: {{ strtoupper($validez) }} desde su emision.</p>
                <p style="margin:3px 0 0; font-size:9px;"><b>"@if ($flagCentavos == false){{ $numeroLetras . ' CON CERO CENTAVOS' }}@else{{ $numeroLetras }}@endif"</b></p>
            </div>
        </div>

        <div class="card border border-dark prefactura-totales">
            <div class="card-body" style="padding:4px 8px;">
                <table style="width:100%; border:none; border-collapse:collapse; font-size:10px;">
                    <tr>
                        <td style="border:none; padding:1px 0;">Importe Exonerado:</td>
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
                </table>
            </div>
        </div>
    </div>

    <p style="margin:4px 0 0; font-size:8px; text-align:right;">Original: Cliente, Copia obligado tributario emisor.</p>

    <div class="prefactura-firmas">
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
