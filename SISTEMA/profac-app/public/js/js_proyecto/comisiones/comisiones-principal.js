

obtenerVendedor();

function obtenerVendedor(){
    $('#vendedorSelect').select2({
        ajax:{
            url:'/ventas/corporativo/vendedores',
            data: function(params) {
                var query = {
                    search: params.term,
                    type: 'public',
                    page: params.page || 1
                }

                return query;
            }

        }
    });
}

function buscarFacturas(){
    var mes = document.getElementById('mes').value;
    var idVendedor = document.getElementById('vendedorSelect').value;
            $("#tbl_facturasVendedor_cerradas").dataTable().fnDestroy();
            $("#tbl_facturasVendedor_sinCerrar").dataTable().fnDestroy();

            $('#tbl_facturasVendedor_cerradas').DataTable({
                "order": [1, 'asc'],
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
                "ajax": "/comisiones/facturas/buscar/"+mes+"/"+idVendedor,
                "columns": [


                    {
                        data: 'id'
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
                        data: 'fechaGracia'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'estadoPago'
                    },
                    {
                        data: 'comision'
                    },
                    {
                        data: 'acciones'
                    }

                ]


            });
            $('#tbl_facturasVendedor_sinCerrar').DataTable({
                "order": [1, 'asc'],
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
                "ajax": "/comisiones/facturas/buscar2/"+mes+"/"+idVendedor,
                "columns": [


                    {
                        data: 'id'
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
                        data: 'fechaGracia'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'estadoPago'
                    },
                    {
                        data: 'comision'
                    },
                    {
                        data: 'acciones'
                    }

                ]


            });

            document.getElementById("btnComiMasivo").removeAttribute("disabled");

}

function validarTecho(){

    var mest = document.getElementById('mes').value;
    var idVendedort = document.getElementById('vendedorSelect').value;

    axios.get("existencia/techo/"+mest+"/"+idVendedort)
    .then(response => {


        //console.log(response);
        let data = response.data;
        if(data.permiso == 0){

            Swal.fire({
                icon: 'info',
                title: 'Cuidado!',
                text: data.message
            })
        }
        if(data.permiso == 1){

            buscarFacturas();
        }

    })
    .catch(err => {
        let data = err.response.data;
        Swal.fire({
            icon: data.icon,
            title: data.title,
            text: data.text
        })
        console.error(err);

    });
}

$(document).on('submit', '#comisionFormMasivo', function(event) {
    event.preventDefault();
    guardarComision();
});

function guardarComision() {

        $('#modal_comision_crearMasivo').modal('hide');
        $('#modalSpinnerLoading').modal('show');

        var data = new FormData($('#comisionFormMasivo').get(0));
        //console.log(data);
        axios.post("/comision/guardar/masivo", data)
            .then(response => {



                let data = response.data;
                if(data.permiso == 0){

                    Swal.fire({
                        icon: 'warning',
                        title: 'Cuidado!',
                        text: data.message
                    })

                }else{

                    document.getElementById("comisionFormMasivo").reset();

                    // $('#tbl_techos_guardados').DataTable().ajax.reload();

                    $('#modalSpinnerLoading').modal('hide');
                     Swal.fire({
                         icon: 'success',
                         title: 'Exito!',
                         text: "Asignado y guardado con Éxito."
                     });


                     window.location.href = "/comisiones/historico";

                }



            })
            .catch(err => {
                let data = err.response.data;
                $('#modalSpinnerLoading').modal('hide');

                document.getElementById("comisionFormMasivo").reset();
                Swal.fire({
                    icon: data.icon,
                    title: data.title,
                    text: data.text
                })
                console.error(err);

            });
}

