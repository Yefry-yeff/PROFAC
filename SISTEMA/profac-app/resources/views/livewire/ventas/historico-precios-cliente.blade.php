<div>
<style>
/* ── HISTORICO PRECIOS ── */
.hp-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 60%, #2563eb 100%);
    border-radius: 16px; padding: 24px 28px; margin-bottom: 0;
    color: #fff; display: flex; align-items: center; gap: 18px;
    box-shadow: 0 8px 32px rgba(30,64,175,.28);
    position: relative; overflow: hidden;
}
.hp-hero::before {
    content:''; position:absolute; top:-50px; right:-30px;
    width:200px; height:200px; border-radius:50%;
    background:rgba(255,255,255,.06); pointer-events:none;
}
.hp-hero-icon {
    width: 52px; height: 52px; border-radius: 13px;
    background: rgba(255,255,255,.15); border: 2px solid rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.hp-hero-body h3 { margin:0; font-size:17px; font-weight:800; }
.hp-hero-body p  { margin:3px 0 0; font-size:12px; color:rgba(255,255,255,.7); }
.hp-role-badge {
    margin-left: auto; flex-shrink: 0;
    display: flex; align-items: center; gap: 7px;
    padding: 6px 14px; border-radius: 30px;
    font-size: 11px; font-weight: 800; letter-spacing: .3px;
}
.hp-role-badge.admin  { background:rgba(251,191,36,.18); color:#fde68a; border:1px solid rgba(251,191,36,.3); }
.hp-role-badge.vendor { background:rgba(52,211,153,.18); color:#a7f3d0; border:1px solid rgba(52,211,153,.3); }

/* ── FILTER CARD ── */
.hp-filter-card {
    background: #fff;
    border-radius: 0 0 14px 14px;
    border: 1px solid #e2e8f0; border-top: none;
    box-shadow: 0 6px 20px rgba(0,0,0,.07);
    padding: 28px 28px 24px;
}
.hp-filter-label {
    font-size: 11px; font-weight: 800; color: #64748b;
    text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 6px; display: flex; align-items: center; gap: 6px;
}
.hp-filter-label i { font-size: 12px; color: #94a3b8; }
.hp-select2-wrap .select2-container--default .select2-selection--single {
    height: 42px !important; border: 1.5px solid #d1d5db !important;
    border-radius: 10px !important; padding: 4px 10px !important;
    box-shadow: none !important; transition: border-color .2s, box-shadow .2s !important;
}
.hp-select2-wrap .select2-container--default .select2-selection--single:hover,
.hp-select2-wrap .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1) !important;
}
.hp-select2-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important; color: #374151 !important; font-size: 13px !important;
}
.hp-select2-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important; right: 8px !important;
}
.hp-btn-buscar {
    background: linear-gradient(135deg, #1e40af, #2563eb) !important;
    color: #fff !important; border: none !important;
    border-radius: 10px !important; padding: 10px 26px !important;
    font-size: 13px !important; font-weight: 700 !important;
    cursor: pointer !important; letter-spacing: .2px !important;
    display: inline-flex !important; align-items: center !important; gap: 8px !important;
    transition: opacity .15s, transform .12s !important;
    box-shadow: 0 4px 14px rgba(37,99,235,.35) !important;
}
.hp-btn-buscar:hover { opacity: .88 !important; transform: translateY(-1px) !important; }
.hp-btn-buscar:active { transform: translateY(0) !important; }

/* info hint */
.hp-info-hint {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 8px; margin-top: 18px;
    font-size: 11.5px;
}
.hp-info-hint.vendor { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.hp-info-hint.admin  { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }

tfoot input { width:100%; padding:3px; box-sizing:border-box; }
</style>

{{-- PAGE HEADER --}}
<div class="row wrapper border-bottom white-bg page-heading d-flex align-items-center">
    <div class="col-lg-12">
        <h2><i class="fa fa-history text-primary"></i> Histórico de Precios</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            <li class="breadcrumb-item">Ventas</li>
            <li class="breadcrumb-item active"><strong>Histórico de Precios</strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight pb-0">
    <div class="row">
        <div class="col-lg-12">

            {{-- Hero --}}
            <div class="hp-hero">
                <div class="hp-hero-icon"><i class="fa fa-history"></i></div>
                <div class="hp-hero-body">
                    <h3>Histórico de Precios por Cliente</h3>
                    <p>Consulta el historial de precios facturados por cliente y producto</p>
                </div>
                @if($rolId == 2)
                    <div class="hp-role-badge vendor">
                        <i class="fa fa-user"></i> Vista Vendedor
                    </div>
                @else
                    <div class="hp-role-badge admin">
                        <i class="fa fa-shield"></i> Vista Administrador
                    </div>
                @endif
            </div>

            {{-- Filter card --}}
            <div class="hp-filter-card">
                <div class="row">
                    <div class="col-md-5 hp-select2-wrap">
                        <div class="hp-filter-label"><i class="fa fa-building-o"></i> Cliente</div>
                        <select id="cliente" name="cliente" class="form-control" style="width:100%;">
                            <option value="" disabled selected>Buscar cliente...</option>
                        </select>
                    </div>
                    <div class="col-md-5 hp-select2-wrap">
                        <div class="hp-filter-label"><i class="fa fa-cube"></i> Producto</div>
                        <select id="producto" name="producto" class="form-control" style="width:100%;">
                            <option value="" disabled selected>Buscar producto...</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="hp-btn-buscar w-100" onclick="listarHistorico()">
                            <i class="fa fa-search"></i> Consultar
                        </button>
                    </div>
                </div>

                @if($rolId == 2)
                <div class="hp-info-hint vendor">
                    <i class="fa fa-info-circle"></i>
                    <span>Mostrando solo los clientes asignados a <strong>{{ $userName }}</strong></span>
                </div>
                @else
                <div class="hp-info-hint admin">
                    <i class="fa fa-info-circle"></i>
                    <span>Acceso completo — se listan <strong>todos los clientes activos</strong></span>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Results table --}}
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="tbl_historico_precios" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Numero Factura</th><th>CAI</th><th>Fecha Emision</th>
                                    <th>Cliente</th><th>Producto</th><th>Descripcion</th>
                                    <th>Unidad Medida</th><th>Cantidad</th><th>Precio Unidad</th>
                                    <th>Sub-Total</th><th>ISV</th><th>Total</th><th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tfoot>
                                    <tr>
                                        <th>Numero Factura</th><th>CAI</th><th>Fecha Emision</th>
                                        <th>Cliente</th><th>Producto</th><th>Descripcion</th>
                                        <th>Unidad Medida</th><th>Cantidad</th><th>Precio Unidad</th>
                                        <th>Sub-Total</th><th>ISV</th><th>Total</th><th>Opciones</th>
                                    </tr>
                                </tfoot>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@push('scripts')

<script>



         $('#cliente').select2({
             ajax: {
                 url: '/ventas/historico_precios/clientes',
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

         $('#producto').select2({
            ajax: {
                url: '/ventas/historico_precios/productos',
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

        $(document).ready(function() {

     })


    // function listarProductos() {
    //     $('#producto').select2({
    //         ajax: {
    //             url: '/ventas/historico_precios_cliente/productos',
    //             data: function(params) {
    //                 var query = {
    //                     search: params.term,
    //                     type: 'public',
    //                     page: params.page || 1
    //                 }


    //                 return query;
    //             }
    //         }
    //     });
    // }

    function listarHistorico() {

        let idCliente = document.getElementById('cliente').value;
        let idProducto = document.getElementById('producto').value;

        let data = {'idCliente':idCliente, 'idProducto':idProducto}
        $("#tbl_historico_precios").dataTable().fnDestroy();

        $('#tbl_historico_precios').DataTable({
                    "order": [0, 'desc'],
                    "language": {
                        "url": "/js/plugins/dataTables/i18n/Spanish.json"
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
                    'ajax': {
                        'data': data,
                        'url': '/ventas/historico/precios',
                        'type': 'POST',
                        'headers': {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    },
                    "columns": [
                        {
                            data: 'numero_factura'
                        },
                        {
                            data: 'cai'
                        },
                        {
                            data: 'fecha_emision'
                        },
                        {
                            data: 'nombre_cliente'
                        },
                        {
                            data: 'producto'
                        },
                        {
                            data: 'descripcion'
                        },
                        {
                            data: 'unidad_medida'
                        },
                        {
                            data: 'cantidad'
                        },
                        {
                            data: 'precio_unidad'
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
                            data: 'opciones'
                        }

                    ],initComplete: function () {
                        var r = $('#tbl_historico_precios tfoot tr');
                        r.find('th').each(function(){
                          $(this).css('padding', 8);
                        });
                        $('#tbl_historico_precios thead').append(r);
                        $('#search_0').css('text-align', 'center');
                        this.api()
                            .columns()
                            .every(function () {
                                let column = this;
                                let title = column.footer().textContent;

                                // Create input element
                                let input = document.createElement('input');
                                input.placeholder = title;
                                column.footer().replaceChildren(input);

                                // Event listener for user input
                                input.addEventListener('keyup', () => {
                                    if (column.search() !== this.value) {
                                        column.search(input.value).draw();
                                    }
                                });
                            });
                    }


                });
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
</script>

@endpush
v
