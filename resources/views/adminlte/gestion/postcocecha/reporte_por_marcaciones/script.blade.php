<script>
    function listar_filtros() {
        $.LoadingOverlay('show');
        datos = {
            planta: $('#filtro_planta').val(),
        };
        $.get('{{ url('reporte_por_marcaciones/listar_filtros') }}', datos, function(retorno) {
            $('#div_listado_filtros').html(retorno);
        }).always(function() {
            $.LoadingOverlay('hide');
        });
    }
</script>
