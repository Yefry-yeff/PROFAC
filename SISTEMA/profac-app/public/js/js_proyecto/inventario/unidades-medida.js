

$(document).on('submit', '#crearUnidadForm', function(event) {
    event.preventDefault();
    guardarUnidad();
});

    function guardarUnidad() {
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#crearUnidadForm').get(0));

        axios.post("/inventario/unidades/guardar", data)
            .then(response => {
                $('#modalSpinnerLoading').modal('hide');


                $('#crearUnidadForm').parsley().reset();

                document.getElementById("crearUnidadForm").reset();
                $('#modal_producto_crear').modal('hide');

                $('#tbl_unidades_listar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Marca creado con exito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_producto_crear').modal('hide');
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            })

    }

    $(document).ready(function() {
        $('#tbl_unidades_listar').DataTable({
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
            "ajax": "/inventario/unidades/listar",
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'simbolo'
                },
                {
                    data: 'opciones'
                }

            ]


        });
    })

    function datosUnidad(id){

        let data = {id:id}
        axios.post('/inventario/unidades/datos',data)
        .then( response =>{

            let datos = response.data.datos;

            document.getElementById('nombre_producto_editar').value = datos.nombre;
            document.getElementById('simbolo_producto_editar').value = datos.simbolo;
            document.getElementById('idUnidad').value = datos.id;

            $('#modal_producto_editar').modal('show');
        })
        .catch( err=>{
            console.log(err)
        })
    }

    $(document).on('submit', '#modal_producto_editar', function(event) {

            event.preventDefault();
            editarUnidad();

    });

     function editarUnidad(){

        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($('#editarProductoForm').get(0));


        axios.post('/inventario/unidades/editar',data)
        .then( response =>{
            $('#modalSpinnerLoading').modal('hide');


            $('#editarProductoForm').parsley().reset();

            document.getElementById("editarProductoForm").reset();
            $('#modal_producto_editar').modal('hide');

            $('#tbl_unidades_listar').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Unidad editada con exito."
            })

        })
        .catch( err=>{
            let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_producto_editar').modal('hide');

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

        })
    }


