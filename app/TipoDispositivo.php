<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoDispositivo extends Model
{
  protected $table = "tipo_dispositivo";
  public $timestamps = false;

  protected $fillable = ['id', 'descricao'];

  public function requisicoes()
  {
    return $this->belongsTo('App\Requisicao', 'tipo_dispositivo');
  }
}
