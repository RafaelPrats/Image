@foreach($areas_50_100 as $pos_a => $a)
    <tr class="tr_rrhh hidden" title="Personal">
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                <em>{{$a['area']}}</em>
            </span>
        </th>
        @foreach($a['valores_50_100'] as $pos_v => $v)
            <td class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
                <div style="width: 100px">
                    {{$v->cantidad}}
                </div>
            </td>
            @php
                $totales_personal[$pos_v] += $v->cantidad;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        </th>
    </tr>
    <tr class="tr_rrhh hidden" title="Personal / ha">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                <em>Personal/<sup>ha</sup></em>
            </span>
        </td>
        @foreach($a['valores_50_100'] as $pos_v => $v)
            <td class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
                <div style="width: 100px">
                    {{number_format($v->cantidad / ($areas[$pos_v]->area / 10000), 2)}}
                </div>
            </td>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        </th>
    </tr>
@endforeach
<tr class="tr_rrhh hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            TOTAL PERSONAL
        </span>
    </th>
    @foreach($totales_personal as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
            <div style="width: 100px">
                {{number_format($v, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
    </th>
</tr>
<tr class="tr_rrhh hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
        <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
            TOTAL PERSONAL/Ha
        </span>
    </th>
    @foreach($totales_personal as $pos_v => $v)
        <th class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_a % 2 == 0 ? '#f9e4e4' : ''}}">
            <div style="width: 100px">
                {{number_format($v / ($areas[$pos_v]->area / 10000), 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
    </th>
</tr>
