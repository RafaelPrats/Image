<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\DistribucionRecetas;
use yura\Modelos\Pedido;

class cronDistribucionRecetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distribucion:recetas {pedido=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para generar las distribucions de las recetas de un pedido';

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
        //dump('<<<<< ! >>>>> Ejecutando comando "distribucion:recetas" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "distribucion:recetas" <<<<< ! >>>>>');
        $pedido = Pedido::find($this->argument('pedido'));
        //dd($pedido->detalles);
        foreach ($pedido->detalles as $detalle) {
            foreach ($detalle->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                foreach ($esp_emp->detalles as $det_esp_emp) {
                    jobCosechaEstimada::dispatch($det_esp_emp->id_variedad, $det_esp_emp->longitud_ramo, opDiasFecha('-', 1, $pedido->fecha_pedido))
                        ->onQueue('cosecha_estimada')
                        ->onConnection('database');
                    $getRamosXCajaModificado = getRamosXCajaModificado($detalle->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                    $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp_emp->cantidad;

                    $delete = DistribucionRecetas::All()
                        ->where('id_cliente', $pedido->id_cliente)
                        ->where('id_detalle_pedido', $detalle->id_detalle_pedido)
                        ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque);
                    foreach ($delete as $del)
                        $del->delete();

                    $variedad = $det_esp_emp->variedad;
                    if ($variedad->receta) {
                        foreach ($variedad->detalles_receta as $item) {
                            $model = new DistribucionRecetas();
                            $model->id_planta = $item->item->id_planta;
                            $model->siglas = $item->item->siglas;
                            $model->fecha = $pedido->fecha_pedido;
                            $model->tallos = $item->unidades * $ramos_x_caja * $detalle->cantidad;
                            $model->id_cliente = $pedido->id_cliente;
                            $model->id_detalle_pedido = $detalle->id_detalle_pedido;
                            $model->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $model->ramos_x_caja = $ramos_x_caja;
                            $model->longitud_ramo = $item->longitud;
                            $model->save();

                            jobCosechaEstimada::dispatch($item->item->id_variedad, $det_esp_emp->longitud_ramo, opDiasFecha('-', 1, $pedido->fecha_pedido))
                                ->onQueue('cosecha_estimada')
                                ->onConnection('database');
                        }
                    }
                }
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        //dump('<*> DURACION: ' . $time_duration . '  <*>');
        //dump('<<<<< * >>>>> Fin satisfactorio del comando "distribucion:recetas" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "distribucion:recetas" <<<<< * >>>>>');
    }
}
