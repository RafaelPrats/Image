<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green th_fija_left_0" rowspan="2">
                <div style="width: 200px">
                    Fechas/Variedades
                </div>
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($variedades as $var)
                <th class="text-center th_yura_green" colspan="{{count($unitarias) + 2}}"
                    style="border-left: 2px solid; border-right: 2px solid; padding-right: 5px; padding-left: 5px">
                    {{$var->nombre}}
                </th>
                @php
                    $u_array = [];
                    foreach($unitarias as $u)
                        $u_array[] = 0;
                    array_push($totales, [
                        'unitarias' => $u_array,
                    ]);
                @endphp
            @endforeach
            <th class="text-center th_yura_green" rowspan="2">
                Tallos
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Porc.
            </th>
        </tr>
        <tr>
            @foreach($variedades as $var)
                @foreach($unitarias as $pos_u => $u)
                    <th class="text-center"
                        style="background-color: {{explode('|', $u->color)[0]}}; color: {{explode('|', $u->color)[1]}}; border-color: #9d9d9d;
                        {{$pos_u == 0 ? 'border-left: 2px solid' : ''}}; padding-right: 5px; padding-left: 5px">
                        {{explode('|',$u->nombre)[0]}}{{$u->siglas}}
                    </th>
                @endforeach
                <th class="text-center th_yura_green" style="border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    Tallos
                </th>
                <th class="text-center th_yura_green" style="border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    Porc.
                </th>
            @endforeach
        </tr>
        @foreach($data as $pos_d => $d)
            <tr>
                <th class="text-center th_fija_left_0"
                    style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    {{getDiaSemanaByFecha($d['fecha']).' '.convertDateToText($d['fecha'])}}
                </th>
                @php
                    $total_tallos_dia = 0;
                @endphp
                @foreach($d['variedades'] as $pos_v => $var)
                    @php
                        $total_tallos_var = 0;
                    @endphp
                    @foreach($var['unitarias'] as $pos_u => $u)
                        <td class="text-center"
                            style="border-color: #9d9d9d; {{$pos_u == 0 ? 'border-left: 2px solid' : ''}}; padding-right: 5px; padding-left: 5px">
                            {{porcentaje($u['tallos'], $totales_f[$pos_d], 1)}}%
                        </td>
                        @php
                            $total_tallos_var += $u['tallos'];

                            $totales[$pos_v]['unitarias'][$pos_u] += $u['tallos'];
                        @endphp
                    @endforeach
                    @php
                        $total_tallos_dia += $total_tallos_var;
                    @endphp
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{number_format($total_tallos_var)}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{porcentaje($total_tallos_var, $totales_f[$pos_d], 1)}}%
                    </th>
                @endforeach
                <th class="text-center"
                    style="background-color: #e9ecef; border-color: #9d9d9d; border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    {{number_format($total_tallos_dia)}}
                </th>
                <th class="text-center"
                    style="background-color: #e9ecef; border-color: #9d9d9d; border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    100%
                </th>
            </tr>
        @endforeach
        {{--<tr>
            <th class="text-center th_yura_green th_fija_left_0">
                Totales
            </th>
            @php
                $total_tallos = 0;
            @endphp
            @foreach($totales as $t)
                @php
                    $tallos_var = 0;
                @endphp
                @foreach($t['unitarias'] as $pos_u => $u)
                    <th class="text-center bg-yura_dark"
                        style="padding-left: 5px; padding-right: 5px; {{$pos_u == 0 ? 'border-left: 2px solid' : ''}}">
                        {{number_format($u)}}
                    </th>
                    @php
                        $tallos_var += $u;
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{number_format($tallos_var)}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">

                </th>
                @php
                    $total_tallos += $tallos_var;
                @endphp
            @endforeach
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-left: 2px solid">
                {{number_format($total_tallos)}}
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-left: 2px solid">
            </th>
        </tr>--}}
    </table>
</div>

<style>
    .th_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>