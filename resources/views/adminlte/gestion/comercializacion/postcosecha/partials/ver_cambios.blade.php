<legend class="text-center" style="width: 100%; font-size: 1.3em; margin-bottom: 5px">
    Cambios en ventas de <b>{{ $planta->nombre }}-{{ $variedad->nombre }}, {{ $empaque->nombre }} de
        {{ $tallos_x_ramo }} tallos {{ $longitud }}cm {{ $peso }}gr</b> el <b>{{ $fecha }}</b>
</legend>
<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 th_yura_green">
            Fecha y Hora
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Cliente
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Caja
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Tipo Caja
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Piezas
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Ramos
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Tallos
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Ramos x Caja
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Usuario
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Cambio Fecha
        </th>
    </tr>
    @foreach ($listado as $item)
        <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')">
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->fecha_registro }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->cliente->detalle()->nombre }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ explode('|', $item->empaque_c->nombre)[0] }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->empaque_c->siglas }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                @if ($item->piezas != '')
                    {{ $item->piezas > 0 ? '+' : '' }}{{ $item->piezas }}
                @else
                    <small><em>mixtos</em></small>
                @endif
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->ramos > 0 ? '+' : '' }}{{ $item->ramos }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->tallos > 0 ? '+' : '' }}{{ $item->tallos }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->ramos_x_caja }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $item->usuario->nombre_completo }}
            </th>
            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                @if ($item->cambio_fecha)
                    {{ $item->fecha_anterior }}
                @else
                    NO
                @endif
            </th>
        </tr>
    @endforeach
</table>
