@if (count($listado) > 0)
    <table class="table-bordered table-striped table-responsive" width="100%;" id="table_reporte_por_marcaciones"
        style="border: 1px solid #9d9d9d;">
        <thead>
            <tr id="tr_fija_top_0">
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
                <th class="text-center th_yura_green">
                    <div style="width: 50px">
                        {{ $marcacion->nombre }}
                    </div>
                </th>
                @php
                    $totales_fecha = [];
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $fecha)
                    <th class="text-center"
                        style="background-color: #e9ecef; border-color: #9d9d9d; border-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                        <input type="hidden" id="fecha_{{ $pos_fecha }}" value="{{ $fecha->fecha_pedido }}">
                        <div style="width: 100px">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha->fecha_pedido)))] }}<br>
                            <small>{{ $fecha->fecha_pedido }}</small>
                        </div>
                    </th>
                    @php
                        $totales_fecha[] = 0;
                        $pos_fecha++;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Invent.
                    </div>
                </th>
            </tr>
        </thead>
        @php
            $pos_comb = 1;
            $total_inventario = 0;
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
                    
                    $inventario = getInventarioFrioByMarcacion($item['id_variedad'], $item['id_dato_exportacion'], $item['marcacion'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo']);
                    $total_inventario += $inventario;
                @endphp
                <tr id="tr_combinacion_{{ $pos_comb }}" onmouseover="$(this).css('background-color','#ADD8E6')"
                    onmouseleave="$(this).css('background-color','')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['var_nombre'] }}
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
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['empaque_p'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['tallos_x_ramos'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $longitud_ramo }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['marcacion'] }}
                    </th>
                    @php
                        $pos_fecha = 1;
                        $invt_restante = $inventario;
                    @endphp
                    @foreach ($fechas as $pos_f => $fecha)
                        @php
                            $pedido = getCantidadRamosPedidosByMarcacion($fecha->fecha_pedido, $item['id_variedad'], $item['id_clasificacion_ramo'], $item['id_empaque_p'], $item['tallos_x_ramos'], $item['longitud_ramo'], $item['id_unidad_medida'], $item['marcacion'], $item['id_dato_exportacion'], $ids_pedidos);
                            if ($item['valores'] != '') {
                                $pedido += $item['valores'][$pos_f]['pedido'];
                            }
                            $totales_fecha[$pos_f] += $pedido;
                            
                            if ($invt_restante >= $pedido) {
                                $clase_btn = 'btn-yura_primary';
                            } else {
                                $clase_btn = 'btn-yura_danger';
                            }
                            $invt_restante -= $pedido;
                        @endphp
                        <td class="text-center"
                            style="border-color: #9d9d9d; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }}; border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }};">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_dark">
                                    {{ number_format($pedido, 0) }}
                                </button>
                                @if ($pedido > 0)
                                    <button type="button" class="btn btn-xs {{ $clase_btn }}">
                                        {{ $invt_restante }}
                                    </button>
                                @endif
                            </div>
                        </td>
                        @php
                            $pos_fecha++;
                        @endphp
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $inventario }}
                    </th>
                </tr>
                @php
                    $pos_comb++;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="padding_lateral_5" style="border-color: #9d9d9d; background-color: #e9ecef" colspan="5">
                    TOTALES
                </th>
                @php
                    $pos_fecha = 1;
                @endphp
                @foreach ($fechas as $pos_f => $fecha)
                    <th style="border-color: #9d9d9d; border-bottom-width: {{ $pos_fecha == 1 ? '3px' : '' }}; background-color: #e9ecef;
                        border-right-width: {{ $pos_fecha == 1 ? '3px' : '' }}; border-left-width: {{ $pos_fecha == 1 ? '3px' : '' }};"
                        class="text-center">
                        {{ number_format($totales_fecha[$pos_f]) }}
                    </th>
                    @php
                        $pos_fecha++;
                    @endphp
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{ number_format($total_inventario) }}
                </th>
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
