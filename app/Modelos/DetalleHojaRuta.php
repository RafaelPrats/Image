<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleHojaRuta extends Model
{
    protected $table = 'detalle_hoja_ruta';
    protected $primaryKey = 'id_detalle_hoja_ruta';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'id_hoja_ruta',
        'id_proyecto',
        'orden',
    ];

    public function hoja_ruta()
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto');
    }
}
