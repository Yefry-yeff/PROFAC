<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificacionesHistorial extends Component
{
    use WithPagination;

    public string $filtro = 'pendientes'; // pendientes | todas

    public function updatingFiltro(): void
    {
        $this->resetPage();
    }

    public function marcarLeida(string $id): void
    {
        $notif = Auth::user()->notifications()->where('id', $id)->first();
        if (!$notif) return;

        $notif->markAsRead();
    }

    public function irA(string $id): void
    {
        $notif = Auth::user()->notifications()->where('id', $id)->first();
        if (!$notif) return;

        $url = $notif->data['url'] ?? null;
        $notif->markAsRead();

        if ($url) {
            $this->redirect($url);
        }
    }

    public function marcarTodasLeidas(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $query = Auth::user()->notifications()->latest();

        if ($this->filtro === 'pendientes') {
            $query = Auth::user()->unreadNotifications()->latest();
        }

        $notificaciones = $query->paginate(15);

        return view('livewire.notificaciones-historial', [
            'notificaciones' => $notificaciones,
            'totalNoLeidas'  => Auth::user()->unreadNotifications()->count(),
        ])->layout('layouts.app');
    }
}
