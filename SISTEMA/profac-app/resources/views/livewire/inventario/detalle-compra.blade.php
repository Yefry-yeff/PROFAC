<div>
    @push('styles')
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Datlle de Compra</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a>Detalle de compra</a>
                </li>
            </ol>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="wrapper wrapper-content animated fadeInRight">


                <div class="ibox mb-0">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-list-check"></i> Informacion de compra</h3>
                    </div>
                    <div class="ibox-content"
                        style="height: 18.5rem;  display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Codigo de compra:
                                </strong> {{$detalleCompra->id}} </small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Número de factura:
                                </strong> {{$detalleCompra->numero_factura}} </small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Número de Orden:
                                    </strong> {{$detalleCompra->numero_orden}} </small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Fecha de emisión:
                            </strong> {{$detalleCompra->fecha_emision}} </small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Fecha de recepción programada:
                            </strong> {{$detalleCompra->fecha_recepcion}} </small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Proveedor: </strong>
                                {{$detalleCompra->nombre}}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Registrado por: </strong>{{$detalleCompra->usuario}}</small></p>
                            <p class="mt-2 mb-2"> <strong> <i class="fa-solid fa-caret-right"></i> Fecha de registro: </strong>{{$detalleCompra->fecha_registro}}</small></p>

                        </div>

                    </div>
                </div>


            </div>
        </div>


        <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
            <div class="wrapper wrapper-content animated fadeInRight">


                <div class="ibox mb-0">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-sack-dollar"></i> Importes</h3>

                    </div>
                    <div class="ibox-content "
                        style="height: 18.5rem; display: flex; flex-direction: column; justify-content: space-between;  ">
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Tipo de compra: </strong> {{$detalleCompra->descripcion}}</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Total de compra </strong> {{ number_format($detalleCompra->total,2)}} Lps.</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Sub-total de compra:
                            </strong> {{number_format($detalleCompra->sub_total,2)}} Lps.</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> ISV de compra: </strong> {{ number_format($detalleCompra->isv_compra,2)}} Lps.</small></p>
                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Retención: </strong> {{ number_format($detalleCompra->monto_retencion,2)}} Lps.</small></p>

                        <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Debito de compra: </strong> {{ number_format($detalleCompra->debito,2)}} Lps.</small></p>
                        {{-- @foreach ($precios as $precio)
                            <p class="mt-2 mb-2 d-block"> <strong> <i class="fa-solid fa-caret-right"></i> Precio
                                    {{ $precio->contador }} de venta :</strong> {{ $precio->precio }} Lps</small></p>
                        @endforeach
     --}}



                    </div>
                </div>


            </div>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-rectangle-list"></i> Lista de compra </h3>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_productos_compra" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>N° Compra</th>
                                        <th>ID Producto</th>
                                        <th>Nombre</th>
                                        <th>Precio Unidad</th>
                                        <th>Cantidad</th>
                                        <th>Sub Total</th>
                                        <th>ISV</th>
                                        <th>Total</th>
                                        <th>Fecha de vencimiento</th>
                                        <th>Estado en bodega</th>


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

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h3><i class="fa-solid fa-cash-register"></i> Pagos Realizados</h3>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            <table id="tbl_pagos_lista" class="table table-striped table-bordered table-hover">
                                <thead class="">
                                    <tr>
                                        <th>ID</th>
                                        <th>N° Factura</th>
                                        <th>N° Compra</th>
                                        <th>Monto Lps.</th>
                                        <th>Fecha</th>
                                        <th>Registrado por:</th>
                                        <th>Registrado en sistema:</th>

                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                        </div>

                        <div class="d-flex d-flex justify-content-between mt-4" >
                            <div >
                                <h3>Pendiente de Pago: <span class="text-danger">{{number_format($deudaRestante,2)}} Lps.</span> </h3>

                            </div>
                            <div >
                                <h3>Total Pagado: <span class="text-success">{{number_format(round($sumaPagos->monto,2),2)}} Lps.</span></h3>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')

    <script>

        //var listaProductos = {!! json_encode($listaCompra) !!}; metodo antiguo
        var listaProductos = @json($listaCompra);
       var listaPagos = @json($listaPagos);





        $(document).ready(function() {

            $('#tbl_productos_compra').DataTable( {
                "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                        },
                        pageLength: 10,
                        responsive: true,
                "data": listaProductos,
                "columns": [
                    { "data": "numero_factura" },
                    { "data": "producto_id" },
                    { "data": "nombre" },
                    { "data": "precio_unidad" },
                    { "data": "cantidad_ingresada" },
                    { "data": "sub_total_producto" },
                    { "data": "isv" },
                    { "data": "precio_total" },
                    { "data": "fecha_expiracion" },
                    { "data": "estado_recibido" },

                ]
            } );

            $('#tbl_pagos_lista').DataTable( {
                "language": {

                            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json",
                            "zeroRecords": "Ningun pago registrado para esta compra!",

                        },
                pageLength: 10,

                responsive: false,
                "data": listaPagos,
                "columns": [
                    { "data": "id" },
                    { "data": "numero_factura" },
                    { "data": "numero_orden" },
                    { "data": "monto" },
                    { "data": "fecha" },
                    { "data": "name" },
                    { "data": "fecha_sistema" }

                ]
            } );



        } );

    </script>

    @endpush
</div>
