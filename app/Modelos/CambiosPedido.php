<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CambiosPedido extends Model
{
    protected $table = 'cambios_pedido';
    protected $primaryKey = 'id_cambios_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_planta',
        'id_variedad',
        'id_empaque_p',
        'id_empaque_c',
        'fecha_anterior',
        'piezas',
        'fecha_registro',
        'fecha_actual',
        'cambio_fecha',
        'id_usuario',
        'ramos',
        'tallos',
        'ramos_x_caja',
        'tallos_x_ramo',
        'longitud_ramo',
        'usar',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empaque_p()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque_p');
    }

    public function empaque_c()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque_c');
    }
}
