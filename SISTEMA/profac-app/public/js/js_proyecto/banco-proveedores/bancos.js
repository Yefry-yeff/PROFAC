

$(document).on('submit', '#crearBancoForm', function(event) {
    event.preventDefault();
    guardarBanco();
});

    function guardarBanco() {
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#crearBancoForm').get(0));

        axios.post("/banco/bancos/guardar", data)
            .then(response => {
                $('#modalSpinnerLoading').modal('hide');


                $('#crearBancoForm').parsley().reset();

                document.getElementById("crearBancoForm").reset();
                $('#modal_banco_crear').modal('hide');

                $('#tbl_bancos_listar').DataTable().ajax.reload();


                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Banco guardado con exito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_banco_crear').modal('hide');
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            })

    }

    $(document).ready(function() {
        $('#tbl_bancos_listar').DataTable({
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
            "ajax": "/banco/bancos/listar",
            "columns": [{
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'cuenta'
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

    function datosBanco(id){

        let data = {id:id}
        axios.post('/banco/bancos/datos',data)
        .then( response =>{

            let datos = response.data.datos;

            document.getElementById('nombre_banco_editar').value = datos.nombre;
            document.getElementById('cuenta_editar').value = datos.cuenta;
            document.getElementById('idBanco').value = datos.id;

            $('#modal_banco_editar').modal('show');
        })
        .catch( err=>{
            console.log(err)
        })
    }

    $(document).on('submit', '#modal_banco_editar', function(event) {

            event.preventDefault();
            editarBanco();

    });

     function editarBanco(){

        $('#modalSpinnerLoading').modal('show');
        var data = new FormData($('#editarBancoForm').get(0));


        axios.post('/banco/bancos/editar',data)
        .then( response =>{
            $('#modalSpinnerLoading').modal('hide');


            $('#editarBancoForm').parsley().reset();

            document.getElementById("editarBancoForm").reset();
            $('#modal_banco_editar').modal('hide');

            $('#tbl_bancos_listar').DataTable().ajax.reload();


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Banco editado con exito."
            })

        })
        .catch( err=>{
            let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');
                $('#modal_banco_editar').modal('hide');

                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

        })
    }


