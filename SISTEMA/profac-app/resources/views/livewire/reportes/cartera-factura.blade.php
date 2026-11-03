@if($detalleFactura)
    @php
        $facturaModal = $detalleFactura['factura'];
        $saldoModal = $detalleFactura['saldo'];
        $monedaFactura = fn ($valor) => 'L ' . number_format((float) $valor, 2);
    @endphp
    @teleport('body')
    <div class="cb-invoice-backdrop" wire:key="cartera-factura-{{ $facturaModal->id }}"
        x-data="{ copia: false, cargando: true, errorCopia: false }" x-init="$nextTick(() => $refs.cerrar.focus({ preventScroll: true }))"
        @click.self="$wire.cerrarFactura()" @keydown.escape.window="$wire.cerrarFactura()" @wheel.self.prevent @touchmove.self.prevent>
        <section class="cb-invoice" role="dialog" aria-modal="true" aria-labelledby="cb-invoice-title" x-trap.inert.noreturn.noautofocus="true">
            <header @wheel.prevent @touchmove.prevent>
                <div>
                    <h4 id="cb-invoice-title" class="font-weight-bold mb-1">Factura {{ $saldoModal->cai ?: $saldoModal->numero_secuencia_cai }}</h4>
                    <small>ID {{ $facturaModal->id }} · {{ $facturaModal->nombre_cliente ?: $saldoModal->cliente }}</small>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px">
                    <button type="button" class="btn btn-outline-secondary" x-show="copia" x-cloak @click="copia = false">Ver detalle</button>
                    <button type="button" class="btn btn-warning" x-show="!copia" @click="cargando = true; errorCopia = false; copia = true"><i class="fa fa-print" aria-hidden="true"></i> Imprimir copia</button>
                    <button type="button" class="btn btn-primary" wire:click="prepararCorreoFactura" wire:loading.attr="disabled" @disabled($correoEnviado || $etapaCorreo !== '')><i class="fa fa-envelope" aria-hidden="true"></i> Enviar factura por correo</button>
                    <button type="button" class="btn btn-outline-secondary" x-ref="cerrar" wire:click="cerrarFactura" wire:loading.attr="disabled" aria-label="Cerrar detalle de factura">Cerrar</button>
                </div>
            </header>
            <div class="cb-invoice-content">
                @include('livewire.reportes.correo-cobro-form')
                <div x-show="!copia">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Cliente:</strong> {{ $facturaModal->nombre_cliente ?: $saldoModal->cliente }}<br><strong>RTN:</strong> {{ $facturaModal->rtn ?: 'Sin RTN' }}</div>
                        <div class="col-md-6"><strong>Emisión:</strong> {{ $saldoModal->fecha_emision ?: 'Sin fecha' }}<br><strong>Vencimiento:</strong> {{ $saldoModal->fecha_vencimiento ?: 'Sin fecha' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Asesor:</strong> {{ $saldoModal->asesor }}</div>
                        <div class="col-md-4"><strong>Teleasesor:</strong> {{ $saldoModal->teleasesor }}</div>
                        <div class="col-md-4"><strong>Gestor de entrega:</strong> {{ $saldoModal->gestor }}</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead><tr><th>ID producto</th><th>Producto</th><th>Unidad</th><th class="text-right">Cantidad</th><th class="text-right">Precio unitario</th><th class="text-right">Subtotal</th><th class="text-right">ISV</th><th class="text-right">Total</th></tr></thead>
                            <tbody>
                                @forelse($detalleFactura['productos'] as $productoFactura)
                                    <tr>
                                        <td>{{ $productoFactura->producto_id }}</td><td>{{ $productoFactura->nombre ?: 'Producto #' . $productoFactura->producto_id }}</td>
                                        <td>{{ $productoFactura->unidad ?: 'Sin unidad' }}</td><td class="text-right">{{ number_format($productoFactura->cantidad, 2) }}</td>
                                        <td class="text-right text-nowrap">{{ $monedaFactura($productoFactura->precio_unidad) }}</td><td class="text-right text-nowrap">{{ $monedaFactura($productoFactura->subtotal) }}</td>
                                        <td class="text-right text-nowrap">{{ $monedaFactura($productoFactura->isv) }}</td><td class="text-right text-nowrap">{{ $monedaFactura($productoFactura->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">Esta factura no tiene productos registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-right">
                        <div>Subtotal: <strong>{{ $monedaFactura($facturaModal->sub_total) }}</strong></div>
                        <div>ISV: <strong>{{ $monedaFactura($facturaModal->isv) }}</strong></div>
                        <div>Total factura: <strong>{{ $monedaFactura($facturaModal->total) }}</strong></div>
                        <div class="mt-2">Saldo pendiente actual: <strong>{{ $monedaFactura($saldoModal->saldo) }}</strong></div>
                    </div>
                    <small class="text-muted d-block mt-2">El saldo corresponde a la cartera actual, considerando los movimientos registrados; puede ser distinto del total original de la factura.</small>
                </div>
                <div x-show="copia" x-cloak wire:ignore>
                    <p>Usa el botón de impresión del visor PDF para imprimir la copia.
                        <a href="{{ $detalleFactura['copia'] }}" target="_blank" rel="noopener">Abrir copia en otra pestaña</a>
                    </p>
                    <p x-show="cargando" role="status"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Cargando copia de la factura...</p>
                    <p x-show="errorCopia" class="text-danger" x-cloak>No se pudo cargar la copia. Intenta abrirla en otra pestaña.</p>
                    <iframe :src="copia ? @js($detalleFactura['copia']) : null" title="Copia imprimible de la factura"
                        @load="cargando = false" x-on:error="cargando = false; errorCopia = true"></iframe>
                </div>
            </div>
        </section>
    </div>
    @endteleport
@endif
