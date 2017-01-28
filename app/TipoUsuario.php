<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
  protected $table = "tipo_usuario";
  public $timestamps = false;

  protected $fillable = ['descricao'];
  protected $hidden = ['id'];

  public function requisicoes()
  {
    return $this->hasMany('App\Requisicao', 'tipo_usuario');
  }
}
