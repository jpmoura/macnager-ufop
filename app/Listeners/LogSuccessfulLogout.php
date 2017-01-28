<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        if(is_null($event->user)) Log::info('Usuário realizou logout.', ['cpf' => 'Desconhecido', 'nome' => 'Desconhecido']);
        else Log::info('Usuário realizou logout.', ['cpf' => $event->user->cpf, 'nome' => $event->user->nome]);
    }
}
