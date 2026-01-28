<div id="table_aperturas">
    @if(sizeof($listado)>0)
        <div style="overflow-y: scroll; max-height: 450px">
            <table width="100%" class="table-responsive table-bordered" id="table_content_aperturas"
                   style="border-color: #9d9d9d; border-radius: 18px 0 0 0">
                <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    </th>
                    <th class="text-center th_yura_green" style="width: 50px">
                        Semana
                    </th>
                    <th class="text-center th_yura_green" style="width: 100px">
                        Calibre
                    </th>
                    <th class="text-center th_yura_green" width="7%">
                        Días Maduración
                    </th>
                    <th class="text-center th_yura_green" title="Entrantes">
                        Tallos
                    </th>
                    <th class="text-center th_yura_green" title="Entrantes">
                        Saldo
                    </th>
                    <th class="text-center th_yura_green" title="Convertidos">
                        Ramos
                    </th>
                    <th class="text-center th_yura_green" width="90px">
                        Est. Acumulado
                    </th>
                    <th class="text-center th_yura_green" width="90px">
                        Real Acumulado
                    </th>
                    <th class="text-center th_yura_green" style="width: 60px;">
                        Cajas
                    </th>
                    <th class="text-center th_yura_green" width="80px">
                        Inventario
                    </th>
                    @if (es_local())
                        <th class="text-center th_yura_green" width="30px" style="border-radius: 0 18px 0 0">

                        </th>
                    @endif
                </tr>
                </thead>
                @php
                    $ramos_x_caja = getConfiguracionEmpresa()->ramos_x_caja;
                    $current_fecha = substr($listado[0]->fecha_inicio,0,10);

                    $total_cajas = 0;
                    $tallos_entrantes = 0;
                    $tallos_restantes = 0;
                    $total_disponibles = 0;
                    $total_ramos = 0;
                    $ids_apertura = '';

                    $ramos_totales = 0;
                    $cajas_totales = 0;
                    $tallos_entrantes_totales = 0;
                    $tallos_restrantes_totales = 0;

                    $i=0;
                @endphp
                <tbody>
                @foreach($listado as $apertura)
                    @php
                        $getStockById = getStockById($apertura->id_stock_apertura);
                        $clasificacion_unitaria = $getStockById->clasificacion_unitaria;
                        $unidad_medida = $clasificacion_unitaria->unidad_medida;
                        $variedad = $getStockById->variedad;
                        $calcularDisponibles = $getStockById->calcularDisponibles();
                        $getDisponibles_estandar = $getStockById->getDisponibles('estandar');
                        $getDisponibles_real = $getStockById->getDisponibles('real');
                        $getRamosEstandar = $getStockById->getRamosEstandar();
                    @endphp
                    <tr onmouseover="$(this).css('background-color','#ADD8E6')" onmouseleave="$(this).css('background-color','')"
                        style="color: {{$unidad_medida->tipo == 'L' ? 'blue' : ''}}">
                        <td class="text-center" style="border-color: #9d9d9d;">
                            <input type="checkbox" id="checkbox_sacar_{{$apertura->id_stock_apertura}}" class="checkbox_sacar"
                                   onchange="seleccionar_checkboxs($(this))">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d;">
                            <input type="hidden" class="ids_apertura" id="id_apertura_{{$apertura->id_stock_apertura}}"
                                   value="{{$apertura->id_stock_apertura}}">
                            <input type="hidden" id="cantidad_disponible_{{$apertura->id_stock_apertura}}"
                                   value="{{$getStockById->cantidad_disponible}}">
                            <input type="hidden" id="ramos_convertidos_{{$apertura->id_stock_apertura}}"
                                   name="ramos_convertidos_{{$apertura->id_stock_apertura}}" class="ramos_convertidos"
                                   value="{{$calcularDisponibles['estandar']}}">
                            {{getSemanaByDateVariedad($apertura->fecha_inicio, $getStockById->id_variedad)->codigo}}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d;
                                    background-color: {{explode('|',$clasificacion_unitaria->color)[0]}};
                                    color: {{explode('|',$clasificacion_unitaria->color)[1]}};">
                            {{explode('|',$clasificacion_unitaria->nombre)[0]}}
                            {{$unidad_medida->siglas}}

                            <input type="hidden" id="calibre_unitario_{{$apertura->id_stock_apertura}}"
                                   value="{{explode('|',$clasificacion_unitaria->nombre)[0]}}">
                            <input type="hidden" id="factor_calibre_unitario_{{$apertura->id_stock_apertura}}"
                                   value="{{explode('|',$clasificacion_unitaria->nombre)[1]}}">
                            <input type="hidden" id="tipo_calibre_unitatio_{{$apertura->id_stock_apertura}}"
                                   value="{{$unidad_medida->tipo}}">
                        </td>
                        @php
                            $color = '';
                            $maximo_estandar = true;
                            $minimo_estandar = true;
                            $estandar_estandar = true;
                            if(difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days > $variedad->maximo_apertura){
                                $color = '#ce8483';
                                $maximo_estandar = false;
                            }
                            if(difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days < $variedad->minimo_apertura){
                                $color = '#ce8483';
                                $minimo_estandar = false;
                            }
                            if(difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days > $variedad->estandar_apertura &&
                                difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days <= $variedad->maximo_apertura){
                                $color = '#ffef92';
                                $estandar_estandar = false;
                            }
                        @endphp
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{$color}}">
                            {{difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days}}
                            <input type="hidden" id="dias_maduracion_{{$apertura->id_stock_apertura}}"
                                   value="{{difFechas(date('Y-m-d'),substr($apertura->fecha_inicio,0,10))->days}}">
                            <input type="checkbox" class="hidden" id="maximo_estandar_{{$apertura->id_stock_apertura}}"
                                    {{!$maximo_estandar ? 'checked' : ''}}>
                            <input type="checkbox" class="hidden" id="minimo_estandar_{{$apertura->id_stock_apertura}}"
                                    {{!$minimo_estandar ? 'checked' : ''}}>
                            <input type="checkbox" class="hidden" id="estandar_estandar_{{$apertura->id_stock_apertura}}"
                                    {{!$estandar_estandar ? 'checked' : ''}}>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d" title="Entrantes"
                            id="celda_tallos_entrantes_{{$apertura->id_stock_apertura}}">
                            {{number_format($getStockById->cantidad_tallos)}}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d" title="Restantes"
                            id="celda_tallos_restantes_{{$apertura->id_stock_apertura}}">
                            {{number_format($getStockById->cantidad_disponible)}}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d" title="Convertidos"
                            id="celda_ramos_convertidos_{{$apertura->id_stock_apertura}}">
                            {{number_format($calcularDisponibles['estandar'], 2)}}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <span class="badge" title="Estandar">
                                {{number_format($getDisponibles_estandar, 2)}}
                            </span>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <span class="badge" style="background-color: #357ca5" title="Real">
                                {{number_format($getDisponibles_real, 2)}}
                            </span>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <span class="badge" style="color: #0a0a0a; background-color: #3cf7ff" title="Cajas">
                                {{number_format($getRamosEstandar / $ramos_x_caja, 2)}}
                            </span>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" class="text-center input_sacar" id="nuevo_conteo_{{$apertura->id_stock_apertura}}" min="0"
                                   style="width: 100%" max="{{$getStockById->cantidad_disponible}}"
                                   onchange="seleccionar_apertura_nuevo_conteo('{{$apertura->id_stock_apertura}}')" value="">
                            <input type="hidden" class="text-center input_sacar" id="sacar_{{$apertura->id_stock_apertura}}" min="1"
                                   style="width: 100%" max="{{$getStockById->cantidad_disponible}}"
                                   onchange="seleccionar_apertura_sacar('{{$apertura->id_stock_apertura}}')" value="">
                            <input type="hidden" class="input_sacar_ini" id="sacar_ini_{{$apertura->id_stock_apertura}}"
                                   value="{{$getStockById->cantidad_disponible}}">
                        </td>
                        @if (es_local())
                            <td class="text-center" style="border-color: #9d9d9d">
                                <button type="button" class="btn btn-xs btn-default" title="Mover fecha"
                                        onclick="mover_fecha('{{$apertura->id_stock_apertura}}')">
                                    <i class="fa fa-fw fa-calendar"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                    @php
                        $total_cajas += round($getRamosEstandar / $ramos_x_caja, 2);
                        $tallos_entrantes += $getStockById->cantidad_tallos;
                        $tallos_restantes += $getStockById->cantidad_disponible;
                        $total_disponibles += $getDisponibles_estandar;
                        $total_ramos += $calcularDisponibles['estandar'];
                        $ids_apertura .= $apertura->id_stock_apertura.'|';
                    @endphp
                    @if(count($listado) > ($i+1) && substr($listado[$i+1]->fecha_inicio,0,10) != substr($apertura->fecha_inicio,0,10) ||
                        count($listado) == ($i+1))
                        <tr style="background-color: #e9ecef;">
                            <td style="border-color: #9d9d9d" colspan="3" class="text-center">
                                <input type="hidden" id="fecha_{{substr($apertura->fecha_inicio,0,10)}}" class="fechas_aperturas"
                                       value="{{substr($apertura->fecha_inicio,0,10)}}">
                                <input type="hidden" id="fecha_ids_aperturas_{{substr($apertura->fecha_inicio,0,10)}}"
                                       value="{{$ids_apertura}}">
                                <input type="hidden" id="total_ramos_{{substr($apertura->fecha_inicio,0,10)}}"
                                       value="{{$total_ramos}}">
                                <strong>
                                    {{getDias(TP_COMPLETO,FR_ARREGLO)[transformDiaPhp(date('w',strtotime(substr($apertura->fecha_inicio,0,10))))]}}
                                    {{convertDateToText(substr($apertura->fecha_inicio,0,10))}}
                                </strong>
                            </td>
                            <th class="text-center" style="border-color: #9d9d9d">Total</th>
                            <th class="text-center" style=" border-color: #9d9d9d">
                                {{number_format($tallos_entrantes)}}
                            </th>
                            <th class="text-center" style=" border-color: #9d9d9d">
                                {{number_format($tallos_restantes)}}
                            </th>
                            <th class="text-center" style=" border-color: #9d9d9d"
                                id="celda_total_ramos_{{substr($apertura->fecha_inicio,0,10)}}">
                                {{number_format($total_ramos, 2)}}
                            </th>
                            <th class="text-center" style=" border-color: #9d9d9d">{{number_format($total_disponibles, 2)}}</th>
                            <td style="border-bottom-color: #9d9d9d; border-right-color: #9d9d9d"></td>
                            <td style="border-bottom-color: #9d9d9d; border-right-color: #9d9d9d" class="text-center">
                                {{number_format($total_cajas, 2)}}
                            </td>
                            <td style="border-bottom-color: #9d9d9d; border-right-color: #9d9d9d" colspan="2"></td>
                        </tr>
                        @php
                            $cajas_totales += $total_cajas;
                            $ramos_totales += $total_ramos;
                            $tallos_entrantes_totales += $tallos_entrantes;
                            $tallos_restrantes_totales += $tallos_restantes;
                            $total_ramos = 0;
                            $total_cajas = 0;
                            $tallos_entrantes = 0;
                            $tallos_restantes = 0;
                            $total_disponibles = 0;
                            $ids_apertura = '';
                        @endphp
                    @endif
                    @php
                        $i++;
                    @endphp
                @endforeach
                <tr id="tr_fijo_bottom_0">
                    <th colspan="4" class="text-center th_yura_green">
                        TOTALES
                    </th>
                    <th class="text-center th_yura_green">
                        {{number_format($tallos_entrantes_totales)}}
                    </th>
                    <th class="text-center th_yura_green">
                        {{number_format($tallos_restrantes_totales)}}
                    </th>
                    <th class="text-center th_yura_green" colspan="2">{{number_format($ramos_totales, 2)}}</th>
                    <th class="th_yura_green"></th>
                    <th class="text-center th_yura_green ">
                        {{number_format($cajas_totales, 2)}}
                    </th>
                    <th colspan="2" class="th_yura_green"></th>
                </tr>
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>

<style>
    #table_content_aperturas tr#tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9 !important;
    }

    #table_content_aperturas tr#tr_fijo_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9 !important;
    }
</style>

<script>
    function seleccionar_apertura_nuevo_conteo(apertura) {
        sacar = $('#sacar_ini_' + apertura).val() - $('#nuevo_conteo_' + apertura).val();
        if (sacar > 0 && sacar <= $('#sacar_ini_' + apertura).val())
            $('#sacar_' + apertura).val(sacar);
        else
            $('#sacar_' + apertura).val('');

        seleccionar_apertura_sacar(apertura);
    }

    function seleccionar_apertura_sacar(apertura) {
        if ($('#sacar_' + apertura).val() != '' && $('#sacar_' + apertura).val() > 0) {
            texto = '';
            if ($('#maximo_estandar_' + apertura).prop('checked'))
                texto = 'por encima del máximo';
            if ($('#minimo_estandar_' + apertura).prop('checked'))
                texto = 'por debajo del mínimo';
            if ($('#estandar_estandar_' + apertura).prop('checked'))
                texto = 'por encima del estandar';

            if (texto != '') {
                if (!$('#check_dont_verify').prop('checked')) {
                    modal_quest('modal_quest_input_sacar',
                        '<div class="alert alert-info text-center">Está seleccionando flores con días de maduración ' + texto + ' permitido</div>',
                        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '{{isPC() ? '35%' : ''}}', function () {
                            tallos_x_coche = $('#tallos_x_coche').val();
                            if (tallos_x_coche == '' || tallos_x_coche <= 0)
                                tallos_x_coche = 1;

                            $('#checkbox_sacar_' + apertura).prop('checked', true);

                            $('#html_current_sacar').html('');

                            listado = $('.checkbox_sacar');
                            $('#btn_sacar').hide();
                            $('#html_current_sacar').hide();
                            cantidad_seleccionada = 0;
                            for (i = 0; i < listado.length; i++) {
                                if (listado[i].checked) {
                                    $('#btn_sacar').show();
                                    $('#html_current_sacar').show();
                                    factor = $('#factor_calibre_unitario_' + listado[i].id.substr(15)).val();
                                    seleccionados = parseFloat($('#sacar_' + listado[i].id.substr(15)).val()) * tallos_x_coche;
                                    cantidad_seleccionada += (Math.round((seleccionados / factor) * 100) / 100);
                                    $('#html_current_sacar').html('Seleccionados: ' + (Math.round(cantidad_seleccionada * 100) / 100));
                                }
                            }

                            cerrar_modals();
                        });
                } else {
                    tallos_x_coche = $('#tallos_x_coche').val();
                    if (tallos_x_coche == '' || tallos_x_coche <= 0)
                        tallos_x_coche = 1;

                    $('#checkbox_sacar_' + apertura).prop('checked', true);

                    $('#html_current_sacar').html('');

                    listado = $('.checkbox_sacar');
                    $('#btn_sacar').hide();
                    $('#html_current_sacar').hide();
                    cantidad_seleccionada = 0;
                    for (i = 0; i < listado.length; i++) {
                        if (listado[i].checked) {
                            $('#btn_sacar').show();
                            $('#html_current_sacar').show();
                            factor = $('#factor_calibre_unitario_' + listado[i].id.substr(15)).val();
                            seleccionados = parseFloat($('#sacar_' + listado[i].id.substr(15)).val()) * tallos_x_coche;
                            cantidad_seleccionada += (Math.round((seleccionados / factor) * 100) / 100);
                            $('#html_current_sacar').html('Seleccionados: ' + (Math.round(cantidad_seleccionada * 100) / 100));
                        }
                    }
                }
            } else {
                $('#checkbox_sacar_' + apertura).prop('checked', true);
                $('#btn_sacar').show();
                $('#html_current_sacar').show();
            }
        } else {
            $('#sacar_' + apertura).val(0);
            $('#checkbox_sacar_' + apertura).prop('checked', false);
            $('#btn_sacar').hide();
            $('#html_current_sacar').hide();
        }
        seleccionar_checkboxs($('#checkbox_sacar_' + apertura));
    }

    function seleccionar_checkboxs(current) {
        tallos_x_coche = $('#tallos_x_coche').val();
        if (tallos_x_coche == '' || tallos_x_coche <= 0)
            tallos_x_coche = 1;

        if (current != '' && current.prop('checked')) {
            current.prop('checked', false);
            apertura = current.prop('id').substr(15);
            $('#btn_sacar').hide();
            $('#html_current_sacar').hide();

            texto = '';
            if ($('#maximo_estandar_' + apertura).prop('checked'))
                texto = 'por encima del máximo';
            if ($('#minimo_estandar_' + apertura).prop('checked'))
                texto = 'por debajo del mínimo';
            if ($('#estandar_estandar_' + apertura).prop('checked'))
                texto = 'por encima del estandar';

            if (texto != '' && !$('#check_dont_verify').prop('checked')) {
                if ($('#sacar_' + current.prop('id').substr(15)).val() > 0) {
                    modal_quest('modal_quest_input_sacar',
                        '<div class="alert alert-info text-center">Está seleccionando flores con días de maduración ' + texto + ' permitido</div>',
                        '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false, '{{isPC() ? '35%' : ''}}', function () {
                            current.prop('checked', true);

                            $('#html_current_sacar').html('');

                            listado = $('.checkbox_sacar');
                            $('#btn_sacar').hide();
                            $('#html_current_sacar').hide();
                            cantidad_seleccionada = 0;
                            for (i = 0; i < listado.length; i++) {
                                if (listado[i].checked) {
                                    $('#btn_sacar').show();
                                    $('#html_current_sacar').show();
                                    factor = $('#factor_calibre_unitario_' + listado[i].id.substr(15)).val();
                                    seleccionados = parseFloat($('#sacar_' + listado[i].id.substr(15)).val()) * tallos_x_coche;
                                    cantidad_seleccionada += (Math.round((seleccionados / factor) * 100) / 100);
                                    $('#html_current_sacar').html('Seleccionados: ' + Math.round(cantidad_seleccionada * 100) / 100);
                                }
                            }

                            cerrar_modals();
                        });
                } else {
                    current.prop('checked', false);
                }
            } else {
                if ($('#sacar_' + current.prop('id').substr(15)).val() > 0) {

                    current.prop('checked', true);

                    $('#html_current_sacar').html('');

                    listado = $('.checkbox_sacar');
                    $('#btn_sacar').hide();
                    $('#html_current_sacar').hide();
                    cantidad_seleccionada = 0;
                    for (i = 0; i < listado.length; i++) {
                        if (listado[i].checked) {
                            $('#btn_sacar').show();
                            $('#html_current_sacar').show();
                            factor = $('#factor_calibre_unitario_' + listado[i].id.substr(15)).val();
                            seleccionados = parseFloat($('#sacar_' + listado[i].id.substr(15)).val()) * tallos_x_coche;
                            cantidad_seleccionada += (Math.round((seleccionados / factor) * 100) / 100);
                            $('#html_current_sacar').html('Seleccionados: ' + Math.round(cantidad_seleccionada * 100) / 100);
                        }
                    }
                } else {
                    current.prop('checked', false);
                }
            }
        } else {
            $('#html_current_sacar').html('');

            listado = $('.checkbox_sacar');
            $('#btn_sacar').hide();
            $('#html_current_sacar').hide();
            cantidad_seleccionada = 0;
            for (i = 0; i < listado.length; i++) {
                if (listado[i].checked) {
                    $('#btn_sacar').show();
                    $('#html_current_sacar').show();
                    factor = $('#factor_calibre_unitario_' + listado[i].id.substr(15)).val();
                    seleccionados = parseFloat($('#sacar_' + listado[i].id.substr(15)).val()) * tallos_x_coche;
                    cantidad_seleccionada += (Math.round((seleccionados / factor) * 100) / 100);
                    $('#html_current_sacar').html('Seleccionados: ' + Math.round(cantidad_seleccionada * 100) / 100);
                }
            }
        }
    }

    function sacar_aperturas() {
        listado = $('.checkbox_sacar');
        arreglo = [];

        tallos_x_coche = $('#tallos_x_coche').val();
        if (tallos_x_coche == '' || tallos_x_coche <= 0)
            tallos_x_coche = 1;

        for (i = 0; i < listado.length; i++) {
            if (listado[i].checked) {
                id = listado[i].id.substr(15);
                pos = $('#pos_pedido').val();

                factor = $('#factor_calibre_unitario_' + id).val();
                seleccionados = parseFloat($('#sacar_' + id).val()) * tallos_x_coche;
                cantidad_seleccionada = (Math.round((seleccionados / factor) * 100) / 100);

                data = {
                    id_stock_apertura: id,
                    dias_maduracion: $('#dias_maduracion_' + id).val(),
                    cantidad_ramos_estandar: cantidad_seleccionada,
                };
                arreglo.push(data);
            }
        }

        datos = {
            _token: '{{csrf_token()}}',
            arreglo: arreglo
        };
        post_jquery('{{url('apertura/sacar')}}', datos, function () {
            buscar_listado();
            $('#tallos_x_coche').val('');
            $('#html_current_sacar').html('');
        });
    }

    set_min_today($('#fecha_pedidos'));
    buscar_pedidos();
</script>
