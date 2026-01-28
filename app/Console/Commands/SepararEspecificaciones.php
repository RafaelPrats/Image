<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;

class SepararEspecificaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'separar:especificaciones';

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
        DB::beginTransaction();

        try {

            $clientePedidoEspecificacion = ClientePedidoEspecificacion::where('cliente_pedido_especificacion.estado',true)
            ->join('detalle_cliente as dc',function($j){
                $j->on('cliente_pedido_especificacion.id_cliente','dc.id_cliente')->where('dc.estado',true);
            })->join('especificacion as esp',function($j){
                $j->on('cliente_pedido_especificacion.id_especificacion','esp.id_especificacion')->where('esp.estado',true);
            })->get();

            $clienteEspecificacion = [];
            $clienteEspecificacionMasivo = [];

            foreach($clientePedidoEspecificacion as $cpe)
                $clienteEspecificacion[$cpe->id_cliente][] = $cpe->id_especificacion;


            foreach($clienteEspecificacion as $idCliente => $especificaciones)
                if(count($especificaciones) > 1)
                    $clienteEspecificacionMasivo[$idCliente] = $especificaciones;

            $this->warn("\n\nSeparando especificaciones...\n");
            $bar = $this->output->createProgressBar(count($clienteEspecificacionMasivo));

            foreach($clienteEspecificacionMasivo as $idCliente => $especificaciones){

                foreach($especificaciones as $especificacion){

                    ClientePedidoEspecificacion::where([
                        ['id_cliente', $idCliente],
                        ['id_especificacion',$especificacion]
                    ])->delete();

                    $especificacion = Especificacion::find($especificacion);

                    $newEspecificacion = $especificacion->replicate();
                    $objEsp = Especificacion::orderBy('id_especificacion','desc')->first();
                    $newIdEspecificacion = isset($objEsp) ? $objEsp->id_especificacion+1 : 1;

                    $newEspecificacion->id_especificacion = $newIdEspecificacion;
                    $newEspecificacion->fecha_registro = now()->toDateTimeString();
                    $newEspecificacion->save();

                    $idsEspecificacionesEmpaque = EspecificacionEmpaque::whereIn('id_especificacion_empaque',$especificacion->especificacionesEmpaque->pluck('id_especificacion_empaque')->toArray())->get();

                    foreach($idsEspecificacionesEmpaque as $espEmp){

                        $especificacionesEmpaque = EspecificacionEmpaque::find($espEmp->id_especificacion_empaque);

                        $newEspecificacionesEmpaque = $especificacionesEmpaque->replicate();
                        $objEspEspemp = EspecificacionEmpaque::orderBy('id_especificacion_empaque','desc')->first();
                        $newIdEspecificacionEmpaque= isset($objEspEspemp) ? $objEspEspemp->id_especificacion_empaque+1 : 1;

                        $newEspecificacionesEmpaque->id_especificacion_empaque = $newIdEspecificacionEmpaque;
                        $newEspecificacionesEmpaque->id_especificacion = $newIdEspecificacion;
                        $newEspecificacionesEmpaque->fecha_registro = now()->toDateTimeString();
                        $newEspecificacionesEmpaque->save();

                        $idsDetalleEspecificacionEmpaque= DetalleEspecificacionEmpaque::whereIn('id_especificacion_empaque',$especificacionesEmpaque->detalles->pluck('id_detalle_especificacionempaque')->toArray())->get();

                        foreach($idsDetalleEspecificacionEmpaque as $detEspEmp){

                            $detalleEspecificacionEmpaque = DetalleEspecificacionEmpaque::find($detEspEmp->id_detalle_especificacionempaque);
                            $newDetalleEspecificacionEmpaque = $detalleEspecificacionEmpaque->replicate();
                            $objDetEspEspEmp = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque','desc')->first();

                            $newDetalleEspecificacionEmpaque->id_detalle_especificacionempaque = isset($objDetEspEspEmp) ? $objDetEspEspEmp->id_detalle_especificacionempaque+1 : 1;
                            $newDetalleEspecificacionEmpaque->id_especificacion_empaque= $newIdEspecificacionEmpaque;
                            $newDetalleEspecificacionEmpaque->fecha_registro = now()->toDateTimeString();
                            $newDetalleEspecificacionEmpaque->save();

                        }

                    }

                    ClientePedidoEspecificacion::create([
                        'id_cliente' => $idCliente,
                        'id_especificacion' => $newIdEspecificacion,
                        'estado' => true
                    ]);

                }

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->info("\n\n✔ Especificaciones separadas ✔\n");

        } catch (\Exception $e) {

            DB::rollBack();
            $this->error($e->getMessage().' '.$e->getLine().' '.$e->getFile()."\n\n ".$e->getTraceAsString());

        }

    }
}
