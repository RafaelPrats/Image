@if (!in_array($agencia->id_agencia_carga, $mis_agencias))
    <legend class="text-center" style="width: 100%; font-size: 1.3em; margin-bottom: 5px">
        Ingrese el sello de la agencia <b>{{ $agencia->nombre }}</b> de carga para cambiar este pedido al despacho
    </legend>
    <input type="text" style="width: 100%" class="text-center form-control" id="cambiar_sello" placeholder="SELLO">
    <div class="text-center" style="margin-top: 5px">
        <button type="button" class="btn btn-yura_primary"
            onclick="cambiar_a_despacho_confirmar('{{ $proyecto->id_proyecto }}', '{{ $hoja_ruta->id_hoja_ruta }}')">
            <i class="fa fa-fw fa-save"></i> Confirmar
        </button>
    </div>
@else
    <div class="alert alert-info text-center" style="font-size: 1.3em">
        Agregando automáticamente al despacho por ser una de sus agencias de carga...
    </div>
    <script>
        setTimeout(function() {
            cambiar_a_despacho_confirmar('{{ $proyecto->id_proyecto }}', '{{ $hoja_ruta->id_hoja_ruta }}');
        }, 500);
    </script>
@endif
<input type="hidden" id="agencia_selected" value="{{ $agencia->id_agencia_carga }}">

<script>
    setTimeout(function() {
        $('#cambiar_sello').focus();
    }, 500);

    function cambiar_a_despacho_confirmar(id_proy, id_hoja_ruta) {
        sello = $('#cambiar_sello').val();
        datos = {
            _token: '{{ csrf_token() }}',
            id_proy: id_proy,
            id_hoja_ruta: id_hoja_ruta,
            sello: sello,
            id_agencia: $('#agencia_selected').val()
        }
        post_jquery_m('{{ url('hoja_ruta/cambiar_a_despacho_confirmar') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        });
    }
</script>
