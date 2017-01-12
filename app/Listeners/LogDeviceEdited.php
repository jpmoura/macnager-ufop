<?php

namespace App\Listeners;

use App\Events\DeviceEdited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogDeviceEdited
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
     * @param  DeviceEdited  $event
     * @return void
     */
    public function handle(DeviceEdited $event)
    {
        Log::info('Dipositivo editado.', ['requisicao' => $event->request->id]);
    }
}
