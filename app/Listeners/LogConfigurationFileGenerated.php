<?php

namespace App\Listeners;

use App\Events\NewConfigurationFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogConfigurationFileGenerated
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
     * @param  NewConfigurationFile  $event
     * @return void
     */
    public function handle(NewConfigurationFile $event)
    {
        Log::info('Novo arquivo de configuração foi criado.');
    }
}
