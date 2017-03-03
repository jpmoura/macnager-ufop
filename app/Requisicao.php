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

    public function tipoStatus()
    {
        return $this->hasOne('App\Status', 'id', 'status');
    }

    public function subrede()
    {
        return $this->belongsTo('App\Subrede');
    }

    /**
     * Define o atributo MAC como sempre com as letras em maiúsculo
     *
     * @param  string  $value  String representando o endereço MAC
     * @return void
     */
    public function setMacAttribute($value)
    {
        $this->attributes['mac'] = strtoupper($value);
    }

    /**
     * Recupera o endereço MAC da requisicição.
     *
     * @param  string  $value  Valor do atributo no banco de ddados
     * @return string  Endereço MAC em maiúsculo
     */
    public function getMacAttribute($value)
    {
        return strtoupper($value);
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
