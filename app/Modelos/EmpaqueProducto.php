<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class EmpaqueProducto extends Model
{
    protected $table = 'empaque_producto';
    protected $primaryKey = 'id_empaque_producto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empaque',
        'id_producto',
        'unidades'
    ];

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
    }

    public function empaque()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque');
    }
}
