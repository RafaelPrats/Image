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
                <th class="text-center th_yura_green" colspan="{{count($unitarias) + 6}}"
                    style="border-left: 2px solid; border-right: 2px solid; padding-right: 5px; padding-left: 5px">
                    {{$var->nombre}}
                </th>
                @php
                    $u_array = [];
                    foreach($unitarias as $u)
                        $u_array[] = 0;
                    array_push($totales, [
                        'unitarias' => $u_array,
                        'venta' => 0,
                    ]);
                @endphp
            @endforeach
            <th class="text-center th_yura_green" rowspan="2">
                Total
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Cal. Real
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Cal. Mon.
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                % Calibre
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Precio
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Venta
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
                    Total
                </th>
                <th class="text-center th_yura_green" style="border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    Cal. Real
                </th>
                <th class="text-center th_yura_green" style="border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    Cal. Mon.
                </th>
                <th class="text-center th_yura_green" style="border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    % Calibre
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Precio
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Venta
                </th>
            @endforeach
        </tr>
        @foreach($data as $d)
            <tr>
                <th class="text-center th_fija_left_0"
                    style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    {{getDiaSemanaByFecha($d['fecha']).' '.convertDateToText($d['fecha'])}}
                </th>
                @php
                    $total_tallos_dia = 0;
                    $total_venta_dia = 0;
                @endphp
                @foreach($d['variedades'] as $pos_v => $var)
                    @php
                        $total_tallos_var = 0;
                        $total_venta_var = 0;
                    @endphp
                    @foreach($var['unitarias'] as $pos_u => $u)
                        <td class="text-center"
                            style="border-color: #9d9d9d; {{$pos_u == 0 ? 'border-left: 2px solid' : ''}}; padding-right: 5px; padding-left: 5px">
                            {{number_format($u['tallos'])}}
                        </td>
                        @php
                            $total_tallos_var += $u['tallos'];
                            $total_venta_var += $u['unitaria']->precio_venta > 0 && $u['tallos'] > 0 ? ($u['tallos'] * $u['unitaria']->precio_venta) : 0;

                            $totales[$pos_v]['unitarias'][$pos_u] += $u['tallos'];
                            $totales[$pos_v]['venta'] += $u['unitaria']->precio_venta > 0 && $u['tallos'] > 0 ? ($u['tallos'] * $u['unitaria']->precio_venta) : 0;
                        @endphp
                    @endforeach
                    @php
                        $total_tallos_dia += $total_tallos_var;
                        $total_venta_dia += $total_venta_var;
                    @endphp
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{number_format($total_tallos_var)}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{$var['calibre_real']}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{$var['calibre_proy']}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        {{100 - porcentaje($var['calibre_proy'], $var['calibre_real'], 1)}}%
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        ¢{{$total_tallos_var > 0 ? round(($total_venta_var / $total_tallos_var) * 100, 3) : 0}}
                    </th>
                    <th class="text-center" style="background-color: #A9FFCC; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                        ${{number_format($total_venta_var, 2)}}
                    </th>
                @endforeach
                <th class="text-center"
                    style="background-color: #e9ecef; border-color: #9d9d9d; border-left: 2px solid; padding-right: 5px; padding-left: 5px">
                    {{number_format($total_tallos_dia)}}
                </th>
                <th class="text-center"
                    style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    {{$d['calibre_real']}}
                </th>
                <th class="text-center"
                    style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    {{$d['calibre_proy']}}
                </th>
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    {{100 - porcentaje($d['calibre_proy'], $d['calibre_real'], 1)}}%
                </th>
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    ¢{{$total_tallos_dia > 0 ? round(($total_venta_dia / $total_tallos_dia) * 100, 3) : 0}}
                </th>
                <th class="text-center" style="background-color: #A9FFCC; border-color: #9d9d9d; padding-right: 5px; padding-left: 5px">
                    ${{number_format($total_venta_dia, 2)}}
                </th>
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green th_fija_left_0">
                Totales
            </th>
            @php
                $total_tallos = 0;
                $total_venta = 0;
            @endphp
            @foreach($totales as $pos_t => $t)
                @php
                    $tallos_var = 0;
                    $venta_var = $t['venta'];
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
                    {{$totales_f[$pos_t]['calibre_real']}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{$totales_f[$pos_t]['calibre_proy']}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{100 - porcentaje($totales_f[$pos_t]['calibre_proy'], $totales_f[$pos_t]['calibre_real'], 1)}}%
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    ¢{{$tallos_var > 0 ? round(($venta_var / $tallos_var) * 100, 3) : 0}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    ${{number_format($venta_var, 2)}}
                </th>
                @php
                    $total_tallos += $tallos_var;
                    $total_venta += $venta_var;
                @endphp
            @endforeach
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-left: 2px solid">
                {{number_format($total_tallos)}}
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                {{$total_calibre}}
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                {{$total_calibre_proy}}
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                {{100 - porcentaje($total_calibre_proy, $total_calibre, 1)}}%
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                ¢{{$total_tallos > 0 ? round(($total_venta / $total_tallos) * 100, 3) : 0}}
            </th>
            <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                ${{number_format($total_venta, 2)}}
            </th>
        </tr>
    </table>
</div>

<style>
    .th_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }

    .th_fija_left_1 {
        position: sticky;
        left: 61px;
        z-index: 9;
    }
</style>