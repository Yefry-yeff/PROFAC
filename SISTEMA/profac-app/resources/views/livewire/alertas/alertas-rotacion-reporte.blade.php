<div>
<style>
.arr-header {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 60%, #059669 100%);
    padding: 24px 32px 20px;
    color: #fff;
    border-radius: 16px 16px 0 0;
    position: relative;
    overflow: hidden;
}
.arr-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.arr-body {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 16px 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,.07);
    padding: 24px 28px;
}
.arr-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 20px;
}
.arr-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
    padding: 10px 14px;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
}
.arr-table th:hover { background: #f1f5f9; }
.arr-table td {
    padding: 11px 14px;
    font-size: 13px;
    color: #374151;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.arr-table tbody tr:hover { background: #fafffe; }
.arr-table tbody tr:last-child td { border-bottom: none; }
.arr-search {
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    padding: 8px 14px 8px 36px;
    font-size: 13px;
    width: 100%;
    outline: none;
    transition: border-color .2s;
}
.arr-search:focus { border-color: #059669; }
</style>

<div style="max-width:1200px; margin:0 auto; padding:28px 16px;">

    {{-- Cabecera --}}
    <div class="arr-header" style="margin-bottom:0;">
        <div style="position:relative; z-index:1;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:14px;">
                <div>
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                        <a href="{{ url()->previous() }}"
                           style="background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.35); color:#fff; border-radius:8px; padding:5px 12px; font-size:12px; text-decoration:none;">
                            <i class="fa fa-arrow-left mr-1"></i> Volver
                        </a>
                        <span style="opacity:.5; font-size:13px;">›</span>
                        <span style="font-size:12px; opacity:.8;">Informe de alerta</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fa {{ $regla->icono }}" style="font-size:20px; color:#fff;"></i>
                        </div>
                        <div>
                            <h2 style="margin:0; font-size:20px; font-weight:900; letter-spacing:-.2px;">{{ $regla->nombre }}</h2>
                            <p style="margin:4px 0 0; font-size:12px; opacity:.8;">{{ $regla->descripcion_parametro }}</p>
                        </div>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                        <span class="arr-badge" style="background:{{ $regla->prioridad_color }}25; color:#fff; border:1.5px solid rgba(255,255,255,.4);">
                            <i class="fa fa-flag"></i> {{ $regla->prioridad_label }}
                        </span>
                        <div style="font-size:26px; font-weight:900; line-height:1;">{{ count($productosFiltrados) }}</div>
                        <div style="font-size:11px; opacity:.8;">producto(s) afectado(s)</div>
                        <button wire:click="descargarExcel" wire:loading.attr="disabled" wire:target="descargarExcel"
                                style="margin-top:4px; background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.5); color:#fff; border-radius:9px; padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer; backdrop-filter:blur(4px);">
                            <span wire:loading.remove wire:target="descargarExcel"><i class="fa fa-file-excel-o mr-1"></i> Descargar Excel</span>
                            <span wire:loading wire:target="descargarExcel"><i class="fa fa-spinner fa-spin mr-1"></i> Generando…</span>
                        </button>
                    </div>
            </div>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div class="arr-body">

        {{-- Buscador --}}
        <div style="position:relative; margin-bottom:20px; max-width:340px;">
            <i class="fa fa-search" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="text" wire:model="busqueda" class="arr-search" placeholder="Buscar producto…">
        </div>

        {{-- Tabla según tipo --}}
        @if(count($productosFiltrados) > 0)
            <div style="border:1.5px solid #e2e8f0; border-radius:12px; overflow:hidden; overflow-x:auto;">
                <table class="table table-borderless arr-table mb-0">
                    <thead>
                        <tr>
                            <th wire:click="ordenar('codigo_barra')" style="min-width:110px;">
                                Código
                                @if($ordenCampo === 'codigo_barra')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>
                            <th wire:click="ordenar('producto_nombre')" style="min-width:200px;">
                                Producto
                                @if($ordenCampo === 'producto_nombre')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>
                            <th wire:click="ordenar('sub_categoria')">
                                Subcategoría
                                @if($ordenCampo === 'sub_categoria')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>
                            <th wire:click="ordenar('stock_actual')">
                                Stock
                                @if($ordenCampo === 'stock_actual')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>
                            <th wire:click="ordenar('precio_base')">
                                Precio base
                                @if($ordenCampo === 'precio_base')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>
                            <th wire:click="ordenar('ultimo_costo_compra')">
                                Último costo
                                @if($ordenCampo === 'ultimo_costo_compra')<i class="fa fa-sort-{{ $ordenDir === 'asc' ? 'asc' : 'desc' }} ml-1"></i>@else<i class="fa fa-sort ml-1" style="opacity:.35;"></i>@endif
                            </th>

                            {{-- Columnas extra según tipo --}}
                            @if($regla->tipo === 'recuperacion_proxima')
                                <th wire:click="ordenar('ultima_compra')">Última compra @if($ordenCampo==='ultima_compra')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th wire:click="ordenar('fecha_limite')">Fecha límite @if($ordenCampo==='fecha_limite')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th>T. recuperación</th>
                            @elseif($regla->tipo === 'recuperacion_vencida')
                                <th wire:click="ordenar('fecha_limite')">Fecha límite @if($ordenCampo==='fecha_limite')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th wire:click="ordenar('dias_vencido')">Días vencido @if($ordenCampo==='dias_vencido')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                            @elseif($regla->tipo === 'sin_ventas')
                                <th wire:click="ordenar('ultima_venta')">Última venta @if($ordenCampo==='ultima_venta')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                            @elseif($regla->tipo === 'baja_rotacion')
                                <th wire:click="ordenar('ventas_60d')">Ventas 60 días @if($ordenCampo==='ventas_60d')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th>Umbral mín.</th>
                            @elseif($regla->tipo === 'sobreinventario')
                                <th wire:click="ordenar('cobertura_meses')">Cobertura (meses) @if($ordenCampo==='cobertura_meses')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th wire:click="ordenar('prom_mensual')">Prom. mensual @if($ordenCampo==='prom_mensual')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th>Límite config.</th>
                            @elseif($regla->tipo === 'incremento_demanda')
                                <th wire:click="ordenar('ventas_30d')">Ventas 30d @if($ordenCampo==='ventas_30d')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th wire:click="ordenar('ventas_30d_ant')">Período anterior @if($ordenCampo==='ventas_30d_ant')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                                <th wire:click="ordenar('pct_crecimiento')">Crecimiento % @if($ordenCampo==='pct_crecimiento')<i class="fa fa-sort-{{ $ordenDir==='asc'?'asc':'desc' }} ml-1"></i>@endif</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productosFiltrados as $p)
                            <tr wire:key="p-{{ $p['producto_id'] }}">
                                <td style="font-size:12px; color:#64748b; font-family:monospace;">{{ $p['codigo_barra'] ?? '—' }}</td>
                                <td>
                                    <span style="font-weight:700; color:#0f172a;">{{ $p['producto_nombre'] }}</span>
                                    @if(!empty($p['sub_categoria']) && $p['sub_categoria'] !== 'Sin categoría')
                                        <span style="display:block; font-size:10px; color:#94a3b8;">{{ $p['sub_categoria'] }}</span>
                                    @endif
                                </td>
                                <td style="font-size:12px; color:#64748b;">{{ $p['sub_categoria'] ?? '—' }}</td>
                                <td>
                                    <span style="font-weight:600;">{{ number_format($p['stock_actual'] ?? 0, 0) }}</span>
                                    <span style="font-size:11px; color:#94a3b8;"> uds</span>
                                </td>
                                <td style="font-size:12px;">L. {{ number_format($p['precio_base'] ?? 0, 2) }}</td>
                                <td style="font-size:12px; color:#64748b;">L. {{ number_format($p['ultimo_costo_compra'] ?? 0, 2) }}</td>

                                @if($regla->tipo === 'recuperacion_proxima')
                                    <td>{{ $p['ultima_compra'] ? \Carbon\Carbon::parse($p['ultima_compra'])->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        <span style="color:#d97706; font-weight:700;">
                                            {{ $p['fecha_limite'] ? \Carbon\Carbon::parse($p['fecha_limite'])->format('d/m/Y') : '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $p['tiempo_recuperacion_meses'] ?? '—' }} mes(es)</td>

                                @elseif($regla->tipo === 'recuperacion_vencida')
                                    <td>
                                        <span style="color:#ef4444; font-weight:700;">
                                            {{ $p['fecha_limite'] ? \Carbon\Carbon::parse($p['fecha_limite'])->format('d/m/Y') : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700;">
                                            {{ $p['dias_vencido'] ?? 0 }} días
                                        </span>
                                    </td>

                                @elseif($regla->tipo === 'sin_ventas')
                                    <td>
                                        @if($p['ultima_venta'])
                                            {{ \Carbon\Carbon::parse($p['ultima_venta'])->format('d/m/Y') }}
                                            <span style="font-size:11px; color:#94a3b8; display:block;">
                                                hace {{ \Carbon\Carbon::parse($p['ultima_venta'])->diffForHumans() }}
                                            </span>
                                        @else
                                            <span style="color:#94a3b8; font-style:italic;">Sin ventas registradas</span>
                                        @endif
                                    </td>

                                @elseif($regla->tipo === 'baja_rotacion')
                                    <td>
                                        <span style="color:#f97316; font-weight:700;">{{ number_format($p['ventas_60d'] ?? 0, 0) }} uds</span>
                                    </td>
                                    <td style="color:#94a3b8;">{{ number_format($regla->parametro_umbral, 0) }} uds</td>

                                @elseif($regla->tipo === 'sobreinventario')
                                    @php $cob = $p['cobertura_meses'] ?? 0; @endphp
                                    <td>
                                        <span style="color:{{ $cob >= ($regla->parametro_umbral * 2) ? '#ef4444' : '#f97316' }}; font-weight:700;">
                                            {{ $cob >= 9000 ? '∞' : number_format($cob, 1) }} meses
                                        </span>
                                    </td>
                                    <td>{{ number_format($p['prom_mensual'] ?? 0, 0) }} uds/mes</td>
                                    <td style="color:#94a3b8;">{{ number_format($regla->parametro_umbral, 1) }} meses</td>

                                @elseif($regla->tipo === 'incremento_demanda')
                                    <td style="color:#059669; font-weight:700;">{{ number_format($p['ventas_30d'] ?? 0, 0) }} uds</td>
                                    <td>{{ number_format($p['ventas_30d_ant'] ?? 0, 0) }} uds</td>
                                    <td>
                                        <span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700;">
                                            +{{ number_format($p['pct_crecimiento'] ?? 0, 1) }}%
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:12px; font-size:11px; color:#94a3b8; text-align:right;">
                Mostrando {{ count($productosFiltrados) }} producto(s) · Generado al cargar la página
            </div>

        @else
            <div style="text-align:center; padding:48px 24px; border:2px dashed #d1fae5; border-radius:12px; background:#f0fdf4;">
                <i class="fa fa-check-circle" style="font-size:40px; color:#a7f3d0; display:block; margin-bottom:12px;"></i>
                <p style="font-size:15px; font-weight:700; color:#065f46; margin-bottom:4px;">Sin productos afectados</p>
                @if($busqueda !== '')
                    <p style="font-size:12px; color:#6b7280;">No hay coincidencias para «{{ $busqueda }}»</p>
                @else
                    <p style="font-size:12px; color:#6b7280;">Esta regla no encontró productos que cumplan los criterios en este momento.</p>
                @endif
            </div>
        @endif

        {{-- Info de la regla --}}
        <div style="margin-top:20px; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:12px;">
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Tipo</div>
                <div style="font-size:13px; font-weight:700; color:#0f172a;">{{ ucfirst(str_replace('_', ' ', $regla->tipo)) }}</div>
            </div>
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Parámetro</div>
                <div style="font-size:13px; font-weight:700; color:#0f172a;">{{ $regla->descripcion_parametro }}</div>
            </div>
            @if($regla->rol)
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Notifica a</div>
                <div style="font-size:13px; font-weight:700; color:#0f172a;"><i class="fa fa-user mr-1" style="color:#6366f1;"></i>{{ $regla->rol->nombre }}</div>
            </div>
            @elseif($regla->area)
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Notifica a</div>
                <div style="font-size:13px; font-weight:700; color:#0f172a;"><i class="fa fa-building mr-1" style="color:#0891b2;"></i>{{ $regla->area->nombre }}</div>
            </div>
            @endif
            <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
                <div style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">Estado</div>
                <div>
                    @if($regla->activo)
                        <span style="background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">Activa</span>
                    @else
                        <span style="background:#f1f5f9; color:#64748b; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">Inactiva</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
</div>
