<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green th_fija_left_0">
                <div style="width: 150px">
                    Semanas/Calibres
                </div>
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center th_yura_green" colspan="{{count($variedades) + 2}}">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="listar_resumen_verde_semanal('{{$sem->codigo}}')">
                        {{$sem->codigo}}
                    </button>
                </th>
                @php
                    $var_array = [];
                    foreach($variedades as $var)
                        $var_array[] = 0;
                    array_push($totales, [
                        'variedades' => $var_array,
                    ]);
                @endphp
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green th_fija_left_0" style="padding-right: 5px; padding-left: 5px">
                Calibres
            </th>
            @foreach($semanas as $sem)
                @foreach($variedades as $pos_v => $var)
                    <th class="text-center bg-yura_dark" style="border-color: white; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{$var->siglas}}
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white;">
                    Total
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white;">
                    Porc.
                </th>
            @endforeach
        </tr>
        @foreach($data as $item)
            <tr>
                <th class="text-center th_fija_left_0"
                    style="border-color: #9d9d9d; width: 50px; background-color: {{explode('|', $item['unitaria']->color)[0]}}; color: {{explode('|', $item['unitaria']->color)[1]}}">
                    {{explode('|',$item['unitaria']->nombre)[0]}}{{$item['unitaria']->siglas}}
                </th>
                @foreach($item['valores'] as $pos => $val)
                    @php
                        $total_sem_unitaria = 0;
                    @endphp
                    @foreach($val as $pos_v => $var)
                        <td class="text-center"
                            style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                            {{number_format($var['tallos'])}}
                        </td>
                        @php
                            $total_sem_unitaria += $var['tallos'];
                            $totales[$pos]['variedades'][$pos_v] += $var['tallos'];
                        @endphp
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; background-color: #e9ecef">
                        {{number_format($total_sem_unitaria)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; background-color: #e9ecef">
                        {{porcentaje($total_sem_unitaria, $totales_sem[$pos], 1)}}%
                    </th>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green th_fija_left_0">
                Totales
            </th>
            @foreach($totales as $t)
                @php
                    $total_sem = 0;
                @endphp
                @foreach($t['variedades'] as $pos_v => $var)
                    <th class="text-center bg-yura_dark"
                        style="border-color: white; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{number_format($var)}}
                    </th>
                    @php
                        $total_sem += $var;
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;">
                    {{number_format($total_sem)}}
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;">
                    100%
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    .th_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>