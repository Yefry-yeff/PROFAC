<div class="modal fade nd-modal" id="modal_filtros_facturas_nd" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content">
        <div class="modal-header nd-modal-header"><h5 class="modal-title"><i class="fa fa-filter mr-2"></i>Filtros de facturas</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <p class="nd-section-label"><i class="fa fa-calendar mr-1"></i>Rango de emisión</p>
            <div class="nd-filter-grid"><div class="row"><div class="col-md-6"><div class="form-group"><label>Desde</label><input type="date" class="form-control" id="ndf_desde"></div></div><div class="col-md-6"><div class="form-group"><label>Hasta</label><input type="date" class="form-control" id="ndf_hasta"></div></div></div></div>
            <p class="nd-section-label"><i class="fa fa-search mr-1"></i>Criterios</p>
            <div class="nd-filter-grid"><div class="row">
                <div class="col-md-6"><div class="form-group"><label>N° Factura</label><input type="text" class="form-control" id="ndf_factura" placeholder="Número o CAI"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Cliente</label><select class="form-control" id="ndf_cliente" style="width:100%"><option></option></select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Vendedor</label><select class="form-control" id="ndf_vendedor" style="width:100%"><option></option></select></div></div>
                <div class="col-md-3"><div class="form-group"><label>Nota de débito</label><select class="form-control" id="ndf_estado_nota"><option value="">Todas</option><option value="sin_asignar">Sin asignar</option><option value="asignada">Asignada</option></select></div></div>
                <div class="col-md-3"><div class="form-group"><label>Estado de cobro</label><select class="form-control" id="ndf_estado_cobro"><option value="">Todos</option><option value="pendiente">Pendiente</option><option value="completo">Completo</option></select></div></div>
            </div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" onclick="ndLimpiarFiltrosFacturas()"><i class="fa fa-eraser mr-1"></i>Limpiar</button><button type="button" class="btn btn-nd-primary btn-sm" onclick="ndAplicarFiltrosFacturas()"><i class="fa fa-search mr-1"></i>Buscar</button></div>
    </div></div>
</div>

<div class="modal fade nd-modal" id="modal_filtros_notas_nd" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content">
        <div class="modal-header nd-modal-header"><h5 class="modal-title"><i class="fa fa-filter mr-2"></i>Filtros de notas generadas</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body">
            <p class="nd-section-label"><i class="fa fa-calendar mr-1"></i>Rango de emisión</p>
            <div class="nd-filter-grid"><div class="row"><div class="col-md-6"><div class="form-group"><label>Desde</label><input type="date" class="form-control" id="ndn_desde"></div></div><div class="col-md-6"><div class="form-group"><label>Hasta</label><input type="date" class="form-control" id="ndn_hasta"></div></div></div></div>
            <p class="nd-section-label"><i class="fa fa-search mr-1"></i>Criterios</p>
            <div class="nd-filter-grid"><div class="row">
                <div class="col-md-6"><div class="form-group"><label>Factura o correlativo</label><input type="text" class="form-control" id="ndn_factura" placeholder="Buscar número"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Cliente</label><select class="form-control" id="ndn_cliente" style="width:100%"><option></option></select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Registrado por</label><select class="form-control" id="ndn_usuario" style="width:100%"><option></option></select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Estado</label><select class="form-control" id="ndn_estado"><option value="">Todos</option><option value="1">Activo</option><option value="2">Anulado</option></select></div></div>
            </div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" onclick="ndLimpiarFiltrosNotas()"><i class="fa fa-eraser mr-1"></i>Limpiar</button><button type="button" class="btn btn-nd-primary btn-sm" onclick="ndAplicarFiltrosNotas()"><i class="fa fa-search mr-1"></i>Buscar</button></div>
    </div></div>
</div>