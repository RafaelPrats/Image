@php
    $modulo = $ciclo->modulo;
@endphp
<legend class="text-center" style="font-size: 1em">{{$app_matriz->nombre}} aplicados al módulo: <strong>{{$modulo->nombre}}</strong></legend>

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                <div style="width: 50px">
                    Días
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 120px">
                    Fecha
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 80px">
                    Semana
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Rep.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Plantas
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Horas día
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Horas ordinarias
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Horas 50%
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Horas 100%
                </div>
            </th>
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    <div style="width: 200px">
                        {{$mo->nombre}}
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                Hombres día
            </th>
            <th class="text-center th_yura_green">
                Totales
            </th>
        </tr>
        @php
            $costo_total = 0;
            $plantas_total = 0;
            $horas_dia_total = 0;
            $hombres_dia_total = 0;
            $hr_ordinarias_total = 0;
            $hr_50_total = 0;
            $hr_100_total = 0;
        @endphp
        @foreach($labores as $pos => $labor)
            @php
                $costo_dia = 0;
                $plantas_total += $labor->plantas;
                $horas_dia_total += $labor->horas_dia;
                $hombres_dia_total += $labor->hombres_dia;
                $dia_semana = date('w', strtotime($labor->fecha));
                $horas_ordinarias = 0;
                $horas_50 = 0;
                $horas_100 = 0;
                if ($dia_semana >= 1 && $dia_semana <= 5) { // lunes a viernes
                    $horas_50 = $labor->horas_dia - 8;
                    $horas_ordinarias = $labor->horas_dia - $horas_50;
                    $costo_dia += $horas_ordinarias * $labor->hombres_dia * $hr_ordinaria->valor_hora_provisiones;
                    $costo_dia += $horas_50 * $labor->hombres_dia * $hr_50->valor_hora_provisiones;
                } else {    // sabado y domingo
                    $horas_100 = $labor->horas_dia;
                    $costo_dia += $horas_100 * $labor->hombres_dia * $hr_100->valor_hora_provisiones;
                }
                $hr_ordinarias_total += $horas_ordinarias;
                $hr_50_total += $horas_50;
                $hr_100_total += $horas_100;
                $costo_total += $costo_dia;
            @endphp
            <tr id="tr_labor_{{$labor->id_aplicacion_campo}}">
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{difFechas($labor->fecha, $ciclo->fecha_inicio)->days}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{getDiaSemanaByFecha($labor->fecha).' '.convertDateToText($labor->fecha)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{getSemanaByDate($labor->fecha)->codigo}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$labor->repeticion}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{number_format($labor->plantas)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$labor->horas_dia}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$horas_ordinarias}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$horas_50}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$horas_100}}
                </th>
                @foreach($mano_obras as $mo)
                    @php
                        $detalle = $labor != '' ? $labor->getDetalleByManoObra($mo->id_mano_obra) : '';
                        $dosis = '';
                        if ($detalle != ''){
                            $dosis = $detalle->dosis;
                        }
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$dosis}}
                    </td>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; padding-right: 5px; padding-left: 5px">
                    {{$labor->hombres_dia}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; padding-right: 5px; padding-left: 5px">
                    ${{round($costo_dia, 3)}}
                </th>
            </tr>
        @endforeach
        {{-- TOTALES --}}
        <tr>
            <th class="text-center th_yura_green" style="padding-left: 5px" colspan="4">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{number_format($plantas_total)}}
            </th>
            <th class="text-center th_yura_green">
                {{$horas_dia_total}}
                {{--{{count($labores) > 0 ? round($horas_dia_total / count($labores), 2) : 0}}--}}
            </th>
            <th class="text-center th_yura_green">
                {{$hr_ordinarias_total}}
            </th>
            <th class="text-center th_yura_green">
                {{$hr_50_total}}
            </th>
            <th class="text-center th_yura_green">
                {{$hr_100_total}}
            </th>
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark">
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{$hombres_dia_total}}
            </th>
            <th class="text-center th_yura_green">
                ${{number_format($costo_total, 2)}}
            </th>
        </tr>
    </table>
</div>

<style>
    .columna_fija_right_0 {
        position: sticky;
        right: 0;
        z-index: 9;
        background-color: #e9ecef;
    }
</style>