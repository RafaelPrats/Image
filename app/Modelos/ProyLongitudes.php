<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyLongitudes extends Model
{
    protected $table = 'proy_longitudes';
    protected $primaryKey = 'id_proy_longitudes';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'nombre',
        'orden',
    ];
}
