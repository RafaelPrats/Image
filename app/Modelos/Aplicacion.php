<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Aplicacion extends Model
{
    protected $table = 'aplicacion';
    protected $primaryKey = 'id_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'dia_ini',
        'semana_ini',
        'repeticiones',
        'veces_x_semana',
        'poda_siembra', // T podas y siembras; S solo siembras; P solo podas
        'estado',
        'litro_x_cama',
        'tipo',
        'id_aplicacion_matriz',
    ];

    public function aplicacion_matriz()
    {
        return $this->belongsTo('\yura\Modelos\AplicacionMatriz', 'id_aplicacion_matriz');
    }

    public function parametros()
    {
        return $this->hasMany('\yura\Modelos\ParametroAplicacion', 'id_aplicacion');
    }

    public function variedades()
    {
        return $this->hasMany('\yura\Modelos\AplicacionVariedad', 'id_aplicacion');
    }

    public function getTipo()
    {
        $tipos = [
            'S' => 'Sanidad',
            'C' => 'Cultural',
        ];
        return $tipos[$this->tipo];
    }

    public function getDetallesParametrizados()
    {
        $listado = [];
        foreach ($this->detalles->sortBy('id_mano_obra') as $det) {
            $parametro = '';
            $tipo_par = $det->getTipoParametros();
            if ($tipo_par == -1) {  // tiene mas de un tipo de parametro
                //dump('tiene mas de un tipo de parametro');
            } elseif ($tipo_par == '') { // no tiene parametros
                //dump('no tiene parametros');
            } else {    // tiene parametros
                if ($tipo_par == 'E') { // parametro estandar
                    $parametro = $det->parametros[0];
                }
                if ($tipo_par == 'D') { // parametro Delta Acum. 10 días
                    //dump('tiene parametros - parametro Delta Acum. 10 días');
                    $last_fecha = DB::table('temperatura')
                        ->select(DB::raw('max(fecha) as fecha'))
                        ->where('estado', 1)
                        ->get()[0]->fecha;
                    $delta_acum_10_dias = DB::table('temperatura')
                        ->select(DB::raw('sum(maxima - minima) as cantidad'))
                        ->where('fecha', '>=', opDiasFecha('-', 9, $last_fecha))
                        ->where('fecha', '<=', $last_fecha)
                        ->get()[0]->cantidad;
                    $delta_acum_10_dias = round($delta_acum_10_dias);
                    foreach ($det->parametros as $par)
                        if ($delta_acum_10_dias >= $par->desde && $delta_acum_10_dias <= $par->hasta)
                            $parametro = $par;
                }
            }
            $listado[] = [
                'det' => $det,
                'parametro' => $parametro,
                'nombre_det' => $det->id_mano_obra != '' ? $det->mano_obra->nombre : $det->producto->nombre,
            ];
        }
        return $listado;
    }
}