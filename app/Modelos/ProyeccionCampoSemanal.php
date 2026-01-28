<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProyeccionCampoSemanal extends Model
{
    protected $table = 'proyeccion_campo_semanal';
    protected $primaryKey = 'id_proyeccion_campo_semanal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_modulo',
        'semana',
        'id_variedad',
        'num_sem',
        'poda_siembra',
    ];

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function aplicaciones()
    {
        return $this->hasMany('\yura\Modelos\ProyeccionCampoSemanalAplicacion', 'id_proyeccion_campo_semanal');
    }

    public function getResumenAplicaciones()
    {
        return DB::table('proyeccion_campo_semanal_aplicacion')
            ->select('app_nombre', DB::raw('count(*) as cant'),
                DB::raw('min(app_repeticion) as min'),
                DB::raw('max(app_repeticion) as max'))
            ->where('id_proyeccion_campo_semanal', $this->id_proyeccion_campo_semanal)
            ->groupBy('app_nombre')
            ->orderBy('app_nombre')
            ->get();
    }
}
