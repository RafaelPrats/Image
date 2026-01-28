<script>
    $('#filtro_cliente, #filtro_agencia').select2();
    $('.select2-selection').css('height', '34px');
    listar_reporte();

    function listar_reporte() {
        datos = {
            cliente: $('#filtro_cliente').val(),
            agencia: $('#filtro_agencia').val(),
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        get_jquery('{{ url('ingreso_daes/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
