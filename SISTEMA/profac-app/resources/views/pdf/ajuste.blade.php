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
        font-size: 12px;
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

        tr:nth-child(even){background-color: #f2f2f2}

        .letra {
            font-weight: 800;


        }
    </style>
    <title>AJUSTE</title>
</head>

<body>

@php
$suma=0;
$altura =20;
    $altura2 = 320;
    $contadorFilas = 0;
@endphp


    <div class="pruebaFondo">
        <img src="img/membrete/Logo3.png" width="800rem"
        style="margin-left:3%; margin-top:25px; position:absolute;"
         alt="">
        <div class="card border border-dark" style="margin-left:44px;  margin-top:150px; width:45rem; height:4rem;">
            <div class="card-header">
                <b>Registro de Ajuste No. {{$ajuste->numero_ajuste}} </b>

            </div>


        </div>

        <div class="card border border-dark"   style="margin-left:44px; margin-top:10px; width:45rem; height:7rem;">
            <div class="card-body">

                <p class="card-text "  style="position:absolute;left:20px;  top:10px;"><b>Fecha de Ajuste: </b> {{$datos->fecha}}
                </p>
                <p class="card-text" style="position:absolute;left:390px;  top:10px;"><b>Solicitado por: </b> {{$datos->solicitado_por}}</p>

                <p class="card-text" style="position:absolute;left:20px;  top:40px;"><b>Motivo de Ajuste: </b> {{$datos->motivo}}</p>

                <p class="card-text" style="position:absolute;left:20px;  top:70px;"><b>Comentario: </b> {{$datos->comentario}}</p>








            </div>
        </div>

        <div class="card border border-dark" style="position: relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside: auto;">
            <div >


                <table  class="" style=" ">
                    <thead>
                    <tr>
                      <th>Código</th>
                      <th>Descripción</th>
                      <th>Bodega</th>
                      <th>Seccion</th>
                      <th>Medida</th>
                      <th>Precio </th>
                      <th>Cantidad</th>
                      <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                    <tr>
                        <td>{{$producto->producto_id}}</td>
                        <td>{{$producto->producto}}</td>
                        <td>{{$producto->bodega}}</td>
                        <td>{{$producto->seccion}}</td>
                        <td>{{$producto->medida}}</td>
                        <td>{{$producto->precio_producto}}</td>
                        <td>{{$producto->cantidad}} ({{$producto->aritmetica}})</td>
                        <td>{{$producto->total}}</td>
                    </tr>


                    @endforeach


                </tbody>
                  </table>
            </div>
        </div>

        {{--  <div class="card  border-dark" style="position: relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside: auto;">
                <table>
                    <thead>
                        <tr>

                          <th>Descripción</th>
                          <th>Cantidad Inicial </th>
                          <th>Unidades a sumar</th>
                          <th>Unidades a Restar</th>
                          <th>Balance </th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                Cantidades

                            </td>
                            <td>
                                {{$ajuste->cantidad_inicial}}
                            </td>
                            @if($ajuste->tipo_aritmetica == "Ajuste de tipo suma de unidades")
                            <td>
                                @php
                                $suma = $ajuste->cantidad_inicial + $ajuste->cantidad_total
                                @endphp
                                {{$ajuste->cantidad_total}}
                            </td>
                            <td>
                                0
                            </td>
                            @else
                            <td>
                                0
                            </td>
                            <td>
                                {{$ajuste->cantidad_total}}
                                @php
                                $suma = $ajuste->cantidad_inicial - $ajuste->cantidad_total
                                @endphp
                            </td>
                            @endif

                            <td>
                                {{$suma}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Costos

                            </td>
                            <td>
                                --
                            </td>
                            @if($ajuste->tipo_aritmetica == "Ajuste de tipo suma de unidades")
                            <td>
                                (+) {{$ajuste->costo}} Lps
                            </td>
                            <td>
                                (-) 0.00 Lps
                            </td>

                            <td>
                                (+) {{$ajuste->costo}} Lps
                            </td>
                            @else
                            <td>
                                (+) 0.00 Lps
                            </td>
                            <td>
                                (-) {{$ajuste->costo}} Lps
                            </td>
                            <td>
                                (-) {{$ajuste->costo}} Lps
                            </td>
                            @endif


                        </tr>

                    </tbody>
                </table>
        </div>
        --}}







        <div style=" position: relative; margin-left:44px;">




            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">  _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;"> _______________________________________</p>


                <p class="card-text" style="position:absolute;left:80px;  top:25px; ">Realizado por {{ucwords($datos->realizado_por)}}</p>
                <p class="card-text" style="position:absolute;left:550px;  top:25px;">Autorizado por</p>
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
