<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        .color-red { color: red; }
        p { font-size: 12px; }
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
        th, td { text-align: left; padding: 2px; }
        thead { background-color: #f2f2f2; }
        .letra { font-weight: 800; }
    </style>
    <title>PRE-FACTURA</title>
</head>
<body>

@php
    $validez = $datos->descripcion_validez ?? ($datos->dias_validez . ' días');
@endphp

<div class="pruebaFondo">
    <img src="{{ public_path('img/membrete/Logo3.png') }}" width="800rem"
         style="margin-left:3%; margin-top:-25px; position:absolute;" alt="">

    <div class="card" style="margin-left:44px; margin-top:100px; width:45rem; height:5.5rem;">
        <div class="card-header">
            <b>PRE-FACTURA No. {{ $datos->codigo }}</b>
        </div>
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:50px;">
                <b>Registro tributario: 08011986138652</b>
            </p>
        </div>
    </div>

    <div class="card" style="margin-left:44px; margin-top:10px; width:45rem; height:6.5rem;">
        <div class="card-body">
            <p class="card-text" style="position:absolute; left:20px; top:10px; max-width:500px;">
                <b>Cliente: </b>{{ $datos->nombre }} - ({{ $datos->clienteId }})
            </p>
            <br><br>
            <p class="card-text" style="position:absolute; left:20px; top:40px; font-size:11px; max-width:500px;">
                <b>Dirección:</b> {{ $datos->direccion }}
            </p>
            <br><br><br>
            <p class="card-text" style="position:absolute; left:20px; top:60px;">
                <b>Correo:</b> {{ $datos->correo }}
            </p>

            <p class="card-text" style="position:absolute; left:540px; top:10px;">
                <b>Fecha:</b> {{ $datos->fecha_emision }}
            </p>
            <p class="card-text" style="position:absolute; left:540px; top:25px;">
                <b>Hora:</b> {{ $datos->hora }}
            </p>
            <p class="card-text" style="position:absolute; left:540px; top:40px;">
                <b>Vence:</b> {{ $datos->fecha_vencimiento }}
            </p>
            <p class="card-text" style="position:absolute; left:540px; top:57px;">
                <b>RTN:</b> {{ $datos->rtn }}
            </p>
            <p class="card-text" style="position:absolute; left:300px; top:60px;">
                <b>Teléfono:</b> {{ $datos->telefono_empresa }}
            </p>
        </div>
    </div>

    <div class="card" style="position:relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside:auto;">
        <div>
            <table class="table" style="font-size:11px;">
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

    <div style="position:relative; margin-left:44px; margin-top:30px; width:26rem; height:20rem;">

        <div class="card" style="position:absolute; left:0px; width:26rem; height:15rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute; left:10px; top:2px; font-size:14px;">
                    <b>Vendedor: {{ $datos->vendedor }}</b>
                </p>
                <p class="card-text" style="position:absolute; left:200px; top:2px; font-size:14px;">
                    <b>Cotizador: {{ $datos->cotizador }}</b>
                </p>
                <p class="card-text" style="position:absolute; left:10px; top:18px; font-size:13px;">
                    <b>Trámite #{{ $datos->flujo_id ?? '—' }}</b>
                </p>

                <p class="card-text" style="position:absolute; left:0px; top:28px; font-size:11px;">
                    ____________________________________________________________________
                </p>
                <p class="card-text" style="position:absolute; left:10px; top:40px; font-size:11px;">
                    Nota: {{ $datos->nota }}
                </p>

                {{-- Validez notice --}}
                <p class="card-text" style="position:absolute; left:10px; top:60px; font-size:11px; color:#cc4400; font-weight:700;">
                    ** ESTA PREFACTURA TIENE UN TIEMPO DE VALIDACIÓN DE {{ strtoupper($validez) }} DESDE SU FECHA DE EMISIÓN **
                </p>

                @if($flagCentavos == false)
                <p class="card-text" style="position:absolute; left:35px; top:240px; font-size:12px;">
                    "{{ $numeroLetras . " CON CERO CENTAVOS" }}"
                </p>
                @else
                <p class="card-text" style="position:absolute; left:35px; top:240px; font-size:12px;">
                    "{{ $numeroLetras }}"
                </p>
                @endif
            </div>
        </div>

        <div class="card" style="position:absolute; left:430px; width:18rem; height:15rem;">
            <div class="card-body">
                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:10px; font-size:14px;">
                        Importe exonerado:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:10px; font-size:14px;">L. 0.00</p>
                </div>
                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:28px; font-size:14px;">
                        Importe Grabado 15%:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:28px; font-size:14px;">
                        L. {{ $importesConCentavos->sub_total_grabado }}
                    </p>
                </div>
                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:46px; font-size:14px;">
                        Importe Grabado 18%:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:46px; font-size:14px;">L. 0.00</p>
                </div>
                <div>
                    <p class="card-text" style="position:absolute; left:10px; top:64px; font-size:14px;">
                        Importe Exento:
                    </p>
                    <p class="card-text" style="position:absolute; right:10px; top:64px; font-size:14px;">
                        L. {{ $importesConCentavos->sub_total_excento }}
                    </p>
                </div>

                <p class="card-text" style="position:absolute; left:10px; top:85px; font-size:14px;">
                    Desc. y Rebajas {{ $importes->porc_descuento }}%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:85px; font-size:14px;">
                    L. {{ $importesConCentavos->monto_descuento }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:105px; font-size:14px;">Sub Total:</p>
                <p class="card-text" style="position:absolute; right:10px; top:105px; font-size:14px;">
                    L. {{ $importesConCentavos->sub_total }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:130px; font-size:14px;">
                    Impuesto sobre venta 15%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:130px; font-size:14px;">
                    L. {{ $importesConCentavos->isv }}
                </p>

                <p class="card-text" style="position:absolute; left:10px; top:148px; font-size:14px;">
                    Impuesto sobre venta 18%:
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:148px; font-size:14px;">L. 0.00</p>

                <p class="card-text" style="position:absolute; left:10px; top:185px; font-size:14px;">
                    <b>Total a Pagar:</b>
                </p>
                <p class="card-text" style="position:absolute; right:10px; top:185px; font-size:14px;">
                    <b>L. {{ $importesConCentavos->total }}</b>
                </p>
            </div>
        </div>

        <div style="position:absolute; left:0px; width:45rem; margin-top:300px;">
            <p class="card-text" style="position:absolute; left:20px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute; left:450px; top:10px;">
                _______________________________________
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:25px; max-width:500px;">
                Cliente: {{ strtoupper($datos->nombre) }}
            </p>
            <p class="card-text" style="position:absolute; left:20px; top:40px; max-width:250px;">Recibido por:</p>
            <p class="card-text" style="position:absolute; left:20px; top:55px; max-width:250px;">Telefono:</p>
            <p class="card-text" style="position:absolute; left:20px; top:70px; max-width:250px;">
                <b>*Se requiere firma y sello de recibido.*</b>
            </p>
            <p class="card-text" style="position:absolute; left:495px; top:25px;">DISTRIBUCIONES VALENCIA</p>
            <p class="card-text" style="position:absolute; left:460px; top:-60px;">
                Original: Cliente, Copia obligado tributario emisor.
            </p>
        </div>
    </div>

</div>

</body>
</html>
