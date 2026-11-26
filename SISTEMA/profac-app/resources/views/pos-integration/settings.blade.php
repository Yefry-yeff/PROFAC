<x-app-layout>
    <div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12">
        <h2>Configuración / API Paperland</h2>
    </div></div>
    <div class="wrapper wrapper-content">
        <div class="ibox"><div class="ibox-title"><h3>Conexión bidireccional</h3></div>
            <div class="ibox-content">
                <p>La prueba usa la URL y clave escritas; una clave vacía usa la guardada. Consulta únicamente el estado firmado del POS; no mueve inventario.</p>
                <form id="paperland-settings" autocomplete="off">
                    <div class="form-group">
                        <label for="paperland-url">URL del otro sistema (POS)</label>
                        <input id="paperland-url" class="form-control" type="url" maxlength="2048" placeholder="http://127.0.0.1:8000" value="{{ $settings['peer_url'] }}">
                    </div>
                    <div class="form-group">
                        <label for="paperland-store">Tienda POS destino</label>
                        <input id="paperland-store" class="form-control" type="number" min="1" max="4294967295" step="1" value="{{ $settings['store_id'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="paperland-secret">Clave compartida nueva</label>
                        <p>{{ $settings['has_secret'] ? 'Tiene clave guardada.' : 'No tiene clave guardada.' }} Dejar en blanco conserva la clave actual.</p>
                        <input id="paperland-secret" class="form-control" type="password" maxlength="512" autocomplete="new-password">
                        <button id="paperland-generate" class="btn btn-default mt-2" type="button">Generar nueva clave</button>
                        <label class="ml-2"><input id="paperland-show-secret" type="checkbox"> Mostrar clave nueva</label>
                        <small class="d-block">La clave generada se muestra solo tras esta acción y debe copiarse al POS antes de guardar.</small>
                    </div>
                    <div class="form-group">
                        <label><input id="paperland-enabled" type="checkbox" @checked($settings['enabled'])> Habilitar sincronización</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                    <button id="paperland-test" class="btn btn-info" type="button">Probar conexión</button>
                </form>
                <p id="paperland-message" role="status" class="mt-3"></p>
            </div>
        </div>
        <div class="ibox"><div class="ibox-title"><h3>Estado de sincronización</h3></div>
            <div class="ibox-content">
                <p>Integración: {{ $settings['enabled'] ? 'Habilitada' : 'Deshabilitada' }}</p>
                <p>Worker: {{ $workerLastSeen ?? 'Sin heartbeat reciente' }}</p>
                <p>Envíos pendientes: {{ $outbox['pending'] ?? 0 }} · enviados: {{ $outbox['sent'] ?? 0 }} · fallidos: {{ $outbox['failed'] ?? 0 }}</p>
                <p>Recepciones pendientes: {{ $inboxPending }}</p>
                <p>Último error: {{ $lastError ?? 'Ninguno' }}</p>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
    (function () {
        const message = document.getElementById('paperland-message');
        const secret = document.getElementById('paperland-secret');
        async function post(action, data) {
            const response = await fetch('/configuracion/api-paperland/' + action, {
                method: 'POST', cache: 'no-store',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify(data || {})
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'No se pudo completar la operación.');
            return result;
        }
        document.getElementById('paperland-show-secret').onchange = function () { secret.type = this.checked ? 'text' : 'password'; };
        document.getElementById('paperland-generate').onclick = async function () {
            this.disabled = true;
            try { const result = await post('generar-clave'); secret.value = result.secret; message.textContent = 'Clave nueva generada. Cópiela al POS y guarde.'; }
            catch (e) { message.textContent = e.message; }
            finally { this.disabled = false; }
        };
        document.getElementById('paperland-settings').onsubmit = async function (event) {
            event.preventDefault();
            try {
                const result = await post('guardar', {
                    enabled: document.getElementById('paperland-enabled').checked,
                    peer_url: document.getElementById('paperland-url').value,
                    store_id: document.getElementById('paperland-store').value,
                    secret: secret.value
                });
                secret.value = ''; message.textContent = result.message; location.reload();
            } catch (e) { message.textContent = e.message; }
        };
        document.getElementById('paperland-test').onclick = async function () {
            this.disabled = true;
            try { const result = await post('probar', {peer_url: document.getElementById('paperland-url').value, secret: secret.value}); message.textContent = result.message + ' POS: ' + (result.peer_enabled ? 'habilitado' : 'deshabilitado') + '. Worker: ' + (result.worker_last_seen_at || 'sin heartbeat reciente'); }
            catch (e) { message.textContent = e.message; }
            finally { this.disabled = false; }
        };
    })();
    </script>
    @endpush
</x-app-layout>
