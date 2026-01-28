<table class="table-striped table-bordered" style="width: 100%; border: 2px solid #9d9d9d">
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">

        </th>
        @php
            $totales_50 = [];
            $totales_100 = [];
            $totales_personal = [];
            $total_mo = 0;
        @endphp
        @foreach($semanas as $pos_s => $sem)
            <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                {{$sem->codigo_semana}}
            </th>
            @php
                $totales_50[] = 0;
                $totales_100[] = 0;
                $total_mo += $total_costos_mo[$pos_s]->valor;
                $totales_personal[] = 0;
            @endphp
        @endforeach

        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Total
        </th>
    </tr>
    {{-- TALLOS COSECHADOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                TALLOS COSECHADOS
            </span>
        </th>
        @php
            $total_tallos_cosechados = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_cosechados, 2)}}
                </div>
            </th>
            @php
                $total_tallos_cosechados += $item->tallos_cosechados;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_cosechados, 2)}}
            </div>
        </th>
    </tr>
    {{-- TALLOS CLASIFICADOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                TALLOS CLASIFICADOS
            </span>
        </th>
        @php
            $total_tallos_clasificados = 0;
        @endphp
        @foreach($resumen_cosecha as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_clasificados, 2)}}
                </div>
            </th>
            @php
                $total_tallos_clasificados += $item->tallos_clasificados;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_clasificados, 2)}}
            </div>
        </th>
    </tr>
    {{-- AREA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                ÁREA<sup>m2</sup>
            </span>
        </th>
        @php
            $total_area = 0;
        @endphp
        @foreach($areas as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->area, 2)}}
                </div>
            </th>
            @php
                $total_area += $item->area;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" title="PROMEDIO">
            <div style="width: 110px">
                {{number_format($total_area / count($semanas), 2)}}
            </div>
        </th>
    </tr>
    {{-- NOTAS DE CREDITO --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                NOTAS DE CRÉDITO
            </span>
        </th>
        @php
            $total_notas_credito = 0;
        @endphp
        @foreach($notas_credito as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item, 2)}}
                </div>
            </th>
            @php
                $total_notas_credito += $item;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_notas_credito, 2)}}
            </div>
        </th>
    </tr>
    {{-- VENTA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                VENTA
            </span>
        </th>
        @php
            $total_valor = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #3cf7ff">
                <div style="width: 100px">
                    ${{number_format($item->valor, 2)}}
                </div>
            </th>
            @php
                $total_valor += $item->valor;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_valor, 2)}}
            </div>
        </th>
    </tr>
    {{-- PROPAGACIÓN --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold" class="btn btn-xs btn-link"
                  onclick="$('.tr_propagacion').toggleClass('hide')">
                PROPAGACIÓN
            </span>
        </th>
        @php
            $total_propagacion = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    @if($item->codigo_semana < 2138)
                        ${{number_format($item->propagacion, 2)}}
                    @else
                        ${{number_format($requerimientos[$pos]->requerimientos * 0.052, 2)}}
                    @endif
                </div>
            </th>
            @php
                if($item->codigo_semana < 2138)
                    $total_propagacion += $item->propagacion;
                else
                    $total_propagacion += $requerimientos[$pos]->requerimientos * 0.052;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_propagacion, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_propagacion hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->propagacion_mp, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_propagacion hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MO
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->propagacion_mo, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_propagacion hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GIP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->propagacion_gip, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_propagacion hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GA
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->propagacion_ga, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    {{-- CAMPO --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold" class="btn btn-xs btn-link"
                  onclick="$('.tr_campo').toggleClass('hide')">
                CAMPO
            </span>
        </th>
        @php
            $total_campo = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->campo, 2)}}
                </div>
            </th>
            @php
                $total_campo += $item->campo;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_campo, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_campo hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->campo_mp, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_campo hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MO
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->campo_mo, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_campo hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GIP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->campo_gip, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_campo hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GA
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->campo_ga, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    {{-- COSECHA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold" class="btn btn-xs btn-link"
                  onclick="$('.tr_cosecha').toggleClass('hide')">
                COSECHA
            </span>
        </th>
        @php
            $total_cosecha = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->cosecha, 2)}}
                </div>
            </th>
            @php
                $total_cosecha += $item->cosecha;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_cosecha, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_cosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->cosecha_mp, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_cosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MO
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->cosecha_mo, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_cosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GIP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->cosecha_gip, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_cosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GA
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->cosecha_ga, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    {{-- POSTCOSECHA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold" class="btn btn-xs btn-link"
                  onclick="$('.tr_postcosecha').toggleClass('hide')">
                POSTCOSECHA
            </span>
        </th>
        @php
            $total_postcosecha = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->postcosecha, 2)}}
                </div>
            </th>
            @php
                $total_postcosecha += $item->postcosecha;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_postcosecha, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_postcosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->postcosecha_mp, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_postcosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MO
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->postcosecha_mo, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_postcosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GIP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->postcosecha_gip, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_postcosecha hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GA
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->postcosecha_ga, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    {{-- SERVICIOS GENERALES --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold" class="btn btn-xs btn-link"
                  onclick="$('.tr_servicios_generales').toggleClass('hide')">
                SERVICIOS GENERALES
            </span>
        </th>
        @php
            $total_servicios_generales = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->servicios_generales, 2)}}
                </div>
            </th>
            @php
                $total_servicios_generales += $item->servicios_generales;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_servicios_generales, 2)}}
            </div>
        </th>
    </tr>
    <tr class="tr_servicios_generales hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->servicios_generales_mp, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_servicios_generales hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                MO
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->servicios_generales_mo, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_servicios_generales hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GIP
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->servicios_generales_gip, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    <tr class="tr_servicios_generales hide">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black">
                GA
            </span>
        </td>
        @foreach($semanas as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->servicios_generales_ga, 2)}}
                </div>
            </td>
        @endforeach
    </tr>
    {{-- COSTOS OPERATIVOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                COSTOS OPERATIVOS
            </span>
        </th>
        @php
            $total_costos_operativos = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                if ($item->codigo_semana < 2138)
                    $propagacion = $item->propagacion;
                else
                    $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
                $costos_operativos = $propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px">
                    ${{number_format($costos_operativos, 2)}}
                </div>
            </th>
            @php
                $total_costos_operativos += $costos_operativos;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_operativos, 2)}}
            </div>
        </th>
    </tr>
    {{-- ADMINISTRATIVOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                ADMINISTRATIVOS
            </span>
        </th>
        @php
            $total_administrativos = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->administrativos, 2)}}
                </div>
            </th>
            @php
                $total_administrativos += $item->administrativos;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_administrativos, 2)}}
            </div>
        </th>
    </tr>
    {{-- REGALÍAS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                REGALÍAS
            </span>
        </th>
        @php
            $total_regalias = 0;
        @endphp
        @foreach($semanas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format($item->regalias, 2)}}
                </div>
            </th>
            @php
                $total_regalias += $item->regalias;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_regalias, 2)}}
            </div>
        </th>
    </tr>
    {{-- TOTAL COSTOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                TOTAL COSTOS
            </span>
        </th>
        @php
            $total_costos = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                if ($item->codigo_semana < 2138)
                    $propagacion = $item->propagacion;
                else
                    $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
                $operativos = $propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales;
                $administrativos = $item->administrativos;
                $regalias = $item->regalias;
                $value = $operativos + $administrativos + $regalias;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px">
                    ${{number_format($value, 2)}}
                </div>
            </th>
            @php
                $total_costos += $value;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos, 2)}}
            </div>
        </th>
    </tr>
    {{-- EBITDA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                EBITDA
            </span>
        </th>
        @foreach($semanas as $pos => $item)
            @php
                if ($item->codigo_semana < 2138)
                    $propagacion = $item->propagacion;
                else
                    $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
                $ebitda = $item->valor - ($propagacion + $item->campo + $item->cosecha + $item->postcosecha + $item->servicios_generales + $item->administrativos + $item->regalias);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; color: {{$ebitda < 0 ? '#D01C62' : '#00B388'}}">
                <div style="width: 100px">
                    ${{number_format($ebitda, 2)}}
                </div>
            </th>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            @php
                $ebitda = $total_valor - ($total_propagacion + $total_campo + $total_cosecha + $total_postcosecha + $total_servicios_generales + $total_administrativos + $total_regalias);
            @endphp
            <div style="width: 110px; color: {{$ebitda < 0 ? '#D01C62' : '#00B388'}}">
                ${{number_format($ebitda, 2)}}
            </div>
        </th>
    </tr>
    {{-- COSTOS 50 y 100 --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_costos_50_100').toggleClass('hidden')">
                Hrs. Extras 50% y 100%
            </span>
        </th>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.generales.partials._row_costos_50_100')
    {{-- RRHH --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_rrhh').toggleClass('hidden')">
                RRHH
            </span>
        </th>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.generales.partials._row_rrhh')
    {{-- OTROS INDICADORES --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_otros_indicadores').toggleClass('hidden')">
                INDICADORES SEMANALES
            </span>
        </th>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.generales.partials._row_otros_indicadores')
    {{-- INDICADORES 4 SEMANAS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #b9e5ff; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_indicadores_4_semanas').toggleClass('hidden')">
                INDICADORES 4 SEMANAS
            </span>
        </th>
        <th class="text-center" style="background-color: #b9e5ff; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.generales.partials._row_indicadores_4_semanas')
</table>

<style>
    .th_fijo_left_0 {
        position: sticky;
        left: 0;
        z-index: 1;
        border-color: #9d9d9d;
        background-color: #e9ecef;
    }
</style>