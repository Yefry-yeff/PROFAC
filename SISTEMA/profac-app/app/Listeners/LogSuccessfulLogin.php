<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $userAgent = Request::header('User-Agent');
        
        LoginHistory::create([
            'user_id' => $event->user->id,
            'nombre' => $event->user->name,
            'terminal' => $userAgent ? substr($userAgent, 0, 255) : null,
            'ip_address' => Request::ip(),
            'fecha_ingreso' => now(),
        ]);
    }
}
