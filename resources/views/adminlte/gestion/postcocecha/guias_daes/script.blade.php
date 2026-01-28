<script>
    vista_actual = 'ingreso_guia_daes';
    listado_guias_daes();

    $("#id_cliente, #id_agencia_carga").select2()

    function listado_guias_daes() {
        $.LoadingOverlay('show');
        datos = {
            desde: $('#desde').val(),
            hasta: $('#hasta').val(),
            id_cliente: $('#id_cliente').val(),
        };
        $.get('{{ url('ingreso_guia_daes/listado') }}', datos, function(retorno) {
            $('#div_listado_guias_daes').html(retorno);
        }).always(function() {
            $.LoadingOverlay('hide');
        })
    }

    function actualizar_envio_pedido() {

        let datos_envios = []

        $.each($("tr.tr_pedido"), function() {
            console.log($(this).find('input.check_guia_dae').is(':checked'))
            if ($(this).find('input.check_guia_dae').is(':checked')) {

                let packing = $(this).data('packing')
                let id_envio = $(this).data('id_envio')
                let dae = $(this).find('input#dae').val()
                let pais = $(this).find('select#codigo_pais').val()
                let guia_madre = $(this).find('input#guia_madre').val()
                let guia_hija = $(this).find('input#guia_hija').val()
                let consignatario = $(this).find('select#consignatario').val()
                let aerolinea = $(this).find('select#aerolinea option:selected').data('id_aerolinea')

                datos_envios.push({
                    packing,
                    id_envio,
                    dae,
                    pais,
                    guia_madre,
                    guia_hija,
                    consignatario,
                    aerolinea
                })

            }

        })

        let datos = {
            _token: '{{ csrf_token() }}',
            datos_envios
        }

        post_jquery('ingreso_guia_daes/actualiza_datos_envio', datos, function() {
            listado_guias_daes()
            cerrar_modals()
            $.LoadingOverlay('hide')
        })

    }

    function set_aerolinea(input) {

        if (input.value.length >= 3) {

            $(input).parent().parent().find('select#aerolinea option').removeAttr('selected')
            $(input).parent().parent().find("select#aerolinea option[value='" + input.value.slice(0, 3) + "']").attr(
                'selected', true)

        }

    }

    function filtrar_listado_guias_js(select, clase) {

        let value = $(select).find("option:selected").text().replace('&', '').trim().toLowerCase()

        if (value == 'todos') {
            $("table#table_content_etiqueta tr").css('display', 'table-row')
        } else {
            $("table#table_content_etiqueta tr").filter(function() {

                if (typeof $(this).find('td.' + clase).html() != 'undefined') {

                    $(this).toggle(
                        $(this).find('td.' + clase).html().replace('&', '').replace('amp;', '').trim()
                        .toLowerCase() == value &&
                        $(this).text().replace('&', '').replace('amp;', '').toLowerCase().indexOf(value) > -
                        1
                    )

                }

            })
        }

    }

    function check_filtro_guia_dae() {
        console.log($('.check_select_all_packing').is(':checked'))

        let check = $('.check_select_all_packing')

        $.each($('.check_guia_dae'), function() {

            if (check.is(':checked')) {
                console.log($(this).parent().parent().attr('style').split(' ')[1])
                if ($(this).parent().parent().attr('style').split(' ')[1] == 'table-row;')
                    $(this).prop('checked', true);

            } else {

                if ($(this).parent().parent().attr('style').split(' ')[1] == 'table-row;')
                    $(this).prop('checked', false);

            }

        })

    }
</script>
