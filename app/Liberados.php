<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Liberados extends Model
{
  protected $table = "liberados";
  public $timestamps = false;

  protected $fillable = ['ip', 'mac', 'descricao', 'autor', 'data', 'validade', 'requisicao'];

  // ligação com a requisicao
  public function request()
  {
    return $this->hasOne('App\Requisicao', 'id', 'requisicao');
  }

  public function tipoDispositivo()
    {
        return $this->request->tipoDispositivo();
    }
}
