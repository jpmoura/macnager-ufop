<?php

namespace App\Listeners;

use App\Events\RequestStored;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestStored
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
     * @param  RequestStored  $event
     * @return void
     */
    public function handle(RequestStored $event)
    {
        Log::info('Requisição criada', ['requisição' => $event->request->id, 'juiz' => $event->judge->nome]);
    }
}
