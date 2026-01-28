<script>
    select_planta($('#filtro_predeterminado_planta').val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value="T" selected>Todos los tipos</option>');

    function listar_disponibilidades() {
        datos = {
            variedad: $('#filtro_predeterminado_variedad').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('propag_disponibilidad/listar_disponibilidades')}}', datos, function (retorno) {
            $('#listado_disponibilidad').html(retorno);
            //estructura_tabla('table_contenedores', false, true);
        });
    }

    function exportar_disponibilidades() {
        $.LoadingOverlay('show');
        $.ajax({
            type: "POST",
            dataType: "html",
            contentType: "application/x-www-form-urlencoded",
            url: '{{url('propag_disponibilidad/exportar_disponibilidades')}}',
            data: {
                variedad: $('#filtro_predeterminado_variedad').val(),
                desde: $('#filtro_predeterminado_desde').val(),
                hasta: $('#filtro_predeterminado_hasta').val(),
                _token: '{{csrf_token()}}',
            },
            success: function (data) {
                var opResult = JSON.parse(data);
                var $a = $("<a>");
                $a.attr("href", opResult.data);
                $("body").append($a);
                $a.attr("download", "Reporte_Disponibilidad.xlsx");
                $a[0].click();
                $a.remove();
            }
        }).always(function () {
            $.LoadingOverlay('hide');
        });
    }
</script>