<div>
    @push('styles')
    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }

        .img-size { width: 100%; height: 16rem; margin: 0 auto; object-fit: contain; }

        /* ── of-card system ─────────────────────────────────────────── */
        .ofr-main-ibox { border: none !important; box-shadow: none !important; background: transparent !important; }
        .ofr-main-ibox > .ibox-title { display: none !important; }
        .ofr-main-ibox > .ibox-content { padding: 0 !important; background: transparent !important; border: none !important; }

        .of-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8eaef;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .of-card-title {
            font-size: 13px; font-weight: 700; color: #6c757d;
            text-transform: uppercase; letter-spacing: .6px;
            margin-bottom: 16px;
            display: flex; align-items: center; gap: 7px;
            cursor: pointer; user-select: none;
        }
        .of-card-title i { font-size: 14px; }
        .of-card-title .of-chevron {
            margin-left: auto; font-size: 12px; color: #9ca3af;
            transition: transform .25s ease; flex-shrink: 0;
        }

        /* ── Field labels ─────────────────────────────────────────── */
        .ofr-label {
            font-size: 11px; font-weight: 700; color: #546e7a;
            text-transform: uppercase; letter-spacing: .5px;
            margin-bottom: 4px; display: block;
        }
        .ofr-label .req { color: #e53935; margin-left: 2px; }

        /* ── of-totals-card ─────────────────────────────────────────── */
        .of-totals-card {
            background: #fff; border: 1.5px solid #e8eaef;
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.07);
            margin-bottom: 18px;
        }
        .of-totals-header {
            background: linear-gradient(135deg,#2d3748,#4a5568);
            padding: 12px 20px; color: #fff; font-size: 13px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }
        .of-totals-body { padding: 16px 20px; }
        .of-total-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 7px 0; border-bottom: 1px solid #f0f2f5; font-size: 13px;
        }
        .of-total-row:last-child { border-bottom: none; }
        .of-total-row .lbl { color: #6b7280; font-weight: 500; }
        .of-total-row .val {
            font-weight: 700; color: #1a202c;
            background: #f7f8fa; border: 1px solid #e8eaef;
            border-radius: 7px; padding: 4px 12px; font-size: 13px;
            min-width: 140px; text-align: right; font-family: monospace;
            outline: none;
        }
        .of-total-grand .lbl { font-size: 15px; font-weight: 800; color: #1a202c; }
        .of-total-grand .val {
            background: linear-gradient(135deg,#1ab394,#0fa37a);
            color: #fff; font-size: 15px; border: none;
            box-shadow: 0 3px 10px rgba(26,179,148,.3);
        }

        /* ── Cart empty state ─────────────────────────────────────── */
        #carritoVacio { text-align: center; padding: 36px 20px; color: #aab; }
        #carritoVacio i { font-size: 48px; opacity: .25; display: block; margin-bottom: 10px; color: #aab; }
        #cart-count-badge {
            background: #e65100; color: #fff; border-radius: 20px;
            font-size: 11px; font-weight: 700; padding: 2px 10px;
        }

        /* ── Header número de venta ─────────────────────────────────── */
        .vale-header-card {
            background: linear-gradient(135deg,#e65100 0%,#f9a826 100%);
            border-radius: 14px; padding: 16px 24px; margin-bottom: 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .vale-header-card h3 { color: #fff; margin: 0; font-size: 17px; font-weight: 700; }
    </style>
    @endpush

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-8 col-xl-10 col-md-8 col-sm-8">
            <h2>Crear Entrega Pendiente</h2>
            <ol class="breadcrumb"></ol>
        </div>
    </div>

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ofr-main-ibox">
                    <div class="ibox-title"><h3>Vale</h3></div>
                    <div class="ibox-content">

                        {{-- Encabezado degradado con número de venta --}}
                        <div class="vale-header-card">
                            <h3><i class="mr-2 fa fa-file-text-o"></i> Crear Vale / Entrega Pendiente</h3>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="color:rgba(255,255,255,.8); font-size:12px; font-weight:600;">N° Venta:</span>
                                <input type="text" id="numero_venta" name="numero_venta"
                                    style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3);
                                           color:#fff; border-radius:8px; padding:4px 10px; max-width:150px;
                                           font-size:14px; font-weight:700; text-align:center;"
                                    value="{{ $detalleVenta->numero_factura }}" readonly>
                            </div>
                        </div>

                        <form onkeydown="return event.key != 'Enter';" autocomplete="off" id="crear_venta"
                              name="crear_venta" data-parsley-validate>

                            <input type="hidden" id="idFactura" name="idFactura" value="{{ $idFactura }}">

                            {{-- SECCION 1: Datos de la Venta --}}
                            <div class="of-card">
                                <div class="of-card-title" onclick="toggleOfCard('body_datos', this)">
                                    <i class="fa fa-info-circle text-primary"></i> Datos de la Venta
                                    <i class="fa fa-chevron-down of-chevron"></i>
                                </div>
                                <div id="body_datos">
                                    <div class="row" style="row-gap:10px;">
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label">Vendedor <span class="req">*</span></label>
                                            <select name="vendedor" id="vendedor" class="form-control form-control-sm" required readonly>
                                                <option value="{{ $detalleVenta->vendedor }}">{{ $detalleVenta->name }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label">Nombre del Cliente <span class="req">*</span></label>
                                            <input class="form-control form-control-sm" required type="text"
                                                value="{{ $detalleVenta->nombre_cliente }}"
                                                id="nombre_cliente_ventas" name="nombre_cliente_ventas"
                                                data-parsley-required readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label">RTN</label>
                                            <input class="form-control form-control-sm" type="text"
                                                value="{{ $detalleVenta->rtn }}" id="rtn_ventas" name="rtn_ventas" readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label">Tipo de Pago <span class="req">*</span></label>
                                            <select class="form-control form-control-sm" name="tipoPagoVenta" id="tipoPagoVenta"
                                                data-parsley-required readonly>
                                                <option value="{{ $detalleVenta->tipo_pago_id }}">{{ $detalleVenta->tipo_pago }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label">Fecha Emisión <span class="req">*</span></label>
                                            <input class="form-control form-control-sm" type="date" id="fecha_emision"
                                                name="fecha_emision" value="{{ $detalleVenta->fecha_emision }}"
                                                data-parsley-required readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="ofr-label" style="color:#f57f17;">Fecha Vencimiento</label>
                                            <input class="form-control form-control-sm" type="date" id="fecha_vencimiento"
                                                name="fecha_vencimiento" value="{{ $detalleVenta->fecha_vencimiento }}"
                                                data-parsley-required readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="ofr-label">Comentario <span class="req">*</span></label>
                                            <textarea class="form-control form-control-sm" id="comentario"
                                                name="comentario" rows="3" data-parsley-required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- SECCION 2: Agregar Producto --}}
                            <div class="of-card">
                                <div class="of-card-title" onclick="toggleOfCard('body_producto', this)">
                                    <i class="fa fa-plus-circle text-success"></i> Agregar producto al carrito
                                    <i class="fa fa-chevron-down of-chevron"></i>
                                </div>
                                <div id="body_producto">
                                    <div class="row" style="row-gap:10px; margin-bottom:10px;">
                                        <div class="col-12 col-md-6">
                                            <label class="ofr-label">Seleccionar Producto <span class="req">*</span></label>
                                            <select id="seleccionarProducto" name="seleccionarProducto"
                                                class="form-control form-control-sm" onchange="obtenerImagenes()">
                                                <option value="" selected disabled>--Seleccione un producto--</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="botonAdd" class="mb-3 d-none">
                                        <button type="button" onclick="agregarProductoCarrito()"
                                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                                   border-radius:8px; padding:6px 16px; font-size:12px; font-weight:700;
                                                   box-shadow:0 2px 8px rgba(230,81,0,.3); cursor:pointer;">
                                            <i class="mr-1 fa fa-shopping-cart"></i> Añadir al Carrito
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-5">
                                            <div id="carouselProducto" class="carousel slide mt-2" data-ride="carousel">
                                                <div id="bloqueImagenes" class="carousel-inner"
                                                     style="border-radius:10px; overflow:hidden; max-height:220px;"></div>
                                                <a class="carousel-control-prev" href="#carouselProducto" role="button" data-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Previous</span>
                                                </a>
                                                <a class="carousel-control-next" href="#carouselProducto" role="button" data-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="sr-only">Next</span>
                                                </a>
                                            </div>
                                            <div class="mt-2 text-center">
                                                <a id="detalleProducto" href="" target="_blank"
                                                    class="font-bold d-none text-success" style="font-size:13px;">
                                                    <i class="fa fa-circle-info"></i> Ver Detalles del Producto
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARRITO --}}
                            <div class="of-card" style="padding:0; overflow:hidden;">
                                <div style="padding:16px 24px 12px; border-bottom:1px solid #f0f2f5;
                                            display:flex; align-items:center; gap:8px; cursor:pointer;"
                                     onclick="toggleOfCard('body_carrito', this)">
                                    <span class="of-card-title" style="cursor:pointer; margin-bottom:0 !important;">
                                        <i class="fa fa-shopping-cart text-warning"></i> Carrito de productos
                                    </span>
                                    <span id="cart-count-badge">0 producto(s)</span>
                                    <i class="fa fa-chevron-down of-chevron" style="margin-left:8px;"></i>
                                </div>
                                <div id="body_carrito">
                                    <div id="divProductos" style="padding:0 0 4px;">
                                        <div id="carritoVacio" class="py-3 text-center">
                                            <i class="mb-2 fa fa-inbox fa-3x d-block"></i>
                                            <p style="font-size:13px; margin:0;">No hay productos en el carrito.<br>
                                                <small>Seleccione un producto arriba para agregar.</small>
                                            </p>
                                        </div>
                                        <div id="carritoTablaWrapper" class="d-none table-responsive" style="max-height:400px; overflow-y:auto;">
                                            <table class="table mb-0 table-sm table-bordered" style="font-size:12px; min-width:820px;">
                                                <thead style="background:linear-gradient(135deg,#e8f5e9,#e0f7fa); position:sticky; top:0; z-index:1;">
                                                    <tr style="color:#00695c; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:.3px;">
                                                        <th style="width:36px;"></th>
                                                        <th style="min-width:150px;">Producto</th>
                                                        <th style="min-width:90px;">Bodega</th>
                                                        <th style="min-width:80px;">Precio</th>
                                                        <th style="min-width:70px;">Cantidad</th>
                                                        <th style="min-width:80px;">Unidad</th>
                                                        <th style="min-width:90px;">Subtotal</th>
                                                        <th style="min-width:75px;">ISV</th>
                                                        <th style="min-width:90px; background:linear-gradient(135deg,#e65100,#f9a826); color:#fff;">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="carritoTbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Totales + Botón --}}
                            <div class="row">
                                <div class="col-12 col-lg-6 offset-lg-6">
                                    <div class="of-totals-card">
                                        <div class="of-totals-header">
                                            <i class="fa fa-calculator"></i> Resumen de totales
                                        </div>
                                        <div class="of-totals-body">
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-list text-muted"></i> Sub Total</span>
                                                <input type="text" id="subTotalGeneralMostrar" name="subTotalGeneralMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input id="subTotalGeneral" name="subTotalGeneral" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-file-text-o text-muted"></i> Sub Total Grabado</span>
                                                <input type="text" id="subTotalGeneralGrabadoMostrar" name="subTotalGeneralGrabadoMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input id="subTotalGeneralGrabado" name="subTotalGeneralGrabado" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-minus-circle text-muted"></i> Sub Total Exento</span>
                                                <input type="text" id="subTotalGeneralExcentoMostrar" name="subTotalGeneralExcentoMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input id="subTotalGeneralExcento" name="subTotalGeneralExcento" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row">
                                                <span class="lbl"><i class="mr-1 fa fa-percent text-muted"></i> ISV</span>
                                                <input type="text" id="isvGeneralMostrar" name="isvGeneralMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input id="isvGeneral" name="isvGeneral" type="hidden" value="" required>
                                            </div>
                                            <div class="of-total-row of-total-grand" style="padding-top:12px; margin-top:4px;">
                                                <span class="lbl">TOTAL</span>
                                                <input type="text" id="totalGeneralMostrar" name="totalGeneralMostrar" class="val" placeholder="L. 0.00" data-parsley-required autocomplete="off" readonly>
                                                <input id="totalGeneral" name="totalGeneral" type="hidden" value="" required>
                                            </div>
                                        </div>
                                    </div>

                                    <button id="btn_venta_coorporativa"
                                            style="background:linear-gradient(135deg,#e65100,#f9a826); color:#fff; border:none;
                                                   border-radius:12px; padding:14px 32px; font-size:15px; font-weight:800;
                                                   box-shadow:0 4px 18px rgba(230,81,0,.35); width:100%; cursor:pointer;
                                                   display:flex; align-items:center; justify-content:center; gap:10px;">
                                        <i class="fa fa-check-circle"></i> Realizar Vale
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            var idFacturaVale   = {{ $idFactura }};
            var idFactura       = {{ $idFactura }};
            var numeroInputs    = 0;
            var arregloIdInputs = [];
            var public_path     = "{{ asset('catalogo/') }}";
            var diasCredito     = 0;

            $('#seleccionarProducto').select2({
                ajax: {
                    url: '/lista/producto/vale',
                    data: function(params) {
                        return { search: params.term, idFactura: idFactura, type: 'public', page: params.page || 1 };
                    }
                }
            });

            function toggleOfCard(bodyId, titleEl) {
                var body = document.getElementById(bodyId);
                if (!body) return;
                var isOpen = body.style.display !== 'none';
                body.style.display = isOpen ? 'none' : '';
                var chevron = (titleEl.querySelector ? titleEl.querySelector('.of-chevron') : null)
                           || (titleEl.closest ? (titleEl.closest('[onclick]') || {querySelector:function(){}}).querySelector('.of-chevron') : null);
                if (chevron) chevron.style.transform = isOpen ? 'rotate(-90deg)' : '';
            }

            function actualizarContadorCarrito() {
                var badge = document.getElementById('cart-count-badge');
                if (badge) badge.textContent = arregloIdInputs.length + ' producto(s)';
            }

            function obtenerImagenes() {
                var id = document.getElementById('seleccionarProducto').value;
                axios.post('/producto/listar/imagenes', { id: id })
                    .then(function(response) {
                        var imagenes = response.data.imagenes;
                        var html = '';
                        if (imagenes.length === 0) {
                            html = '<div class="carousel-item active"><img class="d-block img-size" src="' + public_path + '/noimage.png" alt="noimage"></div>';
                        } else {
                            imagenes.forEach(function(el) {
                                html += '<div class="carousel-item ' + (el.contador === 1 ? 'active' : '') + '"><img class="d-block img-size" src="' + public_path + '/' + el.url_img + '" alt="imagen ' + el.contador + '"></div>';
                            });
                        }
                        document.getElementById('bloqueImagenes').innerHTML = html;
                        document.getElementById('botonAdd').classList.remove('d-none');
                        var a = document.getElementById('detalleProducto');
                        a.href = '/producto/detalle/' + id;
                        a.classList.remove('d-none');
                    })
                    .catch(function(err) { console.log(err); });
            }

            function agregarProductoCarrito() {
                var ids      = document.getElementById('seleccionarProducto').value;
                var arrIds   = ids.split('-');
                var idProd   = arrIds[0];
                var idSec    = arrIds[1];

                axios.post('/datos/producto/vale', { idProducto: idProd, idFactura: idFactura, idSeccion: idSec })
                    .then(function(response) {
                        var bodega  = response.data.bodega;
                        var flag    = false;
                        arregloIdInputs.forEach(function(idx) {
                            if (document.getElementById('idProducto' + idx).value === idProd &&
                                document.getElementById('idSeccion'  + idx).value === idSec && !flag) flag = true;
                        });
                        if (flag) {
                            Swal.fire({ icon: 'warning', title: 'Advertencia!',
                                html: '<p class="text-left">La sección de bodega y producto ya fue agregada.<br>Aumente la cantidad en la fila existente.</p>' });
                            return;
                        }

                        var producto  = response.data.producto;
                        var cantidad  = response.data.cantidad;
                        var unidades  = response.data.unidades;
                        numeroInputs++;
                        var n = numeroInputs;

                        var htmlUnidades = '';
                        unidades.forEach(function(u) {
                            htmlUnidades += '<option ' + (u.valor_defecto == 1 ? 'selected' : '') + ' value="' + u.id + '" data-id="' + u.idUnidadVenta + '">' + u.nombre + '</option>';
                        });

                        var html = '<tr id="' + n + '">'
                            + '<td style="vertical-align:middle;text-align:center;padding:4px 6px;">'
                            + '<input id="idProducto' + n + '" name="idProducto' + n + '" type="hidden" value="' + producto.id + '">'
                            + '<input id="idSeccion'  + n + '" name="idSeccion'  + n + '" type="hidden" value="' + bodega.idSeccion + '">'
                            + '<input id="idBodega'   + n + '" name="idBodega'   + n + '" type="hidden" value="' + bodega.idBodega + '">'
                            + '<input id="isv'        + n + '" name="isv'        + n + '" type="hidden" value="' + producto.isv + '">'
                            + '<input id="restaInventario' + n + '" name="restaInventario' + n + '" type="hidden" value="">'
                            + '<input id="subTotal'   + n + '" name="subTotal'   + n + '" type="hidden" value="" required>'
                            + '<input id="isvProducto'+ n + '" name="isvProducto'+ n + '" type="hidden" value="" required>'
                            + '<input id="total'      + n + '" name="total'      + n + '" type="hidden" value="" required>'
                            + '<button class="btn btn-danger btn-xs" type="button" onclick="eliminarInput(' + n + ')" style="padding:2px 6px;font-size:11px;border-radius:5px;" title="Eliminar"><i class="fa fa-times"></i></button>'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;">'
                            + '<input type="text" id="nombre' + n + '" name="nombre' + n + '" value="' + producto.nombre + '" readonly data-parsley-required style="border:none;background:transparent;font-size:12px;font-weight:700;color:#1b5e20;width:100%;min-width:130px;">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;white-space:nowrap;">'
                            + '<span style="background:#e3f2fd;color:#1565c0;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:700;"><i class="fa fa-archive" style="font-size:10px;"></i> ' + bodega.bodega + '</span>'
                            + '<input id="bodega' + n + '" name="bodega' + n + '" type="hidden" value="' + bodega.bodega + '">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;">'
                            + '<input type="number" id="precio' + n + '" name="precio' + n + '" value="' + producto.precio_unidad + '" class="form-control form-control-sm" readonly data-parsley-required step="any" autocomplete="off" style="min-width:80px;font-size:11px;" onchange="calcularTotales(precio' + n + ',cantidad' + n + ',' + producto.isv + ',unidad' + n + ',' + n + ',restaInventario' + n + ')">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;">'
                            + '<input type="number" id="cantidad' + n + '" name="cantidad' + n + '" class="form-control form-control-sm" min="1" max="' + cantidad.cantidad + '" data-parsley-required autocomplete="off" style="min-width:60px;font-size:11px;" onchange="calcularTotales(precio' + n + ',cantidad' + n + ',' + producto.isv + ',unidad' + n + ',' + n + ',restaInventario' + n + ')">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;">'
                            + '<select class="form-control form-control-sm" name="unidad' + n + '" id="unidad' + n + '" data-parsley-required style="font-size:11px;min-width:80px;" onchange="calcularTotales(precio' + n + ',cantidad' + n + ',' + producto.isv + ',unidad' + n + ',' + n + ',restaInventario' + n + ')">' + htmlUnidades + '</select>'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;text-align:right;">'
                            + '<input type="text" id="subTotalMostrar' + n + '" name="subTotalMostrar' + n + '" placeholder="0.00" readonly autocomplete="off" style="border:none;background:#f1f8e9;border-radius:5px;font-weight:700;color:#2e7d32;font-size:12px;padding:2px 6px;text-align:right;width:100%;min-width:75px;">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;text-align:right;">'
                            + '<input type="text" id="isvProductoMostrar' + n + '" name="isvProductoMostrar' + n + '" placeholder="0.00" readonly autocomplete="off" style="border:none;background:#fce4ec;border-radius:5px;font-weight:700;color:#b71c1c;font-size:12px;padding:2px 6px;text-align:right;width:100%;min-width:65px;">'
                            + '</td>'
                            + '<td style="vertical-align:middle;padding:4px 6px;text-align:right;">'
                            + '<input type="text" id="totalMostrar' + n + '" name="totalMostrar' + n + '" placeholder="0.00" readonly autocomplete="off" style="border:none;background:linear-gradient(135deg,#e65100,#f9a826);border-radius:5px;font-weight:800;color:#fff;font-size:12px;padding:2px 6px;text-align:right;width:100%;min-width:80px;">'
                            + '</td>'
                            + '</tr>';

                        arregloIdInputs.push(n);
                        document.getElementById('carritoTbody').insertAdjacentHTML('beforeend', html);
                        document.getElementById('carritoVacio').classList.add('d-none');
                        document.getElementById('carritoTablaWrapper').classList.remove('d-none');
                        actualizarContadorCarrito();
                    })
                    .catch(function(err) {
                        console.log(err);
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Ha ocurrido un error al agregar el producto.' });
                    });
            }

            function eliminarInput(id) {
                var el = document.getElementById(id);
                if (el) el.remove();
                var idx = arregloIdInputs.indexOf(id);
                if (idx !== -1) { arregloIdInputs.splice(idx, 1); totalesGenerales(); }
                actualizarContadorCarrito();
                if (arregloIdInputs.length === 0) {
                    document.getElementById('carritoVacio').classList.remove('d-none');
                    document.getElementById('carritoTablaWrapper').classList.add('d-none');
                }
            }

            function calcularTotales(idPrecio, idCantidad, isvProducto, idUnidad, id, idRestaInventario) {
                var valorPrecio   = Number(idPrecio.value).toFixed(2);
                var valorCantidad = idCantidad.value;
                var valorUnidad   = idUnidad.value;
                if (valorPrecio && valorCantidad) {
                    var subTotal = valorPrecio * (valorCantidad * valorUnidad);
                    var isv      = subTotal * (isvProducto / 100);
                    var total    = subTotal + isv;
                    var fmt = function(v) { return new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 }).format(v); };
                    document.getElementById('total'              + id).value = total.toFixed(2);
                    document.getElementById('totalMostrar'       + id).value = fmt(total);
                    document.getElementById('subTotal'           + id).value = subTotal.toFixed(2);
                    document.getElementById('subTotalMostrar'    + id).value = fmt(subTotal);
                    document.getElementById('isvProducto'        + id).value = isv.toFixed(2);
                    document.getElementById('isvProductoMostrar' + id).value = fmt(isv);
                    idRestaInventario.value = valorCantidad * valorUnidad;
                    totalesGenerales();
                }
                idPrecio.value = valorPrecio;
                return 0;
            }

            function totalesGenerales() {
                if (arregloIdInputs.length === 0) return;
                var totalGeneralValor = 0, totalISV = 0, subGrabado = 0, subExcento = 0, subTotal = 0;
                arregloIdInputs.forEach(function(idx) {
                    var sub = Number(document.getElementById('subTotal'    + idx).value);
                    var isv = Number(document.getElementById('isvProducto' + idx).value);
                    var tot = Number(document.getElementById('total'       + idx).value);
                    if (isv === 0) subExcento += sub;
                    else if (sub > 0) subGrabado += sub;
                    subTotal        += sub;
                    totalISV        += isv;
                    totalGeneralValor += tot;
                });
                var fmt = function(v) { return new Intl.NumberFormat('es-HN', { style: 'currency', currency: 'HNL', minimumFractionDigits: 2 }).format(v); };
                document.getElementById('subTotalGeneral').value               = subTotal.toFixed(2);
                document.getElementById('subTotalGeneralMostrar').value        = fmt(subTotal);
                document.getElementById('subTotalGeneralGrabado').value        = subGrabado.toFixed(2);
                document.getElementById('subTotalGeneralGrabadoMostrar').value = fmt(subGrabado);
                document.getElementById('subTotalGeneralExcento').value        = subExcento.toFixed(2);
                document.getElementById('subTotalGeneralExcentoMostrar').value = fmt(subExcento);
                document.getElementById('isvGeneral').value                    = totalISV.toFixed(2);
                document.getElementById('isvGeneralMostrar').value             = fmt(totalISV);
                document.getElementById('totalGeneral').value                  = totalGeneralValor.toFixed(2);
                document.getElementById('totalGeneralMostrar').value           = fmt(totalGeneralValor);
            }

            $(document).on('submit', '#crear_venta', function(e) { e.preventDefault(); guardarVale(); });

            function guardarVale() {
                document.getElementById('btn_venta_coorporativa').disabled = true;
                var data = new FormData($('#crear_venta').get(0));
                arregloIdInputs.forEach(function(n) {
                    var e = document.getElementById('unidad' + n);
                    data.append('arregloIdInputs[]', n);
                    data.append('idUnidadVenta' + n, e.options[e.selectedIndex].getAttribute('data-id'));
                });
                data.append('numeroInputs', numeroInputs);

                axios.post('/guardar/vale', data)
                    .then(function(response) {
                        var res = response.data;
                        if (res.idFactura == 0) {
                            Swal.fire({ icon: res.icon, title: res.title, html: res.text });
                            document.getElementById('btn_venta_coorporativa').disabled = false;
                            return;
                        }
                        Swal.fire({ confirmButtonText: 'Cerrar', icon: res.icon, title: res.title, html: res.text });
                        document.getElementById('carritoTbody').innerHTML = '';
                        document.getElementById('carritoVacio').classList.remove('d-none');
                        document.getElementById('carritoTablaWrapper').classList.add('d-none');
                        document.getElementById('bloqueImagenes').innerHTML = '';
                        document.getElementById('crear_venta').reset();
                        $('#crear_venta').parsley().reset();
                        var dp = document.getElementById('detalleProducto');
                        dp.classList.add('d-none'); dp.href = '';
                        document.getElementById('seleccionarProducto').innerHTML = '<option value="" selected disabled>--Seleccione un producto--</option>';
                        arregloIdInputs = []; numeroInputs = 0;
                        actualizarContadorCarrito();
                        document.getElementById('numero_venta').value = res.numeroVenta;
                        document.getElementById('btn_venta_coorporativa').disabled = false;
                    })
                    .catch(function(err) {
                        document.getElementById('btn_venta_coorporativa').disabled = false;
                        var res = (err.response || {}).data || {};
                        Swal.fire({ icon: res.icon || 'error', title: res.title || 'Error', text: res.text || 'Error al guardar el vale.' });
                    });
            }
        </script>
    @endpush
</div>
