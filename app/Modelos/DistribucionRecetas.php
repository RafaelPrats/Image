<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionRecetas extends Model
{
    protected $table = 'distribucion_recetas';
    protected $primaryKey = 'id_distribucion_recetas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'siglas',
        'fecha',
        'tallos',
        'id_cliente',
        'longitud_ramo',
        'id_detalle_pedido',
        'id_detalle_especificacionempaque',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
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
        ])->first();
    }
}
