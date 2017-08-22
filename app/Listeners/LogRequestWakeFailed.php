<?php

namespace App\Listeners;

use App\Events\RequestWakeFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogRequestWakeFailed
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
     * @param  RequestWakeFailed  $event
     * @return void
     */
    public function handle(RequestWakeFailed $event)
    {
        Log::error('Falha ao ligar dispositivo', ['requisição' => $event->requisicao->id, 'tentado por' => $event->usuario->nome, 'erro' => $event->erro]);
    }
}
