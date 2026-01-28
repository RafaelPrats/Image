<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SobranteRecepcion extends Model
{
    protected $table = 'sobrante_recepcion';
    protected $primaryKey = 'id_sobrante_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'longitud',
        'fecha',
        'cantidad',
        'fecha_registro',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
