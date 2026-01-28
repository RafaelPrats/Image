<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadDiaria extends Model
{
    protected $table = 'disponibilidad_diaria';
    protected $primaryKey = 'id_disponibilidad_diaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_variedad',
        'longitud_ramo',
        'tallos_x_ramo',
        'ramos_x_caja',
        'tipo_caja',
        'precio',
    ];

    public function variedad()
    {
        return $this->belongsTo('yura\Modelos\Variedad', 'id_variedad');
    }
}
