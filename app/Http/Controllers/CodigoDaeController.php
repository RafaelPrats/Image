<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\CodigoDae;
use yura\Modelos\Pais;
use yura\Modelos\Submenu;
use DB;
use Validator;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;

class CodigoDaeController extends Controller
{
    public function inicio(Request $request)
    {
        return view(
            'adminlte.gestion.configuracion_facturacion.codigo_dae.inicio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
                'text' => ['titulo' => 'Código DAE', 'subtitulo' => 'módulo de parametros de facturación']
            ]
        );
    }

    public function buscar_codigo_dae(Request $request)
    {
        $busqueda = $request->has('busqueda') ? espacios($request->busqueda) : '';
        $bus = str_replace(' ', '%%', $busqueda);
        $bus = mb_strtoupper($bus);

        $listado = CodigoDae::where('codigo_dae.estado', 1)
            ->join('pais as p', 'codigo_dae.codigo_pais', 'p.codigo');

        if ($request->busqueda != '')
            $listado = $listado->Where(function ($q) use ($bus) {
                $q->Where('codigo_dae.codigo_pais', 'like', '%' . $bus . '%')
                    ->orWhere('p.nombre', 'like', '%' . $bus . '%');
            });

        if ($request->anno != '')
            $listado = $listado->where('codigo_dae.anno', 'like', '%' . $request->anno . '%');
        if ($request->mes != '')
            $listado = $listado->where('codigo_dae.mes', 'like', '%' . $request->mes . '%');

        $listado = $listado->orderBy('codigo_dae.anno', 'desc')
            ->orderBy('codigo_dae.mes', 'desc')
            ->orderBy('p.nombre', 'asc')
            ->get();

        return view('adminlte.gestion.configuracion_facturacion.codigo_dae.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function seleccionar_pais()
    {
        $paises = DB::table('codigo_dae')
            ->select('codigo_pais')->distinct()
            ->where('estado', 1)
            ->get()->pluck('codigo_pais')->toArray();
        return view('adminlte.gestion.configuracion_facturacion.codigo_dae.partials.lista_paises', [
            'dataPaises' => Pais::orderBy('nombre', 'asc')->get(),
            'paises' => $paises
        ]);
    }

    public function busqueda_pais_modal(Request $request)
    {
        $dataPais = Pais::where('nombre', 'like', $request->nombre . '%')->get();

        $html = '';
        foreach ($dataPais as $pais) {
            $id = '"codigo_pais_' . $pais->codigo . '"';
            $html .= "<div class='col-md-4'>
                <input type='checkbox' id='codigo_pais_" . $pais->codigo . "' name='codigo_pais_" . $pais->codigo . "' onclick='selected(" . $id . ")' value='" . $pais->codigo . "'>
                <label for='codigo_pais_" . $pais->codigo . "'>" . $pais->nombre . "</label>
            </div>";
        }
        return $html;
    }

    public function pais(Request $request)

    {
        return Pais::where('codigo', $request->codigo)->first();
    }

    public function exportar_paises(Request $request)
    {
        $spread = new Spreadsheet();
        $this->excel_hoja_paises($spread, $request);

        $fileName = "PAISES.xlsx";
        $writer = new Xlsx($spread);

        //--------------------------- GUARDAR EL EXCEL -----------------------

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');

        //$writer->save('/var/www/html/Dasalflor/storage/storage/excel/excel_prueba.xlsx');
    }

    public function excel_hoja_paises($spread, $request)
    {
        $listado = explode(',', $request->arreglo);
        if (count($listado) > 0) {
            $sheet = $spread->getActiveSheet();
            $sheet->setTitle('PAISES');

            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1:F1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CCFFCC');

            $sheet->getCell('A1')->setValue('Código DAE por país');

            $sheet->getCell('A2')->setValue('CÓDIGO PAÍS');
            $sheet->getCell('B2')->setValue('NOMBRE PAIS');
            //$sheet->getCell('C2')->setValue('DAE');
            $sheet->getCell('C2')->setValue('CÓDIGO DAE');
            $sheet->getCell('D2')->setValue('MES');
            $sheet->getCell('E2')->setValue('AÑO');

            $sheet->getStyle('A2:E2')->getFont()->setBold(true)->setSize(12);

            $sheet->getStyle('A2:E2')
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()
                ->setRGB(PHPExcel_Style_Color::COLOR_BLACK);

            $sheet->getStyle('A2:E2')
                ->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('CCFFCC');

            //--------------------------- LLENAR LA TABLA ---------------------------------------------
            for ($i = 0; $i < sizeof($listado); $i++) {
                $sheet->getCell('A' . ($i + 3))->setValue(substr($listado[$i], 0, 16));
                $sheet->getCell('B' . ($i + 3))->setValue(Pais::where('codigo', $listado[$i])->select('nombre')->first()->nombre);
            }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            //$sheet->getColumnDimension('F')->setAutoSize(true);

        } else {
            return '<div>No se han seleccionado paises</div>';
        }
    }

    public function form_file_codigo_dae()
    {
        return view(
            'adminlte.gestion.configuracion_facturacion.codigo_dae.form.upload_codigo_dae',
            [
                'empresas' => getConfiguracionEmpresa(null, true)
            ]
        );
    }

    public function importar_codigo_dae(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'file' => 'required',
            'id_configuracion_empresa' => 'required'
        ], [
            'id_configuracion_empresa.required' => 'Debe seleccionar la empresa a la que pertencen los códigos dae a subir',
            'file.required' => 'Debe seleccionar el archivo excel descargado con los códigos dae'
        ]);

        if (!$valida->fails()) {
            $msg = '';
            $document = IOFactory::load($request->file);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
            for ($i = 3; $i <= count($activeSheetData); $i++) {
                if ($activeSheetData[$i]['A'] != '' && $activeSheetData[$i]['B'] != '' && $activeSheetData[$i]['C'] != '' && $activeSheetData[$i]['D'] != '' && $activeSheetData[$i]['E'] != '') {
                    $existPais = Pais::where('codigo', $activeSheetData[$i]['A'])->count();
                    if ($existPais > 0) {
                        if (is_numeric($activeSheetData[$i]['D'])) {
                            $existRegistro = CodigoDae::where([
                                ['codigo_pais', $activeSheetData[$i]['A']],
                                //['codigo_dae', $activeSheetData[$i]['C']],
                                ['mes', $activeSheetData[$i]['D']],
                                ['anno', $activeSheetData[$i]['E']]
                            ]);
                            $existRegistro->count() > 0 ? $objCodigoDae = CodigoDae::find($existRegistro->first()->id_codigo_dae) : $objCodigoDae = new CodigoDae;

                            $objCodigoDae->codigo_pais = $activeSheetData[$i]['A'];
                            $objCodigoDae->dae = ''; //$activeSheetData[$i]['C'];
                            $objCodigoDae->codigo_dae = $activeSheetData[$i]['C'];
                            $objCodigoDae->mes = $activeSheetData[$i]['D'] < 10 && strlen($activeSheetData[$i]['D']) == 1 ? ('0' . $activeSheetData[$i]['D']) : $activeSheetData[$i]['D'];
                            $objCodigoDae->anno = $activeSheetData[$i]['E'];
                            $objCodigoDae->id_configuracion_empresa = $request->id_configuracion_empresa;
                            if ($objCodigoDae->save()) {
                                $model = CodigoDae::all()->last();
                                $msg .= '<div class="alert alert-success text-center">' .
                                    '<p> Se ha guardado el código DAE para el país ' . $activeSheetData[$i]['B'] . '  exitosamente</p>'
                                    . '</div>';
                                bitacora('codigo_dae', $model->id_codigo_dae, 'I', 'Inserción satisfactoria de un nuevo codigo dae');
                            } else {
                                $msg .= '<div class="alert alert-danger text-center">' .
                                    '<p> Hubo un error al guardar el código DAE para el país ' . $activeSheetData[$i]['B'] . '  intente nuevamente</p>'
                                    . '</div>';
                            }
                        } else {
                            $msg .= '<div class="alert alert-danger text-center">' .
                                '<p> EL campo MES del código dae ' . $activeSheetData[$i]['D'] . ' no es númerico, debe estar entre el 01 y el 12, correspondiendo a los meses del año verifiquelo y cargue nuevamente el archivo excel</p>'
                                . '</div>';
                        }
                    } else {
                        $msg .= '<div class="alert alert-danger text-center">' .
                            '<p> EL codigo ' . $activeSheetData[$i]['A'] . ' no corresponde al país ' . $activeSheetData[$i]['B'] . ', Exporte nuevamente el archivo excel con este pais y no modifique ningún dato de la columna CÓDIGO PAÍS</p>'
                            . '</div>';
                    }
                } else {
                    $msg .= '<div class="alert alert-danger text-center">' .
                        '<p> El registro de la fila ' . $i . ' no pudo ser guardardo ya que hubo un campo vacío en alguna de las columnas</p>'
                        . '</div>';
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
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }
        return $msg;
    }

    public function descactivar_codigo(Request $request)
    {

        $objCodigoDae = CodigoDae::find($request->id_codigo);
        $objCodigoDae->estado = 0;
        if ($objCodigoDae->save()) {
            $success = true;
            $msg = '<div class="alert alert-success text-center">' .
                '<p> Se ha actualizado exitosamente el estado del código</p>'
                . '</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Hubo un error al actualizar el estado, intente nuevamente</p>'
                . '</div>';
        }
        return [
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_codigo_dae(Request $request)
    {
        $success = true;

        $model = CodigoDae::find($request->id_codigo_dae);
        $model->dae = ''; //$request->dae;
        $model->codigo_dae = $request->codigo_dae;

        if ($model->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha actualizado satisfactoriamente</div>';
        } else {
            $success = false;
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un problema al actualizar</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
}
