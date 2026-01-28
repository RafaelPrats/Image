<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_select_planta_semanal">
        <thead>
            <tr>
                <th class="text-center th_yura_green col_fija_left_0" rowspan="2" style="left:0 !important; position: sticky !important;">
                    <div style="width: 180px">
                        Colores {{ $planta->nombre }}
                    </div>
                </th>
                @foreach ($listado_annos as $a)
                    <th class="text-center th_yura_green" colspan="{{ count($a['semanas']) + 1 }}">
                        {{ $a['anno'] }}
                    </th>
                @endforeach
            </tr>
            <tr>
                @php
                    $array_totales_vacio = [];
                @endphp
                @foreach ($listado_annos as $a)
                    @php
                        $array_totales_semanas_vacio = [];
                    @endphp
                    @foreach ($a['semanas'] as $sem)
                        @php
                            $array_totales_semanas_vacio[] = [
                                'suma' => 0,
                                'positivos' => 0,
                            ];
                        @endphp
                        <th class="text-center bg-yura_dark">
                            <div style="width: 80px" class="text-center">
                                {{ $sem->codigo }}
                            </div>
                        </th>
                    @endforeach
                    @php
                        $array_totales_vacio[] = $array_totales_semanas_vacio;
                    @endphp
                    <th class="text-center bg-yura_dark">
                        <div style="width: 90px" class="text-center">
                            TOTAL <sup>{{ $a['anno'] }}</sup>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $totales_annos = $array_totales_vacio;
            @endphp
            @foreach ($listado as $item)
                @php
                    $totales_annos_long = $array_totales_vacio;
                @endphp
                @foreach ($item['valores_variedades'] as $var)
                    <tr>
                        <th class="padding_lateral_5 col_fija_left_0"
                            style="background-color: #dddddd; border-color: #9d9d9d">
                            {{ $var['variedad']->nombre }} {{ $item['longitud'] }}cm
                        </th>
                        @foreach ($var['valores_anno'] as $pos_a => $a)
                            @php
                                $total_anno_item = 0;
                                $positivos_anno_item = 0;
                            @endphp
                            @foreach ($a['valores_semanas'] as $pos_sem => $sem)
                                @php
                                    $total_anno_item += $sem['valor'];
                                    $totales_annos_long[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                                    $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                                    if ($sem['valor'] > 0) {
                                        $positivos_anno_item++;
                                        $totales_annos_long[$pos_a][$pos_sem]['positivos']++;
                                        $totales_annos[$pos_a][$pos_sem]['positivos']++;
                                    }
                                @endphp
                                <th class="text-center" style="border-color: #9d9d9d">
                                    @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                        {{ round($sem['valor']) }}
                                    @else
                                        {{ round($sem['valor'], 2) }}
                                    @endif
                                </th>
                            @endforeach
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                                @if ($criterio != 'P')
                                    @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                        {{ round($total_anno_item) }}
                                    @else
                                        {{ round($total_anno_item, 2) }}
                                    @endif
                                @else
                                    {{ $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0 }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                @endforeach
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark col_fija_left_0">
                        {{ $planta->nombre }} {{ $item['longitud'] }}cm
                    </th>
                    @foreach ($totales_annos_long as $t)
                        @php
                            $total_anno = 0;
                            $positivos_anno = 0;
                        @endphp
                        @foreach ($t as $val)
                            @php
                                if ($criterio != 'P') {
                                    $total_anno += $val['suma'];
                                    if ($val['suma'] > 0) {
                                        $positivos_anno++;
                                    }
                                } else {
                                    $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                                    $total_anno += $r;
                                    if ($r > 0) {
                                        $positivos_anno++;
                                    }
                                }
                            @endphp
                            <th class="text-center bg-yura_dark">
                                @if ($criterio != 'P')
                                    @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                        {{ round($val['suma']) }}
                                    @else
                                        {{ round($val['suma'], 2) }}
                                    @endif
                                @else
                                    {{ $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0 }}
                                @endif
                            </th>
                        @endforeach
                        <th class="text-center bg-yura_dark">
                            @if ($criterio != 'P')
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ round($total_anno) }}
                                @else
                                    {{ round($total_anno, 2) }}
                                @endif
                            @else
                                {{ $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0 }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green col_fija_left_0">
                TOTALES
            </th>
            @foreach ($totales_annos as $t)
                @php
                    $total_anno = 0;
                    $positivos_anno = 0;
                @endphp
                @foreach ($t as $val)
                    @php
                        if ($criterio != 'P') {
                            $total_anno += $val['suma'];
                            if ($val['suma'] > 0) {
                                $positivos_anno++;
                            }
                        } else {
                            $r = $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0;
                            $total_anno += $r;
                            if ($r > 0) {
                                $positivos_anno++;
                            }
                        }
                    @endphp
                    <th class="text-center th_yura_green">
                        @if ($criterio != 'P')
                            @if (in_array($criterio, ['R', 'T']))
                                {{ round($val['suma']) }}
                            @else
                                {{ round($val['suma'], 2) }}
                            @endif
                        @else
                            {{ $val['positivos'] > 0 ? round($val['suma'] / $val['positivos'], 2) : 0 }}
                        @endif
                    </th>
                @endforeach
                <th class="text-center th_yura_green">
                    @if ($criterio != 'P')
                        @if (in_array($criterio, ['R', 'T']))
                            {{ round($total_anno) }}
                        @else
                            {{ round($total_anno, 2) }}
                        @endif
                    @else
                        {{ $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0 }}
                    @endif
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_select_planta_semanal')
    $('#table_select_planta_semanal_filter>label>input').addClass('input-yura_default');
</script>
