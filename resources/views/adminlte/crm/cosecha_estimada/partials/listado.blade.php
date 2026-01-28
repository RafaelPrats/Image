@if (count($listado) > 0)
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="th_yura_green" style="padding-left: 10px">
                <div style="width: 100px">
                    Variedad
                </div>
            </th>
            <th class="th_yura_green" style="padding-left: 10px">
                <div style="width: 150px">
                    Color
                </div>
            </th>
            @php
                $totales = [];
                $totales_cambio = [];
                $totales_bqt = [];
                $array_total_grupo_fechas = [];
                $array_total_grupo_cambios = [];
                $array_total_grupo_bqt = [];
            @endphp
            @foreach ($fechas as $f)
                <th class="text-center bg-yura_dark">
                    <div style="min-width: 130px">
                        {{ $f }}
                    </div>
                </th>
                @php
                    $totales[] = 0;
                    $totales_cambio[] = 0;
                    $totales_bqt[] = 0;
                    $array_total_grupo_fechas[] = 0;
                    $array_total_grupo_cambios[] = 0;
                    $array_total_grupo_bqt[] = 0;
                @endphp
            @endforeach
            <th class="text-center th_yura_green">
                <div style="min-width: 130px">
                    TOTAL
                </div>
            </th>
        </tr>
        @php
            $actual = $listado[0]['planta']->nombre . ' ' . $listado[0]['long'] . 'cm';
            $total_grupo_fechas = $array_total_grupo_fechas;
            $count_listado = count($listado);
        @endphp
        @foreach ($listado as $pos_i => $item)
            <tr>
                <th style="padding-left: 10px; border-color:#9d9d9d">
                    {{ $item['planta']->nombre }}
                </th>
                <th style="padding-left: 10px; border-color:#9d9d9d">
                    {{ $item['variedad']->nombre }} {{ $item['long'] }}cm
                </th>
                @php
                    $total_var = 0;
                    $total_var_bqt = 0;
                    $total_var_cambios = 0;
                @endphp
                @foreach ($item['valores'] as $pos => $v)
                    @php
                        $total_var += $v['fijos'];
                        $total_var_bqt += $v['tallos_bqt'];
                        $total_var_cambios += $v['mod'];
                        $totales[$pos] += $v['fijos'];
                        $totales_cambio[$pos] += $v['mod'];
                        $totales_bqt[$pos] += $v['tallos_bqt'];
                        $total_grupo_fechas[$pos] += $v['fijos'];
                        if ($v['mod'] > 0) {
                            $class_badge = 'btn-yura_primary';
                        } else {
                            $class_badge = 'btn-yura_danger';
                        }
                        $array_total_grupo_cambios[$pos] += $v['mod'];
                        $array_total_grupo_bqt[$pos] += $v['tallos_bqt'];
                    @endphp
                    <td class="text-center"
                        style="border-color: #9d9d9d; background-color: {{ $pos_i % 2 == 0 ? '#e5e5e5' : '' }}">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                title="{{ $v['tallos_bqt'] > 0 ? 'Tallos Bqt = ' . $v['tallos_bqt'] : '' }}">
                                {{ number_format($v['fijos'] + $v['tallos_bqt']) }}
                                @if ($v['tallos_bqt'] > 0)
                                    <b class="error">*</b>
                                @endif
                            </button>
                            @if ($v['mod'] != 0)
                                <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                                    {{ number_format($v['fijos'] + $v['mod'] + $v['tallos_bqt']) }}
                                </button>
                                <button type="button" class="btn btn-xs {{ $class_badge }}" title="Cambios">
                                    {{ $v['mod'] > 0 ? '+' : '' }}{{ number_format($v['mod']) }}
                                </button>
                            @endif
                        </div>
                    </td>
                @endforeach
                @php
                    if ($total_var_cambios > 0) {
                        $class_badge = 'btn-yura_primary';
                    } else {
                        $class_badge = 'btn-yura_danger';
                    }
                @endphp
                <th class="text-center" style="background-color: #003644">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark"
                            title="{{ $total_var_bqt > 0 ? 'Tallos Bqt = ' . $total_var_bqt : '' }}">
                            {{ number_format($total_var + $total_var_bqt) }}
                            @if ($total_var_bqt > 0)
                                <b class="error">*</b>
                            @endif
                        </button>
                        @if ($total_var_cambios != 0)
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                                {{ number_format($total_var + $total_var_cambios + $total_var_bqt) }}
                            </button>
                            <button type="button" class="btn btn-xs {{ $class_badge }}" title="Cambios">
                                {{ $total_var_cambios > 0 ? '+' : '' }}{{ number_format($total_var_cambios) }}
                            </button>
                        @endif
                    </div>
                </th>
            </tr>
            @php
                $next = $pos_i == $count_listado - 1 ? null : $listado[$pos_i + 1]['planta']->nombre . ' ' . $listado[$pos_i + 1]['long'] . 'cm';
            @endphp
            @if ($actual != $next)
                <tr>
                    <th colspan="2" class="bg-yura_dark mouse-hand text-center">
                        {{ $actual }}
                    </th>
                    @php
                        $total_grupo = 0;
                        $total_grupo_cambio = 0;
                        $total_grupo_bqt = 0;
                    @endphp
                    @foreach ($total_grupo_fechas as $posi => $v)
                        @php
                            $total_grupo += $v;
                            $total_grupo_cambio += $array_total_grupo_cambios[$posi];
                            $total_grupo_bqt += $array_total_grupo_bqt[$posi];
                            if ($array_total_grupo_cambios[$posi] > 0) {
                                $class_badge = 'btn-yura_primary';
                            } else {
                                $class_badge = 'btn-yura_danger';
                            }
                            if ($total_grupo_cambio > 0) {
                                $class_badge_grupo = 'btn-yura_primary';
                            } else {
                                $class_badge_grupo = 'btn-yura_danger';
                            }
                        @endphp
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #003644">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_dark"
                                    title="{{ $array_total_grupo_bqt[$posi] > 0 ? 'Tallos Bqt = ' . $array_total_grupo_bqt[$posi] : '' }}">
                                    {{ number_format($v + $array_total_grupo_bqt[$posi]) }}
                                    @if ($array_total_grupo_bqt[$posi] > 0)
                                        <b class="error">*</b>
                                    @endif
                                </button>
                                @if ($array_total_grupo_cambios[$posi] != 0)
                                    <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                                        {{ number_format($v + $array_total_grupo_cambios[$posi] + $array_total_grupo_bqt[$posi]) }}
                                    </button>
                                    <button type="button" class="btn btn-xs {{ $class_badge }}" title="Cambios">
                                        {{ $array_total_grupo_cambios[$posi] > 0 ? '+' : '' }}{{ number_format($array_total_grupo_cambios[$posi]) }}
                                    </button>
                                @endif
                            </div>
                        </td>
                        @php
                            $array_total_grupo_cambios[$posi] = 0;
                            $array_total_grupo_bqt[$posi] = 0;
                        @endphp
                    @endforeach
                    <th class="text-center" style="background-color: #003644">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                title="{{ $total_grupo_bqt > 0 ? 'Tallos Bqt = ' . $total_grupo_bqt : '' }}">
                                {{ number_format($total_grupo + $total_grupo_bqt) }}
                                @if ($total_grupo_bqt > 0)
                                    <b class="error">*</b>
                                @endif
                            </button>
                            @if ($total_grupo_cambio != 0)
                                <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                                    {{ number_format($total_grupo + $total_grupo_cambio + $total_grupo_bqt) }}
                                </button>
                                <button type="button" class="btn btn-xs {{ $class_badge_grupo }}" title="Cambios">
                                    {{ $total_grupo_cambio > 0 ? '+' : '' }}{{ number_format($total_grupo_cambio) }}
                                </button>
                            @endif
                        </div>
                    </th>
                </tr>
                @php
                    $actual = $next;
                    $total_grupo_fechas = $array_total_grupo_fechas;
                @endphp
            @endif
        @endforeach
        <tr id="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="2">
                TOTALES
            </th>
            @php
                $total = 0;
                $total_cambio = 0;
                $total_bqt = 0;
            @endphp
            @foreach ($totales as $posy => $v)
                @php
                    $total += $v;
                    $total_cambio += $totales_cambio[$posy];
                    $total_bqt += $totales_bqt[$posy];
                    if ($totales_cambio[$posy] > 0) {
                        $class_badge = 'btn-yura_primary';
                    } else {
                        $class_badge = 'btn-yura_danger';
                    }
                    if ($total_cambio > 0) {
                        $class_badge_total = 'btn-yura_primary';
                    } else {
                        $class_badge_total = 'btn-yura_danger';
                    }
                @endphp
                <th class="text-center" style="background-color: #003644">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark"
                            title="{{ $totales_bqt[$posy] > 0 ? 'Tallos Bqt = ' . $totales_bqt[$posy] : '' }}">
                            {{ number_format($v + $totales_bqt[$posy]) }}
                            @if ($totales_bqt[$posy] > 0)
                                <b class="error">*</b>
                            @endif
                        </button>
                        @if ($totales_cambio[$posy] != 0)
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                                {{ number_format($v + $totales_cambio[$posy] + $totales_bqt[$posy]) }}
                            </button>
                            <button type="button" class="btn btn-xs {{ $class_badge }}" title="Cambios">
                                {{ $totales_cambio[$posy] > 0 ? '+' : '' }}{{ number_format($totales_cambio[$posy]) }}
                            </button>
                        @endif
                    </div>
                </th>
            @endforeach
            <th class="text-center" style="background-color: #003644">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_dark"
                        title="{{ $total_bqt > 0 ? 'Tallos Bqt = ' . $total_bqt : '' }}">
                        {{ number_format($total + $total_bqt) }}
                        @if ($total_bqt > 0)
                            <b class="error">*</b>
                        @endif
                    </button>
                    @if ($total_cambio != 0)
                        <button type="button" class="btn btn-xs btn-yura_warning" title="Actual">
                            {{ number_format($total + $total_cambio + $total_bqt) }}
                        </button>
                        <button type="button" class="btn btn-xs {{ $class_badge_total }}" title="Cambios">
                            {{ $total_cambio > 0 ? '+' : '' }}{{ number_format($total_cambio) }}
                        </button>
                    @endif
                </div>
            </th>
        </tr>
    </table>

    <style>
        #tr_fija_top_0 th {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        #tr_fija_bottom_0 th {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }
    </style>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif
