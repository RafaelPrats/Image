<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costo x planta
    </span>
    </th>
    @php
        $prom_costo_x_planta = 0;
    @endphp
    @foreach($semanas as $sem)
        @php
            $valor = 0;
            if ($sem->codigo_semana >= 2138)
                $valor = 5.2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($valor, 2)}}
            </div>
        </th>
        @php
            $prom_costo_x_planta += $valor;
        @endphp
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($prom_costo_x_planta / count($semanas), 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Campo/<sup>ha</sup>/Semana
    </span>
    </th>
    @php
        $total_value = $promedio_area > 0 ? ($total_campo / $promedio_area) * 10000 : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            $campo = porcentaje($porcentaje, $item->campo, 2);
            $value = $areas[$pos_s]->area > 0 ? ($campo / $areas[$pos_s]->area) * 10000 : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_value, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Cosecha x tallo
    </span>
    </th>
    @php
        $total_value = $total_tallos_cosechados > 0 ? $total_cosecha / $total_tallos_cosechados : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
            $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
            $value = $tallos_cosechados[$pos_s] > 0 ? ($cosecha / $tallos_cosechados[$pos_s]) : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($total_value * 100, 3)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costo Total x tallo
    </span>
    </th>
    @php
        $total_value = $total_tallos_clasificados > 0 ? ($total_costos / $total_tallos_clasificados) : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
            $servicios_generales = porcentaje($porcentaje, $item->servicios_generales, 2);
            $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
            $campo = porcentaje($porcentaje, $item->campo, 2);
            $administrativos = porcentaje($porcentaje, $item->administrativos, 2);
            $regalias = porcentaje($porcentaje, $item->regalias, 2);
            $propagacion = $item->codigo_semana >= 2138 ? $requerimientos[$pos]->requerimientos * 0.052 : 0;
            $costos = $servicios_generales + $cosecha + $campo + $administrativos + $regalias + $propagacion;
            $value = $tallos_clasificados[$pos_s] > 0 ? ($costos / $tallos_clasificados[$pos_s]) : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($total_value * 100, 3)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio x tallo
    </span>
    </th>
    @php
        $total_value = $total_tallos_clasificados > 0 ? ($total_valor / $total_tallos_clasificados) : 0;
    @endphp
    @foreach($ventas as $pos_s => $item)
        @php
            $value = $tallos_clasificados[$pos_s] > 0 ? $item / $tallos_clasificados[$pos_s] : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($total_value * 100, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Desecho de Cosecha
    </span>
    </th>
    @php
        $total_value = 100 - porcentaje($total_tallos_clasificados, $total_tallos_cosechados, 1);
    @endphp
    @foreach($tallos_clasificados as $pos_s => $item)
        @php
            $value = 100 - porcentaje($item, $tallos_cosechados[$pos_s], 1);
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}%
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            {{number_format($total_value, 2)}}%
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Venta/m<sup>2</sup>
    </span>
    </th>
    @php
        $total_venta_m2 = $promedio_area > 0 ? ($total_valor / $promedio_area) : 0;
    @endphp
    @foreach($ventas as $pos_s => $item)
        @php
            $value = $areas[$pos_s]->area > 0 ? $item / $areas[$pos_s]->area : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($total_venta_m2 * 100, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos/m<sup>2</sup>
    </span>
    </th>
    @php
        $total_costos_m2 = $promedio_area > 0 ? (($total_costos_operativos + $total_administrativos + $total_regalias) / $promedio_area) : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
            $servicios_generales = porcentaje($porcentaje, $item->servicios_generales, 2);
            $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
            $campo = porcentaje($porcentaje, $item->campo, 2);
            $administrativos = porcentaje($porcentaje, $item->administrativos, 2);
            $regalias = porcentaje($porcentaje, $item->regalias, 2);
            $propagacion = $item->codigo_semana >= 2138 ? $requerimientos[$pos]->requerimientos * 0.052 : 0;
            $costos = $servicios_generales + $cosecha + $campo + $administrativos + $regalias + $propagacion;
            $value = $areas[$pos_s]->area > 0 ? $costos / $areas[$pos_s]->area : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ¢{{number_format($total_costos_m2 * 100, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        EBITDA/m<sup>2</sup>
    </span>
    </th>
    @php
        $ebitda_m2 = $total_venta_m2 - $total_costos_m2;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
            $servicios_generales = porcentaje($porcentaje, $item->servicios_generales, 2);
            $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
            $campo = porcentaje($porcentaje, $item->campo, 2);
            $administrativos = porcentaje($porcentaje, $item->administrativos, 2);
            $regalias = porcentaje($porcentaje, $item->regalias, 2);
            $propagacion = $item->codigo_semana >= 2138 ? $requerimientos[$pos]->requerimientos * 0.052 : 0;
            $venta = $ventas[$pos];
            $costos = $servicios_generales + $cosecha + $campo + $administrativos + $regalias + $propagacion;
            $value = $venta - $costos;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px; color: {{$value < 0 ? '#D01C62' : '#00B388'}}">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px; color: {{$ebitda_m2 < 0 ? '#D01C62' : '#00B388'}}">
            ¢{{number_format($ebitda_m2 * 100, 2)}}
        </div>
    </th>
</tr>
