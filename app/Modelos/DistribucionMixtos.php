<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionMixtos extends Model
{
    protected $table = 'distribucion_mixtos';
    protected $primaryKey = 'id_distribucion_mixtos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_distribucion_mixtos',
        'id_planta',
        'siglas',
        'id_unidad_medida',
        'fecha',
        'ramos',
        'porcentaje',
        'tallos',
        'id_cliente',
        'longitud_ramo',
        'ramos_x_caja',
        'piezas',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function variedad()
    {
        return Variedad::where([
            ['id_planta', $this->id_planta],
            ['siglas', $this->siglas]
        ])
            ->get()
            ->first();
    }
}
