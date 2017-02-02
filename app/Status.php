<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';
    public $timestamps = false;
    protected $guarded= [];

    /**
     * Recupera todas as requisições com um determinado status
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Coleção de Requisicao
     */
    public function requisicoes()
    {
        return $this->hasMany('App\Requisicao', 'status', 'id');
    }
}
