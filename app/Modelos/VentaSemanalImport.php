<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class VentaSemanalImport extends Model
{
    protected $table = 'venta_semanal_import';
    protected $primaryKey = 'id_venta_semanal_import';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'semana',
        'ramos',
        'venta',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
