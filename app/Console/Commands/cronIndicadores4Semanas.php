<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Indicadores4Semanas;
use yura\Modelos\Pedido;
use yura\Modelos\Semana;

class cronIndicadores4Semanas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:indicador_4_semanas {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        dump('<<<<< ! >>>>> Ejecutando comando "cron:indicador_4_semanas" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cron:indicador_4_semanas" <<<<< ! >>>>>');

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

            $array_semanas = DB::table('semana')
                ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                ->where('codigo', '>=', $semana_desde->codigo)
                ->where('codigo', '<=', $semana_hasta->codigo)
                ->where('estado', 1)
                ->orderBy('codigo')
                ->get();

            foreach ($array_semanas as $pos_sem => $sem) {
                dump('sem: ' . $sem->codigo);
                $desde = getSemanaByDate(opDiasFecha('-', 21, $sem->fecha_inicial));
                $model = Indicadores4Semanas::All()
                    ->where('semana', $sem->codigo)
                    ->first();
                if ($model == '') {
                    $model = new Indicadores4Semanas();
                    $model->semana = $sem->codigo;
                }
                $resumen_semanal_total = DB::table('resumen_semanal_total')
                    ->select(DB::raw('sum(campo) as campo'),
                        DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                        DB::raw('sum(valor) as valor'),
                        DB::raw('sum(cosecha) as cosecha'),
                        DB::raw('sum(postcosecha) as postcosecha'),
                        DB::raw('sum(propagacion) as propagacion'),
                        DB::raw('sum(servicios_generales) as servicios_generales'),
                        DB::raw('sum(administrativos) as administrativos'),
                        DB::raw('sum(regalias) as regalias'))
                    ->where('codigo_semana', '>=', $desde->codigo)
                    ->where('codigo_semana', '<=', $sem->codigo)
                    ->get()[0];
                $resumen_cosecha = DB::table('resumen_semana_cosecha')
                    ->select(DB::raw('sum(tallos_clasificados) as tallos_clasificados'))
                    ->where('codigo_semana', '>=', $desde->codigo)
                    ->where('codigo_semana', '<=', $sem->codigo)
                    ->get()[0];
                /* ---------------- Costo x planta ------------------ */
                $query = DB::table('resumen_propagacion')
                    ->select('costo_x_planta', 'semana')->distinct()
                    ->where('semana', '>=', $desde->codigo)
                    ->where('semana', '<=', $sem->codigo)
                    ->where('costo_x_planta', '>', 0)
                    ->orderBy('semana')
                    ->get();
                $costo_x_planta = 0;
                foreach ($query as $item)
                    $costo_x_planta += $item->costo_x_planta;
                $model->costo_x_planta = $costo_x_planta / 4;
                /* --------------- Campo/ha/Semana ------------------ */
                $areas = DB::table('resumen_area_semanal')
                    ->select(DB::raw('sum(area) as area'))
                    ->where('codigo_semana', '>=', $desde->codigo)
                    ->where('codigo_semana', '<=', $sem->codigo)
                    ->get()[0]->area;
                $campo_ha_semana = $areas > 0 ? ($resumen_semanal_total->campo / $areas) * 10000 : 0;
                $model->campo_ha_semana = $campo_ha_semana;
                /* -------------- Cosecha x tallo ------------------- */
                $cosecha_x_tallo = $resumen_semanal_total->tallos_cosechados > 0 ? ($resumen_semanal_total->cosecha / $resumen_semanal_total->tallos_cosechados) : 0;
                $model->cosecha_x_tallo = $cosecha_x_tallo;
                /* -------------- Postcosecha x tallo --------------- */
                $postcosecha_x_tallo = $resumen_semanal_total->tallos_cosechados > 0 ? ($resumen_semanal_total->postcosecha / $resumen_semanal_total->tallos_cosechados) : 0;
                $model->postcosecha_x_tallo = $postcosecha_x_tallo;
                /* -------------- Costo Total x tallo --------------- */
                $costo_total_x_tallo = $resumen_cosecha->tallos_clasificados > 0 ? (($resumen_semanal_total->propagacion + $resumen_semanal_total->campo + $resumen_semanal_total->cosecha + $resumen_semanal_total->postcosecha + $resumen_semanal_total->servicios_generales + $resumen_semanal_total->administrativos + $resumen_semanal_total->regalias) / $resumen_cosecha->tallos_clasificados) : 0;
                $model->costo_total_x_tallo = $costo_total_x_tallo;
                /* -------------- Precio x tallo --------------- */
                $precio_x_tallo = $resumen_cosecha->tallos_clasificados > 0 ? ($resumen_semanal_total->valor / $resumen_cosecha->tallos_clasificados) : 0;
                $model->precio_x_tallo = $precio_x_tallo;
                /* -------------- Desecho de Cosecha --------------- */
                $desecho_cosecha = 100 - porcentaje($resumen_cosecha->tallos_clasificados, $resumen_semanal_total->tallos_cosechados, 1);
                $model->desecho_cosecha = $desecho_cosecha;
                /* -------------- Venta/m2 --------------- */
                $venta_m2 = $areas > 0 ? $resumen_semanal_total->valor / $areas : 0;
                $model->venta_m2 = $venta_m2;
                /* -------------- Costos/m2 --------------- */
                $costos_m2 = $areas > 0 ? ($resumen_semanal_total->propagacion + $resumen_semanal_total->campo + $resumen_semanal_total->cosecha + $resumen_semanal_total->postcosecha + $resumen_semanal_total->servicios_generales + $resumen_semanal_total->administrativos + $resumen_semanal_total->regalias) / $areas : 0;
                $model->costos_m2 = $costos_m2;
                /* -------------- EBITDA/m2 --------------- */
                $model->ebitda_m2 = $model->venta_m2 - $model->costos_m2;
                /* -------------- Precio x Ramo --------------- */
                $pedidos_semanal = Pedido::where('estado', 1)
                    ->where('fecha_pedido', '>=', $desde->fecha_inicial)
                    ->where('fecha_pedido', '<=', $sem->fecha_final)
                    ->get();
                $valor = 0;
                $ramos_estandar = 0;
                $cajas = 0;
                foreach ($pedidos_semanal as $pos => $p) {
                    if (!getFacturaAnulada($p->id_pedido)) {
                        $valor += $p->getPrecioByPedido();
                        $ramos_estandar += $p->getRamosEstandar();
                        $cajas += $p->getCajas();
                    }
                }
                $precio_x_ramo = $ramos_estandar > 0 ? round($valor / $ramos_estandar, 2) : 0;
                $model->precio_x_ramo = $precio_x_ramo;
                /* -------------- Productividad --------------- */
                $data_ciclos = getCiclosCerradosByRango($desde->codigo, $sem->codigo, 'T');
                $ciclo = $data_ciclos['ciclo'];
                $area_cerrada = $data_ciclos['area_cerrada'];
                $tallos_ciclo = $data_ciclos['tallos_cosechados'];
                $data_cosecha = getCosechaByRango($desde->codigo, $sem->codigo, 'T');
                $calibre_ciclo = $data_cosecha['calibre'];
                $ramos_ciclo = $calibre_ciclo > 0 ? round($tallos_ciclo / $calibre_ciclo, 2) : 0;
                $ciclo_ano = $area_cerrada > 0 ? round(365 / $ciclo, 2) : 0;
                $productividad_mensual = [
                    'ciclo_ano' => $ciclo_ano,
                    'ciclo' => $ciclo,
                    'area_cerrada' => $area_cerrada,
                    'tallos_m2' => $area_cerrada > 0 ? round($tallos_ciclo / $area_cerrada, 2) : 0,
                    'ramos_m2' => $area_cerrada > 0 ? round($ramos_ciclo / $area_cerrada, 2) : 0,
                    'ramos_m2_anno' => $area_cerrada > 0 ? round($ciclo_ano * round($ramos_ciclo / $area_cerrada, 2), 2) : 0,
                ];
                $model->productividad = $productividad_mensual['ramos_m2_anno'];
                /* -------------- Calibre --------------- */
                $model->calibre = $calibre_ciclo;
                /* -------------- Tallos m2 --------------- */
                $model->tallos_m2 = $productividad_mensual['tallos_m2'];
                /* -------------- Ciclo --------------- */
                $model->ciclo = $ciclo;
                /* -------------- Area --------------- */
                $model->area = $areas;
                /* -------------- Venta --------------- */
                $model->venta = $resumen_semanal_total->valor;
                /* -------------- Tallos cosechados --------------- */
                $model->tallos_cosechados = $resumen_semanal_total->tallos_cosechados;
                /* -------------- Tallos clasificados --------------- */
                $model->tallos_clasificados = $resumen_cosecha->tallos_clasificados;
                /* -------------- Cajas exportadas --------------- */
                $model->cajas_exportadas = $cajas;
                
                $model->save();
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "cron:indicador_4_semanas" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cron:indicador_4_semanas" <<<<< * >>>>>');
    }
}