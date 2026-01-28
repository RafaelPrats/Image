<?php

namespace yura\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use yura\Modelos\Submenu;
use yura\Modelos\Pedido;
use \Zpl\{ZplBuilder, Printer};
use \Zpl\Fonts\Generic;
use Barryvdh\DomPDF\Facade as PDF;
use Picqer\Barcode\BarcodeGeneratorHTML;
use yura\Modelos\AgenciaCarga;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\DistribucionMixtos;

class EtiquetaController extends Controller
{
    public function inicio(Request $request)
    {
        return view(
            'adminlte.gestion.postcocecha.etiquetas.inicio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
                'text' => ['titulo' => 'Etiquetas', 'subtitulo' => 'módulo de comercialización'],
                'clientes' => DetalleCliente::where('estado', true)->get(),
                'agenciasCarga' => AgenciaCarga::where('estado', true)->get(),
            ]
        );
    }

    public function listado(Request $request)
    {
        $query = Pedido::Join('envio as e', 'pedido.id_pedido', 'e.id_pedido')
            ->join('cliente as cl', 'pedido.id_cliente', 'cl.id_cliente')
            ->join('detalle_cliente as dc', 'cl.id_cliente', 'dc.id_cliente')
            ->select(
                'dc.codigo_pais',
                'packing',
                'dc.nombre as cli_nombre',
                'pedido.id_pedido',
                'dc.impresion',
                'e.guia_madre',
                'e.guia_hija',
                'e.dae',
                'pedido.eitqueta_impresa',
                'pedido.etiqueta_descargada',
                DB::raw("(SELECT id_aerolinea FROM detalle_envio AS de WHERE de.id_envio = e.id_envio LIMIT 1) AS id_aerolinea")
            );
        if ($request->cliente != 'T')
            $query = $query->where('pedido.id_cliente', $request->cliente);
        if ($request->agencia_carga != 'T')
            $query = $query->where(DB::raw('(SELECT COUNT(*) FROM detalle_pedido as dp WHERE dp.id_pedido = pedido.id_pedido AND dp.id_agencia_carga = ' . $request->agencia_carga . ' LIMIT 1)'), '>', 0);

        $query = $query->where('pedido.fecha_pedido', $request->desde);
        $query = $query->where('pedido.estado', true);
        $query = $query->where('dc.estado', true);
        $query = $query->where(DB::raw('(SELECT COUNT(*) FROM detalle_pedido as dp WHERE dp.id_pedido = pedido.id_pedido)'), '>', 0);

        $query = $query->orderBy('packing')->get();
        return view('adminlte.gestion.postcocecha.etiquetas.partials.listado', [
            'pedidos' => $query,
        ]);
    }

    public function update_etiqueta_descargada(Request $request)
    {
        $pedido = Pedido::find($request->id_pedido);
        $pedido->etiqueta_descargada = 1;
        $updated = $pedido->save();
        $response = [
            'success' => $updated
        ];
        return response()->json($response);
    }

    public function exportar_excel(Request $request)
    {
        //---------------------- EXCEL --------------------------------------
        $objPHPExcel = new PHPExcel;
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

        $objPHPExcel->removeSheetByIndex(0); //Eliminar la hoja inicial por defecto

        $this->excel_facturas_etiquetas($objPHPExcel, $request);

        //dd("hola");
        //--------------------------- GUARDAR EL EXCEL -----------------------

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="Etiquestas Cajas.xlsx"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        ob_start();
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $opResult = array(
            'status' => 1,
            'data' => "data:application/vnd.ms-excel;base64," . base64_encode($xlsData)
        );
        echo json_encode($opResult);
    }

    /**
     * @param $objPHPExcel
     * @param $request
     * @throws \PHPExcel_Exception
     */
    public function excel_facturas_etiquetas($objPHPExcel, $request)
    {
        $objSheet = new PHPExcel_Worksheet($objPHPExcel, 'Etiquetas');
        $objPHPExcel->addSheet($objSheet, 0);

        $objSheet->getCell('A1')->setValue('Guía');
        $objSheet->getCell('B1')->setValue('Guía_H');
        $objSheet->getCell('C1')->setValue('Secuencial');
        $objSheet->getCell('D1')->setValue('Total ETQ');
        $objSheet->getCell('E1')->setValue('Cliente');
        $objSheet->getCell('F1')->setValue('Cliente_b');
        $objSheet->getCell('G1')->setValue('Cod. Cliente');
        $objSheet->getCell('H1')->setValue('Ramos caja');
        $objSheet->getCell('I1')->setValue('Cod-Finca');
        $objSheet->getCell('J1')->setValue('Registro');
        $objSheet->getCell('K1')->setValue('País destino');
        $objSheet->getCell('L1')->setValue('Refrendo');
        $objSheet->getCell('M1')->setValue('Ruc');
        $objSheet->getCell('N1')->setValue('Variedad0');
        $objSheet->getCell('O1')->setValue('Color0');
        $objSheet->getCell('P1')->setValue('Length0');
        $objSheet->getCell('Q1')->setValue('Bounches0');
        $objSheet->getCell('R1')->setValue('Weigth0');
        $objSheet->getCell('S1')->setValue('Variedad1');
        $objSheet->getCell('T1')->setValue('Color1');
        $objSheet->getCell('U1')->setValue('Length1');
        $objSheet->getCell('V1')->setValue('Bounches1');
        $objSheet->getCell('W1')->setValue('Weigth1');
        $objSheet->getCell('X1')->setValue('Variedad2');
        $objSheet->getCell('Y1')->setValue('Color2');
        $objSheet->getCell('Z1')->setValue('Length2');
        $objSheet->getCell('AA1')->setValue('Bounches2');
        $objSheet->getCell('AB1')->setValue('Weigth2');
        $objSheet->getCell('AC1')->setValue('Variedad3');
        $objSheet->getCell('AD1')->setValue('Color3');
        $objSheet->getCell('AE1')->setValue('Length3');
        $objSheet->getCell('AF1')->setValue('Bounches3');
        $objSheet->getCell('AG1')->setValue('Weigth3');
        $objSheet->getCell('AH1')->setValue('Variedad4');
        $objSheet->getCell('AI1')->setValue('Color4');
        $objSheet->getCell('AJ1')->setValue('Length4');
        $objSheet->getCell('AK1')->setValue('Bounches4');
        $objSheet->getCell('AL1')->setValue('Weigth4');
        $objSheet->getCell('AM1')->setValue('Variedad5');
        $objSheet->getCell('AN1')->setValue('Color5');
        $objSheet->getCell('AO1')->setValue('Length5');
        $objSheet->getCell('AP1')->setValue('Bounches5');
        $objSheet->getCell('AQ1')->setValue('Weigth5');
        $objSheet->getCell('AR1')->setValue('Variedad6');
        $objSheet->getCell('AS1')->setValue('Color6');
        $objSheet->getCell('AT1')->setValue('Length6');
        $objSheet->getCell('AU1')->setValue('Bounches6');
        $objSheet->getCell('AV1')->setValue('Weigth6');
        $objSheet->getCell('AW1')->setValue('Variedad7');
        $objSheet->getCell('AX1')->setValue('Color7');
        $objSheet->getCell('AY1')->setValue('Length7');
        $objSheet->getCell('AZ1')->setValue('Bounches7');
        $objSheet->getCell('BA1')->setValue('Weigth7');
        $objSheet->getCell('BB1')->setValue('Variedad8');
        $objSheet->getCell('BC1')->setValue('Color8');
        $objSheet->getCell('BD1')->setValue('Length8');
        $objSheet->getCell('BE1')->setValue('Bounches8');
        $objSheet->getCell('BF1')->setValue('Weigth8');
        $objSheet->getCell('BG1')->setValue('Variedad9');
        $objSheet->getCell('BH1')->setValue('Color9');
        $objSheet->getCell('BI1')->setValue('Length9');
        $objSheet->getCell('BJ1')->setValue('Bounches9');
        $objSheet->getCell('BK1')->setValue('Weigth9');
        $objSheet->getCell('BL1')->setValue('Variedad10');
        $objSheet->getCell('BM1')->setValue('Color10');
        $objSheet->getCell('BN1')->setValue('Length10');
        $objSheet->getCell('BO1')->setValue('Bounches10');
        $objSheet->getCell('BP1')->setValue('Weigth10');
        $objSheet->getCell('BQ1')->setValue('Variedad11');
        $objSheet->getCell('BR1')->setValue('Color11');
        $objSheet->getCell('BS1')->setValue('Length11');
        $objSheet->getCell('BT1')->setValue('Bounches11');
        $objSheet->getCell('BU1')->setValue('Weigth11');
        $objSheet->getCell('BV1')->setValue('Variedad12');
        $objSheet->getCell('BW1')->setValue('Color12');
        $objSheet->getCell('BX1')->setValue('Length12');
        $objSheet->getCell('BY1')->setValue('Bounches12');
        $objSheet->getCell('BZ1')->setValue('Weigth12');

        if (sizeof($request->arr_facturas) > 0) {

            $w = 1;
            $empresa = getPedido($request->arr_facturas[0]['id_pedido'])->empresa;
            $prefijo = explode("-", $empresa->codigo_etiqueta_empresa)[0];
            $nombre_empresa = str_split(isset(explode("-", $empresa->codigo_etiqueta_empresa)[1]) ? explode("-", $empresa->codigo_etiqueta_empresa)[1] : explode("-", $empresa->codigo_etiqueta_empresa)[0]);
            $semana = substr(getSemanaByDate(now()->toDateString())->codigo, 2, 2);
            $anno = Carbon::parse(now()->toDateString())->format('y');
            $dia_semana = Carbon::parse(now()->toDateString())->dayOfWeek;
            $numeracion = $anno . $semana . $dia_semana;
            $numeracion = str_split($numeracion);
            $codigo_finca = '';

            foreach ($numeracion as $n) {
                if (isset(getSemanaByDate(now()->toDateString())->codigo)) {
                    for ($x = 0; $x < count($nombre_empresa); $x++) {
                        if ($x == $n) {
                            $codigo_finca .= $nombre_empresa[$x];
                            break;
                        }
                    }
                }
            }

            $arr_pedidos = [];
            foreach ($request->arr_facturas as $factura) {
                $arr_pedidos[$factura['id_pedido']][] = [
                    'caja' => $factura['caja'],
                    'doble' => $factura['doble']
                ];
            }
            foreach ($arr_pedidos as $id_pedido => $arr_ped) {
                $z = 1;
                $pedido = getPedido($id_pedido);
                if ($pedido->tipo_especificacion == "T")
                    $total_cajas = $pedido->cant_rows_etiqueta($arr_ped);
                foreach ($arr_ped as $ped) {

                    $ped['doble'] == "true"  ? $doble = 2  : $doble = 1;
                    $pais_destino = getPais($pedido->cliente->detalle()->codigo_pais)->nombre;
                    $dae = $pedido->envios[0]->dae;
                    $factura_tercero = getFacturaClienteTercero($pedido->envios[0]->id_envio);
                    if ($factura_tercero != "" && isset($factura_tercero->codigo_pais)) {
                        $pais_destino = getPais($factura_tercero->codigo_pais)->nombre;
                        $dae = $factura_tercero->dae;
                    }
                    $ruc = "RUC: " . $pedido->empresa->ruc;

                    $arr_posiciones = $this->posiciones_excel();
                    for ($y = 1; $y <= $doble; $y++) {
                        if ($pedido->tipo_especificacion == "N") {
                            foreach ($pedido->detalles as $det_ped) {
                                $datos_exportacion = '';
                                if (getDatosExportacionByDetPed($det_ped->id_detalle_pedido)->count() > 0) {
                                    foreach (getDatosExportacionByDetPed($det_ped->id_detalle_pedido) as $dE)
                                        $datos_exportacion .= $dE->valor . "-";
                                }
                                foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $esp_emp) {
                                    if (explode("|", $esp_emp->empaque->nombre)[1] === $ped['caja']) {
                                        $x = 1;
                                        for ($z = 1; $z <= $det_ped->cantidad; $z++) {
                                            $posicion = 14;
                                            //--------------------------- LLENAR LA TABLA ---------------------------------------------
                                            $objSheet->getCell('A' . ($w + 1))->setValue("AWB. " . $pedido->envios[0]->guia_madre);
                                            $objSheet->getCell('B' . ($w + 1))->setValue("HAWB. " . $pedido->envios[0]->guia_hija);
                                            $objSheet->getCell('C' . ($w + 1))->setValue($x);
                                            $objSheet->getCell('D' . ($w + 1))->setValue($det_ped->cantidad);
                                            $objSheet->getCell('E' . ($w + 1))->setValue($pedido->envios[0]->id_consignatario != "" ? $pedido->envios[0]->consignatario->nombre : $pedido->cliente->detalle()->nombre);
                                            $objSheet->getCell('F' . ($w + 1))->setValue($pedido->envios[0]->id_consignatario != "" ? $pedido->cliente->detalle()->nombre : "");
                                            $objSheet->getCell('G' . ($w + 1))->setValue($datos_exportacion != '' ? substr($datos_exportacion, 0, -1) : "");
                                            $objSheet->getCell('H' . ($w + 1))->setValue($esp_emp->ramos_x_caja($det_ped->detalle_pedido));
                                            $objSheet->getCell('I' . ($w + 1))->setValue($prefijo . "-" . $codigo_finca);
                                            $objSheet->getCell('J' . ($w + 1))->setValue(getConfiguracionEmpresa($pedido->id_configuracion_empresa)->permiso_agrocalidad);
                                            $objSheet->getCell('K' . ($w + 1))->setValue('País destino. ' . $pais_destino);
                                            $objSheet->getCell('L' . ($w + 1))->setValue($dae);
                                            $objSheet->getCell('M' . ($w + 1))->setValue($ruc);
                                            foreach ($esp_emp->detalles as $det_esp_emp) {
                                                $objSheet->getCell($arr_posiciones[$posicion] . ($w + 1))->setValue($det_esp_emp->variedad->planta->nombre);
                                                $objSheet->getCell($arr_posiciones[$posicion + 1] . ($w + 1))->setValue($det_esp_emp->variedad->nombre);
                                                $objSheet->getCell($arr_posiciones[$posicion + 2] . ($w + 1))->setValue($det_esp_emp->longitud_ramo . " " . $det_esp_emp->unidad_medida->siglas);
                                                $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);
                                                $objSheet->getCell($arr_posiciones[$posicion + 3] . ($w + 1))->setValue(isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);
                                                $objSheet->getCell($arr_posiciones[$posicion + 4] . ($w + 1))->setValue($det_esp_emp->clasificacion_ramo->nombre . " " . $det_esp_emp->clasificacion_ramo->unidad_medida->siglas . ".");
                                                $posicion += 5;
                                            }
                                            $w++;
                                            $x++;
                                        }
                                    }
                                }
                            }
                        } else if ($pedido->tipo_especificacion == "T") {
                            foreach ($pedido->detalles as $det_ped) {
                                $datos_exportacion = '';
                                if (getDatosExportacionByDetPed($det_ped->id_detalle_pedido)->count() > 0)
                                    foreach (getDatosExportacionByDetPed($det_ped->id_detalle_pedido) as $dE)
                                        $datos_exportacion .= $dE->valor . "-";

                                foreach ($det_ped->marcaciones as $mc) {
                                    if (explode("|", $mc->especificacion_empaque->empaque->nombre)[1] === $ped['caja']) {
                                        foreach ($mc->distribuciones as $d => $dist) {
                                            $posicion = 14;
                                            $distribucion_coloracion = json_decode($dist->dist_col);

                                            $objSheet->getCell('A' . ($w + 1))->setValue("AWB. " . $pedido->envios[0]->guia_madre);
                                            $objSheet->getCell('B' . ($w + 1))->setValue("HAWB. " . $pedido->envios[0]->guia_hija);
                                            $objSheet->getCell('C' . ($w + 1))->setValue($z);
                                            $objSheet->getCell('D' . ($w + 1))->setValue($total_cajas);
                                            $objSheet->getCell('E' . ($w + 1))->setValue($pedido->envios[0]->id_consignatario != "" ? $pedido->envios[0]->consignatario->nombre : $pedido->cliente->detalle()->nombre);
                                            $objSheet->getCell('F' . ($w + 1))->setValue($pedido->envios[0]->id_consignatario != "" ? $pedido->cliente->detalle()->nombre : "");
                                            $objSheet->getCell('G' . ($w + 1))->setValue($mc->nombre);
                                            $objSheet->getCell('H' . ($w + 1))->setValue($mc->marcaciones_coloraciones[0]->detalle_especificacionempaque->cantidad);
                                            $objSheet->getCell('I' . ($w + 1))->setValue("DS-" . $codigo_finca);
                                            $objSheet->getCell('J' . ($w + 1))->setValue(getConfiguracionEmpresa($pedido->id_configuracion_empresa)->permiso_agrocalidad);
                                            $objSheet->getCell('K' . ($w + 1))->setValue('País destino. ' . $pais_destino);
                                            $objSheet->getCell('L' . ($w + 1))->setValue($dae);
                                            $objSheet->getCell('M' . ($w + 1))->setValue($ruc);
                                            foreach ($distribucion_coloracion as $dist_col) {
                                                foreach ($dist_col as $dc) {
                                                    if ($dc->cantidad > 0) {
                                                        $objSheet->getCell($arr_posiciones[$posicion] . ($w + 1))->setValue($dc->planta . " - " . $dc->variedad);
                                                        $objSheet->getCell($arr_posiciones[$posicion + 1] . ($w + 1))->setValue($dc->color);
                                                        $objSheet->getCell($arr_posiciones[$posicion + 2] . ($w + 1))->setValue($dc->longitud_ramo . " " . $dc->det_esp_u_m);
                                                        $objSheet->getCell($arr_posiciones[$posicion + 3] . ($w + 1))->setValue($dc->cantidad);
                                                        $objSheet->getCell($arr_posiciones[$posicion + 4] . ($w + 1))->setValue($dc->ramo . " " . $dc->ramo_u_m . ".");
                                                        $posicion += 5;
                                                    }
                                                }
                                            }
                                            $w++;
                                            $z++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $objSheet->getColumnDimension('A')->setAutoSize(true);
            $objSheet->getColumnDimension('B')->setAutoSize(true);
            $objSheet->getColumnDimension('C')->setAutoSize(true);
            $objSheet->getColumnDimension('D')->setAutoSize(true);
            $objSheet->getColumnDimension('E')->setAutoSize(true);
            $objSheet->getColumnDimension('F')->setAutoSize(true);
            $objSheet->getColumnDimension('G')->setAutoSize(true);
            $objSheet->getColumnDimension('H')->setAutoSize(true);
            $objSheet->getColumnDimension('I')->setAutoSize(true);
            $objSheet->getColumnDimension('J')->setAutoSize(true);
            $objSheet->getColumnDimension('K')->setAutoSize(true);
            $objSheet->getColumnDimension('L')->setAutoSize(true);
            $objSheet->getColumnDimension('M')->setAutoSize(true);
            $objSheet->getColumnDimension('N')->setAutoSize(true);
            foreach ($this->posiciones_excel() as $posicion) $objSheet->getColumnDimension($posicion)->setAutoSize(true);
        } else {
            $objSheet->getCell('A1')->setValue('No se han seleccionado facturas');
        }
    }

    public function posiciones_excel()
    {
        return [
            14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z',
            27 => 'AA', 28 => 'AB', 29 => 'AC', 30 => 'AD', 31 => 'AE', 32 => 'AF', 33 => 'AG', 34 => 'AH', 35 => 'AI', 36 => 'AJ', 37 => 'AK', 38 => 'AL', 39 => 'AM',
            40 => 'AN', 41 => 'AO', 42 => 'AP', 43 => 'AQ', 44 => 'AR', 45 => 'AS', 46 => 'AT', 47 => 'AU', 48 => 'AV', 49 => 'AW', 50 => 'AX', 51 => 'AY', 52 => 'AZ',
            53 => 'BA', 54 => 'BB', 55 => 'BC', 56 => 'BD', 57 => 'BE', 58 => 'BF', 59 => 'BG', 60 => 'BH', 61 => 'BI', 62 => 'BJ', 63 => 'BK', 64 => 'BL', 65 => 'BM',
            66 => 'BN', 67 => 'BO', 68 => 'BP', 69 => 'BQ', 70 => 'BR', 71 => 'BS', 72 => 'BS', 73 => 'BT', 74 => 'BU', 75 => 'BU', 76 => 'BV', 77 => 'BW', 78 => 'BX',
            79 => 'BY', 80 => 'BZ', 81 => 'CA'

        ];
    }

    public function imprimir_etiqueta(Request $request)
    {
        set_time_limit(600);
        ini_set('memory_limit', '-1');

        try {

            foreach ($request->data_pedidos as $p) {

                $impresionCajas = [];

                if (isset($p['inicio']) && isset($p['fin']))
                    foreach (range($p['inicio'], $p['fin']) as $a)
                        $impresionCajas[] = $a;

                $nCaja = 1;
                $pedido = Pedido::find($p['id_pedido']);

                foreach ($pedido->detalles as $det_ped) {

                    $marcaciones = DetallePedidoDatoExportacion::where([
                        ['id_detalle_pedido', $det_ped->id_detalle_pedido],
                    ])->get()->pluck('valor')->toArray();

                    if ($p['doble'] == 'true') {

                        $cantidad = $det_ped->cantidad * 2;
                        $nCaja = 1;
                    } else {

                        $cantidad = $det_ped->cantidad;
                    }

                    foreach (range(1, $cantidad) as $caja) {

                        if ($p['doble'] == 'true' && $nCaja > $cantidad / 2)
                            $nCaja = 1;

                        if (!count($impresionCajas) || in_array($nCaja, $impresionCajas)) {

                            $rectangleY = 63;
                            $columnLine = 5;

                            $driver = new ZplBuilder('mm');
                            $driver->setEncoding(28);
                            $driver->setFontMapper(new Generic());

                            $driver->drawGraphic(27, 0, public_path('images/Logo-Senae.jpg'), 150);
                            $driver->drawCode128(21, 16, 9, (isset($pedido->envios) && $pedido->envios[0]->dae != '' ? $pedido->envios[0]->dae : '1234567890'), true);

                            $driver->SetXY(0, 0);

                            $driver->SetFont('0', 6);

                            $driver->drawText(5, 31, 'País de destino: ');

                            $driver->SetFont('0', 6);

                            $driver->drawText(20, 31, (isset($pedido->envios) &&  $pedido->envios[0]->pais != null ? $pedido->envios[0]->pais->nombre : 'SIN PAIS'));

                            $driver->SetFont('0', 10);

                            if ($pedido->cliente->detalle()->nombre_empresa_etiqueta)
                                $driver->drawText(31, 35, strtoupper($pedido->empresa->nombre));

                            $driver->SetFont('0', 9);

                            $driver->drawText(5, 40, 'SOLD TO: ' . strtoupper($pedido->cliente->detalle()->nombre));

                            $driver->SetFont('0', 7);

                            $driver->drawText(5, 43, 'CONSIGNEE: ' . (isset($pedido->envios) &&  $pedido->envios[0]->consignatario != null ? $pedido->envios[0]->consignatario->nombre : 'SIN CONSIGNATARIO'));

                            $driver->drawText(5, 46, 'MAWB: ' . (isset($pedido->envios) ? $pedido->envios[0]->guia_madre : 'SIN GUIA'));

                            $driver->drawText(5, 49, 'HAWB: ' . (isset($pedido->envios) ? $pedido->envios[0]->guia_hija : 'SIN GUIA'));

                            $driver->drawText(5, 52, 'PACK DETAIL:');

                            $driver->drawText(5, 55, 'BOX #' . $nCaja);

                            switch (explode('|', $det_ped->cliente_especificacion->especificacion->especificacionesEmpaque[0]->empaque->nombre)[1]) {
                                case '0.25':
                                    $emp = 'QB';
                                    break;
                                case '0.5':
                                    $emp = 'HB';
                                    break;
                                case '0.125':
                                    $emp = 'EB';
                                    break;
                                default:
                                    $emp = '';
                                    break;
                            }

                            $driver->SetFont('0', 11);
                            $driver->drawText(25, 54, $emp);

                            $driver->drawText(40, 54, (isset($marcaciones) ? implode(' - ', $marcaciones) : ''));

                            $driver->drawRect(4, 58, 79, 5);

                            $driver->SetFont('0', 6);

                            $driver->drawText(5, 60, 'PRODUCTO');
                            $driver->drawText(34, 60, 'STEMS');
                            $driver->drawText(45, 60, 'COLOR');
                            $driver->drawText(65, 58, 'MEDIDA');
                            $driver->drawRect(56, 60, 27, 0);
                            $driver->drawText(58, 61, '90');
                            $driver->drawText(63, 61, '80');
                            $driver->drawText(68, 61, '70');
                            $driver->drawText(73, 61, '60');
                            $driver->drawText(79, 61, '50');

                            foreach ($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as $x =>  $esp_emp) {

                                foreach ($esp_emp->detalles as $y => $det_esp_emp) {

                                    $ramos_modificado = getRamosXCajaModificado($det_ped->id_detalle_pedido, $det_esp_emp->id_detalle_especificacionempaque);

                                    $distribucionAssorted = DistribucionMixtos::where('ramos', '>', 0)
                                        ->where('fecha', opDiasFecha('-', 1, $pedido->fecha_pedido))
                                        ->where('id_cliente', $pedido->id_cliente)
                                        ->where('id_pedido', $pedido->id_pedido)
                                        ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                                        ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)->get();
                                    //dd($distribucionAssorted, $pedido->id_cliente, $pedido->id_pedido, $det_esp_emp->id_detalle_especificacionempaque);

                                    if ($x == 0 && $y == 0 && count($distribucionAssorted))
                                        $driver->drawText(53, 55, 'ASSORTED');

                                    $L50 = '';
                                    $L60 = '';
                                    $L70 = '';
                                    $L80 = '';
                                    $L90 = '';

                                    if ($det_esp_emp->longitud_ramo == 50)
                                        $L50 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                                    if ($det_esp_emp->longitud_ramo == 60)
                                        $L60 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                                    if ($det_esp_emp->longitud_ramo == 70)
                                        $L70 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                                    if ($det_esp_emp->longitud_ramo == 80)
                                        $L80 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);

                                    if ($det_esp_emp->longitud_ramo == 90)
                                        $L90 = (isset($ramos_modificado) ? $ramos_modificado->cantidad : $det_esp_emp->cantidad);



                                    $driver->SetFont('0', 6);

                                    if ($distribucionAssorted->count()) {

                                        foreach ($distribucionAssorted as $disAssorted) {

                                            $columnLine += 5;

                                            $driver->drawRect(4, $rectangleY, 79, 5);
                                            $driver->drawText(5, ($rectangleY + 2), $disAssorted->planta->nombre, 30, '');
                                            $driver->drawText(35, ($rectangleY + 2), ($det_esp_emp->tallos_x_ramos));
                                            $driver->drawText(44, ($rectangleY + 2), $disAssorted->variedad()->nombre);
                                            $driver->drawText(58, ($rectangleY + 2), $det_esp_emp->longitud_ramo == 90 ? ($disAssorted->ramos) : '');
                                            $driver->drawText(63, ($rectangleY + 2), $det_esp_emp->longitud_ramo == 80 ? ($disAssorted->ramos) : '');
                                            $driver->drawText(68, ($rectangleY + 2), $det_esp_emp->longitud_ramo == 70 ? ($disAssorted->ramos) : '');
                                            $driver->drawText(73, ($rectangleY + 2), $det_esp_emp->longitud_ramo == 60 ? ($disAssorted->ramos) : '');
                                            $driver->drawText(79, ($rectangleY + 2), $det_esp_emp->longitud_ramo == 50 ? ($disAssorted->ramos) : '');

                                            $rectangleY += 5;
                                        }
                                    } else {

                                        $columnLine += 5;

                                        $driver->drawRect(4, $rectangleY, 79, 5);
                                        $driver->drawText(5, ($rectangleY + 2), $det_esp_emp->variedad->planta->nombre, 30, '');
                                        $driver->drawText(35, ($rectangleY + 2), ($det_esp_emp->tallos_x_ramos));
                                        $driver->drawText(44, ($rectangleY + 2), $det_esp_emp->variedad->nombre);
                                        $driver->drawText(58, ($rectangleY + 2), $L90);
                                        $driver->drawText(63, ($rectangleY + 2), $L80);
                                        $driver->drawText(68, ($rectangleY + 2), $L70);
                                        $driver->drawText(73, ($rectangleY + 2), $L60);
                                        $driver->drawText(78, ($rectangleY + 2), $L50);

                                        $rectangleY += 5;
                                    }
                                }
                            }

                            $driver->drawRect(33.5, 58, 0.2, $columnLine);
                            $driver->drawRect(43, 58, 0.2, $columnLine);
                            $driver->drawRect(56, 58, 0.2, $columnLine);
                            $driver->drawRect(62, 63, 0.2, $columnLine - 5);
                            $driver->drawRect(67, 63, 0.2, $columnLine - 5);
                            $driver->drawRect(72, 63, 0.2, $columnLine - 5);
                            $driver->drawRect(79, 63, 0.2, $columnLine - 5);

                            $driver->drawGraphic(16, $rectangleY, public_path('images/logo_agro_calidad.png'), 380);
                            $driver->drawText(31, ($rectangleY + 18), '1790996743001.05050802');

                            Printer::printer('172.21.4.181')->send($driver->toZpl());
                            sleep(1);

                            //echo $driver->toZpl();
                        }

                        $nCaja++;
                    }
                }

                $pedido->eitqueta_impresa = true;
                $pedido->save();
            }

            return [
                'mensaje' => '<div class="alert alert-success text-center">ETIQUETAS IMPRESAS</div>',
                'success' => true
            ];
        } catch (\Exception $e) {

            return [
                'mensaje' => $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine(),
                'success' => false
            ];
        }
    }

    public function ver_etiqueta(Request $request)
    {
        $barCode = new BarcodeGeneratorHTML();
        $detallePedidoDatoExportacion = new DetallePedidoDatoExportacion;
        set_time_limit(600);
        ini_set('memory_limit', '-1');
        $pedidosReq = $request->pedidos;
        $pedidos = [];
        foreach ($pedidosReq as $pedidoReq) {
            $pedido = getPedido($pedidoReq["id_pedido"]);
            $pedido->isDoublePage = $pedidoReq["isDoublePage"];
            $pedidos[] = $pedido;
        }

        $html = view('adminlte.gestion.postcocecha.etiquetas.partials.pdf_etiqueta', compact('pedidos', 'barCode', 'detallePedidoDatoExportacion'))->render();
        set_time_limit(600);
        return PDF::loadHTML($html)->setPaper(array(0, 0, 245, 321), 'portrait')->stream();
    }

    public function pdf_etiqueta(Request $request)
    {
        set_time_limit(3600);
        ini_set('memory_limit', '-1');
        $barCode = new BarcodeGeneratorHTML();
        $pedido = Pedido::find($request->pedido);
        $datos = [
            'pedido' => $pedido
        ];

        set_time_limit(3600);
        return PDF::loadView('adminlte.gestion.postcocecha.etiquetas.partials.1pdf_etiqueta', compact('datos', 'barCode'))
            ->setPaper(array(0, 0, 245, 321), 'portrait')->stream();
    }

    public function descargar_all_packings(Request $request)
    {
        $listado = [];
        $empresa = getConfiguracionEmpresa();
        foreach (json_decode($request->data) as $d) {
            $pedido = getPedido($d);
            $getDetalleDespacho = getDetalleDespacho($pedido->id_pedido);
            $despacho = isset($getDetalleDespacho->despacho) ? $getDetalleDespacho->despacho : null;
            $envios = $pedido->envios;
            $facturaTercero = isset($envios) ? getFacturaClienteTercero($envios[0]->id_envio) : null;
            if ($facturaTercero !== null) {
                $cliente = [
                    'nombre' => $facturaTercero->nombre_cliente_tercero,
                    'identificacion' => $facturaTercero->identificacion,
                    'tipo_identificacion' => getTipoIdentificacion($facturaTercero->codigo_identificacion)->nombre,
                    'pais' => getPais($facturaTercero->codigo_pais)->nombre,
                    'provincia' => $facturaTercero->provincia,
                    'direccion' => $facturaTercero->direccion,
                    'telefono' => $facturaTercero->telefono,
                    'dae' => $facturaTercero->dae,
                ];
            } else {
                $model_cliente = $pedido->cliente->detalle();
                $cliente = [
                    'nombre' => $model_cliente->nombre,
                    'identificacion' => $model_cliente->ruc,
                    'tipo_identificacion' => '',    //getTipoIdentificacion($cliente->codigo_identificacion)->nombre,
                    'pais' => getPais($model_cliente->codigo_pais)->nombre,
                    'provincia' => $model_cliente->provincia,
                    'direccion' => $model_cliente->direccion,
                    'telefono' => $model_cliente->telefono,
                    'dae' => $envios[0]->dae
                ];
            }
            $listado[] = [
                'pedido' => $pedido,
                'empresa' => $empresa,
                'despacho' => $despacho,
                'cliente' => $cliente,
            ];
        }

        return PDF::loadView('adminlte.gestion.postcocecha.despachos.partials.pdf_all_packing_list', compact(
            'listado',
        ))
            //->setPaper(array(0, 0, 800, 750), 'landscape')
            ->stream();
    }
}
