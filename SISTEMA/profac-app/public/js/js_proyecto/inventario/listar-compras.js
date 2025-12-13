
$(document).ready(function() {
    $('#tbl_listar_compras').DataTable({
        "order": [0, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },

        pageLength: 10,
        responsive: true,


        "ajax": "/producto/compras/listar",
        "columns": [{
                data: 'id'
            },
            {
                data: 'numero_orden'
            },
            {
                data: 'numero_factura'
            },
            {
                data: 'fecha_emision'
            },
            {
                data: 'fecha_vencimiento'
            },
            {
                data: 'total'
            },
            {
                data: 'estado_retencion'
            },
            {
                data: 'nombre'
            },
            {
                data: 'usuario'
            },
            {
                data: 'fecha_registro'
            },
            {
                data: 'anular'
            },
            {
                data: 'opciones'
            }

        ]


    });
    })

function anularCompra(idCompra){

    Swal.fire({
    title: '¿Está seguro de anular esta compra?',
    text:'Una vez que ha sido anulada la compra no se podrá recibir el producto en bodega.',
    showDenyButton: false,
    showCancelButton: true,
    confirmButtonText: 'Si, Anular Compra',
    cancelButtonText: `Cancelar`,
    }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {

        //Swal.fire('Saved!', '', 'success')
        this.anularCompraGuardar(idCompra);

    }
    })
}

function anularCompraGuardar(idCompra){

    axios.post("/producto/compra/anular", {idCompra:idCompra})
    .then( response =>{


        let data = response.data;
        Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text,
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

function insertarBotonExportar(){

    try {
        var compras_mes = document.getElementById('compras_mes').value;

        let htmlSelect = ''

        htmlSelect =   `
                <a href="/compras/excel_mes/${compras_mes}" class="btn add-btn btn-primary"><i class="fa fa-plus "></i> Exportar Excel</a>
                        `

        document.getElementById('exportar').innerHTML = htmlSelect;

    } catch (error) {

    }

}
