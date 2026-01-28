<script>
    filtrar_ciclos();

    function filtrar_ciclos() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            fecha: $('#filtro_predeterminado_fecha').val(),
            tipo: $('#filtro_predeterminado_tipo').val(),
            ps: $('#filtro_predeterminado_ps').val(),
            estado: $('#filtro_predeterminado_estado').val(),
        };
        get_jquery('{{url('fenograma_costos/filtrar_ciclos')}}', datos, function (retorno) {
            $('#div_listado_ciclos').html(retorno);
            estructura_tabla('table_fenograma_costos', false, false);
            $('#table_fenograma_costos_filter>label>input').addClass('input-yura_default');
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{url('fenograma_costos/exportar_reporte')}}?var=' + $('#filtro_predeterminado_variedad').val() +
            '&fecha=' + $('#filtro_predeterminado_fecha').val() +
            '&tipo=' + $('#filtro_predeterminado_tipo').val() +
            '&variedad=' + $('#filtro_predeterminado_variedad').val() +
            '&ps=' + $('#filtro_predeterminado_ps').val() +
            '&estado=' + $('#filtro_predeterminado_estado').val()
            , '_blank');
        $.LoadingOverlay('hide');
    }
</script>