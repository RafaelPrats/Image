<?php

namespace yura\Http\Controllers\Buquets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\jobCosechaEstimada;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Cliente;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\Pedido;
use yura\Modelos\DistribucionRecetas;
use yura\Modelos\Variedad;

class DistribucionRecetaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::join('variedad as v', 'v.id_planta', '=', 'planta.id_planta')
            ->select('planta.*')->distinct()
            ->where('planta.estado', 1)
            ->where('v.estado', 1)
            ->where('v.receta', 1)
            ->orderBy('planta.nombre')
            ->get();

        return view('adminlte.gestion.buquets.distribucion_recetas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $pedidos = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
            ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
            ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
            ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
            ->select(
                'p.id_cliente',
                'p.id_pedido',
                'dp.id_detalle_pedido',
                'dee.id_detalle_especificacionempaque',
            )->distinct()
            ->where('p.estado', 1)
            ->where('p.fecha_pedido', opDiasFecha('+', 1, $request->fecha))
            ->where('v.receta', 1)
            ->where('v.id_planta', $request->planta)
            ->whereNotNull('dee.tallos_x_ramos')
            ->get();
        $listado = [];
        foreach ($pedidos as $p) {
            $det_esp = DetalleEspecificacionEmpaque::find($p->id_detalle_especificacionempaque);
            $det_ped = DetallePedido::find($p->id_detalle_pedido);

            $getRamosXCajaModificado = getRamosXCajaModificado($p->id_detalle_pedido, $p->id_detalle_especificacionempaque);
            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp->cantidad;

            $item_recetas = DistribucionRecetas::where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                ->where('id_detalle_especificacionempaque', $det_esp->id_detalle_especificacionempaque)
                ->get();

            $listado[] = [
                'pedido' => Pedido::find($p->id_pedido),
                'ramos_x_caja' => $ramos_x_caja,
                'det_ped' => $det_ped,
                'det_esp' => $det_esp,
                'item_recetas' => $item_recetas,
            ];
        }

        return view('adminlte.gestion.buquets.distribucion_recetas.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function admin_recetaByPedido(Request $request)
    {
        $item_recetas = DistribucionRecetas::where('id_detalle_pedido', $request->det_ped)
            ->where('id_detalle_especificacionempaque', $request->det_esp)
            ->get();
        $det_esp = DetalleEspecificacionEmpaque::find($request->det_esp);
        $det_ped = DetallePedido::find($request->det_ped);

        $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
        $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp->cantidad;

        return view('adminlte.gestion.buquets.distribucion_recetas.forms.admin_receta', [
            'item_recetas' => $item_recetas,
            'ramos_x_caja' => $ramos_x_caja,
            'piezas' => $det_ped->cantidad,
            'det_ped' => $det_ped,
            'det_esp' => $det_esp,
            'plantas' => Planta::All()->where('estado', '=', 1),
        ]);
    }

    public function store_agregar_variedades(Request $request)
    {
        DB::beginTransaction();
        try {
            $det_esp = DetalleEspecificacionEmpaque::find($request->det_esp);
            $det_ped = DetallePedido::find($request->det_ped);
            $fecha = $det_ped->pedido->fecha_pedido;
            $delete = DistribucionRecetas::where('id_detalle_pedido', $request->det_ped)
                ->where('id_detalle_especificacionempaque', $request->det_esp)
                ->get();
            foreach ($delete as $del) {
                $id_variedad = $del->variedad()->id_variedad;
                $del->delete();
                jobCosechaEstimada::dispatch($id_variedad, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            $getRamosXCajaModificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp->id_detalle_especificacionempaque);
            $ramos_x_caja = isset($getRamosXCajaModificado) ? $getRamosXCajaModificado->cantidad : $det_esp->cantidad;

            foreach (json_decode($request->data) as $d) {
                $model_var = Variedad::find($d->id_item);
                $model = new DistribucionRecetas();
                $model->id_detalle_pedido = $request->det_ped;
                $model->id_detalle_especificacionempaque = $request->det_esp;
                $model->id_planta = $d->id_planta;
                $model->siglas = $model_var->siglas;
                $model->longitud_ramo = $d->longitud;
                $model->ramos_x_caja = $ramos_x_caja;
                $model->tallos = $d->unidades * $ramos_x_caja * $det_ped->cantidad;
                $model->id_cliente = $det_ped->pedido->id_cliente;
                $model->fecha = $det_ped->pedido->fecha_pedido;
                $model->save();

                jobCosechaEstimada::dispatch($d->id_item, $det_esp->longitud_ramo, opDiasFecha('-', 1, $fecha))
                    ->onQueue('cosecha_estimada')
                    ->onConnection('database');
            }

            $success = true;
            $msg = 'Se han <strong>ASIGNADO</strong> los tallos correctamente';

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
