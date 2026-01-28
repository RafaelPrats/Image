<script>
    function listar_reporte() {
        datos = {
            planta: $('#filtro_planta').val(),
            variedad: $('#filtro_variedad').val(),
            presentacion: $('#filtro_presentacion').val(),
        };
        get_jquery('{{ url('inventario_cuarto_frio/listar_reporte') }}', datos, function(retorno) {
            $('#div_listado').html(retorno);
        });
    }

    function buscar_presentaciones() {
        datos = {
            _token: '{{ csrf_token() }}',
            planta: $('#filtro_planta').val(),
            dias: $('#filtro_dias').val(),
        }
        if (datos['planta'] != '') {
            $('#filtro_presentacion').LoadingOverlay('show');
            $.post('{{ url('postcosecha/buscar_presentaciones') }}', datos, function(retorno) {
                $('#filtro_presentacion').html(retorno.options);
            }, 'json').fail(function(retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function() {
                $('#filtro_presentacion').LoadingOverlay('hide');
            });
        }
    }

    function agregar_inventario() {
        get_jquery('{{ url('inventario_cuarto_frio/agregar_inventario') }}', datos, function(retorno) {
            modal_view('moda-view_agregar_inventario', retorno,
                '<i class="fa fa-fw fa-plus"></i> Agregar Inventario', true, false,
                '{{ isPC() ? '95%' : '' }}');
        });
    }

    function descargar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('inventario_cuarto_frio/descargar_reporte') }}?planta=' + $('#filtro_planta').val() +
            '&variedad=' + $('#filtro_variedad').val() +
            '&presentacion=' + $('#filtro_presentacion').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
