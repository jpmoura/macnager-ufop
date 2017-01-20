<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Faixa extends Model
{
    public $timestamps = false;

    protected $hidden = [
        "id",
    ];

    protected $fillable = [
        "endereco", "descricao",
    ];

    public function subredes()
    {
        return $this->hasMany('App\\Subrede');
    }
}
