/**
 * Funcionalidad de búsqueda en el sidebar
 * Permite filtrar los elementos del menú en tiempo real
 * Muestra solo los submenús que coinciden con la búsqueda
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('menu-search');
    
    if (!searchInput) return;

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const menuItems = document.querySelectorAll('#side-menu > li:not(.nav-header):not(.search-sidebar):not(.dashboard-btn)');
        
        let totalMatches = 0;
        
        // Limpiar mensaje previo de no resultados
        const oldNoResults = document.getElementById('no-results-message');
        if (oldNoResults) oldNoResults.remove();
        
        menuItems.forEach(function(menuItem) {
            const subMenuContainer = menuItem.querySelector('.nav-second-level');
            const subMenuItems = subMenuContainer ? subMenuContainer.querySelectorAll('li') : [];
            
            // Si no hay término de búsqueda, mostrar todo normal y cerrar menús
            if (searchTerm === '') {
                menuItem.style.display = '';
                menuItem.classList.remove('active');
                
                // Mostrar todos los submenús
                subMenuItems.forEach(function(subItem) {
                    subItem.style.display = '';
                });
                
                // Ocultar el contenedor de submenús para que no se vea expandido
                if (subMenuContainer) {
                    subMenuContainer.style.display = '';
                }
                return;
            }
            
            // Buscar coincidencias en los submenús
            let visibleSubmenus = 0;
            
            subMenuItems.forEach(function(subMenuItem) {
                const subMenuLink = subMenuItem.querySelector('a');
                if (!subMenuLink) return;
                
                const subMenuText = subMenuLink.textContent.toLowerCase().trim();
                
                if (subMenuText.includes(searchTerm)) {
                    subMenuItem.style.display = '';
                    visibleSubmenus++;
                    totalMatches++;
                } else {
                    subMenuItem.style.display = 'none';
                }
            });
            
            // Mostrar/ocultar el menú principal según si tiene submenús visibles
            if (visibleSubmenus > 0) {
                menuItem.style.display = '';
                // Forzar expansión del menú si tiene resultados
                if (!menuItem.classList.contains('active')) {
                    menuItem.classList.add('active');
                }
                // Asegurar que el contenedor de submenús esté visible
                if (subMenuContainer) {
                    subMenuContainer.style.display = 'block';
                }
            } else {
                menuItem.style.display = 'none';
                menuItem.classList.remove('active');
            }
        });
        
        // Mensaje cuando no hay resultados
        const noResults = document.getElementById('no-results-message');
        
        if (searchTerm && totalMatches === 0) {
            if (!noResults) {
                const message = document.createElement('li');
                message.id = 'no-results-message';
                message.style.padding = '20px';
                message.style.textAlign = 'center';
                message.style.color = '#a7b1c2';
                message.innerHTML = '<i class="fa fa-search" style="font-size: 24px; margin-bottom: 10px;"></i><br><span style="font-size: 13px;">No se encontraron resultados para "<b>' + searchTerm + '</b>"</span>';
                document.getElementById('side-menu').appendChild(message);
            }
        } else if (noResults) {
            noResults.remove();
        }
        
        // Mostrar contador de resultados en el placeholder
        if (searchTerm && totalMatches > 0) {
            searchInput.setAttribute('placeholder', totalMatches + ' resultado' + (totalMatches > 1 ? 's' : '') + ' encontrado' + (totalMatches > 1 ? 's' : ''));
        } else if (!searchTerm) {
            searchInput.setAttribute('placeholder', 'Buscar en menú...');
        }
    });
    
    // Limpiar búsqueda con tecla Escape
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('keyup'));
            this.blur();
        }
    });
    
    // Evento blur (ya no necesario para resaltado, pero se mantiene por si acaso)
    searchInput.addEventListener('blur', function() {
        // No se requiere acción especial
    });
    
    // Evento focus
    searchInput.addEventListener('focus', function() {
        // El input mantiene su valor, no se requiere acción especial
    });
});
