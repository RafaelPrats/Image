<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoPerdido extends Model
{
    protected $table = 'pedido_perdido';
    protected $primaryKey = 'id_pedido_perdido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'fecha_pedido',
        'fecha_registro'
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetallePedidoPerdido', 'id_pedido_perdido');
    }
}
