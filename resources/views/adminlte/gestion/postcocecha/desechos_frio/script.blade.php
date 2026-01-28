<script>
    listar_reporte();

    function listar_reporte() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{ url('desechos_frio/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('desechos_frio/exportar_reporte') }}?desde=' + $('#filtro_desde').val() +
            '&hasta=' + $('#filtro_hasta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
