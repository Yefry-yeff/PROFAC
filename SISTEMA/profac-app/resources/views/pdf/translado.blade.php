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
            background-image: url('img/membrete/membrete2.jpg');
            
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
        <div class="card border border-dark" style="margin-left:44px;  margin-top:150px; width:45rem; height:4rem;">
            <div class="card-header">
                <b>Registro de Translado No.  </b>
               
            </div> 


        </div>

        <div class="card border border-dark"   style="margin-left:44px; margin-top:10px; width:45rem; height:7rem;">
            <div class="card-body">

                <p class="card-text "  style="position:absolute;left:20px;  top:10px;"><b>Fecha de translado: </b>
                </p>
                <p class="card-text" style="position:absolute;left:390px;  top:10px;"><b>Realizado por: </b> </p>                

                <p class="card-text" style="position:absolute;left:20px;  top:70px;"><b>Comentario: </b> </p>
                







            </div>
        </div>

        <div class="card border border-dark" style="position: relative; margin-left:44px; margin-top:10px; width:45rem; page-break-inside: auto;">
            <div >


                <table  class="" style=" ">
                    <thead>
                    <tr>
                      <th>Código</th>
                      
                      <th>Descripción</th>   
                      <th>Unidad de Medida</th>                 
                      <th>Bodega Origen</th>
                      <th>Bodega Destino</th>                     
                    
                      <th>Cantidad</th>
                     
                    </tr>
                </thead>
                <tbody>
                    
               

                </tbody>      
                  </table>
            </div>
        </div>



        

            
   

        
      
        <div style=" position: relative; margin-left:44px;">

    


            <div style="position:absolute;left:0px;  margin-top:{{$altura2}}px;  width:45rem;">
                <p class="card-text" style="position:absolute;left:20px;  top:10px;">  _______________________________________</p>
                <p class="card-text" style="position:absolute;left:450px;  top:10px;"> _______________________________________</p>


                <p class="card-text" style="position:absolute;left:80px;  top:25px; ">Realizado por </p>
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
