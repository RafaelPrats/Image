<script>
    function listar_formulario() {
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('proyeccion_semana/listar_formulario') }}', datos, function(retorno) {
                $('#div_formulario').html(retorno);
            });
    }

    function exportar_reporte() {
        if ($('#filtro_planta').val() != '') {
            $.LoadingOverlay('show');
            window.open('{{ url('proyeccion_semana/exportar_reporte') }}?desde=' + $('#filtro_desde').val() +
                '&hasta=' + $('#filtro_hasta').val() +
                '&planta=' + $('#filtro_planta').val() +
                '&variedad=' + $('#filtro_variedad').val(), '_blank');
            $.LoadingOverlay('hide');
        }
    }
</script>
