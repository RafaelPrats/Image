<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoModificacion extends Model
{
    protected $table = 'pedido_modificacion';
    protected $primaryKey = 'id_pedido_modificacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_anterior_pedido',
        'id_cliente',
        'id_detalle_especificacionempaque',
        'fecha_pedido',
        'cantidad',
        'operador', //(+ o -)
        'fecha_registro',
        'fecha_nuevo_pedido'
    ];

    public function detalle_especificacionempaque()
    {
        return $this->belongsTo('\yura\Modelos\DetalleEspecificacionEmpaque', 'id_detalle_especificacionempaque');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function getVariedad()
    {
        return Variedad::All()
            ->where('id_planta', $this->id_planta)
            ->where('siglas', $this->siglas)
            ->where('estado', 1)
            ->first();
    }
}
