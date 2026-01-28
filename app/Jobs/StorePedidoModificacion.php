<?php

namespace yura\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoModificacion;

class StorePedidoModificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;//, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $newIdPedido;
    public $oldIdPedido;
    public $tries= 5;

    public function __construct($newIdPedido,$oldIdPedido)
    {
        $this->newIdPedido = $newIdPedido;
        $this->oldIdPedido = $oldIdPedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $oldPedido = Pedido::find($this->oldIdPedido);
        $newPedido = Pedido::find($this->newIdPedido);

        if(now()->toDateString() >= Carbon::parse($oldPedido->fecha_pedido)->subDay()->toDateString()){

            foreach($oldPedido->detalles as $x => $det_ped){

                if(!isset($newPedido->detalles[$x])){

                    foreach($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp){

                        foreach($esp_emp->detalles as $det_esp_emp){

                            $pedidoModificacion = new PedidoModificacion;
                            $pedidoModificacion->fecha_registro= now()->toDateTimeString();
                            $pedidoModificacion->fecha_registro = now()->toDateString();
                            $pedidoModificacion->id_cliente = $newPedido->id_cliente;
                            $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                            $pedidoModificacion->fecha_nuevo_pedido = $newPedido->fecha_pedido;
                            $pedidoModificacion->fecha_anterior_pedido = $oldPedido->fecha_pedido;
                            $pedidoModificacion->cantidad = $det_ped->cantidad;
                            $pedidoModificacion->operador = '-';
                            $pedidoModificacion->save();

                        }

                    }

                }else{

                    $newDetPed = $newPedido->detalles[$x];
                    
                    if($oldPedido->fecha_pedido != $newPedido->fecha_pedido){

                        foreach($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp){

                            foreach($esp_emp->detalles as $det_esp_emp){

                                $pedidoModificacion = new PedidoModificacion;
                                $pedidoModificacion->fecha_registro = now()->toDateTimeString();
                                $pedidoModificacion->fecha_registro = now()->toDateString();
                                $pedidoModificacion->id_cliente = $newPedido->id_cliente;
                                $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                $pedidoModificacion->fecha_nuevo_pedido = $newPedido->fecha_pedido;
                                $pedidoModificacion->fecha_anterior_pedido = $oldPedido->fecha_pedido;
                                $pedidoModificacion->cantidad = $det_ped->cantidad;
                                $pedidoModificacion->operador = '-';
                                $pedidoModificacion->save();

                            }

                        }


                    }else if($newDetPed->cantidad != $det_ped->cantidad){

                        foreach($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp){

                            foreach($esp_emp->detalles as $det_esp_emp){

                                $pedidoModificacion = new PedidoModificacion;
                                $pedidoModificacion->fecha_registro= now()->toDateTimeString();
                                $pedidoModificacion->fecha_registro = now()->toDateString();
                                $pedidoModificacion->id_cliente = $newPedido->id_cliente;
                                $pedidoModificacion->id_detalle_especificacionempaque = $det_esp_emp->id_detalle_especificacionempaque;
                                $pedidoModificacion->fecha_nuevo_pedido = $newPedido->fecha_pedido;
                                $pedidoModificacion->fecha_anterior_pedido = $oldPedido->fecha_pedido;
                                $pedidoModificacion->cantidad = abs($det_ped->cantidad - $newDetPed->cantidad);
                                $pedidoModificacion->operador = $det_ped->cantidad > $newDetPed->cantidad ? '-' : '+';
                                $pedidoModificacion->save();

                            }

                        }

                    }

                }

            }

        }

        Pedido::destroy($this->oldIdPedido);

    }
}
