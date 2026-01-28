<script>
    $('#filtro_presentacion').select2();
    $('.select2-selection').css('height', '34px')

    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            presentacion: $('#filtro_presentacion').val(),
            longitud: $('#filtro_longitud').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{ url('ingresos_frio/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_reporte', false);
            $('#table_reporte_filter>label>input').addClass('input-yura_default')
        });
    }

    function exportar_inventarios(tipo, negativas) {
        $.LoadingOverlay('show');
        window.open('{{ url('cuarto_frio/exportar_inventarios') }}?planta=' + $("#filtro_planta").val() +
            '&variedad=' + $("#filtro_variedad").val() +
            '&tipo=' + $("#filtro_tipo").val() +
            '&presentacion=' + $('#filtro_presentacion').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
