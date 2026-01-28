<legend style="font-size: 1.1em; margin-bottom: 5px" class="text-center">
    Procesos del dia <b>{{ convertDateToText(hoy()) }}</b>
</legend>
<div id="div_segundo_plano" style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
</div>

<style>
    .tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>
<script>
    listar_segundo_plano();
    var funcionInterval = setInterval(() => {
        listar_segundo_plano();
    }, 3500);

    function listar_segundo_plano() {
        $.get('{{ url('listar_segundo_plano') }}', {}, function(retorno) {
            $('#div_segundo_plano').html(retorno);
        });
    }

    $('#btn_cerrar_modal_view_ver_segundo_plano').on('click', function() {
        $('#div_segundo_plano').html('');
        clearInterval(funcionInterval);
    })

    function completar_pedido_proceso(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>COMPLETAR</b> el proceso?</div>',
        };
        modal_quest('modal_combinar_pedidos', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proceso_segundo_plano/completar_pedido_proceso') }}', datos, function() {
                    listar_segundo_plano();
                }, 'tr_pedido_proceso_' + id);
            });
    }

    function delete_pedido_proceso(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-info text-center" style="font-size: 16px">¿Está seguro de <b>ELIMINAR</b> el proceso?</div>',
        };
        modal_quest('modal_combinar_pedidos', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proceso_segundo_plano/delete_pedido_proceso') }}', datos, function() {
                    listar_segundo_plano();
                }, 'tr_pedido_proceso_' + id);
            });
    }
</script>
