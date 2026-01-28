<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyVariedadSemana extends Model
{
    protected $table = 'proy_variedad_semana';
    protected $primaryKey = 'id_proy_variedad_semana';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'siglas',
        'semana',
        'cantidad',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }
}
