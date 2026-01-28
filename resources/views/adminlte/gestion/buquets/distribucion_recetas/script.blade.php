<script>
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            longitud: $('#filtro_longitud').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('distribucion_recetas/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }
</script>
