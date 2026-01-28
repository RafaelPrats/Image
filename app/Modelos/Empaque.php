<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Empaque extends Model
{
    protected $table = 'empaque';
    protected $primaryKey = 'id_empaque';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empaque',
        'id_configuracion_empresa',
        'nombre',   // si es de tipo C (caja) "nombre|factor(equivalente a cuantas cajas fulls representa)|peso en Gr
        'fecha_registro',
        'estado',
        'tipo', //	C => Caja E => Envoltura P => Presentacion
        'f_empaque', // Forma de empaque "" => Caja normal, "T" => Sin caja (En mallas de tallos)
        'cod_jire',
        'siglas'
    ];

    public function configuracion_empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_configuracion_empresa');
    }

    public function productos()
    {
        return $this->hasMany('\yura\Modelos\EmpaqueProducto', 'id_empaque');
    }

    public function productosByPlanta($planta)
    {
        $r = $this->hasMany('\yura\Modelos\EmpaqueProducto', 'id_empaque')->where('id_planta', $planta)->get();
        return $r;
    }
}
