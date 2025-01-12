

$('#tbl_techos_guardados').DataTable({
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
    "ajax": "/listar/techos",
    "columns": [
        {
            data: 'id'
        },
        {
            data: 'mes'
        },
        {
            data: 'vendedor'
        },
        {
            data: 'techo'
        },
        {
            data: 'fechaRegistro'
        },
        {
            data: 'userRegistro'
        },
        {
            data: 'acciones'
        }

    ]


});


$(document).on('submit', '#techoAddForm', function(event) {
    event.preventDefault();
    guardarTecho();
});

    function guardarTecho() {

        $('#modal_techo_crear').modal('hide');
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#techoAddForm').get(0));

        axios.post("/techo/guardar", data)
            .then(response => {



                document.getElementById("techoAddForm").reset();
                $('#tbl_techos_guardados').DataTable().ajax.reload();

               // $('#tbl_techos_guardados').DataTable().ajax.reload();

               $('#modalSpinnerLoading').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Exito!',
                    text: "Techo agregado con Éxito."
                })

            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');

                document.getElementById("techoAddForm").reset();
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            });



    }

    function editarTecho(idVendedor, mes, techoActual){

        console.log(mes);
        $("#idVendedor").val(idVendedor);
        $("#mesL").val(mes);
        $("#techoAct").val(techoActual);
        $("#modal_techo_editar").modal("show");
    }

    $(document).on('submit', '#techoeditform', function(event) {
        event.preventDefault();
        editarTechos();
    });

        function editarTechos() {

            $('#modal_techo_editar').modal('hide');
            $('#modalSpinnerLoading').modal('show');

            var data = new FormData($('#techoeditform').get(0));

            axios.post("/techo/editar", data)
                .then(response => {



                    document.getElementById("techoeditform").reset();
                    $('#tbl_techos_guardados').DataTable().ajax.reload();

                   // $('#tbl_techos_guardados').DataTable().ajax.reload();

                   $('#modalSpinnerLoading').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Exito!',
                        text: "Techo Editado con Éxito."
                    })

                })
                .catch(err => {
                    let data = err.response.data;
                    $('#modalSpinnerLoading').modal('hide');

                    document.getElementById("techoeditform").reset();
                    Swal.fire({
                        icon: data.icon,
                        title: data.title,
                        text: data.text
                    })
                    console.error(err);

                });



        }
