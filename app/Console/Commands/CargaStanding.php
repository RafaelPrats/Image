<?php

namespace yura\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use DB;
use Illuminate\Console\Command;
use PHPExcel_IOFactory;
use Illuminate\Http\Request;
use yura\Http\Controllers\PedidoController;
use yura\Modelos\ClienteAgenciaCarga;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\Consignatario;
use yura\Modelos\DatosExportacion;
use yura\Modelos\DetalleCliente;
use yura\Modelos\Especificacion;
use yura\Modelos\Planta;
use yura\Modelos\Variedad;

class CargaStanding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carga:standing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga pedido Standing';

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
        $document = PHPExcel_IOFactory::load('public/standing2.1.xlsx');
        $hojaPedido = $document->getActiveSheet()->toArray(null, true, true, true);
        DB::beginTransaction();

        try{

            $clientes = [];
            $alertas = '';

            for($i=2; $i <= count($hojaPedido); $i++){

                if(trim($hojaPedido[$i]['A']) =='STANDING'){
                                    //CLIENTE                            DIA
                    $clientes[trim($hojaPedido[$i]['G'])][trim($hojaPedido[$i]['B'])][] = [
                        'A' => trim($hojaPedido[$i]['A']),
                        'B' => trim($hojaPedido[$i]['B']),
                        'C' => trim($hojaPedido[$i]['C']),
                        'D' => trim($hojaPedido[$i]['D']),
                        'E' => trim($hojaPedido[$i]['E']),
                        'F' => trim($hojaPedido[$i]['F']),
                        'G' => trim($hojaPedido[$i]['G']),
                        'H' => trim($hojaPedido[$i]['H']),
                        'I' => trim($hojaPedido[$i]['I']),
                        'J' => trim($hojaPedido[$i]['J']),
                        'K' => trim($hojaPedido[$i]['K']),
                        'L' => trim($hojaPedido[$i]['L']),
                        'M' => trim($hojaPedido[$i]['M']),
                        'N' => trim($hojaPedido[$i]['N']),
                        'O' => trim($hojaPedido[$i]['O']),
                        'P' => trim($hojaPedido[$i]['P']),
                        'Q' => trim($hojaPedido[$i]['Q']),
                        'R' => trim($hojaPedido[$i]['R']),
                        'S' => trim($hojaPedido[$i]['S']),
                        'T' => trim($hojaPedido[$i]['T']),
                        'U' => trim($hojaPedido[$i]['U']),
                        'V' => $i
                    ];

                }

            }

            $bar = $this->output->createProgressBar(count($clientes));

            $dePO = DatosExportacion::where('nombre','PO')->first();

            foreach($clientes as $idcliente => $cli){

                foreach($cli as $dia => $p){

                    $fechas=[];

                    $days = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        7 => 'Sunday'
                    ];

                    $periodo = new DatePeriod(
                        Carbon::parse("first ".$days[trim($dia)]." of this month"),
                        CarbonInterval::week(),
                        Carbon::parse("2023-12-31")
                    );

                    foreach($periodo as $per)
                        $fechas[] = $per->toDateString();

                    $cliente = DetalleCliente::where('ruc',$idcliente)->where('estado',1)->first();

                    if(isset($cliente)){

                        $detallesPedido = [];
                        $arrVariedades = [];
                        $datosExportacion = [];

                        $agenciaCarga = ClienteAgenciaCarga::where([
                            ['id_cliente',$cliente->id_cliente],
                            ['estado',true]
                        ])->first();

                        if(isset($agenciaCarga)){

                            $z = true;

                            foreach($p as $det){

                                $planta= Planta::where('siglas',trim($det['J']))->first();

                                if(isset($planta)){

                                    $variedad = Variedad::where([
                                        ['siglas',trim($det['L'])],
                                        ['id_planta',$planta->id_planta]
                                    ])->first();

                                    if(isset($variedad)){

                                        $cpe = DB::table('cliente_pedido_especificacion as cpe')
                                        ->join('especificacion as esp','esp.id_especificacion','cpe.id_especificacion')
                                        ->join('especificacion_empaque as esp_emp','esp_emp.id_especificacion','esp.id_especificacion')
                                        ->join('empaque as emp',function($j) use($det){

                                            $j->on('esp_emp.id_empaque','emp.id_empaque');

                                            if(trim($det['P']) == 'QB'){
                                                $caja = 'CUARTA';
                                            }elseif(trim($det['P']) == 'HB'){
                                                $caja = 'TABACO';
                                            }else if(trim($det['P']) == 'EB'){
                                                $caja = 'OCTAVA';
                                            }

                                            $j->where(DB::raw("UPPER(emp.nombre)"),'like','%'.$caja.'%');

                                        })->join('detalle_especificacionempaque as det_esp_emp',function($j) use($det, $variedad){

                                            $j->on('esp_emp.id_especificacion_empaque','det_esp_emp.id_especificacion_empaque')
                                            ->where('det_esp_emp.tallos_x_ramos',trim($det['S']))
                                            ->where('det_esp_emp.longitud_ramo',substr(trim($det['N']),0,2))
                                            ->where('det_esp_emp.id_variedad',$variedad->id_variedad)
                                            ->where('det_esp_emp.cantidad',trim($det['R']));

                                        })->join('variedad as v',function($j) use($planta){

                                            $j->on('det_esp_emp.id_variedad','v.id_variedad')
                                            ->where('v.id_planta',$planta->id_planta);

                                        })->where([
                                            //['cpe.estado',true],
                                            //['esp.estado',true],
                                            ['id_cliente',$cliente->id_cliente]
                                        ])->select(
                                            'esp_emp.id_empaque as id_caja',
                                            'det_esp_emp.id_empaque_p as id_presentacion',
                                            'esp.id_especificacion',
                                            'esp.estado',
                                            'cpe.estado as cpe_estado',
                                            'det_esp_emp.id_detalle_especificacionempaque',
                                            'det_esp_emp.id_variedad',
                                            'cpe.id_cliente_pedido_especificacion'
                                        )->first();

                                        if(isset($cpe)){

                                            if($cpe->estado == 0)
                                                Especificacion::where('id_especificacion',$cpe->id_especificacion)->update(['estado'=> 1]);

                                            if($cpe->cpe_estado == 0)
                                                ClientePedidoEspecificacion::where('id_cliente_pedido_especificacion',$cpe->id_cliente_pedido_especificacion)->update(['estado' => 1]);

                                            $pController = new PedidoController;

                                            $detallesPedido[]= [
                                                'cantidad' => trim($det['Q']),
                                                'id_cliente_pedido_especificacion' => $cpe->id_cliente_pedido_especificacion,
                                                'id_agencia_carga' => $agenciaCarga->id_agencia_carga,
                                                'precio' => trim($det['O']).';'.$cpe->id_detalle_especificacionempaque.'|'
                                            ];

                                            $arrVariedades[]= $cpe->id_variedad;

                                            $datosExportacion[] = [
                                                [
                                                    'valor' =>  (trim($det['F'])!= '' ? trim($det['F']) : null),
                                                    'id_dato_exportacion' => $dePO->id_dato_exportacion
                                                ]
                                            ];

                                        }else{

                                            $alertas.= 'El cliente '.$cliente->nombre.' con el código '.$idcliente.' No tiene la siguiente especificacion VARIEDAD '.$planta->nombre.' TIPO '.$variedad->nombre.' CAJA '.trim($det['P']).' RAMOS X CAJA '.trim($det['R']).' TALLOS X RAMO '.trim($det['S']).' LONGITUD '.trim($det['N']).' para el pedido de los '.$days[trim($dia)].' en la fila '.trim($det['V'])."\n\n";
                                            $z = false;
                                            //break 2;

                                        }

                                    }else{

                                        $alertas.= "No existe la variedad con el código ".trim($det['L'])." en la fila ".trim($det['V'])." para la panta ".trim($det['K'])."\n\n";
                                        // $this->error('No existe la variedad con el código '.trim($det['L'])."\n");
                                        $z = false;
                                        //break 2;

                                    }

                                }else{

                                    //$this->error('No existe la planta con el código '.trim($det['J'])."\n");
                                    $alertas.= 'No existe la planta con el código '.trim($det['J'])."\n\n";
                                    $z = false;
                                    //break 2;
                                }

                            }

                            if($z){

                                $dataPedido = [
                                    'fecha_de_entrega' => null,
                                    'id_cliente' => $cliente->id_cliente,
                                    'id_pedido' => null,
                                    'pedido_fijo' => '3',
                                    'opcion' => '3',
                                    'crear_envio' => 'true',
                                    'id_configuracion_empresa'=> 1,
                                    'factura_ficticia' => 'false',
                                    'tipo_pedido' => 'STANDING ORDER',
                                    'variedades' => $arrVariedades,
                                    'arrFechas' => $fechas,
                                    'arrDatosExportacion' => $datosExportacion,
                                    'arrDataDetallesPedido' => $detallesPedido,
                                    'id_consignatario'
                                ];

                                $consignatario = Consignatario::where('identificacion',trim($det['D']))->first();

                                if(isset($consignatario))
                                    $dataPedido['id_consignatario'] = $consignatario->id_consignatario;

                                $a= $pController->store_pedidos(new Request($dataPedido));

                                if(!$a['success'])
                                    throw new \Exception($a['mensaje']);

                            }

                        }else{

                            //$this->error('El cliente '.$cliente->nombre.' no tiene agencia de carga asignada'."\n");
                            $alertas.= "El cliente '.$cliente->nombre.' no tiene agencia de carga asignada\n\n";
                            break 2;

                        }

                    }else{

                        //$this->error('No existe el cliente con el código '.$idcliente."\n");
                        $alertas.= "No existe el cliente con el código ".$idcliente."\n\n";
                        break 2;

                    }

                }

                $bar->advance();

            }

            if($alertas !='')
                $this->error($alertas);

            DB::commit();
            $bar->finish();
            $this->info('✔ Pedidos creados ✔');

        }catch(\Exception $e){

            DB::rollback();
            $this->error($e->getMessage().' '.$e->getLine().' '.$e->getFile()."\n");

        }
    }
}
