<!DOCTYPE html>
<html>

<head>


        <link rel="stylesheet" href="{{ public_path('css/bootstrap.min.css') }}">
    <style>
        .color-red {
            color: red;
        }

        p {
            font-size: 12px;
        }

        body {

           /*  background-size: 100% 100%; */
            margin-left: -95px;
            padding: 50px;
            /* ##background-image: url('img/membrete/Logo1.png'); */


            width: 45rem;
            height: 3rem;


        }


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
            background-color: #f2f2f2
        }

        /* tr:nth-child(even) {
            background-color: #f2f2f2

        } */

        .letra {
            font-weight: 800;


        }
        table {
            border: #b2b2b2 1px solid;
          }
          td, th {
            border: black 1px solid;
          }
    </style>
    <title>ESTADO DE CUENTA</title>
</head>

<body>
    @php
        $fecha_actual = date("Y-m-d");
        $hoy = \Carbon\Carbon::today();
        $diasVencidos = static function ($fechaVencimiento) use ($hoy) {
            if (empty($fechaVencimiento)) {
                return 0;
            }

            try {
                $vence = \Carbon\Carbon::parse($fechaVencimiento)->startOfDay();
            } catch (\Throwable $e) {
                return 0;
            }

            return $vence->lt($hoy) ? $vence->diffInDays($hoy) : 0;
        };
        $totalAcumuladoVencido = 0;
        foreach ($estadoCuenta as $fila) {
            if ($diasVencidos($fila->fecha_vencimiento ?? null) > 0) {
                $totalAcumuladoVencido += (float) ($fila->saldo ?? 0);
            }
        }
    @endphp

    <div class="pruebaFondo">
        <img src="{{ public_path('img/membrete/Logo3.png') }}" width="900rem" style="margin-left:13%; margin-top:-25px; position:absolute;"alt="">

        <div class="card border border-dark" style="margin-left:72px;  margin-top:105px; width:60rem; height:4rem;">
            <div class="card-header">
                <p class="card-text" style="position:absolute;left:400px;  top:-3px; font-size:18px;"><b>ESTADO DE CUENTA</b></p>

            </div>
            <p class="card-text" style="position:absolute;left:20px;  top:40px;"><b>Cliente:{{ $estadoCuenta[0]->cliente}}</b></p>
            <p class="card-text" style="position:absolute;left:825px;  top:40px;"><b>Fecha: {{ $fecha_actual  }}</b></p>
        </div>


                    <div class="" style="position: relative; margin-left:72px; margin-top:10px; width:60rem;">

                        <div>

                            <table class="border border-block" style="font-size: 12px; ">
                                <thead>
                                    <tr>
                                        <th style="text-align: center">Factura</th>
                                                    <th style="text-align: center">No. Compra</th>
                                                    <th style="text-align: center">Fecha Emisión</th>
                                                    <th style="text-align: center">Fecha Vencimiento</th>
                                                    <th style="text-align: center">Días Venc.</th>
                                                    <th style="text-align: center">Cargo</th>
                                                    <th style="text-align: center">Crédito</th>
                                                    <th style="text-align: center">Notas Crédito</th>
                                                    <th style="text-align: center">Notas Débito</th>
                                                    <th style="text-align: center">Saldo</th>
                                                    <th style="text-align: center">Acumulado</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($estadoCuenta as $valor)
                                        @php($diasFila = $diasVencidos($valor->fecha_vencimiento ?? null))
                                        <tr>
                                            <td style="text-align: center">{{ $valor->correlativo }}</td>
                                            <td style="text-align: center">{{ $valor->numOrden }}</td>
                                            <td style="text-align: center">{{ $valor->fecha_emision }}</td>
                                            <td style="text-align: center">{{ $valor->fecha_vencimiento }}</td>
                                            <td style="text-align: center">{{ $diasFila }}</td>
                                             <td style="text-align: center"> L. {{ number_format($valor->cargo, 2, ',') }}</td>
                                            <td style="text-align: center"> L. {{ number_format($valor->credito, 2, ',') }}</td>
                                            <td style="text-align: center"> L. {{ number_format($valor->notaCredito, 2, ',') }}</td>
                                            <td style="text-align: center"> L. {{ number_format($valor->notaDebito, 2, ',') }}</td>
                                            <td style="text-align: center; color: {{ $diasFila > 0 ? 'red' : 'inherit' }}; font-weight: {{ $diasFila > 0 ? '700' : '400' }};"> L. {{ number_format($valor->saldo, 2, ',') }}</td>
                                            <td style="text-align: center"> L. {{ number_format($valor->Acumulado, 2, ',') }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>


                            </table>

                            <div style="margin-top:12px; text-align:right; font-size:13px; font-weight:700; color:#111;">
                                <span>Total Acumulado vencido:</span>
                                <span style="margin-left:10px; color:red;">L. {{ number_format($totalAcumuladoVencido, 2, ',', ',') }}</span>
                            </div>

                            <div style="position:absolute; left:120px;   width:45rem; margin-top:30px">
                                <p class="card-text" style="position:absolute;left:20px;  top:10px;">
                                    _______________________________________</p>
                                <p class="card-text" style="position:absolute;left:450px;  top:10px;">
                                    _______________________________________</p>
                                <p class="card-text" style="position:absolute;left:50px;  top:25px; max-width:250px;  ">
                                    {{  $estadoCuenta[0]->cliente }}
                                    </p>
                                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
                                {{--  <p class="card-text" style="position:absolute;left:460px;  top:-60px;">Original: Cliente, Copia obligado tributario emisor. </p>  --}}
                            </div>
                        </div>

                    </div>


    </div>




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
