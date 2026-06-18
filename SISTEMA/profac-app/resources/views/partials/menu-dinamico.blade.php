{{-- Menú Dinámico basado en permisos de rol --}}
@php
    use App\Http\Controllers\MenuHelper;
    $menusUsuario = MenuHelper::getMenusUsuario();
@endphp

@foreach($menusUsuario as $menu)
    @php
        $menuActivo = $menu->submenus->contains(function($s) {
            $url = ltrim($s->url, '/');
            return request()->is($url) || request()->is($url . '/*');
        });
    @endphp
    <li class="{{ $menuActivo ? 'active' : '' }}">
        <a href="#">
            <i class="{{ $menu->icon }}" style="color:#ffffff;"></i>
            <span class="nav-label" style="color:#ffffff;">{{ $menu->nombre_menu }}</span>
            <span class="fa arrow"></span>
        </a>
        <ul class="nav nav-second-level {{ $menuActivo ? 'in' : '' }}">
            @foreach($menu->submenus as $submenu)
                @php
                    $url = ltrim($submenu->url, '/');
                    $submenuActivo = request()->is($url) || request()->is($url . '/*');
                @endphp
                <li class="{{ $submenuActivo ? 'active' : '' }}">
                    <a href="/{{ $submenu->url }}" style="color:#ffffff;">
                        @if($submenu->icono)
                            <i class="{{ $submenu->icono }}"></i>
                        @endif
                        {{ $submenu->nombre }}
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@endforeach

{{-- ── Sección Configuración: solo para Administrador ── --}}
@if(optional(Auth::user()->rol)->nombre === 'Administrador' || Auth::user()->rol_id == 1)
@php
    $configActivo = request()->is('configuracion/*');
@endphp
<li class="{{ $configActivo ? 'active' : '' }}">
    <a href="#">
        <i class="fa fa-cog" style="color:#ffffff;"></i>
        <span class="nav-label" style="color:#ffffff;">Configuración</span>
        <span class="fa arrow"></span>
    </a>
    <ul class="nav nav-second-level {{ $configActivo ? 'in' : '' }}">
        <li class="{{ request()->routeIs('configuracion.notificaciones.flujo') ? 'active' : '' }}">
            <a href="{{ route('configuracion.notificaciones.flujo') }}" style="color:#ffffff;">
                <i class="fa fa-bell"></i> Notificaciones
            </a>
        </li>
        <li class="{{ request()->routeIs('configuracion.codigos.autorizacion') ? 'active' : '' }}">
            <a href="{{ route('configuracion.codigos.autorizacion') }}" style="color:#ffffff;">
                <i class="fa fa-key"></i> Códigos de Autorización
            </a>
        </li>
    </ul>
</li>
@endif
