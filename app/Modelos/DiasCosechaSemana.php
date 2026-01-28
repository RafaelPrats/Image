<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DiasCosechaSemana extends Model
{
    protected $table = 'dias_cosecha_semana';
    protected $primaryKey = 'id_dias_cosecha_semana';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana',
        'cantidad',
    ];
}
