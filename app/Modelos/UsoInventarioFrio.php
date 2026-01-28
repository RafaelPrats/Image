<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsoInventarioFrio extends Model
{
    protected $table = 'uso_inventario_frio';
    protected $primaryKey = 'id_uso_inventario_frio';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_inventario_frio',
        'fecha_pedido',
        'ramos',
    ];

    public function inventario_frio()
    {
        return $this->belongsTo('\yura\Modelos\InventarioFrio', 'id_inventario_frio');
    }
}
