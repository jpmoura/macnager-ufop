<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requisicao extends Model
{
    protected $table = "requisicoes";
    public $timestamps = false;

    protected $guarded = ['id'];

    public function tipoDoDispositivo()
    {
        return $this->hasOne('App\TipoDispositivo', 'id', 'tipo_dispositivo');
    }

    public function tipoDoUsuario()
    {
        return $this->hasOne('App\TipoUsuario', 'id', 'tipo_usuario');
    }

    public function subrede()
    {
        return $this->belongsTo('App\Subrede');
    }
}
