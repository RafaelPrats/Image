<script>
    listar_resumen();

    function listar_resumen() {
        datos = {
            tipo_reporte: $('#tipo_reporte').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('resumen_plantas_madres/listar_resumen')}}', datos, function (retorno) {
            $('#listado_resumen').html(retorno);
            //estructura_tabla('table_contenedores', false, true);
        });
    }

    function exportar_disponibilidades() {
        $.LoadingOverlay('show');
        window.open('{{url('resumen_plantas_madres/exportar_resumen')}}?tipo_reporte=' + $('#tipo_reporte').val() +
            '&planta=' + $('#filtro_predeterminado_planta').val() +
            '&variedad=' + $('#filtro_predeterminado_variedad').val() +
            '&desde=' + $('#filtro_predeterminado_desde').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val()
            , '_blank');
        $.LoadingOverlay('hide');
    }
</script>