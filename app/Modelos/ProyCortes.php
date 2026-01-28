<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyCortes extends Model
{
    protected $table = 'proy_cortes';
    protected $primaryKey = 'id_proy_cortes';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'nombre',
    ];
}
