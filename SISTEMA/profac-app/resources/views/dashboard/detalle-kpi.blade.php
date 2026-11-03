@if($detalleKpi !== '' && $detalleFilas)
    @php
        $tituloDetalle = match($detalleKpi) {
            'facturas' => 'Facturas emitidas',
            'clientes' => 'Clientes activos desde el inicio hasta hoy',
            'clientes_nuevos' => 'Clientes creados desde el inicio hasta hoy',
            'inventario' => 'Productos bajos de inventario',
            'sin_imagen' => 'Productos sin imagen',
            'poco_movimiento' => 'Productos con poco movimiento',
            'atencion' => 'Clientes que requieren atención',
        };
    @endphp
    @teleport('body')
    <div class="dash-modal-backdrop {{ $darkMode ? 'dash-dark' : '' }}" x-data x-init="$nextTick(() => $refs.cerrar.focus())"
        @keydown.escape.window="$wire.cerrarDetalle()" wire:key="detalle-kpi-{{ $detalleKpi }}" @click.self="$wire.cerrarDetalle()">
        <section class="dash-modal" role="dialog" aria-modal="true" aria-labelledby="dash-modal-title" x-trap.inert.noscroll="true">
            <header class="dash-modal-header">
                <div>
                    <h3 id="dash-modal-title">{{ $tituloDetalle }}</h3>
                    <p>{{ match($detalleKpi) {
                        'inventario' => 'Estado actual · Productos activos con 0 a menos de 10 unidades. No depende del período.',
                        'sin_imagen' => 'Estado actual · Solo productos activos sin imagen registrada. No depende del período seleccionado.',
                        'poco_movimiento' => 'Sin ventas activas desde ' . now()->startOfDay()->subDays(90)->format('d/m/Y') . ' hasta hoy. Incluye productos nunca vendidos. Existencia actual de todas las bodegas.',
                        'clientes', 'clientes_nuevos' => 'Acumulado desde el inicio del sistema hasta ' . now()->format('d/m/Y') . '. No depende del período seleccionado.',
                        'atencion' => 'Historial hasta hoy: sin compra por más de 90 días o caída de al menos 20% del mes actual (en curso) frente al mes anterior completo. No depende del período seleccionado.',
                        default => $periodoLabel,
                    } }}</p>
                </div>
                <button type="button" x-ref="cerrar" wire:click="cerrarDetalle" class="dash-btn" aria-label="Cerrar detalle"><i class="fa fa-times" aria-hidden="true"></i></button>
            </header>
            <div class="dash-modal-toolbar">
                <label for="dash-detalle-buscar">Buscar en todos los registros</label>
                <input id="dash-detalle-buscar" type="search" wire:model.live.debounce.350ms="buscarDetalle" placeholder="Nombre, código o número" class="form-control">
                <span>{{ number_format($detalleFilas->total()) }} registros</span>
                <button type="button" wire:click="descargarDetalle" wire:loading.attr="disabled" class="dash-btn dash-btn-export"><i class="fa fa-file-excel" aria-hidden="true"></i> Descargar Excel</button>
                <small>Exporta todos los resultados de la búsqueda, no solo esta página.</small>
                <span wire:loading wire:target="abrirDetalle,buscarDetalle,gotoPage,nextPage,previousPage,descargarDetalle" role="status"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Cargando...</span>
            </div>
            @if($detalleKpi === 'poco_movimiento')
                <p class="px-4 text-muted">La existencia puede provenir de ingresos por ajuste sin compra vinculada. Estos ingresos se identifican por separado y su fecha no se presenta como fecha de compra.</p>
            @endif
            <div class="dash-modal-table">
                <table class="dash-table">
                    <thead>
                        @if($detalleKpi === 'facturas')
                            <tr><th>ID</th><th>Secuencia CAI</th><th>Fecha de emisión</th><th>Cliente</th><th>Asesor</th><th>Total</th></tr>
                        @elseif($detalleKpi === 'inventario')
                            <tr><th>ID</th><th>Código</th><th>Producto</th><th>Existencia actual</th></tr>
                        @elseif($detalleKpi === 'sin_imagen')
                            <tr><th>ID</th><th>Código</th><th>Producto</th></tr>
                        @elseif($detalleKpi === 'poco_movimiento')
                            <tr><th>ID</th><th>Nombre</th><th>Fecha de última compra</th><th>Existencia</th><th>Fecha de última venta</th><th>Existencia sin compra vinculada</th><th>Último ingreso sin compra</th></tr>
                        @elseif($detalleKpi === 'atencion')
                            <tr><th>ID</th><th>Cliente</th><th>Asesor</th><th>Última compra del cliente</th><th>Comprado mes actual</th><th>Comprado mes anterior</th><th>Variación</th><th>Motivo</th></tr>
                        @else
                            <tr><th>ID</th><th>Cliente</th><th>Fecha de creación</th><th>Asesor asignado</th></tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse($detalleFilas as $filaDetalle)
                            <tr wire:key="detalle-{{ $detalleKpi }}-{{ $filaDetalle->id }}">
                                <td>{{ $filaDetalle->id }}</td>
                                @if($detalleKpi === 'facturas')
                                    <td>{{ $filaDetalle->cai ?: 'Sin secuencia CAI' }}</td><td>{{ $filaDetalle->fecha_emision }}</td>
                                    <td>{{ $filaDetalle->cliente ?? 'Sin cliente' }}</td><td>{{ $filaDetalle->vendedor ?? 'Sin asignar' }}</td><td>L. {{ number_format($filaDetalle->total, 2) }}</td>
                                @elseif($detalleKpi === 'inventario')
                                    <td>{{ $filaDetalle->codigo_barra ?: 'Sin código' }}</td><td>{{ $filaDetalle->nombre }}</td><td><strong>{{ number_format($filaDetalle->stock, 2) }}</strong></td>
                                @elseif($detalleKpi === 'sin_imagen')
                                    <td>{{ $filaDetalle->codigo_barra ?: 'Sin código' }}</td><td>{{ $filaDetalle->nombre }}</td>
                                @elseif($detalleKpi === 'poco_movimiento')
                                    <td>{{ $filaDetalle->nombre }}</td>
                                    <td>{{ $filaDetalle->ultima_compra ? \Carbon\Carbon::parse($filaDetalle->ultima_compra)->format('d/m/Y') : 'Sin compra vinculada' }}</td>
                                    <td>{{ number_format($filaDetalle->existencia, 2) }}</td>
                                    <td>{{ $filaDetalle->ultima_venta ? \Carbon\Carbon::parse($filaDetalle->ultima_venta)->format('d/m/Y') : 'Sin ventas registradas' }}</td>
                                    <td>{{ number_format($filaDetalle->existencia_sin_compra, 2) }}</td>
                                    <td>{{ $filaDetalle->ultimo_ingreso_sin_compra ? \Carbon\Carbon::parse($filaDetalle->ultimo_ingreso_sin_compra)->format('d/m/Y') : 'Sin ingreso registrado' }}</td>
                                @elseif($detalleKpi === 'atencion')
                                    <td>{{ $filaDetalle->nombre }}</td><td>{{ $filaDetalle->vendedor ?? 'Sin asignar' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($filaDetalle->ultima_venta)->format('d/m/Y') }}</td>
                                    <td>L. {{ number_format($filaDetalle->actual, 2) }}</td><td>L. {{ number_format($filaDetalle->anterior, 2) }}</td>
                                    <td>{{ $filaDetalle->anterior > 0 ? number_format(($filaDetalle->actual - $filaDetalle->anterior) / $filaDetalle->anterior * 100, 2) . '%' : 'Sin base' }}</td>
                                    <td>{{ $this->motivoAtencion($filaDetalle->ultima_venta, (float) $filaDetalle->actual, (float) $filaDetalle->anterior) }}</td>
                                @else
                                    <td>{{ $filaDetalle->nombre }}</td><td>{{ $filaDetalle->created_at }}</td><td>{{ $filaDetalle->vendedor ?? 'Sin asignar' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ match($detalleKpi) { 'facturas' => 6, 'poco_movimiento' => 7, 'atencion' => 8, 'sin_imagen' => 3, default => 4 } }}" class="text-center">No hay registros con estos criterios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="dash-modal-footer">
                {{ $detalleFilas->links(data: ['scrollTo' => false]) }}
                <button type="button" wire:click="cerrarDetalle" class="dash-btn">Cerrar</button>
            </footer>
        </section>
    </div>
    @endteleport
@endif
