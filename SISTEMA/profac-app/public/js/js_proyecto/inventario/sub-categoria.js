

$(document).on('submit', '#crearSubCategoriaForm', function(event) {
    event.preventDefault();
    guardarSubCategoria();
});

    function guardarSubCategoria() {
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#crearSubCategoriaForm').get(0));

        axios.post("/sub_categoria/guardar", data)
            .then(response => {
                $('#modalSpinnerLoading').modal('hide');


                $('#crearSubCategoriaForm').parsley().reset();

                document.getElementById("crearSubCategoriaForm").reset();
                $('#modal_sub_categoria_crear').modal('hide');

                $('#tbl_sub_categorias_listar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Sub-Categoria guardado con exito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_sub_categoria_crear').modal('hide');
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            })

    }

    $(document).ready(function() {
        $('#tbl_sub_categorias_listar').DataTable({
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
            "ajax": "/sub_categoria/listar",
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'descripcion'
                },
                {
                    data: 'categoria_descripcion'
                },
                {
                    data: 'opciones'
                }

            ]


        });
        obtenerCategorias();
    })

    function datosSubCategoria(id){

        let data = {id:id}
        axios.post('/sub_categoria/datos',data)
        .then( response =>{

            document.getElementById("categoria_producto_id_editar").innerHTML= '';

            let datos = response.data.datos;

            document.getElementById('descripcion_sub_categoria_editar').value = datos.descripcion;
            document.getElementById('idSubCategoria').value = datos.id;

            $('#modal_sub_categoria_editar').modal('show');
        })
        .catch( err=>{
            console.log(err)
        })
        obtenerCategoriasEditar()
    }

    $(document).on('submit', '#modal_sub_categoria_editar', function(event) {

            event.preventDefault();
            editarSubCategoria();

    });

    //////////////////////////////////////////////
    function obtenerCategorias() {

        axios.get("/sub_categoria/listar/categorias")
            .then( response=>{
            let data = response.data.categorias;
            let htmlSelect = ''
            data.forEach(element => {
            htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
            });
            document.getElementById('categoria_producto_id').innerHTML += htmlSelect;
        })
        .catch(err=>{
            console.log(err.response.data)
            Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error',
            })
        })
    }

    function obtenerCategoriasEditar() {

        axios.get("/sub_categoria/listar/categorias")
            .then( response=>{
            let data = response.data.categorias;
            let htmlSelect = ''
            data.forEach(element => {
            htmlSelect += `<option value="${element.id}">${element.descripcion}</option>`
            });
            document.getElementById('categoria_producto_id_editar').innerHTML += htmlSelect;
        })
        .catch(err=>{
            console.log(err.response.data)
            Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Ha ocurrido un error',
            })
        })
    }
    //////////////////////////////////////////////

     function editarSubCategoria(){

        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($('#editarSubCategoriaForm').get(0));


        axios.post('/sub_categoria/editar',data)
        .then( response =>{
            $('#modalSpinnerLoading').modal('hide');


            $('#editarSubCategoriaForm').parsley().reset();

            document.getElementById("editarSubCategoriaForm").reset();
            $('#modal_sub_categoria_editar').modal('hide');

            $('#tbl_sub_categorias_listar').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Sub-Categoria editado con exito."
            })

        })
        .catch( err=>{
            let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_sub_categoria_editar').modal('hide');

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

        })
    }

