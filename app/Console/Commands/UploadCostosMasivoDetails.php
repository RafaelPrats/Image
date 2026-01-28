<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use PHPExcel_IOFactory;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\Actividad;
use yura\Modelos\ActividadManoObra;
use yura\Modelos\ActividadProducto;
use yura\Modelos\Area;
use yura\Modelos\CostosSemana;
use yura\Modelos\CostosSemanaManoObra;
use yura\Modelos\ManoObra;
use yura\Modelos\Producto;

class UploadCostosMasivoDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costos:importar_file_details {url=0} {concepto=0} {criterio=0} {sobreescribir=0}';

    /**
     * url = nombre completo del archivo
     * concepto => I, insumos _ M, mano de obra
     * criterio => V, dinero _ C, cantidad
     * sobreescribir => S, si _ I, sumar a lo anterior
     *
     * @var string
     */
    protected $description = 'Comando para subir los costos mediante un excel con los detalles por fecha';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "costos:importar_file_details" <<<<< ! >>>>>');

        $url = $this->argument('url');
        $concepto_importar = $this->argument('concepto');
        $criterio_importar = $this->argument('criterio');
        $sobreescribir = $this->argument('sobreescribir');

        $document = IOFactory::load($url);
        $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

        if ($concepto_importar == 'I')
            $this->importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir);
        else
            $this->importar_mano_obra($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir);

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "costos:importar_file_details" <<<<< * >>>>>');
    }

    public function importar_insumos($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false)
    {
        $lista = [];
        foreach ($activeSheetData as $pos_row => $row) {
            $anno = explode('/', $row['A'])[2];
            $mes = explode('/', $row['A'])[0];
            $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
            $dia = explode('/', $row['A'])[1];
            $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
            $fecha = $anno . '-' . $mes . '-' . $dia;
            $semana = getSemanaByDate($fecha);
            $producto = Producto::All()->where('nombre', 'like', espacios(mb_strtoupper($row['C'])))->first();  //query
            if ($producto != '') {
                $area = Area::All()->where('nombre', 'like', espacios(mb_strtoupper($row['B'])))->first();  //query
                if ($area != '') {
                    $actividad = Actividad::All()->where('nombre', 'like', espacios(mb_strtoupper($row['E'])))->first();  //query
                    if ($semana != '' && $actividad != '') {
                        dump('pos: ' . $pos_row . '-' . porcentaje($pos_row, count($activeSheetData), 1) . '% - area: ' . $area->nombre . ' - actividad: ' . $actividad->nombre . ' - producto: ' . $producto->nombre);
                        $existe = false;
                        for ($i = 0; $i < count($lista); $i++) {
                            if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['producto']->id_producto == $producto->id_producto) {
                                $lista[$i]['cantidad'] += $row['F'];
                                $lista[$i]['valor'] += $row['H'];
                                $existe = true;
                            }
                        }
                        if (!$existe) {
                            array_push($lista, [
                                'semana' => $semana->codigo,
                                'actividad' => $actividad,
                                'producto' => $producto,
                                'cantidad' => $row['F'],
                                'valor' => $row['H'],
                            ]);
                        }
                    }
                } else {
                    dump('*********************************************************');
                    dump('No se encontró el area: "' . espacios(mb_strtoupper($row['B'])) . '"');
                }
            } else {
                dump('*********************************************************');
                dump('No se encontró el producto: "' . espacios(mb_strtoupper($row['C'])) . '"');
            }
        }
        dump('GUARDAR información: ' . count($lista) . ' registros');
        foreach ($lista as $pos => $item) {
            dump('pos: ' . $pos . '-' . porcentaje($pos, count($lista), 1) . '% - sem: ' . $item['semana'] . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['producto']->nombre);
            $act_prod = ActividadProducto::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_producto', $item['producto']->id_producto)
                ->first();
            if ($act_prod == '') {
                $act_prod = new ActividadProducto();
                $actividadProducto = ActividadProducto::orderBy('id_actividad_producto','desc')->first();
                $act_prod->id_actividad_producto = isset($actividadProducto->id_actividad_producto) ? $actividadProducto->id_actividad_producto + 1 : 1;
                $act_prod->id_actividad = $item['actividad']->id_actividad;
                $act_prod->id_producto = $item['producto']->id_producto;
                $act_prod->save();
                $act_prod = ActividadProducto::All()->last();
            }
            $costo_semana = CostosSemana::All()
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemana();
                $costoSemana = CostosSemana::orderBy('id_costos_semana','desc')->first();
                $costo_semana->id_costos_semana = isset($costoSemana->id_costos_semana) ? $costoSemana->id_costos_semana + 1 : 1;
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_producto = $act_prod->id_actividad_producto;
                $costo_semana->valor = 0;
                $costo_semana->cantidad = 0;
            }
            if ($sobreescribir == 'S') {
                $costo_semana->valor = $item['valor'];
            } else {
                $costo_semana->valor += $item['valor'];
            }
            $costo_semana->save();
        }
    }

    public function importar_mano_obra($activeSheetData, $concepto_importar, $criterio_importar, $sobreescribir = false)
    {
        $titles = $activeSheetData[1];
        $lista = [];
        $faltantes = [];
        foreach ($activeSheetData as $pos_row => $row) {
            if ($row['A'] != '') {
                dump($row);
                $anno = explode('/', $row['H'])[2];
                $mes = explode('/', $row['H'])[0];
                $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                $dia = explode('/', $row['H'])[1];
                $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                $fecha = $anno . '-' . $mes . '-' . $dia;
                $semana = getSemanaByDate($fecha);
                if ($semana != '') {
                    $area = Area::All()->where('nombre', espacios(mb_strtoupper($row['J'])))->first();  //query
                    if ($area != '') {
                        $actividad = Actividad::All()
                            ->where('nombre', espacios(mb_strtoupper($row['C'])))
                            ->where('id_area', $area->id_area)
                            ->first();  //query
                        if ($actividad != '') {
                            $mo = ManoObra::All()
                                ->where('nombre', espacios(mb_strtoupper($row['D'])))
                                ->first();  //query
                            if ($mo != '') {
                                /* CALCULAR TOTAL */
                                $dias_lab_mes = count(bussiness_days($anno . '-' . $mes . '-01', $anno . '-' . $mes . '-' . getUltimoDiaMes($anno, $mes))[$anno . '-' . $mes]);
                                $hor_efect_mes = $dias_lab_mes * 8; // CantDiasLaborables/Mes *8(hrs/dia)
                                $cost_hr_ord = $row['E'] / $hor_efect_mes;  // Sueldo / HorasEfectivasMes
                                $cost_hr_supl = $row['E'] / 240;    // Sueldo / 240(30dias * 8)
                                $cost_dia_pers = $row['M'] * $cost_hr_ord;   // HoraNormal * CostHrsOrdinaria
                                $cost_dia_50_pers = $row['O'] * (1.5 * $cost_hr_supl); // Hrs50  * (50% de CostHrOrdinaria)
                                $cost_dia_100_pers = $row['Q'] * (2 * $cost_hr_supl); // Hrs100  * (100% de CostHrOrdinaria)
                                $cost_dia_aus_pers = $row['S'] * $cost_hr_ord;    // HrsAusentismo * CostHrsOrdinaria
                                $cost_total_dia_pers = $cost_dia_pers + $cost_dia_50_pers + $cost_dia_100_pers + $cost_dia_aus_pers;    // CostoTotalDiaPersona
                                $anno_ingreso = explode('/', $row['F'])[2];
                                $mes_ingreso = explode('/', $row['F'])[0];
                                $mes_ingreso = strlen($mes) == 1 ? '0' . $mes_ingreso : $mes_ingreso;
                                $dia_ingreso = explode('/', $row['F'])[1];
                                $dia_ingreso = strlen($dia) == 1 ? '0' . $dia_ingreso : $dia_ingreso;
                                $fecha_ingreso = $anno_ingreso . '-' . $mes_ingreso . '-' . $dia_ingreso;
                                $cant_anno_activo = difFechas($fecha, $fecha_ingreso)->days / 365;  // CantidadAnnosActivo
                                $prov_13th = $cost_total_dia_pers / 12;  //  Provision 13º
                                $prov_14th = 401.41 / 261;  //  Sueldo Basico en el pais / ...
                                $fondos_reserva = $cant_anno_activo > 1 ? $prov_13th : 0;
                                $aporte_patronal = (12.15 * $cost_total_dia_pers) / 100;   //  12.15% de CostoTotalDiaPersona
                                $total = $cost_total_dia_pers + $prov_13th + $prov_14th + $fondos_reserva + $aporte_patronal;   //  TOTAL
                                /*dump('$dias_lab_mes = ' . $dias_lab_mes);
                                dump('$hor_efect_mes = ' . $hor_efect_mes);
                                dump('$cost_hr_ord = ' . $cost_hr_ord);
                                dump('$cost_hr_supl = ' . $cost_hr_supl);
                                dump('$cost_dia_pers = ' . $cost_dia_pers);
                                dump('$cost_dia_50_pers = ' . $cost_dia_50_pers);
                                dump('$cost_dia_100_pers = ' . $cost_dia_100_pers);
                                dump('$cost_dia_aus_pers = ' . $cost_dia_aus_pers);
                                dump('$cost_total_dia_pers = ' . $cost_total_dia_pers);
                                dump('$prov_13th = ' . $prov_13th);
                                dump('$prov_14th = ' . $prov_14th);
                                dump('$fondos_reserva = ' . $fondos_reserva);
                                dump('$aporte_patronal = ' . $aporte_patronal);
                                dump('fecha_ingreso: ' . $fecha_ingreso . ' || fecha: ' . $fecha . ' = dias: ' . difFechas($fecha, $fecha_ingreso)->days . ' || annos: ' . $cant_anno_activo);*/
                                dump('pos: ' . $pos_row . '/' . count($activeSheetData) . '-' . porcentaje($pos_row, count($activeSheetData), 1) . '% - fecha: ' . $fecha . ' - sem: ' . $semana->codigo . ' - act: ' . $actividad->nombre . ' - mo: ' . $mo->nombre . ' - TOTAL: ' . $total);
                                $existe = false;
                                for ($i = 0; $i < count($lista); $i++) {
                                    if ($lista[$i]['semana'] == $semana->codigo && $lista[$i]['actividad']->id_actividad == $actividad->id_actividad && $lista[$i]['mano_obra']->id_mano_obra == $mo->id_mano_obra) {
                                        $lista[$i]['valor'] += $total;
                                        $lista[$i]['valor_50'] += $cost_dia_50_pers;
                                        $lista[$i]['valor_100'] += $cost_dia_100_pers;
                                        if (!in_array($row['B'], $lista[$i]['personal']))
                                            $lista[$i]['personal'][] = $row['B'];
                                        $existe = true;
                                    }
                                }
                                if (!$existe) {
                                    array_push($lista, [
                                        'semana' => $semana->codigo,
                                        'actividad' => $actividad,
                                        'mano_obra' => $mo,
                                        'valor' => $total,
                                        'valor_50' => $cost_dia_50_pers,
                                        'valor_100' => $cost_dia_100_pers,
                                        'personal' => [$row['B']],
                                    ]);
                                }
                            } else {
                                dump('pos: ' . $pos_row . '****************** ERROR *******************');
                                dump('No se ha encontrado la MANO de OBRA: "' . espacios(mb_strtoupper($row['D'])) . '"');
                                if (!in_array('No se ha encontrado la MANO de OBRA: ' . espacios(mb_strtoupper($row['D'])), $faltantes))
                                    array_push($faltantes, 'No se ha encontrado la MANO de OBRA: ' . espacios(mb_strtoupper($row['D'])));
                            }
                        } else {
                            dump('pos: ' . $pos_row . '****************** ERROR *******************');
                            dump('No se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])) . ' en el AREA: ' . $area->nombre);
                            if (!in_array('No se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])) . ' en el AREA: ' . $area->nombre, $faltantes))
                                array_push($faltantes, 'No se ha encontrado la ACTIVIDAD: ' . espacios(mb_strtoupper($row['C'])) . ' en el AREA: ' . $area->nombre);
                        }
                    } else {
                        dump('pos: ' . $pos_row . '****************** ERROR *******************');
                        dump('No se ha encontrado el AREA: ' . espacios(mb_strtoupper($row['J'])));
                        if (!in_array('No se ha encontrado el AREA: ' . espacios(mb_strtoupper($row['J'])), $faltantes))
                            array_push($faltantes, 'No se ha encontrado el AREA: ' . espacios(mb_strtoupper($row['J'])));
                    }
                } else {
                    dump('pos: ' . $pos_row . '****************** ERROR *******************');
                    dump('No se ha encontrado la SEMANA de la FECHA: "' . $row['B'] . '"');
                }
            }
        }
        dump('=========== GUARDAR DATOS ===========');
        foreach ($lista as $pos_item => $item) {
            $act_mo = ActividadManoObra::All()
                ->where('id_actividad', $item['actividad']->id_actividad)
                ->where('id_mano_obra', $item['mano_obra']->id_mano_obra)
                ->first();
            if ($act_mo == '') {
                $act_mo = new ActividadManoObra();
                $actividadManoObra = ActividadManoObra::orderBy('id_actividad_mano_obra','desc')->first();
                $act_mo->id_actividad_mano_obra = isset($actividadManoObra->id_actividad_mano_obra) ? $actividadManoObra->id_actividad_mano_obra + 1 : 1;
                $act_mo->id_actividad = $item['actividad']->id_actividad;
                $act_mo->id_mano_obra = $item['mano_obra']->id_mano_obra;
                $act_mo->save();
                $act_mo = ActividadManoObra::All()->last();
            }
            $costo_semana = CostosSemanaManoObra::All()
                ->where('codigo_semana', $item['semana'])
                ->where('id_actividad_mano_obra', $act_mo->id_actividad_mano_obra)
                ->first();
            if ($costo_semana == '') {
                $costo_semana = new CostosSemanaManoObra();
                $costoSemanManoObra = CostosSemanaManoObra::orderBy('id_costos_semana_mano_obra','desc')->first();
                $costo_semana->id_costos_semana_mano_obra = isset($costoSemanManoObra->id_costos_semana_mano_obra) ? $costoSemanManoObra->id_costos_semana_mano_obra + 1 : 1;
                $costo_semana->codigo_semana = $item['semana'];
                $costo_semana->id_actividad_mano_obra = $act_mo->id_actividad_mano_obra;
                $costo_semana->valor = 0;
                $costo_semana->valor_50 = 0;
                $costo_semana->valor_100 = 0;
                $costo_semana->cantidad = 0;
            }
            if ($sobreescribir == 'S') {
                $costo_semana->valor = $item['valor'];
                $costo_semana->valor_50 = $item['valor_50'];
                $costo_semana->valor_100 = $item['valor_100'];
                $costo_semana->cantidad = count($item['personal']);
            } else {
                $costo_semana->valor += $item['valor'];
                $costo_semana->valor_50 += $item['valor_50'];
                $costo_semana->valor_100 += $item['valor_100'];
                $costo_semana->cantidad += count($item['personal']);
            }
            dump(($pos_item + 1) . '/' . count($lista) . '-' . porcentaje($pos_item, count($lista), 1) . '% - act_mo: ' . $act_mo->id_actividad_mano_obra . ' - act: ' . $item['actividad']->nombre . ' - prod: ' . $item['mano_obra']->nombre . ' -sem: ' . $item['semana'] . ' - valor: ' . $item['valor']);
            $costo_semana->save();
        }
        if (count($faltantes) > 0) {
            dump('=========== FALTANTES ===========');
            dump($faltantes);
            /* ------------ ACTUALIZR NOTIFICACION fallos_upload_insumos --------------- */
            NotificacionesSistema::fallos_upload_mano_obra($faltantes);
        }
    }
}
