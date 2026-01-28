<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionVariedad extends Model
{
    protected $table = 'distribucion_variedad';
    protected $primaryKey = 'id_distribucion_variedad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'siglas',
        'id_unidad_medida',
        'longitud',
        'valor',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }
}
