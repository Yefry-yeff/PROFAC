<?php

namespace App\Http\Livewire\Menu;

use Livewire\Component;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Models\Rol;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;

class GestionMenu extends Component
{
    public $menus;
    public $submenus;
    public $roles;
    public $estados;

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->menus = Menu::with(['submenus', 'estado'])->orderBy('orden')->get();
        $this->submenus = SubMenu::with(['menu', 'estado', 'roles'])->orderBy('menu_id')->orderBy('orden')->get();
        $this->roles = Rol::where('estado_id', 1)->get();
        $this->estados = Estado::all();
    }

    public function eliminarMenu($idMenu)
    {
        try {
            $menu = Menu::findOrFail($idMenu);
            
            // Verificar si tiene submenus
            if ($menu->submenus()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el menú porque tiene submenus asociados.');
                return;
            }

            $menu->delete();
            $this->cargarDatos();
            session()->flash('success', 'Menú eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el menú: ' . $e->getMessage());
        }
    }

    public function cambiarEstadoMenu($idMenu)
    {
        try {
            $menu = Menu::findOrFail($idMenu);
            $menu->estado_id = $menu->estado_id == 1 ? 2 : 1;
            $menu->save();
            
            $this->cargarDatos();
            session()->flash('success', 'Estado del menú actualizado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    public function eliminarSubmenu($idSubmenu)
    {
        try {
            $submenu = SubMenu::findOrFail($idSubmenu);
            
            // Eliminar relaciones con roles
            $submenu->roles()->detach();
            
            $submenu->delete();
            $this->cargarDatos();
            session()->flash('success', 'Submenu eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el submenu: ' . $e->getMessage());
        }
    }

    public function cambiarEstadoSubmenu($idSubmenu)
    {
        try {
            $submenu = SubMenu::findOrFail($idSubmenu);
            $submenu->estado_id = $submenu->estado_id == 1 ? 2 : 1;
            $submenu->save();
            
            $this->cargarDatos();
            session()->flash('success', 'Estado del submenu actualizado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.menu.gestion-menu');
    }
}
