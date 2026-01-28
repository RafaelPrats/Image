<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ciclo extends Model
{
    protected $table = 'ciclo';
    protected $primaryKey = 'id_ciclo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'id_modulo',
        'id_variedad',
        'fecha_registro',
        'estado',
        'area',
        'fecha_inicio',
        'fecha_cosecha',
        'fecha_fin',
        'activo',   // boolean 1
        'poda_siembra', // char(1) Poda, Siembra
        'plantas_iniciales',
        'plantas_muertas',
        'conteo',
        'curva',
        'semana_poda_siembra',
        'desecho',
        'no_recalcular_curva',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function monitoreos()
    {
        return $this->hasMany('\yura\Modelos\Monitoreo', 'id_ciclo');
    }

    public function monitoreos_calibre()
    {
        return $this->hasMany('\yura\Modelos\MonitoreoCalibre', 'id_ciclo');
    }

    public function temperaturas()
    {
        return $this->hasMany('\yura\Modelos\CicloTemperatura', 'id_ciclo');
    }

    public function getTallosCosechados($dias_ini = 1)  // dias_ini: Dias despues del inicio de ciclo a tener en cuenta
    {
        $fin = date('Y-m-d');
        if ($this->fecha_fin != '')
            $fin = $this->fecha_fin;

        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('dr.estado', '=', 1)
            ->where('r.estado', '=', 1)
            ->where('dr.id_modulo', '=', $this->id_modulo)
            ->where('r.fecha_ingreso', '>=', opDiasFecha('+', $dias_ini, $this->fecha_inicio))
            ->where('r.fecha_ingreso', '<=', $fin . ' 23:59:59')
            ->get()[0]->cantidad;

        return $r;
    }

    public function getTallosCosechadosByFecha($fecha)
    {
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('dr.estado', '=', 1)
            ->where('r.estado', '=', 1)
            ->where('dr.id_modulo', '=', $this->id_modulo)
            ->where('r.fecha_ingreso', 'like', $fecha . '%')
            ->where('r.fecha_ingreso', '>=', opDiasFecha('+', 1, $this->fecha_inicio))
            ->get()[0]->cantidad;

        return $r;
    }

    public function get80Porciento()
    {
        /* ================== OBTENER LAS COSECHAS DEL MODULO RELACIONADO AL CICLO =============== */
        $cosechas = DB::table('cosecha as c')
            ->join('recepcion as r', 'r.id_cosecha', '=', 'c.id_cosecha')
            ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
            ->select('c.id_cosecha as id','c.fecha_ingreso')->distinct()
            ->where('c.estado', '=', 1)
            ->where('dr.id_modulo', '=', $this->id_modulo)
            ->where('c.fecha_ingreso', '>=', opDiasFecha('+', 1, $this->fecha_inicio));
        if ($this->fecha_fin != '') {
            $cosechas = $cosechas->where('c.fecha_ingreso', '<=', $this->fecha_fin);
        }
        $cosechas = $cosechas->orderBy('c.fecha_ingreso')->get();

        $meta = round($this->getTallosCosechados() * 0.8, 2);
        $dia = $this->fecha_inicio;
        foreach ($cosechas as $c) {
            $c = Cosecha::find($c->id);
            if ($meta > 0) {
                $meta -= $c->getTotalTallosByModulo($this->id_modulo);

                if ($meta <= 0) {
                    $dia = $c->fecha_ingreso;
                    break;
                }
            } else {
                break;
            }
        }

        return difFechas($dia, $this->fecha_inicio)->days;
    }

    public function getMortalidad()
    {
        if ($this->plantas_actuales() > 0 && $this->plantas_iniciales > 0) {
            $r = ($this->plantas_actuales() / $this->plantas_iniciales) * 100;
            return round(100 - $r, 2);
        }
        return 0;
    }

    public function getDensidadIniciales()
    {
        return round($this->plantas_iniciales / $this->area, 2);
    }

    public function plantas_actuales()
    {
        if ($this->plantas_iniciales > 0)
            if ($this->plantas_muertas > 0)
                return $this->plantas_iniciales - $this->plantas_muertas;
            else
                return $this->plantas_iniciales;
        return 0;
    }

    public function semana()
    {
        return Semana::All()
            ->where('estado', 1)
            ->where('id_variedad', $this->id_variedad)
            ->where('fecha_inicial', '<=', $this->fecha_inicio)
            ->where('fecha_final', '>=', $this->fecha_inicio)
            ->first();
    }

    public function getTallosProyectados()
    {
        return round(($this->plantas_actuales() * $this->conteo) * ((100 - $this->desecho) / 100), 2);
    }

    public function getTemperaturaBySemanaFenograma($semana = null)
    {
        $semana = $semana != null ? $semana : (intval(difFechas($this->fecha_inicio, date('Y-m-d'))->days / 7) + 1);
        $temp = CicloTemperatura::All()
            ->where('estado', 1)
            ->where('id_ciclo', $this->id_ciclo)
            ->where('num_semana', $semana)
            ->fisrt();
        return $temp;
    }

    public function getTemperaturaByFecha($fecha)
    {
        if ($fecha != '') {
            $acumulado = DB::table('temperatura')
                ->select(DB::raw('sum(((minima + maxima) / 2) - 8) as cant'))
                ->where('estado', 1)
                ->where('fecha', '>=', $this->fecha_inicio)
                ->where('fecha', '<=', $fecha)
                ->get()[0]->cant;
            return round($acumulado, 2);
        } else
            return 0;
    }

    function getCalibreByFechaUnitaria($fecha, $unitaria)
    {
        return MonitoreoCalibre::where('fecha', $fecha)
            ->where('id_ciclo', $this->id_ciclo)
            ->where('id_clasificacion_unitaria', $unitaria)
            ->first();
    }

    function getCalibreMonitoreo()
    {
        $total_tallos = 0;
        $total_ramos_est = 0;
        foreach ($this->monitoreos_calibre as $item) {
            $total_tallos += $item->ramos * $item->tallos_x_ramo;
            $total_ramos_est += $item->calibre > 0 ? ($item->ramos * $item->tallos_x_ramo) / $item->calibre : 0;
        }
        return $total_ramos_est > 0 ? round($total_tallos / $total_ramos_est, 2) : 0;
    }

    function getCalibreMonitoreoByFecha($fecha)
    {
        $total_tallos = 0;
        $total_ramos_est = 0;
        foreach ($this->monitoreos_calibre->where('fecha', $fecha) as $item) {
            $total_tallos += $item->ramos * $item->tallos_x_ramo;
            $total_ramos_est += $item->calibre > 0 ? ($item->ramos * $item->tallos_x_ramo) / $item->calibre : 0;
        }
        return $total_ramos_est > 0 ? round($total_tallos / $total_ramos_est, 2) : 0;
    }

    function getCalibreAcumulado()
    {
        $fechas = DB::table('recepcion as r')
            ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
            ->select('r.fecha_ingreso')->distinct()
            ->where('r.estado', 1)
            ->where('dr.estado', 1)
            ->where('dr.id_modulo', $this->id_modulo)
            ->where('r.fecha_ingreso', '>=', opDiasFecha('+', 7, $this->fecha_inicio))
            ->where('r.fecha_ingreso', '<=', $this->fecha_fin)
            ->get();
        $array_fechas = [];
        foreach ($fechas as $f)
            if (!in_array(substr($f->fecha_ingreso, 0, 10), $array_fechas))
                array_push($array_fechas, substr($f->fecha_ingreso, 0, 10));
        $data = [];
        foreach ($array_fechas as $f) {
            $tallos_cosechados = DB::table('recepcion as r')
                ->join('desglose_recepcion as dr', 'dr.id_recepcion', '=', 'r.id_recepcion')
                ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cant'))
                ->where('r.estado', 1)
                ->where('dr.estado', 1)
                ->where('dr.id_modulo', $this->id_modulo)
                ->where('r.fecha_ingreso', 'like', $f . '%')
                ->get()[0]->cant;

            array_push($data, [
                'fecha' => $f,
                'tallos_cosechados' => $tallos_cosechados,
            ]);
        }

        /* ----------------------- CALCULAR --------------------- */
        $total_tallos_cosechados = 0;
        $total_ramos_cosechados = 0;

        foreach ($data as $d) {
            $getCalibreMonitoreoByFecha = $this->getCalibreMonitoreoByFecha($d['fecha']);
            $total_tallos_cosechados += $d['tallos_cosechados'];
            $total_ramos_cosechados += $getCalibreMonitoreoByFecha > 0 ? $d['tallos_cosechados'] / $getCalibreMonitoreoByFecha : 0;
        }
        return $total_ramos_cosechados > 0 ? round($total_tallos_cosechados / $total_ramos_cosechados, 2) : 0;
    }

    function getLuzActual()
    {
        $ciclo_luz = CicloLuz::where('id_ciclo', $this->id_ciclo)
            ->orderBy('fecha', 'desc')
            ->get()
            ->first();
        return $ciclo_luz;
    }

    function getLuzByFecha($fecha)
    {
        $ciclo_luz = CicloLuz::All()
            ->where('id_ciclo', $this->id_ciclo)
            ->where('fecha', $fecha)
            ->first();
        return $ciclo_luz;
    }

    function getLuzBySemana($semana)
    {
        $ciclo_luz = CicloLuz::where('id_ciclo', $this->id_ciclo)
            ->where('fecha', '>=', $semana->fecha_inicial)
            ->where('fecha', '<=', $semana->fecha_final)
            ->orderBy('fecha', 'desc')
            ->first();
        return $ciclo_luz;
    }

    function getLastLaborByLabor($labor)
    {
        return AplicacionCampo::where('id_ciclo', $this->id_ciclo)
            ->where('id_aplicacion', $labor)
            ->orderBy('repeticion')
            ->get()
            ->last();
    }

    function getLastLaborByFecha($labor, $fecha)
    {
        return AplicacionCampo::where('id_ciclo', $this->id_ciclo)
            ->where('id_aplicacion', $labor)
            ->where('fecha', '<=', $fecha)
            ->orderBy('repeticion')
            ->get()
            ->last();
    }

    function getMetrosLineales()
    {
        $suma = ($this->ancho_cama + $this->ancho_camino);
        return $suma > 0 ? round($this->area / $suma, 2) : 0;
    }

    function getCamas()
    {
        return $this->getMetrosLineales() > 0 ? round($this->area / (($this->ancho_cama + $this->ancho_camino) * 30), 2) : 0;
    }

    public function fenograma_costos()
    {
        return $this->hasOne('\yura\Modelos\FenogramaCostos', 'id_ciclo');
    }
}
