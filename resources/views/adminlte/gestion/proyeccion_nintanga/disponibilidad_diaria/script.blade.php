<script>
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
        };
        get_jquery('{{ url('disponibilidad_diaria/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('disponibilidad_diaria/exportar_reporte') }}?fecha=' + $('#filtro_fecha').val() +
            '&planta=' + $('#filtro_planta').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
