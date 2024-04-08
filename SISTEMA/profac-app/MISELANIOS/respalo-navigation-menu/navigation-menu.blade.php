<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky" style=" ">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i></a>

    <!-- Primary Navigation Menu -->
    <div class=" px-4 sm:px-6 lg:px-8 " style="width:100vw">

        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        {{-- <x-jet-application-mark class="block h-9 w-auto" /> --}}
                        <img class="animate__animated animate__bounceIn  rounded-full object-cover " style="width:5rem"
                            src="{{ asset('img/LOGO_VALENCIA.jpg') }}" />
                    </a>

                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                       <b>{{ __('DISTRIBUCIONES VALENCIA  - DESARROLLO - V2.2.6') }}</b>
                    </x-jet-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Teams Dropdown -->

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-jet-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                        alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Administracion de cuenta') }}
                            </div>

                            <x-jet-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Perfil') }}
                            </x-jet-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-jet-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-jet-dropdown-link href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Cerrar Sesion') }}
                                </x-jet-dropdown-link>
                            </form>
                        </x-slot>
                    </x-jet-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-jet-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-jet-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                            alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Perfil') }}
                </x-jet-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-jet-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-jet-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-jet-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Salir') }}
                    </x-jet-responsive-nav-link>
                </form>

                <!-- Team Management -->
                {{-- @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-gray-200"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-jet-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}"
                        :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-jet-responsive-nav-link> --}}

                {{-- @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-jet-responsive-nav-link href="{{ route('teams.create') }}"
                            :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-jet-responsive-nav-link>
                    @endcan

                    <div class="border-t border-gray-200"></div>

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Switch Teams') }}
                    </div>

                    @foreach (Auth::user()->allTeams() as $team)
                        <x-jet-switchable-team :team="$team" component="jet-responsive-nav-link" />
                    @endforeach
                @endif --}}
            </div>
        </div>
    </div>

    <!---menu lateral de la plantilla--->
    <style>
        @media screen and (min-width: 600px) {
            .scroll-bar-sidebar {
                overflow-y: auto;
                overflow-x: hidden;
                max-height: 92.7vh
            }
        }
    </style>

    <nav class="navbar-default navbar-static-side " role="navigation">
        <div class="sidebar-collapse ">
            <ul class="nav metismenu scroll-bar-sidebar" id="side-menu" style="">
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        {{-- <img alt="image"  class="rounded-circle" src="" h-8 w-8 rounded-full object-cover/> --}}

                        <img class="rounded-circle" style="max-width: 3.5rem"
                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                            alt="{{ Auth::user()->name }}" />

                        <div data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs font-bold" style="color:#FFF;"><b>
                                    {{ Auth::user()->name }}</b></span>
                            @php
                                $rol = DB::SELECTONE('select nombre from rol where id = ' . Auth::user()->rol_id);
                            @endphp
                            <span class="text-muted text-xs block">{{ $rol->nombre }} <b class="caret"></b></span>
                        </div>
                        <!-- <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                        <li><a class="dropdown-item" href="profile.html">Profile</a></li>
                                        <li><a class="dropdown-item" href="contacts.html">Contacts</a></li>s
                                        <li><a class="dropdown-item" href="mailbox.html">Mailbox</a></li>
                                        <li class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="login.html">Logout</a></li>
                                    </ul> -->
                    </div>
                    <div class="logo-element">
                        IN+
                    </div>
                </li>

                <!---------------------------------------------------MENU DE NAVEGACION----------------------------------------------------------------------------------->

                    <!-----------------------------------Administrador------------------------------->

                @if (Auth::user()->rol_id == '1')
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fa fa-area-chart" style="color:#ffffff;"
                                aria-hidden="true"></i> <span class="nav-label" style="color:#ffffff;">Dashboard</span>
                        </a>

                    <li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label"
                                style="color:#ffffff;">Usuarios</span>
                            <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li href="dashboard_2.html"><a href="/usuarios" style="color:#ffffff;">Lista de
                                    Usuarios</a>
                            </li>
                            {{-- <li><a href="dashboard_2.html " style="color:#ffffff;">Reportes de Usuario</a></li> --}}
                        </ul>
                    </li>
                @endif
                @if (Auth::user()->rol_id == '2' or Auth::user()->rol_id == '1')
                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">Vendedores</span>
                    </a>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            {{--  <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>  --}}
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/proforma/cotizacion/2" style="color:#ffffff;">Cotización </a></li>
                            <li><a href="/cotizacion/listado/estatal" style="color:#ffffff;">Listado de Cotizaciones</a></li>
                        </ul>
                    </li>

                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>
                            <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/proforma/cotizacion/1" style="color:#ffffff;">Cotización </a></li>
                                <li><a href="/cotizacion/listado/corporativo" style="color:#ffffff;">Listado de Cotizaciones</a></li>
                            </ul>
                    </li>


                    <li>
                        <a><i class="fa-solid fa-magnifying-glass-dollar" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Comisiones</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/comisiones/vendedor" style="color:#ffffff;">Comisiones Colaborador</a></li>
                        </ul>
                    </li>

                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Inventario</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/producto/registro" style="color:#ffffff;">Catálogo</a></li>
                        </ul>
                    </li>

                @endif

                @if (Auth::user()->rol_id == '3' or Auth::user()->rol_id == '1')

                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">Facturadores</span></a>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/ventas/estatal" style="color:#ffffff;">Facturacion</a></li>
                            <li><a href="/ventas/sin/restriccion/gobierno" style="color:#ffffff;">FacturacionSR/Clientes A</a></li>
                            <li><a href="/facturas/estatal" style="color:#ffffff;">Listado de Facturas Clientes A</a> </li>
                            <li><a href="/ventas/anulado/estatal" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                            <li><a href="/proforma/cotizacion/2" style="color:#ffffff;">Cotización </a></li>
                            <li><a href="/cotizacion/listado/estatal" style="color:#ffffff;">Listado de Cotizaciones</a></li>
                            <li><a href="/estatal/ordenes" style="color:#ffffff;">Orden de Compra</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>
                            <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/ventas/coporativo" style="color:#ffffff;">Facturacion</a></li>
                                <li><a href="/ventas/sin/restriccion/precio" style="color:#ffffff;">Facturacion SR/P</a></li>
                                <li><a href="/facturas/corporativo/lista" style="color:#ffffff;">Listado de FacturasClientes B</a></li>
                                <li><a href="/ventas/anulado/corporativo" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                                <li><a href="/proforma/cotizacion/1" style="color:#ffffff;">Cotización </a></li>
                                <li><a href="/cotizacion/listado/corporativo" style="color:#ffffff;">Listado de Cotizaciones</a></li>
                                <li><a href="/ventas/coorporativo/orden/compra" style="color:#ffffff;">Orden de compra</a></li>
                            </ul>
                    </li>


                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Ventas exoneradas</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/ventas/exonerado/factura" style="color:#ffffff;">Facturacion</a></li>
                            <li><a href="/exonerado/ventas/lista" style="color:#ffffff;">Listado de Facturas</a></li>
                            <li><a href="/ventas/anulado/exonerado" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                            <li><a href="/estatal/exonerado" style="color:#ffffff;">Registro Exonerado</a></li>
                        </ul>
                    </li>

                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Precios</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/ventas/historico_precios_cliente" style="color:#ffffff;">Historico de Precios</a></li>
                        </ul>
                    </li>

                    <li>
                        <a><i class="fa-solid fa-check-to-slot" style="color:#ffffff"></i> </i><span
                                class="nav-label" style="color:#ffffff;">Comprobante de entrega</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            {{-- <li><a href="/comprobante/entrega" style="color:#ffffff;">Crear Comprobante</a></li> --}}
                            <li><a href="/comprovante/entrega/listado" style="color:#ffffff;">Listado de Comprobantes</a></li>
                            <li><a href=" /comprovante/entrega/anulados" style="color:#ffffff;">Listado de Anulados</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Inventario</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/producto/registro" style="color:#ffffff;">Catálogo</a></li>
                        </ul>
                    </li>



                @endif

                @if (Auth::user()->rol_id == '4' or Auth::user()->rol_id == '1')
                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">Contabilidad</span></a>
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fa fa-area-chart" style="color:#ffffff;" aria-hidden="true"></i> <span class="nav-label" style="color:#ffffff;">Dashboard</span></a>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-users" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Clientes</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/clientes" style="color:#ffffff;">Registrar cliente</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-building-columns" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Bancos</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">

                            <li><a href="/banco/bancos" style="color:#ffffff;">Bancos</a></li>

                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Boleta de compra</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="https://cadss.hn/boleta/blta_listar_boletas.php"
                                    style="color:#ffffff;">Gestión de Boleta</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a><i class="fa-solid fa-arrow-right-arrow-left text-white"></i>
                            <span class="nav-label" style="color:#ffffff;">Nota de crédito</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">

                            <li><a href="/nota/credito" style="color:#ffffff;">Crear devolución</a></li>
                            <li><a href="/nota/credito/listado" style="color:#ffffff;">Listado de notas de credito Clientes A</a>
                            <li><a href="/nota/credito/gobierno" style="color:#ffffff;">Listado de notas de credito Clientes B</a>
                            <li><a href="/ventas/motivo_credito" style="color:#ffffff;">Motivo Nota de Crédito</a>
                            </li>

                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label"style="color:#ffffff;">Nota de débito</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/debito" style="color:#ffffff;">Gestiones</a></li>
                            <li><a href="/nota/debito/lista/gobierno" style="color:#ffffff;">Listado notas debito Clientes A</a></li>
                            <li><a href="/nota/debito/lista" style="color:#ffffff;">Listado notas debito Clientes B</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-magnifying-glass-dollar" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Cuentas por cobrar</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/cuentas/por/cobrar/listado" style="color:#ffffff;">Listado de Facturas</a></li>
                            <li><a href="/ventas/cuentas_por_cobrar" style="color:#ffffff;">Cuentas Por Cobrar</a></li>
                            <li><a href="/cuentas_por_cobrar/pagos" style="color:#ffffff;">Aplicacion de Pagos</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Cierre Diario</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/cierre/caja" style="color:#ffffff;">Detalle de cierre</a></li>
                            <li><a href="/cierre/historico" style="color:#ffffff;">Historico de cierre</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Reportes</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/facturaDia" style="color:#ffffff;">Reporte de ventas</a>
                            <li><a href="/reporte/reporteria" style="color:#ffffff;">Reportes Varios</a>
                        </ul>
                    </li>
                @endif

                @if (Auth::user()->rol_id == '5' or Auth::user()->rol_id == '1')
                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">Auxiliar Administrativo</span></a>
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fa fa-area-chart" style="color:#ffffff;" aria-hidden="true"></i> <span class="nav-label" style="color:#ffffff;">Dashboard</span></a>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-warehouse" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Bodega</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/bodega" style="color:#ffffff;">Crear Bodega</a></li>
                            <li><a href="/bodega/editar/screen" style="color:#ffffff;">Editar Bodega</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-dolly " style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Proveedores</span> <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/proveedores" style="color:#ffffff;">Registrar Proveedor</a></li>
                            <li><a href="/inventario/retenciones" style="color:#ffffff;">Crear Retenciones</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            {{--  <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>  --}}
                            <span class="nav-label" style="color:#ffffff;">CAI - Configuracion</span>
                            <span class="fa arrow"></span>
                            <ul class="nav nav-second-level">
                                <li><a href="/ventas/cai" style="color:#ffffff;">CAI</a></li>
                            </ul>
                        </a>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Inventario</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/marca/producto" style="color:#ffffff;">Marcas de productos</a></li>
                            <li><a href="/producto/registro" style="color:#ffffff;">Catálogo de productos</a>
                            </li>
                            <li><a href="/inventario/unidades/medida" style="color:#ffffff;">Unidades de Medida</a>
                            </li>
                            <li><a href="/producto/compra" style="color:#ffffff;">Comprar Producto</a></li>
                            <li><a href="/producto/listar/compras" style="color:#ffffff;">Listar Compras</a></li>
                            <li><a href="/categoria/categorias" style="color:#ffffff;">Categorias</a></li>
                            <li><a href="/sub_categoria/sub_categorias" style="color:#ffffff;">Sub-Categoria</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Traslado de productos</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/inventario/translado" style="color:#ffffff;">Traslado de Producto</a></li>
                            <li><a href="/translados/historial" style="color:#ffffff;">Historial de traslados</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-box-open" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ajustes</span>
                            <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/inventario/ajustes" style="color:#ffffff;">Realizar Ajustes</a></li>
                            <li><a href="/inventario/ajuste/ingreso" style="color:#ffffff;">Ingresar Producto</a></li>
                            <li><a href="/listado/ajustes" style="color:#ffffff;">Historial de Ajustes</a></li>
                            <li><a href="/inventario/tipoajuste" style="color:#ffffff;">Motivos de Ajuste</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Compras Locales</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="https://cadss.hn/orden/ordn_listar_ordenes.php" style="color:#ffffff;">Orden de compra local</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Boleta de Compra</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="https://cadss.hn/boleta/blta_listar_boletas.php" style="color:#ffffff;">Gestión de Boleta</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/facturas/estatal" style="color:#ffffff;">Listado de Facturas Clientes A</a> </li>
                            <li><a href="/ventas/anulado/estatal" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>
                            <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/facturas/corporativo/lista" style="color:#ffffff;">Listado de FacturasClientes B</a></li>
                                <li><a href="/ventas/anulado/corporativo" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                            </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-clipboard-check" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Declaraciones </span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">


                            <li><a href="/ventas/Configuracion" style="color:#ffffff;">Configuración</a></li>
                            <li><a href="/ventas/listado/comparacion" style="color:#ffffff;">Listado de Declaraciones</a></li>
                            <li><a href="/ventas/seleccionar" style="color:#ffffff;">Seleccionar Declaraciones</a></li>


                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-truck-fast" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Cardex</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/cardex" style="color:#ffffff;">Gestionar cardex</a></li>
                            <li><a href="/cardex/general" style="color:#ffffff;">Cardex general</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-check-to-slot" style="color:#ffffff"></i> </i><span class="nav-label" style="color:#ffffff;">Comprobante De Entrega</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/comprobante/entrega" style="color:#ffffff;">Crear Comprobante</a></li>
                            <li><a href="/comprovante/entrega/listado" style="color:#ffffff;">Listado de Comprobantes</a></li>
                            <li><a href=" /comprovante/entrega/anulados" style="color:#ffffff;">Listado de Anulados</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-truck-medical" style="color:#ffffff"></i><span class="nav-label" style="color:#ffffff;">Entregas Agendadas</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href=" /listar/vale/entrega" style="color:#ffffff;">Listado de Entregas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Vale</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            {{-- <li><a href="/vale/listado/facturas" style="color:#ffffff;">Agregar vale a factura</a>
                            </li> --}}
                            <li><a href="/vale/restar/inventario" style="color:#ffffff;">Lista de vales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Precios</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/ventas/historico_precios_cliente" style="color:#ffffff;">Historico de Precios</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Reportes</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/facturaDia" style="color:#ffffff;">Reporte de ventas</a>
                        </ul>
                    </li>
                @endif

                @if (Auth::user()->rol_id == '7' or Auth::user()->rol_id == '1')
                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">Auditoria y logistica</span></a>

                    <li>
                        <a><i class="fa-solid fa-warehouse" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Bodega</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/bodega" style="color:#ffffff;">Crear Bodega</a></li>
                            <li><a href="/bodega/editar/screen" style="color:#ffffff;">Editar Bodega</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-dolly " style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Proveedores</span> <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/proveedores" style="color:#ffffff;">Registrar Proveedor</a></li>
                            <li><a href="/inventario/retenciones" style="color:#ffffff;">Crear Retenciones</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Inventario</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/marca/producto" style="color:#ffffff;">Marcas de productos</a></li>
                            <li><a href="/producto/registro" style="color:#ffffff;">Catálogo de productos</a></li>
                            <li><a href="/inventario/unidades/medida" style="color:#ffffff;">Unidades de Medida</a>
                            </li>
                            <li><a href="/producto/compra" style="color:#ffffff;">Comprar Producto</a></li>
                            <li><a href="/producto/listar/compras" style="color:#ffffff;">Listar Compras</a></li>
                            <li><a href="/categoria/categorias" style="color:#ffffff;">Categorias</a></li>
                            <li><a href="/sub_categoria/sub_categorias" style="color:#ffffff;">Sub-Categoria</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-cubes" style="color:#ffffff;">
                            </i><span class="nav-label" style="color:#ffffff;">Traslado de productos</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/inventario/translado" style="color:#ffffff;">Traslado de Producto</a></li>
                            <li><a href="/translados/historial" style="color:#ffffff;">Historial de traslados</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-box-open" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ajustes</span>
                            <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/inventario/ajustes" style="color:#ffffff;">Realizar Ajustes</a></li>
                            <li><a href="/inventario/ajuste/ingreso" style="color:#ffffff;">Ingresar Producto</a></li>
                            <li><a href="/listado/ajustes" style="color:#ffffff;">Historial de Ajustes</a></li>
                            <li><a href="/inventario/tipoajuste" style="color:#ffffff;">Motivos de Ajuste</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Compras Locales</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="https://cadss.hn/orden/ordn_listar_ordenes.php" style="color:#ffffff;">Orden de compra local</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/facturas/estatal" style="color:#ffffff;">Listado de Facturas Clientes A</a> </li>
                            <li><a href="/ventas/anulado/estatal" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>
                            <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/facturas/corporativo/lista" style="color:#ffffff;">Listado de FacturasClientes B</a></li>
                                <li><a href="/ventas/anulado/corporativo" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                            </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Vale</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            {{-- <li><a href="/vale/listado/facturas" style="color:#ffffff;">Agregar vale a factura</a>
                            </li> --}}
                            <li><a href="/vale/restar/inventario" style="color:#ffffff;">Lista de vales</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-truck-fast" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Cardex</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/cardex" style="color:#ffffff;">Gestionar cardex</a></li>
                            <li><a href="/cardex/general" style="color:#ffffff;">Cardex general</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-check-to-slot" style="color:#ffffff"></i> </i><span class="nav-label" style="color:#ffffff;">Comprobante De Entrega</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/comprobante/entrega" style="color:#ffffff;">Crear Comprobante</a></li>
                            <li><a href="/comprovante/entrega/listado" style="color:#ffffff;">Listado de Comprobantes</a></li>
                            <li><a href=" /comprovante/entrega/anulados" style="color:#ffffff;">Listado de Anulados</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-truck-medical" style="color:#ffffff"></i><span class="nav-label" style="color:#ffffff;">Entregas Agendadas</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href=" /listar/vale/entrega" style="color:#ffffff;">Listado de Entregas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Vale</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            {{-- <li><a href="/vale/listado/facturas" style="color:#ffffff;">Agregar vale a factura</a>
                            </li> --}}
                            <li><a href="/vale/restar/inventario" style="color:#ffffff;">Lista de vales</a></li>
                        </ul>
                    </li>
                @endif

                @if (Auth::user()->rol_id == '8' or Auth::user()->rol_id == '1')
                    <a><i  style="color:#ffffff;"></i><span style="color:#ffffff;">RRHH</span></a>
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="fa fa-area-chart" style="color:#ffffff;" aria-hidden="true"></i> <span class="nav-label" style="color:#ffffff;">Dashboard</span></a>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label" style="color:#ffffff;">Reportes</span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/facturaDia" style="color:#ffffff;">Reporte de ventas</a>
                            <li ><a href="/reporte/comision" style="color:#ffffff;">Reporte comisiones</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Boleta de Compra</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="https://cadss.hn/boleta/blta_listar_boletas.php" style="color:#ffffff;">Gestión de Boleta</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-list-check" style="color:#ffffff;"></i><span class="nav-label"
                                style="color:#ffffff;">Compras Locales</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">

                            <li><a href="https://cadss.hn/orden/ordn_listar_ordenes.php" style="color:#ffffff;">Listar Boleta</a></li>

                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes A</span>
                            <span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/facturas/estatal" style="color:#ffffff;">Listado de Facturas Clientes A</a> </li>
                            <li><a href="/ventas/anulado/estatal" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i>
                            <span class="nav-label" style="color:#ffffff;">Ventas Clientes B</span>
                            <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/facturas/corporativo/lista" style="color:#ffffff;">Listado de FacturasClientes B</a></li>
                                <li><a href="/ventas/anulado/corporativo" style="color:#ffffff;">Listado de Facturas Anuladas</a></li>
                            </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label"
                                style="color:#ffffff;">Nota de débito</span>
                            <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/debito" style="color:#ffffff;">Gestiones</a></li>
                            <li><a href="/nota/debito/lista/gobierno" style="color:#ffffff;">Listado notas debito Clientes A</a></li>
                            <li><a href="/nota/debito/lista" style="color:#ffffff;">Listado notas debito Clientes B</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span class="nav-label"
                                style="color:#ffffff;">Cierre Diario</span>
                            <span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="/cierre/caja" style="color:#ffffff;">Detalle de cierre</a></li>
                            <li><a href="/cierre/historico" style="color:#ffffff;">Historico de cierre</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-magnifying-glass-dollar" style="color:#ffffff;"></i><span class="nav-label"style="color:#ffffff;">Comisiones</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/comisiones/gestion" style="color:#ffffff;">Gestion inicial</a></li>
                            <li><a href="/comisiones" style="color:#ffffff;">Gestión de comisiones</a></li>
                            <li><a href="/comisiones/vendedor" style="color:#ffffff;">Comisiones Colaborador</a></li>
                            <li><a href="/comisiones/historico" style="color:#ffffff;">Hstórico de comisiones</a></li>
                        </ul>
                    </li>
                    <li>
                        <a><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span class="nav-label" style="color:#ffffff;">Precios</span><span class="fa arrow"></span></a>

                        <ul class="nav nav-second-level">
                            <li><a href="/ventas/historico_precios_cliente" style="color:#ffffff;">Historico de Precios</a></li>
                        </ul>
                    </li>
                @endif


        </div>
    </nav>





</nav>
