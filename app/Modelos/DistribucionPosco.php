<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionPosco extends Model
{
    protected $table = 'distribucion_posco';
    protected $primaryKey = 'id_distribucion_posco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_empaque',
        'fecha',
        'tallos_x_ramo',
        'longitud',
        'id_clasificador',
        'cantidad',
        'id_dato_exportacion',
        'valor_marcacion',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empaque()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque');
    }

    public function clasificador()
    {
        return $this->belongsTo('\yura\Modelos\Clasificador', 'id_clasificador');
    }

    public function dato_exportacion()
    {
        return $this->belongsTo('\yura\Modelos\DatosExportacion', 'id_dato_exportacion');
    }
}
