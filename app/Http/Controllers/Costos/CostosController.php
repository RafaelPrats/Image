<?php

namespace yura\Http\Controllers\Costos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Jobs\ImportarCostos;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\Area;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\OtrosGastos;
use yura\Modelos\ResumenCostosSemanal;
use yura\Modelos\Submenu;
use Validator;
use PHPExcel;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use PHPExcel_Worksheet;
use yura\Modelos\Producto;
use Storage as Almacenamiento;

class CostosController extends Controller
{
    public function gestion_insumo(Request $request)
    {
        return view('adminlte.gestion.costos.insumo.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All()->sortBy('nombre'),
            'actividades' => Actividad::All()->sortBy('nombre'),
            'productos' => Producto::All()->sortBy('nombre'),
        ]);
    }

    public function store_area(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:50|unique:area',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new Area();
            $area = Area::orderBy('id_area','desc')->first();
            $model->id_area = isset($area->id_area) ? $area->id_area + 1 : 1;
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 50);
            $model->fecha_registro = date('Y-m-d H:i:s');

            if ($model->save()) {
                $model = Area::All()->last();
                $success = true;
                bitacora('area', $model->id_area, 'I', 'Inserción satisfactoria de una nueva area');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_area(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:50',
            'id_area' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_area.required' => 'El área es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Area::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 50))
                    ->where('id_area', '!=', $request->id_area)) == 0) {
                $model = Area::find($request->id_area);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 50);

                if ($model->save()) {
                    $success = true;
                    bitacora('area', $model->id_area, 'U', 'Actualización satisfactoria de una area');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El área "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function store_actividad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:50',
            'area' => 'required',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'area.required' => 'El área es obligatoria',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new Actividad();
            $actividad = Actividad::orderBy('id_actividad','desc')->first();
            $model->id_actividad = isset($actividad->id_actividad) ? $actividad->id_actividad + 1 : 1;
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 50);
            $model->id_area = $request->area;
            $model->fecha_registro = date('Y-m-d H:i:s');

            if ($model->save()) {
                $model = Actividad::All()->last();
                $success = true;
                bitacora('actividad', $model->id_actividad, 'I', 'Inserción satisfactoria de una nueva actividad');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_actividad(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:50',
            'id_actividad' => 'required|',
            'area' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_actividad.required' => 'La actividad es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
            'area.required' => 'El área es obligatoria',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = Actividad::find($request->id_actividad);
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 50);
            $model->id_area = $request->area;

            if ($model->save()) {
                $success = true;
                bitacora('actividad', $model->id_actividad, 'U', 'Actualización satisfactoria de una actividad');
            } else {
                $success = false;
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

    public function importar_actividad(Request $request)
    {
        return view('adminlte.gestion.costos.insumo.forms.importar_actividad', [
            'areas' => Area::All(),
        ]);
    }

    public function importar_file_actividad(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_actividad' => 'required',
            'id_area_actividad' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = IOFactory::load($request->file_actividad);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['A'])), 50);
                        if (count(Actividad::All()->where('nombre', $nombre)) == 0) {
                            $model = new Actividad();
                            $actividad = Actividad::orderBy('id_actividad','desc')->first();
                            $model->id_actividad = isset($actividad->id_actividad) ? $actividad->id_actividad + 1 : 1;
                            $model->nombre = $nombre;
                            $model->id_area = $request->id_area_actividad;
                            $model->fecha_registro = date('Y-m-d');

                            $model->save();
                            $model = Actividad::All()->last();
                            bitacora('actividad', $model->id_actividad, 'I', 'Inserción satisfactoria de una nueva actividad');
                            $msg .= '<li class="bg-green">Se ha importado la actividad: "' . $nombre . '."</li>';
                        }
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function store_producto(Request $request)
    {
        $request->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:producto',
            'precio' => 'required|max:11',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'precio.required' => 'El precio es obligatorio',
            'precio.max' => 'El precio es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Producto::All()
                    ->where('nombre', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('estado', 1)) == 0) {
                $model = new Producto();
                $producto = Producto::orderBy('id_producto', 'desc')->first();
                $model->id_producto = isset($producto->id_producto) ? $producto->id_producto + 1 : 1;
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->precio = $request->precio;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = Producto::All()->last();
                    $success = true;
                    bitacora('producto', $model->id_producto, 'I', 'Inserción satisfactoria de un nuevo producto');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-danger text-center">El nombre ya existe</div>';
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'precio' => 'required|max:11',
            'id_producto' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'precio.required' => 'El precio es obligatorio',
            'id_producto.required' => 'El producto es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
            'precio.max' => 'El precio es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(Producto::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_producto', '!=', $request->id_producto)) == 0) {
                $model = Producto::find($request->id_producto);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
                $model->precio = $request->precio;

                if ($model->save()) {
                    $success = true;
                    bitacora('producto', $model->id_producto, 'U', 'Actualización satisfactoria de un producto');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> El producto "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function importar_producto(Request $request)
    {
        return view('adminlte.gestion.costos.insumo.forms.importar_producto', [
        ]);
    }

    public function vincular_actividad_producto(Request $request)
    {
        $actividad = Actividad::find($request->id);
        $productos_vinc = [];
        foreach ($actividad->productos->where('estado', 1) as $p) {
            array_push($productos_vinc, $p->id_producto);
        }

        return view('adminlte.gestion.costos.insumo.forms.vincular_actividad_producto', [
            'actividad' => $actividad,
            'productos_vinc' => $productos_vinc,
            'productos' => Producto::All()->where('estado', 1)->sortBy('nombre'),
        ]);
    }

    public function store_actividad_producto(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'producto' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'producto.required' => 'El producto es obligatorio',
        ]);
        $msg = '';
        $estado = 1;
        if (!$valida->fails()) {
            $model = ActividadProducto::All()
                ->where('id_actividad', $request->actividad)
                ->where('id_producto', $request->producto)
                ->first();
            if ($model == '') {
                $model = new ActividadProducto();
                $actividadProducto = ActividadProducto::orderBy('id_actividad_producto', 'desc')->first();
                $model->id_actividad_producto = isset($actividadProducto->id_actividad_producto) ? $actividadProducto->id_actividad_producto + 1 : 1;
                $model->id_actividad = $request->actividad;
                $model->id_producto = $request->producto;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = ActividadProducto::All()->last();
                    $success = true;
                    bitacora('actividad_producto', $model->actividad_producto, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_producto');
                } else {
                    $success = false;
                }
            } else {
                $model->estado = $model->estado == 1 ? 0 : 1;
                $estado = $model->estado;
                $success = true;

                $model->save();
                bitacora('producto', $model->id_producto, 'U', 'Modificacion satisfactoria del estado de un producto');
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
            'success' => $success,
            'mensaje' => $msg,
            'estado' => $estado,
        ];
    }

    public function importar_file_producto(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_producto' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = IOFactory::load($request->file_producto);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['A'])), 250);
                        $existe = Producto::All()->where('nombre', $nombre)->first();
                        if ($existe == '') {
                            $model = new Producto();
                            $producto = Producto::orderBy('id_producto', 'desc')->first();
                            $model->id_producto = isset($producto->id_producto) ? $producto->id_producto + 1 : 1;
                            $model->nombre = $nombre;
                            $model->fecha_registro = date('Y-m-d');
                            $msg .= '<li class="bg-green">Se ha importado el producto: "' . $nombre . '."</li>';
                            $model->save();
                        }
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function importar_file_act_producto(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_act_producto' => 'required',
        ]);
        $msg = '';
        $success = true;
        $array_ids_prod = [];
        if (!$valida->fails()) {

            $document = IOFactory::load($request->file_act_producto);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['B'])), 250);
                        $producto = Producto::All()->where('nombre', $nombre)->first();

                        if ($producto != '') {
                            $model = ActividadProducto::All()
                                ->where('id_actividad', $request->id_actividad)
                                ->where('id_producto', $producto->id_producto)
                                ->first();
                            if ($model == '') {
                                $model = new ActividadProducto();
                                $model->id_actividad = $request->id_actividad;
                                $model->id_producto = $producto->id_producto;
                                $model->fecha_registro = date('Y-m-d H:i:s');

                                if ($model->save()) {
                                    $model = ActividadProducto::All()->last();
                                    $success = true;
                                    bitacora('actividad_producto', $model->actividad_producto, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_producto');
                                } else {
                                    $success = false;
                                }
                            } else {
                                $model->estado = 1;
                                $success = true;

                                $model->save();
                                bitacora('producto', $model->id_producto, 'U', 'Modificación satisfactoria del estado de un producto');
                            }
                            array_push($array_ids_prod, $producto->id_producto);
                            $msg .= '<li class="bg-green">Se ha vinculado el producto: "' . $nombre . '."</li>';
                        }
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
            'ids' => $array_ids_prod,
        ];
    }

    public function delete_actividad(Request $request)
    {
        $model = Actividad::find($request->id_actividad);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('actividad', $model->id_actividad, 'U', 'Modificacion satisfactoria del estado de una actividad');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function delete_producto(Request $request)
    {
        $model = Producto::find($request->id_producto);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('producto', $model->id_producto, 'U', 'Modificacion satisfactoria del estado de un producto');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    public function buscar_insumosByActividad(Request $request)
    {
        $act_insumos = [];
        $actividad = Actividad::find($request->actividad);
        if ($actividad != '')
            $act_insumos = ActividadProducto::join('producto as p', 'p.id_producto', 'actividad_producto.id_producto')
                ->where('actividad_producto.id_actividad', $request->actividad)
                ->where('p.estado', 1)
                ->orderBy('p.nombre')
                ->get();
        return view('adminlte.gestion.costos.insumo.partials.select_edit_insumo', [
            'act_insumos' => $act_insumos,
            'form' => $request->form,
        ]);
    }

    public function buscar_moByActividad(Request $request)
    {
        $act_mo = [];
        $actividad = Actividad::find($request->actividad);
        if ($actividad != '')
            $act_mo = $actividad->manos_obra;
        return view('adminlte.gestion.costos.mano_obra.partials.select_edit_mo', [
            'act_mo' => $act_mo,
            'form' => $request->form,
        ]);
    }

    public function buscar_valorByActividadInsumoSemana(Request $request)
    {
        $valor = 0;
        $existe = false;
        $act_ins = ActividadProducto::All()
            ->where('estado', 1)
            ->where('id_actividad', $request->actividad)
            ->where('id_producto', $request->insumo)
            ->first();
        if ($act_ins != '') {
            $costo_sem = CostosSemana::All()
                ->where('id_actividad_producto', $act_ins->id_actividad_producto)
                ->where('codigo_semana', $request->semana)
                ->first();
            if ($costo_sem != '') {
                $valor = $costo_sem->valor;
                $existe = true;
            }
        }
        return [
            'valor' => $valor,
            'existe' => $existe,
        ];
    }

    public function buscar_valorByActividadMOSemana(Request $request)
    {
        $valor = 0;
        $existe = false;
        $act_mo = ActividadManoObra::All()
            ->where('estado', 1)
            ->where('id_actividad', $request->actividad)
            ->where('id_mano_obra', $request->mo)
            ->first();
        if ($act_mo != '') {
            $costo_sem = CostosSemanaManoObra::All()
                ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                ->where('codigo_semana', $request->semana)
                ->first();
            if ($costo_sem != '') {
                $valor = $costo_sem->valor;
                $existe = true;
            }
        }
        return [
            'valor' => $valor,
            'existe' => $existe,
        ];
    }

    public function save_costoInsumo(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'semana' => 'required',
            'valor' => 'required',
            'insumo' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'semana.required' => 'La semana es obligatoria',
            'insumo.required' => 'El insumo es obligatorio',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $act_ins = ActividadProducto::All()
                ->where('estado', 1)
                ->where('id_actividad', $request->actividad)
                ->where('id_producto', $request->insumo)
                ->first();
            if ($act_ins != '') {
                $costo_sem = CostosSemana::All()
                    ->where('id_actividad_producto', $act_ins->id_actividad_producto)
                    ->where('codigo_semana', $request->semana)
                    ->first();
                $new = false;
                if ($costo_sem == '') {
                    $costo_sem = new CostosSemana();
                    $costoSemana = CostosSemana::orderBy('id_costos_semana','desc')->first();
                    $costo_sem->id_costos_semana= isset($costoSemana->id_costos_semana) ? $costoSemana->id_costos_semana+1 : 1;
                    $costo_sem->id_actividad_producto = $act_ins->id_actividad_producto;
                    $costo_sem->codigo_semana = $request->semana;
                    $costo_sem->valor = $costo_sem->cantidad = 0;
                    $new = true;
                }
                $costo_sem->valor = $request->valor;

                if ($costo_sem->save()) {
                    $success = true;
                    if ($new)
                        $id = CostosSemana::All()->last()->id_costos_semana;
                    else
                        $id = $costo_sem->id_costos_semana;
                    bitacora('costos_semana', $id, 'I', 'Inserción satisfactoria de un costo por semana');
                } else {
                    $success = false;
                }
            } else
                $success = false;
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
            'success' => $success
        ];
    }

    public function save_costoMO(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'semana' => 'required',
            'valor' => 'required',
            'mo' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'semana.required' => 'La semana es obligatoria',
            'mo.required' => 'La mano de obra es obligatoria',
            'valor.required' => 'El valor es obligatorio',
        ]);
        if (!$valida->fails()) {
            $act_mo = ActividadManoObra::All()
                ->where('estado', 1)
                ->where('id_actividad', $request->actividad)
                ->where('id_mano_obra', $request->mo)
                ->first();
            if ($act_mo != '') {
                $costo_sem = CostosSemanaManoObra::All()
                    ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                    ->where('codigo_semana', $request->semana)
                    ->first();
                $new = false;
                if ($costo_sem == '') {
                    $costo_sem = new CostosSemanaManoObra();
                    $costoSemana = CostosSemanaManoObra::orderBy('id_costos_semana_mano_obra','desc')->first();
                    $costo_sem->id_costos_semana_mano_obra = isset($costoSemana->id_costos_semana_mano_obra) ? $costoSemana->id_costos_semana_mano_obra+1 : 1;
                    $costo_sem->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                    $costo_sem->codigo_semana = $request->semana;
                    $costo_sem->valor = $costo_sem->cantidad = 0;
                    $new = true;
                }
                $costo_sem->valor = $request->valor;

                if ($costo_sem->save()) {
                    $success = true;
                    if ($new)
                        $id = CostosSemanaManoObra::All()->last()->id_costos_semana_mano_obra;
                    else
                        $id = $costo_sem->id_costos_semana_mano_obra;
                    bitacora('costos_semana_mano_obra', $id, 'I', 'Inserción satisfactoria de un costo por semana');
                } else {
                    $success = false;
                }
            } else
                $success = false;
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
            'success' => $success
        ];
    }

    /* ==================================== IMPORTAR ===================================== */
    public function costos_importar(Request $request)
    {
        return view('adminlte.gestion.costos.costos_importar', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function importar_file_costos(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_costos' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {

            $archivo = $request->file_costos;
            $extension = $archivo->getClientOriginalExtension();
            $nombre_archivo = "costos_" . $request->concepto_importar . "." . $extension;
            $r1 = Almacenamiento::disk('pdf_loads')->put($nombre_archivo, \File::get($archivo));

            $url = public_path('storage\pdf_loads\\' . $nombre_archivo);

        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function importar_file_costos_details(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_costos_details' => 'required',
        ]);
        $msg = '<div class="alert alert-info text-center">Se ha importado el archivo, en menos de una hora se reflejarán los datos en el sistema</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $archivo = $request->file_costos_details;
                $extension = $archivo->getClientOriginalExtension();
                $nombre_archivo = "costos_" . $request->concepto_importar_details . "_" . $request->sobreescribir_importar_details . "_details." . $extension;
                $r1 = Almacenamiento::disk('pdf_loads')->put($nombre_archivo, \File::get($archivo));

                //$url = public_path('storage/pdf_loads/' . $nombre_archivo);

                //$document = \PHPExcel_IOFactory::load($url);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'DOMDocument::loadHTML(): Invalid char in CDATA') !== false)
                    $mensaje_error = 'Problema con el archivo excel';
                else
                    $mensaje_error = $e->getMessage();
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        '<p>¡Ha ocurrido un problema al subir el archivo, contacte al administrador del sistema!</p>' .
                        '<legend style="font-size: 0.9em; color: white; margin-bottom: 2px">mensaje de error</legend>' .
                        $mensaje_error .
                        '</div>',
                    'success' => false
                ];
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    /* =================================== MANO OBRA ======================================= */
    public function gestion_mano_obra(Request $request)
    {
        return view('adminlte.gestion.costos.mano_obra.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All()->sortBy('nombre'),
            'actividades' => Actividad::All()->sortBy('nombre'),
            'manos_obra' => ManoObra::All()->sortBy('nombre'),
        ]);
    }

    public function store_mano_obra(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250|unique:mano_obra',
        ], [
            'nombre.unique' => 'El nombre ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            $model = new ManoObra();
            $manoObra = ManoObra::oderBy('id_mano_obra', 'desc')->first();
            $model->id_mano_obra = isset($manoObra) ? $manoObra->id_mano_obra + 1 : 1;
            $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);
            $model->fecha_registro = date('Y-m-d H:i:s');

            if ($model->save()) {
                $model = ManoObra::All()->last();
                $success = true;
                bitacora('mano_obra', $model->id_mano_obra, 'I', 'Inserción satisfactoria de una nueva mano de obra');
            } else {
                $success = false;
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
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function importar_mano_obra(Request $request)
    {
        return view('adminlte.gestion.costos.mano_obra.forms.importar_producto', [
        ]);
    }

    public function importar_file_mano_obra(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_mano_obra' => 'required',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {

            $document = IOFactory::load($request->file_mano_obra);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];

            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['A'])), 250);
                        if (count(ManoObra::All()->where('nombre', $nombre)) == 0) {
                            $model = new ManoObra();
                            $manoObra = ManoObra::oderBy('id_mano_obra', 'desc')->first();
                            $model->id_mano_obra = isset($manoObra) ? $manoObra->id_mano_obra + 1 : 1;
                            $model->nombre = $nombre;
                            $model->fecha_registro = date('Y-m-d');

                            $model->save();
                            $model = ManoObra::All()->last();
                            bitacora('mano_obra', $model->id_mano_obra, 'I', 'Inserción satisfactoria de una nueva mano de obra');
                            $msg .= '<li class="bg-green">Se ha importado la mano de obra: "' . $nombre . '."</li>';
                        }
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
        ];
    }

    public function update_mano_obra(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'nombre' => 'required|max:250',
            'id_mano_obra' => 'required|',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'id_mano_obra.required' => 'La mano de obra es obligatoria',
            'nombre.max' => 'El nombre es muy grande',
        ]);
        $msg = '';
        if (!$valida->fails()) {
            if (count(ManoObra::All()->where('nombre', '=', str_limit(mb_strtoupper(espacios($request->nombre)), 250))
                    ->where('id_mano_obra', '!=', $request->id_mano_obra)) == 0) {
                $model = ManoObra::find($request->id_mano_obra);
                $model->nombre = str_limit(mb_strtoupper(espacios($request->nombre)), 250);

                if ($model->save()) {
                    $success = true;
                    bitacora('mano_obra', $model->id_mano_obra, 'U', 'Actualización satisfactoria de una mano de obra');
                } else {
                    $success = false;
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> La mano de obra "' . espacios($request->nombre) . '" ya se encuentra en el sistema</p>'
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

    public function vincular_actividad_mano_obra(Request $request)
    {
        $actividad = Actividad::find($request->id);
        $manos_obra_vinc = [];
        foreach ($actividad->manos_obra->where('estado', 1) as $p) {
            array_push($manos_obra_vinc, $p->id_mano_obra);
        }

        return view('adminlte.gestion.costos.mano_obra.forms.vincular_actividad_mano_obra', [
            'actividad' => $actividad,
            'manos_obra_vinc' => $manos_obra_vinc,
            'manos_obra' => ManoObra::All()->where('estado', 1)->sortBy('nombre'),
        ]);
    }

    public function store_actividad_mano_obra(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'actividad' => 'required',
            'mano_obra' => 'required',
        ], [
            'actividad.required' => 'La actividad es obligatoria',
            'mano_obra.required' => 'La mano de obra es obligatorio',
        ]);
        $msg = '';
        $estado = 1;
        if (!$valida->fails()) {
            $model = ActividadManoObra::All()
                ->where('id_actividad', $request->actividad)
                ->where('id_mano_obra', $request->mano_obra)
                ->first();
            if ($model == '') {
                $model = new ActividadManoObra();
                $model->id_actividad = $request->actividad;
                $model->id_mano_obra = $request->mano_obra;
                $model->fecha_registro = date('Y-m-d H:i:s');

                if ($model->save()) {
                    $model = ActividadManoObra::All()->last();
                    $success = true;
                    bitacora('actividad_mano_obra', $model->actividad_mano_obra, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_mano_obra');
                } else {
                    $success = false;
                }
            } else {
                $model->estado = $model->estado == 1 ? 0 : 1;
                $estado = $model->estado;
                $success = true;

                $model->save();
                bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificacion satisfactoria del estado de una mano de obra');
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
            'success' => $success,
            'mensaje' => $msg,
            'estado' => $estado,
        ];
    }

    public function importar_file_act_mano_obra(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_act_mano_obra' => 'required',
        ]);
        $msg = '';
        $success = true;
        $array_ids_mo = [];
        if (!$valida->fails()) {

            $document = IOFactory::load($request->file_act_mano_obra);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $titles = $activeSheetData[1];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($pos_row > 1) {
                    if ($row['A'] != '') {
                        $nombre = str_limit(mb_strtoupper(espacios($row['B'])), 250);
                        $mano_obra = ManoObra::All()->where('nombre', $nombre)->first();

                        if ($mano_obra != '') {
                            $model = ActividadManoObra::All()
                                ->where('id_actividad', $request->id_actividad)
                                ->where('id_mano_obra', $mano_obra->id_mano_obra)
                                ->first();
                            if ($model == '') {
                                $model = new ActividadManoObra();
                                $model->id_actividad = $request->id_actividad;
                                $model->id_mano_obra = $mano_obra->id_mano_obra;
                                $model->fecha_registro = date('Y-m-d H:i:s');

                                if ($model->save()) {
                                    $model = ActividadManoObra::All()->last();
                                    $success = true;
                                    bitacora('actividad_mano_obra', $model->actividad_mano_obra, 'I', 'Inserción satisfactoria de un nuevo vínculo actividad_mano_obra');
                                } else {
                                    $success = false;
                                }
                            } else {
                                $model->estado = 1;
                                $success = true;

                                $model->save();
                                bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificación satisfactoria del estado de una mano de obra');
                            }
                            array_push($array_ids_mo, $mano_obra->id_mano_obra);
                            $msg .= '<li class="bg-green">Se ha vinculado la mano de obra: "' . $nombre . '."</li>';
                        }
                    }
                }
            }
        } else {
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $success = false;
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success,
            'ids' => $array_ids_mo,
        ];
    }

    public function delete_mano_obra(Request $request)
    {
        $model = ManoObra::find($request->id_mano_obra);
        $model->estado = $model->estado == 1 ? 0 : 1;
        $model->save();
        bitacora('mano_obra', $model->id_mano_obra, 'U', 'Modificacion satisfactoria del estado de una mano de obra');

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    /* ----------------------------------- REPORTE -------------------------------------------- */
    public function reporte_mano_obra(Request $request)
    {
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        return view('adminlte.gestion.costos.mano_obra.reporte.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All(),
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde
        ]);
    }

    public function listar_reporte_mano_obra(Request $request)
    {
        $semanas = DB::table('costos_semana_mano_obra')
            ->select('codigo_semana')->distinct()
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana_mano_obra as c')
            ->select('c.id_actividad_mano_obra', 'mo.nombre')->distinct()
            ->join('actividad_mano_obra as ap', 'c.id_actividad_mano_obra', '=', 'ap.id_actividad_mano_obra')
            ->join('mano_obra as mo', 'mo.id_mano_obra', '=', 'ap.id_mano_obra');
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->orderBy('mo.nombre')
            ->get();

        $list_ids = [];
        foreach ($ids as $item)
            array_push($list_ids, $item->id_actividad_mano_obra);

        $productos = DB::table('costos_semana_mano_obra as c')
            ->join('actividad_mano_obra as ap', 'ap.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
            ->join('mano_obra as p', 'p.id_mano_obra', '=', 'ap.id_mano_obra')
            ->select('p.nombre', DB::raw('sum(c.valor) as valor'))
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->whereIn('c.id_actividad_mano_obra', $list_ids)
            ->groupBy('p.nombre')
            ->orderBy('p.nombre')
            ->get();

        $matriz = [];
        foreach ($productos as $p) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('costos_semana_mano_obra as c')
                    ->join('actividad_mano_obra as ap', 'ap.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                    ->join('mano_obra as p', 'p.id_mano_obra', '=', 'ap.id_mano_obra')
                    ->select(DB::raw('sum(c.valor) as valor'))
                    ->where('c.codigo_semana', $sem->codigo_semana)
                    ->whereIn('c.id_actividad_mano_obra', $list_ids)
                    ->where('p.nombre', $p->nombre)
                    ->get()[0]->valor;
                $valores[] = $val;
            }
            $matriz[] = [
                'producto' => $p,
                'valores' => $valores,
            ];
        }

        $totales = DB::table('costos_semana_mano_obra')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_mano_obra', $list_ids)
            ->groupBy('codigo_semana')
            ->get();

        return view('adminlte.gestion.costos.mano_obra.reporte.partials.listado', [
            'semanas' => $semanas,
            'area' => $area,
            'actividad' => $actividad,
            'criterio' => $request->criterio,
            'matriz' => $matriz,
            'totales' => $totales,
        ]);
    }

    public function reporte_insumos(Request $request)
    {
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        return view('adminlte.gestion.costos.insumo.reporte.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'areas' => Area::All(),
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde
        ]);
    }

    public function listar_reporte_insumos(Request $request)
    {
        //dd($request->all());
        $semanas = DB::table('costos_semana')
            ->select('codigo_semana')->distinct()
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->orderBy('codigo_semana')
            ->get();
        $area = Area::find($request->area);
        $actividad = Actividad::find($request->actividad);

        $ids = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'c.id_actividad_producto', '=', 'ap.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('c.id_actividad_producto')->distinct();
        if ($actividad != '')   // una actividad en especifico
            $ids = $ids
                ->where('ap.id_actividad', $actividad->id_actividad);
        else if ($area != '') {
            $ids = $ids
                ->join('actividad as a', 'ap.id_actividad', '=', 'a.id_actividad')
                ->where('a.id_area', $area->id_area);
        }
        if ($request->criterio == 'V')  // dinero
            $ids = $ids->where('c.valor', '>', 0);
        else    // cantidad
            $ids = $ids->where('c.cantidad', '>', 0);
        $ids = $ids
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->orderBy('p.nombre')
            ->get();

        $list_ids = [];
        foreach ($ids as $item)
            array_push($list_ids, $item->id_actividad_producto);

        $productos = DB::table('costos_semana as c')
            ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
            ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
            ->select('p.nombre', DB::raw('sum(c.valor) as valor'))
            ->where('c.codigo_semana', '>=', $request->desde)
            ->where('c.codigo_semana', '<=', $request->hasta)
            ->whereIn('c.id_actividad_producto', $list_ids)
            ->groupBy('p.nombre')
            ->orderBy('p.nombre')
            ->get();

        $matriz = [];
        foreach ($productos as $p) {
            $valores = [];
            foreach ($semanas as $sem) {
                $val = DB::table('costos_semana as c')
                    ->join('actividad_producto as ap', 'ap.id_actividad_producto', '=', 'c.id_actividad_producto')
                    ->join('producto as p', 'p.id_producto', '=', 'ap.id_producto')
                    ->select(DB::raw('sum(c.valor) as valor'))
                    ->where('c.codigo_semana', $sem->codigo_semana)
                    ->whereIn('c.id_actividad_producto', $list_ids)
                    ->where('p.nombre', $p->nombre)
                    ->get()[0]->valor;
                $valores[] = $val;
            }
            $matriz[] = [
                'producto' => $p,
                'valores' => $valores,
            ];
        }

        $totales = DB::table('costos_semana')
            ->select(DB::raw('sum(valor) as cant'), 'codigo_semana as semana')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->whereIn('id_actividad_producto', $list_ids)
            ->groupBy('codigo_semana')
            ->get();

        return view('adminlte.gestion.costos.insumo.reporte.partials.listado', [
            'semanas' => $semanas,
            'area' => $area,
            'actividad' => $actividad,
            'criterio' => $request->criterio,
            'matriz' => $matriz,
            'totales' => $totales,
        ]);
    }

    public function costos_generales(Request $request)
    {
        $semana_actual = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));
        $semana_desde = getSemanaByDate(opDiasFecha('-', 42, date('Y-m-d')));
        $variedades = getVariedades();
        return view('adminlte.gestion.costos.generales.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'semana_desde' => $semana_desde,
            'variedades' => $variedades,
        ]);
    }

    public function listar_reporte_general(Request $request)
    {
        $semanas = DB::table('resumen_semanal_total')
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->get();
        $array_semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();
        $areas_totales = DB::table('resumen_area_semanal')
            ->select(DB::raw('sum(area) as area'))
            ->where('codigo_semana', '>=', $request->desde)
            ->where('codigo_semana', '<=', $request->hasta)
            ->groupBy('codigo_semana')
            ->get();
        if ($request->variedad == 'T') {
            $costo_x_planta = DB::table('resumen_propagacion')
                ->select('costo_x_planta', 'semana')->distinct()
                ->where('semana', '>=', $request->desde)
                ->where('semana', '<=', $request->hasta)
                ->where('costo_x_planta', '>', 0)
                ->orderBy('semana')
                ->get();
            $resumen_cosecha = DB::table('resumen_semana_cosecha')
                ->select(DB::raw('sum(tallos_clasificados) as tallos_clasificados'), 'codigo_semana')
                ->where('codigo_semana', '>=', $request->desde)
                ->where('codigo_semana', '<=', $request->hasta)
                ->groupBy('codigo_semana')
                ->orderBy('codigo_semana')
                ->get();
            $indicadores_4_semanas = DB::table('indicadores_4_semanas')
                ->where('semana', '>=', $request->desde)
                ->where('semana', '<=', $request->hasta)
                ->orderBy('semana')
                ->get();
            $requerimientos = DB::table('resumen_propagacion as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select(DB::raw('sum(r.requerimientos) as requerimientos'), 'r.semana')
                ->where('v.estado', 1)
                ->where('r.semana', '>=', $request->desde)
                ->where('r.semana', '<=', $request->hasta)
                ->groupBy('r.semana')
                ->orderBy('r.semana')
                ->get();
            $query_areas_50_100 = DB::table('costos_semana_mano_obra as c')
                ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                ->join('area as r', 'r.id_area', '=', 'a.id_area')
                ->select('r.nombre', 'a.id_area',
                    DB::raw('sum(valor_50) as valor50'),
                    DB::raw('sum(valor_100) as valor100'))
                ->where('c.codigo_semana', '>=', $request->desde)
                ->where('c.codigo_semana', '<=', $request->hasta)
                ->groupBy('r.nombre', 'a.id_area')
                ->having(DB::raw('sum(valor_50)'), '>', 0)
                ->orHaving(DB::raw('sum(valor_100)'), '>', 0)
                ->orHaving(DB::raw('sum(valor)'), '>', 0)
                ->orderBy('r.nombre')
                ->get();
            $areas_50_100 = [];
            foreach ($query_areas_50_100 as $a) {
                $valores_50_100 = [];
                foreach ($array_semanas as $sem) {
                    $valor_50_100 = DB::table('costos_semana_mano_obra as c')
                        ->join('actividad_mano_obra as am', 'am.id_actividad_mano_obra', '=', 'c.id_actividad_mano_obra')
                        ->join('actividad as a', 'a.id_actividad', '=', 'am.id_actividad')
                        ->select(DB::raw('sum(valor_50) as valor50'),
                            DB::raw('sum(valor_100) as valor100'),
                            DB::raw('sum(cantidad) as cantidad'))
                        ->where('c.codigo_semana', $sem->codigo)
                        ->where('a.id_area', $a->id_area)
                        ->get()[0];
                    $valores_50_100[] = $valor_50_100;
                }
                $areas_50_100[] = [
                    'area' => $a->nombre,
                    'valores_50_100' => $valores_50_100,
                ];
            }

            $notas_credito = [];
            $total_costos_mo = [];
            foreach ($array_semanas as $sem) {
                $valor = 0;
                if ($sem->codigo >= 2142) {
                    $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                        ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                        ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                        ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                        ->where('d.estado', 1)
                        ->where('v.estado', 1)
                        ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                        ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                        ->get();
                    foreach ($c_unitarias as $u) {
                        $descartes = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->select(DB::raw('sum(descartes) as descartes'))
                            ->where('d.estado', 1)
                            ->where('v.estado', 1)
                            ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get()[0]->descartes;
                        $valor += $descartes * $u->precio_venta;
                    }
                }
                array_push($notas_credito, $valor);

                $query_mo = DB::table('costos_semana_mano_obra')
                    ->select(DB::raw('sum(valor) as valor'),
                        DB::raw('sum(valor_50) as valor_50'),
                        DB::raw('sum(valor_100) as valor_100'))
                    ->where('codigo_semana', $sem->codigo)
                    ->get()[0];
                array_push($total_costos_mo, $query_mo);
            }
            $datos = [
                'semanas' => $semanas,
                'areas' => $areas_totales,
                'costo_x_planta' => $costo_x_planta,
                'resumen_cosecha' => $resumen_cosecha,
                'indicadores_4_semanas' => $indicadores_4_semanas,
                'requerimientos' => $requerimientos,
                'notas_credito' => $notas_credito,
                'areas_50_100' => $areas_50_100,
                'total_costos_mo' => $total_costos_mo,
            ];
            $view = 'listado';
        } else {
            $areas = DB::table('resumen_area_semanal')
                ->select(DB::raw('sum(area) as area'))
                ->where('codigo_semana', '>=', $request->desde)
                ->where('codigo_semana', '<=', $request->hasta)
                ->where('id_variedad', $request->variedad)
                ->groupBy('codigo_semana')
                ->get();
            $requerimientos = DB::table('resumen_propagacion as r')
                ->join('variedad as v', 'v.id_variedad', '=', 'r.id_variedad')
                ->select(DB::raw('sum(r.requerimientos) as requerimientos'), 'r.semana')
                ->where('v.estado', 1)
                ->where('r.id_variedad', $request->variedad)
                ->where('r.semana', '>=', $request->desde)
                ->where('r.semana', '<=', $request->hasta)
                ->groupBy('r.semana')
                ->orderBy('r.semana')
                ->get();
            $tallos_cosechados = [];
            $tallos_clasificados = [];
            $notas_credito = [];
            $ventas = [];
            foreach ($array_semanas as $sem) {
                $q_cosechados = DB::table('desglose_recepcion as dr')
                    ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                    ->select(DB::raw('sum(cantidad_mallas * tallos_x_malla) as cantidad'))
                    ->where('dr.id_variedad', $request->variedad)
                    ->where('r.fecha_ingreso', '>=', $sem->fecha_inicial . ' 00:00:00')
                    ->where('r.fecha_ingreso', '<=', $sem->fecha_final . ' 23:59:59')
                    ->get()[0]->cantidad;
                $tallos_cosechados[] = $q_cosechados;
                $q_clasificados = DB::table('detalle_clasificacion_verde as dv')
                    ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'dv.id_clasificacion_verde')
                    ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as cantidad'))
                    ->where('dv.id_variedad', $request->variedad)
                    ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                    ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                    ->get()[0]->cantidad;
                $tallos_clasificados[] = $q_clasificados;
                $valor_ventas = 0;
                $valor_descartes = 0;
                if ($sem->codigo >= 2142) {
                    $c_unitarias = DB::table('detalle_clasificacion_verde as d')
                        ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                        ->join('clasificacion_unitaria as u', 'u.id_clasificacion_unitaria', '=', 'd.id_clasificacion_unitaria')
                        ->select('d.id_clasificacion_unitaria', 'u.precio_venta')->distinct()
                        ->where('d.estado', 1)
                        ->where('v.estado', 1)
                        ->where('d.id_variedad', $request->variedad)
                        ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                        ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                        ->get();
                    foreach ($c_unitarias as $u) {
                        $query = DB::table('detalle_clasificacion_verde as d')
                            ->join('clasificacion_verde as v', 'v.id_clasificacion_verde', '=', 'd.id_clasificacion_verde')
                            ->select(DB::raw('sum(d.descartes) as descartes'),
                                DB::raw('sum(d.cantidad_ramos * d.tallos_x_ramos) as tallos'))
                            ->where('d.estado', 1)
                            ->where('v.estado', 1)
                            ->where('d.id_variedad', $request->variedad)
                            ->where('d.id_clasificacion_unitaria', $u->id_clasificacion_unitaria)
                            ->where('v.fecha_ingreso', '>=', $sem->fecha_inicial)
                            ->where('v.fecha_ingreso', '<=', $sem->fecha_final)
                            ->get()[0];
                        $valor_descartes += $query->descartes * $u->precio_venta;
                        $valor_ventas += $query->tallos * $u->precio_venta;
                    }
                }
                $notas_credito[] = $valor_descartes;
                $ventas[] = $valor_ventas;
            }
            $datos = [
                'semanas' => $semanas,
                'areas_totales' => $areas_totales,
                'areas' => $areas,
                'tallos_cosechados' => $tallos_cosechados,
                'tallos_clasificados' => $tallos_clasificados,
                'notas_credito' => $notas_credito,
                'ventas' => $ventas,
                'requerimientos' => $requerimientos,
            ];
            $view = 'variedades.listado';
        }

        return view('adminlte.gestion.costos.generales.partials.' . $view, $datos);
    }

    public function corregir_costos_mano_obra(Request $request)
    {
        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $request->desde)
            ->where('codigo', '<=', $request->hasta)
            ->get();
        foreach ($semanas as $pos_sem => $sem) {
            $model = CostosSemanaManoObra::All()
                ->where('codigo_semana', $sem->codigo)
                ->where('id_actividad_mano_obra', $request->act_mo)
                ->first();
            if ($model == '') {
                $model = new CostosSemanaManoObra();
                $costoSemana = CostosSemanaManoObra::orderBy('id_costos_semana_mano_obra','desc')->first();
                $model->id_costos_semana_mano_obra = isset($costoSemana->id_costos_semana_mano_obra) ? $costoSemana->id_costos_semana_mano_obra+1 : 1;
                $model->id_actividad_mano_obra = $request->act_mo;
                $model->codigo_semana = $sem->codigo;
                $model->valor = 0;
                $model->cantidad = 0;
                $model->save();
            }
        }

        return [
            'success' => true,
            'mensaje' => '',
        ];
    }

    /* =================================== OTROS GASTOS ======================================= */
    public function otros_gastos(Request $request)
    {
        $area = Area::find($request->area);
        $semana_actual = getSemanaByDate(date('Y-m-d'));
        return view('adminlte.gestion.costos.mano_obra.forms.otros_gastos', [
            'area' => $area,
            'otros_gastos' => $area->otrosGastosBySemana($semana_actual->codigo),
            'semana_actual' => $semana_actual,
        ]);
    }

    public function store_otros_gastos(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'id_area' => 'required',
            'semana' => 'required',
            'gip' => 'required',
            'ga' => 'required',
        ], [
            'semana.required' => 'La semana es obligatoria',
            'id_area.required' => 'El área es obligatoria',
            'gip.required' => 'El gip es obligatoria',
            'ga.required' => 'El ga es obligatoria',
        ]);
        if (!$valida->fails()) {
            $semana_actual = getSemanaByDate(date('Y-m-d'));
            for ($i = $request->semana; $i <= $semana_actual->codigo; $i++) {
                $model = OtrosGastos::All()
                    ->where('id_area', $request->id_area)
                    ->where('codigo_semana', $i)
                    ->first();
                if ($model == '') {
                    $model = new OtrosGastos();
                    $otrosGastos = OtrosGastos::orderBy('id_otros_gastos','desc')->first();
                    $model->id_otros_gastos = isset($otrosGastos->id_otros_gastos) ? $otrosGastos->id_otros_gastos + 1 : 1;
                    $model->id_area = $request->id_area;
                    $model->codigo_semana = $request->semana;
                }
                $model->gip = $request->gip;
                $model->ga = $request->ga;

                if ($model->save()) {
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se han guardado los otros gastos satisfactoriamente</p>'
                        . '</div>';
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                    return [
                        'mensaje' => $msg,
                        'success' => $success
                    ];
                }
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

    public function buscar_otros_gastos(Request $request)
    {
        $area = Area::find($request->id_area);
        $costos = $area->otrosGastosBySemana($request->semana);
        return [
            'gip' => $costos != '' ? $costos->gip : 0,
            'ga' => $costos != '' ? $costos->ga : 0,
        ];
    }
}
