<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="th_yura_green padding_lateral_5">
                <div style="width: 140px">
                    Planta
                </div>
            </th>
            @php
                $totales_fecha = [];
            @endphp
            @foreach ($fechas as $pos => $f)
                @php
                    $totales_fecha[] = 0;
                @endphp
                <th class="bg-yura_dark padding_lateral_5">
                    <div style="width: 90px">
                        {{ explode('del ', convertDateToText($f))[0] }}
                    </div>
                </th>
            @endforeach
            @php
                $plantilla_totales = $totales_fecha;
            @endphp
            <th class="th_yura_green padding_lateral_5">
                <div style="width: 60px">
                    Total
                </div>
            </th>
            <th class="th_yura_green padding_lateral_5">
                <div style="width: 60px">
                    % Porc.
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($listado as $item)
            @php
                $totales_planta = $plantilla_totales;
                foreach ($item['valores_longitudes'] as $pos_l => $long) {
                    foreach ($long['valores_variedades'] as $pos_v => $variedad) {
                        foreach ($variedad['valores_fecha'] as $pos => $val) {
                            $totales_planta[$pos] += $val;
                        }
                    }
                }
            @endphp
            <tr onmouseover="$('#icon_planta_{{ $item['planta']->id_planta }}').removeClass('hidden')"
                onmouseleave="$('#icon_planta_{{ $item['planta']->id_planta }}').addClass('hidden')">
                <th class="bg-yura_dark padding_lateral_5">
                    {{ $item['planta']->nombre }}
                    <i class="fa fa-fw fa-caret-right hidden"
                        id="icon_planta_{{ $item['planta']->id_planta }}"></i>
                </th>
                @php
                    $total_planta = 0;
                @endphp
                @foreach ($totales_planta as $val)
                    @php
                        $total_planta += $val;
                    @endphp
                    <th class="bg-yura_dark padding_lateral_5">
                        {{ number_format($val) }}
                    </th>
                @endforeach
                <th class="bg-yura_dark padding_lateral_5">
                    {{ number_format($total_planta) }}
                </th>
                <th class="bg-yura_dark padding_lateral_5">
                    {{ porcentaje($total_planta, $total_cosecha, 1) }}%
                </th>
            </tr>
            @foreach ($item['valores_longitudes'] as $pos_l => $long)
                @php
                    $totales_longitud = $plantilla_totales;
                @endphp
                @foreach ($long['valores_variedades'] as $pos_v => $variedad)
                    <tr onmouseover="$('#icon_variedad_{{ $variedad['variedad']->id_variedad }}_longitud_{{ $long['longitud'] }}').removeClass('hidden')"
                        onmouseleave="$('#icon_variedad_{{ $variedad['variedad']->id_variedad }}_longitud_{{ $long['longitud'] }}').addClass('hidden')">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $variedad['variedad']->nombre }} {{ $long['longitud'] }}cm
                            <i class="fa fa-fw fa-caret-right hidden"
                                id="icon_variedad_{{ $variedad['variedad']->id_variedad }}_longitud_{{ $long['longitud'] }}"></i>
                        </th>
                        @php
                            $total_variedad_long = 0;
                        @endphp
                        @foreach ($variedad['valores_fecha'] as $pos => $val)
                            @php
                                $total_variedad_long += $val;
                                $totales_longitud[$pos] += $val;
                                $totales_fecha[$pos] += $val;
                            @endphp
                            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ number_format($val) }}
                            </td>
                        @endforeach
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ number_format($total_variedad_long) }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ porcentaje($total_variedad_long, $total_cosecha, 1) }}%
                        </th>
                    </tr>
                @endforeach
                <tr onmouseover="$('#icon_planta_{{ $item['planta']->id_planta }}_longitud_{{ $long['longitud'] }}').removeClass('hidden')"
                    onmouseleave="$('#icon_planta_{{ $item['planta']->id_planta }}_longitud_{{ $long['longitud'] }}').addClass('hidden')">
                    <th class="padding_lateral_5" style="background-color: #dddddd; border-color: #9d9d9d">
                        {{ $item['planta']->nombre }} {{ $long['longitud'] }}cm
                        <i class="fa fa-fw fa-caret-right hidden"
                            id="icon_planta_{{ $item['planta']->id_planta }}_longitud_{{ $long['longitud'] }}"></i>
                    </th>
                    @php
                        $total_long = 0;
                    @endphp
                    @foreach ($totales_longitud as $val)
                        @php
                            $total_long += $val;
                        @endphp
                        <th class="padding_lateral_5" style="background-color: #dddddd; border-color: #9d9d9d">
                            {{ number_format($val) }}
                        </th>
                    @endforeach
                    <th class="padding_lateral_5" style="background-color: #dddddd; border-color: #9d9d9d">
                        {{ number_format($total_long) }}
                    </th>
                    <th class="padding_lateral_5" style="background-color: #dddddd; border-color: #9d9d9d">
                        {{ porcentaje($total_long, $total_cosecha, 1) }}%
                    </th>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tr class="tr_fija_bottom_0">
        <th class="th_yura_green padding_lateral_5">
            TOTALES
        </th>
        @php
            $total_reporte = 0;
        @endphp
        @foreach ($totales_fecha as $pos => $val)
            @php
                $total_reporte += $val;
            @endphp
            <td class="bg-yura_dark padding_lateral_5">
                {{ number_format($val) }}
            </td>
        @endforeach
        <th class="th_yura_green padding_lateral_5">
            {{ number_format($total_reporte) }}
        </th>
        <th class="th_yura_green padding_lateral_5">
            {{ porcentaje($total_reporte, $total_cosecha, 1) }}%
        </th>
    </tr>
</table>


<style>
    .tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    .tr_fija_bottom_0 {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>
