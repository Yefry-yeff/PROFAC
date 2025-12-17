<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckUserStatusBeforeLogin
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Attempting  $event
     * @return void
     */
    public function handle(Attempting $event)
    {
        $credentials = $event->credentials;
        
        if (isset($credentials['email'])) {
            $user = DB::table('users')
                ->where('email', $credentials['email'])
                ->first();
            
            if ($user && $user->estado_id != 1) {
                throw ValidationException::withMessages([
                    'email' => ['Contraseña o usuario incorrectos.'],
                ]);
            }
        }
    }
}
