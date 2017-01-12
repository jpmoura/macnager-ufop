<?php

namespace App\Listeners;

use App\Events\DeviceStored;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogDeviceStored
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
     * @param  DeviceStored  $event
     * @return void
     */
    public function handle(DeviceStored $event)
    {
        Log::info('Dispositivo adicionado.', ['requisicao' => $event->request->id]);
    }
}
