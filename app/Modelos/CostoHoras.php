<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CostoHoras extends Model
{
    protected $table = 'costo_horas';
    protected $primaryKey = 'id_costo_horas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'sueldo_promedio',
        'valor_hora',
        'prov_dt',
        'prov_dc',
        'prov_reserva',
        'aporte_patronal',
        'total_provisiones',
        'valor_hora_provisiones',
    ];
}
