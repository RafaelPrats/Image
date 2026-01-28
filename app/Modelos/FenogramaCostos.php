<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class FenogramaCostos extends Model
{
    protected $table = 'fenograma_costos';
    protected $primaryKey = 'id_fenograma_costos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'plantas',
        'luz',
        'giberelico',
        'desbrote',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }
}
