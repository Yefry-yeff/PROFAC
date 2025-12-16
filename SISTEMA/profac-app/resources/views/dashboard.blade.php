<x-app-layout>
  <div class="wrapper wrapper-content animated fadeIn" style="padding-top: 24px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="rounded-2xl shadow-sm" style="background: #ffffff; border: 1px solid #e5e7eb;">
        <div class="p-6 lg:p-8">
          <div class="flex items-center justify-between">
            <div>
                            <h1 class="text-2xl font-semibold" style="color:#111827;">Bienvenido, {{ Auth::user()->name }}</h1>
              <p class="mt-1" style="color:#6b7280;">Tu panel está listo para comenzar</p>
            </div>
            <div class="hidden md:flex items-center gap-3">
              <div class="px-3 py-2 rounded-md" style="background: #f3f4f6; color:#111827;">
                <span id="welcome-time" class="font-semibold"></span>
              </div>
              <div class="px-3 py-2 rounded-md" style="background: #f3f4f6; color:#111827;">
                <span id="welcome-date" class="font-semibold"></span>
              </div>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl p-5" style="background: #f9fafb; border: 1px solid #e5e7eb;">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-full" style="background: #e6f5f1;">
                  <i class="fa fa-id-badge" style="color:#1ab394; font-size:20px;"></i>
                </div>
                <div>
                  <div class="text-sm" style="color:#6b7280;">Rol asignado</div>
                  <div class="text-lg font-semibold" style="color:#111827;">
                    @php
                      $roleName = null;
                      try {
                        if (method_exists(Auth::user(), 'getRoleNames')) {
                          $roleName = Auth::user()->getRoleNames()->first();
                        } elseif (method_exists(Auth::user(), 'roles')) {
                          $roleName = optional(Auth::user()->roles()->first())->name ?? optional(Auth::user()->roles()->first())->nombre;
                        } elseif (property_exists(Auth::user(), 'roles') && Auth::user()->roles) {
                          $first = is_iterable(Auth::user()->roles) ? (Auth::user()->roles[0] ?? null) : Auth::user()->roles;
                          $roleName = $first->name ?? $first->nombre ?? null;
                        } elseif (property_exists(Auth::user(), 'role') && Auth::user()->role) {
                          $roleName = Auth::user()->role->name ?? Auth::user()->role->nombre ?? null;
                        }
                      } catch (\Throwable $e) {
                        $roleName = null;
                      }
                    @endphp
                    {{ $roleName ?? 'Sin rol' }}
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-xl p-5" style="background: #f9fafb; border: 1px solid #e5e7eb;">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 flex items-center justify-center rounded-full" style="background: #e6f5f1;">
                  <i class="fa fa-clock-o" style="color:#1ab394; font-size:20px;"></i>
                </div>
                <div>
                  <div class="text-sm" style="color:#6b7280;">Fecha y hora</div>
                  <div class="text-lg font-semibold" style="color:#111827;"><span id="welcome-date-inline"></span> · <span id="welcome-time-inline"></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function updateWelcomeDateTime() {
      const now = new Date();
      const dateFormatter = new Intl.DateTimeFormat('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      const timeFormatter = new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      const dateText = dateFormatter.format(now);
      const timeText = timeFormatter.format(now);
      const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
      const prettyDate = cap(dateText);

      const elDate = document.getElementById('welcome-date');
      const elTime = document.getElementById('welcome-time');
      const elDateInline = document.getElementById('welcome-date-inline');
      const elTimeInline = document.getElementById('welcome-time-inline');
      if (elDate) elDate.textContent = prettyDate;
      if (elTime) elTime.textContent = timeText;
      if (elDateInline) elDateInline.textContent = prettyDate;
      if (elTimeInline) elTimeInline.textContent = timeText;
    }
    updateWelcomeDateTime();
    setInterval(updateWelcomeDateTime, 5 * 1000);
  </script>
</x-app-layout>

