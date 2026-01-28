<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    @if ($pos != 'T')
        Inventario de <b>{{ $variedad->planta->nombre }} {{ $variedad->nombre }} {{ $empaque->nombre }}
            {{ $tallos_x_ramo }} tallos {{ $longitud }} cm </b>
    @else
        Inventario del <b>{{ $fecha }}</b>
    @endif
</legend>
<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Fecha de Registro
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                Fecha
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Ingresado
            </th>
            <th class="padding_lateral_5 th_yura_green" style="width: 60px">
                Disponibles
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Marcacion
            </th>
            <th class="text-center th_yura_green" style="width: 190px">
                Opciones
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr onmouseover="$(this).css('background-color', 'cyan')"
                onmouseleave="$(this).css('background-color', '')">
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha_registro }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->cantidad }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->disponibles }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    @if ($item->id_dato_exportacion != '')
                        {{ $item->dato_exportacion->nombre }}: {{ $item->valor_marcacion }}
                    @endif
                </th>
                <th style="border-color: #9d9d9d;">
                    <div class="input-group">
                        <input type="number" id="botar_inventario_{{ $item->id_cuarto_frio }}"
                            class="text-center form-control" style="width: 100%; background-color: #eeeeee">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_warning" title="Botar ramos"
                                onclick="botar_inventario('{{ $item->id_cuarto_frio }}')" style="border-radius: 0">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-yura_danger" title="Eliminar ramos"
                                onclick="delete_inventario('{{ $item->id_cuarto_frio }}')" style="border-radius: 0">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </span>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function botar_inventario(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-danger text-center" style="font-size: 16px">¿Está seguro de <b>BOTAR</b> los ramos?</div>',
        };
        modal_quest('modal_botar_inventario', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    cantidad: $('#botar_inventario_' + id).val(),
                    id: id,
                };
                post_jquery_m('{{ url('inventario_cuarto_frio/botar_inventario') }}', datos, function(
                    retorno) {
                    cerrar_modals();
                    modal_inventario('{{ $pos }}', '{{ $fecha }}');
                    listar_reporte();
                });
            });
    }

    function delete_inventario(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-danger text-center" style="font-size: 16px">¿Está seguro de <b>ELIMINAR</b> los ramos?</div>',
        };
        modal_quest('modal_delete_inventario', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    cantidad: $('#botar_inventario_' + id).val(),
                    id: id,
                };
                post_jquery_m('{{ url('inventario_cuarto_frio/delete_inventario') }}', datos, function(
                    retorno) {
                    cerrar_modals();
                    modal_inventario('{{ $pos }}', '{{ $fecha }}');
                    listar_reporte();
                });
            });
    }
</script>
