<!DOCTYPE html>
<html>

<head>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        .color-red {
            color: red;
        }

        p {
            font-size: 12px;
        }

        body {
            margin: -45px;
            padding: 0px;
           /*  background-image: url('img/membrete/membrete2.jpg'); */

            background-size: 200% 200%;
            background-size: cover;

            width: 115% !important;

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
            background-color: #f2f2f2
        }

        /* tr:nth-child(even){background-color: #f2f2f2} */

        .letra {
            font-weight: 800;


        }





    </style>
    <title>Nota de débito</title>
</head>

<body>

@php
    $altura =20;
    $altura2 = 320;
    $contadorFilas = 0;
@endphp

       {{--   @if($cliente->estado_factura_id==1)
        <span style = "font-size: 10px; position:absolute;left:500px;  top:105px;">Documento: N{{$cliente->numero_factura}}-CF11</span></p>
        @else
        <span style = "font-size: 10px; position:absolute;left:500px;  top:105px;">Documento: N{{$cliente->numero_factura}}-CF12</span></p>
        @endif  --}}
    <div class="pruebaFondo">
        <img src="img/membrete/Logo3.png" width="800rem"
        style="margin-left:3%; margin-top:25px; position:absolute;"
         alt="">

        <b style="position:absolute;right: 60px; top:80px; font-size: 14px; " >ORIGINAL</b>
        <br><br><br>

        <br><br>
        <div class="card border border-dark" style="margin-left:44px;  margin-top:30px; width:45rem;">

            <div >
                <table  class="" style="font-size: 12px;">
                    <thead>
                        <tr>
                          <th>Nota de débito No. {{$notaDebito->numeroCai}}</th>
                          <th style=" text-align: right; ">Factura No. {{ $cliente->cai }} </th>
                        </tr>
                    </thead>
                        <tr>
                            <td>Registro tributario:
                                08011986138652 </td>
                            <td>CAI: {{$cai->cai}}</td>
                        </tr>
                        <tr>
                            <td>Fecha límite de emisión: {{$cai->fecha_limite_emision}}</td>
                            <td>Rango autorizado: {{$cai->numero_inicial}} - {{$cai->numero_final}}</td>
                        </tr>
                </table>
            </div>


        </div>

        <div class="card border border-dark" style="margin-left:44px;  margin-top:10px; width:45rem; height:5rem;">

            <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:0px;"><b>Cliente: {{ $cliente->nombre_cliente }} </b></p>
                <p class="card-text" style="position:absolute;left:20px;  top:20px;"><b>Dirección: {{ $cliente->direccion }} </b></p>

                <p class="card-text" style="position:absolute;left:550px;  top:0px;"><b>Fecha: {{$notaDebito->fechaEmision}} </b></p>

                <p class="card-text" style="position:absolute;left:550px;  top:20px;"><b>RTN: {{$cliente->rtn}} </b></p>



                <p class="card-text" style="position:absolute;left:20px;  top:60px;"><b>Correo: {{$cliente->correo}} </b></p>
                <p class="card-text" style="position:absolute;left:550px;  top:60px;"><b>Teléfono: {{$cliente->telefono_empresa}} </b></p>

            </div>
        </div>


        <br>
        <br><br>

        <div class="card border border-dark" style="position: relative; margin-left:44px; margin-top:-20px; width:45rem;">
            <div >
                <table  class="" style="font-size: 11px;">
                    <thead>
                        <tr>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Valor unitario</th>
                          <th>Total</th>
                        </tr>
                    </thead>
                        <tr>
                            <td>{{ $notaDebito->motivoDescripcion }}</td>
                            <td>1</td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                        </tr>
                        {{-- <tr>
                            <td>RECARGO APLICADO</td>
                            <td>L - </td>
                            <td>L. {{ $montoConCentavos->total }}</td>
                        </tr> --}}
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>


        <p class="card-text" style="position:absolute;left:430px;  top:380px;">Sub Total:</p>
        <p class="card-text" style="position:absolute;left:620px;  top:380px;">L. {{ $montoConCentavos->total }}</p>
        <p class="card-text" style="position:absolute;left:430px;  top:395px;">Impuesto sobre la venta 15%:</p>
        <p class="card-text" style="position:absolute;left:620px;  top:395px;">L. 0.00</p>
        <p class="card-text" style="position:absolute;left:430px;  top:420px;"><b>Total:</b></p>
        <p class="card-text" style="position:absolute;left:620px;  top:420px;">L. {{ $montoConCentavos->total }}</b></p>

        <p class="card-text" style="position:absolute;left:20px;  top:530px;">VALOR EN LETRAS: {{ $numeroLetras }} EXACTOS</p>

        <div style=" position: relative; margin-left:44px;">

            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;">
                    _______________________________________</p>
                <p class="card-text" style="position:absolute;left:60px;  top:25px; ">{{ $cliente->nombre_cliente }}</p>
                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
            </div>



        </div>

        <p class="card-text" style="position:absolute;left:30px;  top:800px;">ORIGINAL: CLIENTE</p>
        <p class="card-text" style="position:absolute;left:30px;  top:815px;">COPIA: EMISOR</p>
        @if($notaDebito->estado_id==2)
        <div>
            <p class="" style="position:absolute; margin-top:{{$altura2 + 85}}px;  left:140px;   font-size:30px;">--Nota de Débito Anulada--</p>
        </div>
        @endif






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
