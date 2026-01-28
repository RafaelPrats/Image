<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use yura\Http\Controllers\Indicadores\Area;
use yura\Http\Controllers\Indicadores\Campo;
use yura\Http\Controllers\Indicadores\Costos;
use yura\Http\Controllers\Indicadores\Postcosecha;
use yura\Http\Controllers\Indicadores\Venta;
use yura\Http\Controllers\Indicadores\Proyecciones;

class UpdateIndicador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicador:update {indicador=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commando para actualizar los indicadores de los reportes del sistema';

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
     * @argument D => dashboard
     * @argument DP => dashboard de proyección
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        dump('<<<<< ! >>>>> Ejecutando comando "indicador:update" <<<<< ! >>>>>');
        Log::info('<<<<< ! >>>>> Ejecutando comando "indicador:update" <<<<< ! >>>>>');

        $indicador_par = $this->argument('indicador');

        if ($indicador_par === '0' || $indicador_par === 'D1') {  // Calibre (-7 días)
            dump('INDICADOR: "Calibre (-7 dias)"');
            Postcosecha::calibre_7_dias_atras();
            Log::info('INDICADOR: "Calibre (-7 dias)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D2') {  // Tallos clasificados (-7 días)
            dump('INDICADOR: "Tallos clasificados (-7 dias)"');
            Postcosecha::tallos_clasificados_7_dias_atras();
            Log::info('INDICADOR: "Tallos clasificados (-7 dias)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D3' || $indicador_par === 'D4') {
            // Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)
            dump('INDICADOR: "Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)"');
            Venta::ventas_7_dias_atras();
            Log::info('INDICADOR: "Precio promedio por ramo (-7 días) - Dinero ingresado (-7 días)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D5' || $indicador_par === 'D6') { // Rendimiento (-7 días) - Desecho (-7 días)
            dump('INDICADOR: "Rendimiento (-7 días) - Desecho (-7 días)"');
            Postcosecha::rendimiento_desecho_7_dias_atras();
            Log::info('INDICADOR: "Rendimiento (-7 días) - Desecho (-7 días)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP1') {
            dump('INDICADOR: "Cajas cosechadas +4 semanas"');
            Proyecciones::sumCajasFuturas4Semanas();
            Log::info('INDICADOR: "Cajas cosechadas +4 semanas"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP2') {
            dump('INDICADOR: "Tallos cosechados +4 semanas"');
            Proyecciones::sumTallosFuturos4Semanas();
            Log::info('INDICADOR: "Tallos cosechados +4 semanas"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP3') {
            dump('INDICADOR: "Cajas vendidas a futuro +4 semanas"');
            Proyecciones::sumCajasVendidas();
            Log::info('INDICADOR: "Cajas vendidas a futuro +4 semanas"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP4') {
            dump('INDICADOR: "Dinero generado ventas a futuro +4 semanas"');
            Proyecciones::sumDineroGeneradoVentas();
            Log::info('INDICADOR: "Dinero generado ventas a futuro +4 semanas"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP5') {
            dump('INDICADOR: "Dinero generado ventas a futuro mes 1|mes 2|mes 3"');
            Proyecciones::proyeccionVentaFutura3Meses();
            Log::info('INDICADOR: "Dinero generado ventas a futuro mes 1|mes 2|mes 3"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP6') {
            dump('INDICADOR: "Tallos cosechados a futuro +1 semana"');
            Proyecciones::sumTallosCosechadosFuturo1Semana();
            Log::info('INDICADOR: "Tallos cosechados a futuro +1 semana"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP7') {
            dump('INDICADOR: "Cajas vendidas futuro +1 semana"');
            Proyecciones::sumCajasVendidasFuturas1Semana();
            Log::info('INDICADOR: "Cajas vendidas futuro +1 semana"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP8') {
            dump('INDICADOR: "Cajas cosechadas a futuro +1 semana"');
            Proyecciones::sumCajasCosechadasFuturas1Semana();
            Log::info('INDICADOR: "Cajas cosechadas a futuro +1 semana"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DP9') {
            dump('INDICADOR: "Dinero generado en ventas a futuro +1 semana"');
            Proyecciones::sumDineroGeneradoFuturo1Semana();
            Log::info('INDICADOR: "Dinero generado en ventas a futuro +1 semana"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D7') { // Área en producción (-4 meses)
            dump('INDICADOR: "Área en producción (-4 meses)"');
            Area::area_produccion_4_semanas_atras();
            Log::info('INDICADOR: "Área en producción (-4 meses)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DA1') { // Ciclo (-4 semanas)
            dump('INDICADOR: "Ciclo (-4 semanas)"');
            Area::ciclo_4_semanas_atras();
            Log::info('INDICADOR: "Ciclo (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D8') { // Ramos/m2/año (-4 meses)
            dump('INDICADOR: "Ramos/m2/año (-4 meses)"');
            Area::ramos_m2_anno_4_semanas_atras();
            Log::info('INDICADOR: "Ramos/m2/año (-4 meses)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D9') { // Venta $/m2/año (-4 meses)
            Log::info('inicio INDICADOR: "Venta $/m2/año (-4 meses)"');
            Venta::dinero_m2_anno_4_meses_atras();
            Log::info('fin INDICADOR: "Venta $/m2/año (-4 meses)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D15') { // Venta $/m2/año (-1 mes)
            Log::info('inicio INDICADOR: "Venta $/m2/año (-1 mes)"');
            Venta::dinero_m2_anno_1_mes_atras();
            Log::info('fin INDICADOR: "Venta $/m2/año (-4 meses)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D10') { // Venta $/m2/año (-1 año)
            dump('INDICADOR: "Venta $/m2/año (-1 año)"');
            Venta::dinero_m2_anno_1_anno_atras();
            Log::info('INDICADOR: "Venta $/m2/año (-1 año)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D11') { // Tallos cosechados (-7 días)
            dump('INDICADOR: "Tallos cosechados (-7 días)"');
            Campo::tallos_cosechados_7_dias_atras();
            Log::info('INDICADOR: "Tallos cosechados (-7 días)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D12') { // Tallos/m2 (-4 semanas)
            dump('INDICADOR: "Tallos/m2 (-4 semanas)"');
            Area::tallos_m2_4_semanas_atras();
            Log::info('INDICADOR: "Tallos/m2 (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'DA2') { // Ramos/m2 (-4 semanas)
            dump('INDICADOR: "Ramos/m2 (-4 semanas)"');
            Area::ramos_m2_4_semanas_atras();
            Log::info('INDICADOR: "Ramos/m2 (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D13') { // Cajas equivalentes vendidas(-7 dias)
            dump('INDICADOR: "Cajas equivalentes vendidas (-7 dias)"');
            Venta::cajas_equivalentes_vendidas_7_dias_atras();
            Log::info('INDICADOR: "Cajas equivalentes vendidas (-7 dias)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'D14') { // Precio por tallo (-7 dias)
            dump('INDICADOR: "Precio por ramo (-7 dias)"');
            Venta::precio_por_tallo_7_dias_atras();
            Log::info('INDICADOR: "Precio por ramo (-7 dias)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'P1') { // Cajas cosechadas (-7 dias)
            dump('INDICADOR: "Cajas cosechadas (-7 dias)"');
            Postcosecha::cajas_cosechadas_7_dias_atras();
            Log::info('INDICADOR: "Cajas cosechadas (-7 dias)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C1') { // Costos Mano de Obra (-1 semana)
            dump('INDICADOR: "Costos Mano de Obra (-1 semana)"');
            Costos::mano_de_obra_1_semana_atras();
            Log::info('INDICADOR: "Costos Mano de Obra (-1 semana)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C2') { // Costos Insumos (-1 semana)
            dump('INDICADOR: "Costos Insumos (-1 semana)"');
            Costos::costos_insumos_1_semana_atras();
            Log::info('INDICADOR: "Costos Insumos (-1 semana)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C13') { // Costos Propagacion (-1 semana)
            dump('INDICADOR: "Costos Propagacion (-1 semana)"');
            Costos::costos_propagacion_1_semana_atras();
            Log::info('INDICADOR: "Costos Propagacion (-1 semana)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C3') { // Costos Campo/ha/semana (-4 semanas)
            dump('INDICADOR: "Costos Campo/ha/semana (-4 semanas)"');
            Costos::costos_campo_ha_4_semana_atras();
            Log::info('INDICADOR: "Costos Campo/ha/semana (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C4') { // Costos Cosecha x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Cosecha x Tallo (-4 semanas)"');
            Costos::costos_cosecha_tallo_4_semana_atras();
            Log::info('INDICADOR: "Costos Cosecha x Tallo (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C5') { // Costos Postcosecha x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Postcosecha x Tallo (-4 semanas)"');
            Costos::costos_postcosecha_tallo_4_semana_atras();
            Log::info('INDICADOR: "Costos Postcosecha x Tallo (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C6') { // Costos Total x Tallo (-4 semanas)
            dump('INDICADOR: "Costos Total x Tallo (-4 semanas)"');
            Costos::costos_total_tallo_4_semana_atras();
            Log::info('INDICADOR: "Costos Total x Tallo (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C7') { // Costos Fijos (-1 semana)
            dump('INDICADOR: "Costos Fijos (-1 semana)"');
            Costos::costos_fijos_1_semana_atras();
            Log::info('INDICADOR: "Costos Fijos (-1 semana)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C8') { // Costos Regalías (-1 semana)
            dump('INDICADOR: "Costos Regalías (-1 semana)"');
            Costos::costos_regalias_1_semana_atras();
            Log::info('INDICADOR: "Costos Regalías (-1 semana)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C9') { // Costos/m2 (-16 semanas)
            dump('INDICADOR: "Costos/m2 (-16 semanas)"');
            Costos::costos_m2_16_semanas_atras();
            Log::info('INDICADOR: "Costos/m2 (-16 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C11') { // Costos/m2 (-4 semanas)
            dump('INDICADOR: "Costos/m2 (-4 semanas)"');
            Costos::costos_m2_4_semanas_atras();
            Log::info('INDICADOR: "Costos/m2 (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C12') { // Costo x Planta (-4 semanas)
            dump('INDICADOR: "Costo x Planta (-4 semanas)"');
            Costos::costos_x_planta_4_semanas_atras();
            Log::info('INDICADOR: "Costo x Planta (-4 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'C10') { // Costos/m2 (-52 semanas)
            dump('INDICADOR: "Costos/m2 (-52 semanas)"');
            Costos::costos_m2_52_semanas_atras();
            Log::info('INDICADOR: "Costos/m2 (-52 semanas)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'R1') { // Rentabilidad (-4 meses)
            dump('INDICADOR: "Rentabilidad (-4 meses)"');
            Costos::rentabilidad_4_meses();
            Log::info('INDICADOR: "Rentabilidad (-4 meses)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'R3') { // Rentabilidad (-1 mes)
            dump('INDICADOR: "Rentabilidad (-1 mes)"');
            Costos::rentabilidad_1_mes();
            Log::info('INDICADOR: "Rentabilidad (-1 mes)"');
        }
        if ($indicador_par === '0' || $indicador_par === 'R2') { // Rentabilidad (-1 año)
            dump('INDICADOR: "Rentabilidad (-1 año)"');
            Costos::rentabilidad_1_anno();
            Log::info('INDICADOR: "Rentabilidad (-1 año)"');
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "indicador:update" <<<<< * >>>>>');
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "indicador:update" <<<<< * >>>>>');
    }
}
