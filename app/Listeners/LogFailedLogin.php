<?php

namespace App\Listeners;

use App\Events\LoginFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LoginFailed  $event
     * @return void
     */
    public function handle(LoginFailed $event)
    {
        Log::warning('Login falhou.', ['usuario' => $event->credentials['username'], 'senha' => $event->credentials['password']]);
    }
}
