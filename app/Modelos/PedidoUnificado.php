<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoUnificado extends Model
{
    protected $table = 'pedido_unificado';
    protected $primaryKey = 'id_pedido_unificado';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_cliente',
        'fecha_registro',
        'orden_fija',
        'id_usuario',
    ];

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
