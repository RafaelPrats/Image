<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProductoBodega extends Model
{
    protected $table = 'producto_bodega';
    protected $primaryKey = 'id_producto_bodega';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'id_bodega',
        'cantidad',
        'fecha_registro',
    ];
}
