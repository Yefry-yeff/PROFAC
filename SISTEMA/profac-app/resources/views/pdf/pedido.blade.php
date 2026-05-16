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

        th, td {
            text-align: left;
            padding: 2px;
        }

        thead { background-color: #f2f2f2; }

        .letra { font-weight: 800; }
    </style>
    <title>PEDIDO</title>
</head>
<body>

    <div class="pruebaFondo">

        {{-- ── Logo membrete ── --}}
        <img src="{{ public_path('img/membrete/Logo3.png') }}"
             width="800rem"
             style="margin-left:3%; margin-top:-25px; position:absolute;"
             alt="">

        {{-- ── Encabezado: número de pedido ── --}}
        <div class="card" style="margin-left:44px; margin-top:100px; width:45rem; height:5.5rem;">
            <div class="card-header">
                <b>Pedido No. {{ $datos->codigo }}</b>
            </div>
            <div class="card-body">
                <p class="card-text" style="position:absolute; left:20px; top:50px;">
                    <b>Registro tributario: 08011986138652</b>
                </p>
            </div>
        </div>

        {{-- ── Datos del cliente ── --}}
        <div class="card" style="margin-left:44px; margin-top:10px; width:45rem; height:6.5rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute; left:20px; top:10px; max-width:500px;">
                    <b>Cliente:</b> {{ $datos->nombre }} - ({{ $datos->clienteId }})
                </p>
                <p class="card-text" style="position:absolute; left:20px; top:40px; font-size:11px; max-width:500px;">
                    <b>Dirección:</b> {{ $datos->direccion }}
                </p>
                <p class="card-text" style="position:absolute; left:20px; top:60px;">
                    <b>Correo:</b> {{ $datos->correo }}
                </p>

                <p class="card-text" style="position:absolute; left:540px; top:10px;">
                    <b>Fecha:</b> {{ $datos->fecha_emision }}
                </p>
                <p class="card-text" style="position:absolute; left:540px; top:25px;">
                    <b>Hora:</b> {{ $datos->hora }}
                </p>
                <p class="card-text" style="position:absolute; left:540px; top:57px;">
                    <b>RTN:</b> {{ $datos->rtn }}
                </p>

                <p class="card-text" style="position:absolute; left:300px; top:60px;">
                    <b>Teléfono:</b> {{ $datos->telefono_empresa }}
                </p>
            </div>
        </div>

        {{-- ── Tabla de productos ── --}}
        <div class="card" style="position:relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside:auto;">
            <div>
                <table class="table" style="font-size:11px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th style="text-align:center;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $i => $producto)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $producto->nombre_producto }}</td>
                            <td style="text-align:center;">{{ $producto->cantidad }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Responsable / Nota ── --}}
        <div class="card" style="margin-left:44px; margin-top:30px; width:45rem; height:auto;">
            <div class="card-body" style="padding:10px 14px;">
                <p style="font-size:14px; margin:0 0 6px;"><b>Responsable: {{ $datos->cotizador }}</b></p>
                <p style="font-size:11px; margin:0; color:#555; border-top:1px solid #ccc; padding-top:4px;">
                    @if ($datos->nota)
                        Nota: {{ $datos->nota }}
                    @else
                        &nbsp;
                    @endif
                </p>
            </div>
        </div>

        {{-- ── Sección de firmas ── --}}
        <div style="margin-left:44px; margin-top:28px; width:45rem;">
            <table style="width:100%; border:none; font-size:12px;">
                <tr>
                    <td style="width:50%; vertical-align:top; border:none; padding:0 10px 0 0;">
                        <p style="margin:0;">_______________________________________</p>
                        <p style="margin:4px 0 2px; word-break:break-word;">
                            Cliente: {{ strtoupper($datos->nombre) }}
                        </p>
                        <p style="margin:2px 0;">Recibido por:</p>
                        <p style="margin:2px 0;">Teléfono:</p>
                        <p style="margin:4px 0;"><b>*Se requiere firma y sello de recibido.*</b></p>
                    </td>
                    <td style="width:50%; vertical-align:top; border:none; padding:0 0 0 10px;">
                        <p style="margin:0;">_______________________________________</p>
                        <p style="margin:4px 0 2px;">DISTRIBUCIONES VALENCIA</p>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    {{-- ── Footer fijo: leyenda centrada ── --}}
    <div style="position:fixed; bottom:18px; left:0; right:0; text-align:center; font-size:10px; color:#555;">
        Original: Cliente, Copia obligado tributario emisor.
    </div>

</body>
</html>
