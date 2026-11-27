<x-app-layout>
    <style>
        .paperland-connections .pi-status { display:inline-block; padding:4px 9px; border-radius:20px; font-size:12px; font-weight:600; background:#eef2f7; color:#475569; }
        .paperland-connections .pi-status--on { background:#dcfce7; color:#166534; }
        .paperland-connections .pi-status--off { background:#fee2e2; color:#991b1b; }
        .paperland-connections .pi-actions { display:flex; flex-wrap:wrap; gap:6px; min-width:240px; }
        .paperland-connections .pi-connections-table th:last-child, .paperland-connections .pi-connections-table td:last-child { position:sticky; right:0; background:#fff; box-shadow:-4px 0 8px #0f172a0d; }
        .paperland-connections .pi-connections-table td:first-child { min-width:220px; max-width:320px; overflow-wrap:anywhere; }
        .paperland-connections .pi-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-bottom:18px; }
        .paperland-connections .pi-summary > div { padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; }
        .paperland-connections .pi-summary strong { display:block; margin-bottom:8px; }
        #profile-dialog { width:min(760px,calc(100vw - 32px)); max-height:calc(100vh - 32px); border:0; border-radius:16px; padding:0; box-shadow:0 24px 80px #0f172a66; color:#334155; }
        #profile-dialog:not([open]) { display:none; }
        #profile-dialog::backdrop { background:#0f172a99; backdrop-filter:blur(3px); }
        #profile-dialog .pi-modal-header { padding:18px 24px; background:#f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        #profile-dialog form { padding:24px; }
        #profile-dialog .pi-modal-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:18px; }
    </style>
    <div class="row wrapper border-bottom white-bg page-heading"><div class="col-lg-12">
        <h2>Configuración / API Paperland</h2>
    </div></div>
    <div class="wrapper wrapper-content paperland-connections">
        @php
            $workerLabels = ['offline' => 'Fuera de línea', 'running' => 'Automático activo', 'paused' => 'Pausado', 'stopping' => 'Terminando para pausar', 'operation_running' => 'Procesando'];
            $peerKnown = $peerStatus && !isset($peerStatus['error']);
        @endphp
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="paperland-connection-tab" data-toggle="tab" href="#paperland-connection-panel" role="tab" aria-controls="paperland-connection-panel" aria-selected="true">Conexión y operación</a></li>
            <li class="nav-item"><a class="nav-link" id="paperland-guide-tab" data-toggle="tab" href="#paperland-guide-panel" role="tab" aria-controls="paperland-guide-panel" aria-selected="false">Guía local / VPS</a></li>
        </ul>
        <div class="tab-content">
        <div class="tab-pane fade show active" id="paperland-connection-panel" role="tabpanel" aria-labelledby="paperland-connection-tab">
        <div class="ibox"><div class="ibox-title"><h3>Conexiones guardadas</h3></div><div class="ibox-content">
            <div class="pi-summary">
                <div><strong>Supervisor Valencia</strong><span data-state="supervisor" class="pi-status {{ $online ? 'pi-status--on' : 'pi-status--off' }}">{{ $online ? 'Activo' : 'Apagado' }}</span></div>
                <div><strong>Worker Valencia</strong><span data-state="worker" class="pi-status">{{ $workerLabels[$workerState] ?? $workerState }}</span></div>
                <div><strong>Supervisor POS</strong><span data-state="peer-supervisor" class="pi-status">{{ $peerKnown ? ($peerStatus['coordinator_online'] ? 'Activo' : 'Apagado') : 'Sin verificar' }}</span></div>
                <div><strong>Worker POS</strong><span data-state="peer-worker" class="pi-status">{{ $peerKnown ? ($workerLabels[$peerStatus['worker_state']] ?? $peerStatus['worker_state']) : 'Sin verificar' }}</span></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Una conexión activa a la vez. El supervisor y el worker son procesos distintos.</p>
                <button id="new-profile" type="button" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Nueva conexión</button>
            </div>
            <p id="profile-message" role="status" class="mt-3"></p>
            <p>Solo una conexión puede estar seleccionada. Cambiar entre instancias POS distintas con historial de integración está bloqueado.</p>
            <div class="table-responsive">
            <table class="table table-striped pi-connections-table">
                <thead><tr><th>Nombre / destino</th><th>Tienda</th><th>Conexión</th><th>Supervisor Valencia</th><th>Worker Valencia</th><th>Supervisor POS</th><th>Worker POS</th><th>Opciones</th></tr></thead>
                <tbody>
                @foreach($profiles as $profile)
                    <tr><td><strong>{{ $profile->name }}</strong><div>{{ $profile->peer_url }}</div><small>{{ $profile->has_secret ? 'Clave guardada' : 'Sin clave' }} · POS: {{ $profile->peer_instance_id ?? 'Sin verificar' }}</small></td><td>{{ $profile->store_id }}</td>
                        <td>{{ $profile->active_slot ? ($settings->enabled ? 'Activa' : 'Seleccionada, deshabilitada') : 'Guardada' }}</td>
                        <td>@if($profile->active_slot)<span data-state="supervisor" class="pi-status">{{ $online ? 'Activo' : 'Apagado' }}</span>@else — @endif</td>
                        <td>@if($profile->active_slot)<span data-state="worker" class="pi-status">{{ $workerLabels[$workerState] ?? $workerState }}</span>@else — @endif</td>
                        <td>@if($profile->active_slot)<span data-state="peer-supervisor" class="pi-status">{{ $peerKnown ? ($peerStatus['coordinator_online'] ? 'Activo' : 'Apagado') : 'Sin verificar' }}</span>@else — @endif</td>
                        <td>@if($profile->active_slot)<span data-state="peer-worker" class="pi-status">{{ $peerKnown ? ($workerLabels[$peerStatus['worker_state']] ?? $peerStatus['worker_state']) : 'Sin verificar' }}</span>@else — @endif</td>
                        <td><div class="pi-actions">
                            <button type="button" class="btn btn-sm btn-default edit-profile" data-id="{{ $profile->id }}">Editar</button>
                            <button type="button" class="btn btn-sm btn-info probe-profile" data-id="{{ $profile->id }}">Probar</button>
                            @if($profile->active_slot && $settings->enabled)
                                <button type="button" class="btn btn-sm btn-success control" data-action="start" data-profile="{{ $profile->id }}">Iniciar automático</button>
                                <button type="button" class="btn btn-sm btn-warning control" data-action="pause">Pausar worker</button>
                                <button type="button" class="btn btn-sm btn-default disable-profile">Deshabilitar</button>
                            @else
                                <button type="button" class="btn btn-sm btn-success activate-row" data-id="{{ $profile->id }}">Activar conexión</button>
                                @if(!$profile->active_slot)<button type="button" class="btn btn-sm btn-danger delete-row" data-id="{{ $profile->id }}">Eliminar</button>@endif
                            @endif
                        </div></td></tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <p id="supervisor-offline-warning" class="alert alert-warning" @if($online) hidden @endif>Supervisor apagado: la conexión puede estar activa, pero no se envían mensajes. {{ app()->environment('local') && PHP_OS_FAMILY === 'Windows' ? 'Iniciar automático levantará el supervisor local y verificará su heartbeat.' : 'Levante el servicio del VPS antes de iniciar el worker.' }}</p>
            @if(isset($peerStatus['error']))<p class="alert alert-warning">{{ $peerStatus['error'] }}</p>@endif
            <dialog id="profile-dialog" aria-labelledby="profile-dialog-title">
            <div class="pi-modal-header"><h3 id="profile-dialog-title" class="mb-0">Nueva conexión</h3><button id="close-profile" type="button" class="btn btn-default" aria-label="Cerrar conexión">×</button></div>
            <form id="profile-form" autocomplete="off">
                    <select id="profile-id" hidden>
                        <option value="">Nuevo perfil</option>
                        @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" data-name="{{ $profile->name }}" data-url="{{ $profile->peer_url }}"
                            data-store="{{ $profile->store_id }}" data-secret="{{ $profile->has_secret ? '1' : '0' }}"
                            data-active="{{ $profile->active_slot && $settings->enabled ? '1' : '0' }}" @selected($profile->active_slot)>{{ $profile->name }}</option>
                        @endforeach
                    </select>
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
                <p class="text-muted">Guarde la conexión, actívela y pulse <strong>Iniciar automático</strong>. No necesita procesar rondas manuales ni preparar catálogos en Valencia. En local se levanta el supervisor automáticamente; en VPS debe estar instalado y activo.</p>
                <div class="pi-modal-actions">
                <button class="btn btn-primary" type="submit">Guardar conexión</button>
                <button id="test-profile" class="btn btn-info" type="button">Probar conexión</button>
                <button id="activate-profile" class="btn btn-success" type="button">Activar conexión</button>
                <button id="modal-start-worker" class="btn btn-success control" data-action="start" type="button">Iniciar automático</button>
                </div>
                <p id="modal-message" role="status" class="mt-3"></p>
            </form>
            </dialog>
        </div></div>
        <div class="ibox"><div class="ibox-title"><h3>Servicio y sincronización</h3></div><div class="ibox-content">
            <p>Instancia Valencia: {{ $runtime->instance_id }}</p>
            <p>Integración: {{ $settings->enabled ? 'Habilitada' : 'Deshabilitada' }} · Supervisor: <span data-state="supervisor" class="pi-status">{{ $online ? 'Activo' : 'Apagado' }}</span> · Worker: <span data-state="worker" class="pi-status">{{ $workerLabels[$workerState] ?? $workerState }}</span></p>
            @if(!$runtime->bound_peer_instance_id)
                <p>Instancia POS sin verificar: active el perfil antes de procesar eventos, despachos o rondas.</p>
            @endif
            <p>La pausa se aplica al terminar la ronda en curso. El coordinador permanece activo aunque el worker esté pausado.</p>
            <button type="button" class="btn btn-success control" data-action="start" @disabled(!$settings->enabled)>Iniciar automático</button>
            <button type="button" class="btn btn-warning control" data-action="pause">Pausar</button>
            <details class="mt-3"><summary>Diagnóstico avanzado</summary><button type="button" class="btn btn-info control mt-2" data-action="workonce">Procesar una vez</button></details>
            <p class="mt-3">Envíos pendientes: {{ $outbox['pending'] ?? 0 }} · enviados: {{ $outbox['sent'] ?? 0 }} · fallidos: {{ $outbox['failed'] ?? 0 }} · recepciones pendientes: {{ $inboxPending }}</p>
            <p>Último error del coordinador: {{ $runtime->last_error ?? 'Ninguno' }}</p>
            <p>Último error de envío: {{ $lastOutboxError ?? 'Ninguno' }} · recepción: {{ $lastInboxError ?? 'Ninguno' }}</p>
            <p>La preparación inicial de catálogo e inventario se ejecuta en la pantalla API del POS; PROFAC no necesita preparación.</p>
            <p>En Windows con <code>APP_ENV=local</code>, Iniciar automático levanta el supervisor en segundo plano. Continúa aunque cierre esta página; después de reiniciar Windows vuelva a pulsar Iniciar automático. Pausar worker no apaga el supervisor. Alternativa manual: <code>php artisan pos-integration:manage</code>.</p>
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
        const dialog = document.getElementById('profile-dialog'), modalMessage = document.getElementById('modal-message');
        let busy = false, backdrop = false;
        function notify(text, error = false) {
            [message, modalMessage].forEach(el => { el.textContent = text; el.className = 'mt-3 ' + (error ? 'text-danger' : 'text-success'); });
        }
        function load() {
            const option = select.selectedOptions[0];
            name.value = option.dataset.name || ''; url.value = option.dataset.url || ''; store.value = option.dataset.store || '1'; secret.value = '';
            document.getElementById('profile-secret-state').textContent = option.dataset.secret === '1' ? 'Tiene clave guardada.' : 'No tiene clave guardada.';
            document.getElementById('profile-dialog-title').textContent = select.value ? 'Editar conexión' : 'Nueva conexión';
            document.getElementById('activate-profile').disabled = !select.value || option.dataset.active === '1';
            const start = document.getElementById('modal-start-worker');
            start.disabled = option.dataset.active !== '1'; start.dataset.profile = select.value;
            modalMessage.textContent = '';
        }
        function openProfile(id) { select.value = id || ''; load(); dialog.showModal(); }
        document.getElementById('new-profile').onclick = () => openProfile('');
        document.querySelectorAll('.edit-profile').forEach(button => button.onclick = () => openProfile(button.dataset.id));
        document.getElementById('close-profile').onclick = () => { if (!busy) dialog.close(); };
        dialog.addEventListener('cancel', event => { event.preventDefault(); if (!busy) dialog.close(); });
        dialog.addEventListener('keydown', event => {
            if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); if (!busy) dialog.close(); }
        });
        dialog.addEventListener('close', () => { secret.value = ''; secret.type = 'password'; document.getElementById('show-secret').checked = false; });
        dialog.addEventListener('pointerdown', event => { backdrop = event.target === dialog; });
        dialog.addEventListener('pointerup', event => { if (event.target !== dialog) backdrop = false; });
        dialog.addEventListener('click', event => { if (!busy && backdrop && event.target === dialog) dialog.close(); });
        async function post(action, data) {
            const response = await fetch('/configuracion/api-paperland/' + action, {
                method: 'POST', cache: 'no-store', headers: {'Content-Type': 'application/json', 'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(data || {})
            });
            let result;
            try { result = await response.json(); } catch (_) { throw new Error('No se pudo completar la operación.'); }
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : (result.message || 'No se pudo completar la operación.'));
            return result;
        }
        async function execute(button, fn, reload) {
            if (busy) return;
            busy = true;
            button.disabled = true;
            try { const result = await fn(); notify(result.message); if (reload) location.reload(); }
            catch (e) { notify(e.message, true); }
            finally { busy = false; button.disabled = false; }
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
            if (!select.value) { notify('Guarde primero la conexión.', true); return; }
            execute(this, () => post('perfiles/' + select.value + '/activar'), true);
        };
        document.querySelectorAll('.probe-profile').forEach(button => button.onclick = function () {
            execute(this, async () => { const result = await post('perfiles/probar', {id:this.dataset.id});
                result.message += ' Supervisor POS: ' + (result.coordinator_online ? 'activo' : 'apagado') + ' · Worker POS: ' + result.worker_state; return result; }, false);
        });
        document.querySelectorAll('.activate-row').forEach(button => button.onclick = function () { execute(this, () => post('perfiles/' + this.dataset.id + '/activar'), true); });
        document.querySelectorAll('.delete-row').forEach(button => button.onclick = function () {
            if (confirm('¿Eliminar esta conexión guardada?')) execute(this, () => post('perfiles/' + this.dataset.id + '/eliminar'), true);
        });
        document.querySelectorAll('.disable-profile').forEach(button => button.onclick = function () {
            if (confirm('¿Deshabilitar la comunicación? Primero debe pausar el worker y resolver los pendientes.')) execute(this, () => post('desactivar'), true);
        });
        document.querySelectorAll('.control').forEach(button => button.onclick = function () {
            execute(this, () => post('control/' + this.dataset.action, {profile_id:this.dataset.profile || null}), true);
        });
        const workerLabels = {offline:'Fuera de línea', running:'Automático activo', paused:'Pausado', stopping:'Terminando para pausar', operation_running:'Procesando'};
        function status(name, text, active) {
            document.querySelectorAll('[data-state="' + name + '"]').forEach(el => {
                el.textContent = text; el.className = 'pi-status' + (active === true ? ' pi-status--on' : (active === false ? ' pi-status--off' : ''));
            });
        }
        async function refreshStatus() {
            try {
                const response = await fetch('/configuracion/api-paperland', {cache:'no-store', headers:{Accept:'application/json'}});
                if (!response.ok) throw new Error('No se pudo actualizar el estado de integración. Recargue la página.');
                const state = await response.json();
                if (Number(state.connection_id) !== Number(@json($settings->connection_id)) || state.enabled !== @json((bool) $settings->enabled)) {
                    if (!dialog.open) location.reload();
                    else notify('La conexión activa cambió. Cierre el modal para actualizar los estados.', true);
                    return;
                }
                document.getElementById('supervisor-offline-warning').hidden = state.online;
                status('supervisor', state.online ? 'Activo' : 'Apagado', state.online);
                status('worker', workerLabels[state.worker_state] || state.worker_state, state.worker_state === 'running');
                const peer = state.peer && !state.peer.error ? state.peer : null;
                status('peer-supervisor', peer ? (peer.coordinator_online ? 'Activo' : 'Apagado') : 'Sin verificar', peer ? peer.coordinator_online : null);
                status('peer-worker', peer ? (workerLabels[peer.worker_state] || peer.worker_state) : 'Sin verificar', peer ? peer.worker_state === 'running' : null);
            } catch (e) {
                ['supervisor','worker','peer-supervisor','peer-worker'].forEach(name => status(name, 'Estado no disponible', null));
                notify(e.message, true);
            }
        }
        setInterval(() => { if (!busy && !document.hidden) refreshStatus(); }, 10000);
    })();
    </script>
    @endpush
</x-app-layout>
