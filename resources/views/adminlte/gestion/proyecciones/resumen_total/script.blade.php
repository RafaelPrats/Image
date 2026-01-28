<script>
    listar_proyecciones_resumen_total();

    function listar_proyecciones_resumen_total() {
        data = {
            desde: $("#filtro_predeterminado_desde").val(),
            hasta: $("#filtro_predeterminado_hasta").val(),
        };
        get_jquery('{{url('proy_resumen_total/listar_resumen_total')}}', data, function (retorno) {
            $('#listado_proyecciones_resumen_total').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{url('proy_resumen_total/exportar_reporte')}}?desde=' + $('#filtro_predeterminado_desde').val() +
            '&hasta=' + $('#filtro_predeterminado_hasta').val()
            , '_blank');
        $.LoadingOverlay('hide');
    }
</script>
