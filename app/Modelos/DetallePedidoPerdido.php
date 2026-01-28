<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetallePedidoPerdido extends Model
{
    protected $table = 'detalle_pedido_perdido';
    protected $primaryKey = 'id_detalle_pedido_perdido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente_especificacion',
        'id_pedido_perdido',
        'id_agencia_carga',
        'cantidad',
        'precio',
        'fecha_registro'
    ];

    public function cliente_especificacion()
    {
        return $this->belongsTo('yura\Modelos\ClientePedidoEspecificacion', 'id_cliente_especificacion');
    }

    public function agencia_carga()
    {
        return $this->belongsTo('yura\Modelos\AgenciaCarga', 'id_agencia_carga');
    }

    public function pedido_perdido()
    {
        return $this->belongsTo('yura\Modelos\Pedido', 'id_pedido_perdido');
    }
}
