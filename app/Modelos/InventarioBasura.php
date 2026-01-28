<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class InventarioBasura extends Model
{
    protected $table = 'inventario_basura';
    protected $primaryKey = 'id_inventario_basura';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'fecha',
        'cantidad',
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
