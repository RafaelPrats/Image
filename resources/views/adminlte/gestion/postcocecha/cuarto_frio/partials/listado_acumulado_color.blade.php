@if (count($inventarios) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px;">
        <table class="table-bordered table-striped" width="100%" style="border: 2px solid #9d9d9d;"
            id="table_cuarto_frio">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" rowspan="2">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Color
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Longitud
                    </th>
                    <th class="text-center th_yura_green" colspan="5">
                        Días
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Total
                    </th>
                </tr>
                <tr id="tr_fija_top_1">
                    @php
                        $totales_dia = [];
                    @endphp
                    @for ($i = 0; $i <= 4; $i++)
                        @php
                            $fecha = opDiasFecha('-', $i, date('Y-m-d'));
                            $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                            $totales_dia[] = 0;
                        @endphp
                        <th class="text-center" style="border-color: #0a0a0a; background-color: #e9ecef" width="40px"
                            title="{{ $fecha }}">
                            <span style="padding: 2px">
                                {{ $i == 4 ? $i . '...' : $i }}
                            </span>
                        </th>
                    @endfor
                </tr>
            </thead>

            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach ($inventarios as $pos_inv => $inv)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $inv['variedad']->pta_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $inv['variedad']->var_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            @if (intval($inv['longitud_ramo']) != '' || intval($inv['longitud_ramo']) >= 0)
                                {{ intval($inv['longitud_ramo']) . 'cm' }}
                            @endif
                        </th>
                        @foreach ($inv['dias'] as $pos_dia => $dia)
                            @php
                                $fecha = opDiasFecha('-', $pos_dia, date('Y-m-d'));
                                $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                                $totales_dia[$pos_dia] += $dia['cantidad'];
                            @endphp
                            <td class="text-center"
                                style="border-color: #0a0a0a; background-color: #e9ecef; color: #0a0a0a;"
                                title="{{ $fecha }}">
                                {{ $dia['cantidad'] != '' ? number_format($dia['cantidad']) : '' }}
                            </td>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($inv['disponibles']) }}
                        </th>
                    </tr>
                    @php
                        $total += $inv['disponibles'];
                    @endphp
                @endforeach
            </tbody>
            <tr id="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="3">
                    Totales
                </th>
                @for ($i = 0; $i <= 4; $i++)
                    @php
                        $fecha = opDiasFecha('-', $i, date('Y-m-d'));
                        $fecha = getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($fecha, 0, 10))))] . ' ' . convertDateToText(substr($fecha, 0, 10));
                    @endphp
                    <th class="text-center" style="border-color: #0a0a0a; background-color: #e9ecef" width="40px"
                        title="{{ $fecha }}">
                        {{ number_format($totales_dia[$i]) }}
                    </th>
                @endfor
                <th class="text-center th_yura_green">
                    {{ number_format($total) }}
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        El cuarto frío se encuentra vacío en estos momentos
    </div>
@endif

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
