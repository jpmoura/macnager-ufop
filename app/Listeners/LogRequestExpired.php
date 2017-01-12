<?php

namespace App\Listeners;

use App\Events\RequestExpired;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestExpired
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
     * @param  RequestExpired  $event
     * @return void
     */
    public function handle(RequestExpired $event)
    {
        Log::notice('Requisição expirada.', ['requisição' => $event->request->id]);
    }
}
