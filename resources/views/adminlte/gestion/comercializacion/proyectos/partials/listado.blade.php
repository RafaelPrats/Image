<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.8em">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <input type="checkbox" id="check_all_proyectos"
                    onclick="$('.check_proyectos').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                PACKING
            </th>
            <th class="text-center th_yura_green">
                CLIENTE
            </th>
            <th class="text-center th_yura_green">
                TIPO
            </th>
            <th class="text-center th_yura_green">
                FECHA
            </th>
            <th class="text-center th_yura_green">
                MARCACIONES
            </th>
            <th class="text-center th_yura_green">
                FLOR
            </th>
            <th class="text-center th_yura_green">
                EMPAQUE
            </th>
            <th class="text-center th_yura_green">
                PRESENTACION
            </th>
            <th class="text-center th_yura_green">
                CAJAS
            </th>
            <th class="text-center th_yura_green">
                BUNCHES
            </th>
            <th class="text-center th_yura_green">
                STEMS
            </th>
            <th class="text-center th_yura_green">
                TOTAL STEMS
            </th>
            <th class="text-center th_yura_green">
                PRECIO
            </th>
            <th class="text-center th_yura_green">
                CUARTO FRIO
            </th>
            <th class="text-center th_yura_green">
                OPCIONES
            </th>
        </tr>
        @php
            $resumen_variedad_longitud = [];
            $resumen_presentacion = [];
            $resumen_piezas = [];
        @endphp
        @foreach ($listado as $pos_p => $proy)
            @php
                $total_detalles = 0;
                foreach ($proy['valores_cajas'] as $caja) {
                    foreach ($caja['detalles'] as $detalle) {
                        $total_detalles++;
                    }
                }
                $venta_proy = 0;
                $cajas_proy = 0;
                $ramos_proy = 0;
                $tallos_proy = 0;
            @endphp
            @foreach ($proy['valores_cajas'] as $pos_c => $caja)
                @php
                    $cajas_proy += $caja['caja']->cantidad;

                    //RESUMEN PIEZAS
                    switch (explode('|', $caja['caja']->nombre)[1]) {
                        case 0.125:
                            $nombre = 'CAJAS OCTAVO';
                            break;
                        case 0.25:
                            $nombre = 'CAJAS CUARTO';
                            break;
                        case 0.5:
                            $nombre = 'CAJAS TABACO';
                            break;
                        case 1:
                            $nombre = 'CAJAS FULL';
                            break;
                        default:
                            $nombre = 'CAJAS FULL';
                            break;
                    }
                    $pos_en_resumen = -1;
                    foreach ($resumen_piezas as $pos => $r) {
                        if ($r['nombre'] == $nombre) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_piezas[$pos_en_resumen]['cantidad'] += $caja['caja']->cantidad;
                    } else {
                        $resumen_piezas[] = [
                            'nombre' => $nombre,
                            'factor' => explode('|', $caja['caja']->nombre)[1],
                            'cantidad' => $caja['caja']->cantidad,
                        ];
                    }
                @endphp
                @foreach ($caja['detalles'] as $pos_d => $detalle)
                    @php
                        $tallos = $caja['caja']->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo;
                        $ramos = $caja['caja']->cantidad * $detalle->ramos_x_caja;
                        $venta = $caja['caja']->cantidad * $detalle->ramos_x_caja * $detalle->precio;
                        $venta_proy += $venta;
                        $ramos_proy += $ramos;
                        $tallos_proy += $tallos;

                        //RESUMEN VARIEDAD/LONGITUD
                        $pos_en_resumen = -1;
                        foreach ($resumen_variedad_longitud as $pos => $r) {
                            if (
                                $r['id_variedad'] == $detalle->id_variedad &&
                                $r['longitud'] == $detalle->longitud_ramo &&
                                $r['peso'] == $detalle->peso_ramo
                            ) {
                                $pos_en_resumen = $pos;
                            }
                        }
                        if ($pos_en_resumen != -1) {
                            $resumen_variedad_longitud[$pos_en_resumen]['tallos'] += $tallos;
                            $resumen_variedad_longitud[$pos_en_resumen]['ramos'] += $ramos;
                            $resumen_variedad_longitud[$pos_en_resumen]['venta'] += $venta;
                        } else {
                            $resumen_variedad_longitud[] = [
                                'id_variedad' => $detalle->id_variedad,
                                'longitud' => $detalle->longitud_ramo,
                                'peso' => $detalle->peso_ramo,
                                'nombre_planta' => $detalle->nombre_planta,
                                'nombre_variedad' => $detalle->nombre_variedad,
                                'tallos' => $tallos,
                                'ramos' => $ramos,
                                'venta' => $venta,
                            ];
                        }

                        //RESUMEN PRESENTACION
                        $pos_en_resumen = -1;
                        foreach ($resumen_presentacion as $pos => $r) {
                            if ($r['id'] == $detalle->id_empaque) {
                                $pos_en_resumen = $pos;
                            }
                        }
                        if ($pos_en_resumen != -1) {
                            $resumen_presentacion[$pos_en_resumen]['tallos'] += $tallos;
                            $resumen_presentacion[$pos_en_resumen]['ramos'] += $ramos;
                            $resumen_presentacion[$pos_en_resumen]['venta'] += $venta;
                        } else {
                            $resumen_presentacion[] = [
                                'id' => $detalle->id_empaque,
                                'nombre' => $detalle->nombre_presentacion,
                                'tallos' => $tallos,
                                'ramos' => $ramos,
                                'venta' => $venta,
                            ];
                        }
                    @endphp
                    <tr onmouseover="$('.tr_proy_{{ $proy['proyecto']->id_proyecto }}').css('background-color', 'cyan')"
                        onmouseleave="$('.tr_proy_{{ $proy['proyecto']->id_proyecto }}').css('background-color', '')"
                        class="tr_proy_{{ $proy['proyecto']->id_proyecto }}">
                        @if ($pos_c == 0 && $pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                <input type="checkbox" class="check_proyectos"
                                    data-id_proyecto="{{ $proy['proyecto']->id_proyecto }}"
                                    id="check_proy_{{ $proy['proyecto']->id_proyecto }}">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                {{ $proy['proyecto']->packing }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                {{ $proy['proyecto']->nombre_cliente }}
                                @if ($proy['proyecto']->id_consignatario != '')
                                    <br>
                                    <span style="font-size: 0.9em">{{ $proy['proyecto']->nombre_consignatario }}</span>
                                @endif
                                <br>
                                <span style="font-size: 0.9em" id="span_venta_{{ $proy['proyecto']->id_proyecto }}">
                                </span>
                                <br>
                                <span style="font-size: 0.9em" id="span_cajas_{{ $proy['proyecto']->id_proyecto }}">
                                    Cajas: {{ $proy['total_cajas'] }}
                                </span>
                                <br>
                                <span style="font-size: 0.9em" id="span_ramos_{{ $proy['proyecto']->id_proyecto }}">
                                </span>
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                {{ $proy['proyecto']->tipo }}
                                @if ($proy['proyecto']->orden_fija != '')
                                    <br>
                                    <span style="font-size: 0.9em">#{{ $proy['proyecto']->orden_fija }}</span>
                                @endif
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                {{ getDias(TP_COMPLETO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime(substr($proy['proyecto']->fecha, 0, 10))))] }}
                                <br>
                                {{ convertDateToText(substr($proy['proyecto']->fecha, 0, 10)) }}
                            </th>
                        @endif
                        @if ($pos_d == 0)
                            <th class="padding_lateral_5" style="border-color: #9d9d9d; font-size: 0.9em;"
                                rowspan="{{ count($caja['detalles']) }}">
                                @foreach ($caja['marcaciones'] as $marc)
                                    {{ $marc->valor }} <br>
                                @endforeach
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->nombre_planta }} -
                            <br>
                            {{ $detalle->nombre_variedad }} {{ $detalle->longitud_ramo }}cm {{ $detalle->peso_ramo }}gr
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d; font-size: 0.9em;"
                                rowspan="{{ count($caja['detalles']) }}">
                                {{ explode('|', $caja['caja']->nombre)[0] }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->nombre_presentacion }}
                        </th>
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d; font-size: 0.9em;"
                                rowspan="{{ count($caja['detalles']) }}">
                                {{ $caja['caja']->cantidad }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->ramos_x_caja }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->tallos_x_ramo }}
                        </th>
                        @if ($pos_c == 0 && $pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}"
                                id="th_tallos_{{ $proy['proyecto']->id_proyecto }}">
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            ${{ $detalle->precio }}
                        </th>
                        @if ($pos_c == 0 && $pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                {{ $proy['proyecto']->nombre_agencia }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $total_detalles }}">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="true">
                                        Acciones <span class="fa fa-caret-down"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                        style="background-color: #c8c8c8">
                                        <li>
                                            <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="editar_proyecto('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-fw fa-pencil"></i>
                                                Editar pedido
                                            </a>
                                        </li>
                                        @if ($proy['proyecto']->tipo == 'SO')
                                            <li>
                                                <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                    style="color: black" href="javascript:void(0)"
                                                    onclick="mover_fecha_orden_fija('{{ $proy['proyecto']->id_proyecto }}')">
                                                    <i class="fa fa-fw fa-calendar"></i>
                                                    Mover Fecha de Orden Fija
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                style="color: black" href="javascript:void(0)"
                                                onclick="pre_factura('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-fw fa-file-text-o"></i>
                                                Pre factura
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                style="color: black" href="javascript:void(0)"
                                                onclick="restaurar_recetas('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-fw fa-gift"></i>
                                                Restaurar Recetas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                href="javascript:void(0)" id="edit_pedidos" style="color: black"
                                                onclick="delete_pedido('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
                                                Cancelar pedido
                                            </a>
                                        </li>
                                        @if ($proy['proyecto']->tipo == 'SO')
                                            <li>
                                                <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                    href="javascript:void(0)" style="color: black"
                                                    onclick="delete_orden_fija('{{ $proy['proyecto']->id_proyecto }}')">
                                                    <i class="fa fa-fw fa-trash"></i>
                                                    Cancelar toda la Orden Fija
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="link_opciones_{{ $proy['proyecto']->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="copiar_pedido('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-files-o fa-fw" aria-hidden="true"></i>
                                                Copiar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a target="_blank" style="color: black" href="javascript:void(0)"
                                                onclick="descargar_packing('{{ $proy['proyecto']->id_proyecto }}')">
                                                <i class="fa fa-cubes fa-fw"></i>
                                                Descargar packing list
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
            <script>
                $('#span_venta_{{ $proy['proyecto']->id_proyecto }}').html('Ventas: ${{ round($venta_proy, 2) }}');
                //$('#span_cajas_{{ $proy['proyecto']->id_proyecto }}').html('Cajas: {{ $cajas_proy }}');
                $('#span_ramos_{{ $proy['proyecto']->id_proyecto }}').html('Ramos: {{ $ramos_proy }}');
                $('#th_tallos_{{ $proy['proyecto']->id_proyecto }}').html('{{ $tallos_proy }}');
            </script>
        @endforeach
    </table>
</div>

<div style="overflow-x: scroll">
    <table style="width: 100%; font-size: 0.9em">
        <tbody>
            <tr>
                <td style="vertical-align: top; width: 33%; min-width: 420px" class="padding_lateral_5">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                        <tbody>
                            <tr>
                                <th class="text-center th_yura_green" colspan="2">
                                    VARIEDAD-LONGNITUD-PESO
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    TALLOS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    RAMOS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    MONTO
                                </th>
                            </tr>
                            @php
                                $total_tallos = 0;
                                $total_ramos = 0;
                                $total_monto = 0;
                            @endphp
                            @foreach ($resumen_variedad_longitud as $item)
                                @php
                                    $total_tallos += $item['tallos'];
                                    $total_ramos += $item['ramos'];
                                    $total_monto += $item['venta'];
                                @endphp
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')" class="">
                                    <th style="border-color: #9d9d9d;">
                                        {{ $item['nombre_planta'] }}
                                    </th>
                                    <th style="border-color: #9d9d9d;">
                                        {{ $item['nombre_variedad'] }} {{ $item['longitud'] }}cm {{ $item['peso'] }}gr
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['tallos'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['ramos'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        ${{ round($item['venta'], 2) }}
                                    </th>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="text-center th_yura_green" colspan="2">
                                    TOTALES
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_tallos }}
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_ramos }}
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    ${{ round($total_monto, 2) }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top; width: 33%; min-width: 420px" class="padding_lateral_5">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                        <tbody>
                            <tr>
                                <th class="text-center th_yura_green">
                                    PRESENTACION
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    TALLOS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    RAMOS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    MONTO
                                </th>
                            </tr>
                            @php
                                $total_tallos = 0;
                                $total_ramos = 0;
                                $total_monto = 0;
                            @endphp
                            @foreach ($resumen_presentacion as $item)
                                @php
                                    $total_tallos += $item['tallos'];
                                    $total_ramos += $item['ramos'];
                                    $total_monto += $item['venta'];
                                @endphp
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')" class="">
                                    <th style="border-color: #9d9d9d;">
                                        {{ $item['nombre'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['tallos'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['ramos'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        ${{ round($item['venta'], 2) }}
                                    </th>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="text-center th_yura_green">
                                    TOTALES
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_tallos }}
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_ramos }}
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    ${{ round($total_monto, 2) }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td style="vertical-align: top; width: 33%; min-width: 250px" class="padding_lateral_5">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
                        <tbody>
                            <tr>
                                <th class="text-center th_yura_green" colspan="2">
                                    PIEZAS
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    FULL
                                </th>
                            </tr>
                            @php
                                $total_piezas = 0;
                                $total_full = 0;
                            @endphp
                            @foreach ($resumen_piezas as $item)
                                @php
                                    $total_piezas += $item['cantidad'];
                                    $total_full += $item['cantidad'] * $item['factor'];
                                @endphp
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')" class="">
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['nombre'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ $item['cantidad'] }}
                                    </th>
                                    <th class="text-center" style="border-color: #9d9d9d">
                                        {{ number_format($item['cantidad'] * $item['factor'], 3) }}
                                    </th>
                                </tr>
                            @endforeach
                            <tr>
                                <th class="text-center th_yura_green">
                                    TOTALES
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ $total_piezas }}
                                </th>
                                <th class="text-center th_yura_green padding_lateral_5">
                                    {{ number_format($total_full, 3) }}
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function editar_proyecto(id) {
        datos = {
            id: id
        };
        get_jquery('{{ url('proyectos/editar_proyecto') }}', datos, function(retorno) {
            modal_view('modal-view_editar_proyecto', retorno,
                '<i class="fa fa-fw fa-filter"></i> Editar Pedido', true, false, '95%');
        });
    }

    function delete_pedido(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Cancelar Pedido',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>CANCELAR</b> este pedido?</div>',
        };
        modal_quest('modal_update_especificaciones', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proyectos/delete_pedido') }}', datos, function() {
                    listar_reporte();
                });
            });
    }

    function mover_fecha_orden_fija(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/mover_fecha_orden_fija') }}', datos, function(retorno) {
            modal_view('modal_mover_fecha_orden_fija', retorno,
                '<i class="fa fa-fw fa-calendar"></i> Formulario Orden Fija',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function pre_factura(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('proyectos/pre_factura') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function delete_orden_fija(id_ped) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-danger text-center">¿Está seguro de ELIMINAR la orden fija?</div>',
        };
        modal_quest('modal_delete_orden_fija', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_ped: id_ped,
                }
                post_jquery_m('{{ url('proyectos/delete_orden_fija') }}', datos, function(retorno) {
                    listar_reporte();
                });
            });
    }

    function copiar_pedido(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('proyectos/copiar_pedido') }}', datos, function(retorno) {
            modal_view('modal_copiar_pedido', retorno, '<i class="fa fa-fw fa-calendar"></i> Copiar Pedido',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function descargar_packing(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('proyectos/descargar_packing') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
