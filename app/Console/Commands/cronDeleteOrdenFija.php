<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Log;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\Pedido;

class cronDeleteOrdenFija extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:orden_fija {pedido=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para eliminar los pedidos posteriores tipo STANDING ORDER pertenecientes al pedido parametro';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:orden_fija" <<<<< ! >>>>>');

        $id_pedido = $this->argument('pedido');
        $pedido = Pedido::find($id_pedido);

        $pedidos_futuros = Pedido::where('fecha_pedido', '>=', $pedido->fecha_pedido)
            ->where('tipo_pedido', 'STANDING ORDER')
            ->where('orden_fija', $pedido->orden_fija)
            ->where('id_cliente', $pedido->id_cliente)
            ->get();
        $resumen_variedades = [];
        foreach ($pedidos_futuros as $ped) {
            foreach ($ped->detalles as $det_ped)
                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp)
                    foreach ($esp_emp->detalles as $det_esp) {
                        if (!in_array([
                            'variedad' => $det_esp->id_variedad,
                            'longitud' => $det_esp->longitud_ramo,
                            'fecha' => $ped->fecha_pedido
                        ], $resumen_variedades)) {
                            $resumen_variedades[] = [
                                'variedad' => $det_esp->id_variedad,
                                'longitud' => $det_esp->longitud_ramo,
                                'fecha' => $ped->fecha_pedido
                            ];
                        }
                    }
            bitacora('pedido', $ped->id_pedido, 'E', 'ELIMINAR PEDIDO DE LA ORDEN FIJA #' . $pedido->orden_fija . ' con fecha ' . $ped->fecha_pedido . ' DESDE LA OPCION CANCELAR_TODA_ORDEN_FIJA');
            $ped->delete();
        }

        foreach ($resumen_variedades as $r) {
            jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $r['fecha']))
                ->onQueue('cosecha_estimada')
                ->onConnection('database');
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:orden_fija" <<<<< * >>>>>');
    }
}
