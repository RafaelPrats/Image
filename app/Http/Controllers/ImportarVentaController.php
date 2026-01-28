<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\Submenu;
use yura\Modelos\Variedad;
use Validator;
use PHPExcel_IOFactory;
use yura\Modelos\VentaSemanalImport;

class ImportarVentaController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.importar_data.venta.inicio',
            [
                'url' => $request->getRequestUri(),
                'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            ]);
    }

    public function descargar_plantilla(Request $request)
    {
        $fileName = basename('plantilla_importar_venta_semanal.xlsx');
        $filePath = public_path('storage/' . $fileName);
        if (!empty($fileName) && file_exists($filePath)) {
            // Define headers
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // Read the file
            readfile($filePath);
            exit;
        }
    }

    public function importar_file(Request $request)
    {
        ini_set('max_execution_time', env('MAX_EXECUTION_TIME'));
        $valida = Validator::make($request->all(), [
            'file_importar' => 'required',
        ]);
        $msg = '<div class="alert alert-success text-center">Se ha importado el archivo satisfactoriamente.</div>';
        $success = true;
        if (!$valida->fails()) {
            try {
                $document = PHPExcel_IOFactory::load($request->file_importar);
                $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
                foreach ($activeSheetData as $pos => $row) {
                    if ($pos > 1 && $row['A'] != null) {
                        $variedad = Variedad::All()
                            ->where('nombre', $row['B'])
                            ->where('estado', 1)
                            ->first();
                        if ($variedad != '') {
                            $model = VentaSemanalImport::All()
                                ->where('semana', $row['A'])
                                ->where('id_variedad', $variedad->id_variedad)
                                ->first();
                            if ($model == '') {
                                $model = new VentaSemanalImport();
                                $model->id_variedad = $variedad->id_variedad;
                                $model->semana = $row['A'];
                            }
                            $model->ramos = str_replace(',', '', $row['C']);
                            $model->venta = str_replace('$', '', str_replace(',', '', $row['D']));
                            $model->save();
                        } else {
                            return [
                                'success' => false,
                                'mensaje' => '<div class="alert alert-danger text-center">' .
                                    'No se encuentra la VARIEDAD: "' . $row["B"] . '" de la fila: "' . $pos . '" en el sistema</div>',
                            ];
                        }
                    }
                }
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

}
