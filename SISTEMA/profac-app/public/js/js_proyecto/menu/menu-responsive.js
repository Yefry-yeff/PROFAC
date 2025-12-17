/**
 * Funcionalidad responsiva para el sidebar en dispositivos móviles
 * Maneja el comportamiento de minimización y expansión del menú
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Detectar si estamos en dispositivo móvil
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Inicializar el estado del sidebar según el tamaño de pantalla
    function initSidebarState() {
        if (isMobile()) {
            // En móvil, asegurar que el sidebar esté minimizado por defecto
            document.body.classList.remove('mini-navbar');
            
            // Cerrar todos los menús desplegados
            const menuItems = document.querySelectorAll('#side-menu > li.active');
            menuItems.forEach(function(item) {
                if (!item.classList.contains('nav-header') && 
                    !item.classList.contains('search-sidebar') && 
                    !item.classList.contains('dashboard-btn')) {
                    item.classList.remove('active');
                }
            });
        }
    }
    
    // Manejar el clic en el botón de minimizar
    const minimizeBtn = document.querySelector('.navbar-minimalize');
    if (minimizeBtn) {
        minimizeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (isMobile()) {
                // En móvil, toggle entre minimizado y expandido
                document.body.classList.toggle('mini-navbar');
                
                // Si se está minimizando, cerrar todos los menús
                if (!document.body.classList.contains('mini-navbar')) {
                    const menuItems = document.querySelectorAll('#side-menu > li.active');
                    menuItems.forEach(function(item) {
                        if (!item.classList.contains('nav-header') && 
                            !item.classList.contains('search-sidebar') && 
                            !item.classList.contains('dashboard-btn')) {
                            item.classList.remove('active');
                        }
                    });
                }
            }
        });
    }
    
    // Manejar el posicionamiento de submenús en hover y clic (modo minimizado)
    const menuItems = document.querySelectorAll('#side-menu > li:not(.nav-header):not(.search-sidebar):not(.dashboard-btn)');
    
    // Función para posicionar el submenu al lado del icono
    function positionSubmenu(menuItem, submenu) {
        const rect = menuItem.getBoundingClientRect();
        submenu.style.top = rect.top + 'px';
    }
    
    // Variable para rastrear el submenu activo y su item padre
    let activeSubmenu = null;
    let activeMenuItem = null;
    
    menuItems.forEach(function(menuItem) {
        const submenu = menuItem.querySelector('.nav-second-level');
        
        if (submenu) {
            // Al hacer hover, posicionar el submenu
            menuItem.addEventListener('mouseenter', function() {
                // Verificar si está en modo minimizado (escritorio con mini-navbar O móvil sin mini-navbar)
                const isMinimized = document.body.classList.contains('mini-navbar') || 
                                   (isMobile() && !document.body.classList.contains('mini-navbar'));
                
                if (isMinimized) {
                    positionSubmenu(menuItem, submenu);
                    activeSubmenu = submenu;
                    activeMenuItem = menuItem;
                }
            });
            
            // Al hacer clic, posicionar el submenu también
            menuItem.addEventListener('click', function(e) {
                const isMinimized = document.body.classList.contains('mini-navbar') || 
                                   (isMobile() && !document.body.classList.contains('mini-navbar'));
                
                if (isMinimized) {
                    positionSubmenu(menuItem, submenu);
                    activeSubmenu = submenu;
                    activeMenuItem = menuItem;
                }
            });
            
            // Al salir del hover, limpiar el rastreo si no está activo
            menuItem.addEventListener('mouseleave', function() {
                if (!menuItem.classList.contains('active')) {
                    activeSubmenu = null;
                    activeMenuItem = null;
                }
            });
        }
    });
    
    // Reposicionar el submenu activo al hacer scroll para mantenerlo alineado con el icono
    const scrollContainer = document.querySelector('.scroll-bar-sidebar');
    const sidebar = document.querySelector('.navbar-static-side');
    
    // Función para reposicionar durante el scroll
    function handleScroll() {
        if (activeSubmenu && activeMenuItem) {
            const isMinimized = document.body.classList.contains('mini-navbar') || 
                               (isMobile() && !document.body.classList.contains('mini-navbar'));
            
            if (isMinimized && (activeMenuItem.classList.contains('active') || 
                activeMenuItem.matches(':hover'))) {
                positionSubmenu(activeMenuItem, activeSubmenu);
            }
        }
        
        // Forzar ocultación de iconos en submenús después del scroll
        const isMinimized = document.body.classList.contains('mini-navbar') || 
                           (isMobile() && !document.body.classList.contains('mini-navbar'));
        if (isMinimized) {
            const submenuIcons = document.querySelectorAll('.nav-second-level li a i');
            submenuIcons.forEach(function(icon) {
                icon.style.display = 'none';
                icon.style.visibility = 'hidden';
            });
        }
    }
    
    // Escuchar scroll en el contenedor con scroll
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', handleScroll);
    }
    
    // También escuchar en el sidebar principal por si acaso
    if (sidebar) {
        sidebar.addEventListener('scroll', handleScroll);
    }
    
    // Escuchar scroll en window para movimientos generales
    window.addEventListener('scroll', handleScroll, true);
    
    // Ejecutar handleScroll cada vez que se detecte movimiento del mouse sobre el sidebar
    if (sidebar) {
        sidebar.addEventListener('mousemove', function() {
            if (activeSubmenu && activeMenuItem) {
                const isMinimized = document.body.classList.contains('mini-navbar') || 
                                   (isMobile() && !document.body.classList.contains('mini-navbar'));
                if (isMinimized) {
                    positionSubmenu(activeMenuItem, activeSubmenu);
                }
            }
        });
    }
    
    // Reinicializar al cambiar el tamaño de la ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initSidebarState();
        }, 250);
    });
    
    // Inicializar al cargar
    initSidebarState();
    
    // FORZAR ICONOS VISIBLES - Solución para móviles
    function forceIconsVisible() {
        // Verificar si está en modo minimizado
        const isMinimized = document.body.classList.contains('mini-navbar') || 
                           (isMobile() && !document.body.classList.contains('mini-navbar'));
        
        // Iconos del menú principal
        const mainMenuIcons = document.querySelectorAll('#side-menu > li > a > i');
        mainMenuIcons.forEach(function(icon) {
            // Forzar estilos inline para sobrescribir cualquier otro estilo
            icon.style.display = 'inline-block';
            icon.style.opacity = '1';
            icon.style.visibility = 'visible';
            icon.style.fontSize = '20px';
            icon.style.width = 'auto';
            icon.style.height = 'auto';
            
            // Si el menú está minimizado, color blanco
            if (isMinimized) {
                icon.style.color = '#ffffff';
            }
        });
        
        // Ocultar iconos de los submenús cuando está minimizado - MÁXIMA PRIORIDAD
        if (isMinimized) {
            const submenuIcons = document.querySelectorAll('.nav-second-level li a i, ul.nav-second-level li i, .nav-second-level a > i');
            submenuIcons.forEach(function(icon) {
                icon.style.setProperty('display', 'none', 'important');
                icon.style.setProperty('visibility', 'hidden', 'important');
                icon.style.setProperty('width', '0', 'important');
                icon.style.setProperty('height', '0', 'important');
                icon.style.setProperty('font-size', '0', 'important');
                icon.style.setProperty('opacity', '0', 'important');
                icon.style.setProperty('margin', '0', 'important');
                icon.style.setProperty('padding', '0', 'important');
            });
        }
    }
    
    // DIAGNÓSTICO: Verificar si los iconos existen
    const allMenuIcons = document.querySelectorAll('#side-menu li a i');
    
    // Ejecutar al cargar
    forceIconsVisible();
    
    // Ejecutar después de un pequeño delay por si hay estilos que se aplican tarde
    setTimeout(forceIconsVisible, 100);
    setTimeout(forceIconsVisible, 500);
    setTimeout(forceIconsVisible, 1000);
    
    // Re-diagnóstico después de aplicar estilos
    // Logs de diagnóstico eliminados
    
    // Observar cambios en el DOM para aplicar estilos a iconos nuevos
    const observer = new MutationObserver(function(mutations) {
        forceIconsVisible();
    });
    
    const sideMenu = document.querySelector('#side-menu');
    if (sideMenu) {
        observer.observe(sideMenu, { 
            childList: true, 
            subtree: true 
        });
    }
    
    // Cerrar menú al hacer clic fuera en móvil expandido
    document.addEventListener('click', function(e) {
        if (isMobile() && document.body.classList.contains('mini-navbar')) {
            const sidebar = document.querySelector('.navbar-static-side');
            const toggleBtn = document.querySelector('.navbar-minimalize');
            
            if (sidebar && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                // Clic fuera del sidebar, minimizar
                document.body.classList.remove('mini-navbar');
                
                // Cerrar menús
                const menuItems = document.querySelectorAll('#side-menu > li.active');
                menuItems.forEach(function(item) {
                    if (!item.classList.contains('nav-header') && 
                        !item.classList.contains('search-sidebar') && 
                        !item.classList.contains('dashboard-btn')) {
                        item.classList.remove('active');
                    }
                });
            }
        }
    });
});
