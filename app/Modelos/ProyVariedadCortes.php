<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyVariedadCortes extends Model
{
    protected $table = 'proy_variedad_cortes';
    protected $primaryKey = 'id_proy_variedad_cortes';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_cortes',
        'fecha',
        'cantidad',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function corte()
    {
        return $this->belongsTo('\yura\Modelos\ProyCortes', 'id_cortes');
    }
}
