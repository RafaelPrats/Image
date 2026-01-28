<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_global">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 250px" class="text-center">
                        Clientes / Años
                    </div>
                </th>
                @foreach ($listado_annos as $a)
                    <th class="text-center th_yura_green" colspan="{{ count($a['meses']) + 1 }}">
                        {{ $a['anno'] }}
                    </th>
                @endforeach
            </tr>
            <tr id="tr_fija_top_1">
                @php
                    $totales_annos = [];
                @endphp
                @foreach ($listado_annos as $a)
                    @php
                        $totales_meses = [];
                    @endphp
                    @foreach ($a['meses'] as $mes)
                        @php
                            $totales_meses[] = [
                                'suma' => 0,
                                'positivos' => 0,
                            ];
                        @endphp
                        <th class="text-center bg-yura_dark">
                            <div style="width: 80px" class="text-center">
                                {{ getMeses()[$mes - 1] }}
                            </div>
                        </th>
                    @endforeach
                    @php
                        $totales_annos[] = $totales_meses;
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
            @foreach ($listado as $item)
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark">
                        {{ $item['cliente']->nombre }}
                    </th>
                    @foreach ($item['valores_anno'] as $pos_a => $a)
                        @php
                            $total_anno_item = 0;
                            $positivos_anno_item = 0;
                        @endphp
                        @foreach ($a['valores_meses'] as $pos_mes => $mes)
                            @php
                                $total_anno_item += $mes['valor'];
                                $totales_annos[$pos_a][$pos_mes]['suma'] += $mes['valor'];
                                if ($mes['valor'] > 0) {
                                    $positivos_anno_item++;
                                    $totales_annos[$pos_a][$pos_mes]['positivos']++;
                                }
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d">
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ number_format($mes['valor']) }}
                                @else
                                    {{ number_format($mes['valor'], 2) }}
                                @endif
                            </th>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            @if ($criterio != 'P')
                                @if (in_array($criterio, ['R', 'T', 'RP', 'TP']))
                                    {{ number_format($total_anno_item) }}
                                @else
                                    {{ number_format($total_anno_item, 2) }}
                                @endif
                            @else
                                {{ $positivos_anno_item > 0 ? round($total_anno_item / $positivos_anno_item, 2) : 0 }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green">
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
                            {{ number_format($total_anno) }}
                        @else
                            {{ number_format($total_anno, 2) }}
                        @endif
                    @else
                        {{ $positivos_anno > 0 ? round($total_anno / $positivos_anno, 2) : 0 }}
                    @endif
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 9;
    }
</style>

<script>
    estructura_tabla('table_global');
    $('#table_global_filter>label>input').addClass('input-yura_default')
</script>
