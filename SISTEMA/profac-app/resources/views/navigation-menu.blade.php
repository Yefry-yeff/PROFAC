<div>
<nav x-data="{ open: false }" class="sticky top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 shadow-sm">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary md:hidden flex items-center justify-center w-10 h-10 rounded-lg bg-orange-600 text-white hover:bg-orange-700 transition" href="#"><i class="fa fa-bars"></i></a>

    <!-- Primary Navigation Menu -->
    <div class="px-4 sm:px-6 lg:px-8 w-full">

        <div class="relative flex items-center justify-center h-16">
            <!-- Logo centrado -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}">
                    <img class="object-cover rounded-full shadow-lg border-4 border-orange-500 animate__animated animate__bounceIn" style="width:4.5rem"
                        src="{{ asset('img/LOGO_VALENCIA.jpg') }}" alt="Logo Valencia" />
                </a>
            </div>

            <div class="absolute right-0 hidden sm:flex sm:items-center sm:mr-3 profile-area">
                <!-- Teams Dropdown -->

                {{-- Campana de notificaciones --}}
                @livewire('notificaciones-bell')

                <!-- Settings Dropdown -->
                <div class="relative ml-3">
                    <x-jet-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm transition border-2 border-transparent rounded-full focus:outline-none focus:border-orange-400 shadow-md hover:shadow-lg">
                                    @if (Auth::user()->profile_photo_path && file_exists(public_path('storage/' . Auth::user()->profile_photo_path)))
                                        <img class="object-cover w-8 h-8 rounded-full border-2 border-orange-400"
                                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                            alt="{{ Auth::user()->name }}" />
                                        <!-- Inicial visible solo en móvil -->
                                        <span class="mobile-initial-avatar hidden md:inline-flex ml-2 bg-orange-100 text-orange-700 border border-orange-300" aria-hidden="true">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-orange-200 flex items-center justify-center text-orange-700 font-bold border border-orange-400">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-700 transition bg-white border border-orange-300 rounded-md shadow hover:text-orange-700 hover:border-orange-400 focus:outline-none">
                                        {{ Auth::user()->name }}
                                        <svg class="ml-2 -mr-0.5 h-4 w-4 text-orange-500" xmlns="http://www.w3.org/2000/svg"
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

                            @if(optional(Auth::user()->rol)->nombre === 'Administrador' || Auth::user()->rol_id == 1)
                                <div class="border-t border-gray-100"></div>
                                <div class="block px-4 py-2 text-xs text-gray-400 uppercase tracking-wide">
                                    <i class="fa fa-cog mr-1"></i> Administración
                                </div>
                                <x-jet-dropdown-link href="{{ route('configuracion.notificaciones.flujo') }}">
                                    <i class="fa fa-bell mr-2 text-orange-500"></i> Configuración de notificaciones
                                </x-jet-dropdown-link>
                            @endif

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
        /* ── Fix: colisión Tailwind .collapse (visibility:collapse) vs Bootstrap .collapse ── */
        #side-menu .nav-second-level,
        #side-menu .nav-second-level li,
        #side-menu .nav-second-level li a {
            visibility: visible !important;
        }

        /* ====== HEADER: fijo en la parte superior, nunca se mueve ====== */
        nav.sticky {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 4000 !important;
            background: #ffffff !important;
            border-bottom: 1px solid #e5e7eb !important;
        }
        /* Dropdown del perfil: por encima de todo */
        .profile-area .relative > div:last-child,
        nav.sticky [x-show] {
            z-index: 9999 !important;
        }

        /* ====== Sidebar: fondo naranja uniforme ====== */
        li.nav-header {
            display: none !important;
        }
        /* Fondo degradado para TODO el sidebar (colores del logo Valencia) */
        nav.navbar-static-side,
        .navbar-default.navbar-static-side {
            border: none !important;
            background: linear-gradient(180deg, #F06030 0%, #D02010 55%, #8C1208 100%) !important;
        }
        /* Submenús segundo nivel: oscurecer ligeramente sobre el gradiente */
        .mini-navbar .nav .nav-second-level,
        .navbar-default .nav .nav-second-level,
        .nav-second-level {
            background: rgba(0,0,0,0.18) !important;
        }
        /* Ítems activos: realce blanco semitransparente */
        .navbar-default .nav > li.active,
        .nav > li.active {
            background: rgba(255,255,255,0.15) !important;
            border-left: 4px solid rgba(255,255,255,0.8) !important;
        }
        /* Hover en ítems del menú */
        .navbar-default .nav > li > a:hover,
        .navbar-default .nav > li > a:focus {
            background: rgba(255,255,255,0.12) !important;
            border-left: 3px solid rgba(255,255,255,0.6) !important;
            color: #ffffff !important;
        }

        /* ====== SIDEBAR: empieza debajo del header, scroll interno, footer anclado ====== */
        nav.navbar-static-side {
            position: fixed !important;
            top: 65px !important;          /* igual a la altura del header */
            height: calc(100vh - 65px) !important;
            z-index: 2000 !important;      /* siempre por debajo del header (4000) */
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        /* Solo en escritorio el sidebar está siempre visible a la izquierda */
        @media (min-width: 993px) {
            nav.navbar-static-side {
                left: 0 !important;
            }
        }
        .sidebar-collapse {
            flex: 1 1 auto !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            height: 100% !important;
        }
        .scroll-bar-sidebar {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            /* Dejar espacio para el footer anclado */
        }
        .sidebar-footer-info {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding: 10px 14px;
            text-align: center;
            background: #8C1208;
        }
        .sidebar-footer-info .sf-sistema {
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }
        .sidebar-footer-info .sf-copy {
            font-size: 10px;
            color: rgba(255,255,255,0.75);
            display: block;
        }
        /* Ocultar footer en modo mini-navbar (sidebar 70px) */
        body.mini-navbar .sidebar-footer-info {
            display: none !important;
        }
        /* En móvil/tablet: oculto por defecto, visible cuando el sidebar está abierto */
        @media (max-width: 992px) {
            .sidebar-footer-info { display: none !important; }
            body.mobile-sidebar-open .sidebar-footer-info { display: block !important; }
        }

        /* ====== Header tablet layout: igual que móvil ====== */
        @media (min-width: 769px) and (max-width: 992px) {
            nav .flex.justify-between.h-16 { position: relative; }
            nav .flex.justify-between.h-16 > .flex {
                width: 100%;
                justify-content: center;
            }
            nav .navbar-minimalize {
                position: absolute;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                display: inline-flex !important;
                z-index: 10;
                background: #1ab394 !important;
                color: #ffffff !important;
                border: none !important;
                width: 44px; height: 44px;
                align-items: center; justify-content: center;
                padding: 0 !important;
                line-height: 1;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            }
            nav .navbar-minimalize i.fa { font-size: 22px; color: #ffffff !important; }
            nav .profile-area {
                display: flex !important;
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                margin-left: 0;
            }
        }

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
                /* max-height controlado por flexbox del sidebar-collapse */
            }
        }
    </style>

    <nav class="navbar-default navbar-static-side bg-gradient-to-b from-orange-500 via-orange-700 to-orange-900 text-white" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu scroll-bar-sidebar" id="side-menu" style="">
                <li class="nav-header">
                    <div class="logo-element">
                        IN+
                    </div>
                </li>

                {{-- Cuadro de búsqueda en sidebar --}}
                <li class="search-sidebar px-4 py-3">
                    <div class="flex rounded-md shadow-sm">
                        <input type="text" id="menu-search" class="form-input block w-full rounded-l-md border-gray-300 focus:border-orange-400 focus:ring focus:ring-orange-200 focus:ring-opacity-50 text-gray-700" placeholder="Buscar en menú...">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-white text-gray-400">
                            <i class="fa fa-search"></i>
                        </span>
                    </div>
                </li>

                {{-- Botón Dashboard - Siempre visible para todos los roles --}}
                <li class="dashboard-btn">
                    <a href="{{ route('dashboard') }}" class="dashboard-link flex items-center gap-2 px-4 py-2 rounded-md hover:bg-orange-600 transition">
                        <i class="fa fa-area-chart text-lg"></i>
                        <span class="nav-label font-semibold">Dashboard</span>
                    </a>
                </li>

                <style>
                    .dashboard-btn .dashboard-link {
                        display: flex;
                        align-items: center;
                        justify-content: flex-start;
                        padding: 10px 15px;
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

                    /* Submenús como tooltip al hacer hover cuando está minimizado - rojo oscuro */
                    body.mini-navbar .nav li .nav-second-level {
                        display: none !important;
                        position: fixed !important;
                        left: 70px !important;
                        background: rgba(140, 18, 8, 0.96) !important;
                        border: 1px solid rgba(255, 255, 255, 0.25) !important;
                        border-radius: 8px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                        width: 200px !important;
                        z-index: 2001 !important;
                        padding: 10px 0 !important;
                        max-height: 400px;
                        overflow-y: auto;
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
                        background: rgba(255,255,255,0.15) !important;
                        border-left: 3px solid rgba(255,255,255,0.7) !important;
                        padding-left: 23px !important;
                    }

                    /* Hover en item principal minimizado */
                    body.mini-navbar .nav > li > a:hover {
                        background: rgba(255,255,255,0.12) !important;
                        border-left: 3px solid rgba(255,255,255,0.6);
                        transition: all 0.3s ease;
                    }

                    /* Indicador visual cuando está activo (clicked) */
                    body.mini-navbar .nav > li.active > a {
                        background: rgba(255,255,255,0.15) !important;
                        border-left: 3px solid rgba(255,255,255,0.8);
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
                            -webkit-overflow-scrolling: touch;
                        }
                        .scroll-bar-sidebar {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                        }
                        /* Asegurar que el sidebar esté visible en pantalla */
                        .navbar-static-side {
                            position: fixed !important;
                            top: 65px !important;
                            left: 0 !important;
                            height: calc(100vh - 65px) !important;
                            display: flex !important;
                            z-index: 2000 !important;
                        }
                        /* Ancho minimizado por defecto en móvil */
                        body:not(.mini-navbar) .navbar-static-side {
                            width: 70px !important;
                        }

                        /* Iconos en móvil CERRADO: centrados en la barra de 70px */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .navbar-default .nav > li > a i,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li > a i.fa,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) #side-menu > li > a > i,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .navbar-static-side .nav li a i,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) #side-menu > li > a > i[class*="fa"] {
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

                        /* Iconos en móvil ABIERTO: tamaño fijo, alineados izquierda */
                        body.mobile-sidebar-open .navbar-default .nav > li > a i,
                        body.mobile-sidebar-open #side-menu > li > a > i {
                            display: inline-block !important;
                            font-size: 16px !important;
                            width: 20px !important;
                            min-width: 20px !important;
                            text-align: center !important;
                            margin-right: 8px !important;
                            opacity: 1 !important;
                            visibility: visible !important;
                            color: #ffffff !important;
                        }

                        /* Submenús tooltip en móvil (solo cuando el menú está cerrado/mini) */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li .nav-second-level {
                            display: none !important;
                            position: fixed !important;
                            left: 70px !important;
                            background: rgba(140, 18, 8, 0.96) !important;
                            border: 1px solid rgba(255, 255, 255, 0.25) !important;
                            border-radius: 8px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.25);
                            width: 200px !important;
                            z-index: 2001 !important;
                            padding: 10px 0 !important;
                            max-height: 400px;
                            overflow-y: auto;
                        }

                        /* Mostrar submenu en hover/clic en móvil (solo cerrado/mini) */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li:hover > .nav-second-level,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li.active > .nav-second-level {
                            display: block !important;
                        }

                        /* Ocultar iconos de los submenús en modo mini (cerrado) */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li .nav-second-level li a i,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav-second-level li a i,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .navbar-default .nav .nav-second-level li a i {
                            display: none !important;
                            visibility: hidden !important;
                            width: 0 !important;
                            height: 0 !important;
                            font-size: 0 !important;
                        }

                        /* Items de submenu tooltip en móvil cerrado */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li:hover .nav-second-level li a,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li.active .nav-second-level li a {
                            padding: 12px 20px !important;
                            color: #ffffff !important;
                            display: block;
                            transition: all 0.3s ease;
                            border-left: 3px solid transparent;
                            background: transparent !important;
                        }

                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li:hover .nav-second-level li a:hover,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li.active .nav-second-level li a:hover {
                            background: rgba(255,255,255,0.15) !important;
                            border-left: 3px solid rgba(255,255,255,0.7) !important;
                            padding-left: 23px !important;
                        }

                        /* Hover en iconos móvil cerrado */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li > a:hover {
                            background: rgba(255,255,255,0.12) !important;
                            border-left: 3px solid rgba(255,255,255,0.6);
                            transition: all 0.3s ease;
                        }

                        /* Item activo móvil cerrado */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li.active > a {
                            background: rgba(255,255,255,0.15) !important;
                            border-left: 3px solid rgba(255,255,255,0.8);
                        }

                        /* Ocultar textos y flechas en móvil mini (cerrado) */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li a span.nav-label,
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav li a .fa.arrow {
                            display: none !important;
                        }

                        /* Iconos alineados a la izquierda en móvil cerrado */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li > a {
                            text-align: left;
                            padding: 14px 0 14px 10px !important;
                            display: flex !important;
                            align-items: center !important;
                        }
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .nav > li > a i {
                            font-size: 18px !important;
                            width: 22px !important;
                            min-width: 22px !important;
                            text-align: center !important;
                            margin-right: 0 !important;
                        }

                        /* Dashboard en móvil cerrado: icono a la izquierda */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .dashboard-btn .dashboard-link {
                            justify-content: flex-start !important;
                            padding: 14px 10px 14px 12px !important;
                        }

                        body:not(.mini-navbar):not(.mobile-sidebar-open) .dashboard-btn .dashboard-link i {
                            margin-right: 0 !important;
                            font-size: 18px;
                        }

                        /* Ocultar búsqueda en móvil cerrado */
                        body:not(.mini-navbar):not(.mobile-sidebar-open) .search-sidebar {
                            display: none !important;
                        }

                        /* === ESTADO ABIERTO (mobile-sidebar-open): icono + nombre === */
                        body.mobile-sidebar-open nav.navbar-static-side {
                            width: 220px !important;
                        }

                        /* Eliminar margin/padding del ul y li que deja espacio a la izquierda */
                        body.mobile-sidebar-open #side-menu,
                        body.mobile-sidebar-open #side-menu > li {
                            padding-left: 0 !important;
                            margin-left: 0 !important;
                        }

                        body.mobile-sidebar-open .nav > li > a {
                            text-align: left !important;
                            padding: 10px 10px 10px 10px !important;
                            display: flex !important;
                            align-items: center !important;
                            margin: 0 !important;
                        }

                        body.mobile-sidebar-open .nav > li > a i {
                            font-size: 16px !important;
                            width: 20px !important;
                            min-width: 20px !important;
                            flex-shrink: 0 !important;
                            text-align: center !important;
                            margin-right: 8px !important;
                            margin-left: 0 !important;
                        }

                        /* Mostrar labels cuando está abierto */
                        body.mobile-sidebar-open .nav li a span.nav-label {
                            display: inline !important;
                            font-size: 13px !important;
                            color: #ffffff !important;
                        }

                        body.mobile-sidebar-open .nav li a .fa.arrow {
                            display: inline-block !important;
                            margin-left: auto !important;
                        }

                        /* Submenús inline cuando está abierto */
                        body.mobile-sidebar-open .nav li .nav-second-level {
                            display: none;
                            position: static !important;
                            width: 100% !important;
                            left: auto !important;
                            box-shadow: none !important;
                            border-radius: 0 !important;
                            background: rgba(0,0,0,0.12) !important;
                        }
                        body.mobile-sidebar-open .nav > li.active > .nav-second-level {
                            display: block !important;
                        }
                        body.mobile-sidebar-open .nav li .nav-second-level li a {
                            padding: 8px 10px 8px 40px !important;
                            font-size: 12px !important;
                            color: rgba(255,255,255,0.9) !important;
                            border-left: 3px solid transparent !important;
                        }
                        body.mobile-sidebar-open .nav li .nav-second-level li a:hover {
                            background: rgba(255,255,255,0.12) !important;
                            border-left: 3px solid rgba(255,255,255,0.7) !important;
                            color: #ffffff !important;
                        }

                        /* Dashboard en móvil abierto */
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link {
                            justify-content: flex-start !important;
                            padding: 10px 10px 10px 12px !important;
                        }
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link i {
                            margin-right: 8px !important;
                            font-size: 16px !important;
                        }
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link .nav-label {
                            display: inline !important;
                            font-size: 13px !important;
                        }

                        /* Búsqueda visible cuando está abierto */
                        body.mobile-sidebar-open .search-sidebar {
                            display: block !important;
                        }

                        /* Ajustar contenido principal */
                        body:not(.mini-navbar) #page-wrapper {
                            margin-left: 0 !important;
                        }
                        body.mobile-sidebar-open #page-wrapper {
                            margin-left: 220px !important;
                        }

                        /* Forzar iconos visibles - solo móvil cerrado */
                        body:not(.mobile-sidebar-open) .navbar-default .nav > li > a i,
                        body:not(.mobile-sidebar-open) .navbar-static-side #side-menu li a i {
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

                    /* ========== BÚSQUEDA: evitar centrado y fijar Dashboard ========== */
                    /* Forzar layout de bloque en el menú: elimina el flex de Bootstrap 4
                       que puede centrar ítems cuando la mayoría están ocultos por la búsqueda */
                    ul#side-menu {
                        display: block !important;
                    }

                    /* Search box y Dashboard: sticky dentro del área scroll del sidebar
                       así nunca se mueven ni son desplazados por los resultados */
                    #side-menu .search-sidebar {
                        position: sticky;
                        top: 0;
                        z-index: 20;
                        background: linear-gradient(135deg, #F06030, #E04820);
                    }

                    #side-menu .dashboard-btn {
                        position: sticky;
                        top: 64px; /* altura del search-sidebar */
                        z-index: 19;
                        background: linear-gradient(135deg, #E04820, #D02010);
                    }

                    /* Mini-navbar: search oculto → dashboard sube al tope */
                    body.mini-navbar #side-menu .dashboard-btn {
                        top: 0;
                    }

                    /* Móvil y tablet: search siempre oculto → dashboard al tope */
                    @media (max-width: 992px) {
                        #side-menu .dashboard-btn {
                            top: 0 !important;
                        }
                    }

                    /* Resultados de búsqueda: los submenús se muestran inline,
                       no como tooltips flotantes (que causaban el efecto "centrado") */
                    #side-menu li.search-active > .nav-second-level {
                        display: block !important;
                        position: static !important;
                        width: 100% !important;
                        left: auto !important;
                        box-shadow: none !important;
                        border-radius: 0 !important;
                        max-height: none !important;
                    }

                    /* Alineación izquierda en resultados de búsqueda */
                    #side-menu li.search-active > .nav-second-level li a {
                        text-align: left !important;
                        padding: 8px 20px 8px 35px !important;
                    }

                    /* ========== TABLET: igual comportamiento que móvil, adaptado ========== */
                    @media (min-width: 769px) and (max-width: 992px) {

                        /* Sidebar fuera de pantalla por defecto */
                        nav.navbar-static-side {
                            position: fixed !important;
                            top: 65px !important;
                            left: -300px !important;
                            height: calc(100vh - 65px) !important;
                            display: flex !important;
                            z-index: 2000 !important;
                            width: 260px !important;
                            transition: left 0.3s ease, width 0.3s ease;
                            -webkit-overflow-scrolling: touch;
                        }

                        .scroll-bar-sidebar {
                            overflow-y: auto !important;
                            overflow-x: hidden !important;
                        }

                        /* Página ocupa todo el ancho cuando sidebar cerrado */
                        body:not(.mobile-sidebar-open) #page-wrapper {
                            margin-left: 0 !important;
                        }

                        /* Botón toggle: fijo en esquina superior izquierda */
                        .navbar-minimalize {
                            display: block !important;
                            position: fixed !important;
                            top: 12px !important;
                            left: 12px !important;
                            z-index: 2002 !important;
                            background: #1ab394 !important;
                            color: white !important;
                            border: none !important;
                            padding: 0 !important;
                            width: 44px !important; height: 44px !important;
                            border-radius: 6px !important;
                            cursor: pointer !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                        }

                        /* ---- CERRADO: sidebar fuera de pantalla, el contenido no necesita estilos
                                especiales porque el sidebar es invisible ---- */

                        /* ---- ABIERTO: icono + nombre ---- */
                        body.mobile-sidebar-open nav.navbar-static-side {
                            left: 0 !important;
                            width: 260px !important;
                        }
                        body.mobile-sidebar-open #side-menu,
                        body.mobile-sidebar-open #side-menu > li {
                            padding-left: 0 !important;
                            margin-left: 0 !important;
                        }
                        body.mobile-sidebar-open .nav > li > a {
                            text-align: left !important;
                            padding: 11px 12px !important;
                            display: flex !important;
                            align-items: center !important;
                            margin: 0 !important;
                        }
                        body.mobile-sidebar-open .nav > li > a i {
                            font-size: 18px !important;
                            width: 22px !important; min-width: 22px !important;
                            flex-shrink: 0 !important;
                            text-align: center !important;
                            margin-right: 10px !important;
                            margin-left: 0 !important;
                        }
                        body.mobile-sidebar-open .nav li a span.nav-label {
                            display: inline !important;
                            font-size: 14px !important;
                            color: #ffffff !important;
                        }
                        body.mobile-sidebar-open .nav li a .fa.arrow {
                            display: inline-block !important;
                            margin-left: auto !important;
                        }
                        body.mobile-sidebar-open .nav li .nav-second-level {
                            display: none;
                            position: static !important;
                            width: 100% !important;
                            left: auto !important;
                            box-shadow: none !important;
                            border-radius: 0 !important;
                            background: rgba(0,0,0,0.12) !important;
                        }
                        body.mobile-sidebar-open .nav > li.active > .nav-second-level {
                            display: block !important;
                        }
                        body.mobile-sidebar-open .nav li .nav-second-level li a {
                            padding: 9px 12px 9px 45px !important;
                            font-size: 13px !important;
                            color: rgba(255,255,255,0.9) !important;
                            border-left: 3px solid transparent !important;
                        }
                        body.mobile-sidebar-open .nav li .nav-second-level li a:hover {
                            background: rgba(255,255,255,0.12) !important;
                            border-left-color: rgba(255,255,255,0.7) !important;
                            color: #ffffff !important;
                        }
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link {
                            justify-content: flex-start !important;
                            padding: 11px 12px !important;
                        }
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link i {
                            margin-right: 10px !important; font-size: 18px !important;
                        }
                        body.mobile-sidebar-open .dashboard-btn .dashboard-link .nav-label {
                            display: inline !important;
                            font-size: 14px !important;
                        }
                        body.mobile-sidebar-open .search-sidebar {
                            display: block !important;
                        }
                        body.mobile-sidebar-open #page-wrapper {
                            margin-left: 260px !important;
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
                        nav.navbar-static-side {
                            transition: left 0.25s ease, width 0.25s ease;
                            left: -240px !important;
                            width: 70px !important;
                        }
                        body.mobile-sidebar-open nav.navbar-static-side {
                            left: 0 !important;
                            width: 220px !important;
                        }
                    }

                </style>

                {{--  MENÚ DINÁMICO DESDE BASE DE DATOS  --}}
                @include('partials.menu-dinamico')
                {{--  FIN MENÚ DINÁMICO  --}}
            </ul>

            {{-- Footer anclado al sidebar --}}
            <div class="sidebar-footer-info bg-orange-900 text-white text-center py-2 mt-4 rounded-b-lg shadow-inner">
                <span class="sf-sistema font-bold text-xs tracking-wide block">PROFAC Sistema</span>
                <span class="sf-copy text-xs text-orange-200">&copy; {{ date('Y') }} D. Valencia &mdash; Todos los derechos reservados</span>
            </div>

        </div>
    </nav>
    <!-- Overlay para cerrar el menú en móvil/tablet -->
    <div class="mobile-sidebar-overlay" aria-hidden="true"></div>
</nav>
</div>

@push('styles')
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
        z-index: 1500;
    }
    body.mobile-sidebar-open .mobile-sidebar-overlay {
        opacity: 1;
        visibility: visible;
    }
}
</style>
@endpush

@push('scripts')
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
        });
    }

    // Cerrar sidebar al hacer clic fuera del menú en móvil/tablet
    document.addEventListener('click', (e) => {
        if (!isNonDesktop()) return;
        const clickedInsideMenu = e.target.closest('#side-menu');
        const clickedToggle = e.target.closest('.navbar-minimalize');
        const clickedOverlay = e.target.closest('.mobile-sidebar-overlay');
        if (!clickedInsideMenu && !clickedToggle && !clickedOverlay) {
            document.body.classList.remove('mobile-sidebar-open');
        }
    });

    // Cerrar el sidebar si cambia a escritorio
    window.addEventListener('resize', () => {
        if (!isNonDesktop()) {
            document.body.classList.remove('mobile-sidebar-open');
        }
    });
});
</script>
@endpush
