<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Actividad;
use yura\Modelos\Area;
use yura\Modelos\Pedido;
use yura\Modelos\Semana;
use yura\Modelos\ResumenSemanalTotal;
use yura\Modelos\ResumenCostosSemanal;

class UpdateResumenTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumen_total:update_semanal {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar la tabla resumen_semanal_total para los costos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        dump('<<<<< ! >>>>> Ejecutando comando "resumen_total:update_semanal" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "resumen_total:update_semanal" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');

        if ($desde_par <= $hasta_par) {
            if ($desde_par != 0)
                $semana_desde = Semana::All()->where('estado', 1)->where('codigo', $desde_par)->first();
            else
                $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
            if ($hasta_par != 0)
                $semana_hasta = Semana::All()->where('estado', 1)->where('codigo', $hasta_par)->first();
            else
                $semana_hasta = getSemanaByDate(date('Y-m-d'));

            Log::info('SEMANA PARAMETRO DESDE: ' . $desde_par . ' => ' . $semana_desde->codigo);
            Log::info('SEMANA PARAMETRO HASTA: ' . $hasta_par . ' => ' . $semana_hasta->codigo);

            $array_semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->where('estado', 1)
                ->orderBy('codigo')
                ->get();

            foreach ($array_semanas as $pos_s => $semana) {
                dump(porcentaje($pos_s + 1, count($array_semanas), 1) . '% - sem: ' . ($pos_s + 1) . '/' . count($array_semanas));
                $model = ResumenSemanalTotal::All()
                    ->where('codigo_semana', $semana->codigo)
                    ->first();
                if ($model == '') {
                    $model = new ResumenSemanalTotal();
                    $model->codigo_semana = $semana->codigo;
                    $model->valor = 0;
                }

                /* ----------------------------- venta ------------------------- */
                /*$valor = DB::table('proyeccion_venta_semanal_real')
                    ->select(DB::raw('sum(valor) as cant'))
                    ->where('codigo_semana', $sem)->get()[0]->cant;*/

                $valor = 0;
                if ($semana->codigo < 2142) {   // calcular las ventas a traves de los pedidos
                    $pedidos = Pedido::where('estado', 1)
                        ->where('fecha_pedido', '>=', $semana->fecha_inicial)
                        ->where('fecha_pedido', '<=', $semana->fecha_final)
                        ->get();
                    foreach ($pedidos as $p) {
                        if (!getFacturaAnulada($p->id_pedido)) {
                            $precio = $p->getPrecioByPedido();
                            $valor += $precio;
                        }
                    }
                } else {    // calcular las ventas a traves de clasificacion verde
                    $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                        ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                        ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                        ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                        ->where('d.estado', 1)
                        ->where('v.estado', 1)
                        ->where('v.fecha_ingreso', '>=', $semana->fecha_inicial)
                        ->where('v.fecha_ingreso', '<=', $semana->fecha_final)
                        ->get();
                    foreach ($c_unitarias as $u) {
                        $tallos = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as tallos'),
                                DB::raw('sum(descartes) as descartes'))
                            ->where('d.estado', 1)
                            ->where('v.estado', 1)
                            ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                            ->where('v.fecha_ingreso', '>=', $semana->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $semana->fecha_final)
                            ->get()[0];
                        $valor += ($tallos->tallos * $u->precio_venta) - ($tallos->descartes * $u->precio_venta);
                    }
                }
                $model->valor = $valor != '' ? $valor : 0;

                /* ----------------------------- campo ------------------------- */
                $ids_areas = [];
                $areas = Area::where('estado', 1)->where('nombre', 'like', 'CAMPO%')->get();
                foreach ($areas as $a)
                    array_push($ids_areas, $a->id_area);
                $campo_mp = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->whereIn('a.id_area', $ids_areas)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->campo_mp = $campo_mp;
                $campo_mo = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->whereIn('a.id_area', $ids_areas)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->campo_mo = $campo_mo;
                $campo_gip = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip) as cant'))
                    ->whereIn('o.id_area', $ids_areas)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->campo_gip = $campo_gip;
                $campo_ga = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->whereIn('o.id_area', $ids_areas)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->campo_ga = $campo_ga;
                $model->campo = $campo_mp + $campo_mo + $campo_gip + $campo_ga;

                /* ----------------------------- propagacion ------------------------- */
                $area = Area::All()->where('estado', 1)->where('nombre', 'PROPAGACION')->first();
                $propagacion_mp = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->propagacion_mp = $propagacion_mp;
                $propagacion_mo = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->propagacion_mo = $propagacion_mo;
                $propagacion_gip = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->propagacion_gip = $propagacion_gip;
                $propagacion_ga = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->propagacion_ga = $propagacion_ga;
                $model->propagacion = $propagacion_mp + $propagacion_mo + $propagacion_gip + $propagacion_ga;

                /* ----------------------------- cosecha ------------------------- */
                $area = Area::All()->where('estado', 1)->where('nombre', 'COSECHA')->first();
                $cosecha_mp = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->cosecha_mp = $cosecha_mp;
                $cosecha_mo = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->cosecha_mo = $cosecha_mo;
                $cosecha_gip = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->cosecha_gip = $cosecha_gip;
                $cosecha_ga = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->cosecha_ga = $cosecha_ga;
                $model->cosecha = $cosecha_mp + $cosecha_mo + $cosecha_gip + $cosecha_ga;

                /* ----------------------------- postcosecha ------------------------- */
                $area = Area::All()->where('estado', 1)->where('nombre', 'POSTCOSECHA')->first();
                $postcosecha_mp = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->postcosecha_mp = $postcosecha_mp;
                $postcosecha_mo = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->where('a.id_area', '=', $area->id_area)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->postcosecha_mo = $postcosecha_mo;
                $postcosecha_gip = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->postcosecha_gip = $postcosecha_gip;
                $postcosecha_ga = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->postcosecha_ga = $postcosecha_ga;
                $model->postcosecha = $postcosecha_mp + $postcosecha_mo + $postcosecha_gip + $postcosecha_ga;

                /* ----------------------------- servicios_generales ------------------------- */
                $ids_acts = [];
                $actividades = Actividad::where('estado', 1)->where('nombre', 'like', '%SERVICIOS GENERALES%')->get();
                $area = Area::All()->where('estado', 1)->where('nombre', 'SERVICIOS GENERALES')->first();
                foreach ($actividades as $a)
                    array_push($ids_acts, $a->id_actividad);
                $servicios_generales_mp = DB::table('costos_semana as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_producto as ac', 'ac.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('actividad as a', 'a.id_actividad', '=', 'ac.id_actividad')
                    ->whereIn('a.id_actividad', $ids_acts)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->servicios_generales_mp = $servicios_generales_mp;
                $servicios_generales_mo = DB::table('costos_semana_mano_obra as c')
                    ->select(DB::raw('sum(c.valor) as cant'))
                    ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                    ->whereIn('a.id_actividad', $ids_acts)
                    ->where('c.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->servicios_generales_mo = $servicios_generales_mo;
                $servicios_generales_gip = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.gip) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->servicios_generales_gip = $servicios_generales_gip;
                $servicios_generales_ga = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->servicios_generales_ga = $servicios_generales_ga;
                $model->servicios_generales = $servicios_generales_mp + $servicios_generales_mo + $servicios_generales_gip + $servicios_generales_ga;

                /* ----------------------------- administrativos ------------------------- */
                $area = Area::All()->where('estado', 1)->where('nombre', 'ADMINISTRATIVO')->first();
                $administrativos = DB::table('otros_gastos as o')
                    ->select(DB::raw('sum(o.ga) as cant'))
                    ->where('o.id_area', '=', $area->id_area)
                    ->where('o.codigo_semana', $semana->codigo)
                    ->get()[0]->cant;
                $model->administrativos = $administrativos;

                /* ----------------------------- regalias ------------------------- */
                $costos_semanal = ResumenCostosSemanal::All()->where('codigo_semana', $semana->codigo)->first();
                $model->regalias = $costos_semanal != '' ? $costos_semanal->regalias : 0;

                /* ----------------------------- tallos_cosechados ------------------------- */
                $tallos_cosechados = DB::table('resumen_semana_cosecha')
                    ->select(DB::raw('sum(tallos) as cant'))
                    ->where('codigo_semana', $semana->codigo)->get()[0]->cant;
                $model->tallos_cosechados = $tallos_cosechados != '' ? $tallos_cosechados : 0;

                $model->save();
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "resumen_total:update_semanal" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "resumen_total:update_semanal" <<<<< * >>>>>');
    }
}