<!DOCTYPE html>
<html>

<head>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style>
        .producto {

            width: 230px;
            height: 145px;
            border-radius: 50%;
            object-fit: cover;
        }

        p {
            font-size: 10px;
        }

        .espacio{
            height:15px;
        }
        .polaroid {
            width: 80%;
            background-color: white;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
          }

          body {

            /*  background-size: 100% 100%; */
             margin-left: -95px;
             padding: 50px;
             /* ##background-image: url('img/membrete/Logo1.png'); */


             width: 45rem;
             height: 3rem;


         }
    </style>
    <title>Catálogo</title>
</head>

<body>

    <img src="img/membrete/Logo3.png" width="800rem" style="margin-left:2%; margin-top:-70px; position:absolute;"alt="">
                <div class="container">


                        <div>

                            <table  style="font-size: 12px; margin-top:15px;margin-left:-8%; width:700px;">
                                <thead>
                                    <tr class="bg-warning rounded border-0" style="color: #FFFFFF; font-size: 14px; text-align: center;">
                                        <th>PRODUCTO</th>
                                        <th>DETALLE</th>
                                    </tr >
                                </thead>
                                <tbody>

                                    @foreach ($datos as $producto)
                                        <tr class="rounded border border-warning border-0">
                                            <td style="text-align: center;">

                                                <div class="card border-0" >
                                                    <img class="producto"  style="margin-left:10%;" src="catalogo/{{ $producto->imagen }}"alt="Card image cap">
                                                    <div class="card-body">
                                                      <p class="card-title"><b>{{ $producto->nombre }}</p>
                                                    </div>
                                                  </div>
                                            </td>
                                            <td class="p-3 mb-2 bg-light text-dark">
                                                <p class="card-text"> <b>Marca:</b> {{ $producto->marca }}    </p>
                                                <p class="card-text"><b>Categoría:</b> {{ $producto->categoria}}</p>
                                                <p class="card-text"><b>Unidad:</b> {{ $producto->medida }}</p>
                                                <p class="card-text"><b>Cantidad:</b> {{ $producto->cantidad }}</p>
                                                <p class="card-text"><b>Precio:</b> L. {{ $producto->precio }}</p>
                                            </td>
                                        </tr>
                                    @endforeach


                                    @php

                                        $altura = 50;
                                        $altura2 = 450;
                                    @endphp

                                </tbody>


                            </table>

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
