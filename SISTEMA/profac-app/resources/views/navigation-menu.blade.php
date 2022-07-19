<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky" >
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i></a>

    <!-- Primary Navigation Menu -->
    <div class=" px-4 sm:px-6 lg:px-8 " style="width:100vw" >

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
                        {{ __('DISTRIBUCIONES VALENCIA') }}
                    </x-jet-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Teams Dropdown -->
                {{-- @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())

                    <div class="ml-3 relative">
                        <x-jet-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition">
                                        {{ Auth::user()->currentTeam->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-jet-dropdown-link
                                        href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-jet-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-jet-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-jet-dropdown-link>
                                    @endcan

                                    <div class="border-t border-gray-100"></div>

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-jet-switchable-team :team="$team" />
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-jet-dropdown>
                    </div>

                @endif --}}

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

                                <x-jet-dropdown-link href="{{ route('logout') }}" onclick="event.preventDefault();
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
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
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
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}"
                    :active="request()->routeIs('profile.show')">
                    {{ __('Perfil') }}
                </x-jet-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-jet-responsive-nav-link href="{{ route('api-tokens.index') }}"
                        :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-jet-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-jet-responsive-nav-link href="{{ route('logout') }}" onclick="event.preventDefault();
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
    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        {{-- <img alt="image"  class="rounded-circle" src="" h-8 w-8 rounded-full object-cover/> --}}

                        <img class="rounded-circle" style="max-width: 3.5rem"
                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                            alt="{{ Auth::user()->name }}" />

                        <div data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs font-bold" style="color:#FFF;"><b>
                                    {{ Auth::user()->name }}</b></span>
                            <span class="text-muted text-xs block">Desarrollador <b class="caret"></b></span>
                        </div>
                        <!-- <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a class="dropdown-item" href="profile.html">Profile</a></li>
                                <li><a class="dropdown-item" href="contacts.html">Contacts</a></li>
                                <li><a class="dropdown-item" href="mailbox.html">Mailbox</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="login.html">Logout</a></li>
                            </ul> -->
                    </div>
                    <div class="logo-element">
                        IN+
                    </div>
                </li>
                <li>
                    <a href="{{ route('dashboard') }}"><i class="fa fa-area-chart" style="color:#ffffff;"
                            aria-hidden="true"></i> <span class="nav-label" style="color:#ffffff;">Dashboard</span>
                        </a>
                    {{-- <ul class="nav nav-second-level">
                            <li href="dashboard_2.html"><a href="index.html">Gestiones</a></li>
                            <li><a href="dashboard_2.html">Reportes de Usuario</a></li>
                        </ul> --}}
                </li>
                <li>
                    <a href="index.html"><i class="fa-solid fa-user" style="color:#ffffff;"></i> <span
                            class="nav-label" style="color:#ffffff;">Usuarios</span>
                        <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li href="dashboard_2.html"><a href="/usuarios" style="color:#ffffff;">Lista de Usuarios</a>
                        </li>
                        {{-- <li><a href="dashboard_2.html " style="color:#ffffff;">Reportes de Usuario</a></li> --}}
                    </ul>
                </li>
                <li>
                    <a href="index.html"><i class="fa-solid fa-warehouse" style="color:#ffffff;"></i> <span
                            class="nav-label" style="color:#ffffff;">Bodega</span>
                        <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/bodega" style="color:#ffffff;">Crear Bodega</a></li>
                        <li><a href="/bodega/editar/screen" style="color:#ffffff;">Editar Bodega</a></li>
                    </ul>
                </li>
                <li>
                    <a href="index.html"><i class="fa-solid fa-dolly " style="color:#ffffff;"></i><span
                            class="nav-label" style="color:#ffffff;">Proveedores</span> <span
                            class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/proveedores" style="color:#ffffff;">Registrar Proveedor</a></li>
                        <li><a href="/inventario/retenciones" style="color:#ffffff;">Crear Retenciones</a></li>
                    </ul>
                </li>


                <li>
                    <a href="index.html"><i class="fa-solid fa-cubes" style="color:#ffffff;" >
                    </i><span  class="nav-label" style="color:#ffffff;">Inventario</span>
                    <span class="fa arrow"></span></a>

                    <ul class="nav nav-second-level">
                        <li><a href="/marca/producto" style="color:#ffffff;">Marcas de productos</a></li>
                        <li><a href="/producto/registro" style="color:#ffffff;">Registro y Detalle de Producto</a></li>
                        <li><a href="/producto/compra" style="color:#ffffff;">Comprar Producto</a></li>
                        <li><a href="/producto/listar/compras" style="color:#ffffff;">Listar Compras</a></li>
                        <li><a href="/inventario/translado" style="color:#ffffff;">Translado de Producto</a></li> 
                        <li><a href="/inventario/unidades/medida" style="color:#ffffff;">Unidades de Medida</a></li>

                        {{-- <li><a href="dashboard_2.html" style="color:#ffffff;">Categorias y Unidades de Medida</a></li> --}}
                    </ul>

                </li>
                <li>
                    <a href="index.html"><i class="fa-solid fa-users" style="color:#ffffff;"></i> <span
                            class="nav-label" style="color:#ffffff;">Clientes</span>
                        <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/clientes" style="color:#ffffff;">Registrar cliente</a></li>
                    </ul>
                </li>

                <li>
                    <a href="index.html"><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span
                            class="nav-label" style="color:#ffffff;">Ventas Corporativas</span>
                        <span class="fa arrow"></span></a>

                    <ul class="nav nav-second-level">
                        <li><a href="/ventas/coporativo" style="color:#ffffff;">Facturacion</a></li>
                        <li><a href="/proforma/cotizacion/1" style="color:#ffffff;">Cotización </a></li>
                        <li><a href="/facturas/corporativo" style="color:#ffffff;">Listado de Facturas</a></li> 
                        <li><a href="/facturas/corporativo/vendedor" style="color:#ffffff;">Listado de Facturas</a></li> 
                        <li><a href="/ventas/Configuracion" style="color:#ffffff;">Configuración</a></li>
                        @if(Auth::user()->id =='2' || Auth::user()->id=='3' )  
                        <li><a href="/ventas/listado/comparacion" style="color:#ffffff;">Listado de Declaraciones</a></li> 
                        <li><a href="/ventas/seleccionar" style="color:#ffffff;">Seleccionar Declaraciones</a></li>
                        @endif
                        {{-- <li><a href="/facturas" style="color:#ffffff;">Vales</a></li> --}}
                    </ul>
                </li>

                <li>
                    <a href="index.html"><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span
                            class="nav-label" style="color:#ffffff;">Ventas Gobierno</span>
                        <span class="fa arrow"></span></a>

                    <ul class="nav nav-second-level">
                        <li><a href="/ventas/estatal" style="color:#ffffff;">Facturacion</a></li>
                        <li><a href="/proforma/cotizacion/2" style="color:#ffffff;">Cotización </a></li>
                        <li><a href="/facturas/estatal" style="color:#ffffff;">Listado de Facturas</a></li> 
                        <li><a href="/ventas/estatal/vendedor" style="color:#ffffff;">Listado de Facturas</a></li>
                        {{-- <li><a href="/facturas" style="color:#ffffff;">Vales</a></li> --}}
                    </ul>
                </li>



                <li>
                    <a href="index.html"><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span
                            class="nav-label" style="color:#ffffff;">Ventas exoneradas</span>
                        <span class="fa arrow"></span></a>

                    <ul class="nav nav-second-level">
                        <li><a href="/ventas/exonerado/factura" style="color:#ffffff;">Facturacion</a></li>
                        <li><a href="/exonerado/ventas/lista" style="color:#ffffff;">Listado de Facturas</a></li>
                        
                        {{-- <li><a href="/facturas" style="color:#ffffff;">Vales</a></li> --}}
                    </ul>
                </li>

                {{-- <li>
                    <a href="index.html"><i class="fa-solid fa-file-invoice" style="color:#ffffff;"></i><span
                            class="nav-label" style="color:#ffffff;">Cotizaciones</span>
                        <span class="fa arrow"></span></a>

                    <ul class="nav nav-second-level">
                        <li><a href="/proforma/cotizacion/corporativo" style="color:#ffffff;">Cotización </a></li>
                        <li><a href="/proforma/cotizacion/corporativo" style="color:#ffffff;">Lista de Cotizaciones </a></li>
                        
                        
                       
                    </ul>
                </li> --}}


            </ul>

        </div>
    </nav>




</nav>
