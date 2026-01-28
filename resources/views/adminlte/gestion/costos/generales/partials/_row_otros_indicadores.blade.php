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
            $valor = 5.2;
        @endphp
        @foreach($costo_x_planta as $item)
            @php
                if ($sem->codigo_semana == $item->semana)
                    $valor = $item->costo_x_planta * 100;
                if ($sem->codigo_semana >= 2138)
                    $valor = 5.2;
            @endphp
        @endforeach
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
        $total_value = $total_area > 0 ? ($total_campo / $total_area) * 10000 : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $value = $areas[$pos_s]->area > 0 ? ($item->campo / $areas[$pos_s]->area) * 10000 : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
        @php
            $total_value += $value;
        @endphp
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
        $total_value = 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $value = $item->tallos_cosechados > 0 ? ($item->cosecha / $item->tallos_cosechados) : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
        @php
            $total_value += $value;
        @endphp
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
        Postcosecha x tallo
    </span>
    </th>
    @php
        $total_value = 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $value = $item->tallos_cosechados > 0 ? ($item->postcosecha / $item->tallos_cosechados) : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
        @php
            $total_value += $value;
        @endphp
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
            if($item->codigo_semana < 2138)
                $propagacion = $item->propagacion;
            else
                $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
            $value = $resumen_cosecha[$pos_s]->tallos_clasificados > 0 ? (($propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales + $item->administrativos + $item->regalias) / $resumen_cosecha[$pos_s]->tallos_clasificados) : 0;
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
    @foreach($semanas as $pos_s => $item)
        @php
            $value = $resumen_cosecha[$pos_s]->tallos_clasificados > 0 ? $item->valor / $resumen_cosecha[$pos_s]->tallos_clasificados : 0;
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
    @foreach($semanas as $pos_s => $item)
        @php
            $value = 100 - porcentaje($resumen_cosecha[$pos_s]->tallos_clasificados, $item->tallos_cosechados, 1);
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
        $total_venta_m2 = $total_area > 0 ? ($total_valor / $total_area) : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $value = $areas[$pos_s]->area > 0 ? $item->valor / $areas[$pos_s]->area : 0;
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
        $total_costos_m2 = $total_area > 0 ? (($total_costos_operativos + $total_administrativos + $total_regalias) / $total_area) : 0;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            if($item->codigo_semana < 2138)
                $propagacion = $item->propagacion;
            else
                $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
            $value = $areas[$pos_s]->area > 0 ? ($propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales + $item->administrativos + $item->regalias) / $areas[$pos_s]->area : 0;
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
            $venta = $areas[$pos_s]->area > 0 ? $item->valor / $areas[$pos_s]->area : 0;
            $costo = $areas[$pos_s]->area > 0 ? ($item->propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales + $item->administrativos + $item->regalias) / $areas[$pos_s]->area : 0;
            $value = $venta - $costo;
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