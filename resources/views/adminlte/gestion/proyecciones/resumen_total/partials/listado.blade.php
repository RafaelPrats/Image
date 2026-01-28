<div style="overflow-x: scroll; width: 100%">
    <table style="width: 100%; border: 1px solid #9d9d9d" class="table-striped table-bordered">
        <tr>
            <th class="text-left th_yura_green" style="padding-left: 10px">
                Semanas
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center th_yura_green">
                    {{$sem->codigo}}
                </th>
                @php
                    $totales[] = [
                        'proy' => 0,
                        'area' => 0,
                    ];
                @endphp
            @endforeach
        </tr>
        @foreach($data as $d)
            <tr class="tr_variedades hidden">
                <th class="text-left bg-yura_dark" style="padding-left: 10px" colspan="{{count($semanas) + 1}}">
                    {{$d['variedad']->nombre}}
                </th>
            </tr>
            <tr class="tr_variedades hidden">
                <th class="text-left" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 10px; padding-right: 10px">
                    Proyectados
                </th>
                @foreach($d['valores'] as $pos => $item)
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 10px; padding-right: 10px">
                        {{number_format($item['proy'], 2)}}
                    </td>
                    @php
                        $totales[$pos]['proy'] += $item['proy'];
                    @endphp
                @endforeach
            </tr>
            <tr class="tr_variedades hidden">
                <th class="text-left" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 10px">
                    Área m<sup>2</sup>
                </th>
                @foreach($d['valores'] as $pos => $item)
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['area'], 2)}}
                    </td>
                    @php
                        $totales[$pos]['area'] += $item['area'];
                    @endphp
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th class="text-left bg-yura_dark mouse-hand" style="padding-left: 10px" colspan="{{count($semanas) + 1}}"
                onclick="$('.tr_variedades').toggleClass('hidden')">
                TOTALES <i class="fa fa-fw fa-eye"></i> <i class="fa fa-fw fa-caret-up"></i>
            </th>
        </tr>
        <tr>
            <th class="text-left th_yura_green" style="padding-left: 10px; padding-right: 10px">
                Proyectados
            </th>
            @foreach($totales as $pos => $item)
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 10px; padding-right: 10px">
                    {{number_format($item['proy'], 2)}}
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="text-left th_yura_green" style="padding-left: 10px; padding-right: 10px">
                Área m<sup>2</sup>
            </th>
            @foreach($totales as $pos => $item)
                <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 10px; padding-right: 10px">
                    {{number_format($item['area'], 2)}}
                </th>
            @endforeach
        </tr>
    </table>
</div>