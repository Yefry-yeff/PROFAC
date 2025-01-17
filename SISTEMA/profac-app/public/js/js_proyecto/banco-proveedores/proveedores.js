
$(document).ready(function() {
    $('.i-checks').iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
    });
});

function obtenerDepartamentos() {
    document.getElementById("depto_prov").innerHTML = "<option selected disabled>---Seleccione un Departamento---</option>"
    var id = document.getElementById("pais_prov").value;
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

            document.getElementById("depto_prov").innerHTML = html;


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
    var id = document.getElementById("depto_prov").value;

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

            document.getElementById("municipio_prov").innerHTML = html;


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

$(document).ready(function() {
    $('#tbl_proveedoresListar').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        buttons: [{
                extend: 'copy'
            },
            {
                extend: 'csv'
            },
            {
                extend: 'excel',
                title: 'ExampleFile'
            },
            {
                extend: 'pdf',
                title: 'ExampleFile'
            },

            {
                extend: 'print',
                customize: function(win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ],
        "ajax": "/proveedores/listar/proveedores",
        "columns": [{
                data: 'id'
            },
            {
                data: 'nombre'
            },
            {
                data: 'direccion'
            },
            {
                data: 'contacto'
            },
            {
                data: 'correo_1'
            },
            {
                data: 'rtn'
            },
            {
                data: 'retencion'
            },
            {
                data: 'estado'
            },
            {
                data: 'opciones'
            },
        ]


    });
})


$(document).on('submit', '#proveedorCreacionForm', function(event) {

    event.preventDefault();
    crearProveedores();

});


function crearProveedores() {
    var data = new FormData($('#proveedorCreacionForm').get(0));

    $.ajax({
        type: "POST",
        url: "/proveedores/crear",
        data: data,
        contentType: false,
        cache: false,
        processData: false,
        dataType: "json",
        success: function(data) {


            document.getElementById("proveedorCreacionForm").reset();
            $('#modal_proveedores_crear').modal('hide');


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor creado con exito."
            })
            $('#proveedorCreacionForm').parsley().reset();
            $('#tbl_proveedoresListar').DataTable().ajax.reload();






        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseJSON.message);
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'Ha ocurrido un error al mostrar la lista de proveedores'
            })
        }
    });
}

function desactivarProveedor(id) {


    Swal.fire({
        title: '¿Esta seguro de desactivar este proveedor?',
        text: "¡Si desactiva este proveedor, ya no podrá ingresar o almacenar productos con este proveedor.!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '¡Sí, Desactivar proveedor!'
    }).then((result) => {
        if (result.isConfirmed) {


            axios.post('/proveedores/desactivar', {
                    "id": id
                })
                .then(function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Proveedor desactivado con exito."
                    })

                    $('#tbl_proveedoresListar').DataTable().ajax.reload();

                })
                .catch(function(error) {
                    // handle error
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: "Ha ocurrido un error al desactivar el proveedor."
                    })
                })





        }
    })


}

function activarProveedor(id) {
    axios.post('/proveedores/desactivar', {
            "id": id
        })
        .then(function(response) {

            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor Activado con exito."
            })

            $('#tbl_proveedoresListar').DataTable().ajax.reload();

        })
        .catch(function(error) {
            // handle error
            console.log(error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "Ha ocurrido un error al Activar el proveedor."
            })
        })

}

function mostrarModalEditar(id){

    let data = {"id":id}

    axios.post('/proveedores/editar',data)
    .then( response=>{

        let proveedor =  response.data.proveedor;

        let paisProveedor = response.data.paisProveedor;
        let departamentoProveedor = response.data.departamentoProveedorId;
        let municipioProveedor = response.data.municipioProveedorId;


        let paises = response.data.paises;
        let departamentos = response.data.departamentos;
        let municipios = response.data.municipios;
        let tipoPersonalidad = response.data.tipoPersonalidad;
        let categorias = response.data.categoria;


        let htmlSelectPais ="";
        let htmlSelectDepto = "";
        let htmlSelectMunicipio = "";
        let htmlPersonalidad ="";
        let htmlCategoria ="";


        document.getElementById("idProveedor").value = proveedor.id;
        document.getElementById("editar_codigo_prov").value = proveedor.codigo;
        document.getElementById("editar_nombre_prov").value = proveedor.nombre;
        document.getElementById("editar_direccion_prov").value = proveedor.direccion;
        document.getElementById("editar_contacto_prov").value = proveedor.contacto;
        document.getElementById("editar_telefono_prov").value = proveedor.telefono_1;
        document.getElementById("editar_telefono_prov_2").value = proveedor.telefono_2;
        document.getElementById("editar_correo_prov").value = proveedor.correo_1;
        document.getElementById("editar_correo_prov_2").value = proveedor.correo_2;
        document.getElementById("editar_rtn_prov").value = proveedor.rtn;


        paises.forEach( pais =>{
            if(pais.id === paisProveedor ){

                htmlSelectPais += `
                <option value="${ pais.id }" selected>${pais.nombre}</option>
                `
            }else{
                htmlSelectPais += `
                <option value="${ pais.id }">${pais.nombre}</option>
                `
            }

        });



        departamentos.forEach( departamento =>{
            if(departamento.id === departamentoProveedor ){

                htmlSelectDepto += `
                <option value="${ departamento.id }" selected>${departamento.nombre}</option>
                `
            }else{
                htmlSelectDepto += `
                <option value="${ departamento.id }">${departamento.nombre}</option>
                `
            }

        });

        municipios.forEach( municipio =>{

            if(municipio.id == municipioProveedor){

                htmlSelectMunicipio += `
                <option value="${ municipio.id }" selected>${municipio.nombre}</option>
                `

            }else{

                htmlSelectMunicipio += `
                <option value="${ municipio.id }">${municipio.nombre}</option>
                `
            }

        });

        tipoPersonalidad.forEach( personalidad => {
            if(personalidad.id == proveedor.tipo_personalidad_id){

                htmlPersonalidad += `
                <option value="${ personalidad.id }" selected>${personalidad.nombre}</option>
                `

                }else{

                    htmlPersonalidad += `
                <option value="${ personalidad.id }">${personalidad.nombre}</option>
                `
                }

        })

        categorias.forEach( categoria =>{

            if(categoria.id == proveedor.categoria_id){

                htmlCategoria += `
                <option value="${ categoria.id }" selected>${categoria.nombre}</option>
                `

                }else{

                    htmlCategoria += `
                <option value="${ categoria.id }">${categoria.nombre}</option>
                `
                }

        } );


        document.getElementById("editar_pais_prov").innerHTML = htmlSelectPais;
        document.getElementById("editar_depto_prov").innerHTML = htmlSelectDepto;
        document.getElementById("editar_municipio_prov").innerHTML = htmlSelectMunicipio;
        document.getElementById("editar_giro_neg_prov").innerHTML = htmlPersonalidad;
        document.getElementById("editar_categoria_prov").innerHTML = htmlCategoria;











        $("#modal_proveedores_editar").modal("show");
        return;
    })
    .catch(err=>{

        console.log(err)

    });


}

function editarObtenerDepartamentos() {

    var id = document.getElementById("editar_pais_prov").value;


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

            document.getElementById("editar_depto_prov").innerHTML = html;


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

function editarObtenerMunicipios() {
    //municipio_prov
    var id = document.getElementById("editar_depto_prov").value;

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

            document.getElementById("editar_municipio_prov").innerHTML = html;


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

$(document).on('submit', '#proveedorEditarForm', function(event) {
event.preventDefault();
editarProveedor();
});

function editarProveedor(){
    var data = new FormData($('#proveedorEditarForm').get(0));
    axios.post("/proveedores/editar/guardar", data)
    .then( response =>{
        document.getElementById("proveedorEditarForm").reset();
            $('#modal_proveedores_editar').modal('hide');


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Proveedor editado con exito."
            })

            $('#tbl_proveedoresListar').DataTable().ajax.reload();
            $('#proveedorEditarForm').parsley().reset();


    })
    .catch( err =>{
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'Ha ocurrido un error al editar el proveedor'
            })
    })




}
