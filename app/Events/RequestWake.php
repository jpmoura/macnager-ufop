<?php

namespace App\Events;

use App\Requisicao;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestWake
{
    use InteractsWithSockets, SerializesModels;

    public $requisicao;
    public $usuario;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Requisicao $requisicao, $usuario)
    {
        $this->requisicao = $requisicao;
        $this->usuario = $usuario;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
