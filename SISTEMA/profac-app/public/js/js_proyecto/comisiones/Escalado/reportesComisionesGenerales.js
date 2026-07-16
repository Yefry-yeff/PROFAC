/* === RRHH COMISIONES — REPORTERÍA === */
var dtNomina=null, dtNominaDetalle=null, dtDetalle=null, dtRanking=null, dtRol=null,
    dtFacturas=null, dtProductos=null, dtComparativo=null, dtReversiones=null;
var dtProyecciones=null;
var dtProyeccionesExcluidas=null;
var dtProyBrecha=null;
var dtRevFacturas=null;
var dtRevProductos=null;
var proyeccionesDataActual=[];
var proyeccionesExcluidasActual=[];
var proyBrechaDataActual=[];
var revisionFacturasDataActual=[];
var revisionProductosDataActual=[];
var detalleProductosFacturaMap = {};
var detalleProductosFacturaActual = { facturaComisionId: null, factura: '', cliente: '', productos: [] };

$(document).ready(function(){
    // Fechas por defecto: mes actual
    var hoy=new Date(), ini=new Date(hoy.getFullYear(),hoy.getMonth(),1);
    $('#fpFechaInicio').val(fmtDate(ini));
    $('#fpFechaFin').val(fmtDate(hoy));
    $('#proyFechaInicio').val(fmtDate(ini));
    $('#proyFechaFin').val(fmtDate(hoy));
    $('#revFechaInicio').val(fmtDate(ini));
    $('#revFechaFin').val(fmtDate(hoy));

    // Select2 empleado
    $('#fpEmpleado').select2({
        placeholder:'— Todos los empleados —', allowClear:true,
        ajax:{
            url:'/comision/empleados/lista', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(u){return{id:u.id,text:u.name};})};}
        }
    });

    // Select2 rol
    $('#fpRol').select2({
        placeholder:'— Todos los roles —', allowClear:true,
        ajax:{
            url:'/comision/roles/lista', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(r){return{id:r.id,text:r.name};})};}
        }
    });

    // Select2 usuario activo para proyecciones
    $('#proyUsuario').select2({
        placeholder:'— Seleccione usuario activo —', allowClear:true,
        ajax:{
            url:'/comision/empleados/lista', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(u){return{id:u.id,text:u.name};})};}
        }
    });

    // Select2 rol comisionable para proyecciones
    $('#proyRol').select2({
        placeholder:'— Todos los roles comisionables —', allowClear:true,
        ajax:{
            url:'/comision/roles/comisionables', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(r){return{id:r.id,text:r.name};})};}
        }
    });

    // Select2 usuario activo para revisión de facturas
    $('#revUsuario').select2({
        placeholder:'— Todos los usuarios activos —', allowClear:true,
        ajax:{
            url:'/comision/empleados/lista', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(u){return{id:u.id,text:u.name};})};}
        }
    });

    // Select2 rol comisionable para revisión de facturas
    $('#revRol').select2({
        placeholder:'— Todos los roles comisionables —', allowClear:true,
        ajax:{
            url:'/comision/roles/comisionables', dataType:'json', delay:250,
            data:function(p){return{q:p.term};},
            processResults:function(d){return{results:d.map(function(r){return{id:r.id,text:r.name};})};}
        }
    });

    $('#btnGenerar').on('click', generarReporte);
    $('#btnLimpiar').on('click', limpiarFiltros);
    $('#btnCcGenerar').on('click', cargarConciliadasDesdeFiltro);
    $('#btnCcLimpiar').on('click', limpiarFiltrosConciliadas);
    $('#btnCcExcelMasivo').on('click', descargarConciliadasMasivo);
    $('#btnProyGenerar').on('click', generarProyecciones);
    $('#btnProyLimpiar').on('click', limpiarFiltrosProyecciones);
    $('#btnRevGenerar').on('click', generarRevisionFacturas);
    $('#btnRevLimpiar').on('click', limpiarRevisionFacturas);

    $(document).on('click', '.btn-proy-reprocesar-factura', function(){
        var facturaId = parseInt($(this).data('facturaId') || 0, 10);
        if(facturaId <= 0) return;

        var row = proyBrechaDataActual.find(function(item){
            return parseInt(item.factura_id || 0, 10) === facturaId;
        }) || null;

        reprocesarFacturaBrecha(row);
    });

    cargarPeriodosConciliados();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        var tabId = $(e.target).attr('href');
        if(tabId === '#tab-nomina' && $('#badgePeriodo').is(':visible')){
            cargarTab(tabId, getFiltrosNomina());
        }
        if(tabId === '#tab-conciliadas' && $('#ccPeriodo').val()){
            cargarConciliadas($('#ccPeriodo').val());
        } else if(tabId === '#tab-conciliadas'){
            $('#badgePeriodo').hide();
        }

        if(tabId === '#tab-proyecciones'){
            $('#badgePeriodo').hide();
        }

        if(tabId === '#tab-revision-facturas'){
            $('#badgePeriodo').hide();
        }
    });
});

function fmtDate(d){
    var m=d.getMonth()+1, dd=d.getDate();
    return d.getFullYear()+'-'+(m<10?'0'+m:m)+'-'+(dd<10?'0'+dd:dd);
}
function fmtMoney(v){
    return 'L. '+parseFloat(v||0).toLocaleString('es-HN',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function esc(s){
    if(!s)return'';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function normalizarTokenPoliticaAnterior(v){
    return String(v || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '');
}

function esFilaVendedorPoliticaAnterior(item){
    var capacidadNorm = normalizarTokenPoliticaAnterior(item && item.capacidad ? item.capacidad : '');
    var rolNombreNorm = normalizarTokenPoliticaAnterior(item && item.rol_nombre ? item.rol_nombre : '');
    var rolId = parseInt(item && item.rol_id ? item.rol_id : 0, 10);

    if (capacidadNorm === 'asesor' || capacidadNorm === 'vendedor' || capacidadNorm === 'ventas') {
        return true;
    }

    if (rolId === 2) {
        return true;
    }

    return rolNombreNorm === 'asesor'
        || rolNombreNorm === 'asesorcomercial'
        || rolNombreNorm === 'vendedor'
        || rolNombreNorm === 'ventas';
}

function filtrarExcluidasPoliticaAnterior(rows, usuarioFiltro){
    var filtroUsuario = parseInt(usuarioFiltro || 0, 10);

    return (Array.isArray(rows) ? rows : []).filter(function(item){
        if(!esFilaVendedorPoliticaAnterior(item)) return false;

        if(filtroUsuario > 0){
            var usuarioFila = parseInt(item && item.usuario_id ? item.usuario_id : 0, 10);
            return usuarioFila === filtroUsuario;
        }

        return true;
    });
}

function deduplicarFilasPoliticaAnteriorPorFactura(rows){
    var mapa = {};

    (Array.isArray(rows) ? rows : []).forEach(function(item){
        var facturaId = parseInt(item && item.factura_id ? item.factura_id : 0, 10);
        if(facturaId <= 0 || mapa[facturaId]) return;
        mapa[facturaId] = item;
    });

    return Object.keys(mapa).map(function(k){ return mapa[k]; });
}

function badgeRol(r){
    if(!r)return'—';
    var rl=r.toLowerCase();
    if(rl.includes('televendedor'))return'<span class="badge-rol badge-rol-tv">'+esc(r)+'</span>';
    if(rl.includes('asesor')||rl.includes('vendedor'))return'<span class="badge-rol badge-rol-ac">'+esc(r)+'</span>';
    if(rl.includes('admin')||rl.includes('gerente'))return'<span class="badge-rol badge-rol-adm">'+esc(r)+'</span>';
    return'<span class="badge-rol badge-rol-def">'+esc(r)+'</span>';
}

function formatResumenProductosHtml(row){
    var productos = Array.isArray(row && row.detalle_productos) ? row.detalle_productos : [];
    if(!productos.length){
        return '<span style="color:#94a3b8;">Sin detalle por producto</span>';
    }

    var total = productos.length;
    var btn = '<button type="button" class="btn btn-xs" style="border:1px solid #cbd5e1;background:#f8fafc;color:#334155;font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px;" onclick="abrirDetalleProductosFactura('+parseInt(row.id, 10)+')">Ver detalle</button>';

    return ''
        + '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
        + '<span style="display:inline-block;background:#e2e8f0;color:#334155;border-radius:999px;padding:2px 8px;font-size:10px;font-weight:800;">'+total+' producto'+(total===1?'':'s')+'</span>'
        + btn
        + '</div>';
}

function abrirDetalleProductosFactura(facturaComisionId){
    var productos = detalleProductosFacturaMap[String(facturaComisionId)] || detalleProductosFacturaMap[Number(facturaComisionId)] || [];
    if(!productos.length){
        Swal.fire({icon:'info',title:'Sin detalle',text:'No hay detalle por producto para esta factura.'});
        return;
    }

    var row = dtNominaDetalle ? dtNominaDetalle.rows().data().toArray().find(function(item){
        return parseInt(item.id, 10) === parseInt(facturaComisionId, 10);
    }) : null;

    detalleProductosFacturaActual = {
        facturaComisionId: facturaComisionId,
        factura: row && row.factura ? row.factura : '',
        cliente: row && row.cliente ? row.cliente : '',
        productos: productos
    };

    $('#mpfTitulo').text('Detalle de Productos - Factura ' + (row && row.factura ? row.factura : '#'+facturaComisionId));
    $('#mpfSubtitulo').text((row && row.cliente ? row.cliente + ' | ' : '') + 'Escala, porcentaje, cantidad, precio unitario, precio escala, base comisionable y comisión por línea');

    var html = productos.map(function(item){
        return '<tr>'
            + '<td>'+esc(item.producto || '—')+'</td>'
            + '<td>'+esc(item.categoria_cliente_escala || '—')+'</td>'
            + '<td>'+esc(item.categoria_precio_vendida || '—')+'</td>'
            + '<td class="text-right">'+parseFloat(item.porcentaje_comision || 0).toFixed(2)+'%</td>'
            + '<td class="text-right">'+parseFloat(item.cantidad || 0).toLocaleString('es-HN',{minimumFractionDigits:0,maximumFractionDigits:2})+'</td>'
            + '<td class="text-right">'+fmtMoney(item.precio_unitario || 0)+'</td>'
            + '<td class="text-right">'+fmtMoney(item.precio_venta || 0)+'</td>'
                + '<td class="text-right"><strong>'+fmtMoney(item.base_comisionable || 0)+'</strong></td>'
                + '<td>'+esc(item.fuente_base_comisionable || '—')+'</td>'
            + '<td class="text-right"><strong class="monto-com">'+fmtMoney(item.comision || 0)+'</strong></td>'
            + '</tr>';
    }).join('');

    $('#mpfBody').html(html);
    $('#modalProductosFactura').modal('show');
}

function exportarProductosFacturaExcel(){
    if(typeof XLSX === 'undefined'){
        Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
        return;
    }

    var ctx = detalleProductosFacturaActual || {};
    var productos = Array.isArray(ctx.productos) ? ctx.productos : [];
    if(!productos.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay productos para exportar en esta factura.'});
        return;
    }

    var fechaDescarga = new Date().toLocaleString('es-HN');
    var data = [
        ['VALENCIA - DETALLE DE PRODUCTOS COMISIONADOS'],
        ['Factura', ctx.factura || 'No disponible'],
        ['Cliente', ctx.cliente || 'No disponible'],
        ['Fecha de descarga', fechaDescarga],
        [],
        ['Producto','Categoría Cliente Escala','Categoría Precio Vendida','%','Cantidad','Precio Unitario','Precio Escala','Base Comisionable','Fuente Base','Comisión']
    ];

    productos.forEach(function(item){
        data.push([
            item.producto || '',
            item.categoria_cliente_escala || '',
            item.categoria_precio_vendida || '',
            parseFloat(item.porcentaje_comision || 0),
            parseFloat(item.cantidad || 0),
            parseFloat(item.precio_unitario || 0),
            parseFloat(item.precio_venta || 0),
            parseFloat(item.base_comisionable || 0),
            item.fuente_base_comisionable || '',
            parseFloat(item.comision || 0)
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = [
        {s:{r:0,c:0}, e:{r:0,c:8}}
    ];

    var startRow = 7;
    var endRow = startRow + productos.length - 1;
    for (var r = startRow; r <= endRow; r++) {
        var pctRef = 'D' + r;
        if (ws[pctRef] && typeof ws[pctRef].v === 'number') ws[pctRef].z = '0.00%';
        ['F','G','I'].forEach(function(col){
            var cellRef = col + r;
            if (ws[cellRef] && typeof ws[cellRef].v === 'number') ws[cellRef].z = '"L." #,##0.00';
        });
    }

    ws['!autofilter'] = { ref: 'A6:I6' };
    ws['!cols'] = [
        {wch:40},{wch:24},{wch:24},{wch:10},{wch:12},{wch:14},{wch:16},{wch:36},{wch:14}
    ];

    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Productos Factura');

    var facturaBase = String(ctx.factura || 'factura')
        .replace(/[^a-zA-Z0-9-]+/g, '_')
        .replace(/^_+|_+$/g, '');

    XLSX.writeFile(wb, 'detalle_productos_' + facturaBase + '.xlsx');
}

$(document)
    .off('show.bs.modal.mpf', '#modalProductosFactura')
    .on('show.bs.modal.mpf', '#modalProductosFactura', function(){
        var zIndex = 1065;
        $(this).css('z-index', zIndex);
        setTimeout(function(){
            $('.modal-backdrop').not('.modal-stack').last().css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    })
    .off('hidden.bs.modal.mpf', '#modalProductosFactura')
    .on('hidden.bs.modal.mpf', '#modalProductosFactura', function(){
        $('body').addClass('modal-open');
    });

function lang(){
    return{
        processing:'<div style="padding:14px;color:#7c3aed;"><i class="fa fa-spinner fa-spin mr-2"></i>Cargando...</div>',
        search:'',searchPlaceholder:'Buscar...',
        lengthMenu:'Mostrar _MENU_ registros',
        info:'Mostrando _START_ a _END_ de _TOTAL_',
        infoEmpty:'Sin registros',infoFiltered:'(filtrado de _MAX_)',
        zeroRecords:'<div style="padding:30px;text-align:center;color:#94a3b8;"><i class="fa fa-inbox fa-2x mb-2 d-block"></i>Sin datos para el período seleccionado.</div>',
        paginate:{first:'«',last:'»',next:'›',previous:'‹'}
    };
}

function normalizeDateInput(v){
    var s = String(v || '').trim();
    if(!s) return '';

    // Ya viene en formato ISO (YYYY-MM-DD)
    if(/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;

    // Formato UI común en el módulo: DD/MM/YYYY
    var m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if(m){
        return m[3] + '-' + m[2] + '-' + m[1];
    }

    // Último intento: parseo nativo
    var d = new Date(s);
    if(!isNaN(d.getTime())) return fmtDate(d);

    return s;
}

function getFiltrosNomina(){
    var fechaInicio = normalizeDateInput($('#fpFechaInicio').val());
    var fechaFin = normalizeDateInput($('#fpFechaFin').val());

    return{
        fechaInicio:fechaInicio,
        fechaFin:fechaFin,
        empleado_id:$('#fpEmpleado').val()||'',
        rol_id:$('#fpRol').val()||''
    };
}

function getFiltrosProyecciones(){
    var fechaInicio = normalizeDateInput($('#proyFechaInicio').val());
    var fechaFin = normalizeDateInput($('#proyFechaFin').val());

    return {
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        usuario_id: $('#proyUsuario').val() || '',
        rol_id: $('#proyRol').val() || ''
    };
}



function generarProyecciones(){
    var f = getFiltrosProyecciones();

    if (f.fechaInicio) $('#proyFechaInicio').val(f.fechaInicio);
    if (f.fechaFin) $('#proyFechaFin').val(f.fechaFin);

    if(!f.fechaInicio || !f.fechaFin){
        Swal.fire({icon:'warning',title:'Filtros requeridos',text:'Seleccione fecha inicio y fecha fin.'});
        return;
    }
    if(!f.usuario_id){
        Swal.fire({icon:'warning',title:'Usuario requerido',text:'Seleccione un usuario activo para proyectar.'});
        return;
    }
    if(f.fechaInicio > f.fechaFin){
        Swal.fire({icon:'warning',title:'Rango inválido',text:'La fecha inicio no puede ser mayor a la fecha fin.'});
        return;
    }

    $.getJSON('/comision/reporte/proyecciones', f, function(resp){
        renderProyecciones(resp || {});
    }).fail(function(xhr){
        var mensaje = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.text)) || 'No fue posible generar la proyección.';
        Swal.fire({icon:'warning',title:'Proyecciones',text:mensaje});
    });
}

function getFiltrosBrechaApFc(filtrosBase){
    var f = filtrosBase || getFiltrosProyecciones();

    if(!f.fechaInicio || !f.fechaFin){
        return { error: 'Seleccione fecha inicio y fecha fin para consultar la brecha AP vs FC.' };
    }

    if(f.fechaInicio > f.fechaFin){
        return { error: 'La fecha inicio no puede ser mayor a la fecha fin para consultar la brecha.' };
    }

    return {
        fechaInicio: f.fechaInicio,
        fechaFin: f.fechaFin,
        tipo_brecha: $('#proyBrechaTipo').val() || 'all'
    };
}

function cargarBrechaApFc(filtrosBase){
    var filtros = getFiltrosBrechaApFc(filtrosBase);
    if(filtros.error){
        Swal.fire({icon:'warning',title:'Brecha AP vs FC',text:filtros.error});
        return;
    }

    $.getJSON('/comision/reporte/brecha-ap-fc', filtros, function(resp){
        renderBrechaApFc(resp || {});
    }).fail(function(xhr){
        var mensaje = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.text)) || 'No fue posible cargar la brecha AP vs FC.';
        Swal.fire({icon:'warning',title:'Brecha AP vs FC',text:mensaje});
    });
}


function renderProyecciones(resp){
    var data = Array.isArray(resp.data) ? resp.data : [];
    var excluidasRaw = Array.isArray(resp.excluidas) ? resp.excluidas : [];
    var usuarioFiltro = parseInt($('#proyUsuario').val() || 0, 10);
    var excluidas = filtrarExcluidasPoliticaAnterior(excluidasRaw, usuarioFiltro);
    var totales = resp.totales || {};
    proyeccionesDataActual = data;
    proyeccionesExcluidasActual = excluidas;

    $('#proyEmptyState').hide();
    $('#proyInfo').show();
    $('#proyFacturas').text(totales.facturas_proyectadas || 0);
    $('#proyRegistros').text(totales.registros_proyectados || 0);
    $('#proyBaseUnitaria').text(fmtMoney(totales.base_unitaria_total || 0));
    $('#proyBaseComisionable').text(fmtMoney(totales.base_comisionable_total || 0));
    var totalComisionVisible = (typeof totales.comision_recalculada_total !== 'undefined')
        ? totales.comision_recalculada_total
        : (totales.comision_proyectada_total || 0);
    $('#proyComisionTotal').text(fmtMoney(totalComisionVisible || 0));
    $('#proyExcluidas').text(excluidas.length);

    $('#proyTableWrap').show();

    if(dtProyecciones){
        dtProyecciones.destroy();
        $('#dtProyecciones tbody').empty();
    }

    dtProyecciones = $('#dtProyecciones').DataTable({
        data:data,
        processing:false,
        serverSide:false,
        searching:true,
        paging:true,
        pageLength:10,
        lengthMenu:[[10,25,50,100],[10,25,50,100]],
        scrollX:true,
        responsive:false,
        autoWidth:false,
        language:lang(),
        order:[[0,'asc'],[1,'asc']],
        columns:[
            {data:'fecha_pago',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'fecha_creacion_factura',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'factura',render:function(d){return '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d || '—')+'</code>'; }},
            {data:'producto',width:'340px',render:function(d){return '<strong style="display:block;min-width:340px;white-space:normal;">'+esc(d || '—')+'</strong>'; }},
            {data:'cliente',width:'220px',render:function(d){return '<strong style="display:block;min-width:220px;white-space:normal;">'+esc(d || '—')+'</strong>'; }},
            {data:'escala_cliente',render:function(d){return esc(d || '—');}},
            {data:'escala_precio_vendida',width:'110px',className:'text-center',render:function(d){return '<span style="display:block;min-width:110px;text-align:center;">'+esc(d || '—')+'</span>'; }},
            {data:'cantidad',className:'text-right',render:function(d){
                return '<strong>'+parseFloat(d || 0).toLocaleString('es-HN',{minimumFractionDigits:0,maximumFractionDigits:2})+'</strong>';
            }},
            {data:'rol_nombre',className:'text-center',render:function(d, __, row){
                var txt = String(d || row.capacidad || '—');
                var cls = 'badge badge-secondary';
                if(txt === 'ASESOR') cls = 'badge badge-success';
                if(txt === 'TELEASESOR') cls = 'badge badge-primary';
                if(txt === 'GESTOR_ENTREGA') cls = 'badge badge-warning';
                return '<span class="'+cls+'">'+esc(txt)+'</span>';
            }},
            {data:'usuario',render:function(d){return esc(d || '—');}},
            {data:'base_comisionable_unitaria',className:'text-right',render:function(d){return '<strong>'+fmtMoney(d || 0)+'</strong>'; }},
            {data:'base_comisionable',className:'text-right',render:function(d){return '<strong style="color:#0f766e;">'+fmtMoney(d || 0)+'</strong>'; }},
            {data:'porcentaje_promedio',className:'text-right',render:function(d){return parseFloat(d || 0).toFixed(2)+'%';}},
            {data:'comision_proyectada',className:'text-right',render:function(d){return '<strong class="monto-com">'+fmtMoney(d || 0)+'</strong>'; }}
        ]
    });

    if(excluidas.length){
        $('#proyExcluidasWrap').show();
        if(dtProyeccionesExcluidas){
            dtProyeccionesExcluidas.destroy();
            $('#dtProyeccionesExcluidas tbody').empty();
        }

        dtProyeccionesExcluidas = $('#dtProyeccionesExcluidas').DataTable({
            data:excluidas,
            processing:false,
            serverSide:false,
            searching:true,
            paging:true,
            pageLength:10,
            lengthMenu:[[10,25,50,100],[10,25,50,100]],
            scrollX:true,
            responsive:false,
            autoWidth:false,
            language:lang(),
            order:[[0,'asc'],[1,'asc']],
            columns:[
                {data:'fecha_pago',className:'text-nowrap',render:function(d){return esc(d || '—');}},
                {data:'fecha_creacion_factura',className:'text-nowrap',render:function(d){return esc(d || '—');}},
                {data:'factura',render:function(d){return '<code style="background:#fef2f2;padding:2px 6px;border-radius:4px;font-size:11px;color:#991b1b;">'+esc(d || '—')+'</code>'; }},
                {data:'producto',width:'280px',render:function(d){return '<div style="min-width:280px;white-space:normal;">'+esc(d || '—')+'</div>'; }},
                {data:'cliente',render:function(d){return esc(d || '—');}},
                {data:'categoria_precio',render:function(d){return esc(d || '—');}},
                {data:'rol_nombre',render:function(d, __, row){return esc(d || row.capacidad || '—');}},
                {data:'usuario',render:function(d){return esc(d || '—');}},
                {data:'razon_no_comisionable',render:function(d){return '<strong style="color:#991b1b;">'+esc(d || 'No definido')+'</strong>'; }},
                {data:'motivos',render:function(d){
                    var motivos = Array.isArray(d) ? d.join(' | ') : 'Sin detalle';
                    return '<span style="color:#7f1d1d;">'+esc(motivos)+'</span>';
                }}
            ]
        });
    } else {
        $('#proyExcluidasWrap').hide();
        if(dtProyeccionesExcluidas){
            dtProyeccionesExcluidas.destroy();
            dtProyeccionesExcluidas = null;
            $('#dtProyeccionesExcluidas tbody').empty();
        }
    }
}

function renderBrechaApFc(resp){
    var data = Array.isArray(resp.data) ? resp.data : [];
    var totales = resp.totales || {};
    proyBrechaDataActual = data;

    $('#proyBrechaWrap').show();
    $('#proyBrechaInfo').show();
    $('#proyBrechaPagadas').text(totales.facturas_pagadas_ap || 0);
    $('#proyBrechaTotal').text(totales.facturas_con_brecha || 0);
    $('#proyBrechaSinComision').text(totales.sin_comision || 0);
    $('#proyBrechaDesfaseMes').text(totales.desfase_mes || 0);

    if(dtProyBrecha){
        dtProyBrecha.destroy();
        $('#dtProyBrecha tbody').empty();
    }

    dtProyBrecha = $('#dtProyBrecha').DataTable({
        data:data,
        processing:false,
        serverSide:false,
        searching:true,
        paging:true,
        pageLength:10,
        lengthMenu:[[10,25,50,100],[10,25,50,100]],
        scrollX:true,
        responsive:false,
        autoWidth:false,
        language:lang(),
        order:[[0,'asc'],[1,'asc']],
        columns:[
            {data:'fecha_pago_cierre_ap',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'factura',render:function(d){return '<code style="background:#f8fafc;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d || '—')+'</code>'; }},
            {data:'tipo_brecha',className:'text-center',render:function(d){
                var v = String(d || '').toLowerCase();
                if(v === 'sin_comision'){
                    return '<span class="badge badge-danger">SIN COMISION</span>';
                }
                if(v === 'desfase_mes'){
                    return '<span class="badge badge-warning">DESFASE MES</span>';
                }
                return '<span class="badge badge-secondary">'+esc(d || '—')+'</span>';
            }},
            {data:'cliente',render:function(d){return esc(d || '—');}},
            {data:'facturador',render:function(d){return esc(d || '—');}},
            {data:'vendedor',render:function(d){return esc(d || '—');}},
            {data:'gestor_entrega',render:function(d){return esc(d || '—');}},
            {data:'sub_total_factura',className:'text-right',render:function(d){return '<strong>'+fmtMoney(d || 0)+'</strong>'; }},
            {data:'fc_registros',className:'text-center',render:function(d){return parseInt(d || 0, 10);}},
            {data:'fc_meses',render:function(d){
                if(Array.isArray(d) && d.length){
                    return esc(d.join(' | '));
                }
                return '<span style="color:#94a3b8;">Sin registros FC</span>';
            }},
            {data:'fc_total_comision',className:'text-right',render:function(d){return '<strong>'+fmtMoney(d || 0)+'</strong>'; }}
            ,
            {data:null,orderable:false,searchable:false,className:'text-center',render:function(_, __, row){
                var tipo = String(row.tipo_brecha || '').toLowerCase();
                if(tipo !== 'sin_comision'){
                    return '<span style="color:#94a3b8;font-size:11px;">No aplica</span>';
                }

                return '<button type="button" class="btn btn-sm btn-proy-reprocesar-factura" '
                    + 'style="border:1px solid #06b6d4;background:#ecfeff;color:#0e7490;font-weight:700;" '
                    + 'data-factura-id="'+parseInt(row.factura_id || 0, 10)+'">'
                    + '<i class="fa fa-cogs mr-1"></i>Reprocesar</button>';
            }}
        ]
    });
}

function reprocesarFacturaBrecha(row){
    if(!row || parseInt(row.factura_id || 0, 10) <= 0){
        Swal.fire({icon:'warning',title:'Brecha AP vs FC',text:'No se pudo identificar la factura a reprocesar.'});
        return;
    }

    var facturaId = parseInt(row.factura_id || 0, 10);
    var factura = row.factura || ('#' + facturaId);
    var cliente = row.cliente || 'N/A';
    var fechaAp = row.fecha_pago_cierre_ap || 'N/A';

    var mensaje = ''
        + 'Se reprocesará la factura <strong>' + esc(factura) + '</strong>.<br><br>'
        + '<strong>Qué hará el sistema:</strong><br>'
        + '1) Intentará generar registros en facturas_comision con reglas y escalas activas.<br>'
        + '2) Aplicará retenciones de mora configuradas (si corresponde).<br>'
        + '3) Acreditará comisiones en comision_empleado si el período no está conciliado.<br><br>'
        + '<strong>Afectaciones esperadas:</strong><br>'
        + '- Puede aumentar la comisión de uno o más usuarios.<br>'
        + '- La factura dejará de aparecer como sin_comision en esta brecha.<br>'
        + '- Si el período está conciliado, puede generarse facturas_comision pero no acreditar acumulado.<br><br>'
        + '<strong>Factura:</strong> ' + esc(factura) + '<br>'
        + '<strong>Cliente:</strong> ' + esc(cliente) + '<br>'
        + '<strong>Fecha AP:</strong> ' + esc(fechaAp);

    Swal.fire({
        icon:'question',
        title:'Confirmar reproceso de factura',
        html:mensaje,
        showCancelButton:true,
        confirmButtonText:'Sí, reprocesar factura',
        cancelButtonText:'Cancelar'
    }).then(function(result){
        if(!result.isConfirmed) return;

        $.ajax({
            url:'/comision/reporte/brecha-ap-fc/reprocesar',
            type:'POST',
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
            },
            data:{ factura_ids: [facturaId] },
            success:function(resp){
                var t = (resp && resp.totales) ? resp.totales : {};
                var r = (resp && Array.isArray(resp.resultados) && resp.resultados.length) ? resp.resultados[0] : null;
                var detalle = r && r.motivo ? r.motivo : 'Proceso finalizado.';
                Swal.fire({
                    icon:'success',
                    title:'Reproceso finalizado',
                    html:'Factura: <strong>'+esc(factura)+'</strong><br>'
                        + 'Estado creadas: <strong>'+(t.creadas||0)+'</strong><br>'
                        + 'Estado omitidas: <strong>'+(t.omitidas||0)+'</strong><br>'
                        + 'Estado errores: <strong>'+(t.errores||0)+'</strong><br><br>'
                        + '<span style="font-size:12px;color:#334155;">'+esc(detalle)+'</span>'
                });

                cargarBrechaApFc(getFiltrosProyecciones());
            },
            error:function(xhr){
                var mensaje = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.text)) || 'No fue posible reprocesar las facturas seleccionadas.';
                Swal.fire({icon:'error',title:'Brecha AP vs FC',text:mensaje});
            }
        });
    });
}

function limpiarFiltrosProyecciones(){
    var hoy=new Date(),ini=new Date(hoy.getFullYear(),hoy.getMonth(),1);
    $('#proyFechaInicio').val(fmtDate(ini));
    $('#proyFechaFin').val(fmtDate(hoy));
    $('#proyUsuario').val(null).trigger('change');
    $('#proyRol').val(null).trigger('change');

    $('#proyInfo').hide();
    $('#proyFacturas').text('0');
    $('#proyRegistros').text('0');
    $('#proyBaseUnitaria').text(fmtMoney(0));
    $('#proyBaseComisionable').text(fmtMoney(0));
    $('#proyComisionTotal').text(fmtMoney(0));
    $('#proyExcluidas').text('0');

    $('#proyEmptyState').show();
    $('#proyTableWrap').hide();
    $('#proyExcluidasWrap').hide();
    $('#proyBrechaWrap').hide();
    $('#proyBrechaInfo').hide();
    $('#proyBrechaPagadas').text('0');
    $('#proyBrechaTotal').text('0');
    $('#proyBrechaSinComision').text('0');
    $('#proyBrechaDesfaseMes').text('0');
    $('#proyBrechaTipo').val('all');
    proyeccionesDataActual = [];
    proyeccionesExcluidasActual = [];
    proyBrechaDataActual = [];

    if(dtProyecciones){
        dtProyecciones.destroy();
        dtProyecciones = null;
        $('#dtProyecciones tbody').empty();
    }

    if(dtProyeccionesExcluidas){
        dtProyeccionesExcluidas.destroy();
        dtProyeccionesExcluidas = null;
        $('#dtProyeccionesExcluidas tbody').empty();
    }

    if(dtProyBrecha){
        dtProyBrecha.destroy();
        dtProyBrecha = null;
        $('#dtProyBrecha tbody').empty();
    }
}

function redirigirCalculoPoliticaAnterior(){
    if(!Array.isArray(proyeccionesExcluidasActual) || !proyeccionesExcluidasActual.length){
        Swal.fire({icon:'info',title:'Sin facturas',text:'No hay facturas para comisión por política anterior.'});
        return;
    }
    var filtros = getFiltrosProyecciones();
    var usuarioFiltro = parseInt(filtros && filtros.usuario_id ? filtros.usuario_id : 0, 10);

    var filasPoliticaAnterior = filtrarExcluidasPoliticaAnterior(proyeccionesExcluidasActual, usuarioFiltro);
    filasPoliticaAnterior = deduplicarFilasPoliticaAnteriorPorFactura(filasPoliticaAnterior);

    if(!filasPoliticaAnterior.length){
        Swal.fire({
            icon:'info',
            title:'Sin facturas elegibles',
            text:'Para política anterior solo aplican facturas donde el usuario está en capacidad vendedor/asesor.'
        });
        return;
    }

    var facturaIds = [];
    filasPoliticaAnterior.forEach(function(item){
        var id = parseInt(item && item.factura_id ? item.factura_id : 0, 10);
        if(id > 0) facturaIds.push(id);
    });

    facturaIds = Array.from(new Set(facturaIds));

    if(!facturaIds.length){
        Swal.fire({icon:'warning',title:'Datos incompletos',text:'No fue posible identificar IDs de factura para el cálculo.'});
        return;
    }

    var payload = {
        modo: 'politica_anterior',
        fecha_inicio: filtros.fechaInicio || '',
        fecha_final: filtros.fechaFin || '',
        usuario_id: filtros.usuario_id || '',
        rol_id: filtros.rol_id || '',
        factura_ids: facturaIds,
        filas: filasPoliticaAnterior
    };

    try {
        sessionStorage.setItem('comisionPoliticaAnteriorPayload', JSON.stringify(payload));
    } catch (e) {
        // Ignorar si el navegador no permite almacenamiento.
    }

    // Mostrar sección inline en lugar de redirigir
    var $sec = $('#seccionPoliticaAnterior');
    $('#iframePoliticaAnterior').attr('src', '');
    $('#proyExcluidasWrap').hide();
    $sec.show();
    $('#iframePoliticaAnterior').attr('src', '/reporte/comision/politica-anterior?embed=1');
    setTimeout(function(){ $sec[0].scrollIntoView({behavior:'smooth', block:'start'}); }, 150);
}

function exportarBrechaApFcExcel(){
    if(typeof XLSX === 'undefined'){
        Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
        return;
    }

    if(!proyBrechaDataActual.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay datos de brecha para exportar.'});
        return;
    }

    var ahora = new Date();
    var stamp = ahora.getFullYear().toString()
        + String(ahora.getMonth()+1).padStart(2,'0')
        + String(ahora.getDate()).padStart(2,'0')
        + '_' + String(ahora.getHours()).padStart(2,'0')
        + String(ahora.getMinutes()).padStart(2,'0')
        + String(ahora.getSeconds()).padStart(2,'0');

    var dataEx = [['Fecha Pago/Cierre AP','Factura','Tipo Brecha','Cliente','Facturador','Vendedor','Gestor Entrega','SubTotal Factura','Registros FC','Meses FC','Total Comisión FC']];
    proyBrechaDataActual.forEach(function(r){
        dataEx.push([
            r.fecha_pago_cierre_ap || '',
            r.factura || '',
            r.tipo_brecha || '',
            r.cliente || '',
            r.facturador || '',
            r.vendedor || '',
            r.gestor_entrega || '',
            parseFloat(r.sub_total_factura || 0),
            parseInt(r.fc_registros || 0, 10),
            Array.isArray(r.fc_meses) ? r.fc_meses.join(' | ') : '',
            parseFloat(r.fc_total_comision || 0)
        ]);
    });

    var wsEx = XLSX.utils.aoa_to_sheet(dataEx);
    for (var i = 2; i <= proyBrechaDataActual.length + 1; i++) {
        ['H','K'].forEach(function(col){
            var ref = col + i;
            if (wsEx[ref] && typeof wsEx[ref].v === 'number') wsEx[ref].z = '"L." #,##0.00';
        });
    }
    wsEx['!autofilter'] = { ref: 'A1:K1' };
    wsEx['!cols'] = [{wch:18},{wch:20},{wch:16},{wch:34},{wch:24},{wch:24},{wch:24},{wch:18},{wch:12},{wch:24},{wch:18}];
    var wbEx = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wbEx, wsEx, 'Brecha AP FC');
    XLSX.writeFile(wbEx, 'brecha_ap_fc_' + stamp + '.xlsx');
}

function exportarProyeccionesExcel(tipo){
    if(tipo === 'excluidas'){
        // Excluidas — mantiene export XLSX.js (sin tabs de rol)
        if(typeof XLSX === 'undefined'){
            Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
            return;
        }
        if(!proyeccionesExcluidasActual.length){
            Swal.fire({icon:'info',title:'Sin datos',text:'No hay facturas excluidas para exportar.'});
            return;
        }
        var ahora = new Date();
        var stamp = ahora.getFullYear().toString()
            + String(ahora.getMonth()+1).padStart(2,'0')
            + String(ahora.getDate()).padStart(2,'0')
            + '_' + String(ahora.getHours()).padStart(2,'0')
            + String(ahora.getMinutes()).padStart(2,'0')
            + String(ahora.getSeconds()).padStart(2,'0');

        var dataEx = [['Fecha Pago','Fecha Creacion Factura','Factura','Producto','Cliente','Categoria Precio','Rol Comisión','Usuario','Razon No Comisionable','Detalle Tecnico']];
        proyeccionesExcluidasActual.forEach(function(r){
            dataEx.push([
                r.fecha_pago || '',
                r.fecha_creacion_factura || '',
                r.factura || '',
                r.producto || '',
                r.cliente || '',
                r.categoria_precio || '',
                r.rol_nombre || r.capacidad || '',
                r.usuario || '',
                r.razon_no_comisionable || '',
                Array.isArray(r.motivos) ? r.motivos.join(' | ') : ''
            ]);
        });
        var wsEx = XLSX.utils.aoa_to_sheet(dataEx);
        wsEx['!autofilter'] = { ref: 'A1:J1' };
        wsEx['!cols'] = [{wch:12},{wch:20},{wch:20},{wch:30},{wch:36},{wch:24},{wch:16},{wch:24},{wch:34},{wch:80}];
        var wbEx = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wbEx, wsEx, 'Excluidas');
        XLSX.writeFile(wbEx, 'facturas_excluidas_' + stamp + '.xlsx');
        return;
    }

    // Proyectadas — export formateado vía PHP (3 pestañas por rol + Todas)
    if(!proyeccionesDataActual || !proyeccionesDataActual.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay proyecciones para exportar.'});
        return;
    }

    var filtros = getFiltrosProyecciones ? getFiltrosProyecciones() : {};
    var periodoTexto = (filtros.fechaInicio || '') + ' al ' + (filtros.fechaFin || '');

    var token = 'proy_dl_' + Date.now();
    var form  = document.createElement('form');
    form.method = 'POST';
    form.action = '/comision/reporte/proyecciones/exportar-excel';
    form.style.display = 'none';

    function addInput(name, value) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = name; inp.value = value;
        form.appendChild(inp);
    }

    addInput('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    addInput('rows', JSON.stringify(proyeccionesDataActual));
    addInput('periodo', periodoTexto);
    addInput('download_token', token);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function exportarProyeccionesNomina(){
    if(!proyeccionesDataActual || !proyeccionesDataActual.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'Genera primero la proyección antes de descargar la nómina.'});
        return;
    }

    var filtros = getFiltrosProyecciones ? getFiltrosProyecciones() : {};
    if(!filtros.usuario_id){
        Swal.fire({icon:'warning',title:'Usuario requerido',text:'Selecciona un usuario activo para generar la nómina.'});
        return;
    }

    var token = 'proy_nom_' + Date.now();
    var form  = document.createElement('form');
    form.method = 'POST';
    form.action = '/comision/reporte/proyecciones/exportar-nomina';
    form.style.display = 'none';

    function addInput(name, value){
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = name; inp.value = value;
        form.appendChild(inp);
    }

    addInput('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    addInput('fechaInicio', filtros.fechaInicio || '');
    addInput('fechaFin',    filtros.fechaFin    || '');
    addInput('usuario_id',  filtros.usuario_id  || '');
    addInput('rol_id',      filtros.rol_id      || '');
    addInput('download_token', token);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function getFiltrosRevisionFacturas(){
    var fechaInicio = normalizeDateInput($('#revFechaInicio').val());
    var fechaFin = normalizeDateInput($('#revFechaFin').val());

    return {
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        usuario_id: $('#revUsuario').val() || '',
        rol_id: $('#revRol').val() || ''
    };
}

function generarRevisionFacturas(){
    var f = getFiltrosRevisionFacturas();

    if(!f.fechaInicio || !f.fechaFin){
        Swal.fire({icon:'warning',title:'Filtros requeridos',text:'Seleccione fecha de pago inicio y fin.'});
        return;
    }

    if(f.fechaInicio > f.fechaFin){
        Swal.fire({icon:'warning',title:'Rango inválido',text:'La fecha inicio no puede ser mayor a la fecha fin.'});
        return;
    }

    $.when(
        $.getJSON('/comision/reporte/revision/facturas', f),
        $.getJSON('/comision/reporte/revision/productos', f)
    ).done(function(respFacturas, respProductos){
        renderRevisionFacturas(respFacturas[0] || {});
        renderRevisionProductos(respProductos[0] || {});
    }).fail(function(xhr){
        var msg = (xhr && xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.text)) || 'No fue posible generar revisión de facturas.';
        Swal.fire({icon:'error',title:'Revisión de Facturas',text:msg});
    });
}

function renderRevisionFacturas(resp){
    var data = Array.isArray(resp.data) ? resp.data : [];
    var totales = resp.totales || {};
    revisionFacturasDataActual = data;

    $('#revEmptyState').hide();
    $('#revFacturaWrap').show();
    $('#revInfo').show();
    $('#revFacturas').text(totales.facturas || 0);
    $('#revRegistrosFactura').text(totales.registros || 0);
    $('#revMontoAbonado').text(fmtMoney(totales.monto_abonado_total || 0));

    if(dtRevFacturas){
        dtRevFacturas.destroy();
        $('#dtRevFacturas tbody').empty();
    }

    dtRevFacturas = $('#dtRevFacturas').DataTable({
        data:data,
        processing:false,
        serverSide:false,
        searching:true,
        paging:true,
        pageLength:10,
        lengthMenu:[[10,25,50,100],[10,25,50,100]],
        scrollX:true,
        responsive:false,
        autoWidth:false,
        language:lang(),
        order:[[0,'asc'],[1,'asc']],
        columns:[
            {data:'fecha_pago_revision',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'fecha_creacion_factura',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'factura',render:function(d){return '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d || '—')+'</code>'; }},
            {data:'aplicacion_pagos_id',className:'text-center'},
            {data:'cliente',render:function(d){return esc(d || '—');}},
            {data:'escala_cliente',render:function(d){return esc(d || '—');}},
            {data:'capacidad',className:'text-center',render:function(d){return esc(d || '—');}},
            {data:'rol_nombre',render:function(d){return esc(d || '—');}},
            {data:'usuario',render:function(d){return '<strong>'+esc(d || '—')+'</strong>'; }},
            {data:'saldo',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'monto_abonado_total',className:'text-right',render:function(d){return '<strong>'+fmtMoney(d || 0)+'</strong>'; }},
            {data:'cantidad_abonos',className:'text-center'},
            {data:'fecha_ultimo_abono',render:function(d){return esc(d || '—');}},
            {data:'sub_total_factura',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'total_factura',className:'text-right',render:function(d){return '<strong>'+fmtMoney(d || 0)+'</strong>'; }}
        ]
    });
}

function renderRevisionProductos(resp){
    var data = Array.isArray(resp.data) ? resp.data : [];
    var totales = resp.totales || {};
    revisionProductosDataActual = data;

    $('#revProductoWrap').show();
    $('#revRegistrosProducto').text(totales.registros || 0);

    if(dtRevProductos){
        dtRevProductos.destroy();
        $('#dtRevProductos tbody').empty();
    }

    dtRevProductos = $('#dtRevProductos').DataTable({
        data:data,
        processing:false,
        serverSide:false,
        searching:true,
        paging:true,
        pageLength:10,
        lengthMenu:[[10,25,50,100],[10,25,50,100]],
        scrollX:true,
        responsive:false,
        autoWidth:false,
        language:lang(),
        order:[[0,'asc'],[1,'asc'],[6,'asc']],
        columns:[
            {data:'fecha_pago_revision',className:'text-nowrap',render:function(d){return esc(d || '—');}},
            {data:'factura',render:function(d){return '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d || '—')+'</code>'; }},
            {data:'cliente',render:function(d){return esc(d || '—');}},
            {data:'capacidad',className:'text-center',render:function(d){return esc(d || '—');}},
            {data:'rol_nombre',render:function(d){return esc(d || '—');}},
            {data:'usuario',render:function(d){return esc(d || '—');}},
            {data:'producto',render:function(d){return '<strong>'+esc(d || '—')+'</strong>'; }},
            {data:'categoria_precio',render:function(d){return esc(d || '—');}},
            {data:'cantidad',className:'text-right',render:function(d){return parseFloat(d || 0).toLocaleString('es-HN',{minimumFractionDigits:0,maximumFractionDigits:2});}},
            {data:'precio_unidad',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'precio_seleccionado',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'base_unitaria',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'base_precio_seleccionado',className:'text-right',render:function(d){return fmtMoney(d || 0);}},
            {data:'porcentaje_comision',className:'text-right',render:function(d){return d===null || d===undefined ? '—' : (parseFloat(d).toFixed(2)+'%');}},
            {data:'comision_proyectada',className:'text-right',render:function(d){return d===null || d===undefined ? '—' : ('<strong>'+fmtMoney(d || 0)+'</strong>');}}
        ]
    });
}

function limpiarRevisionFacturas(){
    var hoy=new Date(),ini=new Date(hoy.getFullYear(),hoy.getMonth(),1);
    $('#revFechaInicio').val(fmtDate(ini));
    $('#revFechaFin').val(fmtDate(hoy));
    $('#revUsuario').val(null).trigger('change');
    $('#revRol').val(null).trigger('change');

    $('#revInfo').hide();
    $('#revFacturas').text('0');
    $('#revRegistrosFactura').text('0');
    $('#revRegistrosProducto').text('0');
    $('#revMontoAbonado').text(fmtMoney(0));

    $('#revEmptyState').show();
    $('#revFacturaWrap').hide();
    $('#revProductoWrap').hide();

    revisionFacturasDataActual = [];
    revisionProductosDataActual = [];

    if(dtRevFacturas){
        dtRevFacturas.destroy();
        dtRevFacturas = null;
        $('#dtRevFacturas tbody').empty();
    }

    if(dtRevProductos){
        dtRevProductos.destroy();
        dtRevProductos = null;
        $('#dtRevProductos tbody').empty();
    }
}

function exportarRevisionFacturasExcel(tipo){
    if(typeof XLSX === 'undefined'){
        Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
        return;
    }

    var ahora = new Date();
    var stamp = ahora.getFullYear().toString()
        + String(ahora.getMonth()+1).padStart(2,'0')
        + String(ahora.getDate()).padStart(2,'0')
        + '_' + String(ahora.getHours()).padStart(2,'0')
        + String(ahora.getMinutes()).padStart(2,'0')
        + String(ahora.getSeconds()).padStart(2,'0');

    if(tipo === 'productos'){
        if(!revisionProductosDataActual.length){
            Swal.fire({icon:'info',title:'Sin datos',text:'No hay datos de productos para exportar.'});
            return;
        }

        var dataPr = [['Fecha Pago','Factura','Cliente','Capacidad','Rol','Usuario','Producto','Categoria Precio','Cantidad','Precio Unidad','Precio Seleccionado','Base Unitaria','Base Precio Seleccionado','% Comision','Comision Proyectada']];
        revisionProductosDataActual.forEach(function(r){
            dataPr.push([
                r.fecha_pago_revision || '',
                r.factura || '',
                r.cliente || '',
                r.capacidad || '',
                r.rol_nombre || '',
                r.usuario || '',
                r.producto || '',
                r.categoria_precio || '',
                parseFloat(r.cantidad || 0),
                parseFloat(r.precio_unidad || 0),
                parseFloat(r.precio_seleccionado || 0),
                parseFloat(r.base_unitaria || 0),
                parseFloat(r.base_precio_seleccionado || 0),
                r.porcentaje_comision===null || r.porcentaje_comision===undefined ? '' : parseFloat(r.porcentaje_comision || 0),
                r.comision_proyectada===null || r.comision_proyectada===undefined ? '' : parseFloat(r.comision_proyectada || 0)
            ]);
        });

        var wsPr = XLSX.utils.aoa_to_sheet(dataPr);
        for (var i = 2; i <= revisionProductosDataActual.length + 1; i++) {
            ['J','K','L','M','O'].forEach(function(col){
                var ref = col + i;
                if (wsPr[ref] && typeof wsPr[ref].v === 'number') wsPr[ref].z = '"L." #,##0.00';
            });
            var pctRef = 'N' + i;
            if (wsPr[pctRef] && typeof wsPr[pctRef].v === 'number') wsPr[pctRef].z = '0.00%';
        }
        wsPr['!autofilter'] = { ref: 'A1:O1' };
        wsPr['!cols'] = [{wch:12},{wch:20},{wch:28},{wch:14},{wch:22},{wch:22},{wch:32},{wch:22},{wch:12},{wch:14},{wch:18},{wch:16},{wch:20},{wch:12},{wch:18}];
        var wbPr = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wbPr, wsPr, 'Revision Productos');
        XLSX.writeFile(wbPr, 'revision_facturas_productos_' + stamp + '.xlsx');
        return;
    }

    if(!revisionFacturasDataActual.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay datos de facturas para exportar.'});
        return;
    }

    var dataFa = [['Fecha Pago','Fecha Creacion Factura','Factura','Aplicacion Pago ID','Cliente','Escala Cliente','Capacidad','Rol','Usuario','Saldo','Abonado','Cantidad Abonos','Ultimo Abono','SubTotal Factura','Total Factura']];
    revisionFacturasDataActual.forEach(function(r){
        dataFa.push([
            r.fecha_pago_revision || '',
            r.fecha_creacion_factura || '',
            r.factura || '',
            r.aplicacion_pagos_id || '',
            r.cliente || '',
            r.escala_cliente || '',
            r.capacidad || '',
            r.rol_nombre || '',
            r.usuario || '',
            parseFloat(r.saldo || 0),
            parseFloat(r.monto_abonado_total || 0),
            parseInt(r.cantidad_abonos || 0, 10),
            r.fecha_ultimo_abono || '',
            parseFloat(r.sub_total_factura || 0),
            parseFloat(r.total_factura || 0)
        ]);
    });

    var wsFa = XLSX.utils.aoa_to_sheet(dataFa);
    for (var j = 2; j <= revisionFacturasDataActual.length + 1; j++) {
        ['J','K','N','O'].forEach(function(col){
            var ref2 = col + j;
            if (wsFa[ref2] && typeof wsFa[ref2].v === 'number') wsFa[ref2].z = '"L." #,##0.00';
        });
    }
    wsFa['!autofilter'] = { ref: 'A1:O1' };
    wsFa['!cols'] = [{wch:12},{wch:20},{wch:20},{wch:16},{wch:30},{wch:20},{wch:14},{wch:22},{wch:22},{wch:12},{wch:14},{wch:14},{wch:14},{wch:16},{wch:16}];
    var wbFa = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wbFa, wsFa, 'Revision Facturas');
    XLSX.writeFile(wbFa, 'revision_facturas_' + stamp + '.xlsx');
}

function generarReporte(){
    var f=getFiltrosNomina();

    // Mantener visible en el input el formato normalizado para evitar ambiguedad.
    if (f.fechaInicio) $('#fpFechaInicio').val(f.fechaInicio);
    if (f.fechaFin) $('#fpFechaFin').val(f.fechaFin);

    if(!f.fechaInicio||!f.fechaFin){
        Swal.fire({icon:'warning',title:'Filtros requeridos',text:'Seleccione fecha inicio y fecha fin.'});return;
    }
    if(f.fechaInicio>f.fechaFin){
        Swal.fire({icon:'warning',title:'Rango inválido',text:'La fecha inicio no puede ser mayor a la fecha fin.'});return;
    }
    $('#textPeriodo').text(f.fechaInicio+' al '+f.fechaFin);
    $('#badgePeriodo').show();
    cargarStats(f);
    cargarTab($('#rrhhTabs .nav-link.active').attr('href'),f);
}

function cargarTab(tabId,f){
    if(tabId==='#tab-nomina')        cargarNomina(f);
    else if(tabId==='#tab-detalle')  cargarDetalle(f);
    else if(tabId==='#tab-ranking')  cargarRanking(f);
    else if(tabId==='#tab-rol')      cargarRol(f);
    else if(tabId==='#tab-facturas') cargarFacturas(f);
    else if(tabId==='#tab-productos')cargarProductos(f);
    else if(tabId==='#tab-comparativo')cargarComparativo(f);
    else if(tabId==='#tab-reversiones')cargarReversiones(f);
}

function cargarStats(f){
    $.getJSON('/comision/reporte/stats',f,function(d){
        $('#kpiComision').text('L. '+d.total_comision);
        $('#kpiEmpleados').text(d.total_empleados);
        $('#kpiFacturas').text(d.total_facturas);
        $('#kpiRetenido').text('L. '+d.total_retenido);
        $('#kpiRevertido').text('L. '+d.total_revertido);
    });
}

function cargarNomina(f){
    $('#nominaEmptyState').hide();$('#nominaTableWrap').show();
    if(dtNomina){dtNomina.destroy();$('#dtNomina tbody').empty();}
    dtNomina=$('#dtNomina').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[3,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/nomina',data:f,type:'GET'},
        columns:[
            {data:null,orderable:false,searchable:false,className:'text-muted text-center',
             render:function(d,t,r,m){return m.row+1;}},
            {data:'empleado',render:function(d){return'<strong>'+esc(d)+'</strong>';}},
            {data:null,searchable:false,
             render:function(d,t,r){
                 var cant = parseInt(r.roles_cantidad||0,10);
                 var nombres = esc(r.roles_nombres||'—');
                 return '<div style="line-height:1.2;">'
                     + '<span style="display:block;font-size:12px;font-weight:800;color:#334155;">'+cant+' Rol'+(cant===1?'':'es')+'</span>'
                     + '<small style="color:#64748b;">('+nombres+')</small>'
                     + '</div>';
             }},
            {data:'mes',className:'text-center',
             render:function(d){return'<span style="background:#f0f9ff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:700;color:#0369a1;">'+esc(d)+'</span>';}},
            {data:'facturas_comisionadas',className:'text-center',
             render:function(d){return'<span class="badge badge-secondary">'+(d||0)+' Facturas</span>';}} ,
            {data:'comision_total',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}}
            ,
            {data:null,orderable:false,searchable:false,className:'text-center',
             render:function(d,t,r){
                 return '<button class="nom-detalle-btn" onclick="abrirDetalleNomina('+r.empleado_id+',\''+esc(r.empleado)+'\',\''+esc(r.mes_clave)+'\',\''+esc(r.mes)+'\')">'
                     + '<i class="fa fa-eye mr-1"></i>Ver'
                     + '</button>';
             }}
        ],
        footerCallback:function(){
            var api=this.api();
            var raw=api.column(5,{page:'all'}).data();
            var total=0;
            raw.each(function(v){
                var m=String(v).match(/[\d.,]+/);
                if(m)total+=parseFloat(m[0].replace(/,/g,''));
            });
            $('#nominaFooterTotal').text(fmtMoney(total));
            $('#nominaTotal').text(fmtMoney(total));
            $('#nominaInfo').show();
        }
    });
}

function abrirDetalleNomina(empleadoId, empleadoNombre, mesClave, mesLabel){
    var filtrosActuales = getFiltrosNomina();

    $('#mdnTitulo').html('<i class="fa fa-list-alt mr-2"></i>Detalle de '+esc(empleadoNombre)+' — '+esc(mesLabel));
    $('#modalDetalleNomina').modal('show');

    $('#modalDetalleNomina').off('shown.bs.modal.mdn').on('shown.bs.modal.mdn', function(){
        if (dtNominaDetalle) {
            dtNominaDetalle.columns.adjust().draw(false);
        }
    });

    if(dtNominaDetalle){
        dtNominaDetalle.destroy();
        $('#dtNominaDetalle tbody').empty();
    }

    dtNominaDetalle = $('#dtNominaDetalle').DataTable({
        processing:true,
        serverSide:false,
        searching:true,
        paging:true,
        pageLength:10,
        responsive:false,
        scrollX:true,
        autoWidth:false,
        language:{
            processing:'Cargando...',
            search:'Buscar:',
            lengthMenu:'Mostrar _MENU_ registros',
            info:'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty:'Mostrando 0 a 0 de 0 registros',
            infoFiltered:'(filtrado de _MAX_ registros)',
            zeroRecords:'No se encontraron resultados',
            emptyTable:'No hay datos para el período seleccionado',
            paginate:{first:'Primera',last:'Última',next:'>',previous:'<'}
        },
        ajax:{
            url:'/comision/reporte/nomina/detalle',
            type:'GET',
            data:{
                empleado_id:empleadoId,
                mes_clave:mesClave,
                fechaInicio:filtrosActuales.fechaInicio,
                fechaFin:filtrosActuales.fechaFin
            },
            dataSrc:function(json){
                detalleProductosFacturaMap = {};
                (json.data || []).forEach(function(item){
                    detalleProductosFacturaMap[String(item.id)] = item.detalle_productos || [];
                });
                return json.data || [];
            }
        },
        columns:[
            {data:'factura',className:'text-nowrap',render:function(d){return d?'<span style="display:inline-block;background:#ecfeff;color:#0f766e;border:1px solid #99f6e4;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:800;">'+esc(d)+'</span>':'—';}},
            {data:'cliente',render:function(d){return '<span style="font-weight:600;color:#334155;">'+esc(d||'—')+'</span>'; }},
            {data:'fecha_cierre',className:'text-center',render:function(d){return esc(d||'—');}},
            {data:'rol_comisionado',render:function(d){return badgeRol(d||'—');}},
            {data:'comision_original',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'retencion_aplicada',className:'text-right',render:function(d){return '<span style="color:#b45309;font-weight:700;">'+fmtMoney(d)+'</span>'; }},
            {data:'comision_final',className:'text-right',render:function(d,t,r){
                var finalNum = parseFloat(r.comision_final || 0);
                var retenido = parseFloat(r.retencion_aplicada || 0);
                var original = parseFloat(r.comision_original || 0);
                var retencionTotal = (original > 0.0001 && retenido > 0.0001 && Math.abs(finalNum) <= 0.0001);

                if(retencionTotal){
                    return '<strong class="monto-com" style="color:#b91c1c;">'+fmtMoney(finalNum)+'</strong>'
                        + '<br><span class="badge badge-warning" style="margin-top:4px;">RETENCION TOTAL</span>';
                }

                return '<strong class="monto-com">'+fmtMoney(finalNum)+'</strong>';
            }},
            {data:'base_comisionable',className:'text-right',render:function(d){return '<span style="font-weight:700;color:#0f766e;">'+fmtMoney(d)+'</span>'; }},
            {data:'fuente_base_comisionable',render:function(d){return '<span style="color:#475569;">'+esc(d||'—')+'</span>'; }},
            {data:null,className:'resumen-productos-col',orderable:false,render:function(d,t,r){ return formatResumenProductosHtml(r); }},
            {data:'estado',className:'text-center',render:function(d,t,r){
                var finalNum = parseFloat(r.comision_final || 0);
                var retenido = parseFloat(r.retencion_aplicada || 0);
                var original = parseFloat(r.comision_original || 0);
                var retencionTotal = (original > 0.0001 && retenido > 0.0001 && Math.abs(finalNum) <= 0.0001);

                if(String(d).toUpperCase()==='REVERTIDA'){
                    return '<span class="badge badge-danger">REVERTIDA</span>';
                }

                if(retencionTotal){
                    return '<span class="badge badge-warning">ACTIVA (RETENIDA 100%)</span>';
                }

                return '<span class="badge badge-success">ACTIVA</span>';
            }},
            {data:'observacion_reversa',render:function(d){return d?'<span style="color:#475569;">'+esc(d)+'</span>':'<span style="color:#94a3b8;">-</span>';}}
        ],
        columnDefs:[
            {targets:[4,5,6,7], className:'text-right text-nowrap'},
            {targets:[8,9,11], className:'text-left'},
            {targets:[10], className:'text-center text-nowrap'}
        ]
    });
}

function exportarDetalleNominaExcel(){
    if(typeof XLSX === 'undefined'){
        Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
        return;
    }
    if(!dtNominaDetalle){
        Swal.fire({icon:'info',title:'Sin datos',text:'Primero abra el detalle de nómina.'});
        return;
    }

    var rows = dtNominaDetalle.rows({search:'applied', order:'applied'}).data().toArray();
    if(!rows.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay filas para exportar.'});
        return;
    }

    var titulo = $('#mdnTitulo').text().trim();
    var fechaDescarga = new Date().toLocaleString('es-HN');
    var periodoTxt = ($('#textPeriodo').text() || '').trim();
    var empleadoPeriodo = titulo.replace(/^Detalle de\s*/i, '').trim();

    var data = [
        ['VALENCIA - REPORTE DE NOMINA DE COMISIONES'],
        ['Detalle por empleado y mes'],
        ['Empleado / Mes', empleadoPeriodo],
        ['Periodo filtrado', periodoTxt || 'No especificado'],
        ['Fecha de descarga', fechaDescarga],
        [],
        ['Factura','Cliente','Fecha Cierre','Rol Comisionado','Comisión Original','Retención Aplicada','Comisión Final','Base Comisionable','Fuente Base Comisionable','Resumen Producto/Escala','Estado','Observaciones de Reversa']
    ];

    rows.forEach(function(r){
        data.push([
            r.factura || '',
            r.cliente || '',
            r.fecha_cierre || '',
            r.rol_comisionado || '',
            parseFloat(r.comision_original || 0),
            parseFloat(r.retencion_aplicada || 0),
            parseFloat(r.comision_final || 0),
            parseFloat(r.base_comisionable || 0),
            r.fuente_base_comisionable || '',
            (r.resumen_productos || '').replace(/\n/g, ' | '),
            r.estado || '',
            r.observacion_reversa || ''
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = [
        {s:{r:0,c:0}, e:{r:0,c:11}},
        {s:{r:1,c:0}, e:{r:1,c:11}}
    ];

    // Aplicar formato monetario a columnas E, F, G y H en filas de detalle.
    // Encabezado de tabla está en fila 7, datos inician en fila 8.
    var startRow = 8;
    var endRow = startRow + rows.length - 1;
    var moneyFmt = '"L." #,##0.00';
    for (var r = startRow; r <= endRow; r++) {
        ['E','F','G','H'].forEach(function(col){
            var cellRef = col + r;
            if (ws[cellRef] && typeof ws[cellRef].v === 'number') {
                ws[cellRef].z = moneyFmt;
            }
        });
    }

    ws['!autofilter'] = { ref: 'A7:L7' };
    ws['!cols'] = [
        {wch:22},{wch:28},{wch:14},{wch:22},{wch:18},{wch:18},{wch:16},{wch:18},{wch:36},{wch:55},{wch:12},{wch:38}
    ];

    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Detalle Nomina');

    var stamp = new Date();
    var baseName = (empleadoPeriodo || 'detalle_nomina')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-zA-Z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .toLowerCase();

    var file = (baseName || 'detalle_nomina') + '_'
        + stamp.getFullYear()
        + String(stamp.getMonth()+1).padStart(2,'0')
        + String(stamp.getDate()).padStart(2,'0')
        + '_' + String(stamp.getHours()).padStart(2,'0')
        + String(stamp.getMinutes()).padStart(2,'0')
        + String(stamp.getSeconds()).padStart(2,'0')
        + '.xlsx';

    XLSX.writeFile(wb, file);
}

function cargarDetalle(f){
    if(!f.empleado_id){
        $('#detalleTableWrap').hide();$('#detalleEmptyState').show();return;
    }
    $('#detalleEmptyState').hide();$('#detalleTableWrap').show();
    if(dtDetalle){dtDetalle.destroy();$('#dtDetalle tbody').empty();}
    dtDetalle=$('#dtDetalle').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[0,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/empleado',
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,filtroEspecifico:f.empleado_id,rol_id:f.rol_id},type:'GET'},
        columns:[
            {data:'fecha',className:'text-center',
             render:function(d){return'<span style="font-size:12px;color:#64748b;">'+esc(d||'—')+'</span>';}},
            {data:'factura',
             render:function(d){return'<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d)+'</code>';}},
            {data:'cliente',render:function(d){return esc(d||'—');}},
            {data:'producto',render:function(d){return esc(d||'—');}},
            {data:'cantidad',className:'text-center'},
            {data:'monto_comision',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}}
        ]
    });
}

function cargarRanking(f){
    $('#rankingEmptyState').hide();$('#rankingTableWrap').show();
    if(dtRanking){dtRanking.destroy();$('#dtRanking tbody').empty();}
    dtRanking=$('#dtRanking').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[6,'desc']],pageLength:15,
        ajax:{url:'/comision/reporte/ranking',data:f,type:'GET'},
        columns:[
            {data:null,orderable:false,className:'text-center',
             render:function(d,t,r,m){
                 var p=m.row+1;
                 if(p===1)return'<span style="font-size:20px;">\uD83E\uDD47</span>';
                 if(p===2)return'<span style="font-size:20px;">\uD83E\uDD48</span>';
                 if(p===3)return'<span style="font-size:20px;">\uD83E\uDD49</span>';
                 return'<strong style="color:#64748b;">'+p+'</strong>';
             }},
            {data:'empleado',render:function(d){return'<strong>'+esc(d)+'</strong>';}},
            {data:'rol',render:function(d){return badgeRol(d);}},
            {data:'meses_activos',className:'text-center',
             render:function(d){return'<span class="badge badge-info" style="font-size:13px;">'+d+'</span>';}},
            {data:'mejor_mes',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'promedio_mes',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'total_comision',className:'text-right',
             render:function(d){return'<strong class="monto-com" style="font-size:15px;">'+fmtMoney(d)+'</strong>';}}
        ]
    });
}

function cargarRol(f){
    $('#rolEmptyState').hide();$('#rolTableWrap').show();
    if(dtRol){dtRol.destroy();$('#dtRol tbody').empty();}
    dtRol=$('#dtRol').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[2,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/rol',
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,filtroEspecifico:f.rol_id,empleado_id:f.empleado_id},type:'GET'},
        columns:[
            {data:'rol',render:function(d){return badgeRol(d);}},
            {data:'empleado',render:function(d){return'<strong>'+esc(d)+'</strong>';}},
            {data:'total_comisiones',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}},
            {data:'num_facturas',className:'text-center',
             render:function(d){return'<span class="badge badge-secondary">'+d+'</span>';}}
        ]
    });
}

function cargarFacturas(f){
    $('#facturasEmptyState').hide();$('#facturasTableWrap').show();
    if(dtFacturas){dtFacturas.destroy();$('#dtFacturas tbody').empty();}
    dtFacturas=$('#dtFacturas').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[6,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/facturas',
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,empleado_id:f.empleado_id,rol_id:f.rol_id},type:'GET'},
        columns:[
            {data:'factura',
             render:function(d){return'<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d)+'</code>';}},
            {data:'cliente',render:function(d){return esc(d||'—');}},
            {data:'empleado',render:function(d){return'<strong>'+esc(d)+'</strong>';}},
            {data:'total_venta',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'total_comision',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}},
            {data:null,className:'text-center',
             render:function(d,t,r){
                 var pct=r.total_venta>0?(parseFloat(r.total_comision)/parseFloat(r.total_venta)*100).toFixed(2):'0.00';
                 return'<span style="background:#dcfce7;color:#166534;border-radius:6px;padding:2px 8px;font-weight:700;font-size:11px;">'+pct+'%</span>';
             }},
            {data:'fecha',className:'text-center',
             render:function(d){return'<span style="font-size:12px;color:#64748b;">'+esc(d||'—')+'</span>';}}
        ]
    });
}

function cargarProductos(f){
    $('#productosEmptyState').hide();$('#productosTableWrap').show();
    if(dtProductos){dtProductos.destroy();$('#dtProductos tbody').empty();}
    dtProductos=$('#dtProductos').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[3,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/productos',
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,empleado_id:f.empleado_id,rol_id:f.rol_id},type:'GET'},
        columns:[
            {data:'producto',render:function(d){return esc(d||'—');}},
            {data:'codigo_barra',
             render:function(d){return d?'<code style="font-size:11px;background:#f1f5f9;padding:1px 5px;border-radius:4px;">'+esc(d)+'</code>':'—';}},
            {data:'cantidad_vendida',className:'text-center',
             render:function(d){return'<strong>'+d+'</strong>';}},
            {data:'total_comisiones',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}}
        ]
    });
}

function cargarComparativo(f){
    $('#comparativoEmptyState').hide();$('#comparativoTableWrap').show();
    if(dtComparativo){dtComparativo.destroy();$('#dtComparativo tbody').empty();}
    dtComparativo=$('#dtComparativo').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[0,'asc']],paging:false,
        ajax:{url:'/comision/reporte/comparativo',data:f,type:'GET'},
        columns:[
            {data:'mes',render:function(d){return'<strong style="font-size:13px;">'+esc(d)+'</strong>';}},
            {data:'empleados',className:'text-center',
             render:function(d){return'<span class="badge badge-primary" style="font-size:13px;padding:4px 10px;">'+d+'</span>';}},
            {data:'roles',className:'text-center',
             render:function(d){return'<span class="badge badge-secondary">'+d+'</span>';}},
            {data:'mayor_comision',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'menor_comision',className:'text-right',render:function(d){return fmtMoney(d);}},
            {data:'total_comisiones',className:'text-right',
             render:function(d){return'<strong class="monto-com" style="font-size:15px;">'+fmtMoney(d)+'</strong>';}}
        ]
    });
}

function cargarReversiones(f){
    $('#reversionesEmptyState').hide();$('#reversionesTableWrap').show();
    if(dtReversiones){dtReversiones.destroy();$('#dtReversiones tbody').empty();}
    dtReversiones=$('#dtReversiones').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[0,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/reversiones',data:f,type:'GET'},
        columns:[
            {data:'created_at',className:'text-center',
             render:function(d){
                 if(!d)return'—';
                 var txt=String(d).replace('T',' ');
                 return '<span style="font-size:12px;color:#64748b;">'+esc(txt)+'</span>';
             }},
            {data:'factura',
             render:function(d){
                 return d
                    ? '<code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:11px;">'+esc(d)+'</code>'
                    : '—';
             }},
            {data:'cliente',render:function(d){return esc(d||'—');}},
            {data:'usuario_anulo',render:function(d){return '<strong>'+esc(d||'—')+'</strong>'; }},
            {data:'monto_abono_anulado',className:'text-right',
             render:function(d){return fmtMoney(d);}},
            {data:'total_revertido',className:'text-right',
             render:function(d){return '<strong class="monto-com">'+fmtMoney(d)+'</strong>'; }},
            {data:'comisiones_afectadas',className:'text-center',
             render:function(d){return '<span class="badge badge-secondary">'+(d||0)+'</span>'; }},
            {data:'factura_reabierta',className:'text-center',
             render:function(d){
                 return parseInt(d,10)===1
                    ? '<span class="badge badge-warning">Sí</span>'
                    : '<span class="badge badge-light">No</span>';
             }},
            {data:'motivo',
             render:function(d){
                 if(!d)return '—';
                 var txt = esc(d);
                 return txt.length > 90 ? txt.substring(0,90)+'…' : txt;
             }}
        ]
    });
}

function limpiarFiltros(){
    var hoy=new Date(),ini=new Date(hoy.getFullYear(),hoy.getMonth(),1);
    $('#fpFechaInicio').val(fmtDate(ini));
    $('#fpFechaFin').val(fmtDate(hoy));
    $('#fpEmpleado').val(null).trigger('change');
    $('#fpRol').val(null).trigger('change');
    $('#kpiComision,#kpiEmpleados,#kpiFacturas,#kpiRetenido,#kpiRevertido').text('—');
    $('#badgePeriodo .badge,#textPeriodo').text('');
    $('.empty-state').show();
    $('#nominaTableWrap,#detalleTableWrap,#rankingTableWrap,#rolTableWrap,#facturasTableWrap,#productosTableWrap,#comparativoTableWrap,#reversionesTableWrap').hide();
    if(dtNomina){dtNomina.destroy();dtNomina=null;$('#dtNomina tbody').empty();}
    if(dtNominaDetalle){dtNominaDetalle.destroy();dtNominaDetalle=null;$('#dtNominaDetalle tbody').empty();}
    if(dtDetalle){dtDetalle.destroy();dtDetalle=null;$('#dtDetalle tbody').empty();}
    if(dtRanking){dtRanking.destroy();dtRanking=null;$('#dtRanking tbody').empty();}
    if(dtRol){dtRol.destroy();dtRol=null;$('#dtRol tbody').empty();}
    if(dtFacturas){dtFacturas.destroy();dtFacturas=null;$('#dtFacturas tbody').empty();}
    if(dtProductos){dtProductos.destroy();dtProductos=null;$('#dtProductos tbody').empty();}
    if(dtComparativo){dtComparativo.destroy();dtComparativo=null;$('#dtComparativo tbody').empty();}
    if(dtReversiones){dtReversiones.destroy();dtReversiones=null;$('#dtReversiones tbody').empty();}
}

function exportarExcel(tipo){
    var p=getFiltrosNomina();
    p.tipo=tipo||'nomina';
    window.location.href='/comision/reporte/excel?'+$.param(p);
}

function cargarPeriodosConciliados(){
    if(!$('#ccPeriodo').length && !$('#proyPeriodoConciliado').length) return;

    $.getJSON('/comisiones/conciliacion/periodos', function(resp){
        var periodos = Array.isArray(resp && resp.periodos) ? resp.periodos : [];
        var options = ['<option value="">Seleccione un período conciliado</option>'];
        var optionsProy = ['<option value="">Seleccione un mes conciliado</option>'];

        periodos
            .filter(function(item){ return item && item.estado === 'conciliado'; })
            .forEach(function(item){
                options.push('<option value="'+esc(item.periodo)+'">'+esc(item.periodo_label)+'</option>');
                optionsProy.push('<option value="'+esc(item.periodo)+'">'+esc(item.periodo_label)+'</option>');
            });

        if($('#ccPeriodo').length){
            $('#ccPeriodo').html(options.join(''));
        }

        if($('#proyPeriodoConciliado').length){
            $('#proyPeriodoConciliado').html(optionsProy.join(''));
        }
    });
}

function cargarConciliadasDesdeFiltro(){
    var periodo = $('#ccPeriodo').val();
    if(!periodo){
        Swal.fire({icon:'warning',title:'Período requerido',text:'Seleccione un período conciliado.'});
        return;
    }

    cargarConciliadas(periodo);
}

function cargarConciliadas(periodo){
    $.getJSON('/comisiones/conciliacion/detalle', {periodo: periodo}, function(resp){
        renderConciliadas(resp || {});
    }).fail(function(xhr){
        var mensaje = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.text)) || 'No fue posible cargar el período conciliado.';
        Swal.fire({icon:'error',title:'Error al cargar',text:mensaje});
    });
}

function renderConciliadas(resp){
    var resumen = resp.resumen || {};
    var empleados = Array.isArray(resp.empleados) ? resp.empleados : [];
    var periodo = resp.periodo || $('#ccPeriodo').val() || '';

    $('#ccEmptyState').hide();
    $('#ccResumenWrap').show();
    $('#textPeriodo').text(resp.label || resp.periodo || '');
    $('#badgePeriodo').show();
    $('#ccTotalBruto').text(fmtMoney(resumen.total_bruto || 0));
    $('#ccTotalRetencion').text(fmtMoney(resumen.total_retencion || 0));
    $('#ccTotalNeto').text(fmtMoney(resumen.total_neto || 0));
    $('#ccTotalEmpleados').text(resumen.cantidad_empleados || 0);
    $('#ccTotalFacturas').text(resumen.cantidad_facturas || 0);

    var rows = empleados.map(function(item){
        var rolAsignado = item.roles_asignados || item.rol || '—';
        var facturasReales = parseInt(item.facturas_reales || item.facturas || 0, 10);
        var comisionConciliada = item.comision_conciliada || item.comision_acumulada || 0;

        return '<tr>'
            + '<td><strong>'+esc(item.nombre || '—')+'</strong></td>'
            + '<td>'+badgeRol(rolAsignado)+'</td>'
            + '<td class="text-center">'+facturasReales+'</td>'
            + '<td class="text-right"><strong class="monto-com">'+fmtMoney(comisionConciliada)+'</strong></td>'
            + '<td>'+esc(item.fecha_conciliacion || '—')+'</td>'
            + '<td>'+esc(item.conciliado_por || '—')+'</td>'
            + '<td class="text-center">'
                + '<button class="btn btn-sm" style="border:1px solid #16a34a;color:#166534;background:#f0fdf4;font-weight:700;" '
                + 'onclick="descargarResumenConciliadoEmpleado('+parseInt(item.user_id || 0, 10)+',\''+esc(periodo)+'\')">'
                + '<i class="fa fa-file-excel-o"></i> Excel'
                + '</button>'
            + '</td>'
            + '</tr>';
    }).join('');

    if(!rows){
        rows = '<tr><td colspan="7" style="padding:30px;text-align:center;color:#94a3b8;">Sin empleados conciliados para este período.</td></tr>';
    }

    $('#ccTableBody').html(rows);
}

function limpiarFiltrosConciliadas(){
    $('#ccPeriodo').val('');
    $('#ccResumenWrap').hide();
    $('#ccEmptyState').show();
    if($('#rrhhTabs .nav-link.active').attr('href') === '#tab-conciliadas'){
        $('#badgePeriodo').hide();
        $('#textPeriodo').text('');
    }
    $('#ccTotalBruto').text(fmtMoney(0));
    $('#ccTotalRetencion').text(fmtMoney(0));
    $('#ccTotalNeto').text(fmtMoney(0));
    $('#ccTotalEmpleados').text('0');
    $('#ccTotalFacturas').text('0');
    $('#ccTableBody').empty();
}

function descargarResumenConciliadoEmpleado(userId, periodo){
    userId = parseInt(userId || 0, 10);
    periodo = String(periodo || '').trim();

    if(userId <= 0 || !periodo){
        Swal.fire({icon:'warning',title:'Datos incompletos',text:'No se pudo determinar el empleado o período para exportar.'});
        return;
    }

    var url = '/comisiones/conciliacion/exportar/empleado?periodo=' + encodeURIComponent(periodo) + '&user_id=' + encodeURIComponent(userId);
    window.location.href = url;
}

function descargarConciliadasMasivo(){
    var periodo = String($('#ccPeriodo').val() || '').trim();
    if(!periodo){
        Swal.fire({icon:'warning',title:'Período requerido',text:'Seleccione un período conciliado para exportar.'});
        return;
    }

    var url = '/comisiones/conciliacion/exportar/masivo?periodo=' + encodeURIComponent(periodo);
    window.location.href = url;
}

function imprimirTabla(tabId){
    var $tbl=$('#'+tabId), titulo=$('.nav-tabs .active').text().trim();
    var w=window.open('','_blank','width=900,height=650');
    w.document.write('<html><head><title>Comisiones RRHH \u2014 '+titulo+'<\/title>');
    w.document.write('<style>body{font-family:sans-serif;font-size:12px;}');
    w.document.write('table{border-collapse:collapse;width:100%;}');
    w.document.write('th,td{border:1px solid #ccc;padding:5px 8px;}');
    w.document.write('th{background:#1e1b4b;color:#fff;}<\/style><\/head><body>');
    w.document.write('<h3 style="color:#1e1b4b;">'+titulo+'<\/h3>');
    w.document.write($tbl[0].outerHTML);
    w.document.write('<\/body><\/html>');
    w.document.close();
    w.print();
}

/* ═══════════════════════════════════════════════════════════════════
 *  TAB: FACTURA POR ACTOR
 * ═══════════════════════════════════════════════════════════════════ */
var dtFacturaActor = null;
var facturaActorDataActual = [];

function fmtMoneyFa(v) {
    var n = parseFloat(v || 0);
    return 'L ' + n.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function initFaSelects() {
    // Inicializar Select2 vacíos (se llenan al cambiar fechas)
    var selectsCfg = [
        { id: '#faAsesor',     placeholder: '— Todos los Asesores —' },
        { id: '#faTeleasesor', placeholder: '— Todos los Tele Asesores —' },
        { id: '#faGestor',     placeholder: '— Todos los Gestores —' },
    ];
    selectsCfg.forEach(function(cfg) {
        if (!$(cfg.id).data('select2')) {
            $(cfg.id).select2({ placeholder: cfg.placeholder, allowClear: true, width: '100%' });
        }
    });
}

function cargarActoresPorPeriodo() {
    var desde = $('#faDesde').val();
    var hasta = $('#faHasta').val();
    if (!desde || !hasta) return;

    // Guardar selecciones actuales para restaurarlas si siguen disponibles
    var selAsesor    = $('#faAsesor').val();
    var selTele      = $('#faTeleasesor').val();
    var selGestor    = $('#faGestor').val();

    $.getJSON('/comision/reporte/actores-por-periodo', { fechaInicio: desde, fechaFin: hasta })
    .done(function(resp) {
        var poblarSelect = function(selector, lista, selActual) {
            var $sel = $(selector);
            $sel.empty().append('<option value=""></option>');
            (lista || []).forEach(function(u) {
                var opt = new Option(u.name, u.id, false, String(u.id) === String(selActual));
                $sel.append(opt);
            });
            $sel.trigger('change');
        };
        poblarSelect('#faAsesor',     resp.asesores     || [], selAsesor);
        poblarSelect('#faTeleasesor', resp.teleasesores || [], selTele);
        poblarSelect('#faGestor',     resp.gestores     || [], selGestor);
    });
}

function setFaDefaultDates() {
    var now  = new Date();
    var y    = now.getFullYear();
    var m    = now.getMonth(); // 0-based
    var pad  = function(n) { return String(n).padStart(2, '0'); };
    var lastDay = new Date(y, m + 1, 0).getDate();
    $('#faDesde').val(y + '-' + pad(m + 1) + '-01');
    $('#faHasta').val(y + '-' + pad(m + 1) + '-' + pad(lastDay));
}

function generarFacturaActor() {
    var desde       = $('#faDesde').val();
    var hasta       = $('#faHasta').val();
    var asesorId    = $('#faAsesor').val()     || '';
    var teleId      = $('#faTeleasesor').val() || '';
    var gestorId    = $('#faGestor').val()     || '';

    if (!desde || !hasta) {
        Swal.fire({ icon: 'warning', title: 'Fechas requeridas', text: 'Seleccione el período de búsqueda.' });
        return;
    }

    $('#btnFaGenerar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
    $('#faEmptyState').hide();
    $('#faTableWrap').hide();
    $('#faKpis').hide();

    $.getJSON('/comision/reporte/factura-por-actor', {
        fechaInicio:    desde,
        fechaFin:       hasta,
        asesor_id:      asesorId,
        teleasesor_id:  teleId,
        gestor_id:      gestorId
    })
    .done(function(resp) {
        var filas   = resp.data    || [];
        var totales = resp.totales || {};
        facturaActorDataActual = filas;

        if (!filas.length) {
            $('#faEmptyState').show().find('p').text('No se encontraron facturas cerradas para el período seleccionado.');
            $('#btnFaGenerar').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar');
            return;
        }

        // KPIs
        $('#faTotalFacturas').text(totales.facturas || 0);
        $('#faTotalSubtotal').text(fmtMoneyFa(totales.subtotal));
        $('#faTotalIsv').text(fmtMoneyFa(totales.isv));
        $('#faTotalTotal').text(fmtMoneyFa(totales.total));
        $('#faKpis').show();

        // DataTable
        if ($.fn.DataTable.isDataTable('#dtFacturaActor')) {
            dtFacturaActor.clear().rows.add(filas).draw();
        } else {
            dtFacturaActor = $('#dtFacturaActor').DataTable({
                data: filas,
                pageLength: 25,
                order: [[5, 'desc']],
                scrollX: true,
                autoWidth: false,
                language: {
                    search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ registros',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    infoEmpty: 'Sin registros', zeroRecords: 'No hay resultados',
                    paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' }
                },
                columns: [
                    { data: 'factura',          render: function(d) { return '<code style="background:#f3e8ff;padding:2px 6px;border-radius:4px;font-size:11px;color:#7c3aed;">' + (d || '—') + '</code>'; } },
                    { data: 'asesor_comercial',  render: function(d) { return '<span style="font-weight:600;color:#059669;">' + (d || '—') + '</span>'; } },
                    { data: 'tele_asesor',       render: function(d) { return '<span style="font-weight:600;color:#2563eb;">' + (d || '—') + '</span>'; } },
                    { data: 'gestor_entregas',   render: function(d) { return '<span style="font-weight:600;color:#d97706;">' + (d || '—') + '</span>'; } },
                    { data: 'fecha_creacion',    className: 'text-nowrap' },
                    { data: 'fecha_ultimo_pago', className: 'text-nowrap', render: function(d) { return '<strong>' + (d || '—') + '</strong>'; } },
                    { data: 'tipo_factura',      className: 'text-center', render: function(d) {
                        var cls = (d || '').toLowerCase().indexOf('cred') !== -1 ? 'badge-info' : 'badge-success';
                        return '<span class="badge ' + cls + '">' + (d || '—') + '</span>';
                    }},
                    { data: 'politica', className: 'text-center', render: function(d) {
                        if (!d || d === 'Sin asignar') return '<span class="badge badge-secondary">' + (d || 'Sin asignar') + '</span>';
                        if (d === 'Política Anterior') return '<span class="badge badge-warning" style="background:#f59e0b;color:#fff;">Política Anterior</span>';
                        return '<span class="badge badge-primary" style="background:#7c3aed;color:#fff;">Nueva Política</span>';
                    }},
                    { data: 'subtotal',   className: 'text-right', render: function(d) { return fmtMoneyFa(d); } },
                    { data: 'isv',        className: 'text-right', render: function(d) { return fmtMoneyFa(d); } },
                    { data: 'total',      className: 'text-right', render: function(d) { return '<strong>' + fmtMoneyFa(d) + '</strong>'; } },
                ]
            });
        }

        $('#faTableWrap').show();
    })
    .fail(function(xhr) {
        Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error al cargar los datos.' });
    })
    .always(function() {
        $('#btnFaGenerar').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar');
    });
}

function limpiarFa() {
    $('#faAsesor').val(null).trigger('change');
    $('#faTeleasesor').val(null).trigger('change');
    $('#faGestor').val(null).trigger('change');
    setFaDefaultDates();
    cargarActoresPorPeriodo();
    if ($.fn.DataTable.isDataTable('#dtFacturaActor')) {
        dtFacturaActor.clear().draw();
    }
    facturaActorDataActual = [];
    $('#faTableWrap').hide();
    $('#faKpis').hide();
    $('#faEmptyState').show();
}

function exportarFacturaActorExcel(){
    if(typeof XLSX === 'undefined'){
        Swal.fire({icon:'warning',title:'Librería no disponible',text:'No fue posible cargar la librería de Excel.'});
        return;
    }

    if(!facturaActorDataActual.length){
        Swal.fire({icon:'info',title:'Sin datos',text:'No hay facturas para exportar.'});
        return;
    }

    var ahora = new Date();
    var stamp = ahora.getFullYear().toString()
        + String(ahora.getMonth()+1).padStart(2,'0')
        + String(ahora.getDate()).padStart(2,'0')
        + '_' + String(ahora.getHours()).padStart(2,'0')
        + String(ahora.getMinutes()).padStart(2,'0')
        + String(ahora.getSeconds()).padStart(2,'0');

    var dataEx = [['N° Factura (CAI)','Asesor Comercial','Tele Asesor','Gestor de Entregas','Fecha Creación','Fecha Último Pago','Tipo Factura','Política','Subtotal','ISV','Total']];
    facturaActorDataActual.forEach(function(r){
        dataEx.push([
            r.factura || '',
            r.asesor_comercial || '',
            r.tele_asesor || '',
            r.gestor_entregas || '',
            r.fecha_creacion || '',
            r.fecha_ultimo_pago || '',
            r.tipo_factura || '',
            r.politica || '',
            parseFloat(r.subtotal || 0),
            parseFloat(r.isv || 0),
            parseFloat(r.total || 0)
        ]);
    });

    var wsEx = XLSX.utils.aoa_to_sheet(dataEx);
    for (var i = 2; i <= facturaActorDataActual.length + 1; i++) {
        ['I','J','K'].forEach(function(col){
            var ref = col + i;
            if (wsEx[ref] && typeof wsEx[ref].v === 'number') wsEx[ref].z = '"L." #,##0.00';
        });
    }
    wsEx['!autofilter'] = { ref: 'A1:K1' };
    wsEx['!cols'] = [{wch:20},{wch:22},{wch:22},{wch:22},{wch:14},{wch:14},{wch:12},{wch:16},{wch:16},{wch:14},{wch:16}];
    var wbEx = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wbEx, wsEx, 'Factura por Actor');
    XLSX.writeFile(wbEx, 'factura_por_actor_' + stamp + '.xlsx');
}

// Init al entrar a la pestaña
$('a[href="#tab-factura-actor"]').on('shown.bs.tab', function() {
    if (!$('#faDesde').val()) setFaDefaultDates();
    initFaSelects();
    cargarActoresPorPeriodo();
    if (dtFacturaActor) dtFacturaActor.columns.adjust();
});

$(document).ready(function() {
    $('#btnFaGenerar').on('click', generarFacturaActor);
    $('#btnFaLimpiar').on('click', limpiarFa);

    // Recargar actores cuando cambian las fechas
    var faDateTimer = null;
    $('#faDesde, #faHasta').on('change', function() {
        clearTimeout(faDateTimer);
        faDateTimer = setTimeout(cargarActoresPorPeriodo, 400);
    });
});

/* ===================================================================
   TAB: CUADRE LIBRO DE COBROS vs BASE COMISIONABLE
   =================================================================== */
var dtCuadre = null;
var cuadreDataActual = [];

function fmtL(v) {
    var n = parseFloat(v) || 0;
    return 'L. ' + n.toLocaleString('es-HN', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function initCuadreSelect() {
    if (!$('#cuadreVendedor').hasClass('select2-hidden-accessible')) {
        $('#cuadreVendedor').select2({
            placeholder: '— Todos los vendedores —',
            allowClear: true,
            ajax: {
                url: '/comision/empleados/lista',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { search: params.term }; },
                processResults: function(data) {
                    var items = [{ id: '', text: '— Todos los vendedores —' }];
                    $.each(data.data || data, function(i, u) {
                        items.push({ id: u.id, text: u.name });
                    });
                    return { results: items };
                },
                cache: true
            }
        });
    }
}

function setCuadreDefaultDates() {
    var hoy = new Date();
    var primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    var fmt = function(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    };
    $('#cuadreDesde').val(fmt(primerDia));
    $('#cuadreHasta').val(fmt(hoy));
}

function generarCuadre() {
    var fi = $('#cuadreDesde').val();
    var ff = $('#cuadreHasta').val();
    if (!fi || !ff) {
        Swal.fire({icon:'warning', title:'Fechas requeridas', text:'Seleccione fecha inicio y fecha fin.'});
        return;
    }

    var params = { fechaInicio: fi, fechaFin: ff };
    var vid = $('#cuadreVendedor').val();
    if (vid) params.usuario_id = vid;

    $('#btnCuadreGenerar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generando...');
    $('#cuadreEmptyState').hide();
    $('#cuadreInfo').hide();
    $('#cuadreTableWrap').hide();

    $.getJSON('/comision/reporte/cuadre-libro-cobros', params, function(res) {
        cuadreDataActual = res.data || [];
        var tot = res.totales || {};

        // KPIs
        $('#cuadreFacturasRango').text(tot.facturas_en_rango || 0);
        $('#cuadreFacturasCompletas').text(tot.facturas_completas || 0);
        $('#cuadreTotalCobrado').text(fmtL(tot.total_cobrado));
        $('#cuadreCobradoSinIsv').text(fmtL(tot.total_cobrado_sin_isv));
        $('#cuadreIsvCobrado').text(fmtL(tot.total_isv_cobrado));
        $('#cuadreSubTotalFacturas').text(fmtL(tot.total_sub_total_facturas));

        // Ecuación exacta: Cobrado Sin ISV + ISV Cobrado = Total Cobrado
        $('#cuadreEcuacion').text(
            fmtL(tot.total_cobrado_sin_isv) + ' + ' + fmtL(tot.total_isv_cobrado) + ' = ' + fmtL(tot.total_cobrado)
        );

        var brechaParciales = parseFloat(tot.brecha_parciales || 0);
        $('#cuadreBrechaParciales').text(fmtL(brechaParciales))
            .css('color', brechaParciales > 0.01 ? '#b45309' : '#059669');

        $('#cuadreBaseComisionable').text(fmtL(tot.total_base_comisionable));
        $('#cuadreBaseComisionableCierre').text(fmtL(tot.base_comisionable_cierre_en_rango));
        $('#cuadreFacturasCierre').text(tot.facturas_cierre_en_rango || 0);

        var dif = parseFloat(tot.diferencia || 0);
        $('#cuadreDiferencia').text(fmtL(dif))
            .css('color', dif > 0 ? '#dc2626' : (dif < 0 ? '#b45309' : '#059669'));

        // Footer totals
        $('#cuadreFootCobrado').text(fmtL(tot.total_cobrado));
        $('#cuadreFootCobradoSinIsv').text(fmtL(tot.total_cobrado_sin_isv));
        $('#cuadreFootBase').text(fmtL(tot.total_base_comisionable));
        $('#cuadreFootDif').text(fmtL(tot.diferencia))
            .css('color', dif > 0 ? '#dc2626' : '#059669');

        // DataTable
        if (dtCuadre) { dtCuadre.destroy(); }
        $('#dtCuadre tbody').empty();

        cuadreDataActual.forEach(function(r) {
            var estadoBadge = r.estado_pago === 'PAGADA'
                ? '<span style="background:#d1fae5;color:#065f46;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:600;">PAGADA</span>'
                : '<span style="background:#fef3c7;color:#92400e;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:600;">PARCIAL</span>';

            var difColor = parseFloat(r.diferencia || 0) > 50
                ? 'color:#dc2626;font-weight:600;'
                : (parseFloat(r.diferencia || 0) < -50 ? 'color:#b45309;font-weight:600;' : '');

            $('#dtCuadre tbody').append(
                '<tr>' +
                '<td style="white-space:nowrap;font-size:11px;">' + (r.factura || '') + '</td>' +
                '<td>' + (r.cliente || '') + '</td>' +
                '<td>' + (r.vendedor || '') + '</td>' +
                '<td>' + (r.facturador || '') + '</td>' +
                '<td>' + (r.fecha_pago_cierre || '') + '</td>' +
                '<td class="text-right">' + fmtL(r.total_cobrado_factura) + '</td>' +
                '<td class="text-right">' + fmtL(r.cobrado_sin_isv) + '</td>' +
                '<td class="text-right">' + fmtL(r.isv_cobrado) + '</td>' +
                '<td class="text-right">' + fmtL(r.sub_total_factura) + '</td>' +
                '<td class="text-right">' + fmtL(r.saldo_pendiente) + '</td>' +
                '<td class="text-center">' + estadoBadge + '</td>' +
                '<td class="text-right">' + fmtL(r.base_comisionable) + '</td>' +
                '<td class="text-right" style="' + difColor + '">' + fmtL(r.diferencia) + '</td>' +
                '<td style="font-size:11px;color:#64748b;max-width:260px;word-wrap:break-word;">' + (r.razones_diferencia || '—') + '</td>' +
                '</tr>'
            );
        });

        dtCuadre = $('#dtCuadre').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[4, 'asc']],
            columnDefs: [
                { targets: [10], className: 'text-center' },
                { targets: [5, 6, 7, 8, 9, 11, 12], className: 'text-right' }
            ],
            dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between"ip>'
        });

        $('#cuadreInfo').show();
        $('#cuadreTableWrap').show();
    })
    .fail(function(xhr) {
        Swal.fire({icon:'error', title:'Error', text: (xhr.responseJSON && xhr.responseJSON.message) || 'Error al generar el cuadre.'});
    })
    .always(function() {
        $('#btnCuadreGenerar').prop('disabled', false).html('<i class="fa fa-balance-scale"></i> Generar Cuadre');
    });
}

function limpiarCuadre() {
    setCuadreDefaultDates();
    if ($('#cuadreVendedor').hasClass('select2-hidden-accessible')) {
        $('#cuadreVendedor').val(null).trigger('change');
    }
    if (dtCuadre) { dtCuadre.destroy(); dtCuadre = null; }
    cuadreDataActual = [];
    $('#dtCuadre tbody').empty();
    $('#cuadreInfo').hide();
    $('#cuadreTableWrap').hide();
    $('#cuadreEmptyState').show();
}

function exportarCuadreExcel() {
    if (typeof XLSX === 'undefined') {
        Swal.fire({icon:'warning', title:'Librería no disponible', text:'No fue posible cargar la librería de Excel.'});
        return;
    }
    if (!cuadreDataActual.length) {
        Swal.fire({icon:'info', title:'Sin datos', text:'No hay datos para exportar.'});
        return;
    }

    var ahora = new Date();
    var stamp = ahora.getFullYear().toString()
        + String(ahora.getMonth()+1).padStart(2,'0')
        + String(ahora.getDate()).padStart(2,'0')
        + '_' + String(ahora.getHours()).padStart(2,'0')
        + String(ahora.getMinutes()).padStart(2,'0');

    var headers = [
        'Factura','Cliente','Vendedor','Fecha Pago',
        '# Abonos Rango','Cobrado Rango','Cobrado Sin ISV','SubTotal Factura','ISV Factura',
        'Saldo Pendiente','Estado Pago','AP Cerrada','Cierre en Rango',
        'Base Comisionable','Diferencia (Sin ISV - Base)','Razones Diferencia'
    ];
    var rows = [headers];
    cuadreDataActual.forEach(function(r) {
        rows.push([
            r.factura || '',
            r.cliente || '',
            r.vendedor || '',
            r.ultima_fecha_pago || '',
            parseInt(r.num_abonos_en_rango || 0),
            parseFloat(r.total_cobrado_en_rango || 0),
            parseFloat(r.cobrado_sin_isv || 0),
            parseFloat(r.sub_total_factura || 0),
            parseFloat(r.isv_factura || 0),
            parseFloat(r.saldo_pendiente || 0),
            r.estado_pago || '',
            r.tiene_ap_cerrada ? 'Sí' : 'No',
            r.cierre_en_rango ? 'Sí' : 'No',
            parseFloat(r.base_comisionable || 0),
            parseFloat(r.diferencia_cobrado_base || 0),
            r.razones_diferencia || ''
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(rows);
    var numCols = ['F','G','H','I','J','M','N'];
    for (var i = 2; i <= cuadreDataActual.length + 1; i++) {
        numCols.forEach(function(col) {
            var ref = col + i;
            if (ws[ref] && typeof ws[ref].v === 'number') ws[ref].z = '"L." #,##0.00';
        });
    }
    ws['!autofilter'] = { ref: 'A1:P1' };
    ws['!cols'] = [
        {wch:22},{wch:28},{wch:20},{wch:12},
        {wch:10},{wch:16},{wch:16},{wch:16},{wch:14},
        {wch:14},{wch:10},{wch:10},{wch:14},
        {wch:18},{wch:20},{wch:50}
    ];
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Cuadre Cobros');
    XLSX.writeFile(wb, 'cuadre_libro_cobros_' + stamp + '.xlsx');
}

$('a[href="#tab-cuadre-cobros"]').on('shown.bs.tab', function() {
    if (!$('#cuadreDesde').val()) setCuadreDefaultDates();
    initCuadreSelect();
    if (dtCuadre) dtCuadre.columns.adjust();
});

$(document).ready(function() {
    $('#btnCuadreGenerar').on('click', generarCuadre);
    $('#btnCuadreLimpiar').on('click', limpiarCuadre);
});

/* ===================================================================
   TAB: AUDITORÍA CONTABLE
   =================================================================== */
var dtAuditoria = null;
var auditoriaDataActual = [];

function initAudSelect() {
    if (!$('#audVendedor').hasClass('select2-hidden-accessible')) {
        $('#audVendedor').select2({
            placeholder: '— Seleccione vendedor —',
            allowClear: true,
            ajax: {
                url: '/comision/empleados/lista',
                dataType: 'json',
                delay: 250,
                data: function(p) { return { search: p.term }; },
                processResults: function(data) {
                    var items = [{ id: '', text: '— Todos —' }];
                    $.each(data.data || data, function(i, u) { items.push({ id: u.id, text: u.name }); });
                    return { results: items };
                },
                cache: true
            }
        });
    }
}

function setAudDefaultDates() {
    var hoy = new Date();
    var fmt = function(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    };
    $('#audDesde').val(fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1)));
    $('#audHasta').val(fmt(hoy));
}

function generarAuditoria() {
    var fi = $('#audDesde').val();
    var ff = $('#audHasta').val();
    if (!fi || !ff) { Swal.fire({icon:'warning', title:'Fechas requeridas'}); return; }

    var params = { fechaInicio: fi, fechaFin: ff };
    var vid = $('#audVendedor').val();
    if (vid) params.vendedor_id = vid;

    $('#btnAudGenerar').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Auditando...');
    $('#audEmptyState').hide();
    $('#audKpis').hide();
    $('#audTableWrap').hide();

    $.getJSON('/comision/reporte/auditoria-contable', params, function(res) {
        auditoriaDataActual = res.data || [];
        var k = res.kpi || {};

        // KPIs
        $('#audTotalCobrado').text(fmtL(k.total_cobrado_rango));
        $('#audTotalFacturas').text(k.facturas_total || 0);
        $('#audEnComisiones').text(k.facturas_en_comisiones || 0);
        $('#audEnComisionesDetalle').text(
            'Escala: ' + (k.facturas_en_escala||0) +
            ' · Pol.Ant Elegible: ' + (k.facturas_en_politica_elegible||0) +
            ' · Pol.Ant Registrada: ' + (k.facturas_en_politica_registrada||0)
        );
        $('#audSinComisiones').text(k.facturas_sin_comisiones || 0);
        $('#audPagadas').text(k.facturas_pagadas_completas || 0);
        $('#audParciales').text(k.facturas_parciales || 0);
        $('#audCuadreOk').text(k.facturas_con_cuadre_ok || 0);
        $('#audCuadreError').text(k.facturas_con_cuadre_error || 0);

        // Table
        if (dtAuditoria) { dtAuditoria.destroy(); }
        $('#dtAuditoria tbody').empty();

        auditoriaDataActual.forEach(function(r) {
            // Color de fila:
            // Rojo = sin comisiones Y tiene AP en rango (debería tener comisión)
            // Rosa claro = sin AP en rango (solo abono parcial, no aplica)
            // Amarillo = pago parcial
            // Naranja = cuadre falla
            var rowStyle = '';
            if (!r.en_comisiones && r.tiene_ap_en_rango) rowStyle = 'background:#fff1f2;';
            else if (!r.en_comisiones && !r.tiene_ap_en_rango) rowStyle = 'background:#f8fafc;';
            else if (r.estado_pago === 'PARCIAL') rowStyle = 'background:#fffbeb;';
            else if (!r.cuadre_ok) rowStyle = 'background:#fff7ed;';

            var cuadreBadge = r.cuadre_ok
                ? '<span style="color:#059669;font-weight:700;font-size:13px;">✓</span>'
                : '<span style="color:#dc2626;font-weight:700;font-size:13px;">✗ L.' + Math.abs(parseFloat(r.diferencia_cuadre||0)).toFixed(2) + '</span>';

            var estadoBadge = r.estado_pago === 'PAGADA'
                ? '<span style="background:#d1fae5;color:#065f46;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:600;">PAGADA</span>'
                : '<span style="background:#fef3c7;color:#92400e;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:600;">PARCIAL</span>';

            var apBadge = r.tiene_ap_cerrada
                ? '<span style="color:#059669;font-weight:700;">✓</span>'
                : '<span style="color:#94a3b8;">—</span>';

            var comBadge = r.en_comisiones
                ? '<span style="color:#059669;font-weight:700;">✓</span>'
                : '<span style="background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 6px;font-size:11px;font-weight:700;">NO</span>';

            var alertasHtml = r.alertas
                ? '<span style="font-size:11px;color:#dc2626;">' + r.alertas + '</span>'
                : '<span style="color:#94a3b8;font-size:11px;">—</span>';

            $('#dtAuditoria tbody').append(
                '<tr style="' + rowStyle + '">' +
                '<td style="font-size:11px;white-space:nowrap;">' + (r.factura||'') + '</td>' +
                '<td style="font-size:12px;">' + (r.cliente||'') + '</td>' +
                '<td style="font-size:12px;">' + (r.vendedor||'') + '</td>' +
                '<td>' + (r.fecha_creacion_factura||'') + '</td>' +
                '<td>' + (r.ultima_fecha_pago_rango||'') + '</td>' +
                '<td class="text-center">' + (r.num_abonos_en_rango||0) + '</td>' +
                '<td class="text-right">' + fmtL(r.cobrado_en_rango) + '</td>' +
                '<td class="text-right"><strong>' + fmtL(r.total_abonado_historico) + '</strong></td>' +
                '<td class="text-right"><strong>' + fmtL(r.total_factura) + '</strong></td>' +
                '<td class="text-right">' + cuadreBadge + '</td>' +
                '<td class="text-center">' + cuadreBadge + '</td>' +
                '<td class="text-center">' + estadoBadge + '</td>' +
                '<td class="text-center">' + apBadge + '</td>' +
                '<td class="text-center">' + comBadge + '</td>' +
                '<td style="font-size:11px;">' + (r.politica||'') + '</td>' +
                '<td>' + alertasHtml + '</td>' +
                '</tr>'
            );
        });

        dtAuditoria = $('#dtAuditoria').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 50,
            order: [[4, 'asc']],
            dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between"ip>'
        });

        $('#audKpis').show();
        $('#audTableWrap').show();
    })
    .fail(function(xhr) {
        Swal.fire({icon:'error', title:'Error', text:(xhr.responseJSON && xhr.responseJSON.message)||'Error al auditar.'});
    })
    .always(function() {
        $('#btnAudGenerar').prop('disabled', false).html('<i class="fa fa-search-dollar"></i> Auditar');
    });
}

function limpiarAuditoria() {
    setAudDefaultDates();
    if ($('#audVendedor').hasClass('select2-hidden-accessible')) $('#audVendedor').val(null).trigger('change');
    if (dtAuditoria) { dtAuditoria.destroy(); dtAuditoria = null; }
    auditoriaDataActual = [];
    $('#dtAuditoria tbody').empty();
    $('#audKpis').hide();
    $('#audTableWrap').hide();
    $('#audEmptyState').show();
}

function exportarAuditoriaExcel() {
    if (typeof XLSX === 'undefined') { Swal.fire({icon:'warning',title:'Librería no disponible'}); return; }
    if (!auditoriaDataActual.length) { Swal.fire({icon:'info',title:'Sin datos'}); return; }

    var ahora = new Date();
    var stamp = ahora.getFullYear().toString()
        + String(ahora.getMonth()+1).padStart(2,'0')
        + String(ahora.getDate()).padStart(2,'0')
        + '_' + String(ahora.getHours()).padStart(2,'0')
        + String(ahora.getMinutes()).padStart(2,'0');

    var headers = ['Factura','Cliente','Vendedor','Facturador','Fecha Creación',
        'Último Pago Rango','# Abonos Rango','Cobrado en Rango',
        'Total Abonado Histórico','Total Factura','Diferencia Cuadre','Cuadre OK',
        'Estado Pago','AP Cerrada','AP Cerrada en Rango','Fecha Cierre AP',
        'En Comisiones','En Escala','Pol.Ant Elegible','Pol.Ant Registrada','Política','Alertas'];
    var rows = [headers];
    auditoriaDataActual.forEach(function(r) {
        rows.push([
            r.factura||'', r.cliente||'', r.vendedor||'', r.facturador||'',
            r.fecha_creacion_factura||'', r.ultima_fecha_pago_rango||'',
            parseInt(r.num_abonos_en_rango||0),
            parseFloat(r.cobrado_en_rango||0),
            parseFloat(r.total_abonado_historico||0),
            parseFloat(r.total_factura||0),
            parseFloat(r.diferencia_cuadre||0),
            r.cuadre_ok ? 'SÍ' : 'NO',
            r.estado_pago||'',
            r.tiene_ap_cerrada ? 'SÍ' : 'NO',
            r.tiene_ap_en_rango ? 'SÍ' : 'NO',
            r.fecha_cierre_ap||'',
            r.en_comisiones ? 'SÍ' : 'NO',
            r.en_escala ? 'SÍ' : 'NO',
            r.en_politica_elegible ? 'SÍ' : 'NO',
            r.en_politica_registrada ? 'SÍ' : 'NO',
            r.politica||'',
            r.alertas||''
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(rows);
    ['H','I','J','K'].forEach(function(col) {
        for (var i = 2; i <= auditoriaDataActual.length + 1; i++) {
            var ref = col + i;
            if (ws[ref] && typeof ws[ref].v === 'number') ws[ref].z = '"L." #,##0.00';
        }
    });
    ws['!autofilter'] = { ref: 'A1:T1' };
    ws['!cols'] = [{wch:22},{wch:28},{wch:20},{wch:20},{wch:13},{wch:14},
                   {wch:10},{wch:16},{wch:18},{wch:16},{wch:14},{wch:10},
                   {wch:12},{wch:10},{wch:14},{wch:12},{wch:10},{wch:14},{wch:20},{wch:60}];
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Auditoría Contable');
    XLSX.writeFile(wb, 'auditoria_contable_' + stamp + '.xlsx');
}

$('a[href="#tab-auditoria"]').on('shown.bs.tab', function() {
    if (!$('#audDesde').val()) setAudDefaultDates();
    initAudSelect();
    if (dtAuditoria) dtAuditoria.columns.adjust();
});

$(document).ready(function() {
    $('#btnAudGenerar').on('click', generarAuditoria);
    $('#btnAudLimpiar').on('click', limpiarAuditoria);
});
