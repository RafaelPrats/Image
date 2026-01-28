<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Aerolinea;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\Pais;
use yura\Modelos\Proyecto;
use yura\Modelos\Submenu;

class IngresoDaesController extends Controller
{
    public function inicio(Request $request)
    {
        $agencias = AgenciaCarga::where('estado', 1)
            ->orderBy('nombre')
            ->get();
        $clientes = DB::table('cliente as c')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'c.id_cliente')
            ->select('dc.nombre', 'dc.id_cliente')
            ->where('c.estado', 1)
            ->where('dc.estado', 1)
            ->orderBy('dc.nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.ingreso_daes.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'agencias' => $agencias,
            'clientes' => $clientes,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->select(
                'proyecto.*',
                'ac.nombre as nombre_agencia_carga',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('proyecto.fecha', '>=', $request->desde)
            ->where('proyecto.fecha', '<=', $request->hasta)
            ->where('dc.estado', 1);
        if ($request->cliente != 'T')
            $listado = $listado->where('proyecto.id_cliente', $request->cliente);
        if ($request->agencia != 'T')
            $listado = $listado->where('proyecto.id_agencia_carga', $request->agencia);
        $listado = $listado->orderBy('ac.orden')
            ->orderBy('dc.nombre')
            ->get();

        $paises = Pais::orderBy('nombre')->get();
        $aerolineas = Aerolinea::orderBy('nombre')->get();

        return view('adminlte.gestion.comercializacion.ingreso_daes.partials.listado', [
            'listado' => $listado,
            'paises' => $paises,
            'aerolineas' => $aerolineas,
        ]);
    }

    public function update_daes(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach (json_decode($request->data) as $data) {
                $model = Proyecto::find($data->id);
                $model->dae = $data->dae;
                $model->codigo_pais = $data->pais;
                $model->guia_madre = $data->guia_madre;
                $model->guia_hija = $data->guia_hija;
                $model->id_aerolinea = $data->aerolinea != '' ? $data->aerolinea : null;
                $model->save();
                bitacora('proyecto', $model->id_proyecto, 'U', 'ASIGNACION de DAE y GUIAs');
            }

            DB::commit();
            $success = true;
            $msg = 'Se han <strong>GRABADO</strong> las daes y guias correctamente';
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
