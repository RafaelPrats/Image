@foreach($areas_50_100 as $pos_a => $a)
    <tr class="tr_costos_50_100 hidden">
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                <em>{{$a['area']}} 50%</em>
            </span>
        </th>
        @php
            $total_area_50 = 0;
        @endphp
        @foreach($a['valores_50_100'] as $pos_v => $v)
            <th class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
                <div style="width: 100px">
                    ${{number_format($v->valor50, 2)}}
                </div>
            </th>
            @php
                $total_area_50 += $v->valor50;
                $totales_50[$pos_v] += $v->valor50;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px;">
                ${{number_format($total_area_50, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_costos_50_100 hidden">
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                <em>{{$a['area']}} 100%</em>
            </span>
        </th>
        @php
            $total_area_100 = 0;
        @endphp
        @foreach($a['valores_50_100'] as $pos_v => $v)
            <th class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
                <div style="width: 100px">
                    ${{number_format($v->valor100, 2)}}
                </div>
            </th>
            @php
                $total_area_100 += $v->valor100;
                $totales_100[$pos_v] += $v->valor100;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px;">
                ${{number_format($total_area_100, 2)}}
            </div>
        </th>
    </tr>
@endforeach

<tr class="tr_costos_50_100 hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            <em>Total 50%</em>
        </span>
    </th>
    @php
        $total_50 = 0;
    @endphp
    @foreach($totales_50 as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
            <div style="width: 100px">
                ${{number_format($v, 2)}} <sup>{{porcentaje($v, $total_costos_mo[$pos_v]->valor, 1)}}%</sup>
            </div>
        </th>
        @php
            $total_50 += $v;
        @endphp
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_50, 2)}} <sup>{{porcentaje($total_50, $total_mo, 1)}}%</sup>
        </div>
    </th>
</tr>

<tr class="tr_costos_50_100 hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            <em>Total 100%</em>
        </span>
    </th>
    @php
        $total_100 = 0;
    @endphp
    @foreach($totales_100 as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
            <div style="width: 100px">
                ${{number_format($v, 2)}} <sup>{{porcentaje($v, $total_costos_mo[$pos_v]->valor, 1)}}%</sup>
            </div>
        </th>
        @php
            $total_100 += $v;
        @endphp
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_100, 2)}} <sup>{{porcentaje($total_100, $total_mo, 1)}}%</sup>
        </div>
    </th>
</tr>
