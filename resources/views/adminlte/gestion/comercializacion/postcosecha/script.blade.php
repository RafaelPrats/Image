<script>
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            dias: $('#filtro_dias').val(),
            presentacion: $('#filtro_presentacion').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('postcosecha/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }

    function buscar_presentaciones() {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_planta').val(),
            dias: $('#filtro_dias').val(),
        }
        if (datos['planta'] != '') {
            $('#filtro_presentacion').LoadingOverlay('show');
            $.post('{{ url('postcosecha/buscar_presentaciones') }}', datos, function(retorno) {
                $('#filtro_presentacion').html(retorno.options);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function() {
                $('#filtro_presentacion').LoadingOverlay('hide');
            });
        }
    }
</script>
