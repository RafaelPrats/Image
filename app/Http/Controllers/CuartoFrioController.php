<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Modelos\ClasificacionBlanco;
use yura\Modelos\ClasificacionRamo;
use yura\Modelos\Empaque;
use yura\Modelos\InventarioFrio;
use yura\Modelos\Planta;
use yura\Modelos\Submenu;
use yura\Modelos\UnidadMedida;
use yura\Modelos\Variedad;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Validator;

class CuartoFrioController extends Controller
{
    public function inicio(Request $request)
    {
        $plantas = DB::table('inventario_frio as i')
            ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
            ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
            ->select(
                'v.id_planta',
                'p.nombre'
            )->distinct()
            ->where('i.estado', '=', 1)
            ->where('i.disponibles', '>', 0)
            ->where('i.disponibilidad', '=', 1)
            ->where('i.basura', '=', 0)
            ->orderBy('p.nombre')
            ->get();
        $presentaciones = DB::table('inventario_frio as i')
            ->join('empaque as e', 'e.id_empaque', '=', 'i.id_empaque_p')
            ->select(
                'i.id_empaque_p',
                'e.nombre'
            )->distinct()
            ->where('i.estado', '=', 1)
            ->where('i.disponibles', '>', 0)
            ->where('i.disponibilidad', '=', 1)
            ->where('i.basura', '=', 0)
            ->orderBy('e.nombre')
            ->get();

        return view('adminlte.gestion.postcocecha.cuarto_frio.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'plantas' => $plantas,
            'presentaciones' => $presentaciones,
        ]);
    }

    public function listar_inventarios(Request $request)
    {
        $inventarios = [];
        if ($request->tipo == 'AR' || $request->tipo == 'AT') {
            $query = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as var_nombre',
                    'p.nombre as pta_nombre',
                    'i.longitud_ramo',
                )->distinct()
                ->where('i.estado', '=', 1)
                ->where('i.disponibles', '>', 0)
                ->where('i.disponibilidad', '=', 1)
                ->where('i.basura', '=', 0);
            if ($request->planta != 'T') {
                $query = $query->where('v.id_planta', $request->planta);
            }
            if ($request->variedad != 'T') {
                $query = $query->where('i.id_variedad', $request->variedad);
            }
            if ($request->longitud != '') {
                $query = $query->where('i.longitud_ramo', $request->longitud);
            }
            $query = $query->orderBy('p.orden')
                ->orderBy('i.longitud_ramo', 'desc')
                ->orderBy('v.orden')
                ->orderBy('i.fecha_ingreso')
                ->get();

            $inventarios = [];

            foreach ($query as $q) {
                $dias = [];
                $disponibles = 0;
                for ($i = 0; $i <= 4; $i++) {
                    $ingresos = DB::table('inventario_frio as i')
                        ->select(
                            DB::raw('sum(i.disponibles) as ramos'),
                            DB::raw('sum(i.disponibles * i.tallos_x_ramo) as tallos')
                        )
                        ->where('i.estado', '=', 1)
                        ->where('i.disponibles', '>', 0)
                        ->where('i.disponibilidad', '=', 1)
                        ->where('i.basura', '=', 0)
                        ->where('i.id_variedad', '=', $q->id_variedad)
                        ->where('i.longitud_ramo', '=', $q->longitud_ramo);
                    if ($i == 4)
                        $ingresos = $ingresos->where('i.fecha_ingreso', '<=', opDiasFecha('-', $i, hoy()));
                    else
                        $ingresos = $ingresos->where('i.fecha_ingreso', '=', opDiasFecha('-', $i, hoy()));
                    if ($request->tipo == 'AR')
                        $ingresos = $ingresos->get()[0]->ramos;
                    if ($request->tipo == 'AT')
                        $ingresos = $ingresos->get()[0]->tallos;

                    $disponibles += $ingresos;
                    array_push($dias, [
                        'dia' => $i,
                        'cantidad' => $ingresos,
                    ]);
                }
                array_push($inventarios, [
                    'variedad' => $q,
                    'longitud_ramo' => $q->longitud_ramo,
                    'disponibles' => $disponibles,
                    'dias' => $dias
                ]);
            }

            return view('adminlte.gestion.postcocecha.cuarto_frio.partials.listado_acumulado_color', [
                'inventarios' => $inventarios,
                'tipo' => $request->tipo
            ]);
        } else {
            $query = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'i.id_empaque_p',
                    'i.tallos_x_ramo',
                    'i.longitud_ramo',
                    'i.id_unidad_medida'
                )->distinct()
                ->where('i.estado', '=', 1)
                ->where('i.disponibles', '>', 0)
                ->where('i.disponibilidad', '=', 1)
                ->where('i.basura', '=', 0);
            if ($request->planta != 'T') {
                $query = $query->where('v.id_planta', $request->planta);
            }
            if ($request->variedad != 'T') {
                $query = $query->where('i.id_variedad', $request->variedad);
            }
            if ($request->presentacion != 'T') {
                $query = $query->where('i.id_empaque_p', $request->presentacion);
            }
            if ($request->longitud != '') {
                $query = $query->where('i.longitud_ramo', $request->longitud);
            }
            $query = $query->orderBy('p.orden')
                ->orderBy('v.orden')
                ->orderBy('i.fecha_ingreso')
                ->get();

            foreach ($query as $q) {
                $dias = [];
                $disponibles = 0;
                for ($i = 0; $i <= 4; $i++) {
                    $ingresos = DB::table('inventario_frio as i')
                        ->select(
                            DB::raw('sum(i.disponibles) as ramos'),
                            DB::raw('sum(i.disponibles * i.tallos_x_ramo) as tallos')
                        )
                        ->where('i.estado', '=', 1)
                        ->where('i.disponibles', '>', 0)
                        ->where('i.disponibilidad', '=', 1)
                        ->where('i.basura', '=', 0)
                        ->where('i.id_variedad', '=', $q->id_variedad)
                        ->where('i.id_empaque_p', '=', $q->id_empaque_p)
                        ->where('i.tallos_x_ramo', '=', $q->tallos_x_ramo)
                        ->where('i.longitud_ramo', '=', $q->longitud_ramo)
                        ->where('i.id_unidad_medida', '=', $q->id_unidad_medida);
                    if ($i == 4)
                        $ingresos = $ingresos->where('i.fecha_ingreso', '<=', opDiasFecha('-', $i, hoy()));
                    else
                        $ingresos = $ingresos->where('i.fecha_ingreso', '=', opDiasFecha('-', $i, hoy()));
                    if ($request->tipo == 'R')
                        $ingresos = $ingresos->get()[0]->ramos;
                    if ($request->tipo == 'T')
                        $ingresos = $ingresos->get()[0]->tallos;

                    $disponibles += $ingresos;
                    array_push($dias, [
                        'dia' => $i,
                        'cantidad' => $ingresos,
                    ]);
                }
                array_push($inventarios, [
                    'variedad' => Variedad::find($q->id_variedad),
                    'presentacion' => Empaque::find($q->id_empaque_p),
                    'tallos_x_ramo' => $q->tallos_x_ramo,
                    'longitud_ramo' => $q->longitud_ramo,
                    'unidad_medida' => UnidadMedida::find($q->id_unidad_medida),
                    'disponibles' => $disponibles,
                    'dias' => $dias
                ]);
            }

            return view('adminlte.gestion.postcocecha.cuarto_frio.partials.listado', [
                'inventarios' => $inventarios,
                'tipo' => $request->tipo
            ]);
        }
    }

    public function add_new_inventarios(Request $request)
    {
        $plantas = Planta::where('estado', 1)
            ->orderBy('orden')
            ->get();
        $presentaciones = Empaque::where('estado', 1)
            ->where('tipo', 'P')
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postcocecha.cuarto_frio.forms.add_new_inventarios', [
            'plantas' => $plantas,
            'presentaciones' => $presentaciones,
        ]);
    }

    public function add_inventario(Request $request)
    {
        foreach ($request->add as $data) {
            $fecha = opDiasFecha('-', $data['dia'], date('Y-m-d'));
            /* ============= BLANCO ============= */
            $blanco = ClasificacionBlanco::where('fecha_ingreso', $fecha)
                ->get()
                ->first();
            if ($blanco == '') {
                $last_blanco = DB::select('select distinct *
                from clasificacion_blanco
                order by id_clasificacion_blanco desc limit 1')[0];
                $blanco = new ClasificacionBlanco();
                $blanco->fecha_ingreso = $fecha;
                $blanco->hora_inicio = $last_blanco->hora_inicio;
                $blanco->personal = $last_blanco->personal;

                $blanco->save();
                $blanco->id_clasificacion_blanco = DB::table('clasificacion_blanco')
                    ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                    ->get()[0]->id;
            }
            /* ============= INVENTARIO ============= */
            $inventario = new InventarioFrio();
            $inventario->id_clasificacion_blanco = $blanco->id_clasificacion_blanco;
            $inventario->id_variedad = $request->data['variedad'];
            $inventario->id_empaque_p = $request->data['presentacion'];
            $inventario->tallos_x_ramo = $request->data['tallos_x_ramo'];
            $inventario->longitud_ramo = $request->data['longitud_ramo'];
            $inventario->id_unidad_medida = $request->data['unidad_medida'];
            $inventario->fecha_ingreso = $fecha;
            $inventario->cantidad = $data['valor'];
            $inventario->disponibles = $data['valor'];

            $texto = getVariedad($request->data['variedad'])->siglas;
            $texto .= ' ' . explode('|', getEmpaque($request->data['presentacion'])->nombre)[0];
            $texto .= $request->data['tallos_x_ramo'] != '' ? ', ' . $request->data['tallos_x_ramo'] : '';
            $texto .= $request->data['longitud_ramo'] != '' ? ' ' . $request->data['longitud_ramo'] . getUnidadMedida($request->data['unidad_medida'])->siglas : '';

            $inventario->descripcion = $texto;
            $inventario->save();
            $inventario->id_inventario_frio = DB::table('inventario_frio')
                ->select(DB::raw('max(id_inventario_frio) as id'))
                ->get()[0]->id;
            bitacora('inventario_frio', $inventario->id_inventario_frio, 'I', 'INGRESAR_INVENTARIO_FRIO');
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se han ingresado satisfactoriamente los ramos a inventario</div>',
        ];
    }

    public function edit_inventario(Request $request)
    {
        $fecha = opDiasFecha('-', $request->edit['dia'], date('Y-m-d'));
        $valor = $request->edit['valor'];
        if ($request->edit['valor'] == 0) {
            $models = InventarioFrio::where('id_variedad', $request->data['variedad'])
                ->where('id_empaque_p', $request->data['presentacion'])
                ->where('tallos_x_ramo', $request->data['tallos_x_ramo'])
                ->where('longitud_ramo', $request->data['longitud_ramo'])
                ->where('id_unidad_medida', $request->data['unidad_medida']);
            if ($request->edit['dia'] == 4)
                $models = $models->where('fecha_ingreso', '<=', $fecha);
            else
                $models = $models->where('fecha_ingreso', $fecha);
            $models = $models->where('estado', '=', 1)
                ->where('disponibles', '>', 0)
                ->where('disponibilidad', '=', 1)
                ->where('basura', '=', 0)
                ->orderBy('id_inventario_frio', 'desc')
                ->get();
            foreach ($models as $m) {
                $m->disponibles = 0;
                $m->disponibilidad = 0;
                $m->save();
                bitacora('inventario_frio', $m->id_inventario_frio, 'U', 'EDITAR_INVENTARIO_FRIO');
            }
        } else if ($request->edit['origen'] > $request->edit['valor']) {   // modificar el ultimo registro
            $meta = $request->edit['origen'] - $valor;
            $models = InventarioFrio::where('id_variedad', $request->data['variedad'])
                ->where('id_empaque_p', $request->data['presentacion'])
                ->where('tallos_x_ramo', $request->data['tallos_x_ramo'])
                ->where('longitud_ramo', $request->data['longitud_ramo'])
                ->where('id_unidad_medida', $request->data['unidad_medida'])
                ->where('fecha_ingreso', $fecha)
                ->where('estado', '=', 1)
                ->where('disponibles', '>', 0)
                ->where('disponibilidad', '=', 1)
                ->where('basura', '=', 0)
                ->orderBy('id_inventario_frio', 'desc')
                ->get();
            foreach ($models as $model) {
                if ($meta > 0) {
                    if ($model->disponibles >= $meta) {
                        $model->disponibles = $model->disponibles - $meta;
                        $meta = 0;
                    } else {
                        $meta -= $model->disponibles;
                        $model->disponibles = 0;
                    }

                    if ($model->disponibles == 0)
                        $model->disponibilidad = 0;

                    $model->save();
                    bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'EDITAR_INVENTARIO_FRIO');
                } else
                    break;
            }
        } else {    // agregar un nuevo registro sin mesa
            $meta = $valor - $request->edit['origen'];
            /* ============= BLANCO ============= */
            $blanco = ClasificacionBlanco::where('fecha_ingreso', $fecha)
                ->get()
                ->first();
            $last_blanco = DB::select('select distinct *
                from clasificacion_blanco
                order by id_clasificacion_blanco desc limit 1')[0];
            if ($blanco == '') {
                $blanco = new ClasificacionBlanco();
                $blanco->fecha_ingreso = $fecha;
                $blanco->hora_inicio = $last_blanco->hora_inicio;
                $blanco->personal = $last_blanco->personal;

                $blanco->save();
                $blanco->id_clasificacion_blanco = DB::table('clasificacion_blanco')
                    ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                    ->get()[0]->id;
            }
            /* ============= INVENTARIO ============= */
            $inventario = new InventarioFrio();
            $inventario->id_clasificacion_blanco = $blanco->id_clasificacion_blanco;
            $inventario->id_variedad = $request->data['variedad'];
            $inventario->id_empaque_p = $request->data['presentacion'];
            $inventario->tallos_x_ramo = $request->data['tallos_x_ramo'];
            $inventario->longitud_ramo = $request->data['longitud_ramo'];
            $inventario->id_unidad_medida = $request->data['unidad_medida'];
            $inventario->fecha_ingreso = $fecha;
            $inventario->cantidad = $meta;
            $inventario->disponibles = $meta;

            $texto = getVariedad($request->data['variedad'])->siglas;
            $texto .= ' ' . explode('|', getEmpaque($request->data['presentacion'])->nombre)[0];
            $texto .= $request->data['tallos_x_ramo'] != '' ? ', ' . $request->data['tallos_x_ramo'] : '';
            $texto .= $request->data['longitud_ramo'] != '' ? ' ' . $request->data['longitud_ramo'] . getUnidadMedida($request->data['unidad_medida'])->siglas : '';

            $inventario->descripcion = $texto;
            $inventario->save();
        }

        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se han modificado satisfactoriamente los ramos en inventario de cuarto frio</div>',
        ];
    }

    public function botar_inventario(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        try {
            DB::beginTransaction();

            foreach (json_decode($request->data) as $data) {
                $fecha = opDiasFecha('-', $data->dia, date('Y-m-d'));
                $valor = $data->valor;
                $origen = $data->origen;
                if ($origen >= $valor) {
                    /* MODIFICAR EL INVENTARIO ACTUAL */
                    $meta = $valor;
                    $models = InventarioFrio::where('id_variedad', $request->variedad)
                        ->where('id_empaque_p', $request->presentacion)
                        ->where('tallos_x_ramo', $request->tallos_x_ramo)
                        ->where('longitud_ramo', $request->longitud_ramo)
                        ->where('fecha_ingreso', $fecha)
                        ->where('estado', '=', 1)
                        ->where('disponibles', '>', 0)
                        ->where('disponibilidad', '=', 1)
                        ->where('basura', '=', 0)
                        ->orderBy('id_inventario_frio', 'desc')
                        ->get();
                    foreach ($models as $model) {
                        if ($meta > 0) {
                            if ($model->disponibles >= $meta) {
                                $model->disponibles = $model->disponibles - $meta;
                                $meta = 0;
                            } else {
                                $meta -= $model->disponibles;
                                $model->disponibles = 0;
                            }

                            if ($model->disponibles == 0)
                                $model->disponibilidad = 0;

                            $model->save();
                            bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'EDITAR_INVENTARIO_FRIO');
                        } else
                            break;
                    }
                    // agregar un nuevo registro de basura
                    /* ============= BLANCO ============= */
                    $blanco = ClasificacionBlanco::where('fecha_ingreso', $fecha)
                        ->get()
                        ->first();
                    if ($blanco == '') {
                        $last_blanco = DB::select('select distinct *
                            from clasificacion_blanco
                            order by id_clasificacion_blanco desc limit 1')[0];
                        $blanco = new ClasificacionBlanco();
                        $blanco->fecha_ingreso = $fecha;
                        $blanco->hora_inicio = $last_blanco->hora_inicio;
                        $blanco->personal = $last_blanco->personal;

                        $blanco->save();
                        $blanco->id_clasificacion_blanco = DB::table('clasificacion_blanco')
                            ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                            ->get()[0]->id;
                    }
                    /* ============= INVENTARIO ============= */
                    $basura_inv = new InventarioFrio();
                    $basura_inv->id_clasificacion_blanco = $blanco->id_clasificacion_blanco;
                    $basura_inv->id_variedad = $request->variedad;
                    $basura_inv->id_empaque_p = $request->presentacion;
                    $basura_inv->tallos_x_ramo = $request->tallos_x_ramo;
                    $basura_inv->longitud_ramo = $request->longitud_ramo;
                    $basura_inv->id_unidad_medida = 1;
                    $basura_inv->fecha_ingreso = $fecha;
                    $basura_inv->cantidad = $valor;
                    $basura_inv->disponibles = 0;
                    $basura_inv->disponibilidad = 0;
                    $basura_inv->basura = 1;

                    $texto = getVariedad($request->variedad)->siglas;
                    $texto .= ' ' . explode('|', getEmpaque($request->presentacion)->nombre)[0];
                    $texto .= $request->tallos_x_ramo != '' ? ', ' . $request->tallos_x_ramo : '';
                    $texto .= $request->longitud_ramo != '' ? ' ' . $request->longitud_ramo . getUnidadMedida(1)->siglas : '';

                    $basura_inv->descripcion = $texto;
                    $basura_inv->save();

                    bitacora('inventario_frio', $basura_inv->id_inventario_frio, 'I', 'BOTAR_INVENTARIO_FRIO');
                }
            }
            $success = true;
            $msg = 'Se han botado satisfactoriamente los ramos del cuarto frio';

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
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function delete_dia(Request $request)
    {
        $fecha = opDiasFecha('-', $request->dia, date('Y-m-d'));
        $list = InventarioFrio::join('variedad as v', 'inventario_frio.id_variedad', '=', 'v.id_variedad')
            ->select('inventario_frio.*')
            ->distinct()
            ->where('inventario_frio.estado', 1)
            ->where('inventario_frio.disponibilidad', 1)
            ->where('inventario_frio.basura', 0)
            ->where('inventario_frio.disponibles', '>', 0);
        if ($request->planta != 'T')
            $list = $list->where('v.id_planta', $request->planta);
        if ($request->variedad != 'T')
            $list = $list->where('inventario_frio.id_variedad', $request->variedad);
        if ($request->presentacion != 'T')
            $list = $list->where('inventario_frio.id_empaque_p', $request->presentacion);
        if ($request->dia == 4) {
            $list = $list->where('inventario_frio.fecha_ingreso', '<=', $fecha);
        } else
            $list = $list->where('inventario_frio.fecha_ingreso', $fecha);
        $list = $list->get();

        foreach ($list as $inv) {

            $updateInventarioFrio = InventarioFrio::find($inv->id_inventario_frio);

            $basura = $inv->disponibles;
            $updateInventarioFrio->disponibles = 0;
            $updateInventarioFrio->disponibilidad = 0;

            $updateInventarioFrio->save();
            bitacora('inventario_frio', $inv->id_inventario_frio, 'U', 'BOTAR_TODO_EL_DIA_INVENTARIO_FRIO');

            $basura_inv = new InventarioFrio();
            $basura_inv->basura = 1;
            $basura_inv->fecha_ingreso = $fecha;
            $basura_inv->cantidad = $basura;
            $basura_inv->disponibles = 0;
            $basura_inv->disponibilidad = 0;
            $basura_inv->id_clasificacion_blanco = $inv->id_clasificacion_blanco;
            $basura_inv->id_variedad = $inv->id_variedad;
            $basura_inv->id_empaque_p = $inv->id_empaque_p;
            $basura_inv->tallos_x_ramo = $inv->tallos_x_ramo;
            $basura_inv->longitud_ramo = $inv->longitud_ramo;
            $basura_inv->id_unidad_medida = $inv->id_unidad_medida;
            $basura_inv->descripcion = $inv->descripcion;

            $basura_inv->save();
            $basura_inv->id_inventario_frio = DB::table('inventario_frio')
                ->select(DB::raw('max(id_inventario_frio) as id'))
                ->get()[0]->id;
            bitacora('inventario_frio', $basura_inv->id_inventario_frio, 'I', 'BOTAR_TODO_EL_DIA_INVENTARIO_FRIO');
        }
        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se han eliminado satisfactoriamente los ramos del inventario</div>',
        ];
    }

    public function save_dia(Request $request)
    {
        $fecha = opDiasFecha('-', $request->data['dia'], date('Y-m-d'));

        $models = InventarioFrio::where('disponibilidad', 1)
            ->where('estado', 1)
            ->where('basura', 0)
            ->where('disponibles', '>', 0)
            ->where('id_variedad', $request->data['variedad'])
            ->where('tallos_x_ramo', $request->data['tallos_x_ramo'])
            ->where('longitud_ramo', $request->data['longitud_ramo'])
            ->where('id_empaque_p', $request->data['presentacion'])
            ->where('id_unidad_medida', $request->data['unidad_medida'])
            ->get();

        if ($request->data['dia'] == 4) {
            $models = $models->where('fecha_ingreso', '<=', $fecha);
        } else
            $models = $models->where('fecha_ingreso', $fecha);

        $meta = $request->data['editar'];

        foreach ($models as $pos => $model) {
            $invFrio = InventarioFrio::find($model->id_inventario_frio);
            if ($meta > 0) {
                if ($model->disponibles >= $meta) {
                    $invFrio->disponibles = $model->disponibles - $meta;
                    $meta = 0;
                } else {
                    $meta -= $model->disponibles;
                    $invFrio->disponibles = 0;
                }

                if ($model->disponibles == 0)
                    $invFrio->disponibilidad = 0;

                $invFrio->save();
                bitacora('inventario_frio', $model->id_inventario_frio, 'U', 'GUARDAR_EL_DIA_INVENTARIO_FRIO');
            } else
                break;
        }

        /* ============== INVENTARIOS NUEVOS ============= */
        if ($request->has('arreglo')) {
            foreach ($request->arreglo as $item) {
                $inv = new InventarioFrio();
                $inv->disponibilidad = 1;
                $inv->basura = 0;
                $inv->fecha_ingreso = $fecha;
                $inv->id_variedad = $item['inventario']['variedad'];
                $inv->tallos_x_ramo = $item['inventario']['tallos_x_ramo'];
                $inv->longitud_ramo = $item['inventario']['longitud_ramo'];
                $inv->id_empaque_p = $item['inventario']['presentacion'];
                $inv->id_unidad_medida = $item['inventario']['unidad_medida'];
                $inv->cantidad = $item['inventario']['add'];
                $inv->disponibles = $item['inventario']['add'];

                $texto = getVariedad($item['inventario']['variedad'])->siglas;
                $texto .= ' ' . explode('|', getEmpaque($item['inventario']['presentacion'])->nombre)[0];
                $texto .= $item['inventario']['tallos_x_ramo'] != '' ? ', ' . $item['inventario']['tallos_x_ramo'] : '';
                $texto .= $item['inventario']['longitud_ramo'] != '' ? ' ' . $item['inventario']['longitud_ramo'] . getUnidadMedida($item['inventario']['unidad_medida'])->siglas : '';

                $inv->descripcion = $texto;
                $inv->save();
                $inv->id_inventario_frio = DB::table('inventario_frio')
                    ->select(DB::raw('max(id_inventario_frio) as id'))
                    ->get()[0]->id;
                bitacora('inventario_frio', $inv->id_inventario_frio, 'I', 'GUARDAR_EL_DIA_INVENTARIO_FRIO');
            }
        }
        /* ============== BASURA ============= */
        if ($request->basura > 0) {
            $inv = new InventarioFrio();
            $inv->disponibilidad = 0;
            $inv->basura = 1;
            $inv->fecha_ingreso = $fecha;
            $inv->id_variedad = $request->data['variedad'];
            $inv->tallos_x_ramo = $request->data['tallos_x_ramo'];
            $inv->longitud_ramo = $request->data['longitud_ramo'];
            $inv->id_empaque_p = $request->data['presentacion'];
            $inv->id_unidad_medida = $request->data['unidad_medida'];
            $inv->cantidad = $request->basura;
            $inv->disponibles = 0;

            $texto = getVariedad($request->data['variedad'])->siglas;
            $texto .= ' ' . explode('|', getEmpaque($request->data['presentacion'])->nombre)[0];
            $texto .= $request->data['tallos_x_ramo'] != '' ? ', ' . $request->data['tallos_x_ramo'] : '';
            $texto .= $request->data['longitud_ramo'] != '' ? ' ' . $request->data['longitud_ramo'] . getUnidadMedida($request->data['unidad_medida'])->siglas : '';

            $inv->descripcion = $texto;
            $inv->save();
            $inv->id_inventario_frio = DB::table('inventario_frio')
                ->select(DB::raw('max(id_inventario_frio) as id'))
                ->get()[0]->id;
            bitacora('inventario_frio', $inv->id_inventario_frio, 'I', 'GUARDAR_EL_DIA_INVENTARIO_FRIO');
        }

        return [
            'success' => true,
            'mensaje' => '<div class="alert alert-success text-center">Se han modificado satisfactoriamente los ramos en inventario de cuarto frio</div>',
        ];
    }

    public function store_new_inventario(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha' => 'required',
            'variedad' => 'required',
            'presentacion' => 'required',
            'tallos_x_ramo' => 'required',
            'longitud' => 'required',
            'cantidad' => 'required',
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'variedad.required' => 'La variedad es obligatoria',
            'presentacion.required' => 'La presentacion es obligatoria',
            'longitud.required' => 'La longitud es obligatoria',
            'cantidad.required' => 'La cantidad es obligatoria',
            'tallos_x_ramo.required' => 'Los tallos por ramo son obligatorios',
        ]);
        if (!$valida->fails()) {
            /* ============= BLANCO ============= */
            $blanco = ClasificacionBlanco::All()->where('fecha_ingreso', $request->fecha)->first();
            if ($blanco == '') {
                $blanco = new ClasificacionBlanco();
                $blanco->fecha_ingreso = $request->fecha;
                $blanco->hora_inicio = ClasificacionBlanco::All()->last()->hora_inicio;
                $blanco->personal = ClasificacionBlanco::All()->last()->personal;

                $blanco->save();
                $blanco->id_clasificacion_blanco = DB::table('clasificacion_blanco')
                    ->select(DB::raw('max(id_clasificacion_blanco) as id'))
                    ->get()[0]->id;
                bitacora('clasificacion_blanco', $blanco->id_clasificacion_blanco, 'I', 'GUARDAR_NUEVO_INVENTARIO_FRIO');
            }

            $model = new InventarioFrio();
            $model->id_variedad = $request->variedad;
            $model->id_empaque_p = $request->presentacion;
            $model->tallos_x_ramo = $request->tallos_x_ramo;
            $model->longitud_ramo = $request->longitud;
            $model->cantidad = $request->cantidad;
            $model->disponibles = $request->cantidad;
            $model->fecha_ingreso = $request->fecha;
            $model->id_unidad_medida = 1;
            $model->disponibilidad = 1;
            $model->basura = 0;
            $model->fecha_registro = date('Y-m-d H:i:s');

            $texto = getVariedad($request->variedad)->siglas;
            $texto .= ' ' . explode('|', getEmpaque($request->presentacion)->nombre)[0];
            $texto .= $request->tallos_x_ramo != '' ? ', ' . $request->tallos_x_ramo : '';
            $texto .= $request->longitud != '' ? ' ' . $request->longitud . getUnidadMedida(1)->siglas : '';

            $model->descripcion = $texto;

            if ($model->save()) {
                $model->id_inventario_frio = DB::table('inventario_frio')
                    ->select(DB::raw('max(id_inventario_frio) as id'))
                    ->get()[0]->id;
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha guardado un nuevo inventario frio satisfactoriamente</p>'
                    . '</div>';
                bitacora('inventario_frio', $model->id_inventario_frio, 'I', 'GUARDAR_NUEVO_INVENTARIO_FRIO');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function exportar_inventarios(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_inventarios($spread, $request);

        $fileName = "Cuarto_Frio.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_inventarios($spread, $request)
    {
        $inventarios = [];
        if ($request->tipo == 'AR' || $request->tipo == 'AT') {
            $query = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'v.nombre as var_nombre',
                    'p.nombre as pta_nombre',
                    'i.longitud_ramo',
                )->distinct()
                ->where('i.estado', '=', 1)
                ->where('i.disponibles', '>', 0)
                ->where('i.disponibilidad', '=', 1)
                ->where('i.basura', '=', 0);
            if ($request->planta != 'T') {
                $query = $query->where('v.id_planta', $request->planta);
            }
            if ($request->variedad != 'T') {
                $query = $query->where('i.id_variedad', $request->variedad);
            }
            if ($request->longitud != '') {
                $query = $query->where('i.longitud_ramo', $request->longitud);
            }
            $query = $query->orderBy('p.orden')
                ->orderBy('i.longitud_ramo', 'desc')
                ->orderBy('v.orden')
                ->orderBy('i.fecha_ingreso')
                ->get();

            foreach ($query as $q) {
                $dias = [];
                $disponibles = 0;
                for ($i = 0; $i <= 4; $i++) {
                    $ingresos = DB::table('inventario_frio as i')
                        ->select(
                            DB::raw('sum(i.disponibles) as ramos'),
                            DB::raw('sum(i.disponibles * i.tallos_x_ramo) as tallos')
                        )
                        ->where('i.estado', '=', 1)
                        ->where('i.disponibles', '>', 0)
                        ->where('i.disponibilidad', '=', 1)
                        ->where('i.basura', '=', 0)
                        ->where('i.id_variedad', '=', $q->id_variedad)
                        ->where('i.longitud_ramo', '=', $q->longitud_ramo);
                    if ($i == 4)
                        $ingresos = $ingresos->where('i.fecha_ingreso', '<=', opDiasFecha('-', $i, hoy()));
                    else
                        $ingresos = $ingresos->where('i.fecha_ingreso', '=', opDiasFecha('-', $i, hoy()));
                    if ($request->tipo == 'AR')
                        $ingresos = $ingresos->get()[0]->ramos;
                    if ($request->tipo == 'AT')
                        $ingresos = $ingresos->get()[0]->tallos;

                    $disponibles += $ingresos;
                    array_push($dias, [
                        'dia' => $i,
                        'cantidad' => $ingresos,
                    ]);
                }
                array_push($inventarios, [
                    'variedad' => $q,
                    'longitud_ramo' => $q->longitud_ramo,
                    'disponibles' => $disponibles,
                    'dias' => $dias
                ]);
            }
        } else {
            $query = DB::table('inventario_frio as i')
                ->join('variedad as v', 'v.id_variedad', '=', 'i.id_variedad')
                ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                ->select(
                    'i.id_variedad',
                    'i.id_empaque_p',
                    'i.tallos_x_ramo',
                    'i.longitud_ramo',
                    'i.id_unidad_medida'
                )->distinct()
                ->where('i.estado', '=', 1)
                ->where('i.disponibles', '>', 0)
                ->where('i.disponibilidad', '=', 1)
                ->where('i.basura', '=', 0);
            if ($request->planta != 'T') {
                $query = $query->where('v.id_planta', $request->planta);
            }
            if ($request->variedad != 'T') {
                $query = $query->where('i.id_variedad', $request->variedad);
            }
            if ($request->presentacion != 'T') {
                $query = $query->where('i.id_empaque_p', $request->presentacion);
            }
            $query = $query->orderBy('p.orden')
                ->orderBy('v.orden')
                ->orderBy('i.fecha_ingreso')
                ->get();

            foreach ($query as $q) {
                $dias = [];
                $disponibles = 0;
                for ($i = 0; $i <= 4; $i++) {
                    $ingresos = DB::table('inventario_frio as i')
                        ->select(
                            DB::raw('sum(i.disponibles) as ramos'),
                            DB::raw('sum(i.disponibles * i.tallos_x_ramo) as tallos')
                        )
                        ->where('i.estado', '=', 1)
                        ->where('i.disponibles', '>', 0)
                        ->where('i.disponibilidad', '=', 1)
                        ->where('i.basura', '=', 0)
                        ->where('i.id_variedad', '=', $q->id_variedad)
                        ->where('i.id_empaque_p', '=', $q->id_empaque_p)
                        ->where('i.tallos_x_ramo', '=', $q->tallos_x_ramo)
                        ->where('i.longitud_ramo', '=', $q->longitud_ramo)
                        ->where('i.id_unidad_medida', '=', $q->id_unidad_medida);
                    if ($i == 4)
                        $ingresos = $ingresos->where('i.fecha_ingreso', '<=', opDiasFecha('-', $i, hoy()));
                    else
                        $ingresos = $ingresos->where('i.fecha_ingreso', '=', opDiasFecha('-', $i, hoy()));
                    if ($request->tipo == 'R')
                        $ingresos = $ingresos->get()[0]->ramos;
                    if ($request->tipo == 'T')
                        $ingresos = $ingresos->get()[0]->tallos;

                    $disponibles += $ingresos;
                    array_push($dias, [
                        'dia' => $i,
                        'cantidad' => $ingresos,
                    ]);
                }
                array_push($inventarios, [
                    'variedad' => Variedad::find($q->id_variedad),
                    'presentacion' => Empaque::find($q->id_empaque_p),
                    'tallos_x_ramo' => $q->tallos_x_ramo,
                    'longitud_ramo' => $q->longitud_ramo,
                    'unidad_medida' => UnidadMedida::find($q->id_unidad_medida),
                    'disponibles' => $disponibles,
                    'dias' => $dias
                ]);
            }
        }

        $columnas = getColumnasExcel();
        $sheet = $spread->getActiveSheet();
        $sheet->setTitle('Cuarto Frio');

        $row = 1;
        $col = 0;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Variedad');
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Color');
        if (!in_array($request->tipo, ['AR', 'AT'])) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Presentacion');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Tallos x Ramo');
        }
        $col++;
        setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Longitud');
        $totales_dia = [];
        for ($i = 0; $i <= 4; $i++) {
            $fecha = opDiasFecha('-', $i, date('Y-m-d'));
            $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
            $totales_dia[] = 0;
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $i == 4 ? $i . '...' : $i);
        }
        if (!in_array($request->tipo, ['AR', 'AT'])) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Ramos');
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total Tallos');
        } else {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, 'Total');
        }

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        $total_ramos = 0;
        $total_tallos = 0;
        $total = 0;
        foreach ($inventarios as $pos_inv => $inv) {
            $row++;
            $col = 0;
            if (!in_array($request->tipo, ['AR', 'AT'])) {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['variedad']->planta->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['variedad']->nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, explode('|', $inv['presentacion']->nombre)[0]);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['tallos_x_ramo']);
            } else {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['variedad']->pta_nombre);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['variedad']->var_nombre);
            }
            $col++;
            if (!in_array($request->tipo, ['AR', 'AT'])) {
                if ($inv['longitud_ramo'] != '')
                    setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['longitud_ramo'] . '' . $inv['unidad_medida']->siglas);
            } else {
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['longitud_ramo'] . 'cm');
            }

            foreach ($inv['dias'] as $pos_dia => $dia) {
                $totales_dia[$pos_dia] += $dia['cantidad'];
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $dia['cantidad'] != '' ? $dia['cantidad'] : '-');
            }
            if (!in_array($request->tipo, ['AR', 'AT'])) {
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $request->tipo == 'R' ? $inv['disponibles'] : $inv['disponibles'] / $inv['tallos_x_ramo']);
                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $request->tipo == 'R' ? $inv['disponibles'] * $inv['tallos_x_ramo'] : $inv['disponibles']);
                $total_ramos += $request->tipo == 'R' ? $inv['disponibles'] : $inv['disponibles'] / $inv['tallos_x_ramo'];
                $total_tallos += $request->tipo == 'R' ? $inv['disponibles'] * $inv['tallos_x_ramo'] : $inv['disponibles'];
            } else {

                $col++;
                setValueToCeldaExcel($sheet, $columnas[$col] . $row, $inv['disponibles']);
                $total += $inv['disponibles'];
            }
        }

        $row++;
        if (!in_array($request->tipo, ['AR', 'AT']))
            $col = 4;
        else
            $col = 2;
        for ($i = 0; $i <= 4; $i++) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $totales_dia[$i]);
        }
        if (!in_array($request->tipo, ['AR', 'AT'])) {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_ramos);
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total_tallos);
        } else {
            $col++;
            setValueToCeldaExcel($sheet, $columnas[$col] . $row, $total);
        }

        setBgToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, '00b388');
        setColorTextToCeldaExcel($sheet, $columnas[0] . $row . ':' . $columnas[$col] . $row, 'ffffff');

        setTextCenterToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);
        setBorderToCeldaExcel($sheet, 'A1:' . $columnas[$col] . $row);

        for ($i = 0; $i <= $col; $i++)
            $sheet->getColumnDimension($columnas[$i])->setAutoSize(true);
    }
}
