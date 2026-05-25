{{-- Menú Dinámico basado en permisos de rol --}}
@php
    use App\Http\Controllers\MenuHelper;
    $menusUsuario = MenuHelper::getMenusUsuario();
@endphp

@foreach($menusUsuario as $menu)
    <li>
        <a href="#">
            <i class="{{ $menu->icon }}" style="color:#ffffff;"></i>
            <span class="nav-label" style="color:#ffffff;">{{ $menu->nombre_menu }}</span>
            <span class="fa arrow"></span>
        </a>
        <ul class="nav nav-second-level">
            @foreach($menu->submenus as $submenu)
                <li>
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
<li>
    <a href="#">
        <i class="fa fa-cog" style="color:#ffffff;"></i>
        <span class="nav-label" style="color:#ffffff;">Configuración</span>
        <span class="fa arrow"></span>
    </a>
    <ul class="nav nav-second-level">
        <li>
            <a href="{{ route('configuracion.notificaciones.flujo') }}" style="color:#ffffff;">
                <i class="fa fa-bell"></i> Notificaciones
            </a>
        </li>
    </ul>
</li>
@endif
