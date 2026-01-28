<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\Proyecto;
use yura\Modelos\RenovarOrdenFija;

class cronRenovarOrdenes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renovar:ordenes {orden=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para renovar las ordenes fijas';

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
        ini_set('max_execution_time', 36000);
        set_time_limit(3600);
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "renovar:ordenes" <<<<< ! >>>>>');

        $orden = $this->argument('orden');
        $renovaciones = RenovarOrdenFija::where('renovacion', '>', 0);
        if ($orden != 0)
            $renovaciones = $renovaciones->where('orden_fija', $orden);
        $renovaciones = $renovaciones->get();
        foreach ($renovaciones as $r) {
            $pedidos = Proyecto::where('orden_fija', $r->orden_fija)
                ->where('fecha', '>=', hoy())
                ->orderBy('fecha', 'desc')
                ->get();
            $faltantes = 10 - count($pedidos);
            if ($faltantes > 0) {
                $proy_original = $pedidos->first();
                if ($proy_original != '')
                    for ($i = 1; $i <= $faltantes; $i++) {
                        $fecha = opDiasFecha('+', ($r->renovacion * $i), $proy_original->fecha);
                        dump('*****************************Creando Pedido de la orden: ' . $r->orden_fija . ' para la fecha: ' . $fecha . '*********************************');

                        // NUEVO PROYECTO
                        $proyecto = new Proyecto();
                        $proyecto->id_cliente = $proy_original->id_cliente;
                        $proyecto->orden_fija = $proy_original->orden_fija;
                        $proyecto->fecha = $fecha;
                        $proyecto->tipo = $proy_original->tipo;
                        $proyecto->id_consignatario = $proy_original->id_consignatario;
                        $proyecto->id_agencia_carga = $proy_original->id_agencia_carga;
                        $proyecto->save();
                        $proyecto->id_proyecto = DB::table('proyecto')
                            ->select(DB::raw('max(id_proyecto) as id'))
                            ->get()[0]->id;

                        foreach ($proy_original->cajas as $caja_original) {
                            // NUEVA CAJA PROYECTO
                            $caja = new CajaProyecto();
                            $caja->id_proyecto = $proyecto->id_proyecto;
                            $caja->cantidad = $caja_original->cantidad;
                            $caja->id_empaque = $caja_original->id_empaque;
                            $caja->save();
                            $caja->id_caja_proyecto = DB::table('caja_proyecto')
                                ->select(DB::raw('max(id_caja_proyecto) as id'))
                                ->get()[0]->id;
                            foreach ($caja_original->detalles as $det_original) {
                                // NUEVO DETALLE CAJA PROYECTO
                                $detalle = new DetalleCajaProyecto();
                                $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                                $detalle->id_variedad = $det_original->id_variedad;
                                $detalle->id_empaque = $det_original->id_empaque;
                                $detalle->ramos_x_caja = $det_original->ramos_x_caja;
                                $detalle->tallos_x_ramo = $det_original->tallos_x_ramo;
                                $detalle->precio = $det_original->precio;
                                $detalle->longitud_ramo = $det_original->longitud_ramo;
                                $detalle->save();
                            }
                            foreach ($caja_original->marcaciones as $marcacion) {
                                // NUEVA CAJA PROYECTO MARCACION
                                if ($marcacion->valor != '') {
                                    $caja_marcacion = new CajaProyectoMarcacion();
                                    $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                                    $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                                    $caja_marcacion->valor = $marcacion->valor;
                                    $caja_marcacion->save();
                                }
                            }
                        }
                    }
            }
        }


        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "renovar:ordenes" <<<<< * >>>>>');
    }
}
