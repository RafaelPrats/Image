<script>
    select_planta($('#filtro_planta').val(), 'filtro_variedad', 'div_cargar_variedades', '<option value="T" selected>Todos los tipos</option>');

    function buscar_listado() {
        $.LoadingOverlay('show');
        datos = {
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            uso: $('#filtro_uso').val(),
            reporte: $('#filtro_reporte').val(),
        };
        $.get('{{url('proyeccion_aplicaciones/buscar_listado')}}', datos, function (retorno) {
            $('#div_content_proyecciones').html(retorno);
            /*estructura_tabla('table_aplicaciones');
            $('#table_aplicaciones_filter>label>input').addClass('input-yura_default');*/
            //$('.dataTables_empty').html('No se han encontrado resultados');
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }
</script>