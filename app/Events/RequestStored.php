<?php

namespace App\Events;

use App\Ldapuser;
use App\Requisicao;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequestStored
{
    use InteractsWithSockets, SerializesModels;

    public $request;
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Requisicao $request, Ldapuser $user)
    {
        $this->request = $request;
        $this->user = $user;
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
