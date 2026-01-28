<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costo x planta
    </span>
    </th>
    @foreach($indicadores_4_semanas as $item)
        @php
            $valor = $item->costo_x_planta * 100;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($valor, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Campo/<sup>ha</sup>/Semana
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->campo_ha_semana;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Cosecha x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->cosecha_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Postcosecha x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->postcosecha_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costo Total x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->costo_total_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 3)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio x tallo
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->precio_x_tallo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Desecho de Cosecha
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->desecho_cosecha;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}%
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Venta/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->venta_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->costos_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
</tr>

<tr class="tr_indicadores_4_semanas hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        EBITDA/m<sup>2</sup>
    </span>
    </th>
    @foreach($indicadores_4_semanas as $pos_s => $item)
        @php
            $value = $item->ebitda_m2;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px; color: {{$value < 0 ? '#D01C62' : '#00B388'}}">
                ¢{{number_format($value * 100, 2)}}
            </div>
        </th>
    @endforeach
</tr>