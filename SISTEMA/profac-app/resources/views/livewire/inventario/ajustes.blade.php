<div>
    @push('styles')
        <style>
            :root {
                --aj-orange: #e65100;
                --aj-amber: #f9a826;
                --aj-green: #087f5b;
                --aj-green-soft: #e8f5ef;
                --aj-border: #e4e9ef;
                --aj-text: #37474f;
            }
            .aj-page-title i { color: var(--aj-orange); }
            .aj-card {
                overflow: hidden;
                border: 1px solid var(--aj-border);
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 5px 18px rgba(41, 55, 66, .08);
            }
            .aj-card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 18px;
                background: linear-gradient(135deg, var(--aj-orange), var(--aj-amber));
                color: #fff;
            }
            .aj-card-head h3 { margin: 0; color: #fff; font-size: 16px; font-weight: 800; }
            .aj-card-head small { color: rgba(255,255,255,.86); }
            .aj-card-body { padding: 18px; }
            .aj-product-picker {
                display: flex;
                gap: 10px;
                align-items: end;
                width: 100%;
            }
            .aj-product-picker > div { flex: 1 1 auto; min-width: 0; }
            .aj-product-field {
                min-height: 40px;
                border: 1.5px solid #dbe2e8;
                border-radius: 7px;
                background: #f8fafb;
                color: var(--aj-text);
                font-weight: 700;
            }
            button.aj-btn-search {
                display: inline-flex;
                flex: 0 0 auto;
                align-items: center;
                justify-content: center;
                min-height: 40px;
                border: 1px solid var(--aj-green) !important;
                border-radius: 7px;
                background: var(--aj-green) !important;
                color: #fff !important;
                font-weight: 800;
                padding: 0 18px;
                white-space: nowrap;
            }
            button.aj-btn-search:hover,
            button.aj-btn-search:focus,
            button.aj-btn-search:active {
                border-color: #066b4d !important;
                background: #066b4d !important;
                color: #fff !important;
                box-shadow: 0 0 0 3px rgba(8, 127, 91, .16) !important;
                outline: 0 !important;
            }
            .aj-location-note {
                margin: 14px 0 10px;
                padding: 9px 12px;
                border-left: 4px solid var(--aj-green);
                border-radius: 5px;
                background: var(--aj-green-soft);
                color: #285c4b;
                font-size: 12px;
            }
            .aj-table thead th {
                border-color: #dfe7e3;
                background: #edf7f2;
                color: #17664d;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: .3px;
            }
            .aj-table td { vertical-align: middle; color: #455a64; font-size: 12px; }
            .aj-adjustment-head { background: linear-gradient(135deg, #34495e, #087f5b); }
            .aj-card label { color: #455a64; font-size: 12px; font-weight: 700; }
            .aj-card .form-control { border: 1.5px solid #dbe2e8; border-radius: 7px; }
            .aj-actions { display: flex; justify-content: flex-end; padding-top: 14px; }
            button.aj-save-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--aj-orange) !important;
                border-radius: 7px;
                background: linear-gradient(135deg, var(--aj-orange), var(--aj-amber)) !important;
                color: #fff !important;
                font-weight: 800;
            }
            button.aj-save-btn:hover,
            button.aj-save-btn:focus,
            button.aj-save-btn:active { color: #fff !important; opacity: .92; }
            .aj-modal .modal-content { overflow: hidden; border: 0; border-radius: 10px; box-shadow: 0 18px 45px rgba(31, 44, 54, .25); }
            .aj-modal .modal-header { border: 0; background: linear-gradient(135deg, var(--aj-orange), var(--aj-amber)); color: #fff; }
            .aj-modal .modal-title, .aj-modal .close { color: #fff; }
            .aj-modal .modal-footer .btn-primary { border-color: var(--aj-green); background: var(--aj-green); }
            @media (max-width: 767px) {
                .aj-product-picker { flex-direction: column; align-items: stretch; }
                .aj-btn-search { width: 100%; }
            }
        </style>
    @endpush
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2 class="aj-page-title"><i class="fa fa-sliders mr-2"></i>Ajustes de Producto</h2>

        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="aj-card">
                    <div class="aj-card-head">
                        <div><h3><i class="fa fa-search mr-2"></i>Seleccionar producto</h3><small>Busque el producto y luego seleccione la ubicación que desea ajustar.</small></div>
                    </div>
                    <div class="aj-card-body">
                        <form id="selec_data_form" name="selec_data_form" data-parsley-validate>
                            <label for="productoAjuste_nombre">Producto <span class="text-danger">*</span></label>
                            <div class="aj-product-picker">
                                <div>
                                     <input type="text" id="productoAjuste_nombre" class="form-control aj-product-field"
                                         placeholder="Escriba nombre, código o ID" autocomplete="off" required data-parsley-required
                                         oninput="prepararNuevaBusquedaProductoAjuste(this.value)"
                                         onkeydown="if(event.key === 'Enter'){ event.preventDefault(); buscarPorCodigoAjuste(this.value); return false; }">
                                    <input type="hidden" id="selectProducto" name="selectProducto" value="">
                                </div>
                                <button type="button" class="aj-btn-search" onclick="abrirBuscadorProductoAjuste()">
                                    <i class="fa fa-search mr-1"></i> Buscar producto
                                </button>
                            </div>
                        </form>

                        <div id="ubicaciones_producto_panel" style="display:none;">
                            <div class="aj-location-note"><i class="fa fa-warehouse mr-1"></i><span id="producto_ubicaciones_texto"></span> Seleccione una ubicación para continuar con el ajuste.</div>
                            <h4 class="mb-2" style="color:#087f5b; font-size:14px;"><span id="total"></span></h4>
                            <div class="table-responsive">
                            <table id="tbl_translados" class="table table-bordered table-hover aj-table">
                                <thead class="">
                                    <tr>
                                        <th>Cod Producto</th>
                                        <th>Nombre</th>
                                        <th>Unidad de Medida</th>
                                        <th>Cantidad Disponible</th>
                                        <th>Bodega</th>
                                        <th>Sección</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Acción</th>



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
    </div>

    <!--Tabla para productos a ajustar-->
    <div id="lista_translado" class="">
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="aj-card">
                        <div class="aj-card-head aj-adjustment-head">
                            <div><h3><i class="fa fa-list-check mr-2"></i>Productos a ajustar</h3><small>Complete los datos generales y revise los productos agregados.</small></div>
                        </div>
                        <div class="aj-card-body">
                            <div class="table-responsive">
                                <form onkeydown="return event.key != 'Enter';" id="ajustar_producto_form"
                                    data-parsley-validate>
                                    <div class="form-group">
                                        <label for="tipo_ajuste_id">Motivo de ajuste<span
                                            class="text-danger">*</span></label>
                                        <select class="form-control m-b" name="tipo_ajuste_id" id="tipo_ajuste_id"
                                            required data-parsley-required>
                                            <option value="" selected disabled>---Seleccione una bodega de
                                                destino---
                                            </option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="comentario" class="col-form-label focus-label">Comentario:<span
                                                class="text-danger">*</span></label>
                                        <textarea spellcheck="true" placeholder="Escriba aquí..." required id="comentario" name="comentario" cols="30"
                                            rows="3" class="form-group form-control" data-parsley-required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="solicitado_por">Solicitado por:<span
                                                    class="text-danger">*</span></label>
                                                <select class="form-control " name="solicitado_por" id="solicitado_por"
                                                    required data-parsley-required>
                                                    <option value="" selected disabled>---Seleccionar una
                                                        opción:---
                                                    </option>

                                                </select>
                                            </div>



                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">

                                                <label for="fecha">Fecha de solicitud:<span
                                                    class="text-danger">*</span></label>
                                                <input class="form-control" required data-parsley-required
                                                    type="date" name="fecha" id="fecha">
                                            </div>

                                        </div>
                                    </div>

                                </form>
                                <table id="tbl_translados_productos"
                                    class="table table-striped table-bordered table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>Eliminar</th>
                                            <th>Cod Producto</th>
                                            <th>Nombre Producto</th>
                                            <th>Bodega</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Uniad de medida</th>
                                        </tr>
                                    </thead>

                                    <tbody id="cuerpoListaProducto">


                                    </tbody>

                                </table>

                            </div>
                            <div class="aj-actions">
                                <button id="btn_realizar_ajuste" type="submit" form="ajustar_producto_form"
                                    class="btn aj-save-btn btn-lg mb-4 mt-3"><i class="fa fa-save mr-1"></i>Guardar Ajuste</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-buscador-producto id-modal="buscadorProductoAjustes"
                         callback="alSeleccionarProductoAjuste"
                         url-buscar="/productos/buscar"
                         url-top="/productos/buscar/top-vendidos"
                         url-filtros="/productos/buscar"
                         top-label="Productos con existencia" />



    <!-- Modal para transferir producto a otra bodega-->
    <div class="modal fade aj-modal" id="modal_transladar_producto" role="dialog"
        aria-labelledby="modal_transladar_productoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="modal_transladar_productoLabel"> Datos de Ajuste </h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="datos_ajuste_form" name="datos_ajuste_form" data-parsley-validate>
                                <input type="hidden" name="idRecibido" id="idRecibido">

                                <input type="hidden" name="bodega" id="bodega" value="">
                                <input type="hidden" name="seccion" id="seccion" value="">





                                <div class="form-group">
                                    <label for="aritmetica">Método de ajuste</label>
                                    <select class="form-control m-b" name="aritmetica" id="aritmetica" required
                                        data-parsley-required>
                                        <option value="" selected disabled>---Seleccione un método de ajuste ---
                                        </option>
                                        <option value="1">Sumar Unidades</option>
                                        <option value="2">Restar Unidades</option>
                                    </select>
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">

                                            <label for="idProducto">Código de producto</label>
                                            <input type="number" name="idProducto" id="idProducto"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="nombre_producto">Nombre de producto:</label>
                                            <input class="form-control" required data-parsley-required type="text"
                                                name="nombre_producto" id="nombre_producto" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="cantidad_dispo">Cantidad disponible en sección:</label>
                                            <input type="number" name="cantidad_dispo" id="cantidad_dispo"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="unidad">Unidad de Medida:</label>
                                            <select onchange="calcularTotalUnidades()" class="form-control "
                                                name="unidad" id="unidad" required data-parsley-required>
                                                <option value="" data-id="" selected disabled>---Seleccionar
                                                    una unidad de medida:---</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">

                                            <label for="precio_producto">Precio unitario de producto:</label>
                                            <input type="number" step="any" name="precio_producto"
                                                id="precio_producto" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="cantidad">Cantidad:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" min="1" name="cantidad"
                                                id="cantidad" onchange="calcularTotalUnidades()">
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <div class="form-group">
                                            <label for="total_unidades">Total de unidades para realizar ajuste:</label>
                                            <input class="form-control" autocomplete="off" required
                                                data-parsley-required type="number" name="total_unidades"
                                                id="total_unidades" readonly>
                                        </div>
                                    </div>
                                </div>


                            </form>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" form="datos_ajuste_form" class="btn btn-primary">Agregar producto</button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')

        <script src="{{ asset('js/js_proyecto/inventario/ajustes.js') }}"></script>
        <script>

            var secuenciaBusquedaProductoAjuste = 0;

            function abrirBuscadorProductoAjuste() {
                var texto = document.getElementById('productoAjuste_nombre').value.trim();
                window['abrirBuscador_buscadorProductoAjustes'](texto);
            }

            function prepararNuevaBusquedaProductoAjuste(valorActual) {
                var seleccionado = document.getElementById('selectProducto');
                if (!seleccionado.value) return;
                seleccionado.value = '';
                document.getElementById('ubicaciones_producto_panel').style.display = 'none';
                document.getElementById('productoAjuste_nombre').value = valorActual || '';
                secuenciaBusquedaProductoAjuste++;
            }

            function buscarPorCodigoAjuste(codigo) {
                codigo = String(codigo || '').trim();
                if (!codigo) {
                    abrirBuscadorProductoAjuste();
                    return;
                }

                var secuenciaActual = ++secuenciaBusquedaProductoAjuste;
                axios.get('/productos/buscar', { params: { q: codigo, page: 1 } })
                    .then(function(response) {
                        if (secuenciaActual !== secuenciaBusquedaProductoAjuste) return;
                        var productos = response.data.data || [];
                        var exacto = productos.find(function(producto) {
                            return String(producto.id) === codigo
                                || String(producto.codigo_barra || '').trim() === codigo
                                || String(producto.codigo_estatal || '').trim() === codigo;
                        });

                        if (exacto) {
                            alSeleccionarProductoAjuste(exacto);
                        } else if (productos.length === 1) {
                            alSeleccionarProductoAjuste(productos[0]);
                        } else {
                            window['abrirBuscador_buscadorProductoAjustes'](codigo);
                        }
                    })
                    .catch(function() {
                        if (secuenciaActual !== secuenciaBusquedaProductoAjuste) return;
                        window['abrirBuscador_buscadorProductoAjustes'](codigo);
                    });
            }

            function alSeleccionarProductoAjuste(producto) {
                document.getElementById('selectProducto').value = producto.id;
                document.getElementById('productoAjuste_nombre').value = producto.id + ' - ' + producto.nombre;
                document.getElementById('producto_ubicaciones_texto').textContent = producto.id + ' - ' + producto.nombre + '.';
                document.getElementById('ubicaciones_producto_panel').style.display = 'block';
                obtenerListaBodega();
            }


            function obtenerListaBodega() {
                let idProducto = document.getElementById('selectProducto').value;
                if (!idProducto) return;

                if ($.fn.DataTable.isDataTable('#tbl_translados')) {
                    $('#tbl_translados').DataTable().destroy();
                }

                $('#tbl_translados').DataTable({
                    "language": {
                        "url": "/js/plugins/dataTables/i18n/Spanish.json"
                    },
                    pageLength: 10,
                    responsive: true,
                    "ajax": {
                        'url': "/ajustes/listado/producto/bodega",
                        'data': {
                            idProducto: idProducto
                        },
                        'type': 'post',
                        'headers': {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }


                    },
                    "columns": [{
                            data: 'idProducto'
                        },
                        {
                            data: 'nombre'
                        },
                        {
                            data: 'simbolo'
                        },
                        {
                            data: 'cantidad_disponible'
                        },
                        {
                            data: 'bodega'
                        },
                        {
                            data: 'descripcion'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'opciones'
                        },



                    ],
                    drawCallback: function() {
                        var sum = $('#tbl_translados').DataTable().column(3).data().sum();
                        let html = 'Cantidad total disponible: ' + Number(sum || 0).toLocaleString('es-HN', { maximumFractionDigits: 2 });
                        $('#total').html(html);


                    }

                });



            }


        </script>
    @endpush

</div>
