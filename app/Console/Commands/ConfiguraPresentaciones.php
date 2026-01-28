<?php

namespace yura\Console\Commands;

use DB;
use Illuminate\Console\Command;
use PHPExcel_IOFactory;
use yura\Modelos\Cliente;
use yura\Modelos\DetalleCliente;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetalleEspecificacionEmpaqueProducto;
use yura\Modelos\Empaque;
use yura\Modelos\Especificacion;
use yura\Modelos\Planta;
use yura\Modelos\Producto;
use yura\Modelos\ProductoBodega;
use yura\Modelos\Variedad;

class ConfiguraPresentaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'configura:presentaciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $document = PHPExcel_IOFactory::load('public/presentaciones_cliente.xlsx');
        $arrData= $document->getActiveSheet()->toArray(null, true, true, true);
        DB::beginTransaction();

        try{

            $productos = [];

            $especificacionClienteEspecial = [];
            $especificacionClienteNintanga = [];
            $especificacionClienteNoNintaga= [];

            for($i=5; $i <= count($arrData); $i++){

                //INSUMOS
                if(!in_array($arrData[$i]['E'], array_column($productos,'codigo_jire'))){

                    $productos[] = [
                        'codigo_jire' => strtoupper(trim($arrData[$i]['E'])),
                        'nombre' => strtoupper(str_replace(['"'],'',trim($arrData[$i]['F']))),
                        'aplicacion' => strtoupper(str_replace([' ','#','X'],'',trim($arrData[$i]['H']))),
                        'precio' => 0,
                        'estado'=> true
                    ];

                }

                if($arrData[$i]['J'] != ''){
                    $caja = !isset(explode('-',$arrData[$i]['J'])[1]) ? explode('-',$arrData[$i]['J'])[0] : explode('-',$arrData[$i]['J'])[1];
                    $presentacion = $arrData[$i]['I'];
                }

                if($arrData[$i]['A'] != ''){
                                                      //CLIENTE           PLANTA
                    $especificacionClienteEspecial[$arrData[$i]['A']][$arrData[$i]['C']][$caja] = [
                        'presentacion' =>  $presentacion,
                        'producto' => strtoupper(str_replace(['"'],'',trim($arrData[$i]['F']))),
                        'cantidad' => $arrData[$i]['G']
                    ];

                }else if($arrData[$i]['A']== ''){
                                                        //PLANTA     //CAJA
                    $especificacionClienteNintanga[$arrData[$i]['C']][$caja] = [
                        'presentacion' =>  $presentacion,
                        'producto' => strtoupper(str_replace(['"'],'',trim($arrData[$i]['F']))),
                        'cantidad' => $arrData[$i]['G']
                    ];;

                }

            }

            //GUARDA INSUMOS
            $this->warn("\n\n Guardando insumos... \n");
            $bar = $this->output->createProgressBar(count($productos));

            foreach($productos as $p){

                $producto = Producto::All()->where('nombre',$p['nombre'])->first();

                if(!isset($producto))
                    $producto = new Producto;

                $producto->codigo_jire = $p['codigo_jire'];
                $producto->nombre = $p['nombre'];
                $producto->aplicacion = $p['aplicacion'];
                $producto->precio = $p['precio'];
                $producto->save();

                if(!isset($producto)){

                    $lastProducto = Producto::orderBy('id_producto','desc')->first();

                    $productoBodega = new ProductoBodega;
                    $productoBodega->id_producto = $lastProducto->id_producto;
                    $productoBodega->id_bodega=1;
                    $productoBodega->cantidad=0;
                    $productoBodega->save();

                }

                $bar->advance();

            }

            $bar->finish();
            $this->info("\n\n✔ Insumos guardados ✔\n");

            //PRESENTACIONES CLIENTES ESPECIALES
            $this->warn("\n\nCambiando nombre de presentaciones especiales...\n");
            $bar = $this->output->createProgressBar(count($especificacionClienteEspecial));
            //dd($especificacionClienteEspecial);

            foreach($especificacionClienteEspecial as $codigoJireCliente => $ec){

                $cliente = DetalleCliente::where('ruc', $codigoJireCliente)->first();

                if(isset($cliente)){

                    foreach($ec as $siglasPlanta => $cajaPresentacion){

                        foreach($cajaPresentacion as $cp => $presentacion){

                            $planta = Planta::where('siglas',$siglasPlanta)->first();

                            if(isset($planta)){

                                $idVariedades = Variedad::where('id_planta',$planta->id_planta)->pluck('id_variedad')->toArray();

                                if(count($idVariedades)){

                                    $detsEspEmp = Cliente::join('cliente_pedido_especificacion as cpe',function($j) use($cliente){
                                        $j->on('cliente.id_cliente','cpe.id_cliente')
                                        ->where('cpe.id_cliente',$cliente->id_cliente)->where('cpe.estado',true);
                                    })->join('especificacion as e',function($j) {
                                        $j->on('cpe.id_especificacion','e.id_especificacion')->where('e.estado',true);
                                    })->join('especificacion_empaque as ee',function($j){
                                        $j->on('e.id_especificacion','ee.id_especificacion')->where('ee.estado',true);
                                    })->join('detalle_especificacionempaque as dee',function($j){
                                        $j->on('ee.id_especificacion_empaque','dee.id_especificacion_empaque')->where('dee.estado',true);
                                    })->join('empaque as emp',function($j) use ($cp){
                                        $j->on('emp.id_empaque','ee.id_empaque')->where('emp.tipo','C')
                                        ->where(DB::raw("CASE WHEN emp.nombre like '%CUARTA%' THEN 'QB' WHEN emp.nombre like '%OCTAVA%' THEN 'EB' WHEN emp.nombre like '%TABACO%' THEN 'HB' END"), $cp);
                                    })->whereIn('dee.id_variedad',$idVariedades)
                                    ->where([
                                        ['cliente.estado',true],
                                        ['cliente.id_cliente',$cliente->id_cliente]
                                    ])->select('dee.id_detalle_especificacionempaque')->distinct()->get();

                                    $empaque = Empaque::where('tipo','P')->where('nombre',$presentacion['presentacion'])->first();

                                    if(!isset($empaque)){

                                        $e = Empaque::orderBy('id_empaque','desc')->first();
                                        $idEmpaque= isset($e) ? $e->id_empaque+1 : 1;
                                        $empaque = new Empaque;
                                        $empaque->id_empaque =$idEmpaque;
                                        $empaque->tipo= 'P';
                                        $empaque->estado =1;
                                        $empaque->nombre= $presentacion['presentacion'];
                                        $empaque->id_configuracion_empresa= 1;
                                        $empaque->save();

                                        $idPresentacion = $idEmpaque;

                                    }else{

                                        $idPresentacion = $empaque->id_empaque;

                                    }

                                    foreach($detsEspEmp as $detEspEmp){

                                        $dee = DetalleEspecificacionEmpaque::find($detEspEmp->id_detalle_especificacionempaque);
                                        $dee->id_empaque_p = $idPresentacion;
                                        $dee->save();

                                        $producto = Producto::where('nombre',$presentacion['producto'])->first();

                                        if(!isset($producto))
                                            throw new \Exception('no existe el producto '.$presentacion['producto']);

                                        DetalleEspecificacionEmpaqueProducto::create([
                                            'id_detalle_especificacionempaque' => $dee->especificacion_empaque->especificacion->id_especificacion,
                                            'id_producto' => $producto->id_producto,
                                            'cantidad' => $presentacion['cantidad']
                                        ]);

                                        $especificacionClienteNoNintaga[] = $dee->especificacion_empaque->especificacion->id_especificacion;

                                    }

                                }else{

                                }

                            }else{

                            }

                        }

                    }

                }else{


                }

                $bar->advance();

            }

            $bar->finish();
            $this->info("\n\n✔ Presentaciones especiales actualizadas ✔\n");

            //PRESENTACIONES CLIENTES NINTANGA
            $this->warn("\n\nCambiando nombre de presentaciones nintanga...\n");

            $bar = $this->output->createProgressBar(count($especificacionClienteNintanga));

            foreach($especificacionClienteNintanga as $siglasPlanta => $cajas){

                foreach($cajas as $caja => $presentacion){

                    $planta= Planta::where('siglas',$siglasPlanta)->first();
                    $idVariedades = $planta->variedades->pluck('id_variedad')->toArray();

                    $detsEspEmp= Especificacion::join('especificacion_empaque as ee', 'especificacion.id_especificacion', 'ee.id_especificacion')
                    ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', 'ee.id_especificacion_empaque')
                    ->join('empaque as emp',function($j) use ($caja){
                        $j->on('emp.id_empaque', 'ee.id_empaque')
                        ->where(DB::raw("UPPER(emp.nombre)"),'like','%NINTANGA%')
                        ->where(DB::raw("CASE WHEN emp.nombre like '%CUARTA%' THEN 'QB' WHEN emp.nombre like '%OCTAVA%' THEN 'EB' WHEN emp.nombre like '%TABACO%' THEN 'HB' END"), $caja);
                    })->whereIn('dee.id_variedad',$idVariedades)
                    ->whereNotIn('especificacion.id_especificacion',$especificacionClienteNoNintaga)
                    ->select('dee.id_detalle_especificacionempaque')->distinct()->get();

                    $empaque = Empaque::where('tipo','P')->where('nombre',$presentacion['presentacion'])->first();

                    if(!isset($empaque)){

                        $e = Empaque::orderBy('id_empaque','desc')->first();
                        $idEmpaque= isset($e) ? $e->id_empaque+1 : 1;
                        $empaque = new Empaque;
                        $empaque->id_empaque =$idEmpaque;
                        $empaque->tipo= 'P';
                        $empaque->estado =1;
                        $empaque->nombre= $presentacion['presentacion'];
                        $empaque->id_configuracion_empresa= 1;
                        $empaque->save();

                        $idPresentacion = $idEmpaque;

                    }else{

                        $idPresentacion = $empaque->id_empaque;

                    }

                    foreach($detsEspEmp as $detEspEmp){

                        $dee = DetalleEspecificacionEmpaque::find($detEspEmp->id_detalle_especificacionempaque);
                        $dee->id_empaque_p = $idPresentacion;
                        $dee->save();

                        $producto = Producto::where('nombre',$presentacion['producto'])->first();

                        if(!isset($producto))
                            throw new \Exception('no existe el producto '.$presentacion['producto']);

                        DetalleEspecificacionEmpaqueProducto::create([
                            'id_detalle_especificacionempaque' => $dee->especificacion_empaque->especificacion->id_especificacion,
                            'id_producto' => $producto->id_producto,
                            'cantidad' => $presentacion['cantidad']
                        ]);

                    }


                }

                $bar->advance();

            }

            $bar->finish();
            $this->info("\n\n✔ Presentaciones nintanga actualizadas ✔\n");

            DB::commit();

        }catch(\Exception $e){

            DB::rollback();
            $this->error($e->getMessage().' '.$e->getLine().' '.$e->getFile()."\n\n ".$e->getTraceAsString());

        }
    }
}
