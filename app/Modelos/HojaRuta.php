<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class HojaRuta extends Model
{
    protected $table = 'hoja_ruta';
    protected $primaryKey = 'id_hoja_ruta';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'fecha',
        'id_transportista',
        'id_camion',
        'placa',
        'id_conductor',
        'responsable',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleHojaRuta::class, 'id_hoja_ruta');
    }

    public function sellos()
    {
        return $this->hasMany(SelloHojaRuta::class, 'id_hoja_ruta');
    }

    public function transportista()
    {
        return $this->belongsTo(Transportista::class, 'id_transportista');
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class, 'id_camion');
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor');
    }

    public function tieneSellos()
    {
        foreach ($this->sellos as $sello) {
            if ($sello->sello != '')
                return true;
        }
        return false;
    }
}
