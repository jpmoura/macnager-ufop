<?php

namespace App\Listeners;

use App\Events\RequestDenied;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestDenied
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
     * @param  RequestDenied  $event
     * @return void
     */
    public function handle(RequestDenied $event)
    {
        Log::notice('Requisição negada', ['requisição' => $event->request->id, 'juiz' => $event->judge->nome]);
    }
}
