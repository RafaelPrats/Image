<div style="overflow-y: scroll; width: 100%; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em"
        id="table_form_especificaciones">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" style="min-width: 60px">
                PIEZAS
            </th>
            <th class="text-center th_yura_green" style="min-width: 100px">
                VARIEDAD
            </th>
            <th class="text-center th_yura_green" style="min-width: 90px">
                COLOR
            </th>
            <th class="text-center th_yura_green" style="min-width: 160px">
                CAJA
            </th>
            <th class="text-center th_yura_green" style="min-width: 160px">
                PRESENTACION
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                R. X CAJA
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                TOTAL RAMOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                T. X RAMOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                TOTAL TALLOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 120px">
                LONGITUD
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                PRECIO
                <input type="number" style="width: 100%; color: black" class="text-center" min="0"
                    id="form_input_all_precio_esp" onchange="set_all_precio_esp()" onkeyup="set_all_precio_esp()">
            </th>
            @foreach ($marcaciones as $m)
                <th class="text-center bg-yura_dark" style="min-width: 100px">
                    {{ $m->nombre }}
                    <input type="text" style="width: 100%; color: black" class="text-center" min="0"
                        id="form_input_all_marcacion_{{ $m->id_dato_exportacion }}"
                        onchange="set_all_marcacion('{{ $m->id_dato_exportacion }}')"
                        onkeyup="set_all_marcacion('{{ $m->id_dato_exportacion }}')">
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="min-width: 40px">
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            @php
                $esp_emp = $item->especificacionesEmpaque[0];
                $det_esp = $esp_emp->detalles[0];
                $caja = $esp_emp->empaque;
                $variedad = $det_esp->variedad;
                $planta = $variedad->planta;
            @endphp
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        onchange="calcular_totales_especificaciones('{{ $pos }}')"
                        onkeyup="calcular_totales_especificaciones('{{ $pos }}')"
                        id="form_piezas_{{ $pos }}" min="0">

                    <input type="hidden" class="num_pos_form_especificaciones" value="{{ $pos }}">
                    <input type="hidden" id="form_nombre_planta_{{ $pos }}" value="{{ $planta->nombre }}">
                    <input type="hidden" id="form_variedad_{{ $pos }}" value="{{ $det_esp->id_variedad }}">
                    <input type="hidden" id="form_nombre_variedad_{{ $pos }}"
                        value="{{ $variedad->nombre }}">
                    <input type="hidden" id="form_caja_{{ $pos }}" value="{{ $esp_emp->id_empaque }}">
                    <input type="hidden" id="form_nombre_caja_{{ $pos }}"
                        value="{{ explode('|', $caja->nombre)[0] }}">
                    <input type="hidden" id="form_presentacion_{{ $pos }}"
                        value="{{ $det_esp->id_empaque_p }}">
                    <input type="hidden" id="form_nombre_presentacion_{{ $pos }}"
                        value="{{ $det_esp->empaque_p->nombre }}">
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $planta->nombre }}
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ explode('|', $caja->nombre)[0] }}
                </td>
                <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $det_esp->empaque_p->nombre }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="form_ramos_x_caja_{{ $pos }}" value="{{ $det_esp->cantidad }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="form_total_ramos_{{ $pos }}" readonly>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="form_tallos_x_ramos_{{ $pos }}" value="{{ $det_esp->tallos_x_ramos }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" class="text-center"
                        id="form_total_tallos_{{ $pos }}" readonly>
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%; color: black" class="text-center"
                        id="form_longitud_{{ $pos }}" value="{{ $det_esp->longitud_ramo }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%; color: black" id="form_precio_esp_{{ $pos }}"
                        class="text-center">
                </td>
                @foreach ($marcaciones as $m)
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%; color: black" class="text-center"
                            id="form_marcacion_esp_{{ $m->id_dato_exportacion }}_{{ $pos }}">
                    </td>
                @endforeach
                <td class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_primary" title="Agregar al Pedido"
                            onclick="agregar_especificacion_pedido('{{ $pos }}')">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="15">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_danger"
                        onclick="agregar_all_especificacion_pedido()">
                        <i class="fa fa-fw fa-plus"></i> Agregar Productos al Pedido
                    </button>
                </div>
            </th>
        </tr>
    </table>
</div>

<script>
    function calcular_totales_especificaciones(pos) {
        piezas = parseInt($('#form_piezas_' + pos).val());
        ramos_x_caja = parseInt($('#form_ramos_x_caja_' + pos).val());
        tallos_x_ramos = parseInt($('#form_tallos_x_ramos_' + pos).val());

        total_ramos = piezas * ramos_x_caja;
        total_tallos = piezas * ramos_x_caja * tallos_x_ramos;
        $('#form_total_ramos_' + pos).val(total_ramos);
        $('#form_total_tallos_' + pos).val(total_tallos);
    }

    function set_all_precio_esp() {
        precio = $('#form_input_all_precio_esp').val();
        num_pos_form_especificaciones = $('.num_pos_form_especificaciones');
        for (i = 0; i < num_pos_form_especificaciones.length; i++) {
            pos = num_pos_form_especificaciones[i].value;
            piezas = parseInt($('#form_piezas_' + pos).val());
            if (piezas > 0) {
                $('#form_precio_esp_' + pos).val(precio);
            }
        }
    }

    function set_all_marcacion(marcacion) {
        valor = $('#form_input_all_marcacion_' + marcacion).val();
        num_pos_form_especificaciones = $('.num_pos_form_especificaciones');
        for (i = 0; i < num_pos_form_especificaciones.length; i++) {
            pos = num_pos_form_especificaciones[i].value;
            piezas = parseInt($('#form_piezas_' + pos).val());
            if (piezas > 0) {
                $('#form_marcacion_esp_' + marcacion + '_' + pos).val(valor);
            }
        }
    }

    function agregar_especificacion_pedido(pos) {
        piezas = $('#form_piezas_' + pos).val();
        nombre_planta = $('#form_nombre_planta_' + pos).val();
        variedad = $('#form_variedad_' + pos).val();
        nombre_variedad = $('#form_nombre_variedad_' + pos).val();
        caja = $('#form_caja_' + pos).val();
        nombre_caja = $('#form_nombre_caja_' + pos).val();
        presentacion = $('#form_presentacion_' + pos).val();
        nombre_presentacion = $('#form_nombre_presentacion_' + pos).val();
        ramos_x_caja = $('#form_ramos_x_caja_' + pos).val();
        total_ramos = $('#form_total_ramos_' + pos).val();
        tallos_x_ramos = $('#form_tallos_x_ramos_' + pos).val();
        total_tallos = $('#form_total_tallos_' + pos).val();
        longitud = $('#form_longitud_' + pos).val();
        precio_esp = $('#form_precio_esp_' + pos).val();

        form_cant_detalles++;

        ids_marcaciones = $('.ids_marcaciones');
        celdas_marcaciones = '';
        for (m = 0; m < ids_marcaciones.length; m++) {
            id_marcacion = ids_marcaciones[m].value;
            valor_marcacion = $('#form_marcacion_esp_' + id_marcacion + '_' + pos).val();
            celdas_marcaciones += '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="text" style="width: 100%; color: black" class="text-center" ' +
                'id="ped_marcacion_' + id_marcacion + '_' + form_cant_detalles + '" value="' + valor_marcacion + '">' +
                '</td>';
        }

        clase_tr = "'.tr_form_ped_" + form_cant_detalles + "'";
        clase_bg = "'bg-yura_dark'";
        $('#tbody_form_contenido_pedido').append('<tr class="tr_form_ped_' + form_cant_detalles + '" ' +
            'onmouseover="$(' + clase_tr + ').addClass(' + clase_bg + ')"' +
            'onmouseleave="$(' + clase_tr + ').removeClass(' + clase_bg + ')">' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center"' +
            'onchange="calcular_totales_pedido()"' +
            'onkeyup="calcular_totales_pedido()"' +
            'id="ped_piezas_' + form_cant_detalles + '" min="0" value="' + piezas + '">' +

            '<input type="hidden" class="pos_ped_especificaciones" value="' + form_cant_detalles + '">' +
            '<input type="hidden" id="ped_variedad_' + form_cant_detalles +
            '" value="' + variedad + '">' +
            '<input type="hidden" id="ped_caja_' + form_cant_detalles + '" value="' + caja + '">' +
            '<input type="hidden" id="ped_presentacion_' + form_cant_detalles + '" ' +
            'value = "' + presentacion + '" > ' +
            '<input type="hidden" id="ped_longitud_' + form_cant_detalles + '" ' +
            'value = "' + longitud + '" > ' +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_planta +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_variedad +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_caja +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            nombre_presentacion +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_ramos_x_caja_' + form_cant_detalles + '" min="0" value="' + ramos_x_caja + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_ramos_' + form_cant_detalles + '" readonly disabled value="' + total_ramos + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_tallos_x_ramos_' + form_cant_detalles + '" min="0" value="' + tallos_x_ramos + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_tallos_' + form_cant_detalles + '" readonly disabled value="' + total_tallos + '">' +
            '</td>' +
            '<td class="text-center padding_lateral_5" style="border-color: #9d9d9d">' +
            longitud + 'cm' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%; color: black" class="text-center" ' +
            'onchange="calcular_totales_pedido()" ' +
            'onkeyup="calcular_totales_pedido()" ' +
            'id="ped_precio_esp_' + form_cant_detalles + '" min="0" value="' + precio_esp + '">' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="text" style="width: 100%; color: black" class="text-center" ' +
            'id="ped_total_precio_caja_' + form_cant_detalles + '" readonly disabled>' +
            '</td>' +
            celdas_marcaciones +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="delete_contenido_pedido(' +
            form_cant_detalles + ')" title="Eliminar Pieza">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');

        calcular_totales_pedido();
    }

    function agregar_all_especificacion_pedido() {
        num_pos_form_especificaciones = $('.num_pos_form_especificaciones');
        for (x = 0; x < num_pos_form_especificaciones.length; x++) {
            pos = num_pos_form_especificaciones[x].value;
            piezas = parseInt($('#form_piezas_' + pos).val());
            if (piezas > 0) {
                agregar_especificacion_pedido(pos);
            }
        }
    }
</script>
