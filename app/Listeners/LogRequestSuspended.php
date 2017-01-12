<?php

namespace App\Listeners;

use App\Events\RequestSuspended;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestSuspended
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
     * @param  RequestSuspended  $event
     * @return void
     */
    public function handle(RequestSuspended $event)
    {
        Log::warning('Requisição suspensa', ['requisição' => $event->request->id, 'juiz' => $event->judge->nome]);
    }
}
