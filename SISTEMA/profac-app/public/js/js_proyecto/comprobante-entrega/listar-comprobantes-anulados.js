
$(document).ready(function() {
    $('#tbl_listar_compras').DataTable({
        "order": [3, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [3, 'desc'],
        pageLength: 10,
        responsive: true,


        "ajax": "/comprovante/entrega/listado/anulados",
        "columns": [
            {
                data: 'numero_comprovante'
            },
            {
                data: 'nombre_cliente'
            },
            {
                data: 'RTN'
            },
            {
                data: 'fecha_emision'
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
                data:'estado'
            },
            {
                data: 'name'
            },
            {
                data:'comentarioAnulado'
            },
            {
                data:'fecha_creacion'
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
