@php
    if ($tallos_x_ramo != '') {
        $texto_tallos_x_ramo = $tallos_x_ramo . ' tallos ';
    } else {
        $tallos_x_ramo = '';
    }
    if ($longitud_ramo != '' || $longitud_ramo >= 0) {
        $texto_longitud_ramo = $longitud_ramo . 'cm';
    } else {
        $longitud_ramo = '';
    }
    $texto = $variedad->nombre . ' ' . 0 . 'gr' . ' ' . $presentacion->nombre . ' ' . $texto_tallos_x_ramo . '' . $texto_longitud_ramo;
    $tieneDistribucionesPendientes = tieneDistribucionesPendientes($fechas[0], $presentacion->id_empaque, $tallos_x_ramo, $longitud_ramo, 1, $ids_pedidos);
    $color = getColorByNombre($variedad->nombre);
    $bg_color = $color != '' ? $color->fondo : '';
    $text_color = $color != '' ? $color->texto : '';
@endphp
<th class="text-center"
    style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
    @if (!$tieneDistribucionesPendientes)
        <i class="fa fa-fw fa-exclamation-triangle" title="Falta distribucion de mixtos"></i>
    @endif
    {{ $variedad->planta->nombre }}
</th>
<th class="text-center"
    style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
    @if ($variedad->receta == 0)
        {{ $variedad->nombre }}
    @else
        <button type="button" class="btn btn-xs btn-block btn-yura_default" title="Ver Receta Bouquet"
            onclick="ver_receta('{{ $variedad->id_variedad }}', '{{ $presentacion->id_empaque }}', '{{ $tallos_x_ramo }}', '{{ $longitud_ramo }}')">
            {{ $variedad->nombre }}
        </button>
    @endif
    <input type="hidden" id="texto_{{ $pos_comb }}" value="{{ $texto }}">
    <input type="hidden" id="id_variedad_{{ $pos_comb }}" value="{{ $variedad->id_variedad }}">
    <input type="hidden" id="tallos_x_ramo_{{ $pos_comb }}" value="{{ $tallos_x_ramo }}">
    <input type="hidden" id="longitud_ramo_{{ $pos_comb }}" value="{{ $longitud_ramo }}">
    <input type="hidden" id="id_empaque_p_{{ $pos_comb }}" value="{{ $presentacion->id_empaque }}">
    <input type="hidden" id="id_unidad_medida_{{ $pos_comb }}" value="1">
</th>
<th class="text-center"
    style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
    {{ $presentacion->nombre }}
</th>
<th class="text-center"
    style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
    {{ $tallos_x_ramo }}
</th>
<th class="text-center"
    style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
    {{ $longitud_ramo }}cm
</th>
@php
    $total_inventario = getDisponibleInventarioFrio($variedad->id_variedad, $presentacion->id_empaque, $tallos_x_ramo, $longitud_ramo, 1);

    $pos_fecha = 1;
    $acumulado_pedido = 0;
@endphp
@foreach ($fechas as $pos_f => $fecha)
    @php
        $cant_pedido = getCantidadRamosPedidosForCB($fecha, $variedad->id_variedad, $presentacion->id_empaque, $tallos_x_ramo, intval($longitud_ramo), 1, $ids_pedidos);
        $pedido = $cant_pedido['cant_pedido'];
        $val_ped = '';
        if ($valores != '') {
            $pedido = $cant_pedido['cant_pedido'] + $valores[$pos_f]['pedido'];
            $val_ped = $valores[$pos_f]['pedido'];
        }
        $acumulado_pedido += $pedido + $cant_pedido['cant_mod'];
        $saldo = $total_inventario - $acumulado_pedido;
        //$totales_fecha[$pos_f]['pedidos'] += $pedido;
        //$totales_fecha[$pos_f]['actuales'] += $pedido + $cant_pedido['cant_mod'];
        $cant_dist = getCantidadRamosDistribuidosForCB($fecha, $variedad->id_variedad, $presentacion->id_empaque, $tallos_x_ramo, intval($longitud_ramo));
    @endphp
    <td class="text-center" style="border-color: #9d9d9d; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
        <input type="hidden" id="pedido_{{ $pos_comb }}_{{ $pos_fecha }}" value="{{ $pedido }}">
        <input type="hidden" value="{{ $cant_pedido['cant_mod'] }}"
            id="btn_modificaciones_fecha_{{ $pos_f }}_{{ $pos_comb }}">
        <input type="hidden" id="pedidos_actuales_{{ $pos_comb }}_{{ $pos_fecha }}"
            value="{{ $pedido + $cant_pedido['cant_mod'] }}">
        <input type="hidden" id="pedido_saldo_{{ $pos_comb }}_{{ $pos_fecha }}" value="{{ $saldo }}">
        <div class="btn-group">
            <button type="button" class="btn btn-xs btn-yura_dark" title="Pedidos"
                id="btn_pedido_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                {{ number_format($pedido, 0) }}
            </button>
            <button type="button" class="btn btn-xs btn-yura_warning" title="Pedidos Actuales"
                onclick="distribuir_trabajo('{{ $pos_comb }}', '{{ $pos_fecha }}')">
                {{ number_format($pedido + $cant_pedido['cant_mod'], 0) }}
            </button>

            @if ($saldo >= 0)
                <button type="button" class="btn btn-xs btn-yura_primary" title="Armados"
                    id="btn_armados_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                    {{ $saldo }}
                </button>
            @else
                @if ($pos_f == 0)
                    <script>
                        $('#btn_confirmar_pedidos').addClass('hidden');
                    </script>
                @endif
                <script>
                    $('#tr_combinacion_{{ $pos_comb }}').addClass('tr_listado_faltante');
                </script>
                <button type="button" class="btn btn-xs btn-yura_danger" title="Por armar"
                    id="btn_armados_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                    {{ number_format(substr($saldo, 1), 0) }}
                </button>
            @endif
        </div>
    </td>
    <td class="text-center" style="border-color: #9d9d9d;" onmouseover="$(this).css('background-color','#ADD8E6')"
        onmouseleave="$(this).css('background-color','')">
        @php
            if ($cant_pedido['cant_mod'] > 0) {
                $class_btn = 'primary';
                //$totales_fecha[$pos_f]['cambios'] += $cant_pedido['cant_mod'];
            } else {
                $class_btn = 'danger';
                //$totales_fecha[$pos_f]['cambios'] -= $cant_pedido['cant_mod'] * -1;
            }
        @endphp
        @if ($cant_pedido['cant_mod'] != 0)
            <button type="button" class="btn btn-xs btn-yura_{{ $class_btn }}" title="Cambios"
                onclick="ver_cambios('{{ $pos_comb }}', '{{ $pos_fecha }}')">
                {{ $cant_pedido['cant_mod'] > 0 ? '+' : '' }}{{ number_format($cant_pedido['cant_mod']) }}
            </button>
        @endif
    </td>
    <td class="text-center" style="border-color: #9d9d9d; border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
        @if ($cant_dist > 0)
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-yura_default"
                    style="background-color: #00f3ff !important; color: black !important; border-color: #00b5be !important"
                    title="Distribuidos">
                    {{ number_format($cant_dist) }}
                </button>
            </div>
        @endif
    </td>
    @php
        $pos_fecha++;
    @endphp
@endforeach
<td class="text-center" style="border-color: #9d9d9d;" width="7%">
    <input type="hidden" id="inventario_frio_{{ $pos_comb }}" value="{{ $total_inventario }}">
    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false" id="btn_inventario_{{ $pos_comb }}"
        @if (es_server()) onclick="maduracion('{{ $pos_comb }}')" @endif>
        {{ $total_inventario }}
    </button>
</td>
<td class="text-center" style=" border-color: #9d9d9d;" width="7%">
    <input type="number" style="width: 100%" id="armar_{{ $pos_comb }}" min="0"
        onchange="calcular_inventario_i('{{ $pos_comb }}', '{{ $pos_comb - 1 }}')" class="text-center"
        value="0">
</td>
<td class="text-center" style=" border-color: #9d9d9d;" width="7%">
    <input type="number" style="width: 100%" id="mesa_{{ $pos_comb }}" min="0" class="text-center"
        onkeypress="isNumer(event)">
</td>
<td class="text-center" style=" border-color: #9d9d9d;" width="7%">
    @if (es_server())
        <button type="button" class="btn btn-xs btn-yura_primary" onclick="modal_armar_row('{{ $pos_comb }}')">
            <i class="fa fa-fw fa-check"></i>
        </button>
    @endif
</td>
