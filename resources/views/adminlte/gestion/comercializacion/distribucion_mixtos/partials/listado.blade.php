<div style="overflow-y: scroll; overflow-x: scroll">
    <input type="hidden" id="longitud_selected" value="{{ $longitud }}">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_cortes">
            <th class="text-center th_yura_green columna_fija_left_0" rowspan="2">
                <div style="width: 140px">
                    {{ $planta->nombre }}
                    @if (es_server())
                        <br>
                        <button type="button" class="btn btn-yura_default btn-block" onclick="duplicar_distribucion()">
                            <i class="fa fa-fw fa-copy"></i> Siguiente Día
                        </button>
                    @endif
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 80px">
                    SOLIDOS
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <input type="hidden" id="pedidos_mixtos" value="{{ $ramos_mixtos }}">
                <div style="width: 80px">
                    MIXTOS: <span class="span_ramos_mixtos_totales">{{ number_format($ramos_mixtos) }}</span>
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 80px">
                    TOTALES
                </div>
            </th>
            <th class="text-center bg-yura_dark" colspan="2">
                CUARTO FRIO
            </th>
            @foreach ($cortes as $pos_c => $c)
                <input type="hidden" class="id_cortes" value="{{ $c->id_proy_cortes }}">
                <th class="text-center bg-yura_dark" style="border-left: 2px solid">
                    {{ substr($c->nombre, 2) }}
                </th>
                <th class="text-center bg-yura_dark" id="th_uso_corte_{{ $c->id_proy_cortes }}">
                    <button type="button" id="btn_uso_corte_{{ $c->id_proy_cortes }}"
                        class="btn btn-xs btn-yura_{{ $c->usar == 1 ? 'primary' : 'danger' }}"
                        title="{{ $c->usar == 1 ? 'Tener en cuenta para las distribuciones' : 'No tener en cuenta' }}"
                        onclick="cambiar_uso_corte('{{ $c->id_proy_cortes }}', '{{ $c->usar }}')">
                        <i class="fa fa-fw fa-{{ $c->usar == 1 ? 'check' : 'times' }}"></i>
                    </button>
                    <input type="hidden" id="usar_corte_{{ $c->id_proy_cortes }}" value="{{ $c->usar }}">
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="border-left: 2px solid" colspan="2">
                <div style="width: 130px">
                    SALDO SOLIDOS + <span id="span_saldo_positivo_total_sd"></span>
                </div>
            </th>
            <th class="text-center th_yura_green" style="border-left: 2px solid">
                <div style="width: 130px">
                    SALDO TOTAL + <span id="span_saldo_positivo_total_cs"></span>
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 280px">
                    MIXTOS: <span class="span_ramos_mixtos_totales">{{ number_format($ramos_mixtos) }}</span>
                </div>
            </th>
        </tr>
        <tr>
            <th class="text-center bg-yura_dark">
                <div style="width: 70px">
                    Inventario
                </div>
            </th>
            <th class="text-center bg-yura_dark">
                <div style="width: 70px">
                    Saldo
                </div>
            </th>
            @foreach ($cortes as $pos_c => $c)
                <th class="text-center bg-yura_dark" style="border-left: 2px solid">
                    <div style="width: 70px">
                        Cosecha
                    </div>
                </th>
                <!--<th class="text-center bg-yura_dark">
                    Pedido
                </th>-->
                <th class="text-center bg-yura_dark">
                    <div style="width: 70px">
                        Saldo
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="border-left: 2px solid" colspan="2">
                <div style="width: 130px">
                    SALDO SOLIDOS - <span id="span_saldo_negativo_total_sd"></span>
                </div>
            </th>
            <th class="text-center th_yura_green" style="border-left: 2px solid">
                <div style="width: 130px">
                    SALDO TOTAL - <span id="span_saldo_negativo_total_cs"></span>
                </div>
            </th>
        </tr>
        @php
            $saldo_pos_total_sd = 0;
            $saldo_neg_total_sd = 0;
            $saldo_pos_total_cs = 0;
            $saldo_neg_total_cs = 0;
            $totales_solidos = 0;
            $total_pedidos_solidos = 0;
            $total_armados_mixtos = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            @php
                $solidos = $item['pedidos_solidos'] != null ? $item['pedidos_solidos'] : 0;
                $mixtos = $item['pedidos_mixtos'];
                $saldo_pos_var_sd = 0;
                $saldo_var_cs = 0;
                $total_pedidos_solidos += $solidos;
                $total_armados_mixtos += $mixtos;
            @endphp
            <input type="hidden" class="ids_variedades" value="{{ $item['var']->siglas }}">
            <tr id="tr_var_{{ $item['var']->siglas }}">
                <th class="text-center bg-yura_dark columna_fija_left_0">
                    {{ $item['var']->nombre }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($solidos) }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($mixtos) }}
                </th>
                <th class="text-center bg-yura_dark">
                    {{ number_format($solidos + $mixtos) }}
                </th>
                @php
                    $color_saldo = '';
                    $valor = $item['cuarto_frio'] != '' ? $item['cuarto_frio'] : 0;
                    if ($solidos >= $valor) {
                        $solidos -= $valor;
                        $saldo_sd = 0;
                        $color_saldo = 'text-color_yura_danger';
                    } else {
                        $saldo_sd = $valor - $solidos;
                        $solidos = 0;
                        if ($saldo_sd == $valor) {
                            $color_saldo = 'text-color_yura';
                        } else {
                            $color_saldo = 'text-color_yura_warning';
                        }
                    }
                    $saldo_cs = $saldo_sd;

                    if ($solidos == 0) {
                        if ($mixtos >= $saldo_cs) {
                            $mixtos -= $saldo_cs;
                            $saldo_cs = 0;
                            $color_saldo = 'text-color_yura_danger';
                        } else {
                            $saldo_cs -= $mixtos;
                            $mixtos = 0;
                            if ($saldo_cs == $valor) {
                                $color_saldo = 'text-color_yura';
                            } else {
                                $color_saldo = 'text-color_yura_warning';
                            }
                        }
                    }

                    $color_saldo = $valor == 0 ? '' : $color_saldo;
                    $saldo_pos_var_sd += $saldo_sd;
                    $saldo_pos_total_sd += $saldo_sd;
                    $saldo_var_cs += $saldo_cs;
                @endphp
                <td class="text-center" style="border-color: #9d9d9d; width: 100px; border-left: 2px solid">
                    {{ $item['cuarto_frio'] != '' ? number_format($item['cuarto_frio']) : '' }}
                </td>
                <th class="text-center {{ $color_saldo }}" style="border-color: #9d9d9d; width: 100px">
                    <input type="text" readonly style="width: 100%" value="{{ round($saldo_cs) }}"
                        class="text-center" disabled id="distribucion_cuarto_frio_var_{{ $item['var']->siglas }}">
                </th>
                @foreach ($item['valores'] as $pos_c => $v)
                    @php
                        $color_saldo = '';
                        if ($cortes[$pos_c]->usar == 1) {
                            $valor = $v != '' ? $v : 0;
                            if ($solidos >= $valor) {
                                $solidos -= $valor;
                                $saldo_sd = 0;
                                $color_saldo = 'text-color_yura_danger';
                            } else {
                                $saldo_sd = $valor - $solidos;
                                $solidos = 0;
                                if ($saldo_sd == $valor) {
                                    $color_saldo = 'text-color_yura';
                                } else {
                                    $color_saldo = 'text-color_yura_warning';
                                }
                            }
                            $saldo_cs = $saldo_sd;

                            if ($solidos == 0) {
                                if ($mixtos >= $saldo_cs) {
                                    $mixtos -= $saldo_cs;
                                    $saldo_cs = 0;
                                    $color_saldo = 'text-color_yura_danger';
                                } else {
                                    $saldo_cs -= $mixtos;
                                    $mixtos = 0;
                                    if ($saldo_cs == $valor) {
                                        $color_saldo = 'text-color_yura';
                                    } else {
                                        $color_saldo = 'text-color_yura_warning';
                                    }
                                }
                            }

                            $color_saldo = $valor == 0 ? '' : $color_saldo;
                            $saldo_pos_var_sd += $saldo_sd;
                            $saldo_pos_total_sd += $saldo_sd;
                            $saldo_var_cs += $saldo_cs;
                        } else {
                            $saldo_cs = $v != '' ? $v : 0;
                        }
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d; width: 100px; border-left: 2px solid">
                        {{ $v != '' ? number_format($v) : '' }}
                    </td>
                    <!--<td class="text-center" style="border-color: #9d9d9d; width: 100px">
                        {{ number_format($solidos) }}
                    </td>-->
                    <th class="text-center {{ $color_saldo }}" style="border-color: #9d9d9d; width: 100px">
                        <input type="text" readonly style="width: 100%" value="{{ round($saldo_cs) }}"
                            class="text-center" disabled
                            id="distribucion_var_{{ $item['var']->siglas }}_corte_{{ $cortes[$pos_c]->id_proy_cortes }}">
                    </th>
                @endforeach
                @php
                    $saldo_neg_total_sd += $solidos;
                    $saldo_var_sd = $saldo_pos_var_sd - $solidos;
                    $color_saldo_var_sd = $saldo_var_sd > 0 ? 'text-color_yura' : 'text-color_yura_danger';
                    $color_saldo_var_cs = $saldo_var_cs > 0 ? 'text-color_yura' : 'text-color_yura_danger';
                    if ($saldo_var_cs >= 0) {
                        $saldo_pos_total_cs += $saldo_var_cs;
                    } else {
                        $saldo_neg_total_cs += -1 * $saldo_var_cs;
                    }
                    $porcentaje = porcentaje($item['dist_semana'], $mixtos_semana, 1);
                    $saldos_solidos = round(porcentaje($porcentaje, $ramos_mixtos, 2));
                    $totales_solidos += $item['dist_diaria'] != '' ? $item['dist_diaria']->cantidad : $saldos_solidos;
                @endphp
                <th class="text-center" style="border-color: #9d9d9d; width: 100px; padding-left: 5px">
                    <strong class="{{ $saldo_var_sd == 0 ? '' : $color_saldo_var_sd }}">
                        {{ number_format($saldo_var_sd) }}
                    </strong>
                </th>
                <th class="text-center" style="border-color: #9d9d9d; width: 100px; border-left: 2px solid;">
                    <input type="number" id="saldo_var_{{ $item['var']->siglas }}"
                        value="{{ $item['dist_diaria'] != '' ? $item['dist_diaria']->cantidad : $saldos_solidos }}"
                        style="width: 100%" class="text-center input_dist"
                        onchange="calcular_totales_distribucion_diaria()"
                        onkeyup="calcular_totales_distribucion_diaria()">
                </th>
                <th class="text-center"
                    style="border-color: #9d9d9d; width: 100px; border-left: 2px solid; padding-left: 5px">
                    <strong class="{{ $saldo_var_cs == 0 ? '' : $color_saldo_var_cs }}">
                        {{ number_format($saldo_var_cs) }}
                    </strong>
                    <input type="hidden" id="consolidado_var_{{ $item['var']->siglas }}"
                        value="{{ $saldo_var_cs }}">
                </th>
                @if ($pos == 0)
                    <th class="text-center" style="border: 2px solid; vertical-align: top"
                        rowspan="{{ count($listado) }}">
                        <table class="table-bordered table-stripped" style="border: 1px solid #9d9d9d; width: 100%;">
                            <tr>
                                <th class="text-center bg-yura_dark">
                                    Cliente
                                </th>
                                <th class="text-center bg-yura_dark">
                                    Pedido
                                </th>
                                <th class="text-center bg-yura_dark">
                                    Distribuido
                                </th>
                                <th class="text-center bg-yura_dark">
                                </th>
                            </tr>
                            @foreach ($listado_clientes as $p)
                                @php
                                    $clase_text_color = '';
                                    $title_tr = 'Sin Distribuir';
                                    if ($p['distribuido'] == $p['mixtos_x_cliente']->tallos) {
                                        $clase_text_color = 'text-color_yura';
                                        $title_tr = 'Distribuido';
                                    }
                                    if ($p['distribuido'] > $p['mixtos_x_cliente']->tallos) {
                                        $clase_text_color = 'text-color_yura_warning';
                                        $title_tr = 'Sobre-distribuido';
                                    }
                                @endphp
                                <tr title="{{ $title_tr }}">
                                    <th class="text-left {{ $clase_text_color }}"
                                        style="padding-left: 5px; border-color: #9d9d9d">
                                        {{ $p['nombre'] }}
                                    </th>
                                    <th class="text-center padding_lateral_5 {{ $clase_text_color }}"
                                        style="border-color: #9d9d9d" title="Pedidos">
                                        {{ number_format($p['mixtos_x_cliente']->tallos) }}
                                        <input type="hidden"
                                            id="tallos_pedidos_mixtos_{{ $p['id_cliente'] }}_{{ $p['longitud_ramo'] }}"
                                            value="{{ $p['mixtos_x_cliente']->tallos }}">
                                    </th>
                                    <th class="text-center padding_lateral_5 {{ $clase_text_color }}"
                                        style="border-color: #9d9d9d" title="Distribuido">
                                        {{ number_format($p['distribuido']) }}
                                    </th>
                                    <th class="text-center {{ $clase_text_color }}" style="border-color: #9d9d9d">
                                        <button type="button" class="btn btn-xs btn-yura_dark pull-right"
                                            title="Distribuir"
                                            onclick="distribuir_mixtos('{{ $p['id_cliente'] }}', '{{ $p['longitud_ramo'] }}')">
                                            <i class="fa fa-fw fa-arrow-right"></i>
                                        </button>
                                    </th>
                                </tr>
                            @endforeach
                        </table>
                    </th>
                @endif
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green columna_fija_left_0">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_pedidos_solidos) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_armados_mixtos) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_pedidos_solidos + $total_armados_mixtos) }}
            </th>
            <th colspan="{{ count($cortes) * 2 + 2 }}" class="text-center bg-yura_dark">
            </th>
            <th class="text-center bg-yura_dark">
            </th>
            <th class="text-center th_yura_green">
                <span id="th_total_solidos">{{ number_format($totales_solidos) }}</span>
                <br>
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default"
                        onclick="store_distribucion_mixtos_diaria()" title="Guardar Distribución Total">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="$('.input_dist').val(0); calcular_totales_distribucion_diaria()" title="Todo en 0">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </th>
            <th class="text-center bg-yura_dark" colspan="2">
            </th>
        </tr>
    </table>
</div>

<script>
    $('#span_saldo_positivo_total_sd').html('{{ number_format($saldo_pos_total_sd) }}');
    $('#span_saldo_negativo_total_sd').html('{{ number_format($saldo_neg_total_sd) }}');
    $('#span_saldo_positivo_total_cs').html('{{ number_format($saldo_pos_total_cs) }}');
    $('#span_saldo_negativo_total_cs').html('{{ number_format($saldo_neg_total_cs) }}');

    function distribuir_mixtos(cliente, longitud) {
        ids_variedades = $('.ids_variedades');
        data = [];
        pedidos_mixtos = parseInt($('#pedidos_mixtos').val());
        tallos_pedidos_mixtos = parseInt($('#tallos_pedidos_mixtos_' + cliente + '_' + longitud).val());
        for (i = 0; i < ids_variedades.length; i++) {
            id_var = ids_variedades[i].value;
            saldo = $('#saldo_var_' + id_var).val();
            consolidado = $('#consolidado_var_' + id_var).val();
            porcentaje = (saldo / pedidos_mixtos) * 100;
            saldo = (porcentaje * tallos_pedidos_mixtos) / 100;
            data.push({
                var: id_var,
                saldo: saldo,
                consolidado: consolidado,
                porcentaje: porcentaje,
            });
        }
        datos = {
            planta: $('#filtro_planta').val(),
            fecha: $('#filtro_fecha').val(),
            pedidos_mixtos: pedidos_mixtos,
            tallos_pedidos_mixtos: tallos_pedidos_mixtos,
            data: JSON.stringify(data),
            cliente: cliente,
            longitud: longitud,
            pagina: $('#select_paginas').val(),
        }
        get_jquery('{{ url('distribucion_mixtos/distribuir_mixtos') }}', datos, function(retorno) {
            modal_view('modal-view_distribuir_mixtos', retorno,
                '<i class="fa fa-fw fa-filter"></i> Distribuir Mixtos', true, false, '95%');
        });
    }

    function calcular_totales_distribucion_diaria() {
        ids_variedades = $('.ids_variedades');
        totales_solidos = 0;
        for (i = 0; i < ids_variedades.length; i++) {
            id_var = ids_variedades[i].value;
            saldo = $('#saldo_var_' + id_var).val();
            if (saldo == '') {
                saldo = 0;
                $('#saldo_var_' + id_var).val(0);
            } else {
                saldo = parseInt(saldo);
            }
            totales_solidos += saldo;
        }
        $('#th_total_solidos').html(totales_solidos);
    }

    function cambiar_uso_corte(corte) {
        datos = {
            _token: '{{ csrf_token() }}',
            corte: corte,
        }
        post_jquery_m('{{ url('ingresos_proy/cambiar_uso_corte') }}', datos, function() {
            usar = $('#usar_corte_' + corte).val();
            if (usar == 1) { // marcar como NO tener en cuenta
                $('#btn_uso_corte_' + corte).removeClass('btn-yura_primary');
                $('#btn_uso_corte_' + corte).addClass('btn-yura_danger');
                $('#btn_uso_corte_' + corte).attr('title', 'No tener en cuenta en las distribuciones');
                $('#btn_uso_corte_' + corte).html('<i class="fa fa-fw fa-times"></i>');
                $('#usar_corte_' + corte).val(0);
            } else { // marcar como SI tener en cuenta
                $('#btn_uso_corte_' + corte).removeClass('btn-yura_danger');
                $('#btn_uso_corte_' + corte).addClass('btn-yura_primary');
                $('#btn_uso_corte_' + corte).attr('title', 'Tener en cuenta en las distribuciones');
                $('#btn_uso_corte_' + corte).html('<i class="fa fa-fw fa-check"></i>');
                $('#usar_corte_' + corte).val(1);
            }
            listar_reporte();
        }, 'th_uso_corte_' + corte);
    }

    function store_distribucion_mixtos_diaria() {
        ids_variedades = $('.ids_variedades');
        data = [];
        for (i = 0; i < ids_variedades.length; i++) {
            id_var = ids_variedades[i].value;
            saldo = $('#saldo_var_' + id_var).val();
            data.push({
                var: id_var,
                saldo: saldo,
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_planta').val(),
            fecha: $('#filtro_fecha').val(),
            longitud: $('#longitud_selected').val(),
            data: data,
        }
        post_jquery_m('{{ url('distribucion_mixtos/store_distribucion_mixtos_diaria') }}', datos, function() {
            listar_reporte();
        });
    }

    function duplicar_distribucion() {
        ids_variedades = $('.ids_variedades');
        id_cortes = $('.id_cortes');
        data = [];
        for (v = 0; v < ids_variedades.length; v++) {
            variedad = ids_variedades[v].value;
            query_valores = [];
            for (c = 0; c < id_cortes.length; c++) {
                corte = id_cortes[c].value;
                valor = parseInt($('#distribucion_var_' + variedad + '_corte_' + corte).val());
                query_valores.push(valor);
            }
            valores = [];
            for (c = 1; c <= query_valores.length; c++) {
                pos_actual = c;
                pos_anterior = c - 1;
                if (c < query_valores.length) {
                    valor_actual = query_valores[pos_actual];
                    valor_anterior = query_valores[pos_anterior];
                    valores.push({
                        valor: valor_anterior + valor_actual,
                        corte: id_cortes[pos_anterior].value
                    });
                    query_valores[pos_actual] = 0;
                } else {
                    valores.push({
                        valor: 0,
                        corte: id_cortes[pos_anterior].value
                    });
                }
            }
            data.push({
                var: variedad,
                valores: valores,
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            data: data
        }
        post_jquery_m('{{ url('distribucion_mixtos/duplicar_distribucion') }}', datos, function() {
            //listar_reporte();
        });
    }
</script>
