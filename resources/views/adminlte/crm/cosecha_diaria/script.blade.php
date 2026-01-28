<script>
    $('#vista_actual').val('cosecha_diaria');
    listar_reporte();

    function listar_reporte() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
            planta: $('#filtro_predeterminado_planta').val(),
            tipo: $('#filtro_predeterminado_tipo').val(),
        };
        get_jquery('{{url('cosecha_diaria/listar_reporte')}}', datos, function (retorno) {
            $('#div_reporte').html(retorno);
        });
    }
</script>