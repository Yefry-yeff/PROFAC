/* === RRHH COMISIONES — REPORTERÍA === */
var dtNomina=null, dtNominaDetalle=null, dtDetalle=null, dtRanking=null, dtRol=null,
    dtFacturas=null, dtProductos=null, dtComparativo=null, dtReversiones=null;
var detalleProductosFacturaMap = {};
var detalleProductosFacturaActual = { facturaComisionId: null, factura: '', cliente: '', productos: [] };

$(document).ready(function(){
    // Fechas por defecto: mes actual
    var hoy=new Date(), ini=new Date(hoy.getFullYear(),hoy.getMonth(),1);
    $('#fpFechaInicio').val(fmtDate(ini));
    $('#fpFechaFin').val(fmtDate(hoy));

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

    $('#btnGenerar').on('click', generarReporte);
    $('#btnLimpiar').on('click', limpiarFiltros);

    // Cargar tab activo al cambiar
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        if($('#badgePeriodo').is(':visible')){
            cargarTab($(e.target).attr('href'), getFiltros());
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
    $('#mpfSubtitulo').text((row && row.cliente ? row.cliente + ' | ' : '') + 'Escala, porcentaje, cantidad, precio y comisión por línea');

    var html = productos.map(function(item){
        return '<tr>'
            + '<td>'+esc(item.producto || '—')+'</td>'
            + '<td>'+esc(item.categoria_cliente_escala || '—')+'</td>'
            + '<td>'+esc(item.categoria_precio_vendida || '—')+'</td>'
            + '<td class="text-right">'+parseFloat(item.porcentaje_comision || 0).toFixed(2)+'%</td>'
            + '<td class="text-right">'+parseFloat(item.cantidad || 0).toLocaleString('es-HN',{minimumFractionDigits:0,maximumFractionDigits:2})+'</td>'
            + '<td class="text-right">'+fmtMoney(item.precio_venta || 0)+'</td>'
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
        ['Producto','Categoría Cliente Escala','Categoría Precio Vendida','%','Cantidad','Precio Venta','Comisión']
    ];

    productos.forEach(function(item){
        data.push([
            item.producto || '',
            item.categoria_cliente_escala || '',
            item.categoria_precio_vendida || '',
            parseFloat(item.porcentaje_comision || 0),
            parseFloat(item.cantidad || 0),
            parseFloat(item.precio_venta || 0),
            parseFloat(item.comision || 0)
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = [
        {s:{r:0,c:0}, e:{r:0,c:6}}
    ];

    var startRow = 7;
    var endRow = startRow + productos.length - 1;
    for (var r = startRow; r <= endRow; r++) {
        var pctRef = 'D' + r;
        if (ws[pctRef] && typeof ws[pctRef].v === 'number') ws[pctRef].z = '0.00%';
        ['F','G'].forEach(function(col){
            var cellRef = col + r;
            if (ws[cellRef] && typeof ws[cellRef].v === 'number') ws[cellRef].z = '"L." #,##0.00';
        });
    }

    ws['!autofilter'] = { ref: 'A6:G6' };
    ws['!cols'] = [
        {wch:40},{wch:24},{wch:24},{wch:10},{wch:12},{wch:14},{wch:14}
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

function getFiltros(){
    var fechaInicio = normalizeDateInput($('#fpFechaInicio').val());
    var fechaFin = normalizeDateInput($('#fpFechaFin').val());

    return{
        fechaInicio:fechaInicio,
        fechaFin:fechaFin,
        empleado_id:$('#fpEmpleado').val()||'',
        rol_id:$('#fpRol').val()||''
    };
}

function generarReporte(){
    var f=getFiltros();

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
            data:{empleado_id:empleadoId, mes_clave:mesClave},
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
            {data:'comision_final',className:'text-right',render:function(d){return '<strong class="monto-com">'+fmtMoney(d)+'</strong>'; }},
            {data:null,className:'resumen-productos-col',orderable:false,render:function(d,t,r){ return formatResumenProductosHtml(r); }},
            {data:'estado',className:'text-center',render:function(d){
                if(String(d).toUpperCase()==='REVERTIDA'){
                    return '<span class="badge badge-danger">REVERTIDA</span>';
                }
                return '<span class="badge badge-success">ACTIVA</span>';
            }},
            {data:'observacion_reversa',render:function(d){return d?'<span style="color:#475569;">'+esc(d)+'</span>':'<span style="color:#94a3b8;">-</span>';}}
        ],
        columnDefs:[
            {targets:[4,5,6], className:'text-right text-nowrap'},
            {targets:[7,9], className:'text-left'},
            {targets:[8], className:'text-center text-nowrap'}
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
        ['Factura','Cliente','Fecha Cierre','Rol Comisionado','Comisión Original','Retención Aplicada','Comisión Final','Resumen Producto/Escala','Estado','Observaciones de Reversa']
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
            (r.resumen_productos || '').replace(/\n/g, ' | '),
            r.estado || '',
            r.observacion_reversa || ''
        ]);
    });

    var ws = XLSX.utils.aoa_to_sheet(data);
    ws['!merges'] = [
        {s:{r:0,c:0}, e:{r:0,c:9}},
        {s:{r:1,c:0}, e:{r:1,c:9}}
    ];

    // Aplicar formato monetario a columnas E, F y G en filas de detalle.
    // Encabezado de tabla está en fila 7, datos inician en fila 8.
    var startRow = 8;
    var endRow = startRow + rows.length - 1;
    var moneyFmt = '"L." #,##0.00';
    for (var r = startRow; r <= endRow; r++) {
        ['E','F','G'].forEach(function(col){
            var cellRef = col + r;
            if (ws[cellRef] && typeof ws[cellRef].v === 'number') {
                ws[cellRef].z = moneyFmt;
            }
        });
    }

    ws['!autofilter'] = { ref: 'A7:J7' };
    ws['!cols'] = [
        {wch:22},{wch:28},{wch:14},{wch:22},{wch:18},{wch:18},{wch:16},{wch:55},{wch:12},{wch:38}
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
    var p=getFiltros();
    p.tipo=tipo||'nomina';
    window.location.href='/comision/reporte/excel?'+$.param(p);
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
