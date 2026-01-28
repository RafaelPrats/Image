<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ControlPersonal extends Model
{
    protected $table = 'control_personal';
    protected $primaryKey = 'id_control_personal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'id_control_diario',
        'id_personal_detalle',
        'id_actividad',
        'desde',
        'hasta',
        'observaciones',
    ];

    public function control_diario()
    {
        return $this->belongsTo('\yura\Modelos\ControlDiario', 'id_control_diario');
    }

    public function personal_detalle()
    {
        return $this->belongsTo('\yura\Modelos\PersonalDetalle', 'id_personal_detalle');
    }

    public function actividad()
    {
        return $this->belongsTo('\yura\Modelos\Actividad', 'id_actividad');
    }
    public function ausentismo()
    {
        return $this->belongsTo('\yura\Modelos\Ausentismo', 'id_ausentismo');
    }

}
