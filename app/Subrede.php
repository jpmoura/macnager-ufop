<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subrede extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "endereco", "cidr", "tipo_subrede_id", "descricao", "ignorar_broadcast", "ignorar_gateway",
    ];

    protected $hidden = [
        "id", "tipo_subrede_id",
    ];

    public function tipo() {
        return $this->belongsTo('App\TipoSubrede', 'tipo_subrede_id', 'id');
    }
}
