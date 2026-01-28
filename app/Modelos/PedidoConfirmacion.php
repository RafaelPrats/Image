<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoConfirmacion extends Model
{
    protected $table = 'pedido_confirmacion';
    protected $primaryKey = 'id_pedido_confirmacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_planta',
        'ejecutado',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }
}
