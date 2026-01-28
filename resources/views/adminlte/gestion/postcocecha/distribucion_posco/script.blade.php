<script>
    listar_reporte();

    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            fecha: $('#filtro_fecha').val(),
            clasificador: $('#filtro_clasificador').val(),
        };
        get_jquery('{{ url('distribucion_posco/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        }, 'div_listado');
    }
</script>
