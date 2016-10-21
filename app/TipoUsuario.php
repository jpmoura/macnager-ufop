<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
  protected $table = "tipo_usuario";
  public $timestamps = false;

  protected $fillable = ['id', 'descricao'];

  public function requisicoes()
  {
    return $this->belongsTo('App\Requisicao', 'tipo_usuario');
  }
}
