<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Camion extends Model
{
    protected $table = 'camion';
    protected $primaryKey = 'id_camion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_transportista',
        'placa',
        'modelo',
        'estado'
    ];

    public function transportista(){
        $this->belongsTo('\yura\Modelos\Transportista', 'id_transportista');
    }
}
