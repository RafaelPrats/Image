<script>
    listar_reporte();

    function add_recepcion() {
        datos = {
            fecha: $('#filtro_fecha').val()
        }
        get_jquery('{{ url('recepcion/add_recepcion') }}', datos, function(retorno) {
            modal_view('modal_add_recepcion', retorno, '<i class="fa fa-fw fa-plus"></i> Formulario Cosecha',
                true, false, '{{ isPC() ? '65%' : '' }}',
                function() {});
        })
    }

    function ver_sobrantes() {
        datos = {
            fecha: $('#filtro_fecha').val()
        }
        get_jquery('{{ url('recepcion/ver_sobrantes') }}', datos, function(retorno) {
            modal_view('modal_ver_sobrantes', retorno, '<i class="fa fa-fw fa-gift"></i> Sobrantes',
                true, false, '{{ isPC() ? '65%' : '' }}',
                function() {});
        })
    }

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            longitud: $('#filtro_longitud').val(),
        }
        get_jquery('{{ url('recepcion/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado_recepciones').html(retorno);
            estructura_tabla('table_listado_recepcion');
            $('#table_listado_recepcion_filter label input').addClass('input-yura_default')
        });
    }

    function exportar_recepcion() {
        $.LoadingOverlay('show');
        window.open('{{ url('recepcion/exportar_recepcion') }}?planta=' + $("#filtro_planta").val() +
            '&fecha=' + $("#filtro_fecha").val() +
            '&longitud=' + $('#filtro_longitud').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function update_desglose(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#edit_planta_' + id).val(),
            variedad: $('#edit_variedad_' + id).val(),
            longitud: $('#edit_longitud_' + id).val(),
            mallas: $('#edit_cantidad_mallas_' + id).val(),
            tallos_x_malla: $('#edit_tallos_x_malla_' + id).val(),
            id: id,
        }
        if (datos['mallas'] > 0 && datos['tallos_x_malla'] > 0 && datos['mallas'] != '') {
            post_jquery_m('{{ url('recepcion/update_desglose') }}', datos, function() {
                buscar_listado_recepcion();
            });
        } else {
            alerta('<div class="text-center alert alert-warning">Faltan datos necesarios</div>')
        }
    }

    function delete_desglose(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        modal_quest('modal_quest_delete_desglose', '<div class="alert alert-info text-center">' +
            '¿Está seguro de <strong>ELIMINAR</strong> la cosecha?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de alerta', true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                post_jquery_m('{{ url('recepcion/delete_desglose') }}', datos, function() {
                    cerrar_modals();
                    buscar_listado_recepcion();
                });
            });
    }

    function buscarLongitudesByPlanta(p, input_longitud, elemento_load, li_adicional = '', select = 1) {
        if (p != '') {
            datos = {
                planta: p,
                select: select
            };
            get_jquery('{{ url('distribucion_semana/seleccionar_planta') }}', datos, function(retorno) {
                $('#' + input_longitud).html(li_adicional);
                $('#' + input_longitud).append(retorno);
            }, elemento_load);

        } else
            $('#' + input_longitud).html(li_adicional);
    }
</script>
