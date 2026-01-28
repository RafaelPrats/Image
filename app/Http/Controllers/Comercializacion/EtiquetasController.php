<?php

namespace yura\Http\Controllers\Comercializacion;

use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\Proyecto;
use yura\Modelos\Submenu;
use Barryvdh\DomPDF\Facade as PDF;
use Picqer\Barcode\BarcodeGeneratorHTML;

class EtiquetasController extends Controller
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

        return view('adminlte.gestion.comercializacion.etiquetas.inicio', [
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
            ->where('proyecto.fecha', $request->fecha)
            ->where('dc.estado', 1);
        if ($request->cliente != 'T')
            $listado = $listado->where('proyecto.id_cliente', $request->cliente);
        if ($request->agencia != 'T')
            $listado = $listado->where('proyecto.id_agencia_carga', $request->agencia);
        $listado = $listado->orderBy('ac.orden')
            ->orderBy('dc.nombre')
            ->get();

        return view('adminlte.gestion.comercializacion.etiquetas.partials.listado', [
            'listado' => $listado,
        ]);
    }

    public function descargar_etiqueta(Request $request)
    {
        $dobles = [$request->doble];
        $pedidos = Proyecto::whereIn('id_proyecto', [$request->id])->get();
        foreach ($pedidos as $p) {
            $p->impreso = 1;
            $p->save();
        }
        $datos = [
            'pedidos' => $pedidos,
            'dobles' => $dobles,
        ];
        $barCode = new BarcodeGeneratorHTML();

        return PDF::loadView('adminlte.gestion.comercializacion.etiquetas.partials.etiquetas', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 245, 321), 'portrait')
            //->stream();
            ->download('Etiqueta_' . hoy() . '.pdf');
    }

    public function descargar_etiquetas_all(Request $request)
    {
        $dobles = json_decode($request->dobles);
        $pedidos = Proyecto::join('agencia_carga as ac', 'ac.id_agencia_carga', '=', 'proyecto.id_agencia_carga')
            ->join('detalle_cliente as dc', 'dc.id_cliente', '=', 'proyecto.id_cliente')
            ->select(
                'proyecto.*',
                'ac.nombre as nombre_agencia_carga',
                'dc.nombre as nombre_cliente'
            )->distinct()
            ->where('dc.estado', 1)
            ->whereIn('proyecto.id_proyecto', json_decode($request->data))
            ->orderBy('dc.nombre')
            ->get();
        foreach ($pedidos as $p) {
            $p->impreso = 1;
            $p->save();
        }
        $datos = [
            'pedidos' => $pedidos,
            'dobles' => $dobles,
        ];
        $barCode = new BarcodeGeneratorHTML();

        return PDF::loadView('adminlte.gestion.comercializacion.etiquetas.partials.etiquetas', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 245, 321), 'portrait')
            //->stream();
            ->download('Etiqueta_' . hoy() . '.pdf');
    }
}
