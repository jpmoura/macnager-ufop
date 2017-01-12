<?php

namespace App\Mail;

use App\Requisicao;
use App\Ldapuser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequestApproved extends Mailable
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
        return $this->view('emails.requisicao.approved')->subject('[ICEA] Sistema MACnager - Requisição Aprovada');
    }
}
