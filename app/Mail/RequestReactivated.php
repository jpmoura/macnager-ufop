<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Ldapuser;
use App\Requisicao;

class RequestReactivated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Ldapuser $user, Requisicao $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.requisicao.reactivated')->subject('[ICEA] Sistema MACnager - Requisição Reativada');
    }
}
