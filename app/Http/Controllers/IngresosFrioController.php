<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Submenu;

class IngresosFrioController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('cuarto_frio as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'v.id_planta',
                'p.nombre'
            )->distinct()
            ->orderBy('p.orden')
            ->get();
        $presentaciones = DB::table('cuarto_frio as i')
            ->join('empaque as e', 'e.id_empaque', '=', 'i.id_empaque')
            ->select(
                'i.id_empaque',
                'e.nombre'
            )->distinct()
            ->orderBy('e.nombre')
            ->get();
        $longitudes = DB::table('cuarto_frio as i')
            ->select(
                'i.longitud_ramo',
            )->distinct()
            ->orderBy('i.longitud_ramo')
            ->get()->pluck('longitud_ramo')->toArray();
        $rango_fechas = DB::table('cuarto_frio as i')
            ->select(
                DB::raw('min(fecha) as desde'),
                DB::raw('max(fecha) as hasta')
            )
            ->get()[0];

        return view('adminlte.gestion.postcocecha.ingresos_frio.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'presentaciones' => $presentaciones,
            'rango_fechas' => $rango_fechas,
            'longitudes' => $longitudes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = DB::table('cuarto_frio as i')
            ->join('empaque as e', 'e.id_empaque', '=', 'i.id_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_cuarto_frio',
                'i.fecha',
                'i.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'i.id_empaque',
                'e.nombre as pres_nombre',
                'i.longitud_ramo',
                'i.tallos_x_ramo',
                'i.cantidad',
                'i.fecha_registro',
                'i.valor_marcacion',
                'i.disponibles',
            )->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta);
        if ($request->planta != 'T')
            $listado = $listado->where('v.id_planta', $request->planta);
        if ($request->variedad != 'T')
            $listado = $listado->where('i.id_variedad', $request->variedad);
        if ($request->presentacion != 'T')
            $listado = $listado->where('i.id_empaque', $request->presentacion);
        if ($request->longitud != 'T')
            $listado = $listado->where('i.longitud_ramo', $request->longitud);
        $listado = $listado->orderBy('fecha_registro')
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();

        $basura = DB::table('inventario_basura as i')
            ->join('empaque as e', 'e.id_empaque', '=', 'i.id_empaque')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'i.id_inventario_basura',
                'i.fecha',
                'i.id_variedad',
                'v.nombre as var_nombre',
                'v.id_planta',
                'p.nombre as pta_nombre',
                'i.id_empaque',
                'e.nombre as pres_nombre',
                'i.longitud_ramo',
                'i.tallos_x_ramo',
                'i.cantidad',
                'i.fecha_registro',
                'i.valor_marcacion',
            )->distinct()
            ->where('fecha', '>=', $request->desde)
            ->where('fecha', '<=', $request->hasta);
        if ($request->planta != 'T')
            $basura = $basura->where('v.id_planta', $request->planta);
        if ($request->variedad != 'T')
            $basura = $basura->where('i.id_variedad', $request->variedad);
        if ($request->presentacion != 'T')
            $basura = $basura->where('i.id_empaque', $request->presentacion);
        if ($request->longitud != 'T')
            $basura = $basura->where('i.longitud_ramo', $request->longitud);
        $basura = $basura->orderBy('fecha_registro')
            ->orderBy('p.orden')
            ->orderBy('v.orden')
            ->get();

        return view('adminlte.gestion.postcocecha.ingresos_frio.partials.listado', [
            'listado' => $listado,
            'basura' => $basura,
        ]);
    }
}
