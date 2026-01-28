<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleEspecificacionEmpaqueRamosXCajaPerdido extends Model
{
    protected $table = 'detalle_especificacionempaque_ramos_x_caja_perdido';
    protected $primaryKey = 'id_detalle_especificacionempaque_ramos_x_caja_perdido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_pedido_perdido',
        'id_detalle_especificacionempaque',
        'cantidad',
    ];
}
