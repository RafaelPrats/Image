<script>
    function listar_reporte() {
        datos = {
            cliente: $('#filtro_cliente').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            tipo_caja: $('#filtro_tipo_caja').val(),
            longitud: $('#filtro_longitud').val(),
            peso: $('#filtro_peso').val(),
        };
        if (datos['cliente'] != '')
            get_jquery('{{ url('especificaciones/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }
    
    function add_especificaciones() {
        datos = {
            cliente: $('#filtro_cliente').val(),
        };
        if (datos['cliente'] != '')
            get_jquery('{{ url('especificaciones/add_especificaciones') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }
</script>
