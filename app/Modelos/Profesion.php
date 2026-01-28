<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Profesion extends Model
{
    protected $table = 'profesion';
    protected $primaryKey = 'id_profesion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
