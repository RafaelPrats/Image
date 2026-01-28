<script>
    $("select#cliente").select2();
    listar_reporte();

    function listar_reporte() {
        datos = {
            desde_diario: $('#desde_diario').val(),
            desde_semanal: parseInt($('#desde_semanal').val()),
            hasta_diario: $('#hasta_diario').val(),
            hasta_semanal: parseInt($('#hasta_semanal').val()),
            rango: $('#rango').val(),
        };
        if (datos['desde_diario'] <= datos['hasta_diario'] && datos['desde_semanal'] <= datos['hasta_semanal']) {
            get_jquery('{{ url('resumen_cosecha/listar_reporte') }}', datos, function(retorno) {
                $('#div_contentido_tablas').html(retorno);
            });
        }
    }

    /*function exportar_tabla() {
        datos = {
            desde: parseInt($('#desde').val()),
            hasta: parseInt($('#hasta').val()),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            variedad: $('#variedad').val(),
            criterio: $('#criterio').val(),
            rango: $('#rango').val(),
            acumulado: $('#acumulado').prop('checked'),
        };
        if (datos['desde'] <= datos['hasta']) {
            $.LoadingOverlay('show');
            window.open('{{ url('resumen_cosecha/exportar_tabla') }}' + '?desde=' + datos['desde'] + '&hasta=' + datos[
                    'hasta'] +
                '&annos=' + datos['annos'] + '&cliente=' + datos['cliente'] + '&variedad=' + datos['variedad'] +
                '&criterio=' + datos['criterio'] + '&rango=' + datos['rango'] + '&acumulado=' + datos['acumulado'],
                '_blank');
            $.LoadingOverlay('hide');
        }
    }*/
</script>
