<nav x-data="{ open: false }" class="sticky bg-white border-b border-gray-100" style=" ">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i></a>

    <!-- Primary Navigation Menu -->
    <div class="px-4 sm:px-6 lg:px-8" style="width:100vw">

        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('dashboard') }}">
                        {{-- <x-jet-application-mark class="block w-auto h-9" /> --}}
                        <img class="object-cover rounded-full animate__animated animate__bounceIn " style="width:5rem"
                            src="{{ asset('img/LOGO_VALENCIA.jpg') }}" />
                    </a>

                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                       <b>{{ __('PROCADSS v5.0.0.1') }}</b>
                    </x-jet-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6 profile-area">
                <!-- Teams Dropdown -->

                <!-- Settings Dropdown -->
                <div class="relative ml-3">
                    <x-jet-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm transition border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300">
                                    @if (Auth::user()->profile_photo_path && file_exists(public_path('storage/' . Auth::user()->profile_photo_path)))
                                        <img class="object-cover w-8 h-8 rounded-full"
                                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                            alt="{{ Auth::user()->name }}" />
                                        <!-- Inicial visible solo en móvil -->
                                        <span class="mobile-initial-avatar" aria-hidden="true">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
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
            <div class="flex items-center -mr-2 sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
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
                    <div class="mr-3 shrink-0">
                        @if (Auth::user()->profile_photo_path && file_exists(public_path('storage/' . Auth::user()->profile_photo_path)))
                            <img class="object-cover w-10 h-10 rounded-full"
                                src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                alt="{{ Auth::user()->name }}" />
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold text-lg">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                @endif

                <div>
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
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
        /* ====== Header mobile layout: center logo, align buttons ====== */
        @media (max-width: 768px) {
            /* Make header row positioning context */
            nav .flex.justify-between.h-16 { position: relative; }

            /* Center logo block */
            nav .flex.justify-between.h-16 > .flex {
                width: 100%;
                justify-content: center;
            }

            /* Hide Jetstream hamburger (we'll use the sidebar toggle button) */
            nav .flex.items-center.-mr-2.sm\:hidden { display: none !important; }

            /* Use the existing sidebar toggle button on the left */
            nav .navbar-minimalize {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                display: inline-flex !important;
                z-index: 10;
                background: #1ab394 !important; /* Verde original */
                color: #ffffff !important;
                border: none !important;
                width: 40px; height: 40px;
                align-items: center; justify-content: center;
                padding: 0 !important;
                line-height: 1;
                border-radius: 6px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.08);
            }
            nav .navbar-minimalize i.fa { font-size: 20px; color: #ffffff !important; }

            /* Show profile avatar/initial on the right in mobile */
            nav .profile-area {
                display: flex !important;
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                margin-left: 0;
            }

            /* Force initial-only on mobile even if photo exists */
            nav .profile-area img { display: none !important; }
            nav .profile-area .mobile-initial-avatar {
                display: inline-flex !important;
                width: 32px; height: 32px;
                border-radius: 9999px;
                background: #e5e7eb; /* gray-200 */
                color: #374151; /* gray-700 */
                font-weight: 700;
                align-items: center; justify-content: center;
            }
        }
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
                    <div class="logo-element">
                        IN+
                    </div>
                </li>

                {{-- Cuadro de búsqueda en sidebar --}}
                <li class="search-sidebar" style="padding: 15px 20px;">
                    <div class="input-group">
                        <input type="text" id="menu-search" class="form-control" placeholder="Buscar en menú..." 
                               style="background: #ffffff; border: 1px solid #e7eaec; color: #333; border-radius: 4px;">
                        <span class="input-group-addon" style="background: #ffffff; border: 1px solid #e7eaec; border-left: 0;">
                            <i class="fa fa-search" style="color: #999;"></i>
                        </span>
                    </div>
                </li>

                {{-- Botón Dashboard - Siempre visible para todos los roles --}}
                <li class="dashboard-btn">
                    <a href="{{ route('dashboard') }}" class="dashboard-link">
                        <i class="fa fa-area-chart"></i>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                
                <style>
                    .dashboard-btn .dashboard-link {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 10px 25px;
                        color: #ffffff !important;
                        text-decoration: none;
                    }
                    
                    .dashboard-btn .dashboard-link i {
                        font-size: 16px;
                        margin-right: 10px;
                        color: #ffffff !important;
                    }
                    
                    .dashboard-btn .dashboard-link .nav-label {
                        font-size: 14px;
                        color: #ffffff !important;
                    }
                    
                    /* ========== REGLAS GLOBALES PARA ICONOS (MÁXIMA PRIORIDAD) ========== */
                    /* CRÍTICO: Sobrescribir regla de Inspinia que oculta spans en mini-navbar */
                    body.mini-navbar .navbar-default .nav li a i,
                    body.mini-navbar .navbar-default .nav li a i.fa,
                    body.mini-navbar .navbar-default .nav li a i[class*="fa-"],
                    body.mini-navbar .navbar-static-side .nav li a i,
                    body.mini-navbar #side-menu li a i {
                        display: inline-block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        font-size: 20px !important;
                        color: #ffffff !important;
                        width: auto !important;
                        height: auto !important;
                        margin: 0 !important;
                    }
                    
                    /* Forzar iconos visibles en TODAS las dimensiones - sobrescribe estilos inline */
                    .navbar-default .nav > li > a i,
                    .navbar-static-side #side-menu li a i,
                    #side-menu > li > a > i,
                    .navbar-static-side .nav li a i[class*="fa"],
                    nav.navbar-static-side ul#side-menu li a i {
                        display: inline-block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        font-size: 20px !important;
                        width: auto !important;
                        height: auto !important;
                    }
                    
                    /* Regla adicional para cuando el sidebar está minimizado */
                    .navbar-static-side[style*="width: 70px"] li a i,
                    .navbar-static-side[style*="width:70px"] li a i {
                        display: inline-block !important;
                        visibility: visible !important;
                    }

                    /* ========== ESTILOS UNIVERSALES PARA MODO MINIMIZADO ========== */
                    /* Aplica tanto en escritorio (body.mini-navbar) como en móvil (body:not(.mini-navbar) <768px) */
                    
                    /* Cuando el menú está minimizado en escritorio (clase mini-navbar en body) */
                    body.mini-navbar .navbar-static-side {
                        width: 70px;
                    }
                    
                    /* Iconos blancos en modo minimizado */
                    body.mini-navbar .navbar-default .nav > li > a i,
                    body.mini-navbar .nav > li > a i.fa,
                    body.mini-navbar #side-menu > li > a > i {
                        display: inline-block !important;
                        font-size: 20px !important;
                        width: 100% !important;
                        text-align: center !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                        color: #ffffff !important;
                    }
                    
                    /* Submenús como tooltip al hacer hover cuando está minimizado - fondo muy transparente */
                    body.mini-navbar .nav li .nav-second-level {
                        display: none !important;
                        position: fixed !important;
                        left: 70px !important;
                        background: rgba(47, 64, 80, 0.75) !important;
                        border: 1px solid rgba(26, 179, 148, 0.4) !important;
                        border-radius: 8px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                        width: 200px !important;
                        z-index: 2001 !important;
                        padding: 10px 0 !important;
                        max-height: 400px;
                        overflow-y: auto;
                        backdrop-filter: blur(15px);
                        -webkit-backdrop-filter: blur(15px);
                    }
                    
                    /* Mostrar submenú al hacer hover O al hacer clic (active) cuando está minimizado */
                    body.mini-navbar .nav > li:hover > .nav-second-level,
                    body.mini-navbar .nav > li.active > .nav-second-level {
                        display: block !important;
                    }
                    
                    /* Estilos de items del submenu tooltip */
                    body.mini-navbar .nav li:hover .nav-second-level li,
                    body.mini-navbar .nav li.active .nav-second-level li {
                        border: none !important;
                    }
                    
                    /* Ocultar iconos de los submenús en modo minimizado - ESCRITORIO (MÁXIMA ESPECIFICIDAD) */
                    body.mini-navbar .nav li .nav-second-level li a i,
                    body.mini-navbar .nav-second-level li a i,
                    body.mini-navbar .navbar-default .nav .nav-second-level li a i,
                    body.mini-navbar .navbar-static-side .nav-second-level li a i,
                    body.mini-navbar #side-menu .nav-second-level li a i,
                    body.mini-navbar ul.nav-second-level li a i[class*="fa"] {
                        display: none !important;
                        visibility: hidden !important;
                        width: 0 !important;
                        height: 0 !important;
                        font-size: 0 !important;
                        opacity: 0 !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    
                    body.mini-navbar .nav li:hover .nav-second-level li a,
                    body.mini-navbar .nav li.active .nav-second-level li a {
                        padding: 12px 20px !important;
                        color: #ffffff !important;
                        display: block;
                        transition: all 0.3s ease;
                        border-left: 3px solid transparent;
                        background: transparent !important;
                    }
                    
                    body.mini-navbar .nav li:hover .nav-second-level li a:hover,
                    body.mini-navbar .nav li.active .nav-second-level li a:hover {
                        background: rgba(26, 179, 148, 0.12) !important;
                        border-left: 3px solid #1ab394 !important;
                        padding-left: 23px !important;
                        transform: translateX(3px);
                    }
                    
                    /* Hover en item principal minimizado - sombreado suave y casi transparente */
                    body.mini-navbar .nav > li > a:hover {
                        background: rgba(26, 179, 148, 0.15) !important;
                        border-left: 3px solid #1ab394;
                        transition: all 0.3s ease;
                    }
                    
                    /* Indicador visual cuando está activo (clicked) - sombreado muy suave */
                    body.mini-navbar .nav > li.active > a {
                        background: rgba(26, 179, 148, 0.1) !important;
                        border-left: 3px solid #1ab394;
                    }
                    
                    /* Ocultar textos y flechas en escritorio minimizado */
                    body.mini-navbar .nav li a span.nav-label,
                    body.mini-navbar .nav li a .fa.arrow {
                        display: none !important;
                    }
                    
                    /* Centrar iconos */
                    body.mini-navbar .nav > li > a {
                        text-align: center;
                        padding: 14px 10px !important;
                    }
                    
                    /* Ocultar búsqueda */
                    body.mini-navbar .search-sidebar {
                        display: none !important;
                    }
                    
                    /* ========== FORZAR ICONOS VISIBLES EN MÓVIL ========== */
                    /* Reglas globales para asegurar iconos visibles en cualquier dimensión */
                    .navbar-default #side-menu > li > a > i,
                    .navbar-static-side .nav > li > a > i,
                    #side-menu li a i.fa {
                        display: inline-block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    }

                    /* ========== ESTILOS RESPONSIVOS PARA MÓVIL ========== */
                    
                    /* Ajustes específicos para móvil - aplicar los mismos estilos que escritorio */
                    @media (max-width: 768px) {
                        /* Habilitar scroll dentro del menú lateral */
                        .navbar-static-side {
                            overflow-y: auto !important;
                            -webkit-overflow-scrolling: touch;
                        }
                        .scroll-bar-sidebar {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                            max-height: 100vh !important;
                        }
                        /* Asegurar que el sidebar esté visible en pantalla */
                        .navbar-static-side {
                            position: fixed !important;
                            top: 0 !important;
                            left: 0 !important;
                            height: 100vh !important;
                            display: block !important;
                            z-index: 2000 !important;
                        }
                        /* Ancho minimizado por defecto en móvil */
                        body:not(.mini-navbar) .navbar-static-side {
                            width: 70px !important;
                        }
                        
                        /* Iconos blancos en móvil - máxima especificidad */
                        body:not(.mini-navbar) .navbar-default .nav > li > a i,
                        body:not(.mini-navbar) .nav > li > a i.fa,
                        body:not(.mini-navbar) #side-menu > li > a > i,
                        body:not(.mini-navbar) .navbar-static-side .nav li a i,
                        .navbar-default #side-menu > li > a > i[class*="fa"] {
                            display: inline-block !important;
                            font-size: 20px !important;
                            width: 100% !important;
                            text-align: center !important;
                            margin-right: 0 !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            color: #ffffff !important;
                            min-width: 20px !important;
                            min-height: 20px !important;
                        }
                        
                        /* Submenús tooltip en móvil */
                        body:not(.mini-navbar) .nav li .nav-second-level {
                            display: none !important;
                            position: fixed !important;
                            left: 70px !important;
                            background: rgba(47, 64, 80, 0.75) !important;
                            border: 1px solid rgba(26, 179, 148, 0.4) !important;
                            border-radius: 8px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                            width: 200px !important;
                            z-index: 2001 !important;
                            padding: 10px 0 !important;
                            max-height: 400px;
                            overflow-y: auto;
                            backdrop-filter: blur(15px);
                            -webkit-backdrop-filter: blur(15px);
                        }
                        
                        /* Mostrar submenu en hover/clic en móvil */
                        body:not(.mini-navbar) .nav > li:hover > .nav-second-level,
                        body:not(.mini-navbar) .nav > li.active > .nav-second-level {
                            display: block !important;
                        }
                        
                        /* Ocultar iconos de los submenús en modo minimizado - MÓVIL */
                        body:not(.mini-navbar) .nav li .nav-second-level li a i,
                        body:not(.mini-navbar) .nav-second-level li a i,
                        body:not(.mini-navbar) .navbar-default .nav .nav-second-level li a i {
                            display: none !important;
                            visibility: hidden !important;
                            width: 0 !important;
                            height: 0 !important;
                            font-size: 0 !important;
                        }
                        
                        /* Items de submenu en móvil */
                        body:not(.mini-navbar) .nav li:hover .nav-second-level li a,
                        body:not(.mini-navbar) .nav li.active .nav-second-level li a {
                            padding: 12px 20px !important;
                            color: #ffffff !important;
                            display: block;
                            transition: all 0.3s ease;
                            border-left: 3px solid transparent;
                            background: transparent !important;
                        }
                        
                        body:not(.mini-navbar) .nav li:hover .nav-second-level li a:hover,
                        body:not(.mini-navbar) .nav li.active .nav-second-level li a:hover {
                            background: rgba(26, 179, 148, 0.12) !important;
                            border-left: 3px solid #1ab394 !important;
                            padding-left: 23px !important;
                            transform: translateX(3px);
                        }
                        
                        /* Hover en iconos móvil */
                        body:not(.mini-navbar) .nav > li > a:hover {
                            background: rgba(26, 179, 148, 0.15) !important;
                            border-left: 3px solid #1ab394;
                            transition: all 0.3s ease;
                        }
                        
                        /* Item activo móvil */
                        body:not(.mini-navbar) .nav > li.active > a {
                            background: rgba(26, 179, 148, 0.1) !important;
                            border-left: 3px solid #1ab394;
                        }
                        
                        /* Ocultar textos y flechas en móvil minimizado */
                        body:not(.mini-navbar) .nav li a span.nav-label,
                        body:not(.mini-navbar) .nav li a .fa.arrow {
                            display: none !important;
                        }
                        
                        /* Centrar iconos */
                        body:not(.mini-navbar) .nav > li > a {
                            text-align: center;
                            padding: 14px 10px !important;
                        }
                        
                        /* Dashboard en móvil minimizado */
                        body:not(.mini-navbar) .dashboard-btn .dashboard-link {
                            justify-content: center !important;
                            padding: 14px 10px !important;
                        }
                        
                        body:not(.mini-navbar) .dashboard-btn .dashboard-link i {
                            margin-right: 0 !important;
                            font-size: 20px;
                        }
                        
                        /* Ocultar búsqueda en móvil minimizado */
                        body:not(.mini-navbar) .search-sidebar {
                            display: none !important;
                        }
                        
                        /* Ajustar contenido principal: sin espacio por defecto */
                        body:not(.mini-navbar) #page-wrapper {
                            margin-left: 0 !important;
                        }
                        /* Cuando el sidebar está abierto, empujar el contenido */
                        body.mobile-sidebar-open #page-wrapper {
                            margin-left: 70px !important;
                        }
                        
                        /* FORZAR iconos visibles en móvil con máxima prioridad */
                        .navbar-default .nav > li > a i,
                        .navbar-static-side #side-menu li a i,
                        body:not(.mini-navbar) li a i[class*="fa"] {
                            display: inline-block !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            color: #ffffff !important;
                            font-size: 20px !important;
                            min-width: 20px !important;
                            min-height: 20px !important;
                        }
                    }
                    
                    /* Media query adicional para pantallas muy pequeñas */
                    @media (max-width: 480px) {
                        /* Forzar iconos con máxima especificidad */
                        .navbar-default .nav > li > a i,
                        #side-menu li a i,
                        .nav li a i[class*="fa-"] {
                            display: inline-block !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            color: #ffffff !important;
                            font-size: 20px !important;
                        }
                    }

                    /* ========== TABLET: Solo iconos (similar a móvil) ========== */
                    @media (min-width: 769px) and (max-width: 992px) {
                        .navbar-static-side {
                            position: fixed !important;
                            top: 0 !important;
                            left: 0 !important;
                            height: 100vh !important;
                            display: block !important;
                            z-index: 2000 !important;
                            width: 70px !important;
                            overflow-y: auto !important;
                            -webkit-overflow-scrolling: touch;
                        }

                        .scroll-bar-sidebar {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                            max-height: 100vh !important;
                        }

                        /* Ocultar textos y flechas en tablet */
                        .nav li a span.nav-label,
                        .nav li a .fa.arrow {
                            display: none !important;
                        }

                        /* Centrar iconos */
                        .nav > li > a {
                            text-align: center !important;
                            padding: 14px 10px !important;
                        }

                        /* Ocultar búsqueda en tablet */
                        .search-sidebar {
                            display: none !important;
                        }

                        /* Ajustar contenido principal solo cuando el sidebar esté abierto */
                        body.mobile-sidebar-open #page-wrapper {
                            margin-left: 70px !important;
                        }
                    }
                    
                    /* Asegurar ancho 70px en escritorio minimizado */
                    body.mini-navbar .navbar-static-side {
                        width: 70px !important;
                    }
                    
                    /* Ocultar textos y flechas en escritorio minimizado */
                    body.mini-navbar .nav li a span.nav-label,
                    body.mini-navbar .nav li a .fa.arrow {
                        display: none !important;
                    }
                    
                    /* Ocultar búsqueda en escritorio minimizado */
                    body.mini-navbar .search-sidebar {
                        display: none !important;
                    }
                    
                    /* Botón toggle para expandir/contraer en móvil */
                    @media (max-width: 768px) {
                        .navbar-minimalize {
                            display: block !important;
                            position: fixed;
                            top: 10px;
                            left: 10px;
                            z-index: 2002;
                            background: #1ab394;
                            color: white;
                            border: none;
                            padding: 10px 15px;
                            border-radius: 4px;
                            cursor: pointer;
                        }

                        /* Siempre iconos en móvil (incluso si se pulsa el toggle) */
                        body.mini-navbar .navbar-static-side {
                            width: 70px !important;
                        }

                        body.mini-navbar #page-wrapper {
                            margin-left: 70px !important;
                        }

                        body.mini-navbar .nav li a span.nav-label,
                        body.mini-navbar .nav li a .fa.arrow {
                            display: none !important;
                        }

                        body.mini-navbar .navbar-default .nav > li > a {
                            text-align: center !important;
                            padding: 14px 10px !important;
                        }

                        body.mini-navbar .dashboard-btn .dashboard-link {
                            justify-content: center !important;
                        }

                        body.mini-navbar .dashboard-btn .dashboard-link i {
                            margin-right: 0 !important;
                        }
                    }

                    /* ========== Off-canvas en móvil: oculto por defecto, visible al expandir ========== */
                    @media (max-width: 768px) {
                        .navbar-static-side {
                            transition: left 0.25s ease;
                            left: -80px !important; /* Oculto por defecto */
                            width: 70px !important;
                        }
                        body.mobile-sidebar-open .navbar-static-side {
                            left: 0 !important; /* Visible al expandir */
                        }
                    }

                    /* ========== Off-canvas en tablet: oculto por defecto, visible al expandir ========== */
                    @media (min-width: 769px) and (max-width: 992px) {
                        .navbar-static-side {
                            transition: left 0.25s ease;
                            left: -80px !important; /* Oculto por defecto */
                            width: 70px !important;
                        }
                        body.mobile-sidebar-open .navbar-static-side {
                            left: 0 !important; /* Visible al expandir */
                        }
                    }
                </style>

                {{--  MENÚ DINÁMICO DESDE BASE DE DATOS  --}}
                @include('partials.menu-dinamico')
                {{--  FIN MENÚ DINÁMICO  --}}
            </ul>
        </div>
    </nav>
        <!-- Overlay para cerrar el menú en móvil/tablet -->
        <div class="mobile-sidebar-overlay" aria-hidden="true"></div>
</nav>

<style>
/* Overlay para mobile/tablet */
@media (max-width: 992px) {
    .mobile-sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.45);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        z-index: 1500; /* Debajo del sidebar (2000) */
    }
    body.mobile-sidebar-open .mobile-sidebar-overlay {
        opacity: 1;
        visibility: visible;
    }
}
</style>

<script>
// Toggle del sidebar en móvil/tablet: oculto por defecto, aparece al pulsar
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.navbar-minimalize');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    function isNonDesktop() { return window.innerWidth <= 992; }

    function toggleMobileSidebar(e) {
        if (!toggleBtn) return;
        if (isNonDesktop()) {
            if (e) e.preventDefault();
            document.body.classList.toggle('mobile-sidebar-open');
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleMobileSidebar);
    }

    // Cerrar tocando overlay
    if (overlay) {
        overlay.addEventListener('click', () => {
            document.body.classList.remove('mobile-sidebar-open');
            // Cerrar submenús activos
            document.querySelectorAll('#side-menu > li').forEach(li => li.classList.remove('active'));
        });
    }

    // Cerrar submenús y sidebar al hacer clic fuera del menú en móvil/tablet
    document.addEventListener('click', (e) => {
        if (!isNonDesktop()) return;
        const clickedInsideMenu = e.target.closest('#side-menu');
        const clickedToggle = e.target.closest('.navbar-minimalize');
        const clickedOverlay = e.target.closest('.mobile-sidebar-overlay');
        if (!clickedInsideMenu && !clickedToggle && !clickedOverlay) {
            document.querySelectorAll('#side-menu > li').forEach(li => li.classList.remove('active'));
            document.body.classList.remove('mobile-sidebar-open');
        }
    });

    // Cerrar el sidebar si cambia a escritorio
    window.addEventListener('resize', () => {
        if (!isNonDesktop()) {
            document.body.classList.remove('mobile-sidebar-open');
            document.querySelectorAll('#side-menu > li').forEach(li => li.classList.remove('active'));
        }
    });
});
</script>
