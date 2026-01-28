<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MonitoreoCalibre extends Model
{
    protected $table = 'monitoreo_calibre';
    protected $primaryKey = 'id_monitoreo_calibre';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'fecha_registro',
        'id_ciclo',
        'fecha',
        'calibre',
        'ramos',
        'tallos_x_ramo',
        'id_clasificacion_unitaria',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function clasificacion_unitaria()
    {
        return $this->belongsTo('\yura\Modelos\ClasificacionUnitaria', 'id_clasificacion_unitaria');
    }
}