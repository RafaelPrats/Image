<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Cliente;
use yura\Modelos\Monitoreo;
use yura\Modelos\Notificacion;
use yura\Modelos\PedidoModificacion;
use yura\Modelos\UserNotification;

class NotificacionesSistema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:sistema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear las notificaciones de tipo Sistema';

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
        Log::info('<<<<< ! >>>>> Ejecutando comando "notificaciones:sistema" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "notificaciones:sistema" <<<<< ! >>>>>');

        $notificaciones = Notificacion::All()
            ->where('estado', 1)
            ->where('tipo', 'S')
            ->where('automatica', 1);
        foreach ($notificaciones as $not) {
            $funcion = $not->nombre;
            $this->$funcion($not);
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "notificaciones:sistema" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "notificaciones:sistema" <<<<< * >>>>>');
    }

    public function pedido_modificacion($not)
    {
        $query = PedidoModificacion::whereIn('fecha_anterior_pedido', [hoy(), opDiasFecha('+', 1, hoy())])
            ->orderBy('fecha_anterior_pedido')
            ->get();

        $models = UserNotification::where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion)
            ->delete();

        foreach ($query as $pos => $q) { // crear las nuevas notificaciones
            dump('pedido_modificacion creating item: ' . ($pos + 1) . '/' . count($query));
            $dee = $q->detalle_especificacionempaque;
            $dia = $q->fecha_anterior_pedido == hoy() ? 'hoy' : 'mañana';
            $variedad = $dee->variedad;
            $planta = $variedad->planta;
            $cliente = $q->cliente->detalle();
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Cambios en pedidos: ' . $q->operador . $q->cantidad . ' piezas ' . $planta->nombre . ' ' . $dia;
                $model->texto = 'Hay ' . $q->operador . $q->cantidad . ' piezas de ' . $planta->nombre . ': ' . $variedad->nombre . ', ' . $cliente->nombre . ' modificados en pedidos de ' . $dia;
                $model->url = 'modificaciones_pedidos';
                $model->save();
            }
        }
    }
}
