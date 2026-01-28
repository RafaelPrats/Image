@if (count($presentaciones) > 0 && count($tallos) > 0 && count($longitudes) > 0)
    <form id="form-clasificacion_blanco" style="width: 100%">
        <table style="width: 100%; margin-top: 5px">
            <tr>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-calendar"></i> Inicio
                        </span>
                        <input type="time" id="hora_inicio" placeholder="07:00" required
                            class="form-control input-yura_default text-center w-100"
                            value="{{ isset($blanco) ? $blanco->hora_inicio : '07:00' }}">
                    </div>
                </td>
                <td style="width: 280px">
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            <i class="fa fa-fw fa-users"></i>
                        </span>
                        <input type="number" id="personal" style="width: 100%" placeholder="Personal" required
                            class="form-control input-yura_default text-center"
                            value="{{ isset($blanco) ? $blanco->personal : '' }}" min="1">
                        <span class="input-group-btn">
                            @if (isset($blanco))
                                <button class="btn btn-yura_default" type="button" title="Rendimiento"
                                    onclick="ver_rendimiento({{ $blanco->id_clasificacion_blanco }})">
                                    <strong>{{ $blanco->getRendimiento() }}</strong> ramos/hr
                                </button>
                            @endif
                            @if (es_local())
                                <button class="btn btn-yura_primary" type="button" title="Rendimiento"
                                    onclick="update_calsificacion_blanco()">
                                    <i class="fa fa-fw fa-save"></i>
                                </button>
                            @endif
                        </span>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Pres.
                        </span>
                        <select id="filtro_presentacion" class="form-control w-100 input-yura_default">
                            <option value="">Todos</option>
                            @foreach ($presentaciones as $p)
                                <option value="{{ $p->id_empaque_p }}">{{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Tallos
                        </span>
                        <select id="filtro_tallos" class="form-control w-100 input-yura_default">
                            <option value="">Todos</option>
                            @foreach ($tallos as $t)
                                <option value="{{ $t->tallos_x_ramos }}">{{ $t->tallos_x_ramos }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                            Long.
                        </span>
                        <select id="filtro_longitud" class="form-control w-100 input-yura_default">
                            <option value="">Todos</option>
                            @foreach ($longitudes as $l)
                                <option value="{{ $l->longitud_ramo . '|' . $l->id_unidad_medida }}">
                                    {{ $l->longitud_ramo . $l->siglas }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group-btn">
                            <button class="btn btn-yura_dark" type="button" title="Listar"
                                onclick="listar_combinaciones()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_default" title="Exportar"
                                onclick="exportar_combinaciones()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <input type="hidden" id="id_blanco" value="{{ isset($blanco) ? $blanco->id_clasificacion_blanco : '' }}">
    </form>

    <div id="div_listado_combinaciones" style="overflow-y: scroll; max-height: 700px; margin-top: 5px">
    </div>

    <script>
        function listar_combinaciones() {
            datos = {
                planta: $('#filtro_planta').val(),
                dias: $('#filtro_dias').val(),
                variedad: $('#filtro_variedad').val(),
                fecha_blanco: $('#fecha_blanco').val(),
                presentacion: $('#filtro_presentacion').val(),
                tallos: $('#filtro_tallos').val(),
                longitud: $('#filtro_longitud').val(),
            };
            get_jquery('{{ url('clasificacion_blanco/listar_combinaciones') }}', datos, function(retorno) {
                $('#div_listado_combinaciones').html(retorno);
                //estructura_tabla('table_clasificacion_blanco');
                $('#table_clasificacion_blanco_filter>label>input').addClass('input-yura_default');
            });
        }

        function exportar_combinaciones(tipo, negativas) {
            datos = {
                planta: $('#filtro_planta').val(),
                dias: $('#filtro_dias').val(),
                variedad: $('#filtro_variedad').val(),
                fecha_blanco: $('#fecha_blanco').val(),
                presentacion: $('#filtro_presentacion').val(),
                tallos: $('#filtro_tallos').val(),
                longitud: $('#filtro_longitud').val(),
            };
            datos = JSON.stringify(datos);
            $.LoadingOverlay('show');
            window.open('{{ url('clasificacion_blanco/exportar_combinaciones') }}?datos=' + datos, '_blank');
            $.LoadingOverlay('hide');
        }

        function exportar_reporte(fecha) {
            $.LoadingOverlay('show');
            window.open('{{ url('clasificacion_blanco/exportar_reporte') }}?fecha=' + fecha +
                '&presentacion=' + $('#filtro_presentacion').val() +
                '&tallos=' + $('#filtro_tallos').val() +
                '&longitud=' + $('#filtro_longitud').val() +
                '&planta=' + $('#filtro_planta').val() +
                '&variedad=' + $('#filtro_variedad').val(), '_blank');
            $.LoadingOverlay('hide');
        }

        function calcular_inventario_i(pos_comb) {
            armar = 0;
            if ($('#armar_' + pos_comb).val() != '')
                armar = parseFloat($('#armar_' + pos_comb).val());
            inv = armar + parseFloat($('#inventario_frio_' + pos_comb).val());
            $('#btn_inventario_' + pos_comb).html(inv);
        }

        function confirmar_pedidos(pos_comb) {
            modal_quest('modal_confirmar_pedidos',
                '<div class="alert alert-info text-center">¿Desea <strong>CONFIRMAR</strong> este día?</div>',
                '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje del sistema', true, false,
                '{{ isPC() ? '25%' : '' }}',
                function() {
                    arreglo = [];
                    for (i = 1; i <= pos_comb; i++) {
                        modificaciones = parseInt($('#btn_modificaciones_fecha_' + 0 + '_' + i).val());
                        pedido = parseInt($('#pedido_' + i + '_' + 1).val());
                        data = {
                            pedido: pedido + modificaciones,
                            inventario: parseFloat($('#inventario_frio_' + i).val()),
                            armar: $('#armar_' + i).val() != '' ? parseFloat($('#armar_' + i).val()) : 0,
                            variedad: $('#id_variedad_' + i).val(),
                            tallos_x_ramo: $('#tallos_x_ramo_' + i).val(),
                            longitud_ramo: $('#longitud_ramo_' + i).val(),
                            id_empaque_p: $('#id_empaque_p_' + i).val(),
                            id_unidad_medida: $('#id_unidad_medida_' + i).val(),
                            texto: $('#texto_' + i).val()
                        };
                        if (data['pedido'] <= (data['inventario'] + data['armar'])) {
                            $('#th_pedidos_' + i).removeClass('error');

                            arreglo.push(data);
                        } else {
                            alert('Faltan ramos por armar para los pedidos de "' + '#texto_' + i + '"');
                            $('#th_pedidos_' + i).addClass('error');
                            return;
                        }
                    }
                    datos = {
                        _token: '{{ csrf_token() }}',
                        arreglo: JSON.stringify(arreglo),
                        fecha_pedidos: $('#fecha_' + 1).val(),
                        planta: $('#filtro_planta').val(),
                    };
                    post_jquery('{{ url('clasificacion_blanco/confirmar_pedidos') }}', datos, function() {
                        cerrar_modals();
                        listar_combinaciones();
                    });
                });
        }

        function modal_armar_row(pos_comb) {
            datos = {
                _token: '{{ csrf_token() }}',
                dias: $('#filtro_dias').val(),
                armar: $('#armar_' + pos_comb).val() != '' ? parseFloat($('#armar_' + pos_comb).val()) : 0,
                variedad: $('#id_variedad_' + pos_comb).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_comb).val(),
                id_empaque_p: $('#id_empaque_p_' + pos_comb).val(),
                planta: $('#filtro_planta').val(),
                pos_comb: pos_comb
            };
            if (datos['armar'] > 0)
                get_jquery('{{ url('clasificacion_blanco/modal_armar_row') }}', datos, function(retorno) {
                    modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> MARCACIONES', true,
                        false, '{{ isPC() ? '75%' : '' }}');
                });
        }

        function store_armar_row(pos_comb, id_marcacion, valor_marcacion) {
            datos = {
                _token: '{{ csrf_token() }}',
                blanco: $('#id_blanco').val(),
                fecha: $('#fecha_blanco').val(),
                id_stock_empaquetado: $('#id_stock_empaquetado').val(),
                pedido: parseFloat($('#pedido_' + pos_comb + '_' + 1).val()),
                inventario: parseFloat($('#inventario_frio_' + pos_comb).val()),
                armar: $('#armar_' + pos_comb).val() != '' ? parseFloat($('#armar_' + pos_comb).val()) : 0,
                mesa: $('#mesa_' + pos_comb).val(),
                variedad: $('#id_variedad_' + pos_comb).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_comb).val(),
                id_empaque_p: $('#id_empaque_p_' + pos_comb).val(),
                id_unidad_medida: $('#id_unidad_medida_' + pos_comb).val(),
                texto: $('#texto_' + pos_comb).val(),
                id_marcacion: id_marcacion,
                valor_marcacion: valor_marcacion,
            };
            post_jquery_m('{{ url('clasificacion_blanco/store_armar_row') }}', datos, function() {
                cerrar_modals();
                listar_combinaciones_row(pos_comb);
                $('#armar_' + pos_comb).val(0);
            }, 'tr_combinacion_' + pos_comb);
        }

        function listar_combinaciones_row(pos_comb) {
            input_fechas = $('.input_fechas');
            fechas = [];
            for (i = 0; i < input_fechas.length; i++) {
                fechas.push(input_fechas[i].value);
            }
            datos = {
                variedad: $('#id_variedad_' + pos_comb).val(),
                id_empaque_p: $('#id_empaque_p_' + pos_comb).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_comb).val(),
                fechas: JSON.stringify(fechas),
                pos_comb: pos_comb,
            }
            get_jquery('{{ url('clasificacion_blanco/listar_combinaciones_row') }}', datos, function(retorno) {
                $('#tr_combinacion_' + pos_comb).html('');
                $('#tr_combinacion_' + pos_comb).html(retorno);
                cerrar_modals();
            }, 'tr_combinacion_' + pos_comb);
        }

        function store_armar(pos_comb) {
            arreglo = [];
            armar = 0;
            for (i = 1; i <= pos_comb; i++) {
                data = {
                    pedido: parseFloat($('#pedido_' + i + '_' + 1).val()),
                    inventario: parseFloat($('#inventario_frio_' + i).val()),
                    armar: $('#armar_' + i).val() != '' ? parseFloat($('#armar_' + i).val()) : 0,
                    mesa: $('#mesa_' + i).val(),
                    variedad: $('#id_variedad_' + i).val(),
                    tallos_x_ramo: $('#tallos_x_ramo_' + i).val(),
                    longitud_ramo: $('#longitud_ramo_' + i).val(),
                    id_empaque_p: $('#id_empaque_p_' + i).val(),
                    id_unidad_medida: $('#id_unidad_medida_' + i).val(),
                    texto: $('#texto_' + i).val()
                };
                armar += data['armar'];
                arreglo.push(data);
            }
            if (armar > 0) {
                datos = {
                    _token: '{{ csrf_token() }}',
                    blanco: $('#id_blanco').val(),
                    fecha: $('#fecha_blanco').val(),
                    id_stock_empaquetado: $('#id_stock_empaquetado').val(),
                    arreglo: JSON.stringify(arreglo),
                };
                post_jquery('{{ url('clasificacion_blanco/store_armar') }}', datos, function() {
                    cerrar_modals();
                    listar_combinaciones()
                });
            }
        }

        function maduracion(pos_comb) {
            arreglo = [];
            for (i = 1; i <= $('#pos_comb_total').val(); i++) {
                if (i != pos_comb) {
                    data = {
                        inventario: parseFloat($('#inventario_frio_' + i).val()),
                        tallos_x_ramo: $('#tallos_x_ramo_' + i).val(),
                        longitud_ramo: $('#longitud_ramo_' + i).val(),
                        variedad: $('#id_variedad_' + i).val(),
                        id_empaque_p: $('#id_empaque_p_' + i).val(),
                        id_unidad_medida: $('#id_unidad_medida_' + i).val(),
                        texto: $('#texto_' + i).val()
                    };
                    arreglo.push(data);
                }
            }
            datos = {
                variedad: $('#id_variedad_' + pos_comb).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_comb).val(),
                id_empaque_p: $('#id_empaque_p_' + pos_comb).val(),
                id_unidad_medida: $('#id_unidad_medida_' + pos_comb).val(),
                texto: $('#texto_' + pos_comb).val(),
                dias: $('#filtro_dias').val(),
                arreglo: JSON.stringify(arreglo)
            };
            get_jquery('{{ url('clasificacion_blanco/maduracion') }}', datos, function(retorno) {
                modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> Días de maduración', true,
                    false, '{{ isPC() ? '65%' : '' }}');
            });
        }

        function update_calsificacion_blanco() {
            if ($('#form-clasificacion_blanco').valid()) {
                datos = {
                    _token: '{{ csrf_token() }}',
                    hora_inicio: $('#hora_inicio').val(),
                    personal: $('#personal').val(),
                    blanco: $('#id_blanco').val(),
                };
                post_jquery('{{ url('clasificacion_blanco/update_calsificacion_blanco') }}', datos, function() {
                    listar_clasificacion_blanco();
                });
            }
        }

        function mostrar_despacho(fecha) {
            datos = {
                fecha: fecha
            };
            get_jquery('{{ url('despachos/listar_resumen_pedidos') }}', datos, function(retorno) {
                modal_view('modal_view_listar_resumen_pedidos', retorno,
                    '<i class="fa fa-fw fa-list-alt"></i> Despachos', true, false, '{{ isPC() ? '95%' : '' }}');
            });
        }

        function modal_inventario_row(pos_comb) {
            datos = {
                _token: '{{ csrf_token() }}',
                dias: $('#filtro_dias').val(),
                variedad: $('#id_variedad_' + pos_comb).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_comb).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_comb).val(),
                id_empaque_p: $('#id_empaque_p_' + pos_comb).val(),
                planta: $('#filtro_planta').val(),
                pos_comb: pos_comb
            };
            get_jquery('{{ url('clasificacion_blanco/modal_inventario_row') }}', datos, function(retorno) {
                modal_view('modal_view', retorno, '<i class="fa fa-fw fa-gift"></i> INVENTARIO', true,
                    false, '{{ isPC() ? '75%' : '' }}');
            });
        }
    </script>
@else
    <div class="alert alert-info text-center">
        No se han encontrado datos que mostrar
    </div>
@endif
