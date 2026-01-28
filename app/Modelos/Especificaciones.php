<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Especificaciones extends Model
{
    protected $table = 'especificaciones';
    protected $primaryKey = 'id_especificaciones';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empaque_c',
        'id_planta',
        'id_variedad',
        'id_empaque_p',
        'ramos_x_caja',
        'tallos_x_ramos',
        'longitud_ramo',
        'estado',
        'fecha_registro',
        'id_unidad_medida',
        'id_cliente',
    ];

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
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
