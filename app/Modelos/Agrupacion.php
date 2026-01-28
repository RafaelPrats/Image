<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Agrupacion extends Model
{
    protected $table = 'agrupacion';
    protected $primaryKey = 'id_agrupacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
