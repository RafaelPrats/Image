<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Empaque;
use yura\Modelos\Submenu;

class ReporteCajasController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.crm.reporte_cajas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }
}
