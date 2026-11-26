{{-- Menú Dinámico basado en permisos de rol --}}
@php
    use App\Http\Controllers\MenuHelper;
    $menusUsuario = MenuHelper::getMenusUsuario();
    $menusConfiguracion = $menusUsuario->filter(fn ($menu) =>
        in_array(mb_strtolower(trim($menu->nombre_menu)), ['configuración', 'configuracion'], true)
    );
    $submenusConfiguracion = $menusConfiguracion->flatMap(fn ($menu) => $menu->submenus);
    $menusUsuario = $menusUsuario->diff($menusConfiguracion);
    $esAdministrador = Auth::user()->esAdministrador();
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

@if($esAdministrador || $submenusConfiguracion->isNotEmpty())
@php
    $configActivo = request()->is('configuracion/*') || $submenusConfiguracion->contains(function ($submenu) {
        $url = ltrim($submenu->url, '/');
        return request()->is($url) || request()->is($url . '/*');
    });
@endphp
<li class="{{ $configActivo ? 'active' : '' }}">
    <a href="#">
        <i class="fa fa-cog" style="color:#ffffff;"></i>
        <span class="nav-label" style="color:#ffffff;">Configuración</span>
        <span class="fa arrow"></span>
    </a>
    <ul class="nav nav-second-level {{ $configActivo ? 'in' : '' }}">
        @if($esAdministrador)
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
        @endif
        @foreach($submenusConfiguracion as $submenu)
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
@endif
