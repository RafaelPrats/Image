<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Mixtos extends Model
{
    protected $table = 'mixtos';
    protected $primaryKey = 'id_mixtos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'id_variedad ',
        'fecha',
        'ramos',
        'porcentaje',
        'tallos',
        'id_cliente',
        'longitud_ramo',
        'ramos_x_caja',
        'piezas',
        'id_proyecto ',
        'id_caja_proyecto ',
        'id_detalle_caja_proyecto ',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function proyecto()
    {
        return $this->belongsTo('\yura\Modelos\Proyecto', 'id_proyecto');
    }

    public function caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\CajaProyecto', 'id_caja_proyecto');
    }

    public function detalle_caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\DetalleCajaProyecto', 'id_detalle_caja_proyecto');
    }
}
