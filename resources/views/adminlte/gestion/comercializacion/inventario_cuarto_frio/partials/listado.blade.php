<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 1em" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Color
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Presentacion
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Tallos
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                    Longitud
                </th>
                @php
                    $totales_fecha = [];
                @endphp
                @for ($i = 0; $i <= 5; $i++)
                    @php
                        $fecha = opDiasFecha('-', $i, hoy());
                        $totales_fecha[] = 0;
                    @endphp
                    <th class="padding_lateral_5 text-center bg-yura_dark">
                        {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha)))] }}
                        {{ $i == 5 ? '...' : '' }}<br>
                        <small>{{ explode(' del ', convertDateToText($fecha))[0] }}</small>
                    </th>
                @endfor
                <th class="text-center th_yura_green" style="width: 60px">
                    Total
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr id="tr_item_{{ $pos }}" data-id_variedad="{{ $item['item']->id_variedad }}"
                    data-id_empaque="{{ $item['item']->id_empaque }}"
                    data-tallos_x_ramo="{{ $item['item']->tallos_x_ramo }}"
                    data-longitud_ramo="{{ $item['item']->longitud_ramo }}"
                    onmouseover="$(this).css('background-color', '#dddddd')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['item']->pres_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="width: 60px; border-color: #9d9d9d">
                        {{ $item['item']->tallos_x_ramo }}
                    </th>
                    <th class="padding_lateral_5" style="width: 60px; border-color: #9d9d9d">
                        {{ $item['item']->longitud_ramo }} cm
                    </th>
                    @php
                        $total_item = 0;
                    @endphp
                    @for ($i = 0; $i <= 5; $i++)
                        @php
                            $fecha = opDiasFecha('-', $i, hoy());
                            $valor = 0;
                            foreach ($item['valores'] as $val) {
                                if ($i < 5) {
                                    // desde hoy hasta 4 dias de antiguedad
                                    if ($val->fecha == $fecha) {
                                        $valor += $val->cantidad;
                                    }
                                } else {
                                    // todo lo que tiene 5 o mas dias de antiguedad
                                    if ($val->fecha <= $fecha) {
                                        $valor += $val->cantidad;
                                    }
                                }
                            }
                            $total_item += $valor;
                            $totales_fecha[$i] += $valor;
                        @endphp
                        <td class="text-center mouse-hand" style="border-color: #9d9d9d; background-color: #eeeeee"
                            onmouseover="$(this).css('background-color', 'cyan')"
                            onmouseleave="$(this).css('background-color', '#eeeeee')"
                            onclick="modal_inventario('{{ $pos }}', '{{ $fecha }}')">
                            {{ $valor }}
                        </td>
                    @endfor
                    <th class="text-center" style="width: 60px; border-color: #9d9d9d">
                        {{ $total_item }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green" colspan="5">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_fecha as $i => $val)
                @php
                    $fecha = opDiasFecha('-', $i, hoy());
                @endphp
                <th class="text-center bg-yura_dark">
                    <button type="button" class="btn btn-xs btn-yura_dark btn-block"
                        onclick="modal_inventario('T', '{{ $fecha }}')">
                        {{ $val }}
                    </button>
                </th>
                @php
                    $total += $val;
                @endphp
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total }}
            </th>
        </tr>
    </table>
</div>
<script>
    function modal_inventario(pos, fecha) {
        if (pos != 'T')
            datos = {
                variedad: $('#tr_item_' + pos).data('id_variedad'),
                empaque: $('#tr_item_' + pos).data('id_empaque'),
                tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
                longitud: $('#tr_item_' + pos).data('longitud_ramo'),
                pos: pos,
                fecha: fecha,
            };
        else
            datos = {
                planta: $('#filtro_planta').val(),
                variedad: $('#filtro_variedad').val(),
                empaque: $('#filtro_presentacion').val(),
                tallos_x_ramo: '',
                longitud: '',
                pos: pos,
                fecha: fecha,
            };
        get_jquery('{{ url('inventario_cuarto_frio/modal_inventario') }}', datos, function(retorno) {
            modal_view('moda-view_modal_inventario', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Inventario', true, false,
                '{{ isPC() ? '75%' : '' }}');
        });
    }
</script>
