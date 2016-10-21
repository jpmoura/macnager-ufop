<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requisicao extends Model
{
  protected $table = "requisicoes";
  public $timestamps = false;

  protected $guarded = [];

  public function tipoDoDispositivo()
  {
    return $this->hasOne('App\TipoDispositivo', 'id', 'tipo_dispositivo');
  }

  public function tipoDoUsuario()
  {
    return $this->hasOne('App\TipoUsuario', 'id', 'tipo_usuario');
  }

  public function usuarioDaRequisicao()
  {
    return $this->hasOne('App\Ldapuser', 'cpf', 'usuario');
  }

  public function responsavelDaRequisicao()
  {
    return $this->hasOne('App\Ldapuser', 'cpf', 'responsavel');
  }
}
