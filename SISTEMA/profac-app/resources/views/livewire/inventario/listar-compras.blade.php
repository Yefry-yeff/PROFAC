<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Listado de Compras</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">
                    <a>Listado</a>
                </li>

                <li class="breadcrumb-item">
                    <a>Detalle de compra</a>
                </li>
                <li class="breadcrumb-item">
                    <a>Recepción de producto</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id="tbl_listar_compras" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>Codigo Interno</th>
                                        <th>N° Compra</th>
                                        <th>N° Factura</th>
                                        <th>Emisión</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Total de compra</th>
                                        <th>Retencion</th>
                                        <th>Proveedor</th>
                                        <th>Registrado por</th>
                                        <th>Fecha de Registro</th>
                                        <th>Anular</th>
                                        <th>Opciones</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
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
        </script>
    @endpush
</div>
