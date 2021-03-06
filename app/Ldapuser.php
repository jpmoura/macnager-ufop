<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ldapuser extends Authenticatable
{

    use Notifiable;

    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'cpf', 'nome', 'email', 'nivel', 'status',
    ];

    protected $hiddem = [
        'id', 'remember_token'
    ];

    public function requestsAsOwner()
    {
        return $this->hasMany('App\Requisicao', 'cpf', 'responsavel');
    }

    public function requestsAsUser()
    {
        return $this->hasMany('App\Requisicao', 'cpf', 'usuario');
    }

    public function isAdmin()
    {
        return $this->nivel == 1;
    }
}
