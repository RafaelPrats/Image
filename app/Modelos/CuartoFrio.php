<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CuartoFrio extends Model
{
    protected $table = 'cuarto_frio';
    protected $primaryKey = 'id_cuarto_frio';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'fecha',
        'cantidad',
        'disponibles',
        'id_variedad',
        'id_empaque',
        'tallos_x_ramo',
        'longitud_ramo',
        'id_dato_exportacion ',
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

    public function dato_exportacion()
    {
        return $this->belongsTo('\yura\Modelos\DatosExportacion', 'id_dato_exportacion');
    }
}
