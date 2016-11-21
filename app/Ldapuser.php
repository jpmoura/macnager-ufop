<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ldapuser extends Model
{
  protected $table = "ldapusers";
  public $timestamps = false;
  protected $primaryKey = 'cpf';

  public function requestsAsOwner() {
    return $this->hasMany('App\Requisicao', 'cpf', 'responsavel');
  }

  public function requestsAsUser()
  {
    return $this->hasMany('App\Requisicao', 'cpf', 'usuario');
  }
}
