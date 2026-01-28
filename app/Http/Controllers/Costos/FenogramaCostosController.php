<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\AplicacionCampo;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\Ciclo;
use yura\Modelos\CostoHoras;
use yura\Modelos\ManoObra;
use yura\Modelos\Producto;
use yura\Modelos\Submenu;

class FenogramaCostosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.costos.fenograma_costos.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_ciclos(Request $request)
    {
        $ciclos = Ciclo::where('estado', 1)
            ->where('fecha_inicio', '<=', $request->fecha)
            ->where('fecha_fin', '>=', $request->fecha);
        if ($request->ps != 'T')
            $ciclos = $ciclos->where('poda_siembra', $request->ps);
        if ($request->estado != 'T')
            $ciclos = $ciclos->where('activo', $request->estado);
        if ($request->variedad != 'T')
            $ciclos = $ciclos->where('id_variedad', $request->variedad);
        $ciclos = $ciclos->orderBy('fecha_inicio')->get();

        $app_giberelico = AplicacionMatriz::All()
            ->where('nombre', 'ACIDO GIBERELICO')
            ->first();
        $app_desbrote = AplicacionMatriz::All()
            ->where('nombre', 'DESBROTE')
            ->first();
        return view('adminlte.gestion.costos.fenograma_costos.partials.listado', [
            'ciclos' => $ciclos,
            'fecha' => $request->fecha,
            'app_giberelico' => $app_giberelico,
            'app_desbrote' => $app_desbrote,
        ]);
    }

    public function exportar_reporte(Request $request)
    {
        dd('En desarrollo');
    }

    public function ver_labores_giberelico(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->app_matriz);
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $labores = AplicacionCampo::where('id_ciclo', $request->ciclo)
            ->whereIn('id_aplicacion', $ids_app)
            ->orderBy('fecha')
            ->get();
        $ids_productos = [];
        $ids_mano_obras = [];
        foreach ($labores as $labor)
            foreach ($labor->detalles as $det) {
                if ($det->id_producto != '' && !in_array($det->id_producto, $ids_productos))
                    array_push($ids_productos, $det->id_producto);
                if ($det->id_mano_obra != '' && !in_array($det->id_mano_obra, $ids_mano_obras))
                    array_push($ids_mano_obras, $det->id_mano_obra);
            }
        $productos = Producto::whereIn('id_producto', $ids_productos)
            ->orderBy('nombre')
            ->get();
        $mano_obras = ManoObra::whereIn('id_mano_obra', $ids_mano_obras)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.costos.fenograma_costos.partials._ver_labores_giberelico', [
            'ciclo' => Ciclo::find($request->ciclo),
            'app_matriz' => $app_matriz,
            'labores' => $labores,
            'productos' => $productos,
            'mano_obras' => $mano_obras,
        ]);
    }

    public function ver_labores_desbrote(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->app_matriz);
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $labores = AplicacionCampo::where('id_ciclo', $request->ciclo)
            ->whereIn('id_aplicacion', $ids_app)
            ->orderBy('fecha')
            ->get();
        $ids_mano_obras = [];
        foreach ($labores as $labor)
            foreach ($labor->detalles as $det) {
                if ($det->id_mano_obra != '' && !in_array($det->id_mano_obra, $ids_mano_obras))
                    array_push($ids_mano_obras, $det->id_mano_obra);
            }
        $mano_obras = ManoObra::whereIn('id_mano_obra', $ids_mano_obras)
            ->orderBy('nombre')
            ->get();

        $hr_ordinaria = CostoHoras::All()
            ->where('nombre', 'ORDINARIA')
            ->first();
        $hr_50 = CostoHoras::All()
            ->where('nombre', '50%')
            ->first();
        $hr_100 = CostoHoras::All()
            ->where('nombre', '100%')
            ->first();
        return view('adminlte.gestion.costos.fenograma_costos.partials._ver_labores_desbrote', [
            'ciclo' => Ciclo::find($request->ciclo),
            'app_matriz' => $app_matriz,
            'labores' => $labores,
            'mano_obras' => $mano_obras,
            'hr_ordinaria' => $hr_ordinaria,
            'hr_50' => $hr_50,
            'hr_100' => $hr_100,
        ]);
    }
}
