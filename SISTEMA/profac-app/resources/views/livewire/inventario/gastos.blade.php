<div class="wrapper wrapper-content">
    <div class="ibox">
        <div class="ibox-title d-flex justify-content-between align-items-center">
            <h2>Gastos</h2>
            <a href="{{ url('/producto/compra?modo=gasto') }}" class="btn btn-primary">Nuevo gasto</a>
        </div>
        <div class="ibox-content">
            <p class="text-muted">Gastos no incluidos en el reporte de Compras. No se registran abonos ni movimientos de inventario.</p>
            <label for="buscarGasto">Buscar por factura o proveedor</label>
            <input id="buscarGasto" type="search" class="form-control mb-3" wire:model.live.debounce.400ms="buscar">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Factura</th><th>Proveedor</th><th>Emisión</th><th>Total</th><th>Estado</th><th>Documento</th></tr></thead>
                    <tbody>
                    @forelse ($gastos as $gasto)
                        <tr wire:key="gasto-{{ $gasto->id }}">
                            <td>G-{{ $gasto->id }}</td><td>{{ $gasto->numero_factura }}</td><td>{{ $gasto->proveedor }}</td>
                            <td>{{ $gasto->fecha_emision }}</td><td>L. {{ number_format($gasto->total, 2) }}</td>
                            <td>{{ $gasto->estado === 'anulado' ? 'Anulado' : 'Registrado' }}</td>
                            <td><a class="btn btn-sm btn-primary" href="{{ url('/gastos/imprimir/'.$gasto->id) }}" target="_blank" rel="noopener">Imprimir gasto</a></td>
                        </tr>
                    @empty <tr><td colspan="7">No hay gastos registrados.</td></tr> @endforelse
                    </tbody>
                </table>
            </div>
            {{ $gastos->links() }}
        </div>
    </div>
</div>
