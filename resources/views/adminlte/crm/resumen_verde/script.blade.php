<script>
    buscar_resumen_verde();

    function buscar_resumen_verde() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            reporte: $('#filtro_reporte').val(),
        };
        get_jquery('{{url('resumen_verde/buscar_resumen_verde')}}', datos, function (retorno) {
            $('#div_listado_resumen_verde').html(retorno);
            $('.filtro_diarios').addClass('hidden');
        });
    }

    function listar_resumen_verde_semanal(desde, hasta) {
        datos = {
            desde: desde,
            hasta: hasta,
            reporte: $('#filtro_reporte').val(),
        };
        $('#filtro_diario_desde').val(desde);
        $('#filtro_diario_hasta').val(hasta);
        $('.filtro_diarios').removeClass('hidden');
        get_jquery('{{url('resumen_verde/listar_resumen_verde_semanal')}}', datos, function (retorno) {
            $('#div_listado_resumen_verde').html(retorno);
        });
    }
</script>