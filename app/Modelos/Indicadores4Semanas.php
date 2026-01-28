<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Indicadores4Semanas extends Model
{
    protected $table = 'indicadores_4_semanas';
    protected $primaryKey = 'id_indicadores_4_semanas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana',
        'costo_x_planta',
        'campo_ha_semana',
        'cosecha_x_tallo',
        'postcosecha_x_tallo',
        'costo_total_x_tallo',
        'precio_x_tallo',
        'desecho_cosecha',
        'venta_m2',
        'costos_m2',
        'ebitda_m2',
    ];
}
