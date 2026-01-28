<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_select_planta_diario">
        <thead>
            <tr>
                <th class="text-center th_yura_green col_fija_left_0"
                    style="left:0 !important; position: sticky !important;">
                    <div style="width: 180px">
                        Colores {{ $planta->nombre }}
                    </div>
                </th>
                @php
                    $array_totales_vacio = [];
                @endphp
                @foreach ($fechas as $f)
                    @php
                        $array_totales_vacio[] = [
                            'suma' => 0,
                            'positivos' => 0,
                        ];
                    @endphp
                    <th class="text-center bg-yura_dark">
                        <div style="width: 80px" class="text-center">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}<br>
                            {{ $f }}
                        </div>
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark">
                    <div style="width: 90px" class="text-center">
                        TOTAL
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $totales = $array_totales_vacio;
            @endphp
            @foreach ($listado as $item)
                @php
                    $totales_long = $array_totales_vacio;
                @endphp
                @foreach ($item['valores_variedades'] as $var)
                    <tr>
                        <th class="padding_lateral_5 col_fija_left_0"
                            style="background-color: #dddddd; border-color: #9d9d9d">
                            {{ $var['variedad']->nombre }} {{ $item['longitud'] }}cm
                        </th>
                        @php
                            $total_item = 0;
                            $positivos_item = 0;
                        @endphp
                        @foreach ($var['valores_fechas'] as $pos_dia => $dia)
                            @php
                                $total_item += $dia['valor'];
                                $totales_long[$pos_dia]['suma'] += $dia['valor'];
                                $totales[$pos_dia]['suma'] += $dia['valor'];
                                if ($dia['valor'] > 0) {
                                    $positivos_item++;
                                    $totales_long[$pos_dia]['positivos']++;
                                    $totales[$pos_dia]['positivos']++;
                                }
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d">
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ number_format($dia['valor']) }}
                                @else
                                    {{ number_format($dia['valor'], 2) }}
                                @endif
                            </th>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            @if ($criterio != 'P')
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ number_format($total_item) }}
                                @else
                                    {{ number_format($total_item, 2) }}
                                @endif
                            @else
                                {{ $positivos_item > 0 ? round($total_item / $positivos_item, 2) : 0 }}
                            @endif
                        </th>
                    </tr>
                @endforeach
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark col_fija_left_0">
                        {{ $planta->nombre }} {{ $item['longitud'] }}cm
                    </th>
                    @php
                        $total = 0;
                        $positivos = 0;
                    @endphp
                    @foreach ($totales_long as $val)
                        @php
                            if ($criterio != 'P') {
                                $total += $val['suma'];
                                if ($val['suma'] > 0) {
                                    $positivos++;
                                }
                            } else {
                                $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                                $total += $r;
                                if ($r > 0) {
                                    $positivos++;
                                }
                            }
                        @endphp
                        <th class="text-center bg-yura_dark">
                            @if ($criterio != 'P')
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ number_format($val['suma']) }}
                                @else
                                    {{ number_format($val['suma'], 2) }}
                                @endif
                            @else
                                {{ $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0 }}
                            @endif
                        </th>
                    @endforeach
                    <th class="text-center bg-yura_dark">
                        @if ($criterio != 'P')
                            @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                {{ number_format($total) }}
                            @else
                                {{ number_format($total, 2) }}
                            @endif
                        @else
                            {{ $positivos > 0 ? round($total / $positivos, 2) : 0 }}
                        @endif
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green col_fija_left_0">
                TOTALES
            </th>
            @php
                $total = 0;
                $positivos = 0;
            @endphp
            @foreach ($totales as $val)
                @php
                    if ($criterio != 'P') {
                        $total += $val['suma'];
                        if ($val['suma'] > 0) {
                            $positivos++;
                        }
                    } else {
                        $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                        $total += $r;
                        if ($r > 0) {
                            $positivos++;
                        }
                    }
                @endphp
                <th class="text-center th_yura_green">
                    @if ($criterio != 'P')
                        @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                            {{ number_format($val['suma']) }}
                        @else
                            {{ number_format($val['suma'], 2) }}
                        @endif
                    @else
                        {{ $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0 }}
                    @endif
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                @if ($criterio != 'P')
                    @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                        {{ number_format($total) }}
                    @else
                        {{ number_format($total, 2) }}
                    @endif
                @else
                    {{ $positivos > 0 ? round($total / $positivos, 2) : 0 }}
                @endif
            </th>
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_select_planta_diario')
    $('#table_select_planta_diario_filter>label>input').addClass('input-yura_default');
</script>
