<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificacionesBell extends Component
{
    public int    $count          = 0;
    public array  $notificaciones = [];
    public bool   $mostrarPanel   = false;

    protected $listeners = ['notificacionNueva' => 'cargar'];

    public function mount(): void
    {
        $this->cargar();
    }

    public function cargar(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $this->count = $user->unreadNotifications()->count();

        $this->notificaciones = $user->unreadNotifications()
            ->latest()
            ->take(12)
            ->get()
            ->map(fn ($n) => [
                'id'      => $n->id,
                'data'    => $n->data,
                'tiempo'  => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function togglePanel(): void
    {
        $this->mostrarPanel = !$this->mostrarPanel;
        if ($this->mostrarPanel) {
            $this->cargar();
        }
    }

    public function marcarLeida(string $id): void
    {
        $notif = Auth::user()->notifications()->where('id', $id)->first();
        if (!$notif) return;

        $notif->markAsRead();
        $this->cargar();
    }

    public function marcarTodasLeidas(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->cargar();
        $this->mostrarPanel = false;
    }

    public function render()
    {
        return view('livewire.notificaciones-bell');
    }
}
