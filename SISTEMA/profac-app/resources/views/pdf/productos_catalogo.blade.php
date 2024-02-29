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
    </style>
    <title>FACTURA</title>
</head>

<body>

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
        <img src="img/membrete/Logo3.png" width="800rem" style="margin-left:3%; margin-top:-25px; position:absolute;"alt="">

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
