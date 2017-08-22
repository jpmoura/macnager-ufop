<?php

namespace App\Events;

use App\Requisicao;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestWakeFailed
{
    use InteractsWithSockets, SerializesModels;

    public $requisicao;
    public $erro;
    public $usuario;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Requisicao $requisicao, $usuario, $erro)
    {
        $this->requisicao = $requisicao;
        $this->usuario = $usuario;
        $this->erro = $erro;
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
