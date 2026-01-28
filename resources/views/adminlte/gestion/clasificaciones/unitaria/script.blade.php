<script>

    buscar_clasificaciones_unitarias();

    function buscar_clasificaciones_unitarias() {
        $.LoadingOverlay('show');
        datos = {
            busqueda: $('#busqueda_cu').val().trim(),
        };
        $.get('{{url('clientes/buscar')}}', datos, function (retorno) {
            $('#div_listado_clasificaciones_unitarias').html(retorno);
            estructura_tabla('table_content_clientes');
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }

    $(document).on("click", "#pagination_listado_clientes .pagination li a", function (e) {
        $.LoadingOverlay("show");
        //para que la pagina se cargen los elementos
        e.preventDefault();
        var url = $(this).attr("href");
        url = url.replace('?', '?busqueda=' + $('#busqueda_cu').val() + '&');
        $('#div_listado_clasificaciones_unitarias').html($('#table_clientes').html());
        $.get(url, function (resul) {
            $('#div_listado_clasificaciones_unitarias').html(resul);
            estructura_tabla('table_content_clientes');
        }).always(function () {
            $.LoadingOverlay("hide");
        });
    });

</script>
