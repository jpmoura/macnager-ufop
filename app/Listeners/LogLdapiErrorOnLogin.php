<?php

namespace App\Listeners;

use App\Events\LdapiErrorOnLogin;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogLdapiErrorOnLogin
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
     * @param  LdapiErrorOnLogin  $event
     * @return void
     */
    public function handle(LdapiErrorOnLogin $event)
    {
        Log::critical('Erro do LDAPI durante login.', ['usuario' => $event->credentials['user'], 'senha' => $event->credentials['password']]);
    }
}
