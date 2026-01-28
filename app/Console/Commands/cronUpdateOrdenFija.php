<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\Pedido;

class cronUpdateOrdenFija extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:orden_fija {pedido=0} {detalle_pedido=0} {fecha=0} {eliminar_detalles=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para actualizar los pedidos posteriores tipo STANDING ORDER pertenecientes al pedido parametro';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "update:orden_fija" <<<<< ! >>>>>');

        $id_pedido = $this->argument('pedido');
        $detalle_pedido = $this->argument('detalle_pedido');
        $eliminar_detalles = $this->argument('eliminar_detalles');
        $fecha_par = $this->argument('fecha');
        $pedido = Pedido::find($id_pedido);

        $ped = Pedido::where('fecha_pedido', $fecha_par)
            ->where('tipo_pedido', 'STANDING ORDER')
            ->where('id_cliente', $pedido->id_cliente)
            ->where('orden_fija', $pedido->orden_fija)
            ->get()
            ->first();
        if ($ped != '') {
            $resumen_variedades = [];
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
            if ($eliminar_detalles) {
                DB::select('DELETE from detalle_pedido where id_pedido = ' . $ped->id_pedido);
            }

            $detOriginal = DetallePedido::find($detalle_pedido);
            $pedOriginal = $detOriginal->pedido;
            $det_expOriginales = $detOriginal->detalle_pedido_dato_exportacion;
            $cli_ped_espOriginal = $detOriginal->cliente_especificacion;
            $espOriginal = $cli_ped_espOriginal->especificacion;
            $esp_empOriginal = $espOriginal->especificacionesEmpaque[0];

            $enviosOriginal = $pedOriginal->envios;
            if (count($enviosOriginal) == 0) {
                dump('Creando ENVIO');
                $consignatario = ClienteConsignatario::All()
                    ->where('id_cliente', $pedOriginal->id_cliente)
                    ->first();
                $consignatario = $consignatario != '' ? $consignatario->id_consignatario : '';
                $envio = new Envio();
                $envio->fecha_envio = $pedOriginal->fecha_pedido;
                $envio->id_pedido = $pedOriginal->id_pedido;
                $envio->id_consignatario = $consignatario;
                $envio->save();
            } else
                $consignatario = $enviosOriginal[0]->id_consignatario;

            $envios = $ped->envios;
            if (count($envios) > 0) {
                $envio = $envios[0];
                $envio->id_consignatario = $consignatario;
                $envio->save();
            }

            $esp = new Especificacion();
            $esp->estado = 1;
            $esp->tipo = $espOriginal->tipo;
            $esp->creada = 'EJECUCION';
            $esp->save();
            $id = DB::table('especificacion')
                ->select(DB::raw('max(id_especificacion) as id'))
                ->get()[0]->id;
            $esp->id_especificacion = $id;

            $esp_emp = new EspecificacionEmpaque();
            $esp_emp->id_especificacion = $esp->id_especificacion;
            $esp_emp->id_empaque = $esp_empOriginal->id_empaque;
            $esp_emp->cantidad = 1;
            $esp_emp->save();
            $id = DB::table('especificacion_empaque')
                ->select(DB::raw('max(id_especificacion_empaque) as id'))
                ->get()[0]->id;
            $esp_emp->id_especificacion_empaque = $id;

            $cli_ped = new ClientePedidoEspecificacion();
            $cli_ped->id_especificacion = $esp->id_especificacion;
            $cli_ped->id_cliente = $cli_ped_espOriginal->id_cliente;
            $cli_ped->estado = 1;
            $cli_ped->save();
            $id = DB::table('cliente_pedido_especificacion')
                ->select(DB::raw('max(id_cliente_pedido_especificacion) as id'))
                ->get()[0]->id;
            $cli_ped->id_cliente_pedido_especificacion = $id;

            $precio = '';
            foreach ($esp_empOriginal->detalles as $pos => $det_espOriginal) {
                $det_esp = new DetalleEspecificacionEmpaque();
                $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                $det_esp->id_variedad = $det_espOriginal->id_variedad;
                $det_esp->id_clasificacion_ramo = $det_espOriginal->id_clasificacion_ramo;
                $det_esp->cantidad = $det_espOriginal->cantidad;
                $det_esp->id_empaque_p = $det_espOriginal->id_empaque_p;
                $det_esp->tallos_x_ramos = $det_espOriginal->tallos_x_ramos;
                $det_esp->longitud_ramo = $det_espOriginal->longitud_ramo;
                $det_esp->id_unidad_medida = $det_espOriginal->id_unidad_medida;
                $det_esp->save();
                $id = DB::table('detalle_especificacionempaque')
                    ->select(DB::raw('max(id_detalle_especificacionempaque) as id'))
                    ->get()[0]->id;
                $det_esp->id_detalle_especificacionempaque = $id;

                $p = getPrecioByDetEsp($detOriginal->precio, $det_espOriginal->id_detalle_especificacionempaque);
                if ($pos == 0) {
                    $precio = $p . ';' . $det_esp->id_detalle_especificacionempaque;
                } else {
                    $precio .= '|' . $p . ';' . $det_esp->id_detalle_especificacionempaque;
                }

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

            $det_ped = new DetallePedido();
            $det_ped->id_pedido = $ped->id_pedido;
            $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
            $det_ped->id_agencia_carga = $detOriginal->id_agencia_carga;
            $det_ped->cantidad = $detOriginal->cantidad;
            $det_ped->orden = $detOriginal->orden;
            $det_ped->precio = $precio;
            $det_ped->estado = 1;
            $det_ped->save();
            $id = DB::table('detalle_pedido')
                ->select(DB::raw('max(id_detalle_pedido) as id'))
                ->get()[0]->id;
            $det_ped->id_detalle_pedido = $id;

            foreach ($det_expOriginales as $det_expOriginal) {
                $det_ped_exp = new DetallePedidoDatoExportacion();
                $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                $det_ped_exp->id_dato_exportacion = $det_expOriginal->id_dato_exportacion;
                $det_ped_exp->valor = $det_expOriginal->valor;
                $det_ped_exp->save();
            }

            foreach ($resumen_variedades as $r) {
                jobCosechaEstimada::dispatch($r['variedad'], $r['longitud'], opDiasFecha('-', 1, $r['fecha']))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "update:orden_fija" <<<<< * >>>>>');
    }
}
