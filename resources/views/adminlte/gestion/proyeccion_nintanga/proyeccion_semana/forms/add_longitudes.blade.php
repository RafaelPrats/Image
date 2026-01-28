<table class="table-bordered" style="border: 2px solid #9d9d9d; width: 100%">
    <tr>
        <th class="text-center th_yura_green">
            NOMBRE
        </th>
        <th class="text-center th_yura_green">
            ORDEN
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" maxlength="250" class="text-center bg-yura_dark" style="width: 100%" id="new_nombre">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" maxlength="11" class="text-center bg-yura_dark" style="width: 100%" id="new_orden"
                min="0">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_longitud('{{ $planta }}')">
                <i class="fa fa-fw fa-save"></i>
            </button>
        </td>
    </tr>
    @foreach ($listado as $item)
        <tr id="tr_long_{{ $item->id_proy_longitudes }}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" maxlength="250" class="text-center" style="width: 100%"
                    value="{{ $item->nombre }}" id="edit_nombre_{{ $item->id_proy_longitudes }}">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" maxlength="11" class="text-center" style="width: 100%" value="{{ $item->orden }}"
                    id="edit_orden_{{ $item->id_proy_longitudes }}" min="0">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_warning"
                        onclick="update_longitud('{{ $item->id_proy_longitudes }}')">
                        <i class="fa fa-fw fa-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger"
                        onclick="delete_longitud('{{ $item->id_proy_longitudes }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function store_longitud(planta) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#new_nombre').val(),
            orden: $('#new_orden').val(),
            planta: planta
        }
        if (datos['nombre'] != '' && datos['orden'] != '')
            post_jquery_m('{{ url('proyeccion_semana/store_longitud') }}', datos, function() {
                add_longitudes(planta);
                cerrar_modals();
                listar_formulario();
            });
    }

    function update_longitud(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#edit_nombre_' + id).val(),
            orden: $('#edit_orden_' + id).val(),
            id: id,
        }
        if (datos['nombre'] != '' && datos['orden'] != '')
            post_jquery_m('{{ url('proyeccion_semana/update_longitud') }}', datos, function() {
                listar_formulario();
            });
    }

    function delete_longitud(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
        }
        post_jquery_m('{{ url('proyeccion_semana/delete_longitud') }}', datos, function() {
            $('#tr_long_' + id).remove();
            listar_formulario();
        });
    }
</script>
