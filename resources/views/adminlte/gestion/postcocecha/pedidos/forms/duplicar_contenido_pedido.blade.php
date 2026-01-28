@foreach ($detalles_combo as $pos_d => $det)
    <tr class="tr_form_ped_{{ $num_pos }}"
        onmouseover="$('.tr_form_ped_{{ $num_pos }}').addClass('bg-yura_dark')"
        onmouseleave="$('.tr_form_ped_{{ $num_pos }}').removeClass('bg-yura_dark')">
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                    id="ped_piezas_{{ $num_pos }}" min="0" value="{{ $piezas }}">
                <input type="hidden" class="pos_ped_especificaciones pos_ped_combo" value="{{ $num_pos }}">
                @if (count($detalles_combo) > 1)
                    <input type="hidden" id="cant_detalles_combo_{{ $num_pos }}"
                        value="{{ count($detalles_combo) }}">
                @endif
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <select id="ped_planta_{{ $num_pos }}_{{ $pos_d }}" style="width: 100%; color: black"
                    onchange="select_planta($(this).val(), 'ped_variedad_{{ $num_pos }}_{{ $pos_d }}', 'ped_variedad_{{ $num_pos }}_{{ $pos_d }}', ''); edit_seleccionar_planta('{{ $num_pos }}_{{ $pos_d }}')">
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}"
                            {{ $p->id_planta == $det['variedad']->id_planta ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            @else
                <select id="ped_planta_{{ $num_pos }}" style="width: 100%; color: black"
                    onchange="select_planta($(this).val(), 'ped_variedad_{{ $num_pos }}', 'ped_variedad_{{ $num_pos }}', ''); edit_seleccionar_planta('{{ $num_pos }}')">
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}"
                            {{ $p->id_planta == $det['variedad']->id_planta ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            @endif
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <select id="ped_variedad_{{ $num_pos }}_{{ $pos_d }}" style="width: 100%; color: black">
                    {!! getVariedadesByPlanta($det['variedad']->id_planta, 'option', $det['variedad']->id_variedad) !!}
                </select>
            @else
                <select id="ped_variedad_{{ $num_pos }}" style="width: 100%; color: black">
                    {!! getVariedadesByPlanta($det['variedad']->id_planta, 'option', $det['variedad']->id_variedad) !!}
                </select>
            @endif
        </td>
        @if ($pos_d == 0)
            <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles_combo) }}">
                <select id="ped_caja_{{ $num_pos }}" style="width: 100%; color: black">
                    @foreach ($cajas as $c)
                        <option value="{{ $c->id_empaque }}"
                            {{ $c->id_empaque == $caja->id_empaque ? 'selected' : '' }}>
                            {{ explode('|', $c->nombre)[0] }}
                        </option>
                    @endforeach
                </select>
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <select id="ped_presentacion_{{ $num_pos }}_{{ $pos_d }}"
                    style="width: 100%; color: black">
                    {!! getPresentacionesByClientePlanta($cliente, $det['variedad']->id_planta, $det['presentacion']->id_empaque) !!}
                </select>
            @else
                <select id="ped_presentacion_{{ $num_pos }}" style="width: 100%; color: black">
                    {!! getPresentacionesByClientePlanta($cliente, $det['variedad']->id_planta, $det['presentacion']->id_empaque) !!}
                </select>
            @endif
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <input type="number" style="width: 100%; color: black"
                    class="text-center ramos_x_caja_combo_{{ $num_pos }}" onchange="calcular_totales_pedido()"
                    onkeyup="calcular_totales_pedido()" id="ped_ramos_x_caja_{{ $num_pos }}_{{ $pos_d }}"
                    min="0" value="{{ $det['ramos_x_caja'] }}">
            @else
                <input type="number" style="width: 100%; color: black"
                    class="text-center ramos_x_caja_combo_{{ $num_pos }}" onchange="calcular_totales_pedido()"
                    onkeyup="calcular_totales_pedido()" id="ped_ramos_x_caja_{{ $num_pos }}" min="0"
                    value="{{ $det['ramos_x_caja'] }}">
            @endif
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    id="ped_total_ramos_{{ $num_pos }}" readonly disabled>
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <input type="number" style="width: 100%; color: black"
                    class="text-center tallos_x_ramos_combo_{{ $num_pos }}" onchange="calcular_totales_pedido()"
                    onkeyup="calcular_totales_pedido()"
                    id="ped_tallos_x_ramos_{{ $num_pos }}_{{ $pos_d }}" min="0"
                    value="{{ $det['tallos_x_ramos'] }}">
            @else
                <input type="number" style="width: 100%; color: black"
                    class="text-center tallos_x_ramos_combo_{{ $num_pos }}" onchange="calcular_totales_pedido()"
                    onkeyup="calcular_totales_pedido()" id="ped_tallos_x_ramos_{{ $num_pos }}" min="0"
                    value="{{ $det['tallos_x_ramos'] }}">
            @endif
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%; color: black" class="text-center"
                    id="ped_total_tallos_{{ $num_pos }}" readonly disabled>
            </td>
        @endif
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <input type="number" id="ped_longitud_{{ $num_pos }}_{{ $pos_d }}" class="text-center"
                    style="width: 100%; color: black" value="{{ $det['longitud'] }}">
            @else
                <input type="number" id="ped_longitud_{{ $num_pos }}" class="text-center"
                    style="width: 100%; color: black" value="{{ $det['longitud'] }}">
            @endif
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            @if (count($detalles_combo) > 1)
                <input type="number" style="width: 100%; color: black" class="text-center"
                    onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                    id="ped_precio_esp_{{ $num_pos }}_{{ $pos_d }}" min="0"
                    value="{{ $det['precio'] }}">
            @else
                <input type="number" style="width: 100%; color: black" class="text-center"
                    onchange="calcular_totales_pedido()" onkeyup="calcular_totales_pedido()"
                    id="ped_precio_esp_{{ $num_pos }}" min="0" value="{{ $det['precio'] }}">
            @endif
        </td>
        @if ($pos_d == 0)
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%; color: black" class="text-center"
                    id="ped_total_precio_caja_{{ $num_pos }}" readonly disabled>
            </td>
        @endif
        @if ($pos_d == 0)
            @foreach ($valores_marcaciones as $m)
                <td class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles_combo) }}">
                    <input type="text" style="width: 100%; color: black" class="text-center"
                        value="{{ $m->valor_marcacion }}"
                        id="ped_marcacion_{{ $m->id_marcacion }}_{{ $num_pos }}">
                </td>
            @endforeach
            <td class="text-center" rowspan="{{ count($detalles_combo) }}" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_danger"
                    onclick="delete_contenido_pedido({{ $num_pos }})" title="Eliminar Pieza">
                    <i class="fa fa-fw fa-trash"></i>
                </button>
            </td>
        @endif
    </tr>
@endforeach
