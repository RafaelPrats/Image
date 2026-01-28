<script>
    function listar_formulario() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('ingresos_proy/listar_formulario') }}', datos, function(retorno) {
                $('#div_formulario').html(retorno);
            });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('ingresos_proy/exportar_reporte') }}?fecha=' + $('#filtro_fecha').val() +
            '&planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
