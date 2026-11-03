                @if($etapaCorreo !== '')
                    <div class="border rounded p-3 mb-3" role="region" aria-label="Enviar factura por correo">
                        <h5>{{ !empty($esEstadoCuenta) ? 'Enviar estado de cuenta por correo' : 'Enviar factura por correo' }}</h5>
                        <p class="small mb-2">Remitente: <strong>{{ \App\Mail\FacturaCobro::REMITENTE }}</strong><br>
                            Se adjuntará {{ !empty($esEstadoCuenta) ? 'el estado de cuenta completo en PDF' : 'la copia PDF de esta factura' }} con un recordatorio de su saldo pendiente actual,
                            el membrete de la empresa y las cuentas del catálogo Bancos, sin Promerica.</p>
                        @if($etapaCorreo === 'direccion')
                            <p>¿El correo del cliente es correcto? Si no, corrígelo antes de enviar.</p>
                            <p class="small text-muted">Correo registrado: {{ $correoOriginal ?: 'El cliente no tiene un correo registrado.' }}</p>
                            <label for="cb-correo-factura">Correo del destinatario</label>
                            <input type="email" id="cb-correo-factura" class="form-control mb-2" wire:model="correoFactura" @input="$wire.correoCorrecto = false" maxlength="254" autocomplete="off">
                            @error('correoFactura') <p class="text-danger" role="alert">{{ $message }}</p> @enderror
                            <label class="d-flex align-items-center" style="gap:8px">
                                <input type="checkbox" wire:model="correoCorrecto"> Confirmo que este correo es correcto y pertenece al cliente.
                            </label>
                            @error('correoCorrecto') <p class="text-danger" role="alert">{{ $message }}</p> @enderror
                            <div class="d-flex flex-wrap mt-2" style="gap:8px">
                                <button type="button" class="btn btn-primary" wire:click="solicitarEnvioFactura" wire:loading.attr="disabled">Enviar</button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="cancelarCorreoFactura" wire:loading.attr="disabled">Cancelar envío</button>
                            </div>
                        @elseif($etapaCorreo === 'actualizar')
                            <p>Se enviará {{ !empty($esEstadoCuenta) ? 'el estado de cuenta' : 'la factura' }} a <strong>{{ $correoDestino }}</strong>.</p>
                            <p>¿Desea actualizar el correo del cliente con esta dirección?</p>
                            <div class="d-flex flex-wrap" style="gap:8px">
                                <button type="button" class="btn btn-primary" wire:click="enviarCorreoFactura(true)" wire:loading.attr="disabled">Sí, actualizar y enviar</button>
                                <button type="button" class="btn btn-outline-primary" wire:click="enviarCorreoFactura(false)" wire:loading.attr="disabled">No, solo enviar</button>
                                <button type="button" class="btn btn-outline-secondary" wire:click="cancelarCorreoFactura" wire:loading.attr="disabled">Cancelar envío</button>
                            </div>
                        @elseif($etapaCorreo === 'enviar')
                            <p>Destinatario confirmado: <strong>{{ $correoDestino }}</strong>.</p>
                            <button type="button" class="btn btn-primary" wire:click="enviarCorreoFactura(false)" wire:loading.attr="disabled">Reintentar envío</button>
                            <button type="button" class="btn btn-outline-secondary" wire:click="cancelarCorreoFactura" wire:loading.attr="disabled">Cancelar envío</button>
                        @endif
                        @if($mensajeCorreo !== '')
                            <p class="text-success mt-2" role="status">{{ $mensajeCorreo }}</p>
                        @endif
                        @error('envioCorreo') <p class="text-danger mt-2" role="alert">{{ $message }}</p> @enderror
                        <p wire:loading wire:target="solicitarEnvioFactura,enviarCorreoFactura" role="status" class="mt-2"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Preparando PDF y enviando correo...</p>
                    </div>
                @endif
