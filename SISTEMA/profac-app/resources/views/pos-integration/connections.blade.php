<x-app-layout>
    <div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12">
        <h2>Configuración / API Paperland</h2>
    </div></div>
    <div class="wrapper wrapper-content">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="paperland-connection-tab" data-toggle="tab" href="#paperland-connection-panel" role="tab" aria-controls="paperland-connection-panel" aria-selected="true">Conexión y operación</a></li>
            <li class="nav-item"><a class="nav-link" id="paperland-guide-tab" data-toggle="tab" href="#paperland-guide-panel" role="tab" aria-controls="paperland-guide-panel" aria-selected="false">Guía local / VPS</a></li>
        </ul>
        <div class="tab-content">
        <div class="tab-pane fade show active" id="paperland-connection-panel" role="tabpanel" aria-labelledby="paperland-connection-tab">
        <div class="ibox"><div class="ibox-title"><h3>Conexiones guardadas</h3></div><div class="ibox-content">
            <p>Solo una conexión puede estar seleccionada. Cambiar entre instancias POS distintas con historial de integración está bloqueado.</p>
            <table class="table table-striped">
                <thead><tr><th>Nombre</th><th>URL</th><th>Tienda</th><th>Estado</th><th>Clave</th><th>Instancia POS</th></tr></thead>
                <tbody>
                @foreach($profiles as $profile)
                    <tr><td>{{ $profile->name }}</td><td>{{ $profile->peer_url }}</td><td>{{ $profile->store_id }}</td>
                        <td>{{ $profile->active_slot ? ($settings->enabled ? 'Activa' : 'Seleccionada, deshabilitada') : 'Guardada' }}</td>
                        <td>{{ $profile->has_secret ? 'Guardada' : 'Sin clave' }}</td><td>{{ $profile->peer_instance_id ?? 'Sin verificar' }}</td></tr>
                @endforeach
                </tbody>
            </table>
            <form id="profile-form" autocomplete="off">
                <div class="form-group"><label for="profile-id">Perfil</label>
                    <select id="profile-id" class="form-control">
                        <option value="">Nuevo perfil</option>
                        @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" data-name="{{ $profile->name }}" data-url="{{ $profile->peer_url }}"
                            data-store="{{ $profile->store_id }}" data-secret="{{ $profile->has_secret ? '1' : '0' }}"
                            @selected($profile->active_slot)>{{ $profile->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label for="profile-name">Nombre</label><input id="profile-name" class="form-control" maxlength="255" required></div>
                <div class="form-group"><label for="profile-url">URL del POS</label><input id="profile-url" class="form-control" type="url" maxlength="2048" placeholder="http://127.0.0.1:8000"></div>
                <div class="form-group"><label for="profile-store">Tienda POS destino</label><input id="profile-store" class="form-control" type="number" min="1" max="4294967295" step="1" value="1" required></div>
                <div class="form-group"><label for="profile-secret">Clave compartida nueva</label>
                    <p id="profile-secret-state"></p>
                    <input id="profile-secret" class="form-control" type="password" maxlength="512" autocomplete="new-password">
                    <button id="generate" class="btn btn-default mt-2" type="button">Generar nueva clave</button>
                    <label class="ml-2"><input id="show-secret" type="checkbox"> Mostrar clave nueva</label>
                    <small class="d-block">La clave guardada nunca se muestra. Dejar vacío conserva la clave de este perfil.</small>
                </div>
                <button class="btn btn-primary" type="submit">Guardar perfil</button>
                <button id="test-profile" class="btn btn-info" type="button">Probar conexión</button>
                <button id="activate-profile" class="btn btn-success" type="button">Activar perfil</button>
                <button id="delete-profile" class="btn btn-danger" type="button">Eliminar perfil</button>
                <button id="deactivate" class="btn btn-default" type="button">Deshabilitar sincronización</button>
            </form>
            <p id="profile-message" role="status" class="mt-3"></p>
        </div></div>
        <div class="ibox"><div class="ibox-title"><h3>Servicio y sincronización</h3></div><div class="ibox-content">
            <p>Instancia PROFAC: {{ $runtime->instance_id }}</p>
            <p>Integración: {{ $settings->enabled ? 'Habilitada' : 'Deshabilitada' }} · Coordinador: {{ $online ? 'En línea' : 'Sin heartbeat reciente' }} · Worker: {{ $workerState }}</p>
            @if(!$runtime->bound_peer_instance_id)
                <p>Instancia POS sin verificar: active el perfil antes de procesar eventos, despachos o rondas.</p>
            @endif
            <p>La pausa se aplica al terminar la ronda en curso. El coordinador permanece activo aunque el worker esté pausado.</p>
            <button type="button" class="btn btn-success control" data-action="start">Iniciar</button>
            <button type="button" class="btn btn-warning control" data-action="pause">Pausar</button>
            <button type="button" class="btn btn-info control" data-action="workonce">Procesar ronda</button>
            <p class="mt-3">Envíos pendientes: {{ $outbox['pending'] ?? 0 }} · enviados: {{ $outbox['sent'] ?? 0 }} · fallidos: {{ $outbox['failed'] ?? 0 }} · recepciones pendientes: {{ $inboxPending }}</p>
            <p>Último error del coordinador: {{ $runtime->last_error ?? 'Ninguno' }}</p>
            <p>Último error de envío: {{ $lastOutboxError ?? 'Ninguno' }} · recepción: {{ $lastInboxError ?? 'Ninguno' }}</p>
            <p>La preparación inicial de catálogo e inventario se ejecuta en la pantalla API del POS; PROFAC no necesita preparación.</p>
            <p>En local no necesita VPS: mantenga una terminal abierta en cada proyecto con <code>php artisan pos-integration:manage</code>. Al cerrar las terminales o reiniciar el equipo, vuelva a ejecutar el comando. Los botones controlan el procesamiento, no crean procesos del servidor.</p>
            <p>En VPS Linux instale una sola vez por SSH el <a href="/configuracion/api-paperland/servicio">archivo systemd</a>; ajuste el usuario del servicio y la ruta PHP CLI a su servidor. Consulte la guía antes de habilitarlo.</p>
            <table class="table table-striped">
                <thead><tr><th>Operación</th><th>Perfil</th><th>Solicitó</th><th>Estado</th><th>Inicio</th><th>Fin</th><th>Resultado</th></tr></thead>
                <tbody>@foreach($operations as $operation)
                    <tr><td>{{ $operation->action }}</td><td>{{ $operation->connection_id }}</td><td>{{ $operation->requested_by ?? 'Servicio' }}</td>
                        <td>{{ $operation->status }}</td><td>{{ $operation->started_at }}</td><td>{{ $operation->finished_at }}</td>
                        <td>{{ $operation->error ?? $operation->output ?? '' }}</td></tr>
                @endforeach</tbody>
            </table>
        </div></div>
        </div>
        <div class="tab-pane fade" id="paperland-guide-panel" role="tabpanel" aria-labelledby="paperland-guide-tab">
            @include('pos-integration.guide')
        </div>
        </div>
    </div>
    @push('scripts')
    <script>
    (function () {
        const select = document.getElementById('profile-id'), secret = document.getElementById('profile-secret');
        const name = document.getElementById('profile-name'), url = document.getElementById('profile-url'), store = document.getElementById('profile-store');
        const message = document.getElementById('profile-message');
        function load() {
            const option = select.selectedOptions[0];
            name.value = option.dataset.name || ''; url.value = option.dataset.url || ''; store.value = option.dataset.store || '1'; secret.value = '';
            document.getElementById('profile-secret-state').textContent = option.dataset.secret === '1' ? 'Tiene clave guardada.' : 'No tiene clave guardada.';
        }
        async function post(action, data) {
            const response = await fetch('/configuracion/api-paperland/' + action, {
                method: 'POST', cache: 'no-store', headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(data || {})
            });
            let result;
            try { result = await response.json(); } catch (_) { throw new Error('No se pudo completar la operación.'); }
            if (!response.ok) throw new Error(result.message || 'No se pudo completar la operación.');
            return result;
        }
        async function execute(button, fn, reload) {
            button.disabled = true;
            try { const result = await fn(); message.textContent = result.message; if (reload) location.reload(); }
            catch (e) { message.textContent = e.message; }
            finally { button.disabled = false; }
        }
        select.onchange = load; load();
        document.getElementById('show-secret').onchange = function () { secret.type = this.checked ? 'text' : 'password'; };
        document.getElementById('generate').onclick = function () {
            execute(this, async () => { const result = await post('generar-clave'); secret.value = result.secret; return {message: 'Copie la clave al POS y guarde.'}; }, false);
        };
        document.getElementById('profile-form').onsubmit = function (event) {
            event.preventDefault();
            execute(this.querySelector('[type=submit]'), () => post('perfiles', {id: select.value || null, name: name.value, peer_url: url.value, store_id: store.value, secret: secret.value}), true);
        };
        document.getElementById('test-profile').onclick = function () {
            execute(this, async () => { const r = await post('perfiles/probar', {id: select.value || null, peer_url: url.value, secret: secret.value});
                r.message += ' Instancia: ' + r.instance_id + ' · Preparado: ' + (r.prepared ? 'sí' : 'no') + ' · Worker: ' + r.worker_state; return r; }, false);
        };
        document.getElementById('activate-profile').onclick = function () {
            if (!select.value) { message.textContent = 'Guarde y seleccione un perfil.'; return; }
            execute(this, () => post('perfiles/' + select.value + '/activar'), true);
        };
        document.getElementById('delete-profile').onclick = function () {
            if (!select.value || !confirm('¿Eliminar este perfil guardado?')) return;
            execute(this, () => post('perfiles/' + select.value + '/eliminar'), true);
        };
        document.getElementById('deactivate').onclick = function () { execute(this, () => post('desactivar'), true); };
        document.querySelectorAll('.control').forEach(button => button.onclick = function () { execute(this, () => post('control/' + this.dataset.action), true); });
    })();
    </script>
    @endpush
</x-app-layout>
