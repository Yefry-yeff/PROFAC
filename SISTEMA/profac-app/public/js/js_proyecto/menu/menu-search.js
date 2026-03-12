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
            
            // Si no hay término de búsqueda, restaurar estado normal (sin active de búsqueda)
            if (searchTerm === '') {
                menuItem.style.display = '';
                // Solo quitar si fue añadido por la búsqueda, no por el menú normal
                menuItem.classList.remove('search-active');
                menuItem.classList.remove('active');
                
                subMenuItems.forEach(function(subItem) {
                    subItem.style.display = '';
                });
                
                if (subMenuContainer) {
                    subMenuContainer.style.display = '';
                    subMenuContainer.style.position = '';
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
                menuItem.classList.add('search-active');
                // Expandir el submenú inline (sin depender del class 'active' de MetisMenu)
                if (subMenuContainer) {
                    subMenuContainer.style.display = 'block';
                    // En modo mini-navbar los submenús son position:fixed; aquí los forzamos inline
                    subMenuContainer.style.position = 'static';
                    subMenuContainer.style.width = '';
                    subMenuContainer.style.left = '';
                }
            } else {
                menuItem.style.display = 'none';
                menuItem.classList.remove('search-active');
                if (subMenuContainer) {
                    subMenuContainer.style.display = '';
                    subMenuContainer.style.position = '';
                }
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
