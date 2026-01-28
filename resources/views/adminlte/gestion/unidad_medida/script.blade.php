<script>
    buscar_listado();

    function buscar_listado() {
        $('#div_content').LoadingOverlay('show');
        datos = {};
        $.get('{{url('unidad_medida/buscar_listado')}}', datos, function (retorno) {
            $('#div_content').html(retorno);
            /*estructura_tabla('table_unidades');
            $('#table_unidades_filter').addClass('hidden');*/
        }).always(function () {
            $('#div_content').LoadingOverlay('hide');
        });
    }

    function store_unidad() {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#new_nombre').val(),
            siglas: $('#new_siglas').val(),
            tipo: $('#new_tipo').val(),
            uso: $('#new_uso').val(),
        };
        $('#tr_new_app').LoadingOverlay('show');
        $.post('{{url('unidad_medida/store_unidad')}}', datos, function (retorno) {
            if (retorno.success) {
                buscar_listado();
                $('#new_nombre').val('');
                $('#new_siglas').val('');
            } else {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_new_app').LoadingOverlay('hide');
        })
    }

    function update_unidad(id_unidad) {
        datos = {
            _token: '{{csrf_token()}}',
            id_unidad: id_unidad,
            siglas: $('#edit_siglas_' + id_unidad).val(),
            nombre: $('#edit_nombre_' + id_unidad).val(),
            tipo: $('#edit_tipo_' + id_unidad).val(),
            uso: $('#edit_uso_' + id_unidad).val(),
        };
        $('#tr_unidad_' + id_unidad).LoadingOverlay('show');
        $.post('{{url('unidad_medida/update_unidad')}}', datos, function (retorno) {
            if (!retorno.success) {
                alerta(retorno.mensaje);
            }
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#tr_unidad_' + id_unidad).LoadingOverlay('hide');
        })
    }
</script>