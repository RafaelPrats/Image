<table class="table-striped table-bordered" style="width: 100%; border: 2px solid #9d9d9d">
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">

        </th>
        @foreach($semanas as $sem)
            <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                {{$sem->codigo_semana}}
            </th>
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
        @foreach($tallos_cosechados as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item)}}
                </div>
            </th>
            @php
                $total_tallos_cosechados += $item;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_cosechados)}}
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
        @foreach($tallos_clasificados as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item)}}
                </div>
            </th>
            @php
                $total_tallos_clasificados += $item;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_clasificados)}}
            </div>
        </th>
    </tr>
    {{-- AREA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                ÁERA
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
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            @php
                $promedio_area = $total_area / count($areas);
            @endphp
            <div style="width: 110px">
                {{number_format($promedio_area, 2)}}
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
        @foreach($ventas as $item)
            <th class="text-center" style="border-color: #9d9d9d; background-color: #3cf7ff">
                <div style="width: 100px">
                    ${{number_format($item, 2)}}
                </div>
            </th>
            @php
                $total_valor += $item;
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
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
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
                        $0.00
                    @else
                        ${{number_format($requerimientos[$pos]->requerimientos * 0.052, 2)}}
                    @endif
                </div>
            </th>
            @php
                if($item->codigo_semana >= 2138)
                    $total_propagacion += $requerimientos[$pos]->requerimientos * 0.052;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_propagacion, 2)}}
            </div>
        </th>
    </tr>
    {{-- CAMPO --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                CAMPO
            </span>
        </th>
        @php
            $total_campo = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format(porcentaje($porcentaje, $item->campo, 2), 2)}}
                </div>
            </th>
            @php
                $total_campo += porcentaje($porcentaje, $item->campo, 2);
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_campo, 2)}}
            </div>
        </th>
    </tr>
    {{-- COSECHA --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                COSECHA
            </span>
        </th>
        @php
            $total_cosecha = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                $porcentaje = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format(porcentaje($porcentaje, $item->cosecha, 2), 2)}}
                </div>
            </th>
            @php
                $total_cosecha += porcentaje($porcentaje, $item->cosecha, 2);
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_cosecha, 2)}}
            </div>
        </th>
    </tr>
    {{-- SERVICIOS GENERALES --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                SERVICIOS GENERALES
            </span>
        </th>
        @php
            $total_servicios_generales = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format(porcentaje($porcentaje, $item->servicios_generales, 2), 2)}}
                </div>
            </th>
            @php
                $total_servicios_generales += porcentaje($porcentaje, $item->servicios_generales, 2);
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_servicios_generales, 2)}}
            </div>
        </th>
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
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
                $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
                $servicios_generales = porcentaje($porcentaje, $item->servicios_generales, 2);
                $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
                $campo = porcentaje($porcentaje, $item->campo, 2);
                if ($item->codigo_semana < 2138)
                    $propagacion = 0;
                else
                    $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px">
                    ${{number_format($servicios_generales + $cosecha + $campo + $propagacion, 2)}}
                </div>
            </th>
            @php
                $total_costos_operativos += $servicios_generales + $cosecha + $campo + $propagacion;
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
        @foreach($semanas as $pos => $item)
            @php
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format(porcentaje($porcentaje, $item->administrativos, 2), 2)}}
                </div>
            </th>
            @php
                $total_administrativos += porcentaje($porcentaje, $item->administrativos, 2);
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_administrativos, 2)}}
            </div>
        </th>
    </tr>
    {{-- REGALIAS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                REGALÍAS
            </span>
        </th>
        @php
            $total_regalias = 0;
        @endphp
        @foreach($semanas as $pos => $item)
            @php
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #ffd1d1">
                <div style="width: 100px">
                    ${{number_format(porcentaje($porcentaje, $item->regalias, 2), 2)}}
                </div>
            </th>
            @php
                $total_regalias += porcentaje($porcentaje, $item->regalias, 2);
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
                $porcentaje = porcentaje($areas[$pos]->area, $areas_totales[$pos]->area, 1);
                $porcentaje_cosecha = porcentaje($tallos_cosechados[$pos], $semanas[$pos]->tallos_cosechados, 1);
                $servicios_generales = porcentaje($porcentaje, $item->servicios_generales, 2);
                $cosecha = porcentaje($porcentaje_cosecha, $item->cosecha, 2);
                $campo = porcentaje($porcentaje, $item->campo, 2);
                $administrativos = porcentaje($porcentaje, $item->administrativos, 2);
                $regalias = porcentaje($porcentaje, $item->regalias, 2);
                if ($item->codigo_semana < 2138)
                    $propagacion = 0;
                else
                    $propagacion = $requerimientos[$pos]->requerimientos * 0.052;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px">
                    ${{number_format($servicios_generales + $cosecha + $campo + $propagacion + $administrativos + $regalias, 2)}}
                </div>
            </th>
            @php
                $total_costos += $servicios_generales + $cosecha + $campo + $propagacion + $administrativos + $regalias;
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
        @php
            $total_ebitda = $total_valor - $total_costos;
        @endphp
        @foreach($semanas as $pos => $item)
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
                $venta = $ventas[$pos];
                $ebitda = $venta - $costos;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px; color: {{$ebitda < 0 ? '#D01C62' : '#00B388'}}">
                    ${{number_format($ebitda, 2)}}
                </div>
            </th>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d; color: {{$ebitda < 0 ? '#D01C62' : '#00B388'}}">
            <div style="width: 110px">
                ${{number_format($total_ebitda, 2)}}
            </div>
        </th>
    </tr>
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
    @include('adminlte.gestion.costos.generales.partials.variedades._row_otros_indicadores')
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