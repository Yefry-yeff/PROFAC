<div class="ibox">
    <div class="ibox-title"><h3>Guía de activación Profac y POS</h3></div>
    <div class="ibox-content">
        <p>Primero conecte los sistemas, después prepare el POS y finalmente inicie los workers. Esta guía es informativa: no ejecuta comandos ni modifica inventario.</p>
        <figure>
            <svg viewBox="0 0 760 190" role="img" aria-label="Esquema: POS y Profac intercambian mensajes HTTP firmados; cada sistema necesita su propio supervisor." style="width:100%;max-height:190px">
                <rect x="20" y="15" width="240" height="115" rx="16" fill="#eff6ff" stroke="#2563eb"/>
                <rect x="500" y="15" width="240" height="115" rx="16" fill="#ecfdf5" stroke="#059669"/>
                <g font-family="sans-serif" text-anchor="middle">
                    <text x="140" y="58" font-size="26" fill="#1e40af">POS</text>
                    <text x="140" y="93" font-size="17" fill="#334155">Ventas y distribución</text>
                    <text x="620" y="58" font-size="26" fill="#047857">PROFAC</text>
                    <text x="620" y="93" font-size="17" fill="#334155">Catálogo maestro</text>
                    <text x="380" y="47" font-size="17" fill="#334155">HTTP firmado</text>
                    <text x="140" y="163" font-size="17" fill="#334155">Supervisor propio</text>
                    <text x="620" y="163" font-size="17" fill="#334155">Supervisor propio</text>
                </g>
                <path d="M280 70 H480 L467 62 M480 70 L467 78 M480 100 H280 L293 92 M280 100 L293 108" fill="none" stroke="#64748b" stroke-width="3"/>
            </svg>
            <figcaption class="text-center text-muted">Diagrama ilustrativo, no estado actual. Local: dos supervisores en segundo plano. VPS: dos servicios systemd.</figcaption>
        </figure>
        <ul class="nav nav-tabs mb-3" role="tablist" aria-label="Entorno de la guía">
            <li class="nav-item"><a class="nav-link active" id="paperland-local-tab" data-toggle="tab" href="#paperland-local-guide" role="tab" aria-controls="paperland-local-guide" aria-selected="true">Local / Laragon</a></li>
            <li class="nav-item"><a class="nav-link" id="paperland-vps-tab" data-toggle="tab" href="#paperland-vps-guide" role="tab" aria-controls="paperland-vps-guide" aria-selected="false">VPS / Linux</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="paperland-local-guide" role="tabpanel" aria-labelledby="paperland-local-tab">
                <h4>Paso a paso en local</h4>
                <ol>
                    <li class="mb-3"><strong>Abra ambos sistemas.</strong> POS: <code>http://127.0.0.1:8000</code>. Profac: <code>http://127.0.0.1:8001</code>. Mantenga ejecutándose sus servidores web.</li>
                    <li class="mb-3"><strong>Levante un supervisor por proyecto.</strong> En Windows local, Iniciar automático lo levanta si está apagado; POS también ofrece Levantar supervisor local para la preparación. Si ya aparece activo, no abra otro. Alternativa manual: dos terminales PowerShell independientes.
                        <pre class="mt-2"><code># Terminal Profac
Set-Location "C:\laragon\www\PROFAC\SISTEMA\profac-app"
&amp; "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan pos-integration:manage

# Otra terminal: POS
Set-Location "C:\laragon\www\mira_copan"
&amp; "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan pos-integration:manage</code></pre>
                    </li>
                    <li class="mb-3"><strong>Configure direcciones recíprocas.</strong> En API Paperland de Profac: <code>http://127.0.0.1:8000</code>. En Configuración → API → Conexión Valencia del POS: <code>http://127.0.0.1:8001</code>. Seleccione la tienda POS correcta, use la misma clave de al menos 32 caracteres y guarde. Campo de clave vacío conserva la guardada.</li>
                    <li class="mb-3"><strong>Pruebe y active los perfiles.</strong> Pause ambos workers y espere la ronda actual. Pulse Probar conexión y luego Activar perfil en Profac y Activar en POS. Activar no inicia el worker.</li>
                    <li class="mb-3"><strong>Prepare el POS</strong> con la lista de preparación de abajo. No ejecute esos tres pasos en Profac.</li>
                    <li class="mb-3"><strong>Inicie ambos workers.</strong> Pulse Iniciar automático en ambas vistas. Confirme supervisores activos, workers en automático y ausencia de errores. No necesita procesar rondas manuales.</li>
                </ol>
                <p class="alert alert-info">En Windows con APP_ENV=local, Iniciar automático levanta el supervisor si está apagado y confirma su heartbeat. No necesita terminales ni rondas manuales. Puede cerrar el navegador; después de reiniciar Windows pulse Iniciar automático de nuevo. POS ofrece Levantar supervisor local para ejecutar la preparación inicial.</p>
            </div>
            <div class="tab-pane fade" id="paperland-vps-guide" role="tabpanel" aria-labelledby="paperland-vps-tab">
                <h4>Paso a paso en VPS Linux</h4>
                <ol>
                    <li class="mb-3"><strong>Despliegue y respalde.</strong> Ambas aplicaciones deben abrir mediante HTTPS. Respalde bases y APP_KEY. Aplique individualmente solo las migraciones nuevas de integración que falten; no ejecute todas las históricas.</li>
                    <li class="mb-3"><strong>Configure los dominios reales.</strong> Ejemplo: Profac apunta a <code>https://pos.tudominio.com</code> y POS a <code>https://profac.tudominio.com</code>. Reemplace los ejemplos por sus dominios; use la misma clave y pruebe ambos perfiles. <code>127.0.0.1</code> no comunica servidores diferentes.</li>
                    <li class="mb-3"><strong>Descargue las unidades desde las vistas desplegadas en Linux.</strong> Profac: <a href="/configuracion/api-paperland/servicio">profac-paperland.service</a>. POS, en Conexión Valencia: <code>pos-integration-pos.service</code>. Revise User, WorkingDirectory y ExecStart: usuario con permisos, carpeta real y PHP CLI correcto. Profac requiere pcntl en Linux; ambos requieren acceso a BD y almacenamiento/cache. No copie rutas Windows al VPS.</li>
                    <li class="mb-3"><strong>Instale por SSH una sola vez.</strong> Desde la carpeta de los archivos descargados, ejecute cada bloque en el servidor correspondiente. Si comparten VPS, instale ambos allí.
                        <pre class="mt-2"><code># Servidor Profac
sudo install -m 0644 profac-paperland.service /etc/systemd/system/profac-paperland.service
sudo systemctl daemon-reload
sudo systemctl enable --now profac-paperland.service
sudo systemctl status profac-paperland.service --no-pager

# Servidor POS
sudo install -m 0644 pos-integration-pos.service /etc/systemd/system/pos-integration-pos.service
sudo systemctl daemon-reload
sudo systemctl enable --now pos-integration-pos.service
sudo systemctl status pos-integration-pos.service --no-pager</code></pre>
                    </li>
                    <li class="mb-3"><strong>Active y prepare desde las vistas.</strong> Con workers pausados, pruebe y active ambos perfiles. Complete la preparación del POS de abajo y luego inicie ambos workers. Compruebe servicios active (running) y supervisores En línea.</li>
                    <li class="mb-3"><strong>Opere sin terminal abierta.</strong> systemd mantiene los supervisores y los levanta al reiniciar el VPS. Para diagnosticar:
                        <pre class="mt-2"><code>sudo journalctl -u profac-paperland.service -n 50 --no-pager
sudo journalctl -u pos-integration-pos.service -n 50 --no-pager</code></pre>
                    </li>
                </ol>
            </div>
        </div>
        <div class="alert alert-warning mt-3">
            <h4>Preparación del POS: obligatoria antes del primer inicio</h4>
            <p>Respalde ambas bases y concilie el inventario histórico Paperland: no lo sume nuevamente al espejo POS. Mantenga el worker POS pausado.</p>
            <ol>
                <li><strong>Vincular referencias:</strong> espere Completada. Usa el archivo histórico paperland.id_zenvy_valencia; sin productos locales no necesita vinculación histórica.</li>
                <li><strong>Sincronizar catálogos:</strong> resuelva las incidencias en Traslados. Unidades y factores deben ser exactos; no fuerce factor 1.</li>
                <li><strong>Publicar inventario inicial:</strong> encola saldos sin modificar cantidades.</li>
                <li><strong>Procesar una ronda de prueba:</strong> procesa mensajes reales; revise el resultado antes del inicio automático.</li>
            </ol>
            <p>Pendiente no significa ejecutada. Un fallo puede dejar cambios parciales: revise antes de repetir. El archivo histórico debe estar disponible para la vinculación. Estos pasos se ejecutan en la API del POS, no en Profac.</p>
        </div>
        <dl>
            <dt>Supervisor frente a worker</dt><dd>El supervisor atiende solicitudes y publica heartbeat. Iniciar automático también lo levanta en Windows local; en VPS solo valida el servicio instalado. Pausar worker no apaga el supervisor y guardar un perfil no inicia procesos.</dd>
            <dt>Pausar frente a deshabilitar</dt><dd>Pausar termina la ronda actual y conserva eventos. Deshabilitar suspende el registro de eventos POS; al reactivar se debe republicar inventario.</dd>
            <dt>Traslados e inventario</dt><dd>Aparecer en el listado no ingresa stock: recepción física y distribución se confirman aparte.</dd>
            <dt>Pruebas y producción</dt><dd>Use bases independientes para inventarios distintos. No cambie perfiles con mensajes o traslados pendientes.</dd>
        </dl>
    </div>
</div>
