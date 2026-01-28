<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\Indicador;
use yura\Modelos\Pedido;
use yura\Modelos\Planta;
use yura\Modelos\ProyeccionVentaSemanalReal;
use yura\Modelos\ResumenVentaDiaria;
use yura\Modelos\Semana;
use yura\Modelos\Submenu;

class crmVentasController extends Controller
{
    public function inicio(Request $request)
    {
        /* ======= INDICADORES ======= */
        $semana_desde = getSemanaByDate(opDiasFecha('-', 28, hoy()));
        $semana_hasta = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $indicadores = DB::table('historico_ventas as h')
            ->select(
                'h.semana',
                DB::raw('sum(h.dinero) as dinero'),
                DB::raw('sum(h.ramos) as ramos'),
                DB::raw('sum(h.tallos) as tallos')
            )
            ->where('h.semana', '>=', $semana_desde->codigo)
            ->where('h.semana', '<=', $semana_hasta->codigo)
            ->groupBy('h.semana')
            ->orderBy('h.semana')
            ->get();

        /* ======= GRAFICAS ======= */
        $annos = DB::table('historico_ventas')
            ->select('anno')->distinct()
            ->orderBy('anno', 'desc')
            ->get();
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        return view('adminlte.crm.ventas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'annos' => $annos,
            'indicadores' => $indicadores,
            'plantas' => $plantas,
            'clientes' => getClientes(),
        ]);
    }

    public function listar_graficas(Request $request)
    {
        if ($request->annos == '') {
            $view = 'graficas_rango';

            if ($request->rango == 'D') {   // diario
                $labels = DB::table('historico_ventas')
                    ->select('fecha')->distinct()
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('fecha')
                    ->get()->pluck('fecha')->toArray();
            } else if ($request->rango == 'M') {   // mensual
                $labels = DB::table('historico_ventas')
                    ->select(DB::raw('DISTINCT DATE_FORMAT(fecha, "%Y-%m") AS mes'))
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('fecha')
                    ->groupBy('mes', 'fecha')
                    ->get()
                    ->pluck('mes')
                    ->toArray();
            } else {    // semanal
                $labels = DB::table('historico_ventas')
                    ->select('semana')->distinct()
                    ->where('fecha', '>=', $request->desde)
                    ->where('fecha', '<=', $request->hasta)
                    ->orderBy('semana')
                    ->get()->pluck('semana')->toArray();
            }
            $data = [];
            foreach ($labels as $l) {
                $query = DB::table('historico_ventas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select(
                        DB::raw('sum(h.dinero) as dinero'),
                        DB::raw('sum(h.ramos) as ramos'),
                        DB::raw('sum(h.tallos) as tallos')
                    );
                if ($request->rango == 'D') { // diario
                    $query = $query->where('h.fecha', $l);
                } else if ($request->rango == 'M') { // mensual
                    $query = $query->whereMonth('h.fecha', '=', date('m', strtotime($l)))
                        ->whereYear('h.fecha', '=', date('Y', strtotime($l)));
                } else { // semanal 
                    $query = $query->where('h.semana', $l);
                }
                if ($request->cliente != 'T')
                    $query = $query->where('h.id_cliente', $request->cliente);
                if ($request->planta != 'T')
                    $query = $query->where('v.id_planta', $request->planta);
                $query = $query->get()[0];

                $data[] = $query;
            }
            if ($request->tipo_grafica == 'line') {
                $tipo_grafica = 'line';
                $fill_grafica = 'false';
            } else if ($request->tipo_grafica == 'area') {
                $tipo_grafica = 'line';
                $fill_grafica = 'true';
            } else {
                $tipo_grafica = 'bar';
                $fill_grafica = 'true';
            }
            $datos = [
                'labels' => $labels,
                'data' => $data,
                'tipo_grafica' => $tipo_grafica,
                'fill_grafica' => $fill_grafica,
            ];
        } else {
            dd('EN DESARROLLO');
            $view = 'graficas_annos';
            $annos = explode(' - ', $request->annos);
            $labels = getMeses(TP_ABREVIADO);

            $data = [];
            foreach ($annos as $a) {
                $valores = [];
                foreach ($labels as $pos_l => $l) {
                    $mes = str_pad(($pos_l + 1), 2, '0', STR_PAD_LEFT);
                    $query = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.dinero) as dinero'),
                            DB::raw('sum(h.ramos) as ramos'),
                            DB::raw('sum(h.tallos) as tallos')
                        )
                        ->where('h.mes', '=', $mes)
                        ->where('h.anno', '=', $a);
                    if ($request->cliente != 'T')
                        $query = $query->where('h.id_cliente', $request->cliente);
                    if ($request->planta != 'T')
                        $query = $query->where('v.id_planta', $request->planta);
                    $query = $query->get()[0];

                    $valores[] = $query;
                }
                $data[] = [
                    'anno' => $a,
                    'valores' => $valores,
                ];
            }

            $datos = [
                'data' => $data,
                'labels' => $labels
            ];
        }

        return view('adminlte.crm.ventas.partials.' . $view, $datos);
    }

    public function listar_ranking(Request $request)
    {
        if ($request->tipo_ranking == 'C') {  // clientes
            $query = DB::table('historico_ventas as h')
                ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                ->select(
                    'h.id_cliente',
                    'c.nombre',
                    DB::raw('sum(h.dinero) as dinero'),
                    DB::raw('avg(h.dinero / h.tallos) as precio_x_tallo'),
                    DB::raw('sum(h.tallos) as tallos')
                )
                ->where('c.estado', 1)
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->groupBy(
                    'h.id_cliente',
                    'c.nombre'
                );
            if ($request->criterio_ranking == 'D')   // Dinero
                $query = $query->orderBy('dinero', 'desc');
            if ($request->criterio_ranking == 'P')   // Precio x Tallo
                $query = $query->orderBy('precio_x_tallo', 'desc');
            if ($request->criterio_ranking == 'T')   // Tallos
                $query = $query->orderBy('tallos', 'desc');
            $query = $query->limit(4)->get();
        } else {    // flores
            $query = DB::table('historico_ventas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'v.id_planta',
                    'p.nombre',
                    DB::raw('sum(h.dinero) as dinero'),
                    DB::raw('avg(h.dinero / h.tallos) as precio_x_tallo'),
                    DB::raw('sum(h.tallos) as tallos')
                )
                ->where('h.fecha', '>=', $request->desde)
                ->where('h.fecha', '<=', $request->hasta)
                ->groupBy(
                    'v.id_planta',
                    'p.nombre'
                );
            if ($request->criterio_ranking == 'D')   // Dinero
                $query = $query->orderBy('dinero', 'desc');
            if ($request->criterio_ranking == 'P')   // Precio x Tallo
                $query = $query->orderBy('precio_x_tallo', 'desc');
            if ($request->criterio_ranking == 'T')   // Tallos
                $query = $query->orderBy('tallos', 'desc');
            $query = $query->limit(4)->get();
        }
        return view('adminlte.crm.ventas.partials.listar_ranking', [
            'query' => $query,
            'criterio' => $request->criterio_ranking,
        ]);
    }
}
