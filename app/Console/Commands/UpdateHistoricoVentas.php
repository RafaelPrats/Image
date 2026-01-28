<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Cliente;
use yura\Modelos\DistribucionMixtos;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\Mixtos;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoPerdido;
use yura\Modelos\Proyecto;
use yura\Modelos\Sector;
use yura\Modelos\Semana;

class UpdateHistoricoVentas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historico_ventas:update {desde=0} {hasta=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Añadir los pedidos a la tabla historico_ventas';

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
        dump('<<<<< ! >>>>> Ejecutando comando "historico_ventas:update" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "historico_ventas:update" <<<<< ! >>>>>');

        $desde_par = $this->argument('desde');
        $hasta_par = $this->argument('hasta');

        if ($desde_par <= $hasta_par) {
            if ($desde_par == 0)
                $desde_par = opDiasFecha('-', 2, hoy());
            if ($hasta_par == 0)
                $hasta_par = opDiasFecha('+', 90, hoy());

            for ($f = $desde_par; $f <= $hasta_par; $f = opDiasFecha('+', 1, $f)) {
                $semana = getSemanaByDate($f);
                dump('preparing fecha: ' . $f);
                $delete = HistoricoVentas::where('fecha', $f)
                    ->delete();

                if ($f < '2025-12-09') {    // viejo
                    $pedidos = Pedido::where('estado', 1)
                        ->where('fecha_pedido', $f)
                        ->get();
                    foreach ($pedidos as $pos_p => $p) {
                        $dinero_pedido = 0;
                        $ramos_pedido = 0;
                        $tallos_pedido = 0;
                        foreach ($p->detalles as $pos_det_p => $det_ped) {
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $pos_esp_emp => $esp_emp) {
                                foreach ($esp_emp->detalles as $pos_det_esp => $det_esp) {
                                    dump('fecha: ' . $f . '/' . $hasta_par .
                                        '; pedido (' . $p->packing . '): ' . ($pos_p + 1) . '/' . count($pedidos) .
                                        '; detalles: ' . ($pos_det_p + 1) . '/' . count($p->detalles) .
                                        '; esp_emp: ' . ($pos_esp_emp + 1) . '/' . count($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque) .
                                        '; det_esp: ' . ($pos_det_esp + 1) . '/' . count($esp_emp->detalles));
                                    $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
                                    $ramos_x_caja = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);

                                    $distribucionAssorted = DistribucionMixtos::where('ramos', '>', 0)
                                        ->where('fecha', opDiasFecha('-', 1, $p->fecha_pedido))
                                        ->where('id_cliente', $p->id_cliente)
                                        ->where('id_pedido', $p->id_pedido)
                                        ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                        ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                                        ->get();

                                    if (count($distribucionAssorted) > 0) {
                                        foreach ($distribucionAssorted as $dist) {
                                            $ramos = $dist->ramos * $dist->piezas;
                                            $tallos = $dist->tallos;
                                            $dinero = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                                            $model = HistoricoVentas::where('fecha', $f)
                                                ->where('id_cliente', $p->id_cliente)
                                                ->where('id_variedad', $dist->variedad()->id_variedad)
                                                ->where('ramos_x_caja', $ramos_x_caja)
                                                ->where('longitud_ramo', $dist->longitud_ramo)
                                                ->get()
                                                ->first();
                                            if ($model == '') {
                                                $model = new HistoricoVentas();
                                                $model->fecha = $f;
                                                $model->id_cliente = $p->id_cliente;
                                                $model->id_variedad = $dist->variedad()->id_variedad;
                                                $model->ramos_x_caja = $ramos_x_caja;
                                                $model->longitud_ramo = $dist->longitud_ramo;
                                                $model->mes = substr($f, 5, 2);
                                                $model->anno = substr($f, 0, 4);
                                                $model->semana = $semana->codigo;
                                                $model->dinero = $dinero;
                                                $model->ramos = $ramos;
                                                $model->tallos = $tallos;
                                                $model->save();
                                            } else {
                                                $model->dinero += $dinero;
                                                $model->ramos += $ramos;
                                                $model->tallos += $tallos;
                                                $model->save();
                                            }

                                            $dinero_pedido += $dinero;
                                            $ramos_pedido += $ramos;
                                            $tallos_pedido += $tallos;
                                        }
                                    } else {
                                        $ramos = $det_ped->cantidad * $esp_emp->cantidad * $ramos_x_caja;
                                        $tallos = $det_ped->cantidad * $esp_emp->cantidad * $ramos_x_caja * $det_esp->tallos_x_ramos;
                                        $dinero = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                                        $model = HistoricoVentas::where('fecha', $f)
                                            ->where('id_cliente', $p->id_cliente)
                                            ->where('id_variedad', $det_esp->id_variedad)
                                            ->where('ramos_x_caja', $ramos_x_caja)
                                            ->where('longitud_ramo', $det_esp->longitud_ramo)
                                            ->get()
                                            ->first();
                                        if ($model == '') {
                                            $model = new HistoricoVentas();
                                            $model->fecha = $f;
                                            $model->id_cliente = $p->id_cliente;
                                            $model->id_variedad = $det_esp->id_variedad;
                                            $model->ramos_x_caja = $ramos_x_caja;
                                            $model->longitud_ramo = $det_esp->longitud_ramo;
                                            $model->mes = substr($f, 5, 2);
                                            $model->anno = substr($f, 0, 4);
                                            $model->semana = $semana->codigo;
                                            $model->dinero = $dinero;
                                            $model->ramos = $ramos;
                                            $model->tallos = $tallos;
                                            $model->save();
                                        } else {
                                            $model->dinero += $dinero;
                                            $model->ramos += $ramos;
                                            $model->tallos += $tallos;
                                            $model->save();
                                        }

                                        $dinero_pedido += $dinero;
                                        $ramos_pedido += $ramos;
                                        $tallos_pedido += $tallos;
                                    }
                                }
                            }
                        }
                        dump('-------------------- FIN PEDIDO (' . $p->packing . ') --------------------- ');
                        dump('Dinero: $' . $dinero_pedido, 'Ramos: ' . $ramos_pedido, 'Tallos: ' . $tallos_pedido);
                    }

                    $pedidos_perdidos = PedidoPerdido::where('fecha_pedido', $f)
                        ->where('tipo', 'P')
                        ->get();
                    foreach ($pedidos_perdidos as $pos_p => $p) {
                        foreach ($p->detalles as $pos_det_p => $det_ped) {
                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $pos_esp_emp => $esp_emp) {
                                foreach ($esp_emp->detalles as $pos_det_esp => $det_esp) {
                                    dump('fecha: ' . $f . '/' . $hasta_par .
                                        '; pedido perdido: ' . ($pos_p + 1) . '/' . count($pedidos_perdidos) .
                                        '; detalles: ' . ($pos_det_p + 1) . '/' . count($p->detalles) .
                                        '; esp_emp: ' . ($pos_esp_emp + 1) . '/' . count($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque) .
                                        '; det_esp: ' . ($pos_det_esp + 1) . '/' . count($esp_emp->detalles));
                                    $ramos_modificado = getRamosXCajaModificadoPerdido($det_ped->id_detalle_pedido_perdido, $det_esp->id_detalle_especificacionempaque);
                                    $ramos_x_caja = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp->cantidad);

                                    $ramos = $det_ped->cantidad * $esp_emp->cantidad * $ramos_x_caja;
                                    $tallos = $det_ped->cantidad * $esp_emp->cantidad * $ramos_x_caja * $det_esp->tallos_x_ramos;
                                    $dinero = $ramos * getPrecioByDetEsp($det_ped->precio, $det_esp->id_detalle_especificacionempaque);
                                    $model = HistoricoVentas::where('fecha', $f)
                                        ->where('id_cliente', $p->id_cliente)
                                        ->where('id_variedad', $det_esp->id_variedad)
                                        ->where('ramos_x_caja', $ramos_x_caja)
                                        ->where('longitud_ramo', $det_esp->longitud_ramo)
                                        ->get()
                                        ->first();
                                    if ($model == '') {
                                        $model = new HistoricoVentas();
                                        $model->fecha = $f;
                                        $model->id_cliente = $p->id_cliente;
                                        $model->id_variedad = $det_esp->id_variedad;
                                        $model->ramos_x_caja = $ramos_x_caja;
                                        $model->longitud_ramo = $det_esp->longitud_ramo;
                                        $model->mes = substr($f, 5, 2);
                                        $model->anno = substr($f, 0, 4);
                                        $model->semana = $semana->codigo;
                                        $model->dinero_perdido = $dinero;
                                        $model->ramos_perdido = $ramos;
                                        $model->tallos_perdido = $tallos;
                                        $model->save();
                                    } else {
                                        $model->dinero_perdido += $dinero;
                                        $model->ramos_perdido += $ramos;
                                        $model->tallos_perdido += $tallos;
                                        $model->save();
                                    }
                                }
                            }
                        }
                    }
                } else {    // nuevo
                    $pedidos = Proyecto::where('estado', 1)
                        ->where('fecha', $f)
                        ->get();
                    foreach ($pedidos as $pos_p => $p) {
                        $dinero_pedido = 0;
                        $ramos_pedido = 0;
                        $tallos_pedido = 0;
                        $cajas = $p->cajas;
                        foreach ($p->cajas as $pos_caja => $caja) {
                            $detalles = $caja->detalles;
                            foreach ($caja->detalles as $pos_det => $detalle) {
                                dump('fecha: ' . $f . '/' . $hasta_par .
                                    '; proyecto (' . $p->packing . '): ' . ($pos_p + 1) . '/' . count($pedidos) .
                                    '; caja: ' . ($pos_caja + 1) . '/' . count($cajas) .
                                    '; detalles: ' . ($pos_det + 1) . '/' . count($detalles));
                                $ramos_x_caja = $detalle->ramos_x_caja;

                                $distribucionAssorted = Mixtos::where('ramos', '>', 0)
                                    ->where('fecha', opDiasFecha('-', 1, $p->fecha))
                                    ->where('id_cliente', $p->id_cliente)
                                    ->where('id_proyecto', $p->id_proyecto)
                                    ->where('id_caja_proyecto', $caja->id_caja_proyecto)
                                    ->where('id_detalle_caja_proyecto', $detalle->id_detalle_caja_proyecto)
                                    ->get();

                                if (count($distribucionAssorted) > 0) {
                                    foreach ($distribucionAssorted as $dist) {
                                        $ramos = $dist->ramos * $dist->piezas;
                                        $tallos = $dist->tallos;
                                        $dinero = $ramos * $detalle->precio;
                                        $model = HistoricoVentas::where('fecha', $f)
                                            ->where('id_cliente', $p->id_cliente)
                                            ->where('id_variedad', $dist->id_variedad)
                                            ->where('ramos_x_caja', $ramos_x_caja)
                                            ->where('longitud_ramo', $dist->longitud_ramo)
                                            ->get()
                                            ->first();
                                        if ($model == '') {
                                            $model = new HistoricoVentas();
                                            $model->fecha = $f;
                                            $model->id_cliente = $p->id_cliente;
                                            $model->id_variedad = $dist->id_variedad;
                                            $model->ramos_x_caja = $ramos_x_caja;
                                            $model->longitud_ramo = $dist->longitud_ramo;
                                            $model->mes = substr($f, 5, 2);
                                            $model->anno = substr($f, 0, 4);
                                            $model->semana = $semana->codigo;
                                            $model->dinero = $dinero;
                                            $model->ramos = $ramos;
                                            $model->tallos = $tallos;
                                            $model->save();
                                        } else {
                                            $model->dinero += $dinero;
                                            $model->ramos += $ramos;
                                            $model->tallos += $tallos;
                                            $model->save();
                                        }

                                        $dinero_pedido += $dinero;
                                        $ramos_pedido += $ramos;
                                        $tallos_pedido += $tallos;
                                    }
                                } else {
                                    $ramos = $caja->cantidad * $ramos_x_caja;
                                    $tallos = $caja->cantidad * $ramos_x_caja * $detalle->tallos_x_ramo;
                                    $dinero = $ramos * $detalle->precio;
                                    $model = HistoricoVentas::where('fecha', $f)
                                        ->where('id_cliente', $p->id_cliente)
                                        ->where('id_variedad', $detalle->id_variedad)
                                        ->where('ramos_x_caja', $ramos_x_caja)
                                        ->where('longitud_ramo', $detalle->longitud_ramo)
                                        ->get()
                                        ->first();
                                    if ($model == '') {
                                        $model = new HistoricoVentas();
                                        $model->fecha = $f;
                                        $model->id_cliente = $p->id_cliente;
                                        $model->id_variedad = $detalle->id_variedad;
                                        $model->ramos_x_caja = $ramos_x_caja;
                                        $model->longitud_ramo = $detalle->longitud_ramo;
                                        $model->mes = substr($f, 5, 2);
                                        $model->anno = substr($f, 0, 4);
                                        $model->semana = $semana->codigo;
                                        $model->dinero = $dinero;
                                        $model->ramos = $ramos;
                                        $model->tallos = $tallos;
                                        $model->save();
                                    } else {
                                        $model->dinero += $dinero;
                                        $model->ramos += $ramos;
                                        $model->tallos += $tallos;
                                        $model->save();
                                    }

                                    $dinero_pedido += $dinero;
                                    $ramos_pedido += $ramos;
                                    $tallos_pedido += $tallos;
                                }
                            }
                        }
                        dump('-------------------- FIN PEDIDO proy:(' . $p->id_proyecto . ') --------------------- ');
                        dump('Dinero: $' . $dinero_pedido, 'Ramos: ' . $ramos_pedido, 'Tallos: ' . $tallos_pedido);
                    }
                }
            }
        }
        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "historico_ventas:update" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "historico_ventas:update" <<<<< * >>>>>');
    }
}
