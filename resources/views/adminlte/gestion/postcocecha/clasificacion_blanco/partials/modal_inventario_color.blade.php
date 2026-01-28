<legend class="text-center" style="margin-bottom: 5px; font-size: 1.2em">
    INVENTARIO de <b>{{ $planta->nombre }} {{ $variedad->nombre }}</b>
</legend>
<input type="hidden" id="inv_id_variedad" value="{{ $variedad->id_variedad }}">

<div style="overflow-y: scroll; max-height: 800px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green" style="width: 50%">
                INVENTARIO ACTUAL
            </th>
            <th class="text-center th_yura_green" style="width: 50%">
                PEDIDOS
            </th>
        </tr>
        <tr>
            <td class="" style="vertical-align: top; position: relative;">
                <div style="position: absolute; background-color: #008000ba; height: {{ 27 * (count($inventarios) + 1) }}px; width: 100%; top: 0px; z-index: 999; vertical-align: center; color: white"
                    class="text-center hidden" id="div_banner_inventario">
                    <h3 style="top: 84px; position: absolute; right: 0px;">
                        <b>SELECCIONE LA PRESENTACION <i class="fa fa-fw fa-arrow-right"></i></b>
                    </h3>
                </div>
                <table class="table-bordered" id="table_inventario_color"
                    style="width: 100%; border: 1px solid #9d9d9d; position: absolute; top: 0px">
                    <tr>
                        <th class="padding_lateral_5 bg-yura_dark">
                            PRESENTACION
                        </th>
                        <th class="text-center bg-yura_dark">
                            TxR
                        </th>
                        <th class="text-center bg-yura_dark">
                            LONGITUD
                        </th>
                        <th class="text-center bg-yura_dark">
                            DIAS
                        </th>
                        <th class="text-center bg-yura_dark">
                            CANTIDAD
                        </th>
                        <th class="text-center bg-yura_dark">
                        </th>
                    </tr>
                    @foreach ($inventarios as $pos_inv => $inv)
                        <tr onmouseover="$(this).css('background-color','#ADD8E6')"
                            onmouseleave="$(this).css('background-color','')">
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ $inv->presentacion }}

                                <input type="hidden" id="inv_presentacion_{{ $pos_inv }}"
                                    value="{{ $inv->id_empaque_p }}">
                                <input type="hidden" id="inv_tallos_x_ramo_{{ $pos_inv }}"
                                    value="{{ $inv->tallos_x_ramo }}">
                                <input type="hidden" id="inv_longitud_ramo_{{ $pos_inv }}"
                                    value="{{ $inv->longitud_ramo }}">
                                <input type="hidden" id="inv_fecha_ingreso_{{ $pos_inv }}"
                                    value="{{ $inv->fecha_ingreso }}">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $inv->tallos_x_ramo }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $inv->longitud_ramo }}cm
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" title="{{ $inv->fecha_ingreso }}">
                                {{ difFechas(hoy(), $inv->fecha_ingreso)->days }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                <input type="number" style="width: 100%" class="text-center"
                                    id="inv_cantidad_{{ $pos_inv }}" value="{{ $inv->cantidad }}" min="0"
                                    max="{{ $inv->cantidad }}">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                <button type="button" class="btn btn-xs btn-yura_primary" title="Seleccionar Inventario a CAMBIAR"
                                    onclick="seleccionar_cambio_presentacion('{{ $pos_inv }}')">
                                    <i class="fa fa-fw fa-arrow-right"></i>
                                </button>
                            </th>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td class="padding_lateral_5" style="vertical-align: top">
                <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                    <tr>
                        <th class="padding_lateral_5 bg-yura_dark">
                            PRESENTACION
                        </th>
                        <th class="text-center bg-yura_dark">
                            TxR
                        </th>
                        <th class="text-center bg-yura_dark">
                            LONGITUD
                        </th>
                        @foreach ($fechas as $f)
                            <th class="text-center" style="background-color: #dddddd; border-color: #9d9d9d">
                                {{ convertDateToText($f->fecha_pedido) }}
                            </th>
                        @endforeach
                        <th class="text-center bg-yura_dark">
                            ARMADOS
                        </th>
                    </tr>
                    @foreach ($listado as $pos_comb => $item)
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
                            $tieneDistribucionesPendientes = tieneDistribucionesPendientes($fechas[0]->fecha_pedido, $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $ids_pedidos);
                            $color = getColorByNombre($item['var_nombre']);
                            $bg_color = $color != '' ? $color->fondo : '';
                            $text_color = $color != '' ? $color->texto : '';
                        @endphp
                        <tr onmouseover="$(this).css('background-color','#ADD8E6')"
                            onmouseleave="$(this).css('background-color','')">
                            <th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d"
                                onclick="seleccionar_presentacion_a_cambiar('{{ $pos_comb }}')">
                                {{ $item['empaque_p'] }}
                                <input type="hidden" id="id_empaque_cambiar_{{ $pos_comb }}"
                                    value="{{ $item['id_empaque_p'] }}">
                                <input type="hidden" id="tallos_x_ramos_cambiar_{{ $pos_comb }}"
                                    value="{{ $item['tallos_x_ramos'] }}">
                                <input type="hidden" id="longitud_ramo_cambiar_{{ $pos_comb }}"
                                    value="{{ $item['longitud_ramo'] }}">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $item['tallos_x_ramos'] }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
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
                                    //$totales_fecha[$pos_f]['pedidos'] += $pedido;
                                    //$totales_fecha[$pos_f]['actuales'] += $pedido + $cant_pedido['cant_mod'];

                                    $cant_dist = getCantidadRamosDistribuidosForCB($fecha->fecha_pedido, $item['id_variedad'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo']);
                                @endphp
                                <td class="text-center"
                                    style="border-color: #9d9d9d; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-yura_warning"
                                            title="Pedidos Actuales">
                                            {{ number_format($pedido + $cant_pedido['cant_mod']) }}
                                        </button>
                                        <button type="button"
                                            class="btn btn-xs btn-yura_{{ $saldo >= 0 ? 'primary' : 'danger' }}"
                                            title="SALDO">
                                            {{ number_format($saldo) }}
                                        </button>
                                    </div>
                                </td>
                            @endforeach
                            <th class="text-center" style="border-color: #9d9d9d;">
                                {{ $total_inventario }}
                            </th>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
</div>

<input type="hidden" id="pos_inv_selected">
<input type="hidden" id="pos_presentacion_selected">
<script>
    function seleccionar_cambio_presentacion(pos) {
        $('#pos_inv_selected').val(pos);
        $('#pos_presentacion_selected').val('');
        $('#div_banner_inventario').removeClass('hidden');
    }

    function seleccionar_presentacion_a_cambiar(pos) {
        $('#pos_presentacion_selected').val(pos);
        if ($('#pos_inv_selected').val() != '') {
            store_cambiar_presentacion();
        }
    }

    function store_cambiar_presentacion() {
        pos_inv = $('#pos_inv_selected').val();
        inventario = {
            empaque: $('#inv_presentacion_' + pos_inv).val(),
            tallos_x_ramo: $('#inv_tallos_x_ramo_' + pos_inv).val(),
            longitud_ramo: $('#inv_longitud_ramo_' + pos_inv).val(),
            fecha_ingreso: $('#inv_fecha_ingreso_' + pos_inv).val(),
            cantidad: $('#inv_cantidad_' + pos_inv).val(),
        }
        pos_comb = $('#pos_presentacion_selected').val();
        cambio = {
            empaque: $('#id_empaque_cambiar_' + pos_comb).val(),
            tallos_x_ramo: $('#tallos_x_ramos_cambiar_' + pos_comb).val(),
            longitud_ramo: $('#longitud_ramo_cambiar_' + pos_comb).val(),
        }
        datos = {
            _token: '{{ csrf_token() }}',
            inventario: inventario,
            cambio: cambio,
            variedad: $('#inv_id_variedad').val(),
        }
        modal_quest('modal_quest_store_cambiar_presentacion', '<div class="alert alert-info text-center">' +
            '<h3>¿Está seguro de <strong>CAMBIAR</strong> el inventario?</h3></div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('clasificacion_blanco/store_cambiar_presentacion') }}', datos, function() {
                    cerrar_modals();
                    modal_inventario_color(datos['variedad']);
                });
            });
    }
</script>
