<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleProyeccionCampoSemanalAplicacion extends Model
{
    protected $table = 'detalle_proyeccion_campo_semanal_aplicacion';
    protected $primaryKey = 'id_detalle_proyeccion_campo_semanal_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyeccion_campo_semanal_aplicacion',
        'id_mano_obra',
        'id_producto',
        'dosis',
        'rendimiento',
        'id_unidad_medida',
        'factor_conversion',
        'id_unidad_conversion',
    ];

    public function proyeccion_campo_semanal_aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\ProyeccionCampoSemanalAplicacion', 'id_proyeccion_campo_semanal_aplicacion');
    }

    public function mano_obra()
    {
        return $this->belongsTo('\yura\Modelos\ManoObra', 'id_mano_obra');
    }

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }

    public function unidad_conversion()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_conversion');
    }
}
