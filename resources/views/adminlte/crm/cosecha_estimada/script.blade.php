<script>
    //buscar_cosecha_estimada();

    function buscar_cosecha_estimada() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            cambios: $('#filtro_predeterminado_cambios').val(),
        };
        get_jquery('{{ url('cosecha_estimada/buscar_cosecha_estimada') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function buscar_cosecha_estimada_new() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            cambios: $('#filtro_predeterminado_cambios').val(),
        };
        get_jquery('{{ url('cosecha_estimada/buscar_cosecha_estimada_new') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('cosecha_estimada/exportar_reporte') }}?desde=' + $('#filtro_predeterminado_desde').val() +
            '&cambios=' + $('#filtro_predeterminado_cambios').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val() +
            '&planta=' + $('#filtro_predeterminado_planta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
