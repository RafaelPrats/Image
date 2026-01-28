<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\CostoHoras;
use yura\Modelos\Submenu;

class CostoHorasController extends Controller
{
    public function inicio(Request $request)
    {
        $costo_horas = CostoHoras::All();
        return view('adminlte.gestion.costos.costo_horas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'costo_horas' => $costo_horas,
        ]);
    }

    public function store_costo_horas(Request $request)
    {
        $model = new CostoHoras();
        $costoHora = CostoHoras::orderBy('id_costo_horas', 'desc')->first();
        $model->id_costo_horas= isset($costoHora->id_costo_horas) ? $costoHora->id_costo_horas + 1 : 1;
        $model->nombre = mb_strtoupper($request->nombre);
        $model->sueldo_promedio = $request->sueldo_promedio;
        $model->valor_hora = $request->valor_hora;
        $model->prov_dt = $request->prov_dt;
        $model->prov_dc = $request->prov_dc;
        $model->prov_reserva = $request->prov_reserva;
        $model->aporte_patronal = $request->aporte_patronal;
        $model->total_provisiones = $request->total_provisiones;
        $model->valor_hora_provisiones = $request->valor_hora_provisiones;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>creado</strong> el nuevo costo por hora.',
        ];
    }

    public function update_costo_horas(Request $request)
    {
        $model = CostoHoras::find($request->id);
        $model->nombre = mb_strtoupper($request->nombre);
        $model->sueldo_promedio = $request->sueldo_promedio;
        $model->valor_hora = $request->valor_hora;
        $model->prov_dt = $request->prov_dt;
        $model->prov_dc = $request->prov_dc;
        $model->prov_reserva = $request->prov_reserva;
        $model->aporte_patronal = $request->aporte_patronal;
        $model->total_provisiones = $request->total_provisiones;
        $model->valor_hora_provisiones = $request->valor_hora_provisiones;
        $model->save();
        return [
            'success' => true,
            'mensaje' => 'Se ha <strong>creado</strong> el nuevo costo por hora.',
        ];
    }
}
