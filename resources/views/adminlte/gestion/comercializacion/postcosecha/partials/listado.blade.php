<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 1em" id="table_listado">
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
                $confirmar_pedidos = 1;
            @endphp
            @foreach ($fechas as $pos_f => $fecha)
                <th class="text-center bg-yura_dark" colspan="3" id="th_fecha_{{ $pos_f }}"
                    data-fecha="{{ $fecha }}">
                    {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha)))] }}<br>
                    <small>{{ $fecha }}</small>
                </th>
                @php
                    $totales_fecha[] = [
                        'anteriores' => 0,
                        'actuales' => 0,
                        'cambios' => 0,
                    ];
                @endphp
            @endforeach
            <th class="text-center th_yura_green" style="width: 60px">
                Cuarto Frio
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Armar
            </th>
            <th class="text-center th_yura_green" style="width: 90px">
                <button type="button" class="btn btn-yura_default dropdown-toggle" data-toggle="dropdown"
                    aria-expanded="true">
                    Mostrar...
                </button>
                <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" style="background-color: #c8c8c8">
                    <li>
                        <a class="" href="javascript:void(0)" style="color: black"
                            onclick="mostrar_solo('faltante')">
                            Mostrar solo faltantes
                        </a>
                        <a class="" href="javascript:void(0)" style="color: black"
                            onclick="mostrar_solo('armado')">
                            Mostrar solo armados
                        </a>
                        <a class="" href="javascript:void(0)" style="color: black" onclick="mostrar_solo('todo')">
                            Mostrar todo
                        </a>
                    </li>
                </ul>
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            @php
                $color_fondo = 'white';
                $color_texto = 'black';
                foreach ($colores as $c) {
                    if ($c->nombre == $item['item']->var_nombre) {
                        $color_fondo = $c->fondo;
                        $color_texto = $c->texto;
                    }
                }
            @endphp
            <tr onmouseover="$(this).css('background-color', '#ADD8E6')"
                onmouseleave="$(this).css('background-color', '')" id="tr_item_{{ $pos }}"
                data-id_variedad="{{ $item['item']->id_variedad }}" data-id_empaque="{{ $item['item']->id_empaque }}"
                data-tallos_x_ramo="{{ $item['item']->tallos_x_ramo }}"
                data-longitud_ramo="{{ $item['item']->longitud_ramo }}">
                <th class="padding_lateral_5"
                    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
                    {{ $planta->nombre }}
                </th>
                <th class="padding_lateral_5"
                    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
                    {{ $item['item']->var_nombre }}
                </th>
                <th class="padding_lateral_5"
                    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
                    {{ $item['item']->pres_nombre }}
                </th>
                <th class="padding_lateral_5"
                    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
                    {{ $item['item']->tallos_x_ramo }}
                </th>
                <th class="padding_lateral_5"
                    style="border-color: {{ $color_texto }}; color: {{ $color_texto }}; background-color: {{ $color_fondo }}">
                    {{ $item['item']->longitud_ramo }}cm
                </th>
                @php
                    $acumulado_pedido = 0;
                    $total_inventario = $item['ramos_inventario'];
                    $clase_mostrar = '';
                @endphp
                @foreach ($fechas as $pos_f => $fecha)
                    @php
                        $ramos_actuales = 0;
                        $ramos_distribuidos = 0;
                        $ramos_cambios = 0;
                        foreach ($item['ramos_solidos'] as $r) {
                            if ($r->fecha == $fecha) {
                                $ramos_actuales += $r->cantidad;
                            }
                        }
                        foreach ($item['ramos_mixtos'] as $r) {
                            if ($r->fecha == $fecha) {
                                $ramos_actuales += $r->cantidad;
                            }
                        }
                        foreach ($item['ramos_distribuidos'] as $r) {
                            if ($r->fecha == $fecha) {
                                $ramos_distribuidos += $r->cantidad;
                            }
                        }
                        $cambios_en_fecha = false;
                        foreach ($item['ramos_cambios'] as $r) {
                            if ($r->fecha == $fecha) {
                                $ramos_cambios += $r->ramos;
                                $cambios_en_fecha = true;
                            }
                        }
                        $ramos_anteriores = $ramos_actuales - $ramos_cambios;
                        $acumulado_pedido += $ramos_actuales;
                        $saldo = $total_inventario - $acumulado_pedido;
                        $totales_fecha[$pos_f]['anteriores'] += $ramos_anteriores;
                        $totales_fecha[$pos_f]['actuales'] += $ramos_actuales;
                        $totales_fecha[$pos_f]['cambios'] += $ramos_cambios;
                        if ($saldo < 0 && $pos_f == 0) {
                            $confirmar_pedidos = 0;
                        }
                        if ($saldo >= 0) {
                            $clase_mostrar = 'armado';
                        } else {
                            $clase_mostrar = 'faltante';
                        }
                    @endphp
                    <th class="text-center" style="border-color: #9d9d9d; width: 120px">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark" title="Pedidos"
                                id="btn_pedidos_{{ $pos }}_{{ $pos_f }}"
                                data-valor="{{ $ramos_anteriores }}">
                                {{ $ramos_anteriores }}
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Pedidos Actuales"
                                onclick="distribuir_trabajo('{{ $pos }}', '{{ $pos_f }}')"
                                id="btn_actuales_{{ $pos }}_{{ $pos_f }}"
                                data-valor="{{ $ramos_actuales }}">
                                {{ $ramos_actuales }}
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_{{ $saldo < 0 ? 'danger' : 'primary' }}"
                                title="{{ $saldo < 0 ? 'Por Armar' : 'Armados' }}"
                                id="btn_saldo_{{ $pos }}_{{ $pos_f }}"
                                data-valor="{{ $saldo }}">
                                {{ abs($saldo) }}
                            </button>
                        </div>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if ($cambios_en_fecha)
                            @if ($ramos_cambios != 0)
                                <button type="button"
                                    class="btn btn-xs btn-yura_{{ $ramos_cambios >= 0 ? 'primary' : 'danger' }}"
                                    title="Ver cambios"
                                    onclick="ver_cambios('{{ $pos }}', '{{ $pos_f }}')">
                                    {{ $ramos_cambios > 0 ? '+' : '' }}{{ $ramos_cambios }}
                                </button>
                            @else
                                <button type="button" class="btn btn-xs btn-yura_default" title="Ver cambios"
                                    onclick="ver_cambios('{{ $pos }}', '{{ $pos_f }}')">
                                    <i class="fa fa-fw fa-exchange"></i>
                                </button>
                            @endif
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if ($ramos_distribuidos > 0)
                            <button type="button" class="btn btn-xs btn-yura_default"
                                style="background-color: #00f3ff !important; color: black !important; border-color: #00b5be !important">
                                {{ $ramos_distribuidos }}
                            </button>
                        @endif
                    </th>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['ramos_inventario'] > 0)
                        <button type="button" class="btn btn-xs btn-yura_dark"
                            onclick="modal_inventario('{{ $pos }}')">
                            {{ $item['ramos_inventario'] }}
                        </button>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" id="armar_{{ $pos }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="modal_armar_row('{{ $pos }}')" title="Grabar armados">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="actualizar_row('{{ $pos }}')" title="Actualizar presentacion">
                            <i class="fa fa-fw fa-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark"
                            onclick="ver_inventario('{{ $pos }}')" title="Ver Inventario">
                            <i class="fa fa-fw fa-gift"></i>
                        </button>
                    </div>
                </th>
            </tr>
            <script>
                $('#tr_item_{{ $pos }}').addClass('{{ $clase_mostrar }}');
            </script>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="5">
                TOTALES
            </th>
            @foreach ($totales_fecha as $pos_f => $val)
                <th class="text-center" style="background-color: #eeeeee; border-color: #9d9d9d" colspan="3">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Anteriores">
                            {{ $val['anteriores'] }}
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_warning" title="Actuales">
                            {{ $val['actuales'] }}
                        </button>
                        @if ($val['cambios'] > 0)
                            <button type="button" title="Cambios"
                                class="btn btn-xs btn-yura_{{ $val['cambios'] > 0 ? 'primary' : 'danger' }}">
                                {{ $val['cambios'] > 0 ? '+' : '-' }}{{ $val['cambios'] }}
                            </button>
                        @endif
                    </div>
                    @if ($pos_f == 0)
                        <button type="button"
                            class="btn btn-xs btn-block btn-yura_primary 
                            {{ $confirmar_pedidos == 1 && $filtro_variedad == '' && $filtro_presentacion == '' ? '' : 'hidden' }}"
                            id="btn_confirmar_pedidos" onclick="confirmar_pedidos('{{ $pos_f }}')">
                            Confirmar Pedidos
                        </button>
                    @endif
                </th>
            @endforeach
            <th class="padding_lateral_5 th_yura_green" colspan="3">
            </th>
        </tr>
    </table>
</div>
<input type="hidden" id="planta_selected" value="{{ $planta->id_planta }}">
<script>
    function distribuir_trabajo(pos, pos_f) {
        datos = {
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            empaque: $('#tr_item_' + pos).data('id_empaque'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud: $('#tr_item_' + pos).data('longitud_ramo'),
            pedidos: $('#btn_pedidos_' + pos + '_' + pos_f).data('valor'),
            actuales: $('#btn_actuales_' + pos + '_' + pos_f).data('valor'),
            saldo: $('#btn_saldo_' + pos + '_' + pos_f).data('valor'),
            fecha: $('#th_fecha_' + pos_f).data('fecha'),
            pos: pos,
            pos_f: pos_f,
        };
        get_jquery('{{ url('postcosecha/distribuir_trabajo') }}', datos, function(retorno) {
            modal_view('moda-view_distribuir_trabajo', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Distribuir Trabajo', true, false,
                '{{ isPC() ? '75%' : '' }}');
        });
    }

    function actualizar_row(pos) {
        datos = {
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            empaque: $('#tr_item_' + pos).data('id_empaque'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud: $('#tr_item_' + pos).data('longitud_ramo'),
            dias: $('#filtro_dias').val(),
            pos: pos,
        };
        get_jquery('{{ url('postcosecha/actualizar_row') }}', datos, function(retorno) {
            $('#tr_item_' + pos).html(retorno);
        }, 'tr_item_' + pos)
    }

    function modal_armar_row(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            dias: $('#filtro_dias').val(),
            armar: $('#armar_' + pos).val() != '' ? parseFloat($('#armar_' + pos).val()) : 0,
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud_ramo: $('#tr_item_' + pos).data('longitud_ramo'),
            id_empaque_p: $('#tr_item_' + pos).data('id_empaque'),
            planta: $('#filtro_planta').val(),
            pos_comb: pos
        };
        if (datos['armar'] > 0)
            get_jquery('{{ url('postcosecha/modal_armar_row') }}', datos, function(retorno) {
                modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> MARCACIONES', true,
                    false, '{{ isPC() ? '75%' : '' }}');
            });
    }

    function store_armar_row(pos, id_marcacion, valor_marcacion) {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#filtro_fecha').val(),
            armar: $('#armar_' + pos).val() != '' ? parseFloat($('#armar_' + pos).val()) : 0,
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud_ramo: $('#tr_item_' + pos).data('longitud_ramo'),
            id_empaque_p: $('#tr_item_' + pos).data('id_empaque'),
            id_marcacion: id_marcacion,
            valor_marcacion: valor_marcacion,
        };
        post_jquery_m('{{ url('postcosecha/store_armar_row') }}', datos, function() {
            cerrar_modals();
            actualizar_row(pos);
            $('#armar_' + pos).val('');
        }, 'tr_item_' + pos);
    }

    function modal_inventario(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            dias: $('#filtro_dias').val(),
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud_ramo: $('#tr_item_' + pos).data('longitud_ramo'),
            id_empaque: $('#tr_item_' + pos).data('id_empaque'),
            planta: $('#filtro_planta').val(),
        };
        get_jquery('{{ url('postcosecha/modal_inventario') }}', datos, function(retorno) {
            modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> INVENTARIO', true,
                false, '{{ isPC() ? '70%' : '' }}');
        });
    }

    function confirmar_pedidos(pos_f) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-warning text-center" style="font-size: 16px">¿Está seguro de <b>CONFIRMAR</b> los pedidos del dia?</div>',
        };
        modal_quest('modal_confirmar_pedidos', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                planta = $('#planta_selected').val();
                fecha = $('#th_fecha_' + pos_f).data('fecha');
                datos = {
                    _token: '{{ csrf_token() }}',
                    planta: planta,
                    fecha: fecha,
                }
                post_jquery_m('{{ url('postcosecha/confirmar_pedidos') }}', datos, function() {
                    listar_reporte();
                });
            });
    }

    function ver_cambios(pos, pos_f) {
        datos = {
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            empaque: $('#tr_item_' + pos).data('id_empaque'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud: $('#tr_item_' + pos).data('longitud_ramo'),
            fecha: $('#th_fecha_' + pos_f).data('fecha'),
            pos: pos,
            pos_f: pos_f,
        };
        get_jquery('{{ url('postcosecha/ver_cambios') }}', datos, function(retorno) {
            modal_view('moda-view_ver_cambios', retorno,
                '<i class="fa fa-fw fa-balance-scale"></i> Cambios en pedidos', true, false,
                '{{ isPC() ? '90%' : '' }}');
        });
    }

    function mostrar_solo(x) {
        if (x == 'todo') {
            $('#table_listado tbody tr').removeClass('hidden');
        }
        if (x == 'faltante') {
            $('.armado').addClass('hidden');
            $('.faltante').removeClass('hidden');
        }
        if (x == 'armado') {
            $('.faltante').addClass('hidden');
            $('.armado').removeClass('hidden');
        }
    }

    function ver_inventario(pos) {
        datos = {
            _token: '{{ csrf_token() }}',
            dias: $('#filtro_dias').val(),
            variedad: $('#tr_item_' + pos).data('id_variedad'),
            tallos_x_ramo: $('#tr_item_' + pos).data('tallos_x_ramo'),
            longitud_ramo: $('#tr_item_' + pos).data('longitud_ramo'),
            id_empaque_p: $('#tr_item_' + pos).data('id_empaque'),
            planta: $('#filtro_planta').val(),
            pos_comb: pos
        };
        get_jquery('{{ url('postcosecha/ver_inventario') }}', datos, function(retorno) {
            modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> MARCACIONES', true,
                false, '{{ isPC() ? '75%' : '' }}');
        });
    }
</script>
