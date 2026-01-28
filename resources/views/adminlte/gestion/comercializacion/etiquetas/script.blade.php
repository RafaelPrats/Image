<script>
    $('#filtro_cliente, #filtro_agencia').select2();
    $('.select2-selection').css('height', '34px');
    listar_reporte();

    function listar_reporte() {
        datos = {
            cliente: $('#filtro_cliente').val(),
            agencia: $('#filtro_agencia').val(),
            fecha: $('#filtro_fecha').val(),
        };
        get_jquery('{{ url('etiquetas/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }
</script>
