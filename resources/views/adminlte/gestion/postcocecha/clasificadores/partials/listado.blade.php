<div style="overflow-y: scroll; overflow-x: scroll; height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_clasificador').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_clasificador" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="nombre_new" placeholder="NOMBRE"
                    required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_clasificador()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_clasificador_{{ $item->id_clasificador }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" class="text-center" id="nombre_{{ $item->id_clasificador }}"
                        value="{{ $item->nombre }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_clasificador('{{ $item->id_clasificador }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_clasificador('{{ $item->id_clasificador }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_clasificador() {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_new').val(),
        }
        post_jquery_m('{{ url('clasificadores/store_clasificador') }}', datos, function() {
            listar_reporte();
        }, 'tr_new_clasificador');
    }

    function update_clasificador(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_' + id).val(),
            id: id,
        }
        post_jquery_m('{{ url('clasificadores/update_clasificador') }}', datos, function() {
            //listar_reporte();
        }, 'tr_clasificador_' + id);
    }

    function cambiar_estado_clasificador(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar clasificador' :
                '<i class="fa fa-fw fa-unlock"></i> Activar clasificador',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este clasificador?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este clasificador?</div>',
        };
        modal_quest('modal_delete_clasificador', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('clasificadores/cambiar_estado_clasificador') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
