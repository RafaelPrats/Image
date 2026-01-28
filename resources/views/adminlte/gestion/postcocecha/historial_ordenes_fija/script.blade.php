<script>
    vista_actual = 'historial_ordenes_fija';
    $('#filtro_cliente').select2();
    $('.select2-selection').css('border-radius', '0');
    $('.select2-selection').css('height', '34px');

    //listar_reporte();

    function listar_reporte() {
        datos = {
            fecha: $('#filtro_fecha').val(),
            cliente: $('#filtro_cliente').val(),
        };
        if (datos['planta'] != '')
            get_jquery('{{ url('historial_ordenes_fija/listar_reporte') }}', datos, function(retorno) {
                $('#div_listado').html(retorno);
            });
    }

    function ver_toda_orden(orden_fija) {
        datos = {
            orden_fija: orden_fija,
            fecha: $('#filtro_fecha').val(),
        }
        get_jquery('{{ url('historial_ordenes_fija/ver_toda_orden') }}', datos, function(retorno) {
            modal_view('modal_ver_toda_orden', retorno,
                '<i class="fa fa-fw fa-calendar"></i> Historial de la Orden Fija #' + orden_fija,
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }
</script>
