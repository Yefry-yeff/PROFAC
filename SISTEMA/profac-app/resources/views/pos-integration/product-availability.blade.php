    <div class="table-wrap">
        <p class="text-muted mb-3">
            Solo consulta: no suma al inventario de Valencia ni se utiliza para ventas o reservas.
            La cantidad está expresada en la unidad de medida indicada.
        </p>
        @if($inventarioPaperland->isEmpty())
            <p class="text-muted">Sin inventario espejo recibido para este producto.</p>
        @endif
        <div class="table-responsive">
            <table id="tbl_paperland_disponibilidad" class="table table-hover" style="width:100%">
                <thead><tr>
                    <th>Cód. Producto Paperland</th><th>Nombre</th><th>Bodega</th><th>Unidad de medida</th><th>Cantidad</th>
                </tr></thead>
                <tbody>
                    @foreach($inventarioPaperland as $mirror)
                        @php($data = $mirror['data'])
                        @forelse($data['presentations'] as $presentation)
                            <tr>
                                <td>{{ $data['product_pos_id'] }}</td>
                                <td>{{ $data['name'] }}</td>
                                <td>Paperland · Tienda {{ $data['store_id'] }}</td>
                                <td>{{ $presentation['name'] }}</td>
                                <td>{{ $presentation['quantity'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>{{ $data['product_pos_id'] }}</td>
                                <td>{{ $data['name'] }}</td>
                                <td>Paperland · Tienda {{ $data['store_id'] }}</td>
                                <td>Unidades base</td>
                                <td>{{ $data['physical_base'] ?? 'Pendiente de regularizar' }}</td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>
        </div>
</div>
