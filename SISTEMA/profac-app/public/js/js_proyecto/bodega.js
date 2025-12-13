// $('#bodegaCreacion').submit(function(e){
    
//     e.preventDefault();
//     console.log('llego');
//     //crearBodega();
// });



// $(function () {
//     $('#bodegaCreacion').parsley().on('field:validated', function() {

//       var ok = $('.parsley-error').length === 0;
//       $('.bs-callout-info').toggleClass('hidden', !ok);
//       $('.bs-callout-warning').toggleClass('hidden', ok);
//     })

//     .on('form:submit', function() {
//       return false; // Don't submit form for this demo
//     });
//   });

var numeroInputs = 0;
var arregloIdInputs = [0];

$(document).on('submit', '#bodegaCreacion', function(event){
       event.preventDefault();
    crearBodega();
    
});


function crearBodega(){
    var data = new FormData($('#bodegaCreacion').get(0));

    for (var i = 0; i < arregloIdInputs.length; i++) {
        data.append('arregloIdInputs[]', arregloIdInputs[i]);
      }

      console.log(data.arregloIdInputs)
  
    $.ajax({
        type:"POST",
        url: "/bodega/crear",
        data: data,
        contentType: false,
        cache: false,
        processData:false,
        dataType:"json",
        success: function(data){

            document.getElementById("bodegaCreacion").reset();
            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: 'Exito al crear bodega.'
            })

            //console.log(data);
            

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log( jqXHR.responseJSON.message);
            Swal.fire({
                icon: 'warning',
                title: 'Error...',
                text: jqXHR.responseJSON.message
            })
        }
    })


}

function agregarInputs(){
    //console.log(arregloIdInputs);

    numeroInputs += 1;
   

    let html =`
    <div id=${numeroInputs} class="row no-gutters">
        <div class="form-group col-7 col-sm-7 col-md-7 col-lg-7 col-xl-7 ">
            <div class="d-flex">
                <button class="btn btn-danger" type="button" style="display: inline" onclick="eliminarInput(${numeroInputs})"><i
                        class="fa-regular fa-rectangle-xmark"></i></button>
                <div style="width:100%">
                    <label for="segmento1" class="sr-only">Letra de
                        Seccion</label>
                    <input type="text" placeholder="Letra de seccion" id="segmento${numeroInputs}"
                        name="segmento${numeroInputs}" class="form-control" pattern="[A-Z]{1}"
                        data-parsley-required data-parsley-pattern="[A-Z]{1}"
                        autocomplete="off">
                </div>
            </div>
        </div>

        <div class="form-group col-5 col-sm-5 col-md-5 col-lg-5 col-xl-5">
            <label for="seccion1" class="sr-only">Numero de secciones</label>
            <input type="number" placeholder="Numero de secciones" id="seccion${numeroInputs}"
                name="seccion${numeroInputs}" class="form-control" min="1" data-parsley-required
                autocomplete="off">
        </div>

    </div>
    `;


    arregloIdInputs.splice(numeroInputs, 0, numeroInputs);
    document.getElementById('divSecciones').insertAdjacentHTML('beforeend', html);

    //console.log(arregloIdInputs);

   
}

function eliminarInput(id){
    const element = document.getElementById(id);
    element.remove();
    console.log(arregloIdInputs);
    
    /*arregloIdInputs.splice(id, 1);
    console.log(arregloIdInputs);*/

    var myIndex = arregloIdInputs.indexOf(id);
    if (myIndex !== -1) {
        arregloIdInputs.splice(myIndex, 1);
    }
    console.log(arregloIdInputs)
}

function obtenerDepartamentos() {
    document.getElementById("depto_bodega").innerHTML = "<option selected disabled>---Seleccione un Departamento---</option>"
    var id = document.getElementById("pais_bodega").value;
    //console.log(id)
    ///proveedores/obeter/departamento
    let datos = {
        "id": id
    };

    axios.post('/proveedores/obeter/departamentos', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            let html = "";

            array.forEach(departamento => {

                html +=
                    `
            <option value="${ departamento.id }">${departamento.nombre}</option>   
           `
            });

            document.getElementById("depto_bodega").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los departamentos"
            })
        })



}

function obtenerMunicipios() {
    //municipio_prov
    var id = document.getElementById("depto_bodega").value;

    let datos = {
        "id": id
    }


    axios.post('/proveedores/obeter/municipios', datos)
        .then(function(response) {

            let array = response.data.departamentos;
            let html = "";

            array.forEach(municipio => {

                html +=
                    `
            <option value="${ municipio.id }">${municipio.nombre}</option>   
           `
            });

            document.getElementById("municipio_bodega").innerHTML = html;


        })
        .catch(function(error) {
            // handle error
            console.log(error);

            Swal.fire({
                icon: 'error',
                title: 'Error...',
                text: "Ha ocurrido un error al obtener los municipios"
            })
        })

}