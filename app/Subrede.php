<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subrede extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "endereco", "cidr", "faixa_id", "descricao",
    ];

    protected $hidden = [
        "id", "faixa_id",
    ];

    public function faixa() {
        return $this->belongsTo("App\\Faixa");
    }
}
