<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Discapacidad extends Model
{
    protected $table = 'discapacidad';
    protected $primaryKey = 'id_discapacidad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
