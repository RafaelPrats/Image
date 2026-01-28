<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CambiosPedido;
use yura\Modelos\Clasificador;
use yura\Modelos\Color;
use yura\Modelos\CuartoFrio;
use yura\Modelos\DistribucionPosco;
use yura\Modelos\Empaque;
use yura\Modelos\InventarioBasura;
use yura\Modelos\PedidoConfirmacion;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;

class PostcosechaController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.postcosecha.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
        ]);
    }

    public function buscar_presentaciones(Request $request)
    {
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fecha_hasta = opDiasFecha('+', $request->dias - 1, $fecha_min);
        $query = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
            ->select('dc.id_empaque', 'e.nombre')->distinct()
            ->where('p.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->where('p.fecha', '>=', $fecha_min)
            ->where('p.fecha', '<=', $fecha_hasta)
            ->orderBy('e.nombre')
            ->get();
        $options = '<option value="">Todos</option>';
        foreach ($query as $item) {
            $options .= '<option value="' . $item->id_empaque . '">' . $item->nombre . '</option>';
        }

        return [
            'options' => $options,
        ];
    }

    public function listar_reporte(Request $request)
    {
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fechas = [];
        for ($i = 1; $i <= $request->dias; $i++) {
            $fechas[] = opDiasFecha('+', $i - 1, $fecha_min);
        }
        $items_solidos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
            ->select(
                'dc.id_variedad',
                'v.nombre as var_nombre',
                'v.orden',
                'dc.id_empaque',
                'e.nombre as pres_nombre',
                'dc.tallos_x_ramo',
                'dc.longitud_ramo',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 0)
            ->where('v.id_planta', $request->planta)
            ->whereIn('p.fecha', $fechas);
        if ($request->variedad != '')
            $items_solidos = $items_solidos->where('dc.id_variedad', $request->variedad);
        if ($request->presentacion != '')
            $items_solidos = $items_solidos->where('dc.id_empaque', $request->presentacion);
        $items_solidos = $items_solidos->orderBy('v.orden')
            ->get();
        $items_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
            ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
            ->join('variedad as vm', 'vm.id_variedad', '=', 'm.id_variedad')
            ->select(
                'm.id_variedad',
                'vm.nombre as var_nombre',
                'vm.orden',
                'dc.id_empaque',
                'e.nombre as pres_nombre',
                'dc.tallos_x_ramo',
                'dc.longitud_ramo',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('m.ramos', '>', 0)
            ->whereIn('p.fecha', $fechas);
        if ($request->variedad != '')
            $items_mixtos = $items_mixtos->where('m.id_variedad', $request->variedad);
        if ($request->presentacion != '')
            $items_mixtos = $items_mixtos->where('dc.id_empaque', $request->presentacion);
        $items_mixtos = $items_mixtos->orderBy('v.orden')
            ->get();
        $items = [];
        foreach ($items_solidos as $item) {
            $items[] = $item;
        }
        foreach ($items_mixtos as $item) {
            if (!in_array($item, $items))
                $items[] = $item;
        }
        // Ordenar por el campo "orden"
        usort($items, function ($a, $b) {
            return $a->orden <=> $b->orden; // Operador nave espacial
        });
        $colores = Color::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $listado = [];
        foreach ($items as $item) {
            $ramos_solidos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->select(
                    'p.fecha',
                    DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as cantidad')
                )
                ->where('p.estado', 1)
                ->where('dc.id_variedad', $item->id_variedad)
                ->where('dc.id_empaque', $item->id_empaque)
                ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                ->where('dc.longitud_ramo', $item->longitud_ramo)
                ->whereIn('p.fecha', $fechas)
                ->groupBy('p.fecha')
                ->orderBy('p.fecha')
                ->get();
            $ramos_mixtos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                ->select(
                    'p.fecha',
                    DB::raw('sum(m.ramos * m.piezas) as cantidad')
                )
                ->where('p.estado', 1)
                ->where('m.id_variedad', $item->id_variedad)
                ->where('dc.id_empaque', $item->id_empaque)
                ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                ->where('m.longitud_ramo', $item->longitud_ramo)
                ->whereIn('p.fecha', $fechas)
                ->groupBy('p.fecha')
                ->orderBy('p.fecha')
                ->get();
            $ramos_distribuidos = DB::table('distribucion_posco')
                ->select(
                    'fecha',
                    DB::raw('sum(cantidad) as cantidad')
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud', $item->longitud_ramo)
                ->whereIn('fecha', $fechas)
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();
            $ramos_cambios = DB::table('cambios_pedido')
                ->select(
                    'fecha_actual as fecha',
                    DB::raw('sum(ramos) as ramos')
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque_p', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud_ramo', $item->longitud_ramo)
                ->whereIn('fecha_actual', $fechas)
                ->groupBy('fecha_actual')
                ->orderBy('fecha_actual')
                ->get();
            $ramos_inventario = DB::table('cuarto_frio')
                ->select(
                    DB::raw('sum(disponibles) as cantidad')
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud_ramo', $item->longitud_ramo)
                ->where('disponibles', '>', 0)
                ->get()[0]->cantidad;
            $listado[] = [
                'item' => $item,
                'ramos_solidos' => $ramos_solidos,
                'ramos_mixtos' => $ramos_mixtos,
                'ramos_distribuidos' => $ramos_distribuidos,
                'ramos_cambios' => $ramos_cambios,
                'ramos_inventario' => $ramos_inventario,
            ];
        }
        return view('adminlte.gestion.comercializacion.postcosecha.partials.listado', [
            'listado' => $listado,
            'fechas' => $fechas,
            'colores' => $colores,
            'planta' => Planta::find($request->planta),
            'filtro_variedad' => $request->variedad,
            'filtro_presentacion' => $request->presentacion,
        ]);
    }

    public function distribuir_trabajo(Request $request)
    {
        $clasificadores = Clasificador::where('estado', 1)->get();
        $variedad = Variedad::find($request->variedad);
        $empaque = Empaque::find($request->empaque);
        $distribuciones = DistribucionPosco::where('fecha', $request->fecha)
            ->where('id_variedad', $request->variedad)
            ->where('id_empaque', $request->empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud', $request->longitud)
            ->orderBy('cantidad')
            ->get();
        $marcaciones_solidos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
            ->select(
                'cm.valor as marcacion',
                'cm.id_dato_exportacion',
                'de.nombre',
            )->distinct()
            ->where('p.estado', 1)
            ->where('dc.id_variedad', $request->variedad)
            ->where('cm.id_dato_exportacion', 1)
            ->where('p.fecha', $request->fecha)
            ->where('dc.id_empaque', $request->empaque)
            ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
            ->where('dc.longitud_ramo', $request->longitud)
            ->get();
        $marcaciones_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
            ->join('mixtos as dist', 'dist.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
            ->select(
                'cm.valor as marcacion',
                'cm.id_dato_exportacion',
                'de.nombre',
            )->distinct()
            ->where('p.estado', 1)
            ->where('dist.id_variedad', $request->variedad)
            ->where('cm.id_dato_exportacion', 1)
            ->where('p.fecha', $request->fecha)
            ->where('dc.id_empaque', $request->empaque)
            ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
            ->where('dc.longitud_ramo', $request->longitud)
            ->get();
        $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');
        return view('adminlte.gestion.comercializacion.postcosecha.forms.distribuir_trabajo', [
            'distribuciones' => $distribuciones,
            'clasificadores' => $clasificadores,
            'planta' => $variedad->planta,
            'variedad' => $variedad,
            'empaque' => $empaque,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud' => $request->longitud,
            'pedidos' => $request->pedidos,
            'actuales' => $request->actuales,
            'saldo' => $request->saldo,
            'fecha' => $request->fecha,
            'pos_comb' => $request->pos,
            'pos_fecha' => $request->pos_f,
            'marcaciones' => $marcaciones,
        ]);
    }

    public function store_distribucion(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (json_decode($request->data) as $d) {
                $model = new DistribucionPosco();
                $model->id_variedad = $request->variedad;
                $model->id_empaque = $request->empaque;
                $model->tallos_x_ramo = $request->tallos_x_ramo;
                $model->longitud = $request->longitud;
                $model->fecha = $request->fecha;
                $model->id_clasificador = $d->clasificador;
                $model->cantidad = $d->cantidad;
                $model->id_dato_exportacion = $d->id_marcacion != '' ? $d->id_marcacion : null;
                $model->valor_marcacion = $d->valor_marcacion != '' ? $d->valor_marcacion : null;
                $model->save();
                $model->id_distribucion_posco = DB::table('distribucion_posco')
                    ->select(DB::raw('max(id_distribucion_posco) as id'))
                    ->get()[0]->id;
                bitacora('distribucion_posco', $model->id_distribucion_posco, 'I', 'STORE_DISTRIBUCION');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>GUARDADO</strong> la distribucion correctamente'
        ];
    }

    public function delete_distribucion(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = DistribucionPosco::find($request->id);
            $model->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>ELIMINADO</strong> la distribucion correctamente'
        ];
    }

    public function actualizar_row(Request $request)
    {
        $variedad = Variedad::find($request->variedad);
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $variedad->id_planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fechas = [];
        for ($i = 1; $i <= $request->dias; $i++) {
            $fechas[] = opDiasFecha('+', $i - 1, $fecha_min);
        }
        $empaque = Empaque::find($request->empaque);
        $color = Color::where('nombre', $variedad->nombre)
            ->get()
            ->first();
        $ramos_solidos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->select(
                'p.fecha',
                DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as cantidad')
            )
            ->where('p.estado', 1)
            ->where('dc.id_variedad', $variedad->id_variedad)
            ->where('dc.id_empaque', $empaque->id_empaque)
            ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
            ->where('dc.longitud_ramo', $request->longitud)
            ->whereIn('p.fecha', $fechas)
            ->groupBy('p.fecha')
            ->orderBy('p.fecha')
            ->get();
        $ramos_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
            ->select(
                'p.fecha',
                DB::raw('sum(m.ramos * m.piezas) as cantidad')
            )
            ->where('p.estado', 1)
            ->where('m.id_variedad', $variedad->id_variedad)
            ->where('dc.id_empaque', $empaque->id_empaque)
            ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
            ->where('m.longitud_ramo', $request->longitud)
            ->whereIn('p.fecha', $fechas)
            ->groupBy('p.fecha')
            ->orderBy('p.fecha')
            ->get();
        $ramos_distribuidos = DB::table('distribucion_posco')
            ->select(
                'fecha',
                DB::raw('sum(cantidad) as cantidad')
            )
            ->where('id_variedad', $variedad->id_variedad)
            ->where('id_empaque', $empaque->id_empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud', $request->longitud)
            ->whereIn('fecha', $fechas)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        $ramos_cambios = DB::table('cambios_pedido')
            ->select(
                'fecha_actual as fecha',
                DB::raw('sum(ramos) as ramos')
            )
            ->where('id_variedad', $variedad->id_variedad)
            ->where('id_empaque_p', $empaque->id_empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud)
            ->whereIn('fecha_actual', $fechas)
            ->groupBy('fecha_actual')
            ->orderBy('fecha_actual')
            ->get();
        $ramos_inventario = DB::table('cuarto_frio')
            ->select(
                DB::raw('sum(disponibles) as cantidad')
            )
            ->where('id_variedad', $variedad->id_variedad)
            ->where('id_empaque', $empaque->id_empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud)
            ->where('disponibles', '>', 0)
            ->get()[0]->cantidad;
        return view('adminlte.gestion.comercializacion.postcosecha.partials._actualizar_row', [
            'variedad' => $variedad,
            'empaque' => $empaque,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud,
            'query_ramos_solidos' => $ramos_solidos,
            'query_ramos_mixtos' => $ramos_mixtos,
            'query_ramos_distribuidos' => $ramos_distribuidos,
            'query_ramos_cambios' => $ramos_cambios,
            'ramos_inventario' => $ramos_inventario,
            'fechas' => $fechas,
            'color' => $color,
            'pos' => $request->pos,
        ]);
    }

    public function modal_armar_row(Request $request)
    {
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $marcaciones = [];
        if ($fecha_min) {
            $variedad = getVariedad($request->variedad);
            $fecha_fin = opDiasFecha('+', ($request->dias - 1), $fecha_min);
            $pedidos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->select('p.id_proyecto', 'p.fecha')->distinct()
                ->where('p.estado', '=', 1)
                ->where('v.id_planta', '=', $request->planta)
                ->where('p.fecha', '>=', $fecha_min)
                ->where('p.fecha', '<=', $fecha_fin)
                ->orderBy('p.fecha')
                ->get();
            $ids_pedidos = $pedidos->pluck('id_proyecto')->toArray();

            $marcaciones_solidos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                ->select(
                    'cm.valor as marcacion',
                    'cm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_proyecto', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 0)
                ->where('dc.id_variedad', $request->variedad)
                ->where('cm.id_dato_exportacion', 1)
                ->where('p.fecha', '>=', $fecha_min)
                ->where('p.fecha', '<=', $fecha_fin)
                ->where('dc.id_empaque', $request->id_empaque_p)
                ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                ->where('dc.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones_mixtos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                ->join('mixtos as dist', 'dist.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                ->select(
                    'cm.valor as marcacion',
                    'cm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->whereIn('p.id_proyecto', $ids_pedidos)
                ->where('p.estado', 1)
                ->where('v.assorted', 1)
                ->where('v.id_planta', $request->planta)
                ->where('dist.id_variedad', $variedad->id_variedad)
                ->where('cm.id_dato_exportacion', 1)
                ->where('p.fecha', '>=', $fecha_min)
                ->where('p.fecha', '<=', $fecha_fin)
                ->where('dc.id_empaque', $request->id_empaque_p)
                ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                ->where('dc.longitud_ramo', $request->longitud_ramo)
                ->get();
            $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');

            $listado = [];
            foreach ($marcaciones as $pos_m => $mar) {
                $pedido = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    //->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                    ->select(
                        DB::raw('sum(dc.ramos_x_caja * cp.cantidad) as ramos')
                    )
                    ->whereIn('p.id_proyecto', $ids_pedidos)
                    ->where('p.estado', '=', 1)
                    ->where('p.fecha', '>=', $fecha_min)
                    ->where('p.fecha', '<=', $fecha_fin)
                    ->where('dc.id_variedad', '=', $variedad->id_variedad)
                    ->where('cm.id_dato_exportacion', '=', $mar->id_dato_exportacion)
                    ->where('cm.valor', '=', $mar->marcacion)
                    ->where('dc.id_empaque', '=', $request->id_empaque_p);
                if ($request->tallos_x_ramo != '')
                    $pedido = $pedido->where('dc.tallos_x_ramo', '=', $request->tallos_x_ramo);
                if ($request->longitud_ramo != '')
                    $pedido = $pedido->where('dc.longitud_ramo', '=', $request->longitud_ramo);
                $pedido = $pedido->get()[0]->ramos;

                $pedido += DB::table('mixtos as m')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_detalle_caja_proyecto', '=', 'm.id_detalle_caja_proyecto')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'm.id_proyecto')
                    ->join('proyecto as p', 'p.id_proyecto', '=', 'm.id_proyecto')
                    ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(m.ramos * m.piezas) as ramos')
                    )
                    ->where('m.id_planta', $variedad->id_planta)
                    ->where('m.id_variedad', $variedad->id_variedad)
                    ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                    ->where('dc.longitud_ramo', $request->longitud_ramo)
                    ->where('dc.id_empaque', $request->id_empaque_p)
                    ->where('p.fecha', '>=', $fecha_min)
                    ->where('p.fecha', '<=', $fecha_fin)
                    ->where('cm.id_dato_exportacion', '=', $mar->id_dato_exportacion)
                    ->where('cm.valor', '=', $mar->marcacion)
                    ->get()[0]->ramos;

                $armados = getCuartoFrioByMarcacion($variedad->id_variedad, $mar->id_dato_exportacion, $mar->marcacion, $request->id_empaque_p, $request->tallos_x_ramo, $request->longitud_ramo);
                $listado[] = [
                    'marcacion' => $mar,
                    'pedidos' => $pedido,
                    'armados' => $armados,
                ];
            }
        } else {
            return '<div class="alert alert-warning text-center">No se han encontrado resultados que mostrar</div>';
        }
        //dd($listado);
        return view('adminlte.gestion.comercializacion.postcosecha.forms.modal_armar_row', [
            'pos_comb' => $request->pos_comb,
            'armar' => $request->armar,
            'variedad' => $variedad,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'presentacion' => Empaque::find($request->id_empaque_p),
            'marcaciones' => $marcaciones,
            'listado' => $listado,
        ]);
    }

    public function store_armar_row(Request $request)
    {
        $success = true;
        $msg = '';
        if ($request->armar > 0) {
            $inventario = new CuartoFrio();
            $inventario->id_variedad = $request->variedad;
            $inventario->id_empaque = $request->id_empaque_p;
            $inventario->tallos_x_ramo = $request->tallos_x_ramo;
            $inventario->longitud_ramo = $request->longitud_ramo;
            $inventario->fecha = $request->fecha;
            $inventario->cantidad = $request->armar;
            $inventario->disponibles = $request->armar;
            $inventario->id_dato_exportacion = $request->id_marcacion;
            $inventario->valor_marcacion = $request->valor_marcacion;

            if ($inventario->save()) {
                $id = DB::table('cuarto_frio')
                    ->select(DB::raw('max(id_cuarto_frio) as id'))
                    ->get()[0]->id;
                bitacora('cuarto_frio', $id, 'I', 'ARMAR_INVENTARIO_FILA');
            } else {
                $success = false;
                $msg .= '<div class="alert alert-warning text-center">' .
                    'Ha ocurrido un problema con los armados</div>';
            }
        }

        if ($success) {
            $msg = 'Se ha <strong>GUARDADO</strong> toda la informacion satisfactoriamente';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function modal_inventario(Request $request)
    {
        $inventarios = DB::table('cuarto_frio')
            ->select(
                'fecha',
                DB::raw('sum(disponibles) as disponibles')
            )
            ->where('id_variedad', $request->variedad)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud_ramo)
            ->where('id_empaque', $request->id_empaque)
            ->where('disponibles', '>', 0)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fechas = [];
        for ($i = 1; $i <= $request->dias; $i++) {
            $fechas[] = opDiasFecha('+', $i - 1, $fecha_min);
        }
        $items_solidos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
            ->select(
                'dc.id_variedad',
                'v.nombre as var_nombre',
                'v.orden',
                'dc.id_empaque',
                'e.nombre as pres_nombre',
                'dc.tallos_x_ramo',
                'dc.longitud_ramo',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 0)
            ->where('v.id_planta', $request->planta)
            ->where('dc.id_variedad', $request->variedad)
            ->whereIn('p.fecha', $fechas)
            ->orderBy('v.orden')
            ->get();
        $items_mixtos = DB::table('proyecto as p')
            ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
            ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
            ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
            ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
            ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
            ->join('variedad as vm', 'vm.id_variedad', '=', 'm.id_variedad')
            ->select(
                'm.id_variedad',
                'vm.nombre as var_nombre',
                'vm.orden',
                'dc.id_empaque',
                'e.nombre as pres_nombre',
                'dc.tallos_x_ramo',
                'dc.longitud_ramo',
            )->distinct()
            ->where('p.estado', 1)
            ->where('v.assorted', 1)
            ->where('v.id_planta', $request->planta)
            ->where('m.ramos', '>', 0)
            ->whereIn('p.fecha', $fechas)
            ->where('m.id_variedad', $request->variedad)
            ->orderBy('v.orden')
            ->get();
        $items = [];
        foreach ($items_solidos as $item) {
            $items[] = $item;
        }
        foreach ($items_mixtos as $item) {
            if (!in_array($item, $items))
                $items[] = $item;
        }
        $listado = [];
        foreach ($items as $item) {
            $ramos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->select(
                    DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as cantidad')
                )
                ->where('p.estado', 1)
                ->where('dc.id_variedad', $item->id_variedad)
                ->where('dc.id_empaque', $item->id_empaque)
                ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                ->where('dc.longitud_ramo', $item->longitud_ramo)
                ->whereIn('p.fecha', $fechas)
                ->get()[0]->cantidad;
            $ramos += DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                ->select(
                    DB::raw('sum(m.piezas * m.ramos) as cantidad')
                )
                ->where('p.estado', 1)
                ->where('m.id_variedad', $item->id_variedad)
                ->where('dc.id_empaque', $item->id_empaque)
                ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                ->where('m.longitud_ramo', $item->longitud_ramo)
                ->whereIn('p.fecha', $fechas)
                ->get()[0]->cantidad;
            $ramos_inventario = DB::table('cuarto_frio')
                ->select(
                    DB::raw('sum(disponibles) as cantidad')
                )
                ->where('id_variedad', $item->id_variedad)
                ->where('id_empaque', $item->id_empaque)
                ->where('tallos_x_ramo', $item->tallos_x_ramo)
                ->where('longitud_ramo', $item->longitud_ramo)
                ->where('disponibles', '>', 0)
                ->get()[0]->cantidad;
            $listado[] = [
                'item' => $item,
                'ramos' => $ramos,
                'ramos_inventario' => $ramos_inventario,
            ];
        }
        return view('adminlte.gestion.comercializacion.postcosecha.forms.modal_inventario', [
            'inventarios' => $inventarios,
            'variedad' => Variedad::find($request->variedad),
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'empaque' => Empaque::find($request->id_empaque),
            'listado' => $listado,
            'fechas' => $fechas,
        ]);
    }

    public function store_cambios(Request $request)
    {
        try {
            DB::beginTransaction();
            // QUITAR del inventario original
            $data = json_decode($request->data_quitar);
            $inventarios = CuartoFrio::where('id_variedad', $request->variedad)
                ->where('id_empaque', $request->empaque)
                ->where('tallos_x_ramo', $request->tallos_x_ramo)
                ->where('longitud_ramo', $request->longitud_ramo)
                ->where('fecha', $data->fecha)
                ->where('disponibles', '>', 0)
                ->orderBy('disponibles')
                ->get();
            $meta = $data->cantidad;
            foreach ($inventarios as $model) {
                if ($meta > 0) {
                    if ($model->disponibles >= $meta) {
                        $model->disponibles = $model->disponibles - $meta;
                        $meta = 0;
                    } else {
                        $meta -= $model->disponibles;
                        $model->disponibles = 0;
                    }

                    $model->save();
                    $id = $model->id_cuarto_frio;
                    bitacora('cuarto_frio', $id, 'U', 'DES-ARMAR_INVENTARIO ' . $data->cantidad);
                }
            }

            // CREAR inventario
            foreach (json_decode($request->data_crear) as $d) {
                $inventario = new CuartoFrio();
                $inventario->id_variedad = $d->id_variedad;
                $inventario->id_empaque = $d->id_empaque;
                $inventario->tallos_x_ramo = $d->tallos_x_ramo;
                $inventario->longitud_ramo = $d->longitud_ramo;
                $inventario->fecha = $data->fecha;
                $inventario->cantidad = $d->cantidad;
                $inventario->disponibles = $d->cantidad;
                //$inventario->id_dato_exportacion = $request->id_marcacion;
                //$inventario->valor_marcacion = $request->valor_marcacion;
                $inventario->save();
                $id = DB::table('cuarto_frio')
                    ->select(DB::raw('max(id_cuarto_frio) as id'))
                    ->get()[0]->id;
                bitacora('cuarto_frio', $id, 'I', 'RE-ARMAR_INVENTARIO');
            }
            // GRABR basura
            if ($request->basura > 0) {
                $basura = new InventarioBasura();
                $basura->id_variedad = $model->id_variedad;
                $basura->id_empaque = $model->id_empaque;
                $basura->tallos_x_ramo = $model->tallos_x_ramo;
                $basura->longitud_ramo = $model->longitud_ramo;
                $basura->fecha = $data->fecha;
                $basura->cantidad = $request->basura;
                $basura->id_dato_exportacion = $model->id_dato_exportacion;
                $basura->valor_marcacion = $model->valor_marcacion;
                $basura->save();
                $id = DB::table('inventario_basura')
                    ->select(DB::raw('max(id_inventario_basura) as id'))
                    ->get()[0]->id;
                bitacora('inventario_basura', $id, 'I', 'RE-ARMAR_INVENTARIO con id:' . $model->id_cuarto_frio . ' a BASURA');
            }

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';
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

    public function confirmar_pedidos(Request $request)
    {
        try {
            DB::beginTransaction();
            $confirmado = PedidoConfirmacion::where('fecha', $request->fecha)
                ->where('id_planta', $request->planta)
                ->where('ejecutado', 1)
                ->get()
                ->first();
            if ($confirmado == '') {
                $items_solidos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                    ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
                    ->select(
                        'dc.id_variedad',
                        'v.nombre as var_nombre',
                        'v.orden',
                        'dc.id_empaque',
                        'e.nombre as pres_nombre',
                        'dc.tallos_x_ramo',
                        'dc.longitud_ramo',
                    )->distinct()
                    ->where('p.estado', 1)
                    ->where('v.assorted', 0)
                    ->where('v.id_planta', $request->planta)
                    ->where('p.fecha', $request->fecha)
                    ->get();
                $items_mixtos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('variedad as v', 'v.id_variedad', '=', 'dc.id_variedad')
                    ->join('empaque as e', 'e.id_empaque', '=', 'dc.id_empaque')
                    ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                    ->join('variedad as vm', 'vm.id_variedad', '=', 'm.id_variedad')
                    ->select(
                        'm.id_variedad',
                        'vm.nombre as var_nombre',
                        'vm.orden',
                        'dc.id_empaque',
                        'e.nombre as pres_nombre',
                        'dc.tallos_x_ramo',
                        'dc.longitud_ramo',
                    )->distinct()
                    ->where('p.estado', 1)
                    ->where('v.assorted', 1)
                    ->where('v.id_planta', $request->planta)
                    ->where('m.ramos', '>', 0)
                    ->where('p.fecha', $request->fecha)
                    ->get();
                $items = [];
                foreach ($items_solidos as $item) {
                    $items[] = $item;
                }
                foreach ($items_mixtos as $item) {
                    if (!in_array($item, $items))
                        $items[] = $item;
                }
                foreach ($items as $item) {
                    $ramos = DB::table('proyecto as p')
                        ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                        ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                        ->select(
                            DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('dc.id_variedad', $item->id_variedad)
                        ->where('dc.id_empaque', $item->id_empaque)
                        ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                        ->where('dc.longitud_ramo', $item->longitud_ramo)
                        ->where('p.fecha', $request->fecha)
                        ->get()[0]->cantidad;
                    $ramos += DB::table('proyecto as p')
                        ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                        ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                        ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                        ->select(
                            DB::raw('sum(m.piezas * m.ramos) as cantidad')
                        )
                        ->where('p.estado', 1)
                        ->where('m.id_variedad', $item->id_variedad)
                        ->where('dc.id_empaque', $item->id_empaque)
                        ->where('dc.tallos_x_ramo', $item->tallos_x_ramo)
                        ->where('m.longitud_ramo', $item->longitud_ramo)
                        ->where('p.fecha', $request->fecha)
                        ->get()[0]->cantidad;
                    $ramos_inventario = DB::table('cuarto_frio')
                        ->select(
                            DB::raw('sum(disponibles) as cantidad')
                        )
                        ->where('id_variedad', $item->id_variedad)
                        ->where('id_empaque', $item->id_empaque)
                        ->where('tallos_x_ramo', $item->tallos_x_ramo)
                        ->where('longitud_ramo', $item->longitud_ramo)
                        ->where('disponibles', '>', 0)
                        ->get()[0]->cantidad;
                    //dd($item->var_nombre . ', ' . $item->pres_nombre . ' ' . $item->tallos_x_ramo . ' tallos ' . $item->longitud_ramo . 'cm</b>"</p>', $ramos, $ramos_inventario);
                    if ($ramos_inventario >= $ramos) {
                        $inventarios = CuartoFrio::where('disponibles', '>', 0)
                            ->where('id_variedad', '=', $item->id_variedad)
                            ->where('id_empaque', '=', $item->id_empaque)
                            ->where('tallos_x_ramo', '=', $item->tallos_x_ramo)
                            ->where('longitud_ramo', '=', $item->longitud_ramo)
                            ->orderBy('fecha', 'asc')
                            ->get();
                        $pedido = $ramos;
                        foreach ($inventarios as $model) {
                            if ($pedido >= 0) {
                                $disponible = $model->disponibles;
                                $usado = 0;
                                if ($pedido >= $disponible) {
                                    $pedido = $pedido - $disponible;
                                    $usado = $disponible;
                                    $disponible = 0;
                                } else {
                                    $disponible = $disponible - $pedido;
                                    $usado = $pedido;
                                    $pedido = 0;
                                }
                                $model->disponibles = $disponible;
                                $model->save();
                                bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'CONFIRMAR_PEDIDO ' . $usado . ' ramos');

                                /*if ($usado > 0) {
                                    $uso_inventario = new UsoInventarioFrio();
                                    $uso_inventario->id_inventario_frio = $model->id_inventario_frio;
                                    $uso_inventario->fecha_pedido = $request->fecha_pedidos;
                                    $uso_inventario->ramos = $usado;
                                    $uso_inventario->save();
                                }*/
                            }
                        }
                    } else {
                        DB::rollBack();
                        return [
                            'success' => false,
                            'mensaje' => '<div class="alert alert-warning text-center">' .
                                '<p>No hay ramos suficientes de "<b>' . $item->var_nombre . ', ' . $item->pres_nombre . ' ' . $item->tallos_x_ramo . ' tallos ' . $item->longitud_ramo . 'cm</b>"</p>'
                                . '</div>',
                        ];
                    }
                }

                /* ============ TABLA PEDIDO_CONFIRMACION ===============*/
                $confirmacion = PedidoConfirmacion::where('fecha', '<=', $request->fecha)
                    ->where('id_planta', $request->planta)
                    ->where('ejecutado', 0)
                    ->get();
                foreach ($confirmacion as $conf) {
                    $conf->ejecutado = 1;
                    $conf->save();
                    $id = $conf->id_pedido_confirmacion;
                    bitacora('pedido_confirmacion', $id, 'U', 'CONFIRMAR_PEDIDO');
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la informacion correctamente';
            } else {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p>Ya se <b>CONFIRMO</b> la fecha <b>"' . $request->fecha . '"</b> para la <b>flor indicada</b>, por favor, actualice la pagina</p>'
                    . '</div>';
            }
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

    public function ver_cambios(Request $request)
    {
        $variedad = Variedad::find($request->variedad);
        $empaque = Empaque::find($request->empaque);
        $cambios = CambiosPedido::where('fecha_actual', $request->fecha)
            ->where('id_variedad', $request->variedad)
            ->where('id_empaque_p', $request->empaque)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud)
            ->orderBy('fecha_registro')
            ->get();
        return view('adminlte.gestion.comercializacion.postcosecha.partials.ver_cambios', [
            'listado' => $cambios,
            'planta' => $variedad->planta,
            'variedad' => $variedad,
            'empaque' => $empaque,
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud' => $request->longitud,
            'fecha' => $request->fecha,
            'pos_comb' => $request->pos,
            'pos_fecha' => $request->pos_f,
        ]);
    }

    public function ver_inventario(Request $request)
    {
        $inventarios = CuartoFrio::where('id_variedad', $request->variedad)
            ->where('id_empaque', $request->id_empaque_p)
            ->where('tallos_x_ramo', $request->tallos_x_ramo)
            ->where('longitud_ramo', $request->longitud_ramo)
            ->orderBy('fecha_registro')
            ->get();

        $fecha_min = DB::table('pedido_confirmacion')
            ->select(DB::raw('min(fecha) as fecha'))
            ->where('id_planta', $request->planta)
            ->where('ejecutado', 0)
            ->get()[0]->fecha;
        $fechas = [];
        for ($i = 1; $i <= $request->dias; $i++) {
            $fechas[] = opDiasFecha('+', $i - 1, $fecha_min);
        }
        $listado_marcaciones = [];
        $array_marcaciones = [];
        if ($fecha_min) {
            $marcaciones_solidos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                ->select(
                    'cm.valor as marcacion',
                    'cm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->where('p.estado', 1)
                ->whereIn('p.fecha', $fechas)
                ->where('dc.id_variedad', $request->variedad)
                ->where('dc.id_empaque', $request->id_empaque_p)
                ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                ->where('dc.longitud_ramo', $request->longitud_ramo)
                ->where('cm.id_dato_exportacion', 1)
                ->orderBy('cm.valor')
                ->get();
            $marcaciones_mixtos = DB::table('proyecto as p')
                ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                ->join('dato_exportacion as de', 'de.id_dato_exportacion', '=', 'cm.id_dato_exportacion')
                ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                ->select(
                    'cm.valor as marcacion',
                    'cm.id_dato_exportacion',
                    'de.nombre',
                )->distinct()
                ->where('p.estado', 1)
                ->whereIn('p.fecha', $fechas)
                ->where('m.id_variedad', $request->variedad)
                ->where('dc.id_empaque', $request->id_empaque_p)
                ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                ->where('dc.longitud_ramo', $request->longitud_ramo)
                ->where('cm.id_dato_exportacion', 1)
                ->orderBy('cm.valor')
                ->get();
            $marcaciones = $marcaciones_solidos->merge($marcaciones_mixtos)->sortBy('nombre');

            foreach ($marcaciones as $pos_m => $mar) {
                $ramos_solidos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->select(
                        DB::raw('sum(cp.cantidad * dc.ramos_x_caja) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->whereIn('p.fecha', $fechas)
                    ->where('dc.id_variedad', $request->variedad)
                    ->where('dc.id_empaque', $request->id_empaque_p)
                    ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                    ->where('dc.longitud_ramo', $request->longitud_ramo)
                    ->where('cm.id_dato_exportacion', 1)
                    ->where('cm.valor', $mar->marcacion)
                    ->get()[0]->cantidad;
                $ramos_mixtos = DB::table('proyecto as p')
                    ->join('caja_proyecto as cp', 'cp.id_proyecto', '=', 'p.id_proyecto')
                    ->join('detalle_caja_proyecto as dc', 'dc.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('caja_proyecto_marcacion as cm', 'cm.id_caja_proyecto', '=', 'cp.id_caja_proyecto')
                    ->join('mixtos as m', 'm.id_detalle_caja_proyecto', '=', 'dc.id_detalle_caja_proyecto')
                    ->select(
                        DB::raw('sum(m.ramos * m.piezas) as cantidad')
                    )
                    ->where('p.estado', 1)
                    ->whereIn('p.fecha', $fechas)
                    ->where('m.id_variedad', $request->variedad)
                    ->where('dc.id_empaque', $request->id_empaque_p)
                    ->where('dc.tallos_x_ramo', $request->tallos_x_ramo)
                    ->where('m.longitud_ramo', $request->longitud_ramo)
                    ->where('cm.id_dato_exportacion', 1)
                    ->where('cm.valor', $mar->marcacion)
                    ->get()[0]->cantidad;
                $armados = DB::table('cuarto_frio')
                    ->select(DB::raw('sum(disponibles) as cantidad'))
                    ->where('id_variedad', $request->variedad)
                    ->where('id_dato_exportacion', $mar->id_dato_exportacion)
                    ->where('valor_marcacion', $mar->marcacion)
                    ->where('id_empaque', $request->id_empaque_p)
                    ->where('tallos_x_ramo', $request->tallos_x_ramo)
                    ->where('longitud_ramo', $request->longitud_ramo)
                    ->where('disponibles', '>', 0)
                    ->get()[0]->cantidad;
                $pedidos = $ramos_solidos + $ramos_mixtos;
                if ($pedidos > 0) {
                    $listado_marcaciones[] = [
                        'marcacion' => $mar,
                        'pedidos' => $pedidos,
                        'armados' => $armados,
                    ];
                    $array_marcaciones[] = $mar->marcacion;
                }
            }
        }

        return view('adminlte.gestion.comercializacion.postcosecha.forms.ver_inventario', [
            'listado' => $inventarios,
            'listado_marcaciones' => $listado_marcaciones,
            'array_marcaciones' => $array_marcaciones,
            'variedad' => Variedad::find($request->variedad),
            'tallos_x_ramo' => $request->tallos_x_ramo,
            'longitud_ramo' => $request->longitud_ramo,
            'presentacion' => Empaque::find($request->id_empaque_p),
        ]);
    }

    public function update_marcacion_inventario(Request $request)
    {
        DB::beginTransaction();
        try {
            $model = CuartoFrio::find($request->id);
            $model->valor_marcacion = $request->valor;
            $model->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine()
            ];
        }
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>MODIFCADO</strong> la marcacion correctamente'
        ];
    }
}
