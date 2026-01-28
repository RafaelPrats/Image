<script>
    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            planta: $('#filtro_planta').val(),
            longitud: $('#filtro_longitud').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('distribucion_mixtos/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }

    function seleccionar_planta(p, input_longitud, elemento_load, li_adicional = '', select = 1) {
        if (p != '') {
            datos = {
                planta: p,
                select: select
            };
            get_jquery('{{ url('distribucion_semana/seleccionar_planta') }}', datos, function(retorno) {
                $('#' + input_longitud).html(li_adicional);
                $('#' + input_longitud).append(retorno);
            }, elemento_load);

        } else
            $('#' + input_longitud).html(li_adicional);
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('distribucion_mixtos/exportar_reporte') }}?fecha=' + $('#filtro_fecha').val() +
            '&planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
