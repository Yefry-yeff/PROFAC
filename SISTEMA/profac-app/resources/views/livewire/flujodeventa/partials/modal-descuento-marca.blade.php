@if($mostrarModalDescuentoMarca)
    <div class="expo-detail-backdrop" wire:click.self="cerrarModalDescuentoMarca" style="z-index:2100;" wire:key="expo-descuento-marca-modal">
        <div class="expo-detail-modal" style="max-width:760px;" role="dialog" aria-modal="true" aria-labelledby="expo-descuento-marca-titulo">
            <div class="expo-detail-head">
                <div>
                    <h4 id="expo-descuento-marca-titulo"><i class="fa fa-tags mr-2"></i>{{ $marcaDescuentoEditandoId ? 'Editar descuento por marca' : 'Descuento por marca' }}</h4>
                    <small>{{ $marcaDescuentoEditandoId ? 'Actualice los escalones por subtotal neto de la oferta.' : 'Seleccione una marca y configure sus escalones por subtotal neto de la oferta.' }}</small>
                </div>
                <button type="button" wire:click="cerrarModalDescuentoMarca" class="expo-detail-close" title="Cerrar"><i class="fa fa-times"></i></button>
            </div>
            <div class="expo-detail-body">
                <div class="form-group">
                    <label class="expo-label">Marca <span class="text-danger">*</span></label>
                    <select wire:model.defer="marcaDescuentoSeleccionada" class="form-control" @if($marcaDescuentoEditandoId) disabled @endif>
                        <option value="">Seleccione una marca</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                    @error('marcaDescuentoSeleccionada') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Escalones de descuento</strong>
                    <button type="button" wire:click="agregarEscalonMarcaModal" class="btn btn-sm btn-outline-warning"><i class="fa fa-plus mr-1"></i>Agregar escalón</button>
                </div>
                @error('escalonesMarcaModal') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
                <div class="table-responsive expo-discount-wrap">
                    <table class="table table-sm expo-discount-table mb-0">
                        <thead><tr><th>Subtotal neto desde (L.)</th><th>Descuento (%)</th><th style="width:150px;">Requiere asistencia</th><th style="width:60px;">Acción</th></tr></thead>
                        <tbody>
                            @foreach($escalonesMarcaModal as $indice => $escalon)
                                <tr wire:key="expo-modal-escalon-{{ $indice }}">
                                    <td>
                                        <input type="text" inputmode="decimal" wire:model.defer="escalonesMarcaModal.{{ $indice }}.venta_minima"
                                               class="form-control form-control-sm expo-money-input" placeholder="0.00" autocomplete="off"
                                               x-data="{ formatMoney() { let raw = $el.value.replace(/,/g, '').replace(/[^0-9.]/g, ''); const point = raw.indexOf('.'); if (point !== -1) raw = raw.slice(0, point + 1) + raw.slice(point + 1).replace(/\./g, '').slice(0, 2); let parts = raw.split('.'); parts[0] = (parts[0] || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ','); $el.value = parts[0] + (raw.includes('.') ? '.' + (parts[1] || '') : ''); } }"
                                               x-on:input="formatMoney()"
                                               x-on:blur="const amount = Number($el.value.replace(/,/g, '')); $el.value = Number.isFinite(amount) ? amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : ''">
                                        @error('escalonesMarcaModal.'.$indice.'.venta_minima') <small class="text-danger">{{ $message }}</small> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" wire:model.defer="escalonesMarcaModal.{{ $indice }}.porcentaje_descuento" class="form-control form-control-sm" placeholder="0.00">
                                        @error('escalonesMarcaModal.'.$indice.'.porcentaje_descuento') <small class="text-danger">{{ $message }}</small> @enderror
                                    </td>
                                    <td class="align-middle">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="requiere-asistencia-escalon-{{ $indice }}" wire:model.defer="escalonesMarcaModal.{{ $indice }}.requiere_asistencia" class="custom-control-input">
                                            <label class="custom-control-label" for="requiere-asistencia-escalon-{{ $indice }}">Sí, exigir lista</label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="eliminarEscalonMarcaModal({{ $indice }})" class="btn btn-xs btn-white" title="Eliminar escalón" @if(count($escalonesMarcaModal) <= 1) disabled @endif><i class="fa fa-trash text-danger"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mt-2"><i class="fa fa-info-circle mr-1"></i>El subtotal neto de la oferta, después de descuentos, determina el escalón. El porcentaje se aplica a los productos de esta marca; la asistencia se valida únicamente cuando está marcada.</small>
            </div>
            <div class="p-3 border-top d-flex justify-content-end" style="gap:8px;">
                <button type="button" wire:click="cerrarModalDescuentoMarca" class="btn btn-default">Cancelar</button>
                <button type="button" wire:click="guardarDescuentoMarcaModal" wire:loading.attr="disabled" class="btn expo-save-btn"><i class="fa {{ $marcaDescuentoEditandoId ? 'fa-save' : 'fa-check' }} mr-1"></i>{{ $marcaDescuentoEditandoId ? 'Actualizar descuentos' : 'Agregar descuentos' }}</button>
            </div>
        </div>
    </div>
@endif
