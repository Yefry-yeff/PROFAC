/* === RRHH COMISIONES — REPORTERÍA === */
var dtNomina=null, dtDetalle=null, dtRanking=null, dtRol=null,
    dtFacturas=null, dtProductos=null, dtComparativo=null, dtReversiones=null;

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
function getFiltros(){
    return{
        fechaInicio:$('#fpFechaInicio').val(),
        fechaFin:$('#fpFechaFin').val(),
        empleado_id:$('#fpEmpleado').val()||'',
        rol_id:$('#fpRol').val()||''
    };
}

function generarReporte(){
    var f=getFiltros();
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
        $('#kpiPromedio').text('L. '+d.promedio);
    });
}

function cargarNomina(f){
    $('#nominaEmptyState').hide();$('#nominaTableWrap').show();
    if(dtNomina){dtNomina.destroy();$('#dtNomina tbody').empty();}
    dtNomina=$('#dtNomina').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[3,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/nomina',data:f,type:'GET'},
        columns:[
            {data:null,orderable:false,className:'text-muted text-center',
             render:function(d,t,r,m){return m.row+1;}},
            {data:'empleado',render:function(d){return'<strong>'+esc(d)+'</strong>';}},
            {data:'rol',render:function(d){return badgeRol(d);}},
            {data:'mes',className:'text-center',
             render:function(d){return'<span style="background:#f0f9ff;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:700;color:#0369a1;">'+esc(d)+'</span>';}},
            {data:'num_facturas',className:'text-center',
             render:function(d){return'<span class="badge badge-secondary">'+d+'</span>';}},
            {data:'total_comision',className:'text-right',
             render:function(d){return'<strong class="monto-com">'+fmtMoney(d)+'</strong>';}}
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

function cargarDetalle(f){
    if(!f.empleado_id){
        $('#detalleTableWrap').hide();$('#detalleEmptyState').show();return;
    }
    $('#detalleEmptyState').hide();$('#detalleTableWrap').show();
    if(dtDetalle){dtDetalle.destroy();$('#dtDetalle tbody').empty();}
    dtDetalle=$('#dtDetalle').DataTable({
        processing:true,serverSide:true,language:lang(),order:[[0,'desc']],pageLength:25,
        ajax:{url:'/comision/reporte/empleado',
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,filtroEspecifico:f.empleado_id},type:'GET'},
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
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin,filtroEspecifico:f.rol_id},type:'GET'},
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
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin},type:'GET'},
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
              data:{fechaInicio:f.fechaInicio,fechaFin:f.fechaFin},type:'GET'},
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
    $('#kpiComision,#kpiEmpleados,#kpiFacturas,#kpiPromedio').text('—');
    $('#badgePeriodo .badge,#textPeriodo').text('');
    $('.empty-state').show();
    $('#nominaTableWrap,#detalleTableWrap,#rankingTableWrap,#rolTableWrap,#facturasTableWrap,#productosTableWrap,#comparativoTableWrap,#reversionesTableWrap').hide();
    if(dtNomina){dtNomina.destroy();dtNomina=null;$('#dtNomina tbody').empty();}
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
