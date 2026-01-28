@if (count($listado) > 0)
    <table class="table-bordered table-striped table-responsive" width="100%;" id="table_clasificacion_blanco"
        style="border: 1px solid #9d9d9d;">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Color
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 50px">
                        Peso
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Presentación
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
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $fecha)
                    <th class="text-center"
                        style="background-color: #e9ecef; border-color: #9d9d9d; border-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                        <input type="hidden" id="fecha_{{ $pos_fecha }}" value="{{ $fecha->fecha_pedido }}">
                        <div style="width: 120px">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha->fecha_pedido)))] }}<br>
                            <small>{{ $fecha->fecha_pedido }}</small>
                        </div>
                    </th>
                    <th class="text-center"
                        style="background-color: #e9ecef; border-color: #9d9d9d; border-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Generar Excel"
                            onclick="exportar_reporte('{{ $fecha->fecha_pedido }}')">
                            <i class="fa fa-fw fa-file-excel-o"></i>
                        </button>
                    </th>
                    @php
                        $pos_fecha++;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">
                    Cuarto Frío
                </th>
                <th class="text-center th_yura_green">
                    Armado
                </th>
                <th class="text-center th_yura_green">
                    Mesa
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </thead>
        @php
            $pos_comb = 1;
        @endphp
        <tbody>
            @foreach ($listado as $item)
                @php
                    if ($item['tallos_x_ramos'] != '') {
                        $tallos_x_ramo = $item['tallos_x_ramos'] . ' tallos ';
                    } else {
                        $tallos_x_ramo = '';
                    }
                    if ($item['longitud_ramo'] != '' && $item['id_unidad_medida'] != '') {
                        $longitud_ramo = $item['longitud_ramo'] . $item['siglas_longitud'];
                    } else {
                        $longitud_ramo = '';
                    }
                    $texto = $item['var_nombre'] . ' ' . $item['nombre_peso'] . $item['siglas_peso'] . ' ' . $item['empaque_p'] . ' ' . $tallos_x_ramo . '' . $longitud_ramo;
                @endphp
                <tr id="tr_combinacion_{{ $pos_comb }}">
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{ $item['var_nombre'] }}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d"
                        id="th_pedidos_{{ $pos_comb }}">
                        {{ $item['nombre_peso'] . ' ' . $item['siglas_peso'] }}
                        <input type="hidden" id="texto_{{ $pos_comb }}" value="{{ $texto }}">
                        <input type="hidden" id="clasificacion_ramo_{{ $pos_comb }}"
                            value="{{ $item['id_clasificacion_ramo'] }}">
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
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{ $item['empaque_p'] }}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{ $item['tallos_x_ramos'] }}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{ $longitud_ramo }} <sup>{{$item['tipo']}}</sup>
                    </th>
                    @php
                        $total_inventario = getDisponibleInventarioFrio($item['id_variedad'], $item['id_clasificacion_ramo'], /*$item->id_empaque_e,*/ $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida']);

                        $pos_fecha = 1;
                        $acumulado_pedido = 0;
                    @endphp
                    @foreach ($fechas as $pos_f => $fecha)
                        @php
                            $cant_pedido = getCantidadRamosPedidosForCB($fecha->fecha_pedido, $item['id_variedad'], $item['id_clasificacion_ramo'], /*$item->id_empaque_e,*/ $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida']);
                            $pedido = $cant_pedido['cant_pedido'];
                            $val_ped = '';
                            if ($item['valores'] != '') {
                                $pedido = $cant_pedido['cant_pedido'] + $item['valores'][$pos_f]['pedido'];
                                $val_ped = $item['valores'][$pos_f]['pedido'];
                            }
                            $acumulado_pedido += $pedido + $cant_pedido['cant_mod'];
                            $saldo = $total_inventario - $acumulado_pedido;
                        @endphp
                        <td class="text-center"
                            style="border-color: #9d9d9d; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};"
                            onmouseover="$(this).css('background-color','#ADD8E6')"
                            onmouseleave="$(this).css('background-color','')">
                            <input type="hidden" id="pedido_{{ $pos_comb }}_{{ $pos_fecha }}"
                                value="{{ $pedido }}">
                            <input type="hidden" value="{{ $cant_pedido['cant_mod'] }}"
                                id="btn_modificaciones_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_dark" title="Pedidos"
                                    id="btn_pedido_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                                    {{ number_format($pedido, 0) }}
                                    <sup>{{ $cant_pedido['cant_pedido'] }} +
                                        {{ $val_ped }}</sup>
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
                                    <button type="button" class="btn btn-xs btn-yura_danger" title="Por armar"
                                        id="btn_armados_fecha_{{ $pos_f }}_{{ $pos_comb }}">
                                        {{ number_format(substr($saldo, 1), 0) }}
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }};"
                            onmouseover="$(this).css('background-color','#ADD8E6')"
                            onmouseleave="$(this).css('background-color','')">
                            @php
                                if ($cant_pedido['cant_mod'] > 0) {
                                    $class_btn = 'primary';
                                } else {
                                    $class_btn = 'danger';
                                }
                            @endphp
                            @if ($cant_pedido['cant_mod'] != 0)
                                <button type="button" class="btn btn-xs btn-yura_{{ $class_btn }}" title="Cambios">
                                    {{ $cant_pedido['cant_mod'] > 0 ? '+' : '' }}{{ number_format($cant_pedido['cant_mod']) }}
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
                            id="btn_inventario_{{ $pos_comb }}" onclick="maduracion('{{ $pos_comb }}')">
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
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="store_armar_row('{{ $pos_comb }}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </td>
                </tr>
                @php
                    $pos_comb++;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="border-color: #9d9d9d; background-color: #e9ecef" colspan="5"></td>
                @php
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $fecha)
                    <td style="border-color: #9d9d9d; border-bottom-width: {{ $pos_fecha == 1 ? '3px' : '' }}; background-color: #e9ecef;
                        border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }}; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};"
                        class="text-center" colspan="2">
                        @if ((((($variedad == $peso) == $presentacion) == $tallos) == $longitud) == '')
                            <div class="btn-group">
                                <!--<button type="button" class="btn btn-xs btn-yura_dark" title="Mandar a armar"
                        onclick="mostrar_despacho('{ $fecha->fecha_pedido }}')">
                        <i class="fa fa-fw fa-gift"></i>
                    </button>-->
                                @if ($pos_fecha == 1)
                                    <button type="button" class="btn btn-xs btn-yura_primary"
                                        title="Confirmar pedidos" id="btn_confirmar_pedidos"
                                        onclick="confirmar_pedidos('{{ $pos_comb - 1 }}')">
                                        <i class="fa fa-fw fa-arrow-up"></i> Confirmar Pedidos
                                    </button>
                                @endif
                            </div>
                        @endif
                    </td>
                    @php
                        $pos_fecha++;
                    @endphp
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" colspan="4">
                    <button type="button" class="btn btn-xs btn-yura_primary" title="Guardar armados"
                        onclick="store_armar('{{ $pos_comb - 1 }}')">
                        <i class="fa fa-fw fa-save"></i> Guardar
                    </button>
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
