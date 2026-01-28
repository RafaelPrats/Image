<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Clasificador extends Model
{
    protected $table = 'clasificador';
    protected $primaryKey = 'id_clasificador';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
