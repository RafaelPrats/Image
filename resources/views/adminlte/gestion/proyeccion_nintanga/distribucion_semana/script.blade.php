<script>
    function listar_formulario() {
        datos = {
            semana: $('#filtro_semana').val(),
            planta: $('#filtro_planta').val(),
            longitud: $('#filtro_longitud').val(),
        };
        if (datos['planta'] != '' && datos['longitud'] != null)
            get_jquery('{{ url('distribucion_semana/listar_formulario') }}', datos, function(retorno) {
                $('#div_formulario').html(retorno);
            });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('distribucion_semana/exportar_reporte') }}?semana=' + $('#filtro_semana').val() +
            '&planta=' + $('#filtro_planta').val() +
            '&longitud=' + $('#filtro_longitud').val(), '_blank');
        $.LoadingOverlay('hide');
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
</script>
