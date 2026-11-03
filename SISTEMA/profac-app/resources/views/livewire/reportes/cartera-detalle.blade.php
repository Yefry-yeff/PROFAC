<div class="cb-panel mb-3" id="cb-detalle"
    wire:key="detalle-local-{{ md5(json_encode($detalleCompleto)) }}-{{ $orden }}"
    x-data="{
        filas: @js($detalleCompleto), pagina: 1, orden: @js($orden), porPagina: 20,
        get paginas() { return Math.max(1, Math.ceil(this.filas.length / this.porPagina)); },
        get visibles() { return this.filas.slice((this.pagina - 1) * this.porPagina, this.pagina * this.porPagina); },
        get enlaces() {
            const numeros = new Set([1, this.paginas]);
            for (let i = Math.max(1, this.pagina - 2); i &lt;= Math.min(this.paginas, this.pagina + 2); i++) numeros.add(i);
            return [...numeros].sort((a, b) => a - b);
        },
        cambiar(pagina) { this.pagina = Math.max(1, Math.min(this.paginas, pagina)); this.$refs.tabla.scrollTop = 0; },
        ordenar() {
            const campo = this.orden === 'mora' ? 'dias' : (this.orden === 'antiguedad' ? 'antiguedad' : 'centavos');
            this.filas.sort((a, b) => (b[campo] ?? -Number.MAX_VALUE) - (a[campo] ?? -Number.MAX_VALUE));
            this.cambiar(1);
        },
        dinero(valor) { return 'L ' + (valor / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
        mora(fila) { return fila.dias === null ? 'Sin vencimiento' : (fila.dias > 0 ? fila.dias + ' días' : 'Por vencer'); }
    }">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h4 class="font-weight-bold mb-1">Detalle de cartera pendiente</h4><small>{{ count($detalleCompleto) }} facturas · Las mismas selecciones de los gráficos</small><small class="d-block text-muted">Paginación y ordenamiento sobre los datos cargados. Usa Actualizar para consultar cambios recientes.</small><small class="d-block text-muted">Haz clic en la factura para ver sus productos e imprimir una copia.</small></div>
        <button class="btn btn-sm btn-outline-secondary" wire:click="descargar('detalle')">Excel · todas las páginas</button>
        <div>
            <label for="cb-orden" class="small">Ordenar por</label>
            <select id="cb-orden" class="form-control form-control-sm" x-model="orden" @change="ordenar()">
                <option value="saldo">Mayor saldo</option><option value="mora">Mayor mora</option><option value="antiguedad">Emisión más antigua</option>
            </select>
        </div>
    </div>
    <div class="small mb-2 d-flex flex-wrap" style="gap:6px" aria-label="Leyenda de riesgo del saldo y mora">
        <span class="cb-risk cb-risk-current">Por vencer (incluye hoy)</span>
        <span class="cb-risk cb-risk-warning">1–30 días</span>
        <span class="cb-risk cb-risk-overdue">31–90 días</span>
        <span class="cb-risk cb-risk-critical">Más de 90 días</span>
        <span class="cb-risk cb-risk-unknown">Sin vencimiento</span>
    </div>
    <div class="table-responsive cb-table-scroll" x-ref="tabla">
        <table class="table table-hover table-sm cb-detail">
            <thead><tr><th>Cliente</th><th>Factura / CAI</th><th>Emisión</th><th>Vencimiento</th><th class="text-right">Saldo</th><th>Mora</th><th>Antigüedad</th><th>Asesor</th><th>Teleasesor</th><th>Gestor de entrega</th></tr></thead>
            <tbody>
                <template x-for="fila in visibles" :key="fila.id">
                    <tr :data-factura="fila.id">
                        <td><button class="cb-link" @click="$wire.seleccionar('cliente', String(fila.cliente_id))" x-text="fila.cliente"></button><small class="d-block text-muted" x-text="'#' + fila.cliente_id"></small></td>
                        <td><button type="button" class="cb-link" @click="$wire.abrirFactura(fila.id)" :aria-label="'Ver detalle de factura ' + (fila.cai || fila.numero_secuencia_cai)" x-text="fila.cai || fila.numero_secuencia_cai"></button><small class="d-block text-muted" x-text="'ID ' + fila.id"></small></td>
                        <td x-text="fila.fecha_emision || 'Sin fecha'"></td>
                        <td x-text="fila.fecha_vencimiento || 'Sin fecha'"></td>
                        <td class="text-right font-weight-bold text-nowrap"><span class="cb-risk" :class="'cb-risk-' + fila.riesgo" :title="'Riesgo por vencimiento: ' + mora(fila)" x-text="dinero(fila.centavos)"></span></td>
                        <td><button class="cb-risk text-nowrap" :class="'cb-risk-' + fila.riesgo" @click="$wire.seleccionar('tramo', fila.tramo)" x-text="mora(fila)"></button></td>
                        <td x-text="fila.antiguedad !== null ? fila.antiguedad + ' días' : 'Sin fecha'"></td>
                        @foreach(['asesor', 'teleasesor', 'gestor'] as $campo)
                            <td><button class="cb-link" @click="$wire.seleccionar('{{ $campo }}', String(fila.{{ $campo }}_id))" x-text="fila.{{ $campo }}"></button></td>
                        @endforeach
                    </tr>
                </template>
                <tr x-show="!filas.length"><td colspan="10" class="text-center py-4">No hay deuda pendiente con los filtros seleccionados. Limpia los filtros para consultar toda la cartera.</td></tr>
            </tbody>
            <tfoot><tr><th colspan="4">Total de la selección (todas las páginas)</th><th class="text-right text-nowrap">{{ $dinero($metricas['total']) }}</th><th colspan="5"></th></tr></tfoot>
        </table>
    </div>
    <nav class="d-flex flex-wrap justify-content-between align-items-center mt-2" aria-label="Páginas del detalle de cartera">
        <span class="small text-muted" aria-live="polite" x-text="filas.length ? 'Mostrando ' + ((pagina - 1) * porPagina + 1) + ' a ' + Math.min(pagina * porPagina, filas.length) + ' de ' + filas.length : '0 registros'"></span>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="cambiar(pagina - 1)" :disabled="pagina === 1" aria-label="Página anterior">‹</button>
            <template x-for="(numero, indice) in enlaces" :key="numero">
                <span class="d-inline-flex">
                    <span class="px-2 align-self-center" x-show="indice > 0 &amp;&amp; numero - enlaces[indice - 1] > 1">…</span>
                    <button type="button" class="btn btn-sm" :class="numero === pagina ? 'btn-warning' : 'btn-outline-secondary'" @click="cambiar(numero)" :aria-current="numero === pagina ? 'page' : null" :aria-label="'Página ' + numero" x-text="numero"></button>
                </span>
            </template>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="cambiar(pagina + 1)" :disabled="pagina === paginas" aria-label="Página siguiente">›</button>
        </div>
    </nav>
</div>
