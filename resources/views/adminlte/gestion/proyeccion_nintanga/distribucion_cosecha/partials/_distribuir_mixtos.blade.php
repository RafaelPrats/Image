<table style="width: 100%">
    <tr>
        <td>
            <div class="form-check text-left">
                <label class="form-check-label mouse-hand">Paginas</label>
                @php
                    $cantidad_paginas =
                        $cantidad_pedidos % 5 == 0 ? $cantidad_pedidos / 5 : intVal($cantidad_pedidos / 5) + 1;
                @endphp
                <select class="form-check-input" id="select_paginas"
                    onchange="cerrar_modals(); distribuir_mixtos('{{ $cliente }}', '{{ $longitud }}')">
                    @for ($i = 1; $i <= $cantidad_paginas; $i++)
                        <option value="{{ $i }}" {{ $i == $pagina ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                <input type="hidden" id="cantidad_paginas" value="{{ $cantidad_paginas }}">
            </div>
        </td>
        <td>
            <div class="form-check text-right">
                <input class="form-check-input mouse-hand" type="checkbox" id="check_guardar_cambios" checked>
                <label class="form-check-label mouse-hand" for="check_guardar_cambios">Guardar como cambios</label>
            </div>
        </td>
    </tr>
</table>

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 460px;">
    <table class="table-stripped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center padding_lateral_5 th_yura_green columna_fija_left_0" rowspan="4">
                <div style="width: 150px">
                    {{ $planta->nombre }}
                </div>
                @if ($tiene_backups)
                    <button type="button" class="btn btn-block btn-yura_default btn-xs"
                        onclick="restaurar_distribuciones()">
                        <i class="fa fa-fw fa-copy"></i> Restaurar Distribuciones
                    </button>
                @endif
                @if ($cantidad_paginas > 1)
                    <button type="button" class="btn btn-block btn-yura_danger btn-xs"
                        onclick="eliminar_distribuciones()">
                        <i class="fa fa-fw fa-trash"></i> Eliminar Distribuciones
                    </button>
                @endif
            </th>
            <th class="text-center padding_lateral_5 th_yura_green" rowspan="4">
                <div style="width: 100px">
                    SALDO
                </div>
            </th>
            <th class="text-center padding_lateral_5 th_yura_green" rowspan="4">
                <div style="width: 100px">
                    Distribución
                </div>
            </th>
            @php
                $totales = [];
                $total_consolidado = 0;
                $saldo_total = 0;
                $tallos_total = 0;
                $final_total = 0;
                $ramos_mixtos = 0;
            @endphp
            @foreach ($pedidos_mixtos as $pos => $p)
                <th class="text-center bg-yura_dark" colspan="7" style="border-left: 2px solid">
                    <input type="hidden" class="pos" value="{{ $pos }}">
                    <input type="hidden" id="guia_id_pedido_{{ $pos }}" value="{{ $p->id_pedido }}">
                    <input type="hidden" id="guia_id_detalle_pedido_{{ $pos }}"
                        value="{{ $p->id_detalle_pedido }}">
                    <input type="hidden" id="guia_id_detalle_especificacionempaque_{{ $pos }}"
                        value="{{ $p->id_detalle_especificacionempaque }}">
                    <input type="hidden" id="guia_id_cliente_{{ $pos }}" value="{{ $p->id_cliente }}">
                    <input type="hidden" id="guia_nombre_cliente_{{ $pos }}" value="{{ $p->nombre }}">
                    <input type="hidden" id="guia_longitud_ramo_{{ $pos }}" value="{{ $p->longitud_ramo }}">
                    <input type="hidden" id="guia_tallos_x_ramos_{{ $pos }}"
                        value="{{ $p->tallos_x_ramos }}">
                    <input type="hidden" id="guia_id_unidad_medida_{{ $pos }}"
                        value="{{ $p->id_unidad_medida }}">
                    <input type="hidden" id="guia_ramos_x_caja_{{ $pos }}"
                        value="{{ $array_valores_x_caja[$pos] }}">
                    <input type="hidden" id="guia_piezas_{{ $pos }}" value="{{ $p->piezas }}">
                    {{ $p->nombre }}
                    <sup>{{ $p->longitud_ramo . $p->siglas }}</sup>
                </th>
                @php
                    $ramos_mixtos += $p->piezas * $array_valores_x_caja[$pos] * $p->tallos_x_ramos;
                    $totales[] = [
                        'porcentaje' => 0,
                        'tallos' => 0,
                        'ramos' => 0,
                    ];
                @endphp
            @endforeach
            <th class="text-center padding_lateral_5 th_yura_green" rowspan="4">
                <div style="width: 100px">
                    TALLOS: {{ number_format($ramos_mixtos) }}
                </div>
                <input type="hidden" id="guia_ramos_mixtos" value="{{ $ramos_mixtos }}">
            </th>
        </tr>
        <tr>
            @foreach ($pedidos_mixtos as $pos => $p)
                <th class="text-center bg-yura_dark" colspan="7" style="border-left: 2px solid">
                    {{ $p->piezas }} <sup>Caja</sup> x
                    {{ $array_valores_x_caja[$pos] }}
                    <sup>Bunches</sup>
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($pedidos_mixtos as $pos => $p)
                <th class="text-right bg-yura_dark" colspan="7">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-xs btn-yura_default"
                                onclick="$('.marcar_nuevo_{{ $pos }}').val(0)">
                                <i class="fa fa-fw fa-gift"></i> NUEVO
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check text-left">
                                <input class="form-check-input mouse-hand" type="checkbox"
                                    id="check_guardar_cambios_{{ $pos }}" checked>
                                <label class="form-check-label mouse-hand"
                                    for="check_guardar_cambios_{{ $pos }}">
                                    Marcar como cambio
                                </label>
                            </div>
                        </div>
                    </div>
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($pedidos_mixtos as $pos => $p)
                @php
                    $tallos = $p->piezas * $array_valores_x_caja[$pos] * $p->tallos_x_ramos;
                @endphp
                <th class="text-center bg-yura_dark" style="border-left: 2px solid">
                    %
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Tallos: {{ $tallos }}
                    </div>
                    <input type="hidden" id="guia_tallos_{{ $pos }}" value="{{ $tallos }}">
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Anterior
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Bunches
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Total Ramos
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Anterior
                    </div>
                </th>
                <th class="text-center bg-yura_dark">
                    <div style="width: 100px">
                        Total Ramos <sup><em>ANTERIOR</em></sup>
                    </div>
                </th>
            @endforeach
        </tr>
        @foreach ($listado as $pos_v => $item)
            @php
                $item['saldo'] = round(porcentaje($item['porcentaje'], $ramos_mixtos, 2));
                $tallos_var = 0;
                $saldo_total += $item['saldo'];
                $total_consolidado += $item['consolidado'];
            @endphp
            <tr>
                <input type="hidden" class="ids_variedad" value="{{ $item['var']->siglas }}">
                <th class="text-center padding_lateral_5 bg-yura_dark columna_fija_left_0">
                    {{ $item['var']->nombre }}
                </th>
                <th class="text-center {{ $item['consolidado'] > 0 ? 'text-color_yura' : 'text-color_yura_danger' }}"
                    style="border-color: #9d9d9d">
                    <input type="number" readonly style="width: 100%; background-color: #efefef" class="text-center"
                        value="{{ $item['consolidado'] }}" id="consolidado_variedad_{{ $item['var']->siglas }}">
                </th>
                <th class="text-center {{ $item['saldo'] > 0 ? 'text-color_yura' : 'text-color_yura_danger' }}"
                    style="border-color: #9d9d9d">
                    <input type="number" readonly style="width: 100%; background-color: #efefef" class="text-center"
                        value="{{ $item['saldo'] }}" id="saldo_variedad_{{ $item['var']->siglas }}">
                </th>
                @foreach ($item['valores'] as $pos => $val)
                    @php
                        if ($val['model'] != '') {
                            $porcentaje = $val['model']->porcentaje;
                            $tallos = $val['model']->tallos;
                            $ramos = $val['model']->ramos;
                        } else {
                            $porcentaje = porcentaje($item['saldo'], $ramos_mixtos, 1);
                            $tallos = round(porcentaje($porcentaje, $pedidos_mixtos[$pos]->cantidad, 2));
                            $ramos = porcentaje($porcentaje, $pedidos_mixtos[$pos]->ramos_x_caja, 2);
                        }
                        $tallos_var += $tallos;
                        $totales[$pos]['porcentaje'] += $porcentaje;
                        $totales[$pos]['tallos'] += $tallos;
                        $totales[$pos]['ramos'] += round($ramos);

                        if ($item['backups'][$pos]['model'] != '') {
                            $back_porcentaje = $item['backups'][$pos]['model']->porcentaje;
                            $back_tallos = $item['backups'][$pos]['model']->tallos;
                            $back_ramos = $item['backups'][$pos]['model']->ramos;
                            $back_piezas = $item['backups'][$pos]['model']->piezas;
                            $back_tallos_x_ramos = $item['backups'][$pos]['model']->tallos_x_ramos;
                        } else {
                            $back_porcentaje = null;
                            $back_tallos = null;
                            $back_ramos = null;
                            $back_piezas = null;
                            $back_tallos_x_ramos = null;
                        }
                    @endphp
                    <th class="text-center padding_lateral_5" style="border-color: #9d9d9d; border-left: 2px solid"
                        id="porcentaje_variedad_{{ $item['var']->siglas }}_{{ $pos }}">
                        {{ $porcentaje }}%
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" value="{{ $tallos }}"
                            readonly id="tallos_variedad_{{ $item['var']->siglas }}_{{ $pos }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" title="Anterior Distribucion">
                        <input type="number" disabled style="width: 100%"
                            value="{{ $back_tallos != null ? $back_tallos : $tallos }}"
                            id="back_tallos_variedad_{{ $item['var']->siglas }}_{{ $pos }}"
                            class="text-center marcar_nuevo_{{ $pos }}">
                    </td>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" style="width: 100%" value="{{ round($ramos) }}"
                            min="0" id="ramos_variedad_{{ $item['var']->siglas }}_{{ $pos }}"
                            onchange="calcular_distribuciones()" onkeyup="calcular_distribuciones()">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" title="Ramos totales">
                        <input type="number" disabled class="text-center" style="width: 100%"
                            value="{{ round($ramos * $pedidos_mixtos[$pos]->piezas) }}" min="0"
                            id="total_ramos_variedad_{{ $item['var']->siglas }}_{{ $pos }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" title="Anterior Distribucion">
                        <input type="number" disabled style="width: 100%"
                            value="{{ $item['backups'][$pos]['model'] != null ? $back_ramos : round($ramos) }}"
                            id="back_ramos_variedad_{{ $item['var']->siglas }}_{{ $pos }}"
                            class="text-center marcar_nuevo_{{ $pos }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" title="Ramos totales">
                        <input type="number" disabled style="width: 100%"
                            value="{{ $item['backups'][$pos]['model'] != null ? $back_ramos * $back_piezas : 0 }}"
                            id="total_back_ramos_variedad_{{ $item['var']->siglas }}_{{ $pos }}"
                            class="text-center marcar_nuevo_{{ $pos }}">
                    </th>
                @endforeach
                @php
                    $saldo_final_var = $item['saldo'] - $tallos_var;
                    $tallos_total += $tallos_var;
                    $final_total += $saldo_final_var;
                @endphp
                <th class="text-center {{ $tallos_var > 0 ? 'text-color_yura' : 'text-color_yura_danger' }}"
                    style="border-color: #9d9d9d">
                    <input type="number" readonly style="width: 100%; background-color: #efefef"
                        value="{{ $tallos_var }}" class="text-center"
                        id="tallos_variedad_{{ $item['var']->siglas }}">
                </th>
            </tr>
        @endforeach
        <!-- TOTALES -->
        <tr>
            <th class="text-center padding_lateral_5 th_yura_green columna_fija_left_0">
                TOTALES
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" readonly style="width: 100%" class="text-center th_yura_green"
                    value="{{ $total_consolidado }}" id="total_consolidado">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" readonly style="width: 100%" class="text-center th_yura_green"
                    value="{{ $saldo_total }}" id="saldo_total">
            </th>
            @foreach ($totales as $pos => $val)
                <th class="text-center padding_lateral_5 bg-yura_dark" id="porcentaje_total_{{ $pos }}"
                    style="border-color: #9d9d9d; border-left: 2px solid">
                    {{ round($val['porcentaje']) }}%
                </th>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center bg-yura_dark" style="width: 100%"
                        value="{{ round($val['tallos']) }}" readonly id="tallos_total_{{ $pos }}">
                </td>
                <td class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center bg-yura_dark" style="width: 100%"
                        value="{{ round($val['ramos']) }}" readonly id="ramos_total_{{ $pos }}">
                </th>
                <td class="text-center bg-yura_dark" style="border-color: #9d9d9d" colspan="3">
                </td>
            @endforeach
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" readonly style="width: 100%" value="{{ $tallos_total }}"
                    class="text-center th_yura_green" id="tallos_total">
            </th>
        </tr>
    </table>
</div>

<div class="text-center" style="margin-top: 10px">
    <button type="button" class="btn btn-yura_primary" onclick="store_distribucion()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<input type="hidden" id="cliente_seleccionado" value="{{ $cliente }}">

<script>
    calcular_distribuciones();

    function calcular_distribuciones() {
        pos = $('.pos');
        totales = [];
        for (x = 0; x < pos.length; x++)
            totales.push({
                porcentaje: 0,
                tallos: 0,
                ramos: 0,
            });

        ids_variedad = $('.ids_variedad');
        for (i = 0; i < ids_variedad.length; i++) {
            id_var = ids_variedad[i].value;
            saldo = parseFloat($('#saldo_variedad_' + id_var).val());
            tallos_var = 0;
            for (x = 0; x < pos.length; x++) {
                p = pos[x].value;
                guia_ramos_x_caja = parseInt($('#guia_ramos_x_caja_' + p).val());
                guia_piezas = parseInt($('#guia_piezas_' + p).val());
                guia_tallos = $('#guia_tallos_' + p).val();
                ramos = parseFloat($('#ramos_variedad_' + id_var + '_' + p).val());
                ramos = ramos != '' ? ramos : 0;
                total_ramos_variedad = ramos * guia_piezas;
                $('#total_ramos_variedad_' + id_var + '_' + p).val(total_ramos_variedad);
                total_back_ramos_variedad = parseInt($('#total_back_ramos_variedad_' + id_var + '_' + p).val());
                if (total_ramos_variedad != total_back_ramos_variedad) {
                    $('#total_ramos_variedad_' + id_var + '_' + p).css('background-color', '#ffd661');
                } else {
                    $('#total_ramos_variedad_' + id_var + '_' + p).css('background-color', '#f8f8f8');
                }
                porcentaje = (ramos / guia_ramos_x_caja) * 100;
                porcentaje = Math.round(porcentaje * 100) / 100;
                $('#porcentaje_variedad_' + id_var + '_' + p).html(porcentaje + '%');
                tallos = (porcentaje * guia_tallos) / 100;
                tallos = Math.round(tallos);
                $('#tallos_variedad_' + id_var + '_' + p).val(tallos);
                tallos_var += tallos;
                totales[x]['porcentaje'] += porcentaje;
                totales[x]['tallos'] += tallos;
                totales[x]['ramos'] += ramos;
            }
            saldo_final_var = saldo - tallos_var;
            $('#tallos_variedad_' + id_var).val(tallos_var);
        }

        tallos_total = 0;
        for (x = 0; x < pos.length; x++) {
            p = pos[x].value;
            $('#porcentaje_total_' + p).html(Math.round(totales[x]['porcentaje']) + '%');
            $('#tallos_total_' + p).val(Math.round(totales[x]['tallos']));
            $('#ramos_total_' + p).val(Math.round(totales[x]['ramos']));
            tallos_total += Math.round(totales[x]['tallos']);
        }
        $('#tallos_total').val(tallos_total);
        saldo_total = parseInt($('#saldo_total').val());
    }

    function store_distribucion() {
        guia_ramos_mixtos = parseInt($('#guia_ramos_mixtos').val());
        tallos_total = parseInt($('#tallos_total').val());
        if (guia_ramos_mixtos == tallos_total) {
            pos = $('.pos');
            ids_variedad = $('.ids_variedad');
            data = [];
            for (i = 0; i < ids_variedad.length; i++) {
                id_var = ids_variedad[i].value;
                for (x = 0; x < pos.length; x++) {
                    p = pos[x].value;
                    guia_ramos_x_caja = parseInt($('#guia_ramos_x_caja_' + p).val());
                    guia_tallos = parseInt($('#guia_tallos_' + p).val());
                    ramos = parseFloat($('#ramos_variedad_' + id_var + '_' + p).val());
                    total_ramos = parseFloat($('#total_ramos_variedad_' + id_var + '_' + p).val());
                    total_back_ramos = parseFloat($('#total_back_ramos_variedad_' + id_var + '_' + p).val());
                    porcentaje = (ramos / guia_ramos_x_caja) * 100;
                    porcentaje = Math.round(porcentaje * 100) / 100;
                    tallos = (porcentaje * guia_tallos) / 100;
                    tallos = Math.round(tallos);
                    back_tallos = parseFloat($('#back_tallos_variedad_' + id_var + '_' + p).val());
                    guia_id_detalle_especificacionempaque = parseInt($('#guia_id_detalle_especificacionempaque_' + p)
                        .val());
                    guia_id_pedido = parseInt($('#guia_id_pedido_' + p).val());
                    guia_id_detalle_pedido = parseInt($('#guia_id_detalle_pedido_' + p).val());
                    guia_id_cliente = parseInt($('#guia_id_cliente_' + p).val());
                    guia_nombre_cliente = $('#guia_nombre_cliente_' + p).val();
                    guia_longitud_ramo = parseInt($('#guia_longitud_ramo_' + p).val());
                    guia_id_unidad_medida = parseInt($('#guia_id_unidad_medida_' + p).val());
                    guia_ramos_x_caja = parseInt($('#guia_ramos_x_caja_' + p).val());
                    guia_tallos_x_ramos = parseInt($('#guia_tallos_x_ramos_' + p).val());
                    guia_piezas = parseInt($('#guia_piezas_' + p).val());
                    data.push({
                        var: id_var,
                        ramos: ramos,
                        total_ramos: total_ramos,
                        total_back_ramos: total_back_ramos,
                        porcentaje: porcentaje,
                        tallos: tallos,
                        back_tallos: back_tallos,
                        guia_id_detalle_especificacionempaque: guia_id_detalle_especificacionempaque,
                        guia_id_pedido: guia_id_pedido,
                        guia_id_detalle_pedido: guia_id_detalle_pedido,
                        guia_id_cliente: guia_id_cliente,
                        guia_nombre_cliente: guia_nombre_cliente,
                        guia_longitud_ramo: guia_longitud_ramo,
                        guia_id_unidad_medida: guia_id_unidad_medida,
                        guia_ramos_x_caja: guia_ramos_x_caja,
                        guia_tallos_x_ramos: guia_tallos_x_ramos,
                        guia_piezas: guia_piezas,
                        check_guardar_cambios: $('#check_guardar_cambios_' + p).prop('checked'),
                    });
                }
            }
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#filtro_fecha').val(),
                planta: $('#filtro_planta').val(),
                cliente: $('#cliente_seleccionado').val(),
                cantidad_paginas: $('#cantidad_paginas').val(),
                check_guardar_cambios: $('#check_guardar_cambios').prop('checked'),
                longitud: '{{ $longitud }}',
                data: JSON.stringify(data),
            };
            post_jquery_m('{{ url('distribucion_cosecha/store_distribucion') }}', datos, function(retorno) {
                cerrar_modals();
                @if ($cantidad_paginas > 1)
                    distribuir_mixtos(datos['cliente'], datos['longitud']);
                @endif
                listar_reporte();
            });
        } else
            alerta(
                '<div class="alert alert-danger text-center">Los tallos distribuidos <strong>NO COINCIDEN</strong> con los tallos pedidos</div>'
            )
    }

    function restaurar_distribuciones() {
        pos = $('.pos');
        ids_variedad = $('.ids_variedad');
        data = [];
        for (i = 0; i < ids_variedad.length; i++) {
            id_var = ids_variedad[i].value;
            for (x = 0; x < pos.length; x++) {
                p = pos[x].value;
                back_ramos = parseFloat($('#back_ramos_variedad_' + id_var + '_' + p).val());
                $('#ramos_variedad_' + id_var + '_' + p).val(back_ramos);
            }
        }
        calcular_distribuciones();
    }

    function eliminar_distribuciones() {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-danger text-center" style="font-size: 16px">¿Está seguro de <b>ELIMINAR</b> las distribuciones del cliente?</div>',
        };
        modal_quest('modal_update_factor_conversion', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    fecha: $('#filtro_fecha').val(),
                    planta: $('#filtro_planta').val(),
                    cliente: $('#cliente_seleccionado').val(),
                    check_guardar_cambios: $('#check_guardar_cambios').prop('checked'),
                    longitud: '{{ $longitud }}',
                };
                post_jquery_m('{{ url('distribucion_cosecha/eliminar_distribuciones') }}', datos, function(
                    retorno) {
                    cerrar_modals();
                    distribuir_mixtos(datos['cliente'], datos['longitud']);
                    listar_reporte();
                });
            });
    }
</script>
