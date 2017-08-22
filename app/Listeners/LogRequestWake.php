<?php

namespace App\Listeners;

use App\Events\RequestWake;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestWake
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
     * @param  RequestWake  $event
     * @return void
     */
    public function handle(RequestWake $event)
    {
        Log::warning('Dispositivo acordado', ['dispositivo' => $event->requisicao->id, 'acordado por' => $event->usuario->nome]);
    }
}
