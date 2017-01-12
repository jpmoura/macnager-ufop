<?php

namespace App\Listeners;

use App\Events\RequestApproved;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestApproved
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
     * @param  RequestApproved  $event
     * @return void
     */
    public function handle(RequestApproved $event)
    {
        Log::info('Requisição aprovada.', ['requisicao' => $event->request->id, 'juiz' => $event->judge->nome]);
    }
}
