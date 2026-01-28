<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoProceso extends Model
{
    protected $table = 'pedido_proceso';
    protected $primaryKey = 'id_pedido_proceso';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'estado',
        'descripcion',
        'id_usuario',
        'tipo_proceso',
        'progreso',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
