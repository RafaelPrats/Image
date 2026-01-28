<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px">
    <table class="table-bordered" style="border:1px solid #9d9d9d; width: 100%">
        <tr>
            <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
                <div style="width: 150px">
                    Variedad
                </div>
            </th>
            @php
                $totales = [];
            @endphp
            @foreach ($labels as $l)
                <th class="text-center bg-yura_dark" colspan="2">
                    <div style="width: 180px">
                        @if ($rango == 'S')
                            {{ $l->codigo }}
                        @else
                            {{ convertDateToText($l) }}
                        @endif
                    </div>
                </th>
                @php
                    $totales[] = [
                        'proyectados' => 0,
                        'vendidos' => 0,
                    ];
                @endphp
            @endforeach
        </tr>
        <tr>
            @foreach ($labels as $l)
                <th class="text-center bg-yura_dark">
                    Proyectados
                </th>
                <th class="text-center bg-yura_dark">
                    Vendidos
                </th>
            @endforeach
        </tr>
        @foreach ($listado as $item)
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['planta']->nombre }}
                </th>
                @foreach ($item['valores'] as $pos => $v)
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($v['proyectados']) }}
                    </th>
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($v['vendidos']) }}
                    </th>
                    @php
                        $totales[$pos]['proyectados'] += $v['proyectados'];
                        $totales[$pos]['vendidos'] += $v['vendidos'];
                    @endphp
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green">
                TOTALES
            </th>
            @foreach ($totales as $pos => $v)
                <th class="text-center padding_lateral_5 bg-yura_dark" style="border-color: #9d9d9d">
                    {{ number_format($v['proyectados']) }}
                </th>
                <th class="text-center padding_lateral_5 bg-yura_dark" style="border-color: #9d9d9d">
                    {{ number_format($v['vendidos']) }}
                </th>
            @endforeach
        </tr>
    </table>
</div>
