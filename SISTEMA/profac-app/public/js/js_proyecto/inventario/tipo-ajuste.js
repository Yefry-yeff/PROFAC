

$(document).on('submit', '#crearTipoAjusteForm', function(event) {
    event.preventDefault();
    guardarTipoAjuste();
});

    function guardarTipoAjuste() {
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#crearTipoAjusteForm').get(0));

        axios.post("/inventario/tipoajuste/guardar", data)
            .then(response => {
                $('#modalSpinnerLoading').modal('hide');


                $('#crearTipoAjusteForm').parsley().reset();

                document.getElementById("crearTipoAjusteForm").reset();
                $('#modal_tipo_ajuste_crear').modal('hide');

                $('#tbl_tipos_listar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Tipo de Ajuste guardado con exito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_tipo_ajuste_crear').modal('hide');
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            })

    }

    $(document).ready(function() {
        $('#tbl_tipos_listar').DataTable({
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
            "ajax": "/inventario/tipoajuste/listar",
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'name'
                },
                {
                    data: 'opciones'
                }

            ]


        });
    })

    function datosTipoAjuste(id){

        let data = {id:id}
        axios.post('/inventario/tipoajuste/datos',data)
        .then( response =>{

            let datos = response.data.datos;

            document.getElementById('nombre_editar').value = datos.nombre;
            document.getElementById('idAjuste').value = datos.id;

            $('#modal_tipo_ajuste_editar').modal('show');
        })
        .catch( err=>{
            console.log(err)
        })
    }

    $(document).on('submit', '#modal_tipo_ajuste_editar', function(event) {

        event.preventDefault();
        editarTipoAjuste();

    });

    function editarTipoAjuste(){

        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($('#editarTipoAjusteForm').get(0));


        axios.post('/inventario/tipoajuste/editar',data)
        .then( response =>{
            $('#modalSpinnerLoading').modal('hide');


            $('#editarTipoAjusteForm').parsley().reset();

            document.getElementById("editarTipoAjusteForm").reset();
            $('#modal_tipo_ajuste_editar').modal('hide');

            $('#tbl_tipos_listar').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Tipo de Ajuste editado con exito."
            })

        })
        .catch( err=>{
            let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_tipo_ajuste_editar').modal('hide');

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

        })
    }

    /*function desactivarTipoAjuste(id){

        let data = {id:id}
        axios.post('/inventario/tipoajuste/desactivar',data)
        .then( response =>{
            $('#tbl_tipos_listar').DataTable().ajax.reload();
        })
        .catch( err=>{
            console.log(err)
        })
    }*/
