<div class="wrapper wrapper-content">
    <div class="ibox"><div class="ibox-title">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="paperland-entrantes-link" data-toggle="tab"
                href="#paperland-entrantes" role="tab" aria-controls="paperland-entrantes" aria-selected="true"><i class="fa fa-arrow-down mr-2"></i> Traslados entrantes Paperland</a></li>
            <li class="nav-item"><a class="nav-link" id="paperland-enviados-link" data-toggle="tab"
                href="#paperland-enviados" role="tab" aria-controls="paperland-enviados" aria-selected="false"><i class="fa fa-arrow-up mr-2"></i> Traslados enviados a Paperland</a></li>
        </ul>
    </div>
        <div class="ibox-content">
            <div class="tab-content">
            <div class="tab-pane show active" id="paperland-entrantes" role="tabpanel" aria-labelledby="paperland-entrantes-link">
            <p class="history-note"><i class="fa fa-info-circle mr-1"></i> Los pendientes se muestran independientemente del rango de fechas. No son compras.</p>
            <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Referencia</th><th>Fecha</th><th>Estado</th><th>Opciones</th></tr></thead><tbody id="pos-transfers"></tbody></table></div>
            </div>
            <div class="tab-pane" id="paperland-enviados" role="tabpanel" aria-labelledby="paperland-enviados-link">
            <p class="history-note"><i class="fa fa-info-circle mr-1"></i> Un rechazo no devuelve stock. Confirme el retorno solo después de recibir físicamente los productos.</p>
            <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Referencia</th><th>Fecha</th><th>Estado</th><th>Motivo</th><th>Opciones</th></tr></thead><tbody id="pos-outgoing"></tbody></table></div>
            </div>
            </div>
            <p id="pos-error" class="text-danger" role="alert"></p>
        </div>
    </div>
</div>
<div class="modal fade" id="pos-distribution" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
        <div class="modal-header"><h4>Distribuir traslado Paperland</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body"><p>Seleccione presentación y ubicación para cada reparto. El total debe coincidir exactamente con las unidades base.</p><div id="pos-lines"></div><p id="pos-modal-error" class="text-danger" role="alert"></p></div>
        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button><button type="button" id="pos-approve" class="btn btn-primary">Aprobar recepción</button></div>
    </div></div>
</div>
@push('scripts')
<script>
$(function () {
    const canWrite = @json(auth()->user()->tienePermiso('inventario/translado'));
    const csrf = @json(csrf_token());
    let activeUuid = null, detail = null;
    const text = (tag, value) => { const el = document.createElement(tag); el.textContent = value == null ? '—' : String(value); return el; };
    const option = (select, value, label) => { const el = text('option', label); el.value = value; select.appendChild(el); };
    const printLink = transfer => {
        const link = text('a', 'Imprimir');
        const icon = text('i', ''); icon.className = 'fa fa-print mr-1'; link.prepend(icon);
        link.className = 'btn btn-sm btn-warning';
        link.href = '/translados/pos/' + encodeURIComponent(transfer.transfer_uuid) + '/imprimir';
        link.target = '_blank'; link.rel = 'noopener';
        return link;
    };
    const statusBadge = status => {
        const labels = {pending: 'Pendiente', dispatched: 'Enviado', received: 'Recibido', rejected: 'Rechazado', returned: 'Devuelto'};
        const known = Object.prototype.hasOwnProperty.call(labels, status);
        const badge = text('span', known ? labels[status] : status);
        badge.className = 'history-status history-status-' + (known ? status : 'other');
        const cell = document.createElement('td'); cell.appendChild(badge);
        return cell;
    };
    async function request(url, body) {
        const response = await fetch(url, {method: body ? 'POST' : 'GET', headers: {'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf}, body: body ? JSON.stringify(body) : undefined});
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || result.error || 'No se pudo completar la operación.');
        return result;
    }
    async function refresh() {
        try {
            const result = await request('/translados/pos');
            const transfers = document.getElementById('pos-transfers'); transfers.replaceChildren();
            result.transfers.forEach(t => {
                const row = document.createElement('tr');
                [t.reference, t.created_at].forEach(v => row.appendChild(text('td', v)));
                row.appendChild(statusBadge(t.status));
                const actions = document.createElement('td');
                actions.appendChild(printLink(t));
                if (canWrite && t.status === 'pending') {
                    const approve = text('button', ' Distribuir / Aprobar'); approve.className = 'btn btn-sm btn-primary ml-2'; approve.onclick = () => open(t.transfer_uuid); actions.appendChild(approve);
                    const reject = text('button', 'Rechazar'); reject.className = 'btn btn-sm btn-danger ml-2';
                    reject.onclick = async () => { const reason = prompt('Motivo del rechazo (5 a 400 caracteres):'); if (reason === null) return; try { await request('/translados/pos/' + t.transfer_uuid + '/rechazar', {reason}); await refresh(); } catch (e) { alert(e.message); } };
                    actions.appendChild(reject);
                }
                row.appendChild(actions); transfers.appendChild(row);
            });
            const outgoing = document.getElementById('pos-outgoing'); outgoing.replaceChildren();
            result.outgoing.forEach(t => {
                const row = document.createElement('tr');
                [t.reference, t.created_at].forEach(v => row.appendChild(text('td', v)));
                row.appendChild(statusBadge(t.status));
                row.appendChild(text('td', t.reason));
                const actions = document.createElement('td');
                actions.appendChild(printLink(t));
                if (canWrite && t.status === 'rejected') {
                    const button = text('button', 'Confirmar retorno físico'); button.className = 'btn btn-sm btn-warning ml-2';
                    button.onclick = async () => {
                        if (!confirm('¿Confirmó físicamente el retorno completo? Se devolverán los lotes a su origen.')) return;
                        button.disabled = true;
                        try { await request('/translados/pos/' + t.transfer_uuid + '/retorno', {}); await refresh(); }
                        catch (e) { alert(e.message); button.disabled = false; }
                    };
                    actions.appendChild(button);
                }
                row.appendChild(actions); outgoing.appendChild(row);
            });
            document.getElementById('pos-error').textContent = '';
        } catch (e) { document.getElementById('pos-error').textContent = e.message; }
    }
    function addAllocation(line, container) {
        const row = document.createElement('div'); row.className = 'border p-2 mb-2 pos-allocation'; row.dataset.line = line.line_uuid;
        const presentation = document.createElement('select'); presentation.className = 'form-control mb-1 pos-presentation';
        option(presentation, '', 'Seleccione presentación');
        detail.presentations.filter(p => Number(p.producto_id) === Number(line.product_profac_id)).forEach(p => option(presentation, p.id, p.nombre + ' × ' + p.unidad_venta));
        const quantity = document.createElement('input'); quantity.type = 'number'; quantity.min = '1'; quantity.step = '1'; quantity.placeholder = 'Cantidad de presentaciones'; quantity.className = 'form-control mb-1 pos-quantity';
        const warehouse = document.createElement('select'), segment = document.createElement('select'), section = document.createElement('select');
        warehouse.className = 'form-control mb-1 pos-warehouse'; segment.className = 'form-control mb-1 pos-segment'; section.className = 'form-control mb-1 pos-section';
        function unique(items, id, label, select, prompt) { select.replaceChildren(); option(select, '', prompt); const seen = new Set(); items.forEach(item => { if (!seen.has(item[id])) { seen.add(item[id]); option(select, item[id], item[label]); } }); }
        unique(detail.locations, 'bodega_id', 'bodega', warehouse, 'Seleccione bodega');
        function segments() { unique(detail.locations.filter(x => String(x.bodega_id) === warehouse.value), 'segmento_id', 'segmento', segment, 'Seleccione segmento'); sections(); }
        function sections() { unique(detail.locations.filter(x => String(x.bodega_id) === warehouse.value && String(x.segmento_id) === segment.value), 'seccion_id', 'seccion', section, 'Seleccione sección'); }
        warehouse.onchange = segments; segment.onchange = sections; segments();
        const remove = text('button', 'Quitar reparto'); remove.type = 'button'; remove.className = 'btn btn-sm btn-default'; remove.onclick = () => row.remove();
        row.append(presentation, quantity, warehouse, segment, section, remove); container.appendChild(row);
    }
    async function open(uuid) {
        try {
            detail = await request('/translados/pos/' + uuid); activeUuid = uuid;
            const target = document.getElementById('pos-lines'); target.replaceChildren();
            detail.data.lines.forEach(line => {
                const group = document.createElement('div'); group.className = 'mb-3';
                group.appendChild(text('strong', line.name + ' · ' + line.quantity_base + ' unidades base · lote ' + line.source_lot_id + ' · vence ' + (line.expires_at || '—')));
                const allocations = document.createElement('div'); group.appendChild(allocations); addAllocation(line, allocations);
                const add = text('button', 'Añadir reparto'); add.type = 'button'; add.className = 'btn btn-sm btn-info'; add.onclick = () => addAllocation(line, allocations); group.appendChild(add); target.appendChild(group);
            });
            document.getElementById('pos-modal-error').textContent = ''; $('#pos-distribution').modal('show');
        } catch (e) { alert(e.message); }
    }
    document.getElementById('pos-approve').onclick = async function () {
        const allocations = Array.from(document.querySelectorAll('.pos-allocation')).map(row => ({
            line_uuid: row.dataset.line, presentation_id: row.querySelector('.pos-presentation').value,
            quantity: row.querySelector('.pos-quantity').value, bodega_id: row.querySelector('.pos-warehouse').value,
            segmento_id: row.querySelector('.pos-segment').value, seccion_id: row.querySelector('.pos-section').value
        }));
        this.disabled = true;
        try { await request('/translados/pos/' + activeUuid + '/aprobar', {allocations}); $('#pos-distribution').modal('hide'); await refresh(); }
        catch (e) { document.getElementById('pos-modal-error').textContent = e.message; }
        finally { this.disabled = false; }
    };
    refresh();
});
</script>
@endpush
