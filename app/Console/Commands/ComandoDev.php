<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use yura\Modelos\Cama;
use yura\Modelos\ClienteConsignatario;
use yura\Modelos\ClientePedidoEspecificacion;
use yura\Modelos\CosechaDiaria;
use yura\Modelos\CostosSemana;
use yura\Modelos\DetalleEspecificacionEmpaque;
use yura\Modelos\DetallePedido;
use yura\Modelos\DetallePedidoDatoExportacion;
use yura\Modelos\Envio;
use yura\Modelos\Especificacion;
use yura\Modelos\EspecificacionEmpaque;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\Monitoreo;
use yura\Modelos\Notificacion;
use yura\Modelos\Pedido;
use yura\Modelos\PedidoConfirmacion;
use yura\Modelos\Producto;
use yura\Modelos\ProyeccionModulo;
use yura\Modelos\Semana;
use yura\Modelos\UserNotification;
use yura\Modelos\Variedad;
use \PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use yura\Modelos\CajaProyecto;
use yura\Modelos\CajaProyectoMarcacion;
use yura\Modelos\CuartoFrio;
use yura\Modelos\DetalleCajaProyecto;
use yura\Modelos\DetalleCliente;
use yura\Modelos\Especificaciones;
use yura\Modelos\InventarioFrio;
use yura\Modelos\Proyecto;

class ComandoDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comando:dev {comando} {desde=0} {hasta=0} {variedad=0} {modulo=0} {opcion=0} {planta=0} {empresa=0}';

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
        $comando = $this->argument('comando');
        if ($comando == 'nombre_productos') {
            $this->arreglar_nombre_productos();
        }
        if ($comando == 'cosecha_diaria') {
            $this->cosecha_diaria();
        }
        if ($comando == 'corregir_costos_insumos') {
            $this->corregir_costos_insumos();
        }
        if ($comando == 'eliminar_proyecciones_modulo_duplicadas') {
            $this->eliminar_proyecciones_modulo_duplicadas();
        }
        if ($comando == 'procesar_semanas') {
            $this->procesar_semanas();
        }
        if ($comando == 'copiar_semanas') {
            $this->copiar_semanas();
        }
        if ($comando == 'actualizar_pedido_confirmacion') {
            $this->actualizar_pedido_confirmacion();
        }
        if ($comando == 'corregir_packings') {
            $this->corregir_packings();
        }
        if ($comando == 'corregir_ordenes_fija') {
            $this->corregir_ordenes_fija();
        }
        if ($comando == 'crear_envios') {
            $this->crear_envios();
        }
        if ($comando == 'importar_historicos') {
            $this->importar_historicos();
        }
        if ($comando == 'copy_old_especificaciones') {
            $this->copy_old_especificaciones();
        }
        if ($comando == 'copy_old_pedidos') {
            $this->copy_old_pedidos();
        }
        if ($comando == 'copy_old_inventario_frio') {
            $this->copy_old_inventario_frio();
        }
        if ($comando == 'caca') {
            $this->caca();
        }
    }

    function arreglar_nombre_productos()
    {
        $productos = Producto::where('estado', 1)->get();
        foreach ($productos as $p) {
            $p->nombre = espacios(str_replace('(CC)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(UND)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(LTRS)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MTLN)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(KG)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ROLLO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(GR)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(TIRAS)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(LBS)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(GLN)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(SACO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(UN)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CAMA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ABLANDADOR)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ACETAMIPRID 200)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(BETAFOS)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(1050X341X334)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PARES)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PROCLORAZ 450)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(NO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MT3)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PROPA+METALAXYL)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CHLORPYRIFOS+CIPER)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CARTAP 500)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIAZINON 600)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIFLUBENZURON 480G)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(METHOMIL)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CAPTAN 80%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CARBENDAZIM 50%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CLOROTALONIL 720)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIFENOCONAZOLE 25%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIFECONAZOLE)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIFLUBENZURON 25%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ABONO ORGANICO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(FLUXAPIROXAD-PIRACLOSTROBINA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CARBOSULFAN)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(METHOMYLL 90%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PROCYMIDONE 500)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(TROQUELADA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(FURA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(BENFURACARB 400)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(FIPRONIL 20%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(380 OXADIAZON)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(FOSETYL ALUMINIO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ABAMECTINA 18)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CIROMAZINA 75% WP)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CAJA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CAJAFL)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(GLYPHOSATO 480)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PYRIPROXYFEN 10%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ACEFATO+IMIDACLOPRID)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(IMIDACLOPRID 700)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PROCHLORAZ)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MANCOZEB 800)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(IPRODIONE 500)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CARDENDAZIM 50%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MANCOZEB 80)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(430)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(8100)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MANCO640+META 80)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MACOZEB 800)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DIMETHOMORPH)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(A. FOLICO 4%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(RESMA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(FUNDA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PAECILOMYCES+LECANII)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(CARBENDAZIM 500)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PROPAMOCARB 722)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PYRIMETHANIL 400 SC)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(TRAJE DE CUARTOS FRIOS)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ACETAMINAPRID 200)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ACIDOS + ELE M)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ACIDOS + ELEM)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(INS. ORGANICO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(LUFENURON50G)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(ROSCADO)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(THIOCYCLAM 50%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PINK)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MAGENTA)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(MANCOZEB+METALAXYL)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(14.94%)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(DELTAMETRINA 25)', '', $p->nombre));
            $p->nombre = espacios(str_replace('(PYRIPROXYFEN 100)', '', $p->nombre));
            $p->nombre = espacios($p->nombre);
            $p->save();
            dump($p->id_producto . ': ' . $p->nombre);
        }
    }

    function cosecha_diaria()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "cosecha_diaria" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        if ($desde <= $hasta) {
            $item = $desde;
            $fechas = [];
            while ($item <= $hasta) {
                array_push($fechas, $item);
                $item = opDiasFecha('+', 1, $item);
            }

            foreach ($fechas as $pos_fecha => $fecha) {
                $variedades = DB::table('ciclo as c')
                    ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                    ->join('planta as p', 'p.id_planta', '=', 'v.id_planta')
                    ->select('c.id_variedad', 'v.id_planta', 'v.nombre as variedad_nombre', 'p.nombre as planta_nombre')->distinct()
                    ->where('c.estado', 1)
                    ->orderBy('p.nombre')
                    ->orderBy('v.nombre')
                    ->get();
                foreach ($variedades as $pos_var => $var) {
                    $model = CosechaDiaria::All()
                        ->where('fecha', $fecha)
                        ->where('id_variedad', $var->id_variedad)
                        ->first();
                    if ($model == '') {
                        $model = new CosechaDiaria();
                        $model->fecha = $fecha;
                        $model->id_variedad = $var->id_variedad;
                        $model->id_planta = $var->id_planta;
                        $model->variedad_nombre = $var->variedad_nombre;
                        $model->planta_nombre = $var->planta_nombre;
                    }
                    $cosechados = DB::table('desglose_recepcion as dr')
                        ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
                        ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
                        ->where('r.estado', 1)
                        ->where('dr.estado', 1)
                        ->where('dr.id_variedad', $var->id_variedad)
                        ->where('r.fecha_ingreso', 'like', $fecha . '%')
                        ->get()[0]->cantidad;
                    $model->cosechados = $cosechados > 0 ? $cosechados : 0;
                    $model->save();
                    dump('Fecha(' . $fecha . '): ' . ($pos_fecha + 1) . '/' . count($fechas)
                        . ' - Var(' . $var->id_variedad . '): ' . ($pos_var + 1) . '/' . count($variedades) . ' - cos: ' . $cosechados);
                }
            }
        }
    }

    function corregir_costos_insumos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "corregir_costos_insumos" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');

        $semanas = DB::table('semana')
            ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
            ->where('estado', 1)
            ->where('codigo', '>=', $desde)
            ->where('codigo', '<=', $hasta)
            ->get();

        $list_act_prod = DB::table('costos_semana')
            ->select('id_actividad_producto', DB::raw('count(*)'))->distinct()
            ->where('codigo_semana', '>=', $desde)
            ->where('codigo_semana', '<=', $hasta)
            ->groupBy('id_actividad_producto')
            ->having(DB::raw('count(*)'), '<', count($semanas))
            ->get();
        foreach ($list_act_prod as $pos_act_prod => $act_prod) {
            foreach ($semanas as $pos_sem => $sem) {
                $model = CostosSemana::All()
                    ->where('codigo_semana', $sem->codigo)
                    ->where('id_actividad_producto', $act_prod->id_actividad_producto)
                    ->first();
                if ($model == '') {
                    $model = new CostosSemana();
                    $model->id_actividad_producto = $act_prod->id_actividad_producto;
                    $model->codigo_semana = $sem->codigo;
                    $model->valor = 0;
                    $model->cantidad = 0;
                    $model->save();
                    dump('ap: ' . ($pos_act_prod + 1) . '/' . count($list_act_prod) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas) . ' *NEW*');
                } else
                    dump('ap: ' . ($pos_act_prod + 1) . '/' . count($list_act_prod) . ' - sem: ' . ($pos_sem + 1) . '/' . count($semanas));
            }
        }
    }

    function eliminar_proyecciones_modulo_duplicadas()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "eliminar_proyecciones_modulo_duplicadas" <<<<< ! >>>>>');
        $modulos = DB::table('proyeccion_modulo')
            ->select('id_modulo', DB::raw('count(*) as cant'))
            ->where('estado', 1)
            ->groupBy('id_modulo')
            ->having(DB::raw('count(*)'), '>', 1)
            ->orderBy('cant', 'desc')
            ->get();
        foreach ($modulos as $pos_m => $mod) {
            dump(porcentaje($pos_m + 1, count($modulos), 1) . '% - ' . ($pos_m + 1) . '/' . count($modulos));
            $proys = ProyeccionModulo::where('id_modulo', $mod->id_modulo)
                ->orderBy('fecha_inicio', 'desc')
                ->get();
            foreach ($proys as $pos_p => $p) {
                if ($pos_p > 0)
                    $p->delete();
            }
        }
    }

    function procesar_semanas()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "procesar_semanas" <<<<< ! >>>>>');
        $desde = $this->argument('desde');
        $hasta = $this->argument('hasta');
        $anno = $this->argument('empresa');
        $variedad = $this->argument('variedad');

        $existe = Semana::All()->where('anno', '=', $anno)
            ->where('id_variedad', '=', $variedad)
            ->first();
        if ($existe == '') {
            if ($desde < $hasta) {
                /* =========================== OBTENER LAS SEMANAS =======================*/
                $arreglo = [];
                $inicio = $desde;
                $fin = strtotime('+6 day', strtotime($inicio));
                $fin = date('Y-m-d', $fin);

                array_push($arreglo, [
                    'inicio' => $inicio,
                    'fin' => $fin
                ]);

                $inicio = strtotime('+1 day', strtotime($fin));
                $inicio = date('Y-m-j', $inicio);

                while ($inicio < $hasta) {
                    if (existInSemana($inicio, $variedad, $anno) && existInSemana($fin, $variedad, $anno)) {
                        $fin = strtotime('+6 day', strtotime($inicio));
                        $fin = date('Y-m-d', $fin);

                        array_push($arreglo, [
                            'inicio' => $inicio,
                            'fin' => $fin
                        ]);

                        $inicio = strtotime('+1 day', strtotime($fin));
                        $inicio = date('Y-m-d', $inicio);
                    } else {
                        dd('El rango indicado incluye al menos una fecha que ya está registrada');
                        break;
                    }
                }
                /* =========================== VERIFICAR LA CANTIDAD DE SEMANAS EN UN AÑO =======================*/
                if (count($arreglo) >= 52 && count($arreglo) <= 53) {
                    /* =========================== GRABAR EN LA BASE LAS SEMANAS =======================*/
                    for ($i = 0; $i < count($arreglo); $i++) {
                        dump('sem: ' . ($i + 1) . '/' . count($arreglo));
                        $model = new Semana();
                        $model->id_variedad = $variedad;
                        $model->anno = $anno;
                        $pref = ($i + 1) < 10 ? '0' : '';
                        $model->codigo = substr($anno, 2) . $pref . ($i + 1);
                        $model->fecha_inicial = $arreglo[$i]['inicio'];
                        $model->fecha_final = $arreglo[$i]['fin'];
                        $model->save();
                    }
                } else {
                    dd('No se ha cumplido el rango de 52-53 semanas de un año en el rango indicado');
                }
            } else {
                dd('<div class="text-center alert alert-danger">La fecha inicial debe ser menor que la final');
            }
        } else {
            dd('Ya existe una programación para esta variedad en el año ' . $anno);
        }
    }

    function copiar_semanas()
    {
        $opcion = $this->argument('opcion');
        if ($opcion == 0)
            dump('<<<<< ! >>>>> Ejecutando comando:dev "copiar_semanas" <<<<< ! >>>>>');
        $anno = $this->argument('desde');
        $variedad = $this->argument('variedad');
        $only_planta = $this->argument('planta');
        $semanas = Semana::where('estado', 1)
            ->where('id_variedad', $variedad)
            ->where('anno', $anno)
            ->get();
        if (count($semanas) > 0) {
            $variedad_par = Variedad::find($variedad);
            $variedades = getVariedades();
            foreach ($variedades as $pos_v => $var) {
                $sem_var = Semana::where('estado', 1)
                    ->where('id_variedad', $var->id_variedad)
                    ->where('anno', $anno)
                    ->first();
                if ($sem_var == '')
                    if ($var->id_planta == $variedad_par->id_planta || $only_planta == 'x')
                        foreach ($semanas as $pos_s => $sem) {
                            if ($opcion == 0)
                                dump('var: ' . ($pos_v + 1) . '/' . count($variedades) . ' - sem: ' . ($pos_s + 1) . '/' . count($semanas));
                            $new = new Semana();
                            $new->id_variedad = $var->id_variedad;
                            $new->anno = $sem->anno;
                            $new->codigo = $sem->codigo;
                            $new->fecha_inicial = $sem->fecha_inicial;
                            $new->fecha_final = $sem->fecha_final;
                            $new->curva = $sem->curva;
                            $new->desecho = $sem->desecho;
                            $new->semana_poda = $sem->semana_poda;
                            $new->semana_siembra = $sem->semana_siembra;
                            $new->tallos_planta_siembra = $sem->tallos_planta_siembra;
                            $new->tallos_planta_poda = $sem->tallos_planta_poda;
                            $new->tallos_ramo_siembra = $sem->tallos_ramo_siembra;
                            $new->tallos_ramo_poda = $sem->tallos_ramo_poda;
                            $new->mes = $sem->mes;
                            $new->save();
                        }
            }
            if ($opcion == 0)
                dd('Se han copiado las semanas satisfactoriamente');
        } else {
            if ($opcion == 0)
                dd('La variedad no tiene semanas ingresadas para el aÃ±o indicado');
        }
    }

    function actualizar_pedido_confirmacion()
    {
        $fecha = $this->argument('desde');

        $fechas = DB::table('pedido as p')
            ->select(
                'p.fecha_pedido',
            )->distinct()
            ->where('p.estado', '=', 1)
            ->where('p.fecha_pedido', '>=', $fecha)
            ->where('p.fecha_pedido', '<=', opDiasFecha('+', 15, $fecha))
            ->get();

        foreach ($fechas as $pos_f => $f) {
            $plantas = DB::table('pedido as p')
                ->join('detalle_pedido as dp', 'dp.id_pedido', '=', 'p.id_pedido')
                ->join('cliente_pedido_especificacion as cpe', 'cpe.id_cliente_pedido_especificacion', '=', 'dp.id_cliente_especificacion')
                ->join('especificacion_empaque as ee', 'ee.id_especificacion', '=', 'cpe.id_especificacion')
                ->join('detalle_especificacionempaque as dee', 'dee.id_especificacion_empaque', '=', 'ee.id_especificacion_empaque')
                ->join('variedad as v', 'v.id_variedad', '=', 'dee.id_variedad')
                ->select(
                    'v.id_planta',
                )->distinct()
                ->where('p.estado', '=', 1)
                ->where('p.fecha_pedido', $f->fecha_pedido)
                ->get();
            $ids_plantas = [];
            foreach ($plantas as $pos_p => $p) {
                dump('fecha: ' . $pos_f . '/' . count($fechas) . '; planta: ' . ($pos_p + 1) . '/' . count($plantas));
                $model = PedidoConfirmacion::All()
                    ->where('id_planta', $p->id_planta)
                    ->where('fecha', $f->fecha_pedido)
                    ->first();
                if ($model == '') {
                    $model = new PedidoConfirmacion();
                    $model->fecha = $f->fecha_pedido;
                    $model->id_planta = $p->id_planta;
                    $model->ejecutado = 0;
                    $model->save();
                }
                $ids_plantas[] = $p->id_planta;
            }

            $delete_query = PedidoConfirmacion::where('fecha', $f->fecha_pedido)
                ->whereNotIn('id_planta', $ids_plantas)
                ->get();
            foreach ($delete_query as $pos_d => $del) {
                dump('fecha: ' . $pos_f . '/' . count($fechas) . '; del: ' . ($pos_d + 1) . '/' . count($delete_query));
                $del->delete();
            }
        }
    }

    function corregir_packings()
    {
        $query = DB::table('pedido')
            ->select(
                DB::raw('count(*)'),
                DB::raw('group_concat(packing)'),
                DB::raw('group_concat(id_pedido) as ids_pedido'),
                DB::raw('group_concat(fecha_pedido)'),
            )
            //->where('fecha_pedido', '>=', hoy())
            ->groupBy('packing')
            ->having('count(*)', '>', 1)
            ->get();
        $empresa = getConfiguracionEmpresa();
        foreach ($query as $pos_q => $q) {
            $ids_pedidos = explode(',', $q->ids_pedido);
            foreach ($ids_pedidos as $pos_pedido => $p) {
                dump('Corrigiendo pedido: ' . ($pos_pedido + 1) . '/' . count($ids_pedidos) . '; query: ' . ($pos_q + 1) . '/' . count($query));
                $empresa->numero_packing += 1;
                $pedido = Pedido::find($p);
                $pedido->packing = $empresa->numero_packing;
                //$pedido->save();
            }
        }
        $empresa->save();
    }

    function corregir_ordenes_fija()
    {
        $fecha = $this->argument('desde');
        $pedido_par = $this->argument('hasta');
        $dias_par = $this->argument('variedad');
        $dias_par == 0 ? 365 : $dias_par;
        try {
            $pedidos = Pedido::where('tipo_pedido', 'STANDING ORDER')
                ->where('fecha_pedido', $fecha);
            if ($pedido_par != 0)
                $pedidos = $pedidos->where('id_pedido', $pedido_par);
            $pedidos = $pedidos->get();

            $fecha_hasta = opDiasFecha('+', $dias_par, $fecha);
            $getDiaSemanaByFecha = getDiaSemanaByFecha($fecha);
            $fechas = [];
            while ($fecha <= $fecha_hasta) {
                if ($getDiaSemanaByFecha == getDiaSemanaByFecha($fecha) && $fecha != $this->argument('desde')) {
                    $fechas[] = $fecha;
                }
                $fecha = opDiasFecha('+', 1, $fecha);
            }

            foreach ($pedidos as $pos_ped => $pedOriginal) {
                /*$last_ordeFija = Pedido::orderBy('orden_fija', 'desc')->first();
                $pedOriginal->orden_fija = isset($last_ordeFija->orden_fija) ? $last_ordeFija->orden_fija + 1 : 1;
                $pedOriginal->save();*/
                dump('id_pedOriginal: ' . $pedOriginal->id_pedido);
                $envios = $pedOriginal->envios;
                if (count($envios) == 0) {
                    dump('Creando ENVIO');
                    $consignatario = ClienteConsignatario::All()
                        ->where('id_cliente', $pedOriginal->id_cliente)
                        ->first();
                    $consignatario = $consignatario != '' ? $consignatario->id_consignatario : '';
                    $last_env = Envio::orderBy('id_envio', 'desc')->first();
                    $envio = new Envio();
                    $envio->id_envio = isset($last_env->id_envio) ? $last_env->id_envio + 1 : 1;
                    $envio->fecha_envio = $pedOriginal->fecha_pedido;
                    $envio->id_pedido = $pedOriginal->id_pedido;
                    $envio->id_consignatario = $consignatario;
                    $envio->save();
                } else
                    $consignatario = $envios[0]->id_consignatario;
                foreach ($fechas as $pos_f => $fecha) {
                    $existe_pedido = Pedido::All()
                        ->where('tipo_pedido', 'STANDING ORDER')
                        ->where('fecha_pedido', $fecha)
                        ->where('orden_fija', $pedOriginal->orden_fija)
                        ->first();
                    if ($existe_pedido == '') {
                        DB::beginTransaction();
                        dump('Pedido: ' . ($pos_ped + 1) . '/' . count($pedidos) . ' - fecha: ' . $fecha . ' - ' . ($pos_f + 1) . '/' . count($fechas));

                        dump('Creando PEDIDO');
                        $ped = new Pedido();
                        $last_ped = Pedido::orderBy('id_pedido', 'desc')->first();
                        $ped->id_pedido = isset($last_ped->id_pedido) ? $last_ped->id_pedido + 1 : 1;
                        $ped->id_cliente = $pedOriginal->id_cliente;
                        $ped->descripcion = $pedOriginal->descripcion;
                        $ped->tipo_pedido = $pedOriginal->tipo_pedido;
                        $ped->fecha_pedido = $fecha;
                        $ped->id_configuracion_empresa = $pedOriginal->id_configuracion_empresa;
                        $ped->variedad = '';
                        $last_packing = Pedido::orderBy('packing', 'desc')->first();
                        $ped->packing = isset($last_packing->packing) ? $last_packing->packing + 1 : 1;
                        $ped->orden_fija = $pedOriginal->orden_fija;
                        $ped->save();
                        $ped = Pedido::All()->last();

                        $last_env = Envio::orderBy('id_envio', 'desc')->first();
                        $envio = new Envio();
                        $envio->id_envio = isset($last_env->id_envio) ? $last_env->id_envio + 1 : 1;
                        $envio->fecha_envio = $ped->fecha_pedido;
                        $envio->id_pedido = $ped->id_pedido;
                        $envio->id_consignatario = $consignatario;
                        $envio->save();

                        foreach ($pedOriginal->detalles as $pos_det => $detOriginal) {
                            $det_expOriginales = $detOriginal->detalle_pedido_dato_exportacion;
                            $cli_ped_espOriginal = $detOriginal->cliente_especificacion;
                            $espOriginal = $cli_ped_espOriginal->especificacion;
                            $esp_empOriginal = $espOriginal->especificacionesEmpaque[0];

                            dump('Procesando DETALLE ' . ($pos_det + 1) . '/' . count($pedOriginal->detalles));
                            dump('Creando ESPECIFICACION');
                            $esp = new Especificacion();
                            $last_esp = Especificacion::orderBy('id_especificacion', 'desc')->first();
                            $esp->id_especificacion = isset($last_esp->id_especificacion) ? $last_esp->id_especificacion + 1 : 1;
                            $esp->estado = 1;
                            $esp->tipo = $espOriginal->tipo;
                            $esp->creada = 'EJECUCION';
                            $esp->save();
                            $esp = Especificacion::All()->last();

                            dump('Creando ESPECIFICACION_EMPAQUE');
                            $esp_emp = new EspecificacionEmpaque();
                            $espEmpaque = EspecificacionEmpaque::orderBy('id_especificacion_empaque', 'desc')->first();
                            $esp_emp->id_especificacion_empaque = isset($espEmpaque->id_especificacion_empaque) ? $espEmpaque->id_especificacion_empaque + 1 : 1;
                            $esp_emp->id_especificacion = $esp->id_especificacion;
                            $esp_emp->id_empaque = $esp_empOriginal->id_empaque;
                            $esp_emp->cantidad = 1;
                            $esp_emp->save();
                            $esp_emp = EspecificacionEmpaque::All()->last();

                            dump('Creando CLIENTE_PEDIDO_ESPECIFICACION');
                            $cli_ped = new ClientePedidoEspecificacion();
                            $last_cli_ped = ClientePedidoEspecificacion::orderBy('id_cliente_pedido_especificacion', 'desc')->first();
                            $cli_ped->id_cliente_pedido_especificacion = isset($last_cli_ped->id_cliente_pedido_especificacion) ? $last_cli_ped->id_cliente_pedido_especificacion + 1 : 1;
                            $cli_ped->id_especificacion = $esp->id_especificacion;
                            $cli_ped->id_cliente = $cli_ped_espOriginal->id_cliente;
                            $cli_ped->estado = 1;
                            $cli_ped->save();
                            $cli_ped = ClientePedidoEspecificacion::All()->last();

                            $precio = '';
                            foreach ($esp_empOriginal->detalles as $pos_det_esp => $det_espOriginal) {
                                dump('Procesando DETALLE_ESPECIFICACION ' . ($pos_det_esp + 1) . '/' . count($esp_empOriginal->detalles));
                                dump('Creando DETALLE_ESPECIFICACION');
                                $det_esp = new DetalleEspecificacionEmpaque();
                                $last_det_esp = DetalleEspecificacionEmpaque::orderBy('id_detalle_especificacionempaque', 'desc')->first();
                                $det_esp->id_detalle_especificacionempaque = isset($last_det_esp->id_detalle_especificacionempaque) ? $last_det_esp->id_detalle_especificacionempaque + 1 : 1;
                                $det_esp->id_especificacion_empaque = $esp_emp->id_especificacion_empaque;
                                $det_esp->id_variedad = $det_espOriginal->id_variedad;
                                $det_esp->id_clasificacion_ramo = $det_espOriginal->id_clasificacion_ramo;
                                $det_esp->cantidad = $det_espOriginal->cantidad;
                                $det_esp->id_empaque_p = $det_espOriginal->id_empaque_p;
                                $det_esp->tallos_x_ramos = $det_espOriginal->tallos_x_ramos;
                                $det_esp->longitud_ramo = $det_espOriginal->longitud_ramo;
                                $det_esp->id_unidad_medida = $det_espOriginal->id_unidad_medida;
                                $det_esp->save();
                                $det_esp = DetalleEspecificacionEmpaque::All()->last();

                                $p = getPrecioByDetEsp($detOriginal->precio, $det_espOriginal->id_detalle_especificacionempaque);
                                if ($pos_det_esp == 0) {
                                    $precio = $p . ';' . $det_esp->id_detalle_especificacionempaque;
                                } else {
                                    $precio .= '|' . $p . ';' . $det_esp->id_detalle_especificacionempaque;
                                }
                            }

                            dump('Creando DETALLE_PEDIDO');
                            $det_ped = new DetallePedido();
                            $last_cli_ped = DetallePedido::orderBy('id_detalle_pedido', 'desc')->first();
                            $det_ped->id_detalle_pedido = isset($last_cli_ped->id_detalle_pedido) ? $last_cli_ped->id_detalle_pedido + 1 : 1;
                            $det_ped->id_pedido = $ped->id_pedido;
                            $det_ped->id_cliente_especificacion = $cli_ped->id_cliente_pedido_especificacion;
                            $det_ped->id_agencia_carga = $detOriginal->id_agencia_carga;
                            $det_ped->cantidad = $detOriginal->cantidad;
                            $det_ped->orden = $detOriginal->orden;
                            $det_ped->precio = $precio;
                            $det_ped->estado = 1;
                            $det_ped->save();
                            $det_ped = DetallePedido::All()->last();

                            foreach ($det_expOriginales as $pos_det_exp => $det_expOriginal) {
                                dump('Procesando DETALLE_PEDIDO_DATO_EXPORTACION ' . ($pos_det_exp + 1) . '/' . count($det_expOriginales));
                                dump('Creando DETALLE_PEDIDO_DATO_EXPORTACION');
                                $det_ped_exp = new DetallePedidoDatoExportacion();
                                $last_det_ped_exp = DetallePedidoDatoExportacion::orderBy('id_detallepedido_datoexportacion', 'desc')->first();
                                $det_ped_exp->id_detallepedido_datoexportacion = isset($last_det_ped_exp->id_detallepedido_datoexportacion) ? $last_det_ped_exp->id_detallepedido_datoexportacion + 1 : 1;
                                $det_ped_exp->id_detalle_pedido = $det_ped->id_detalle_pedido;
                                $det_ped_exp->id_dato_exportacion = $det_expOriginal->id_dato_exportacion;
                                $det_ped_exp->valor = $det_expOriginal->valor;
                                $det_ped_exp->save();
                            }
                        }

                        DB::commit();
                        dump('------------------ PEDIDO del cliente "' . $ped->cliente->detalle()->nombre . '" CREADO con fecha ' . $fecha . ' --------------------');
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    function crear_envios()
    {
        $fecha = $this->argument('desde');
        try {
            $pedidos = Pedido::where('tipo_pedido', 'STANDING ORDER')
                ->where('fecha_pedido', '>=', $fecha);
            $pedidos = $pedidos->get();

            foreach ($pedidos as $pos_ped => $pedOriginal) {
                dump(($pos_ped + 1) . '/' . count($pedidos));
                $envios = $pedOriginal->envios;
                if (count($envios) == 0) {
                    dump('CREANDO ENVIO al PEDIDO: (packing)' . $pedOriginal->packing);
                    $consignatario = ClienteConsignatario::All()
                        ->where('id_cliente', $pedOriginal->id_cliente)
                        ->first();
                    $consignatario = $consignatario != '' ? $consignatario->id_consignatario : '';
                    $last_env = Envio::orderBy('id_envio', 'desc')->first();
                    $envio = new Envio();
                    $envio->id_envio = isset($last_env->id_envio) ? $last_env->id_envio + 1 : 1;
                    $envio->fecha_envio = $pedOriginal->fecha_pedido;
                    $envio->id_pedido = $pedOriginal->id_pedido;
                    $envio->id_consignatario = $consignatario;
                    $envio->save();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    function importar_historicos()
    {
        dump('<<<<< ! >>>>> Ejecutando comando:dev "importar_historicos" <<<<< ! >>>>>');
        try {
            $url = public_path('storage/pdf_loads/historico_venta.xlsx');
            $document = IOFactory::load($url);
            $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

            $variedades_fails = [];
            $clientes_fails = [];
            foreach ($activeSheetData as $pos_row => $row) {
                if ($row['A'] != '' && $pos_row > 1) {
                    dump('row: ' . $pos_row . '/' . count($activeSheetData));
                    if ($row['L'] != '') {
                        $mes = explode('/', $row['A'])[0];
                        $mes = strlen($mes) == 1 ? '0' . $mes : $mes;
                        $dia = explode('/', $row['A'])[1];
                        $dia = strlen($dia) == 1 ? '0' . $dia : $dia;
                        $anno = explode('/', $row['A'])[2];
                        $fecha = $anno . '-' . $mes . '-' . $dia;
                        $cliente = DetalleCliente::All()
                            ->where('estado', 1)
                            ->where('ruc', $row['B'])
                            ->first();
                        $variedad = Variedad::join('planta as p', 'p.id_planta', '=', 'variedad.id_planta')
                            ->select('variedad.*')->distinct()
                            ->where('p.siglas', espacios($row['D']))
                            ->where('variedad.siglas', $row['F'])
                            ->get()
                            ->first();
                        if ($cliente != '') {
                            if ($variedad != '') {
                                $model = HistoricoVentas::All()
                                    ->where('fecha', $fecha)
                                    ->where('id_cliente', $cliente->id_cliente)
                                    ->where('id_variedad', $variedad->id_variedad)
                                    ->where('ramos_x_caja', 1)
                                    ->where('longitud_ramo', $row['L'])
                                    ->first();
                                $dinero = str_replace(',', '', espacios(str_replace('$', '', $row['H'])));
                                $dinero = str_replace('-', '', $dinero);
                                if ($model == '') {
                                    $model = new HistoricoVentas();
                                    $model->fecha = $fecha;
                                    $model->id_cliente = $cliente->id_cliente;
                                    $model->id_variedad = $variedad->id_variedad;
                                    $model->ramos_x_caja = 1;
                                    $model->longitud_ramo = $row['L'];
                                    $model->mes = $mes;
                                    $model->anno = $anno;
                                    $model->semana = getSemanaByDate($fecha)->codigo;
                                    $model->dinero = $dinero != '' && is_numeric($dinero) ? $dinero : 0;
                                    $model->ramos = $row['I'];
                                    $model->tallos = $row['J'];
                                    $model->save();
                                } else {
                                    $model->dinero += $dinero != '' && is_numeric($dinero) ? $dinero : 0;
                                    $model->ramos += $row['I'];
                                    $model->tallos += $row['J'];
                                    $model->save();
                                }
                                dump('STORE');
                            } else {
                                if (!in_array([
                                    'planta' => $row['E'],
                                    'color' => $row['F']
                                ], $variedades_fails))
                                    $variedades_fails[] = [
                                        'planta' => $row['E'],
                                        'color' => $row['F']
                                    ];
                            }
                        } else {
                            $clientes_fails[] = 'fila: ' . $pos_row . ' - ' . $row['B'];
                        }
                    }
                }
            }
            dd($clientes_fails, $variedades_fails);
            //unlink($url);
        } catch (\Exception $e) {
            dump('************************* ERROR *************************');
            dump($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    function copy_old_especificaciones()
    {
        $query = DB::table('cliente_pedido_especificacion as ce')
            ->join('especificacion as e', 'e.id_especificacion', '=', 'ce.id_especificacion')
            ->select('ce.id_cliente', 'ce.id_especificacion')->distinct()
            ->where('e.creada', 'PRE-ESTABLECIDA')
            ->where('e.estado', 1)
            //->where('ce.id_cliente', 252)
            ->get();
        foreach ($query as $pos_q => $q) {
            $especificacion = Especificacion::find($q->id_especificacion);
            $pos_det = 0;
            foreach ($especificacion->especificacionesEmpaque as $esp_emp) {
                foreach ($esp_emp->detalles as $det_esp) {
                    $variedad = $det_esp->variedad;
                    if ($variedad->estado == 1) {
                        $pos_det++;
                        dump('query: ' . $pos_q . '/' . count($query) . '; det: ' . $pos_det);
                        if ($det_esp->longitud_ramo != '' && $det_esp->tallos_x_ramos != '') {
                            $model = new Especificaciones();
                            $model->id_planta = $det_esp->variedad->id_planta;
                            $model->id_empaque_c = $esp_emp->id_empaque;
                            $model->id_variedad = $det_esp->id_variedad;
                            $model->id_empaque_p = $det_esp->id_empaque_p;
                            $model->ramos_x_caja = $det_esp->cantidad;
                            $model->tallos_x_ramos = $det_esp->tallos_x_ramos;
                            $model->longitud_ramo = $det_esp->longitud_ramo;
                            $model->id_cliente = $q->id_cliente;
                            $model->save();
                        }
                    }
                }
            }
        }
    }

    function copy_old_pedidos()
    {
        try {
            DB::beginTransaction();
            $query = Pedido::where('fecha_pedido', '>=', hoy())
                ->get();
            foreach ($query as $pos_q => $pedOriginal) {
                dump('pedido: ' . $pos_q . '/' . count($query));
                $envios = $pedOriginal->envios;
                $envio_0 = count($envios) > 0 ? $envios[0] : '';
                $detalles = $pedOriginal->detalles;
                $detalle_0 = count($detalles) > 0 ? $detalles[0] : '';
                if ($envio_0 != '' && $detalle_0 != '') {
                    $consignatario = $envio_0->id_consignatario;
                    $agencia = $detalle_0->id_agencia_carga;

                    // NUEVO PROYECTO
                    $proyecto = new Proyecto();
                    $proyecto->id_cliente = $pedOriginal->id_cliente;
                    $proyecto->packing = $pedOriginal->packing;
                    $proyecto->orden_fija = $pedOriginal->orden_fija;
                    $proyecto->fecha = $pedOriginal->fecha_pedido;
                    $proyecto->tipo = $pedOriginal->tipo_pedido == 'OPEN MARKET' ? 'OM' : 'SO';
                    $proyecto->id_consignatario = $consignatario;
                    $proyecto->id_agencia_carga = $agencia;
                    $proyecto->save();
                    $proyecto->id_proyecto = DB::table('proyecto')
                        ->select(DB::raw('max(id_proyecto) as id'))
                        ->get()[0]->id;

                    foreach ($pedOriginal->detalles as $detOriginal) {
                        $det_expOriginales = $detOriginal->detalle_pedido_dato_exportacion;
                        $cli_ped_espOriginal = $detOriginal->cliente_especificacion;
                        $espOriginal = $cli_ped_espOriginal->especificacion;
                        foreach ($espOriginal->especificacionesEmpaque as $esp_empOriginal) {
                            // NUEVA CAJA PROYECTO
                            $caja = new CajaProyecto();
                            $caja->id_proyecto = $proyecto->id_proyecto;
                            $caja->cantidad = $detOriginal->cantidad;
                            $caja->id_empaque = $esp_empOriginal->id_empaque;
                            $caja->save();
                            $caja->id_caja_proyecto = DB::table('caja_proyecto')
                                ->select(DB::raw('max(id_caja_proyecto) as id'))
                                ->get()[0]->id;

                            foreach ($esp_empOriginal->detalles as $det_espOriginal) {
                                $p = getPrecioByDetEsp($detOriginal->precio, $det_espOriginal->id_detalle_especificacionempaque);
                                // NUEVO DETALLE CAJA PROYECTO
                                $detalle = new DetalleCajaProyecto();
                                $detalle->id_caja_proyecto = $caja->id_caja_proyecto;
                                $detalle->id_variedad = $det_espOriginal->id_variedad;
                                $detalle->id_empaque = $det_espOriginal->id_empaque_p;
                                $detalle->ramos_x_caja = $det_espOriginal->cantidad;
                                $detalle->tallos_x_ramo = $det_espOriginal->tallos_x_ramos;
                                $detalle->precio = $p;
                                $detalle->longitud_ramo = $det_espOriginal->longitud_ramo;
                                $detalle->save();
                            }
                            foreach ($det_expOriginales as $marcacion) {
                                // NUEVA CAJA PROYECTO MARCACION
                                if ($marcacion->valor != '') {
                                    $caja_marcacion = new CajaProyectoMarcacion();
                                    $caja_marcacion->id_caja_proyecto = $caja->id_caja_proyecto;
                                    $caja_marcacion->id_dato_exportacion = $marcacion->id_dato_exportacion;
                                    $caja_marcacion->valor = $marcacion->valor;
                                    $caja_marcacion->save();
                                }
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            //echo $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
        }
    }

    function copy_old_inventario_frio()
    {
        $inventarios = InventarioFrio::where('disponibles', '>', 0)
            ->get();
        foreach ($inventarios as $pos => $inv) {
            dump('Inventario: ' . $pos . ' / ' . count($inventarios));
            $model = new CuartoFrio();
            $model->id_variedad = $inv->id_variedad;
            $model->id_empaque = $inv->id_empaque_p;
            $model->tallos_x_ramo = $inv->tallos_x_ramo;
            $model->longitud_ramo = $inv->longitud_ramo;
            $model->cantidad = $inv->cantidad;
            $model->fecha = $inv->fecha_ingreso;
            $model->disponibles = $inv->disponibles;
            $model->id_dato_exportacion = $inv->id_dato_exportacion;
            $model->valor_marcacion = $inv->valor_marcacion;
            $model->save();
        }
    }

    function caca()
    {
        $fechas = DB::table('proyecto')
            ->select('fecha')
            ->distinct()
            ->where('fecha', '>=', '2026-01-01')
            ->orderBy('fecha')
            ->get()->pluck('fecha')->toArray();
        //DB::select('delete from resumen_cosecha_estimada where fecha >= "2026-01-01"');
        foreach ($fechas as $pos_f => $fecha) {
            dump('fecha: ' . ($pos_f + 1) . '/' . count($fechas));
            Artisan::call('update:cosecha_estimada', [
                'variedad' => 0,
                'longitud' => 0,
                'fecha' => $fecha,
            ]);
        }
    }
}
