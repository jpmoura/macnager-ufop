<?php

namespace App\Listeners;

use App\Events\LdapiErrorOnSearch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogLdapiErrorOnSearch
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
     * @param  LdapiErrorOnSearch  $event
     * @return void
     */
    public function handle(LdapiErrorOnSearch $event)
    {
        Log::critical('Erro LDAPI durante busca.', ['usuario' => $event->user->nome]);
    }
}
