<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class InventarioRecepcion extends Model
{
    protected $table = 'inventario_recepcion';
    protected $primaryKey = 'id_inventario_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'fecha',
        'cantidad',
        'id_variedad',
        'id_modulo',
        'cantidad',
        'disponibles',
        'apertura ',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }
}
