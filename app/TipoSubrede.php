<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoSubrede extends Model
{
    public $timestamps = false;
    protected $table = "tipo_subrede";

    public function subredes()
    {
        return $this->hasMany('App\Subrede', 'tipo_subrede_id', 'id');
    }
}
