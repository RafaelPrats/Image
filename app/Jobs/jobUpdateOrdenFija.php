<?php

namespace yura\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoProceso;

class jobUpdateOrdenFija implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $pedido;
    protected $detalle_pedido;
    protected $fecha;
    protected $eliminar_detalles;
    protected $pos_progreso;
    protected $total_progreso;
    protected $usuario;

    public function __construct($pedido, $detalle_pedido, $fecha, $eliminar_detalles, $pos_progreso, $total_progreso, $usuario)
    {
        $this->pedido = $pedido;
        $this->detalle_pedido = $detalle_pedido;
        $this->fecha = $fecha;
        $this->eliminar_detalles = $eliminar_detalles;
        $this->pos_progreso = $pos_progreso;
        $this->total_progreso = $total_progreso;
        $this->usuario = $usuario;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('max_execution_time', 36000);
        set_time_limit(3600);
        /* REGISTRO en TABLA PEDIDO_PROCESO */
        $proceso = PedidoProceso::where('id_pedido', $this->pedido)
            ->where('estado', 'P')  // Pendiente
            ->where('tipo_proceso', 'U')    // Update Orden Fija
            ->get()
            ->first();
        if ($proceso == '') {
            $pedidoOriginal = Pedido::find($this->pedido);
            $proceso = new PedidoProceso();
            $proceso->id_pedido = $this->pedido;
            $proceso->estado = 'P';
            $proceso->descripcion = 'ACTUALIZACION DE TODA LA ORDEN FIJA #' . $pedidoOriginal->orden_fija . ', del CLIENTE: ' . $pedidoOriginal->cliente->detalle()->nombre . ', fecha: ' . $pedidoOriginal->fecha_pedido;
            $proceso->id_usuario = $this->usuario;
            $proceso->tipo_proceso = 'U';
            $proceso->cant_procesado = 0;
            $proceso->total_procesar = $this->total_progreso;
            $proceso->progreso = porcentaje($proceso->cant_procesado, $this->total_progreso, 1);
            $proceso->save();
            $id = DB::table('pedido_proceso')
                ->select(DB::raw('max(id_pedido_proceso) as id'))
                ->get()[0]->id;
            $proceso->id_pedido_proceso = $id;
            $cant_procesado = 0;
        }
        $cant_procesado = $proceso->cant_procesado;

        /* LOGICA DEL JOB */
        Artisan::call('update:orden_fija', [
            'pedido' => $this->pedido,
            'detalle_pedido' => $this->detalle_pedido,
            'fecha' => $this->fecha,
            'eliminar_detalles' => $this->eliminar_detalles,
        ]);

        /* REGISTRO en TABLA PEDIDO_PROCESO */
        $cant_procesado++;
        if ($cant_procesado == $this->total_progreso) { // progreso completado
            $proceso->estado = 'C';
            $proceso->progreso = 100;
            $proceso->cant_procesado = $cant_procesado;
            $proceso->last_update = date('Y-m-d H:i:s');
            $proceso->save();
        } else {    // actualizar progreso
            $proceso->progreso = porcentaje($cant_procesado, $this->total_progreso, 1);
            $proceso->cant_procesado = $cant_procesado;
            $proceso->last_update = date('Y-m-d H:i:s');
            $proceso->save();
        }
    }
}
