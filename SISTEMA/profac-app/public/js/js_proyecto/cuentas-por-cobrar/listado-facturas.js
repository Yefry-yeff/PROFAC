
$(document).ready(function() {
    $('#tbl_listar_compras').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },

        pageLength: 10,
        responsive: true,



        dom: '<"html5buttons"B>lTfgitp',
        buttons: [

            {
                extend: 'excel',
                title: 'Facuras',
                className:'btn btn-success'
            }
        ],
        "ajax": "/cuentas/cobrar/lista",
        "columns": [
            {
                data:'contador',
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'correlativo'
            },

            {
                data: 'fecha_emision'
            },
            {
                data: 'nombre'
            },
            {
                data: 'descripcion'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'sub_total'
            },
            {
                data: 'isv'
            },
            {
                data: 'total'
            },
            {
                data: 'estado_cobro'
            },
            {
                data: 'creado_por'
            },

            {
                data: 'opciones'
            }

        ]


    });
    })

function anularVentaConfirmar(idFactura){

    Swal.fire({
    title: '¿Está seguro de anular esta factura?',


         // --------------^-- define html element with id
    html: '<p>Una vez que ha sido anulada la factura el producto registrado en la misma sera devuelto al inventario.</p> <textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentario"     class="form-group form-control" data-parsley-required></textarea>',
    showDenyButton: false,
    showCancelButton: false,
    showDenyButton:true,
    confirmButtonText: 'Si, Anular Factura',
    denyButtonText: `Cancelar`,
    confirmButtonColor:'#19A689',
    denyButtonColor:'#676A6C',
    }).then((result) => {

        let motivo = document.getElementById("comentario").value

    if (result.isConfirmed && motivo ) {


        anularVenta(idFactura,motivo);

    }else if(result.isDenied){
        Swal.close()
    }else{
        Swal.close()
    }
    })
}

function anularVenta(idFactura,motivo){

    axios.post("/factura/corporativo/anular", {'idFactura':idFactura,'motivo':motivo})
    .then( response =>{


        let data = response.data;
        Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    html: data.text,
                });
                $('#tbl_listar_compras').DataTable().ajax.reload();

    })
    .catch( err => {

        Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Ha ocurrido un error al anular la compra.',
                })

    })

}
