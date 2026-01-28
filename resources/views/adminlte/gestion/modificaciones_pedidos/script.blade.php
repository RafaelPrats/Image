<script>
    $("#filtro_cliente, #filtro_planta").select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            cliente: $('#filtro_cliente').val(),
            planta: $('#filtro_planta').val(),
        };
        get_jquery('{{ url('modificaciones_pedidos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
            estructura_tabla('table_modificaciones');
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('modificaciones_pedidos/exportar_reporte') }}?fecha=' + $('#filtro_fecha').val() +
            '&planta=' + $('#filtro_planta').val() +
            '&cliente=' + $('#filtro_cliente').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function cambiar_uso(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        };
        post_jquery_m('{{ url('modificaciones_pedidos/cambiar_uso') }}', datos, function(retorno) {
            listar_reporte();
        });
    }
</script>
