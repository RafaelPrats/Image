@if (count($listado) > 0)
    <table class="table-bordered table-striped table-responsive" width="100%;" id="table_clasificacion_blanco"
        style="border: 1px solid #9d9d9d;">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Variedad
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Color
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Presentacion
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 50px">
                        Tallos
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Longitud
                    </div>
                    <input type="hidden" id="count_fechas" value="{{ count($fechas) }}">
                </th>
                @php
                    $totales_fecha = [];
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $fecha)
                    <th class="text-center"
                        style="background-color: #e9ecef; border-color: #9d9d9d; border-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                        <input type="hidden" class="input_fechas" id="fecha_{{ $pos_fecha }}"
                            value="{{ $fecha->fecha_pedido }}">
                        <div style="width: 120px">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha->fecha_pedido)))] }}<br>
                            <small>{{ $fecha->fecha_pedido }}</small>
                        </div>
                    </th>
                    <th class="text-center" colspan="2"
                        style="background-color: #e9ecef; border-color: #9d9d9d; border-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Generar Excel"
                            onclick="exportar_reporte('{{ $fecha->fecha_pedido }}')">
                            <i class="fa fa-fw fa-file-excel-o"></i>
                        </button>
                    </th>
                    @php
                        $totales_fecha[] = [
                            'pedidos' => 0,
                            'actuales' => 0,
                            'cambios' => 0,
                        ];
                        $pos_fecha++;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green" style="width: 60px">
                    Cuarto Frio
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    Armado
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    Mesa
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    <div class="btn-group">
                        <button type="button" class="btn btn-box-tool btn-yura_default dropdown-toggle"
                            data-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-fw fa-filter"></i> Mostrar...
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                            <li>
                                <a href="javascript:void(0)" onclick="mostrar_faltantes()">
                                    Mostrar solo Faltantes
                                </a>
                                <a href="javascript:void(0)" onclick="mostrar_armados()">
                                    Mostrar solo Armados
                                </a>
                                <a href="javascript:void(0)" onclick="mostrar_todo()">
                                    Mostrar Todo
                                </a>
                            </li>
                        </ul>
                    </div>
                </th>
            </tr>
        </thead>
        @php
            $pos_comb = 1;
        @endphp
        <tbody>
            @foreach ($listado as $pos_item => $item)
                @php
                    if ($item['tallos_x_ramos'] != '') {
                        $tallos_x_ramo = $item['tallos_x_ramos'] . ' tallos ';
                    } else {
                        $tallos_x_ramo = '';
                    }
                    if (($item['longitud_ramo'] != '' || $item['longitud_ramo'] >= 0) && $item['id_unidad_medida'] != '') {
                        $longitud_ramo = $item['longitud_ramo'] . $item['siglas_longitud'];
                    } else {
                        $longitud_ramo = '';
                    }
                    $texto = $item['var_nombre'] . ' ' . $item['empaque_p'] . ' ' . $tallos_x_ramo . '' . $longitud_ramo;
                    $tieneDistribucionesPendientes = tieneDistribucionesPendientes($fechas[0]->fecha_pedido, $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);
                    $color = getColorByNombre($item['var_nombre']);
                    $bg_color = $color != '' ? $color->fondo : '';
                    $text_color = $color != '' ? $color->texto : '';
                @endphp
                <tr id="tr_combinacion_{{ $pos_comb }}" onmouseover="$(this).css('background-color','#ADD8E6')"
                    onmouseleave="$(this).css('background-color','')" class="tr_listado">
                    <th class="text-center"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        @if (!$tieneDistribucionesPendientes)
                            <i class="fa fa-fw fa-exclamation-triangle" title="Falta distribucion de mixtos"></i>
                        @endif
                        {{ $planta->nombre }}
                    </th>
                    <th class="text-center"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        @if ($item['receta'] == 0)
                            <a href="javascript:void(0)" style="color: {{ $text_color }}"
                                onclick="modal_inventario_color('{{ $item['id_variedad'] }}')">
                                {{ $item['var_nombre'] }}
                            </a>
                        @else
                            <button type="button" class="btn btn-xs btn-block btn-yura_default"
                                title="Ver Receta Bouquet"
                                onclick="ver_receta('{{ $item['id_variedad'] }}', '{{ $item['id_empaque_p'] }}', '{{ $item['tallos_x_ramos'] }}', '{{ $item['longitud_ramo'] }}')">
                                {{ $item['var_nombre'] }}
                            </button>
                        @endif
                        <input type="hidden" id="texto_{{ $pos_comb }}" value="{{ $texto }}">
                        <input type="hidden" id="id_variedad_{{ $pos_comb }}" value="{{ $item['id_variedad'] }}">
                        <input type="hidden" id="tallos_x_ramo_{{ $pos_comb }}"
                            value="{{ $item['tallos_x_ramos'] }}">
                        <input type="hidden" id="longitud_ramo_{{ $pos_comb }}"
                            value="{{ $item['longitud_ramo'] }}">
                        {{-- <input type="hidden" id="id_empaque_e_{{$pos_comb}}" value="{{$item->id_empaque_e}}"> --}}
                        <input type="hidden" id="id_empaque_p_{{ $pos_comb }}"
                            value="{{ $item['id_empaque_p'] }}">
                        <input type="hidden" id="id_unidad_medida_{{ $pos_comb }}"
                            value="{{ $item['id_unidad_medida'] }}">
                    </th>
                    <th class="text-center"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $item['empaque_p'] }}
                    </th>
                    <th class="text-center"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $item['tallos_x_ramos'] }}
                    </th>
                    <th class="text-center"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $longitud_ramo }}
                    </th>
                    @php
                        $total_inventario = getDisponibleInventarioFrio($item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida']);
                        $pos_fecha = 1;
                        $acumulado_pedido = 0;
                    @endphp
                    @foreach ($fechas as $pos_f => $fecha)
                        @php
                            $cant_pedido = getCantidadRamosPedidosForCB($fecha->fecha_pedido, $item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);
                            $pedido = $cant_pedido['cant_pedido'];
                            $val_ped = '';
                            if ($item['valores'] != '') {
                                $pedido = $cant_pedido['cant_pedido'] + $item['valores'][$pos_f]['pedido'];
                                $val_ped = $item['valores'][$pos_f]['pedido'];
                            }
                            $acumulado_pedido += $pedido + $cant_pedido['cant_mod'];
                            $saldo = $total_inventario - $acumulado_pedido;
                            $totales_fecha[$pos_f]['pedidos'] += $pedido;
                            $totales_fecha[$pos_f]['actuales'] += $pedido + $cant_pedido['cant_mod'];

                            $cant_dist = getCantidadRamosDistribuidosForCB($fecha->fecha_pedido, $item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo']);
                        @endphp
                        <td class="text-center"
                            style="border-color: #9d9d9d; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                            <input type="hidden" id="pedido_{{ $pos_comb }}_{{ $pos_fecha }}"
                                value="{{ $pedido }}">
                            <input type="hidden" value="{{ $cant_pedido['cant_mod'] }}"
                                id="btn_modificaciones_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                            <input type="hidden" id="pedidos_actuales_{{ $pos_comb }}_{{ $pos_fecha }}"
                                value="{{ $pedido + $cant_pedido['cant_mod'] }}">
                            <input type="hidden" id="pedido_saldo_{{ $pos_comb }}_{{ $pos_fecha }}"
                                value="{{ $saldo }}">
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
                        <td class="text-center" style="border-color: #9d9d9d;"
                            onmouseover="$(this).css('background-color','#ADD8E6')"
                            onmouseleave="$(this).css('background-color','')">
                            @php
                                if ($cant_pedido['cant_mod'] > 0) {
                                    $class_btn = 'primary';
                                    $totales_fecha[$pos_f]['cambios'] += $cant_pedido['cant_mod'];
                                } else {
                                    $class_btn = 'danger';
                                    $totales_fecha[$pos_f]['cambios'] -= $cant_pedido['cant_mod'] * -1;
                                }
                            @endphp
                            @if ($cant_pedido['cant_mod'] != 0)
                                <button type="button" class="btn btn-xs btn-yura_{{ $class_btn }}"
                                    title="Cambios"
                                    onclick="ver_cambios('{{ $pos_comb }}', '{{ $fecha->fecha_pedido }}')">
                                    {{ $cant_pedido['cant_mod'] > 0 ? '+' : '' }}{{ number_format($cant_pedido['cant_mod']) }}
                                </button>
                            @endif
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                            @if ($cant_dist > 0)
                                <button type="button" class="btn btn-xs btn-yura_default"
                                    style="background-color: #00f3ff !important; color: black !important; border-color: #00b5be !important"
                                    title="Distribuidos">
                                    {{ number_format($cant_dist) }}
                                </button>
                            @endif
                        </td>
                        @php
                            $pos_fecha++;
                        @endphp
                    @endforeach
                    <td class="text-center" style="border-color: #9d9d9d;" width="7%">
                        <input type="hidden" id="inventario_frio_{{ $pos_comb }}"
                            value="{{ $total_inventario }}">
                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            id="btn_inventario_{{ $pos_comb }}"
                            @if (es_server()) onclick="maduracion('{{ $pos_comb }}')" @endif>
                            {{ $total_inventario }}
                        </button>
                    </td>
                    <td class="text-center" style=" border-color: #9d9d9d;" width="7%">
                        <input type="number" style="width: 100%" id="armar_{{ $pos_comb }}" min="0"
                            onchange="calcular_inventario_i('{{ $pos_comb }}', '{{ $pos_comb - 1 }}')"
                            class="text-center" value="0">
                    </td>
                    <td class="text-center" style=" border-color: #9d9d9d;" width="7%">
                        <input type="number" style="width: 100%" id="mesa_{{ $pos_comb }}" min="0"
                            class="text-center" onkeypress="isNumer(event)">
                    </td>
                    <td class="text-center" style=" border-color: #9d9d9d;" width="7%">
                        @if (es_server())
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Armar"
                                    onclick="modal_armar_row('{{ $pos_comb }}')">
                                    <i class="fa fa-fw fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Inventario"
                                    onclick="modal_inventario_row('{{ $pos_comb }}')">
                                    <i class="fa fa-fw fa-gift"></i>
                                </button>
                            </div>
                        @endif
                    </td>
                </tr>
                @php
                    $pos_comb++;
                @endphp
            @endforeach
        </tbody>
        <tfoot id="tfoot_listado">
            <tr>
                <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #e9ecef"
                    colspan="5">
                    TOTALES
                </th>
                @php
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $pos_f => $fecha)
                    <th style="border-color: #9d9d9d; border-bottom-width: {{ $pos_fecha == 1 ? '3px' : '' }}; background-color: #e9ecef;
                        border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }}; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};"
                        class="text-center" colspan="3">
                        @php
                            if ($totales_fecha[$pos_f]['cambios'] > 0) {
                                $class_btn = 'primary';
                            } else {
                                $class_btn = 'danger';
                            }
                        @endphp
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark" title="Pedidos">
                                {{ number_format($totales_fecha[$pos_f]['pedidos']) }}
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Actuales">
                                {{ number_format($totales_fecha[$pos_f]['actuales']) }}
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_{{ $class_btn }}" title="Cambios">
                                {{ $totales_fecha[$pos_f]['cambios'] > 0 ? '+' : '' }}{{ number_format($totales_fecha[$pos_f]['cambios']) }}
                            </button>
                        </div>
                        @if ($all_filtros)
                            <br>
                            <div class="btn-group">
                                @if ($pos_fecha == 1)
                                    <button type="button" class="btn btn-xs btn-yura_primary"
                                        title="Confirmar pedidos" id="btn_confirmar_pedidos"
                                        onclick="confirmar_pedidos('{{ $pos_comb - 1 }}')">
                                        <i class="fa fa-fw fa-arrow-up"></i> Confirmar Pedidos
                                    </button>
                                @endif
                            </div>
                        @endif
                    </th>
                    @php
                        $pos_fecha++;
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" colspan="4">
                    @if (es_server())
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Guardar armados"
                            onclick="store_armar('{{ $pos_comb - 1 }}')">
                            <i class="fa fa-fw fa-save"></i> Guardar
                        </button>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" id="pos_comb_total" value="{{ $pos_comb - 1 }}">

    <style>
        #tr_fija_top_0 th {
            position: sticky;
            top: 0;
            z-index: 9;
        }
    </style>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados que mostrar
    </div>
@endif

<script>
    function ver_receta(variedad, empaque, tallos_x_ramo, longitud) {
        datos = {
            variedad: variedad,
            empaque: empaque,
            tallos_x_ramo: tallos_x_ramo,
            longitud: longitud,
        };
        get_jquery('{{ url('clasificacion_blanco/ver_receta') }}', datos, function(retorno) {
            modal_view('moda-view_ver_receta', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Ver Receta', true, false,
                '{{ isPC() ? '95%' : '' }}');
        });
    }

    function distribuir_trabajo(pos_comb, pos_fecha) {
        datos = {
            variedad: $('#id_variedad_' + pos_comb).val(),
            empaque: $('#id_empaque_p_' + pos_comb).val(),
            tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
            longitud: $('#longitud_ramo_' + pos_comb).val(),
            pedidos: $('#pedido_' + pos_comb + '_' + pos_fecha).val(),
            actuales: $('#pedidos_actuales_' + pos_comb + '_' + pos_fecha).val(),
            saldo: $('#pedido_saldo_' + pos_comb + '_' + pos_fecha).val(),
            fecha: $('#fecha_' + pos_fecha).val(),
            pos_comb: pos_comb,
            pos_fecha: pos_fecha,
        };
        get_jquery('{{ url('clasificacion_blanco/distribuir_trabajo') }}', datos, function(retorno) {
            modal_view('moda-view_distribuir_trabajo', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Distribuir Trabajo', true, false,
                '{{ isPC() ? '75%' : '' }}');
        });
    }

    function ver_cambios(pos, fecha) {
        datos = {
            variedad: $('#id_variedad_' + pos).val(),
            empaque: $('#id_empaque_p_' + pos).val(),
            tallos_x_ramo: $('#tallos_x_ramo_' + pos).val(),
            longitud: $('#longitud_ramo_' + pos).val(),
            fecha: fecha
        };
        get_jquery('{{ url('clasificacion_blanco/ver_cambios') }}', datos, function(retorno) {
            modal_view('moda-view_ver_cambios', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Ver Cambios', true, false,
                '{{ isPC() ? '95%' : '' }}');
        });
    }

    function modal_inventario_color(id_var) {
        datos = {
            id_var: id_var,
            dias: $('#filtro_dias').val()
        };
        get_jquery('{{ url('clasificacion_blanco/modal_inventario_color') }}', datos, function(retorno) {
            modal_view('moda-view_modal_inventario_color', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Ver INVENTARIO', true, false,
                '{{ isPC() ? '99%' : '' }}');
        });
    }

    function mostrar_faltantes() {
        $('.tr_listado').addClass('hidden');
        $('.tr_listado_faltante').removeClass('hidden');
        $('#tfoot_listado').addClass('hidden');
    }

    function mostrar_armados() {
        $('.tr_listado').removeClass('hidden');
        $('.tr_listado_faltante').addClass('hidden');
        $('#tfoot_listado').addClass('hidden');
    }

    function mostrar_todo() {
        $('.tr_listado').removeClass('hidden');
        $('#tfoot_listado').removeClass('hidden');
    }
</script>
