<?php

namespace App\Listeners;

use App\Events\RequestExcluded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestExcluded
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
     * @param  RequestExcluded  $event
     * @return void
     */
    public function handle(RequestExcluded $event)
    {
        Log::notice('Requisição desligada.', ['requisição' => $event->request->id, 'juiz' => $event->judge->nome]);
    }
}
