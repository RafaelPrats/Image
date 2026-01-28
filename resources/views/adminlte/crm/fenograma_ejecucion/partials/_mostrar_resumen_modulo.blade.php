<table class="table-bordered" style="width: 100%">
    <tr>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green">
            Tallos Cosechados
        </th>
        <th class="text-center th_yura_green">
            Tallos Acum.
        </th>
        @foreach($monitoreos as $m)
            <th class="text-center" style="border: 2px solid black; background-color: {{explode('|', $m->color)[0]}};
                    color: {{explode('|', $m->color)[1]}}">
                {{explode('|',$m->unitaria_nombre)[0]}}{{$m->um_siglas}}
            </th>
        @endforeach
        <th class="text-center th_yura_green">
            Calibre
        </th>
        <th class="text-center th_yura_green">
            Ramos Estandar
        </th>
        <th class="text-center th_yura_green">
            Ramos Acum.
        </th>
    </tr>
    @php
        $getCalibreMonitoreo = $ciclo->getCalibreMonitoreo();
        $total_tallos_cosechados = 0;
        $total_ramos_cosechados = 0;
    @endphp
    @foreach($data as $d)
        @php
            $getCalibreMonitoreoByFecha = $ciclo->getCalibreMonitoreoByFecha($d['fecha']);
            $getCalibreMonitoreoByFecha = $getCalibreMonitoreoByFecha > 0 ? $getCalibreMonitoreoByFecha : 0;
            $tallos_cosechados = $d['tallos_cosechados'] > 0 ? $d['tallos_cosechados'] : 0;
            $total_tallos_cosechados += $tallos_cosechados;
            $ramos_cosechados = $getCalibreMonitoreoByFecha > 0 ? $d['tallos_cosechados'] / $getCalibreMonitoreoByFecha : 0;
            $total_ramos_cosechados += $ramos_cosechados;
        @endphp
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                {{$d['fecha']}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{number_format($d['tallos_cosechados'])}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{number_format($total_tallos_cosechados)}}
            </td>
            @foreach($monitoreos as $m)
                @php
                    $getCalibreByFechaUnitaria = $ciclo->getCalibreByFechaUnitaria($d['fecha'], $m->id_clasificacion_unitaria);
                @endphp
                <td class="text-center" style="border: 2px solid black">
                    @if($getCalibreByFechaUnitaria != '')
                        {{porcentaje($getCalibreByFechaUnitaria->ramos * $getCalibreByFechaUnitaria->tallos_x_ramo, $d['tallos_monitoreo'], 1)}}%
                    @else
                        0%
                    @endif
                </td>
            @endforeach
            <td class="text-center" style="border-color: #9d9d9d">
                {{$getCalibreMonitoreoByFecha}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{number_format($ramos_cosechados, 2)}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                {{number_format($total_ramos_cosechados, 2)}}
            </td>
        </tr>
    @endforeach
    {{--<tr>
        <th class="text-center th_yura_green">
            Total
        </th>
        <th class="text-center th_yura_green" colspan="2">
            {{number_format($total_tallos_cosechados)}}
        </th>
        <th class="text-center th_yura_green" colspan="{{count($monitoreos)}}">
            Calibre Acum: {{$total_ramos_cosechados > 0 ? round($total_tallos_cosechados / $total_ramos_cosechados, 2) : 0}}
        </th>
        <th class="text-center th_yura_green">
            {{$getCalibreMonitoreo}}
        </th>
        <th class="text-center th_yura_green" colspan="2">
            {{number_format($total_ramos_cosechados, 2)}}
        </th>
    </tr>--}}
</table>