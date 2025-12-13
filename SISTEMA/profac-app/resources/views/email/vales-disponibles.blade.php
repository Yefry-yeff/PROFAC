<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vales Disponibles</title>
</head>
<body>
    <div class="card" style="width: 18rem;">
        <div class="card-body">
          <h2 class="card-title">vales disponibles:</h2>
          <div class="list-group">
            <a class="list-group-item list-group-item-action active">
              Vales Disponibles para anular.
            </a>

            @for ($i = 0; $i < count($valesArray); $i++)
            <a href="https://ventas.distribucionesvalencia.hn/vale/restar/inventario" class="list-group-item list-group-item-action">Vale No: {{$valesArray[$i]}}</a>
            @endfor           
        
          
          </div>
       
        </div>
      </div>
</body>
</html>