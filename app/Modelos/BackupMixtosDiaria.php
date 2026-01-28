<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class BackupMixtosDiaria extends Model
{
    protected $table = 'backup_mixtos_diaria';
    protected $primaryKey = 'id_backup_mixtos_diaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'siglas',
        'fecha',
        'ramos',
        'porcentaje',
        'tallos',
        'id_cliente',
        'longitud_ramo',
        'id_unidad_medida',
        'ramox_x_caja',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }
}
