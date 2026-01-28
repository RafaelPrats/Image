<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SelloHojaRuta extends Model
{
    protected $table = 'sello_hoja_ruta';
    protected $primaryKey = 'id_sello_hoja_ruta';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'id_hoja_ruta',
        'id_agencia_carga',
        'sello',
    ];

    public function hoja_ruta()
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta');
    }

    public function agencia_carga()
    {
        return $this->belongsTo(AgenciaCarga::class, 'id_agencia_carga');
    }
}
