<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="padding_lateral_5 th_yura_green" style="width: 60px">
            No.
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Fecha despacho
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Responsable
        </th>
        <th class="padding_lateral_5 th_yura_green">
            Conductor
        </th>
        <th class="text-center th_yura_green" style="width: 110px">
            Opciones
        </th>
    </tr>
    @foreach ($despachos as $despacho)
        <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')">
            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                #{{ $despacho->id_hoja_ruta }}
            </td>
            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $despacho->fecha }}
            </td>
            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $despacho->responsable }}
            </td>
            <td class="padding_lateral_5" style="border-color: #9d9d9d">
                {{ $despacho->conductor->nombre }}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Despacho"
                        onclick="ver_hoja_ruta('{{ $despacho->id_hoja_ruta }}')">
                        <i class="fa fa-fw fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_default" title="Exportar PDF"
                        onclick="exportar_despacho('{{ $despacho->id_hoja_ruta }}')">
                        <i class="fa fa-fw fa-file-pdf-o"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar Despacho"
                        onclick="delete_despacho('{{ $despacho->id_hoja_ruta }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function ver_hoja_ruta(id_hoja_ruta) {
        datos = {
            id_hoja_ruta: id_hoja_ruta
        };
        get_jquery('{{ url('hoja_ruta/ver_hoja_ruta') }}', datos, function(retorno) {
            modal_view('moda-view_ver_hoja_ruta', retorno,
                '<i class="fa fa-fw fa-eye"></i> Despacho', true, false,
                '{{ isPC() ? '95%' : '' }}');
        });
    }

    function delete_despacho(id_hoja_ruta) {
        mensaje = {
            title: '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmacion',
            mensaje: '<div class="alert alert-warning text-center" style="font-size: 1.3em">¿Desea <b>ELIMINAR</b> este despacho?</div>',
        };
        modal_quest('modal_delete_despacho', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_hoja_ruta: id_hoja_ruta
                };
                post_jquery_m('{{ url('hoja_ruta/delete_despacho') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
