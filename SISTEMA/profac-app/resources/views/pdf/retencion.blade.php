<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
            background-image: url('img/membrete/membrete2.jpg');
            background-repeat: no-repeat;
            background-size: 200% 200%;
            background-size: cover;

            width: 115%!important;

        }

    </style>
    <title>Documento de Retencion</title>
</head>

<body>



    <div class="pruebaFondo">
        <div class="card border border-dark" style="position:absolute;left:0px;  top:180px; width:45rem; height:5.5rem;">
            <div class="card-header">
               <b>Retención No. {{$data->cai_retencion}} </b>
              </div>
            <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:50px;"><b>Reistro tributario: 0801198892837</b></p>
                <p class="card-text" style="position:absolute;left:433px;  top:50px;"><b>CAI: {{ $data->numeroCai }}</b></p>
                <p class="card-text" style="position:absolute;left:20px;  top:65px;"><b>Fecha límite de emisión: {{$data->fecha_limite}}</b></p>
                <p class="card-text" style="position:absolute;left:350px;  top:65px;"><b>Rango autorizado: {{$data->numero_inicial}} al {{$data->numero_final}}</b></p>
            </div>
        </div>

        <div class="card border border-dark" style="position:absolute;left:0px;  top:300px; width:45rem; height:5.5rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;"><b>Proveedor:</b> {{$data->nombre}}</p>
                <p class="card-text" style="position:absolute;left:350px;  top:10px;"><b>Fecha:</b> {{$data->fecha}}</p>
                <p class="card-text" style="position:absolute;left:550px;  top:10px;"><b>Porcentaje:</b> 1%</p>
                <p class="card-text" style="position:absolute;left:20px;  top:35px;"><b>CAI:</b> {{$data->cai_compra}}</p>
                <p class="card-text" style="position:absolute;left:550px;  top:35px;"><b>RTN:</b> {{$data->rtn}}</p>
                <p class="card-text" style="position:absolute;left:20px;  top:60px;"><b>Comentarios:</b> RETENCION DEL 1%</p>
            </div>
        </div>

        <div class="card border border-dark" style="position:absolute;left:0px;  top:420px; width:45rem; height:4rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;"><b>Linea</b></p>
                <p class="card-text" style="position:absolute;left:150px;  top:10px;"><b>Descripción</b></p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;"><b>Valor</b></p>
                <p class="card-text" style="position:absolute;left:600px;  top:10px;"><b>Retenido</b></p>
                <p class="card-text" style="position:absolute;left:30px;  top:30px;">1</p>
                <p class="card-text" style="position:absolute;left:90px;  top:30px;">FACTURA N° {{$data->numero_factura}} / {{$data->fecha_emision}}</p>
                <p class="card-text" style="position:absolute;left:455px;  top:30px;">{{ $data->total }}</p>
                <p class="card-text" style="position:absolute;left:605px;  top:30px;">{{$data->monto_retencion}}</p>
            </div>
        </div>

        <div class="card border border-dark" style="position:absolute;left:0px;  top:520px; width:45rem; height:3rem;">
            <div class="card-body">
                <p class="card-text" style="position:absolute;left:50px;  top:10px;">"{{$numeroLetras}}"</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;"><b><h5>Valor Retenido: {{$data->monto_retencion}}</h5></b></p>
            </div>
        </div>

        <div style="position:absolute;left:0px;  top:820px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">_______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;">_______________________________________</p>
                <p class="card-text" style="position:absolute;left:20px;  top:25px; ">FACTURA N° {{$data->numero_factura}} / {{$data->fecha_emision}}</p>
                <p class="card-text" style="position:absolute;left:495px;  top:25px;">DISTRIBUCIONES VALENCIA</p>
        </div>
    </div>




        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>

</html>
