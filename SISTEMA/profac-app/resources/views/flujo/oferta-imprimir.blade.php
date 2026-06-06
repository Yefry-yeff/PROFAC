<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        p {
            font-size: 12px;
        }

        body {
            margin-left: -95px;
            padding: 50px;
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

        thead {
            background-color: #f2f2f2;
        }

        .letra {
            font-weight: 800;
        }

        .badge-pedido {
            background: #e07b00;
            color: #fff;
            padding: 2px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 700;
        }
        .watermark-cancelada {
            position: fixed;
            top: 38%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 110px;
            font-weight: 900;
            color: rgba(200, 0, 0, 0.13);
            border: 12px solid rgba(200, 0, 0, 0.13);
            border-radius: 16px;
            padding: 8px 24px;
            pointer-events: none;
            z-index: 9999;
            letter-spacing: 8px;
            white-space: nowrap;
        }
    </style>
    <title>OFERTA DE PEDIDO #{{ $oferta->pedido_id }}</title>
</head>

<body>

@php
    $codigo = date('Y').'-'.$oferta->id;
@endphp

<div class="pruebaFondo">

    @if(!empty($esCancelada) && $esCancelada)
    <div class="watermark-cancelada">CANCELADA</div>
    @endif

    {{-- Company logo header --}}
    <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem"
         style="margin-left:3%; margin-top:-25px; position:absolute;" alt="">

    {{-- Title card --}}
    <div class="card" style="margin-left:44px; margin-top:100px; width:45rem; height:5.5rem;">
        <div class="card-header">
            <b>Oferta de Pedido No. {{ $codigo }}</b>
            &nbsp;
            <span class="badge-pedido">Pedido #{{ $oferta->pedido_id }}</span>
        </div>
        <div class="card-body">
            <p class="card-text" style="position:absolute;left:20px; top:50px;">
                <b>Registro tributario: 08011986138652</b>
            </p>
        </div>
    </div>

    {{-- Client info card --}}
    <div class="card" style="margin-left:44px; margin-top:10px; width:45rem; height:6.5rem;">
        <div class="card-body">
            <p class="card-text" style="position:absolute;left:20px; top:10px; max-width:500px">
                <b>Cliente: </b>{{ $oferta->cliente_nombre }} - ({{ $oferta->cliente_id }})
            </p>
            <br><br>
            <p class="card-text" style="position:absolute;left:20px; top:40px; font-size:11px; max-width:500px">
                <b>Dirección:</b> {{ $oferta->direccion }}
            </p>
            <br><br><br>
            <p class="card-text" style="position:absolute;left:20px; top:60px;">
                <b>Correo:</b> {{ $oferta->correo }}
            </p>

            <p class="card-text" style="position:absolute;left:540px; top:10px;">
                <b>Fecha:</b> {{ $oferta->fecha_emision }}
            </p>
            <p class="card-text" style="position:absolute;left:540px; top:25px;">
                <b>Hora:</b> {{ date('H:i', strtotime($oferta->created_at)) }}
            </p>
            <p class="card-text" style="position:absolute;left:540px; top:40px;">
                <b>Vence:</b> {{ $oferta->fecha_vencimiento }}
            </p>
            <p class="card-text" style="position:absolute;left:540px; top:57px;">
                <b>RTN:</b> {{ $oferta->rtn }}
            </p>
            <p class="card-text" style="position:absolute;left:300px; top:60px;">
                <b>Teléfono:</b> {{ $oferta->telefono_empresa }}
            </p>
        </div>
    </div>

    {{-- Products table --}}
    <div class="card" style="position:relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside:auto;">
        <div>
            <table class="table" style="font-size:11px;">
                <thead>
                    <tr style="background-color:#e07b00; color:#fff;">
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

    {{-- Totals & signatures section --}}
    <div style="position:relative; margin-left:44px; margin-top:30px; width:26rem; height:20rem;">

        {{-- Notes / seller card --}}
        <div class="card" style="position:absolute;left:0px; width:26rem; height:15rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute;left:10px; top:2px; font-size:14px;">
                    <b>Asesor comercial: {{ $oferta->vendedor_nombre }}</b>
                </p>
                <p class="card-text" style="position:absolute;left:200px; top:2px; font-size:14px;">
                    <b>Cotizador: {{ $oferta->cotizador }}</b>
                </p>
                <p class="card-text" style="position:absolute;left:0px; top:28px; font-size:11px;">
                    ____________________________________________________________________
                </p>
                <p class="card-text" style="position:absolute;left:10px; top:40px; font-size:11px;">
                    Nota: {{ $oferta->nota }}
                </p>

                @if($flagCentavos == false)
                    <p class="card-text" style="position:absolute;left:35px; top:240px; font-size:12px;">
                        "{{ $numeroLetras.' CON CERO CENTAVOS' }}"
                    </p>
                @else
                    <p class="card-text" style="position:absolute;left:35px; top:240px; font-size:12px;">
                        "{{ $numeroLetras }}"
                    </p>
                @endif
            </div>
        </div>

        {{-- Amounts card --}}
        <div class="card" style="position:absolute;left:430px; width:18rem; height:15rem;">
            <div class="card-body">

                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:10px; font-size:14px;">
                        Importe exonerado:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:10px; font-size:14px;">
                        L. 0.00
                    </p>
                </div>

                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:28px; font-size:14px;">
                        Importe Grabado 15%:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:28px; font-size:14px;">
                        L. {{ number_format((float)$oferta->sub_total_grabado, 2) }}
                    </p>
                </div>

                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:46px; font-size:14px;">
                        Importe Grabado 18%:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:46px; font-size:14px;">
                        L. 0.00
                    </p>
                </div>

                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:64px; font-size:14px;">
                        Importe Exento:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:64px; font-size:14px;">
                        L. {{ number_format((float)$oferta->sub_total_excento, 2) }}
                    </p>
                </div>

                <p class="card-text" style="position:absolute; left:10px; top:85px; font-size:14px;">
                    Desc. y Rebajas {{ $oferta->porc_descuento }}%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:85px; font-size:14px;">
                    L. {{ number_format((float)$oferta->monto_descuento, 2) }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:105px; font-size:14px;">
                    Sub Total:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:105px; font-size:14px;">
                    L. {{ number_format((float)$oferta->sub_total, 2) }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:130px; font-size:14px;">
                    Impuesto sobre venta 15%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:130px; font-size:14px;">
                    L. {{ number_format((float)$oferta->isv, 2) }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:148px; font-size:14px;">
                    Impuesto sobre venta 18%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:148px; font-size:14px;">
                    L. 0.00
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:185px; font-size:14px;">
                    <b>Total a Pagar:</b>
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:185px; font-size:14px;">
                    <b>L. {{ number_format((float)$oferta->total, 2) }}</b>
                </p>

            </div>
        </div>

        {{-- Signature lines --}}
        <div style="position:absolute; left:0px; width:45rem; margin-top:300px;">
            <p class="card-text" style="position:absolute;left:20px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute;left:450px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute;left:20px; top:25px; max-width:500px;">
                Cliente: {{ strtoupper($oferta->cliente_nombre) }}
            </p>
            <p class="card-text" style="position:absolute;left:20px; top:40px; max-width:250px;">
                Recibido por:
            </p>
            <p class="card-text" style="position:absolute;left:20px; top:55px; max-width:250px;">
                Telefono:
            </p>
            <p class="card-text" style="position:absolute;left:20px; top:70px; max-width:250px;">
                <b>*Se requiere firma y sello de recibido.*</b>
            </p>
            <p class="card-text" style="position:absolute;left:495px; top:25px;">
                DISTRIBUCIONES VALENCIA
            </p>
            <p class="card-text" style="position:absolute;left:460px; top:-60px;">
                Original: Cliente, Copia obligado tributario emisor.
            </p>
        </div>

    </div>

</div>

</body>
</html>
