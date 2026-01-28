<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionMixtosSemana extends Model
{
    protected $table = 'distribucion_mixtos_semana';
    protected $primaryKey = 'id_distribucion_mixtos_semana';
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
