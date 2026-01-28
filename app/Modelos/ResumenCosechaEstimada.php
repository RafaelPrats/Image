<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenCosechaEstimada extends Model
{
    protected $table = 'resumen_cosecha_estimada';
    protected $primaryKey = 'id_resumen_cosecha_estimada';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'longitud',
        'fecha',
        'solidos',
        'mixtos',
        'cambios',
        'tallos_bqt',
    ];

    public function variedad()
    {
        $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
