
$(document).on('submit', '#crearCategoriaForm', function(event) {
    event.preventDefault();
    guardarCategoria();
});

    function guardarCategoria() {
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#crearCategoriaForm').get(0));

        axios.post("/categoria/guardar", data)
            .then(response => {
                $('#modalSpinnerLoading').modal('hide');


                $('#crearCategoriaForm').parsley().reset();

                document.getElementById("crearCategoriaForm").reset();
                $('#modal_categoria_crear').modal('hide');

                $('#tbl_categorias_listar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Categoria guardado con exito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_categoria_crear').modal('hide');
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            })

    }

    $(document).ready(function() {
        $('#tbl_categorias_listar').DataTable({
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
            "ajax": "/categoria/listar",
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'descripcion'
                },
                {
                    data: 'opciones'
                }

            ]


        });
    })

    function datosCategoria(id){

        let data = {id:id}
        axios.post('/categoria/datos',data)
        .then( response =>{

            let datos = response.data.datos;

            document.getElementById('descripcion_categoria_editar').value = datos.descripcion;
            document.getElementById('idCategoria').value = datos.id;

            $('#modal_categoria_editar').modal('show');
        })
        .catch( err=>{
            console.log(err)
        })
    }

    $(document).on('submit', '#modal_categoria_editar', function(event) {

            event.preventDefault();
            editarCategoria();

    });

     function editarCategoria(){

        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($('#editarCategoriaForm').get(0));


        axios.post('/categoria/editar',data)
        .then( response =>{
            $('#modalSpinnerLoading').modal('hide');


            $('#editarCategoriaForm').parsley().reset();

            document.getElementById("editarCategoriaForm").reset();
            $('#modal_categoria_editar').modal('hide');

            $('#tbl_categorias_listar').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Categoria editado con exito."
            })

        })
        .catch( err=>{
            let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_categoria_editar').modal('hide');

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

        })
    }
