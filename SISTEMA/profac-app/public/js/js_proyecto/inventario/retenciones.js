

$(document).ready(function() {
    console.log("entro")
    $('#tbl_retenciones').DataTable({
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
        "ajax": "/inventario/retenciones/listar",
        "columns": [
            {
                data: 'codigo'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'valor'
            },
            {
                data: 'tipo'
            },
            {
                data: 'name'
            },
            {
                data: 'fecha'
            },
            {
                data: 'opciones'
            },


        ]


    });
})

$(document).on('submit', '#retencionCreacionForm', function(event) {

    event.preventDefault();
    crear_Retencion();

    });

function  crear_Retencion(){
    var data = new FormData($('#retencionCreacionForm').get(0));

    axios.post("/proveedores/retencion/crear", data)
    .then( response =>{
        document.getElementById("retencionCreacionForm").reset();
            $('#modal_retenciones_crear').modal('hide');


            Swal.fire({
                icon: 'success',
                title: 'Exito!',
                text: "Retencion creada con exito."
            })

            $('#tbl_retenciones').DataTable().ajax.reload();

    })
}

