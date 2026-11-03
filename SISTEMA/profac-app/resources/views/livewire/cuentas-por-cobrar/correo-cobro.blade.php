<div>
    <style>
        .cc-backdrop { position:fixed; inset:0; z-index:6000; display:flex; align-items:center; justify-content:center; padding:20px; background:#14223599; }
        .cc-dialog { width:760px; max-width:100%; max-height:90vh; overflow:auto; overscroll-behavior:contain; border-radius:14px; background:white; color:#26354b; padding:22px; box-shadow:0 20px 70px #0004; }
    </style>
    @if($facturaDetalleId !== null)
        @teleport('body')
        <div class="cc-backdrop" x-data x-init="$nextTick(() => $refs.cerrar.focus({ preventScroll: true }))"
            @click.self="$wire.cerrar()" @keydown.escape.window="$wire.cerrar()" @wheel.self.prevent>
            <section class="cc-dialog" role="dialog" aria-modal="true" aria-labelledby="cc-title" x-trap.inert.noreturn.noautofocus="true">
                <header class="d-flex justify-content-between align-items-center mb-3">
                    <h4 id="cc-title">{{ $tituloDocumento }}</h4>
                    <button type="button" class="btn btn-outline-secondary" x-ref="cerrar" wire:click="cerrar" wire:loading.attr="disabled">Cerrar</button>
                </header>
                @include('livewire.reportes.correo-cobro-form', ['esEstadoCuenta' => $tipoDocumento === 'estado'])
            </section>
        </div>
        @endteleport
    @endif
</div>
