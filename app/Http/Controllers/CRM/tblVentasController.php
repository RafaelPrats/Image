<?php

namespace yura\Http\Controllers\CRM;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\Pais;
use yura\Modelos\Submenu;
use yura\Modelos\Planta;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class tblVentasController extends Controller
{
    public function inicio(Request $request)
    {
        $annos = DB::table('historico_ventas')
            ->select('anno')->distinct()
            ->orderBy('anno')
            ->get();
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));

        return view('adminlte.crm.tbl_ventas.inicio', [
            'annos' => $annos,
            'plantas' => $plantas,
            'semana_pasada' => $semana_pasada,
            'clientes' => getClientes(),

            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function filtrar_tablas(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $desde_semanal = substr($a, 2, 2) . $desde_semanal;
                    $hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $hasta_semanal = substr($a, 2, 2) . $hasta_semanal;
                    $semanas = getSemanasByCodigos($desde_semanal, $hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }
                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    $clientes = DB::table('historico_ventas as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('h.id_cliente', $c->id_cliente)
                                    ->where('h.semana', $sem->codigo);
                                if ($request->planta != 'T')
                                    $query = $query->where('v.id_planta', $request->planta);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'semanal_flores';
                    $plantas = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('v.id_planta', 'p.nombre')->distinct();
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    $plantas = $plantas->orderBy('p.orden')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('v.id_planta', $p->id_planta)
                                    ->where('h.semana', $sem->codigo);
                                if ($request->cliente != 'T')
                                    $query = $query->where('h.id_cliente', $request->cliente);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_semanas))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_semanas' => $valores_semanas
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    $clientes = DB::table('historico_ventas as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('h.id_cliente', $c->id_cliente)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno']);
                                if ($request->planta != 'T')
                                    $query = $query->where('v.id_planta', $request->planta);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'mensual_flores';
                    $plantas = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('v.id_planta', 'p.nombre')->distinct();
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    $plantas = $plantas->orderBy('p.orden')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('v.id_planta', $p->id_planta)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno']);
                                if ($request->cliente != 'T')
                                    $query = $query->where('h.id_cliente', $request->cliente);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_meses))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_meses' => $valores_meses
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }

        return view('adminlte.crm.tbl_ventas.partials.' . $view, [
            'listado' => $listado,
            'listado_annos' => $listado_annos,
            'criterio' => $request->criterio,
        ]);
    }

    public function select_planta_semanal(Request $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
            $listado_annos = [];
            foreach ($annos as $a) {
                $desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                $desde_semanal = substr($a, 2, 2) . $desde_semanal;
                $hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                $hasta_semanal = substr($a, 2, 2) . $hasta_semanal;
                $semanas = getSemanasByCodigos($desde_semanal, $hasta_semanal);
                $listado_annos[] = [
                    'anno' => $a,
                    'semanas' => $semanas,
                ];
            }

            $longitudes = DB::table('historico_ventas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.longitud_ramo')->distinct()
                ->where('v.id_planta', $request->planta)
                ->orderBy('h.longitud_ramo', 'desc')
                ->get()->pluck('longitud_ramo')->toArray();

            foreach ($longitudes as $l) {
                $variedades = DB::table('historico_ventas as h')
                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                    ->select('h.id_variedad', 'v.nombre')->distinct()
                    ->where('v.id_planta', $request->planta)
                    ->where('h.longitud_ramo', $l)
                    ->orderBy('v.orden')
                    ->get();
                $valores_variedades = [];
                foreach ($variedades as $v) {
                    $valores_anno = [];
                    foreach ($listado_annos as $a) {
                        $valores_semanas = [];
                        foreach ($a['semanas'] as $sem) {
                            $query = DB::table('historico_ventas as h')
                                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                ->select(
                                    DB::raw('sum(h.dinero) as dinero'),
                                    DB::raw('sum(h.ramos) as ramos'),
                                    DB::raw('sum(h.tallos) as tallos'),
                                    DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                    DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                    DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                )
                                ->where('v.id_planta', $request->planta)
                                ->where('h.id_variedad', $v->id_variedad)
                                ->where('h.longitud_ramo', $l)
                                ->where('h.semana', $sem->codigo);
                            if ($request->cliente != 'T')
                                $query = $query->where('h.id_cliente', $request->cliente);
                            $query = $query->get()[0];

                            $valor = 0;
                            if ($request->criterio == 'D') {
                                $valor = round($query->dinero, 2);
                            }
                            if ($request->criterio == 'R') {
                                $valor = $query->ramos;
                            }
                            if ($request->criterio == 'T') {
                                $valor = $query->tallos;
                            }
                            if ($request->criterio == 'P') {
                                $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                            }
                            if ($request->criterio == 'DP') {
                                $valor = round($query->dinero_perdido, 2);
                            }
                            if ($request->criterio == 'RP') {
                                $valor = $query->ramos_perdido;
                            }
                            if ($request->criterio == 'TP') {
                                $valor = $query->tallos_perdido;
                            }
                            $valores_semanas[] = [
                                'semana' => $sem->codigo,
                                'valor' => $valor != '' ? $valor : 0,
                            ];
                        }
                        $valores_anno[] = [
                            'anno' => $a['anno'],
                            'valores_semanas' => $valores_semanas
                        ];
                    }
                    $valores_variedades[] = [
                        'variedad' => $v,
                        'valores_anno' => $valores_anno
                    ];
                }
                $listado[] = [
                    'longitud' => $l,
                    'valores_variedades' => $valores_variedades
                ];
            }
            return view('adminlte.crm.tbl_ventas.partials.detalles.select_planta_semanal', [
                'listado' => $listado,
                'listado_annos' => $listado_annos,
                'criterio' => $request->criterio,
                'planta' => Planta::find($request->planta),
            ]);
        } else {
            return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
        }
    }

    public function select_planta_diario(Request $request)
    {
        $fechas = DB::table('historico_ventas as h')
            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
            ->select('h.fecha')->distinct()
            ->where('v.id_planta', $request->planta)
            ->where('h.semana', $request->semana)
            ->orderBy('h.fecha')
            ->get()->pluck('fecha')->toArray();

        $longitudes = DB::table('historico_ventas as h')
            ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
            ->select('h.longitud_ramo')->distinct()
            ->where('v.id_planta', $request->planta)
            ->where('h.semana', $request->semana)
            ->orderBy('h.longitud_ramo', 'desc')
            ->get()->pluck('longitud_ramo')->toArray();

        $listado = [];
        foreach ($longitudes as $l) {
            $variedades = DB::table('historico_ventas as h')
                ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                ->select('h.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('h.longitud_ramo', $l)
                ->orderBy('v.orden')
                ->get();
            $valores_variedades = [];
            foreach ($variedades as $v) {
                $valores_fechas = [];
                foreach ($fechas as $f) {
                    $query = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->select(
                            DB::raw('sum(h.dinero) as dinero'),
                            DB::raw('sum(h.ramos) as ramos'),
                            DB::raw('sum(h.tallos) as tallos'),
                            DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                            DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                            DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                        )
                        ->where('v.id_planta', $request->planta)
                        ->where('h.id_variedad', $v->id_variedad)
                        ->where('h.longitud_ramo', $l)
                        ->where('h.fecha', $f);
                    if ($request->cliente != 'T')
                        $query = $query->where('h.id_cliente', $request->cliente);
                    $query = $query->get()[0];

                    $valor = 0;
                    if ($request->criterio == 'D') {
                        $valor = round($query->dinero, 2);
                    }
                    if ($request->criterio == 'R') {
                        $valor = $query->ramos;
                    }
                    if ($request->criterio == 'T') {
                        $valor = $query->tallos;
                    }
                    if ($request->criterio == 'P') {
                        $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                    }
                    if ($request->criterio == 'DP') {
                        $valor = round($query->dinero_perdido, 2);
                    }
                    if ($request->criterio == 'RP') {
                        $valor = $query->ramos_perdido;
                    }
                    if ($request->criterio == 'TP') {
                        $valor = $query->tallos_perdido;
                    }
                    $valores_fechas[] = [
                        'fecha' => $f,
                        'valor' => $valor != '' ? $valor : 0,
                    ];
                }
                $valores_variedades[] = [
                    'variedad' => $v,
                    'valores_fechas' => $valores_fechas
                ];
            }
            $listado[] = [
                'longitud' => $l,
                'valores_variedades' => $valores_variedades
            ];
        }
        return view('adminlte.crm.tbl_ventas.partials.detalles.select_planta_diario', [
            'listado' => $listado,
            'fechas' => $fechas,
            'criterio' => $request->criterio,
            'planta' => Planta::find($request->planta),
        ]);
    }

    public function exportar_tabla(Request $request)
    {
        $datos = json_decode($request->datos);
        $spread = new Spreadsheet();
        $this->excel_listado($spread, $datos);

        $fileName = "Tabla Ventas.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
    }

    public function excel_listado($spread, $request)
    {
        if ($request->annos == '')
            $annos = [date('Y')];
        else
            $annos = explode(' - ', $request->annos);

        $listado = [];
        if ($request->rango == 'S') { // SEMANAL
            if ($request->desde_semanal != '' && $request->hasta_semanal != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $desde_semanal = strlen($request->desde_semanal) < 2 ? '0' . $request->desde_semanal : $request->desde_semanal;
                    $desde_semanal = substr($a, 2, 2) . $desde_semanal;
                    $hasta_semanal = strlen($request->hasta_semanal) < 2 ? '0' . $request->hasta_semanal : $request->hasta_semanal;
                    $hasta_semanal = substr($a, 2, 2) . $hasta_semanal;
                    $semanas = getSemanasByCodigos($desde_semanal, $hasta_semanal);
                    $listado_annos[] = [
                        'anno' => $a,
                        'semanas' => $semanas,
                    ];
                }
                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'semanal_clientes';
                    $clientes = DB::table('historico_ventas as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('h.id_cliente', $c->id_cliente)
                                    ->where('h.semana', $sem->codigo);
                                if ($request->planta != 'T')
                                    $query = $query->where('v.id_planta', $request->planta);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_semanas' => $valores_semanas
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'semanal_flores';
                    $plantas = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('v.id_planta', 'p.nombre')->distinct();
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    $plantas = $plantas->orderBy('p.orden')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_semanas = [];
                            foreach ($a['semanas'] as $sem) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('v.id_planta', $p->id_planta)
                                    ->where('h.semana', $sem->codigo);
                                if ($request->cliente != 'T')
                                    $query = $query->where('h.id_cliente', $request->cliente);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_semanas[] = [
                                    'semana' => $sem->codigo,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_semanas))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_semanas' => $valores_semanas
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Las Semanas "desde" y "hasta" son incorrectas.</div>';
            }
        } else {    // MENSUAL
            if ($request->desde_mensual != '' && $request->hasta_mensual != '') {
                $listado_annos = [];
                foreach ($annos as $a) {
                    $meses = [];
                    for ($m = $request->desde_mensual; $m <= $request->hasta_mensual; $m++) {
                        $meses[] = strlen($m) == 1 ? '0' . $m : $m;
                    }
                    $listado_annos[] = [
                        'anno' => $a,
                        'meses' => $meses,
                    ];
                }

                if ($request->tipo_listado == 'C') {    // por clientes
                    $view = 'mensual_clientes';
                    $clientes = DB::table('historico_ventas as h')
                        ->join('detalle_cliente as c', 'c.id_cliente', '=', 'h.id_cliente')
                        ->select('h.id_cliente', 'c.nombre')->distinct()
                        ->where('c.estado', 1);
                    if ($request->cliente != 'T')
                        $clientes = $clientes->where('h.id_cliente', $request->cliente);
                    $clientes = $clientes->orderBy('c.nombre')
                        ->get();
                    foreach ($clientes as $c) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('h.id_cliente', $c->id_cliente)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno']);
                                if ($request->planta != 'T')
                                    $query = $query->where('v.id_planta', $request->planta);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            $valores_anno[] = [
                                'anno' => $a['anno'],
                                'valores_meses' => $valores_meses
                            ];
                        }
                        $listado[] = [
                            'cliente' => $c,
                            'valores_anno' => $valores_anno
                        ];
                    }
                } else {    // por flores
                    $view = 'mensual_flores';
                    $plantas = DB::table('historico_ventas as h')
                        ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                        ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                        ->select('v.id_planta', 'p.nombre')->distinct();
                    if ($request->planta != 'T')
                        $plantas = $plantas->where('v.id_planta', $request->planta);
                    $plantas = $plantas->orderBy('p.orden')
                        ->get();
                    foreach ($plantas as $p) {
                        $valores_anno = [];
                        foreach ($listado_annos as $a) {
                            $valores_meses = [];
                            foreach ($a['meses'] as $mes) {
                                $query = DB::table('historico_ventas as h')
                                    ->join('variedad as v', 'v.id_variedad', '=', 'h.id_variedad')
                                    ->select(
                                        DB::raw('sum(h.dinero) as dinero'),
                                        DB::raw('sum(h.ramos) as ramos'),
                                        DB::raw('sum(h.tallos) as tallos'),
                                        DB::raw('sum(h.dinero_perdido) as dinero_perdido'),
                                        DB::raw('sum(h.ramos_perdido) as ramos_perdido'),
                                        DB::raw('sum(h.tallos_perdido) as tallos_perdido')
                                    )
                                    ->where('v.id_planta', $p->id_planta)
                                    ->where('h.mes', $mes)
                                    ->where('h.anno', $a['anno']);
                                if ($request->cliente != 'T')
                                    $query = $query->where('h.id_cliente', $request->cliente);
                                if ($request->variedad != 'T')
                                    $query = $query->where('h.id_variedad', $request->variedad);
                                $query = $query->get()[0];

                                $valor = 0;
                                if ($request->criterio == 'D') {
                                    $valor = round($query->dinero, 2);
                                }
                                if ($request->criterio == 'R') {
                                    $valor = $query->ramos;
                                }
                                if ($request->criterio == 'T') {
                                    $valor = $query->tallos;
                                }
                                if ($request->criterio == 'P') {
                                    $valor = $query->ramos > 0 ? round($query->dinero / $query->ramos, 2) : 0;
                                }
                                if ($request->criterio == 'DP') {
                                    $valor = round($query->dinero_perdido, 2);
                                }
                                if ($request->criterio == 'RP') {
                                    $valor = $query->ramos_perdido;
                                }
                                if ($request->criterio == 'TP') {
                                    $valor = $query->tallos_perdido;
                                }

                                $valores_meses[] = [
                                    'mes' => $mes,
                                    'valor' => $valor != '' ? $valor : 0,
                                ];
                            }
                            if (count($valores_meses))
                                $valores_anno[] = [
                                    'anno' => $a['anno'],
                                    'valores_meses' => $valores_meses
                                ];
                        }
                        if (count($valores_anno))
                            $listado[] = [
                                'planta' => $p,
                                'valores_anno' => $valores_anno
                            ];
                    }
                }
            } else {
                return '<div class="alert alert-warning text-center">Los Meses "desde" y "hasta" son incorrectos.</div>';
            }
        }
        //dd($listado, $listado_annos);

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Tabla General');

        if ($view == 'semanal_flores') {
            $this->get_hoja_semanal_flores($sheet, $listado_annos, $columnas, $listado, $request->criterio);
        }
        if ($view == 'semanal_clientes') {
            $this->get_hoja_semanal_clientes($sheet, $listado_annos, $columnas, $listado, $request->criterio);
        }
        if ($view == 'mensual_flores') {
            $this->get_hoja_mensual_flores($sheet, $listado_annos, $columnas, $listado, $request->criterio);
        }
        if ($view == 'mensual_clientes') {
            $this->get_hoja_mensual_clientes($sheet, $listado_annos, $columnas, $listado, $request->criterio);
        }
    }

    public function get_hoja_semanal_flores($sheet, $listado_annos, $columnas, $listado, $criterio)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_semanas = [];
            foreach ($a['semanas'] as $sem) {
                $totales_semanas[] = [
                    'suma' => 0,
                    'positivos' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $totales_annos[] = $totales_semanas;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $positivos_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    if ($sem['valor'] > 0) {
                        $positivos_anno_item++;
                        $totales_annos[$pos_a][$pos_sem]['positivos']++;
                    }
                    $col++;
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($sem['valor'], 2));
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno_item, 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            $positivos_anno = 0;
            foreach ($t as $val) {
                if ($criterio != 'P') {
                    $total_anno += $val['suma'];
                    if ($val['suma'] > 0) {
                        $positivos_anno++;
                    }
                } else {
                    $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                    $total_anno += $r;
                    if ($r > 0) {
                        $positivos_anno++;
                    }
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($val['suma'], 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0);
            }
            $col++;
            if ($criterio != 'P')
                if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno, 2));
            else
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_semanal_clientes($sheet, $listado_annos, $columnas, $listado, $criterio)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Clientes / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_semanas = [];
            foreach ($a['semanas'] as $sem) {
                $totales_semanas[] = [
                    'suma' => 0,
                    'positivos' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem->codigo);
            }
            $totales_annos[] = $totales_semanas;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['cliente']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $positivos_anno_item = 0;
                foreach ($a['valores_semanas'] as $pos_sem => $sem) {
                    $total_anno_item += $sem['valor'];
                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                    if ($sem['valor'] > 0) {
                        $positivos_anno_item++;
                        $totales_annos[$pos_a][$pos_sem]['positivos']++;
                    }
                    $col++;
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $sem['valor']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($sem['valor'], 2));
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno_item, 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            $positivos_anno = 0;
            foreach ($t as $val) {
                if ($criterio != 'P') {
                    $total_anno += $val['suma'];
                    if ($val['suma'] > 0) {
                        $positivos_anno++;
                    }
                } else {
                    $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                    $total_anno += $r;
                    if ($r > 0) {
                        $positivos_anno++;
                    }
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($val['suma'], 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0);
            }
            $col++;
            if ($criterio != 'P')
                if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno, 2));
            else
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_mensual_flores($sheet, $listado_annos, $columnas, $listado, $criterio)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Flores / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_meses = [];
            foreach ($a['meses'] as $mes) {
                $totales_meses[] = [
                    'suma' => 0,
                    'positivos' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
            }
            $totales_annos[] = $totales_meses;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['planta']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $positivos_anno_item = 0;
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $total_anno_item += $mes['valor'];
                    $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                    if ($mes['valor'] > 0) {
                        $positivos_anno_item++;
                        $totales_annos[$pos_a][$pos_mes]['positivos']++;
                    }
                    $col++;
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mes['valor']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($mes['valor'], 2));
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno_item, 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            $positivos_anno = 0;
            foreach ($t as $val) {
                if ($criterio != 'P') {
                    $total_anno += $val['suma'];
                    if ($val['suma'] > 0) {
                        $positivos_anno++;
                    }
                } else {
                    $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                    $total_anno += $r;
                    if ($r > 0) {
                        $positivos_anno++;
                    }
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($val['suma'], 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0);
            }
            $col++;
            if ($criterio != 'P')
                if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno, 2));
            else
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }

    public function get_hoja_mensual_clientes($sheet, $listado_annos, $columnas, $listado, $criterio)
    {
        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Clientes / Años');
        $totales_annos = [];
        foreach ($listado_annos as $a) {
            $totales_meses = [];
            foreach ($a['meses'] as $mes) {
                $totales_meses[] = [
                    'suma' => 0,
                    'positivos' => 0,
                ];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, getMeses()[$mes - 1]);
            }
            $totales_annos[] = $totales_meses;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTAL ' . $a['anno']);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        foreach ($listado as $item) {
            $row++;
            $col = 0;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $item['cliente']->nombre);
            setBgToCeldaExcel($sheet, $columnas[$col] . $row, '00b388');
            setColorTextToCeldaExcel($sheet, $columnas[$col] . $row, 'ffffff');
            foreach ($item['valores_anno'] as $pos_a => $a) {
                $total_anno_item = 0;
                $positivos_anno_item = 0;
                foreach ($a['valores_meses'] as $pos_mes => $mes) {
                    $total_anno_item += $mes['valor'];
                    $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                    if ($mes['valor'] > 0) {
                        $positivos_anno_item++;
                        $totales_annos[$pos_a][$pos_mes]['positivos']++;
                    }
                    $col++;
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $mes['valor']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($mes['valor'], 2));
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno_item);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno_item, 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0);
            }
        }

        $row++;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'TOTALES');
        foreach ($totales_annos as $t) {
            $total_anno = 0;
            $positivos_anno = 0;
            foreach ($t as $val) {
                if ($criterio != 'P') {
                    $total_anno += $val['suma'];
                    if ($val['suma'] > 0) {
                        $positivos_anno++;
                    }
                } else {
                    $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                    $total_anno += $r;
                    if ($r > 0) {
                        $positivos_anno++;
                    }
                }
                $col++;
                if ($criterio != 'P')
                    if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['suma']);
                    else
                        setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($val['suma'], 2));
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0);
            }
            $col++;
            if ($criterio != 'P')
                if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_anno);
                else
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, round($total_anno, 2));
            else
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0);
        }
        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
