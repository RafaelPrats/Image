<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleEspecificacionEmpaqueProducto extends Model
{
    protected $table = 'detalle_especificacionempaque_producto';
    protected $primaryKey = 'id_detalle_especificacionempaque_producto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_especificacionempaque',
        'id_producto',
        'cantidad',
        'fecha_registro'
    ];
}
