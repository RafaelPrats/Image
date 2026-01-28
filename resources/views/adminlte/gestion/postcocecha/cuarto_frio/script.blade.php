<script>
    //listar_inventarios();

    function listar_inventarios() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            presentacion: $('#filtro_presentacion').val(),
            longitud: $('#filtro_longitud').val(),
            tipo: $('#filtro_tipo').val(),
        };
        get_jquery('{{ url('cuarto_frio/listar_inventarios') }}', datos, function(retorno) {
            $('#div_content_cuarto_frio').html(retorno);
            estructura_tabla('table_cuarto_frio', false);
            $('#table_cuarto_frio_filter>label>input').addClass('input-yura_default')
        });
    }

    function exportar_inventarios(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('cuarto_frio/exportar_inventarios') }}?planta=' + $("#filtro_planta").val() +
            '&variedad=' + $("#filtro_variedad").val() +
            '&tipo=' + $("#filtro_tipo").val() +
            '&presentacion=' + $('#filtro_presentacion').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function editar_dia(pos_inv, pos_dia) {
        origen = parseInt($('#btn_editar_' + pos_inv + '_' + pos_dia).html());
        $('#span_editar_' + pos_inv + '_' + pos_dia).hide();
        $('#input_editar_' + pos_inv + '_' + pos_dia).show();
        $('#input_editar_' + pos_inv + '_' + pos_dia).focus();
        $('#input_editar_' + pos_inv + '_' + pos_dia).val(origen);

        $('#input_accion_' + pos_inv + '_' + pos_dia).val('E');

        $('#btn_save_' + pos_inv).show();
        $('#btn_botar_' + pos_inv).show();
    }

    function add_dia(pos_inv, pos_dia) {
        $('#span_editar_' + pos_inv + '_' + pos_dia).hide();
        $('#input_add_' + pos_inv + '_' + pos_dia).show();
        $('#input_add_' + pos_inv + '_' + pos_dia).focus();

        $('#input_accion_' + pos_inv + '_' + pos_dia).val('A');

        $('#btn_save_' + pos_inv).show();
    }

    function editar_inventario(pos_inv) {
        var add = [];
        var edit = [];
        for (i = 0; i <= 4; i++) {
            accion = $('#input_accion_' + pos_inv + '_' + i).val();
            valor = '';
            if (accion == 'A') {
                valor = $('#input_add_' + pos_inv + '_' + i).val();
                if (valor > 0)
                    add.push({
                        valor: valor,
                        dia: i,
                    });
            }
            if (accion == 'E') {
                valor = $('#input_editar_' + pos_inv + '_' + i).val();
                origen = $('#span_editar_' + pos_inv + '_' + i + '>span').html();
                if (valor >= 0)
                    edit.push({
                        origen: origen,
                        valor: valor,
                        dia: i,
                    });
            }
        }
        data = {
            variedad: $('#variedad_' + pos_inv).val(),
            presentacion: $('#presentacion_' + pos_inv).val(),
            tallos_x_ramo: $('#tallos_x_ramo_' + pos_inv).val(),
            longitud_ramo: $('#longitud_ramo_' + pos_inv).val(),
            unidad_medida: $('#unidad_medida_' + pos_inv).val(),
        };
        if (add.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                add: add,
                data: data
            };
            post_jquery('{{ url('cuarto_frio/add_inventario') }}', datos, function() {
                cerrar_modals();
                listar_inventarios();
            });
        }
        if (edit.length > 0) {
            for (i = 0; i < edit.length; i++) {
                valor = $('#input_editar_' + pos_inv + '_' + edit[i]['dia']).prop('readonly', true);
                datos = {
                    _token: '{{ csrf_token() }}',
                    edit: edit[i],
                    data: data
                };
                post_jquery('{{ url('cuarto_frio/edit_inventario') }}', datos, function() {
                    cerrar_modals();
                    listar_inventarios();
                });

                $('#inventario_target_' + edit[i]['dia']).val(pos_inv);
            }
        } else {
            listar_inventarios();
        }
    }

    function botar_inventario(pos_inv) {
        var data = [];
        for (i = 0; i <= 4; i++) {
            valor = $('#input_editar_' + pos_inv + '_' + i).val();
            origen = parseInt($('#span_editar_' + pos_inv + '_' + i + '>span').html());
            if (valor > 0)
                data.push({
                    origen: origen,
                    valor: valor,
                    dia: i,
                });
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
                variedad: $('#variedad_' + pos_inv).val(),
                presentacion: $('#presentacion_' + pos_inv).val(),
                tallos_x_ramo: $('#tallos_x_ramo_' + pos_inv).val(),
                longitud_ramo: $('#longitud_ramo_' + pos_inv).val(),
            };
            post_jquery_m('{{ url('cuarto_frio/botar_inventario') }}', datos, function() {
                cerrar_modals();
                listar_inventarios();
            });
        }
    }

    function delete_dia(dia) {
        datos = {
            _token: '{{ csrf_token() }}',
            dia: dia,
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            presentacion: $('#filtro_presentacion').val(),
        };
        post_jquery('{{ url('cuarto_frio/delete_dia') }}', datos, function() {
            cerrar_modals();
            listar_inventarios();
        });
    }

    function save_dia(dia) {
        pos_inv = $('#inventario_target_' + dia).val();
        data = {
            variedad: $('#variedad_' + pos_inv).val(),
            presentacion: $('#presentacion_' + pos_inv).val(),
            tallos_x_ramo: $('#tallos_x_ramo_' + pos_inv).val(),
            longitud_ramo: $('#longitud_ramo_' + pos_inv).val(),
            unidad_medida: $('#unidad_medida_' + pos_inv).val(),
            editar: $('#input_editar_' + pos_inv + '_' + dia).val(),
            dia: dia
        };
        basura = $('#basura_dia_' + dia).val();
        arreglo = [];

        list = $('.input_add_' + dia);
        total_convert = 0;

        for (i = 0; i < list.length; i++) {
            if (list[i].value > 0) {
                var inv_i = list[i].name.substr(4);

                factor = 0;
                convert = list[i].value * factor;
                total_convert += convert;

                inventario = {
                    variedad: $('#variedad_' + inv_i).val(),
                    presentacion: $('#presentacion_' + inv_i).val(),
                    tallos_x_ramo: $('#tallos_x_ramo_' + inv_i).val(),
                    longitud_ramo: $('#longitud_ramo_' + inv_i).val(),
                    unidad_medida: $('#unidad_medida_' + inv_i).val(),
                    add: $('#input_add_' + inv_i + '_' + dia).val()
                };
                arreglo.push({
                    inventario: inventario
                });
            }
        }
        total_convert += parseInt(basura);

        if (total_convert <= data['editar']) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: data,
                arreglo: arreglo,
                basura: basura
            };
            post_jquery('{{ url('cuarto_frio/save_dia') }}', datos, function() {
                cerrar_modals();
                listar_inventarios();
            });
        } else {
            alerta('<div class="alert alert-warning text-center">La cantidad de ramos total ingresada (' +
                total_convert + ') ' +
                'es mayor a la cantidad a editar (' + data['editar'] + ')</div>');
        }
    }

    function add_new_inventarios() {
        datos = {}
        get_jquery('{{ url('cuarto_frio/add_new_inventarios') }}', datos, function(retorno) {
            modal_view('modal_add_new_inventarios', retorno,
                '<i class="fa fa-fw fa-plus"></i> Agregar nuevo formulario', true, false, '85%');
        })
    }
</script>
