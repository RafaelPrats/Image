<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Log;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\ResumenCosechaEstimada;

class cronCosechaEstimada extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cosecha:estimada {variedad=0} {longitud=0} {fecha=0}';

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
        $variedad_par = $this->argument('variedad');
        $longitud_par = $this->argument('longitud');
        $fecha_par = $this->argument('fecha');

        Artisan::call('update:cosecha_estimada', [
            'variedad' => $variedad_par,
            'longitud' => $longitud_par,
            'fecha' => $fecha_par,
        ]);
        /*$ini = date('Y-m-d H:i:s');
        //dump('<<<<< ! >>>>> Ejecutando comando "cosecha:estimada" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "cosecha:estimada" <<<<< ! >>>>>');

        $variedades = DB::table('variedad')
            ->select('*')
            ->where('assorted', 0)
            ->where('estado', 1);
        if ($variedad_par != 0)
            $variedades = $variedades->where('id_variedad', $variedad_par);
        $variedades = $variedades->get();

        foreach ($variedades as $pos_v => $var) {
            $longitudes = DB::table('proy_longitudes')
                ->select('*')
                ->where('id_planta', $var->id_planta);
            if ($longitud_par != 0)
                $longitudes = $longitudes->where('nombre', $longitud_par);
            $longitudes = $longitudes->orderBy('orden')
                ->get();
            foreach ($longitudes as $pos_l => $long) {
                dump('var: ', $pos_v . '/' . count($variedades) . '; long: ' . $pos_l . '/' . count($longitudes));
                $query = DB::table('pedido as p')
                    ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                    ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                    ->join('detalle_cliente as cli', 'cli.id_cliente', '=', 'cpe.id_cliente')
                    ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        'dp.id_detalle_pedido',
                        'dp.cantidad as piezas',
                        'dee.id_detalle_especificacionempaque',
                        'dee.tallos_x_ramos',
                        DB::raw('sum(dee.cantidad * dee.tallos_x_ramos * dp.cantidad) as tallos'),
                        DB::raw('sum(dee.cantidad * dp.cantidad) as ramos')
                    )->distinct()
                    ->where('p.estado', 1)
                    ->where('cli.estado', 1)
                    ->where('p.fecha_pedido', opDiasFecha('+', 1, $fecha))
                    ->where('dee.id_variedad', $var->id_variedad)
                    ->where('dee.longitud_ramo', $long->nombre)
                    ->whereNotNull('dee.tallos_x_ramos')
                    ->groupBy(
                        'dp.id_detalle_pedido',
                        'dp.cantidad',
                        'dee.id_detalle_especificacionempaque',
                        'dee.tallos_x_ramos',
                    )
                    ->get();
                $tallos = 0;
                $ramos = 0;
                foreach ($query as $q) {
                    $getRamosXCajaModificado = getRamosXCajaModificado($q->id_detalle_pedido, $q->id_detalle_especificacionempaque);
                    $ramos += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->piezas) : $q->ramos;
                    $tallos += isset($getRamosXCajaModificado) ? ($getRamosXCajaModificado->cantidad * $q->tallos_x_ramos * $q->piezas) : $q->tallos;
                }

                $cant_mixtos = DB::table('distribucion_mixtos as d')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'd.id_detalle_especificacionempaque')
                    ->select(DB::raw('sum(d.ramos * d.piezas * dee.tallos_x_ramos) as cantidad'))
                    ->where('d.id_planta', $var->id_planta)
                    ->where('d.siglas', $var->siglas)
                    ->where('d.fecha', $fecha)
                    ->where('dee.longitud_ramo', $long->nombre)
                    ->get()[0]->cantidad;
                $actual = $tallos + $cant_mixtos;

                $modificaciones_solidos = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        'pedido_modificacion.*',
                        'dee.cantidad as dee_cantidad',
                        'dee.tallos_x_ramos as dee_tallos_x_ramos'
                    )->distinct()
                    ->where('v.id_planta', $var->id_planta)
                    ->where('v.siglas', $var->siglas)
                    ->whereNotNull('pedido_modificacion.cantidad')
                    ->where('pedido_modificacion.fecha_anterior_pedido', opDiasFecha('+', 1, $fecha))
                    ->where('pedido_modificacion.usar', 1)
                    ->where('dee.longitud_ramo', $long->nombre)
                    ->get();

                $modificaciones_mixtas = PedidoModificacion::join('detalle_especificacionempaque as dee', 'dee.id_detalle_especificacionempaque', '=', 'pedido_modificacion.id_detalle_especificacionempaque')
                    ->select('pedido_modificacion.*')->distinct()
                    ->where('pedido_modificacion.id_planta', $var->id_planta)
                    ->where('pedido_modificacion.siglas', $var->siglas)
                    ->whereNull('pedido_modificacion.cantidad')
                    ->where('pedido_modificacion.fecha_anterior_pedido', opDiasFecha('+', 1, $fecha))
                    ->where('pedido_modificacion.usar', 1)
                    ->where('dee.longitud_ramo', $long->nombre)
                    ->get();

                $val_mod = 0;
                foreach ($modificaciones_solidos as $mod) {
                    $ramos_x_caja = $mod->ramos_x_caja != '' ? $mod->ramos_x_caja : $mod->dee_cantidad;
                    if ($mod->operador == '+') {
                        $val_mod += $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                        $actual -= $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                    } else {
                        $val_mod -= $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                        $actual += $mod->cantidad * $ramos_x_caja * $mod->dee_tallos_x_ramos;
                    }
                }
                foreach ($modificaciones_mixtas as $mod) {
                    if ($mod->operador == '+') {
                        $val_mod += $mod->tallos;
                        $actual -= $mod->tallos;
                    } else {
                        $val_mod -= $mod->tallos;
                        $actual += $mod->tallos;
                    }
                }

                $tallos_bqt = DB::table('distribucion_recetas as dr')
                    ->join('detalle_pedido as dp', 'dp.id_detalle_pedido', '=', 'dr.id_detalle_pedido')
                    ->join('pedido as p', 'p.id_pedido', '=', 'dp.id_pedido')
                    ->select(DB::raw('sum(dr.tallos) as cantidad'))
                    ->where('dr.id_planta', $var->id_planta)
                    ->where('dr.siglas', $var->siglas)
                    ->where('dr.longitud_ramo', $long->nombre)
                    ->where('p.fecha_pedido', opDiasFecha('+', 1, $fecha))
                    ->get()[0]->cantidad;

                $model = ResumenCosechaEstimada::where('id_variedad', $var->id_variedad)
                    ->where('longitud', $long->nombre)
                    ->where('fecha', $fecha)
                    ->get()
                    ->first();

                if ($model == '') {
                    $model = new ResumenCosechaEstimada();
                    $model->id_variedad = $var->id_variedad;
                    $model->longitud = $long->nombre;
                    $model->fecha = $fecha;
                    $model->actual = $actual != '' ? $actual : 0;
                    $model->solidos = $tallos != '' ? $tallos : 0;
                    $model->mixtos = $cant_mixtos != '' ? $cant_mixtos : 0;
                    $model->cambios = $val_mod != '' ? $val_mod : 0;
                    $model->tallos_bqt = $tallos_bqt != '' ? $tallos_bqt : 0;
                    $model->save();
                } else {
                    $model->actual = $actual != '' ? $actual : 0;
                    $model->solidos = $tallos != '' ? $tallos : 0;
                    $model->mixtos = $cant_mixtos != '' ? $cant_mixtos : 0;
                    $model->cambios = $val_mod != '' ? $val_mod : 0;
                    $model->tallos_bqt = $tallos_bqt != '' ? $tallos_bqt : 0;
                    $model->save();
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        //dump('<*> DURACION: ' . $time_duration . '  <*>');
        //dump('<<<<< * >>>>> Fin satisfactorio del comando "cosecha:estimada" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "cosecha:estimada" <<<<< * >>>>>');*/
    }
}
