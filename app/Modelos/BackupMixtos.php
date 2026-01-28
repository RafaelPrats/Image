<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class BackupMixtos extends Model
{
    protected $table = 'backup_mixtos';
    protected $primaryKey = 'id_backup_mixtos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'id_variedad',
        'fecha',
        'ramos',
        'porcentaje',
        'tallos',
        'id_cliente',
        'longitud_ramo',
        'piezas',
        'ramox_x_caja',
        'tallos_x_ramo',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
