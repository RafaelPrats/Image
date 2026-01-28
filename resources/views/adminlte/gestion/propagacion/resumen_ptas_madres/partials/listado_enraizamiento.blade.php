<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border-radius: 18px 0 0 0" id="table_resumen_propagacion">
        <tr>
            <th class="th_yura_green columna_fija_left_0" style="padding-left: 5px; border-radius: 18px 0 0 0">
                <div style="width: 150px">Semanas</div>
            </th>
            @php
                $totales_plantas_sembradas = [];
                $totales_requerimientos = [];
                $totales_porcentaje_requerimientos = [];
                $totales_costo_x_planta = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center th_yura_green">
                    <div style="width: 100px">{{$sem->semana}}</div>
                </th>
                @php
                    array_push($totales_plantas_sembradas, 0);
                    array_push($totales_requerimientos, 0);
                    array_push($totales_porcentaje_requerimientos, [
                        'cant' => 0,
                        'positivos' => 0
                    ]);
                    array_push($totales_costo_x_planta, 0);
                @endphp
            @endforeach
        </tr>
        @foreach($listado as $item)
            <tr class="tr_variedad hide">
                <th class="bg-yura_dark columna_fija_left_0" style="padding-left: 5px; border-color: #5a7177" colspan="{{count($semanas) - 1}}">
                    {{$item['variedad']->planta}}: {{$item['variedad']->variedad}}
                </th>
                <th class="bg-yura_dark" style="border-color: #5a7177" colspan="2">
                </th>
            </tr>
            <tr class="tr_variedad hide hidden">
                <th style="padding-left: 5px; background-color: #e9ecef; border-color: #9d9d9d" class="columna_fija_left_0">
                    Ptas Sembradas
                </th>
                @foreach($item['valores'] as $pos => $val)
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{number_format($val->plantas_sembradas)}}
                    </td>
                    @php
                        $totales_plantas_sembradas[$pos] += $val->plantas_sembradas;
                    @endphp
                @endforeach
            </tr>
            <tr class="tr_variedad hide">
                <th style="padding-left: 5px; background-color: #e9ecef; border-color: #9d9d9d" class="columna_fija_left_0">
                    Requerimientos
                </th>
                @foreach($item['valores'] as $pos => $val)
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{number_format($val->requerimientos)}}
                    </td>
                    @php
                        $totales_requerimientos[$pos] += $val->requerimientos;
                    @endphp
                @endforeach
            </tr>
            <tr class="tr_variedad hide">
                <th style="padding-left: 5px; background-color: #e9ecef; border-color: #9d9d9d" class="columna_fija_left_0">
                    % Enraizamiento
                </th>
                @foreach($item['valores'] as $pos => $val)
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{$val->porcentaje_requerimiento}}%
                    </td>
                    @php
                        $totales_porcentaje_requerimientos[$pos]['cant'] += $val->porcentaje_requerimiento;
                        if($val->porcentaje_requerimiento > 0)
                            $totales_porcentaje_requerimientos[$pos]['positivos'] ++;
                        $totales_costo_x_planta[$pos] = $val->costo_x_planta;
                    @endphp
                @endforeach
            </tr>
        @endforeach
        {{-- TOTALES --}}
        <tr class="mouse-hand"
            onclick="$('.tr_variedad').toggleClass('hide'); $('#caret_totales_down').toggleClass('hide'); $('#caret_totales_up').toggleClass('hide')">
            <th class="bg-yura_dark columna_fija_left_0" style="padding-left: 5px; border-color: #5a7177" colspan="{{count($semanas) - 1}}">
                TOTALES
                <button type="button" class="btn btn-xs btn-yura_dark" style="margin-left: 10px">
                    <i class="fa fa-fw fa-eye"></i>
                    <span class="fa fa-fw fa-caret-down" id="caret_totales_down"></span>
                    <span class="fa fa-fw fa-caret-up hide" id="caret_totales_up"></span>
                </button>
            </th>
            <th class="bg-yura_dark" style="border-color: #5a7177" colspan="2">
            </th>
        </tr>
        <tr class="hidden">
            <th class="th_yura_green columna_fija_left_0" style="padding-left: 5px;">
                <div style="width: 150px">Ptas Sembradas</div>
            </th>
            @foreach($totales_plantas_sembradas as $val)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <div style="width: 100px">{{number_format($val)}}</div>
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="th_yura_green columna_fija_left_0" style="padding-left: 5px;">
                <div style="width: 150px">Requerimientos</div>
            </th>
            @foreach($totales_requerimientos as $val)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <div style="width: 100px">{{number_format($val)}}</div>
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="th_yura_green columna_fija_left_0" style="padding-left: 5px;">
                <div style="width: 150px">% Enraizamiento</div>
            </th>
            @foreach($totales_porcentaje_requerimientos as $pos => $val)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <div style="width: 100px">{{$val['positivos'] > 0 ? round($val['cant'] / $val['positivos'], 2) : 0}}%</div>
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="th_yura_green columna_fija_left_0" style="padding-left: 5px; border-radius: 0 0 0 18px">
                <div style="width: 150px">Costo x planta</div>
            </th>
            @foreach($totales_costo_x_planta as $pos => $val)
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <div style="width: 100px">
                        @if($semanas[$pos]->semana >= 2138)
                        &cent;{{round(5.2, 3)}}
                        @else
                        &cent;{{round($val * 100, 3)}}
                        @endif
                    </div>
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    #table_resumen_propagacion .columna_fija_left_0 {
        z-index: 8;
        position: sticky;
        left: 0;
    }
</style>