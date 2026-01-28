<script>
    $('#filtro_cliente, #filtro_marcacion').select2();
    $('.select2-container').css('width', '100%');
    $('.select2-selection').css('height', '34px');

    function listar_reporte() {
        datos = {
            tipo: $('#filtro_tipo').val(),
            cliente: $('#filtro_cliente').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            marcacion: $('#filtro_marcacion').val(),
        };
        get_jquery('{{ url('proyectos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function add_proyecto() {
        datos = {};
        get_jquery('{{ url('proyectos/add_proyecto') }}', datos, function(retorno) {
            modal_view('modal-view_add_proyecto', retorno,
                '<i class="fa fa-fw fa-filter"></i> Nuevo Pedido', true, false, '95%');
        });
    }

    function generar_packings() {
        data = [];
        check_proyectos = $('.check_proyectos');
        for (i = 0; i < check_proyectos.length; i++) {
            id = check_proyectos[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proyecto'));
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('proyectos/generar_packings') }}', datos, function() {
                listar_reporte();
            })
        }
    }

    function combinar_pedidos() {
        data = [];
        check_proyectos = $('.check_proyectos');
        for (i = 0; i < check_proyectos.length; i++) {
            id = check_proyectos[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proyecto'));
            }
        }
        if (data.length > 1) {
            datos = {
                _token: '{{ csrf_token() }}',
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('proyectos/combinar_pedidos') }}', datos, function() {
                listar_reporte();
            })
        }
    }

    function descargar_packings_all() {
        data = [];
        check_proyectos = $('.check_proyectos');
        for (i = 0; i < check_proyectos.length; i++) {
            id = check_proyectos[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proyecto'));
            }
        }
        if (data.length > 0) {
            $.LoadingOverlay('show');
            window.open('{{ url('proyectos/descargar_packings_all') }}?data=' + JSON.stringify(data), '_blank');
            $.LoadingOverlay('hide');
        }
    }

    function descargar_flor_postco() {
        datos = {
            tipo: $('#filtro_tipo').val(),
            cliente: $('#filtro_cliente').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        $.LoadingOverlay('show');
        window.open('{{ url('proyectos/descargar_flor_postco') }}?datos=' + JSON.stringify(datos), '_blank');
        $.LoadingOverlay('hide');
    }

    function descargar_jire() {
        data = [];
        check_proyectos = $('.check_proyectos');
        for (i = 0; i < check_proyectos.length; i++) {
            id = check_proyectos[i].id;
            if ($('#' + id).prop('checked') == true) {
                data.push($('#' + id).data('id_proyecto'));
            }
        }
        if (data.length > 0) {
            $.LoadingOverlay('show');
            window.open('{{ url('proyectos/descargar_jire') }}?data=' + JSON.stringify(data), '_blank');
            $.LoadingOverlay('hide');
        }
    }
</script>
