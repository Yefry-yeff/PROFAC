
$(document).ready(function() {
    $('#tbl_listar_comprobantes').DataTable({
        "order": [3, 'desc'],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [9, 'desc'],
        pageLength: 10,
        responsive: true,


        "ajax": "/comprovante/entrega/listado/activos",
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
                data:'fecha_creacion'
            },

            {
                data: 'opciones'
            }

        ]


    });
    })


    function anularComprobanteConfirmar(idComprobante){

        Swal.fire({
        title: '¿Está seguro de anular este comprobante?',


            // --------------^-- define html element with id
        html: ' <textarea rows="4" placeholder="Es obligatorio describir el motivo." required id="comentarion"     class="form-group form-control" data-parsley-required></textarea>',
        showDenyButton: false,
        showCancelButton: false,
        showDenyButton:true,
        confirmButtonText: 'Si, Anular Comprobante',
        denyButtonText: `Cancelar`,
        confirmButtonColor:'#19A689',
        denyButtonColor:'#676A6C',
        }).then((result) => {

            let motivo = document.getElementById("comentarion").value

        if (result.isConfirmed && motivo ) {


            anularComprobante(idComprobante,motivo);

        }else if(result.isDenied){
            Swal.close()
        }else{
            Swal.close()
        }
        })
    }

function anularComprobante(idComprobante,motivo){
           axios.post('/comprobante/entrega/anular', {'idComprobante':idComprobante,'motivo':motivo})
           .then(response=>{
            let data = response.data;
                Swal.fire({
                            icon: data.icon,
                            title: data.title,
                            html: data.text,
                        });
                        $('#tbl_listar_comprobantes').DataTable().ajax.reload();
           })
           .catch(err=>{
            Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Ha ocurrido un error al anular el comprobante.',
                })
           })
    }
