<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionMixtosDiaria extends Model
{
    protected $table = 'distribucion_mixtos_diaria';
    protected $primaryKey = 'id_distribucion_mixtos_diaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'siglas',
        'fecha',
        'cantidad',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }
}
