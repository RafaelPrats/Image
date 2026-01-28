<div style="overflow-y: scroll; overflow-x: scroll; height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green">
                CODIGO
            </th>
            <th class="text-center th_yura_green" style="width: 30%">
                NOMBRE
            </th>
            <th class="text-center th_yura_green">
                STOCK MINIMO
            </th>
            <th class="text-center th_yura_green">
                DISPONIBLES
            </th>
            <th class="text-center th_yura_green">
                CONVERSION
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                TOTAL <br> UNIDADES
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                <button type="button" class="btn btn-xs btn-yura_default"
                    onclick="$('#tr_new_producto').removeClass('hidden')">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_new_producto" class="hidden">
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="codigo_new" placeholder="Codigo JIRE"
                    required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="text" style="width: 100%" class="text-center bg-yura_dark" id="nombre_new" placeholder="NOMBRE"
                    required>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%" class="text-center bg-yura_dark" required min="0"
                    id="stock_minimo_new" placeholder="0">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%" class="text-center bg-yura_dark" required min="0"
                    id="disponibles_new" placeholder="0">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" style="width: 100%" class="text-center bg-yura_dark" required min="0"
                    id="conversion_new" placeholder="0">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">

            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="store_producto()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr id="tr_producto_{{ $item->id_producto }}" class="{{ $item->estado == 0 ? 'error' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" class="text-center" id="codigo_{{ $item->id_producto }}"
                        value="{{ $item->codigo_jire }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="text" style="width: 100%" class="text-center" id="nombre_{{ $item->id_producto }}"
                        value="{{ $item->nombre }}" required>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="stock_minimo_{{ $item->id_producto }}" value="{{ $item->stock_minimo }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="disponibles_{{ $item->id_producto }}" value="{{ $item->disponibles }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0"
                        id="conversion_{{ $item->id_producto }}" value="{{ $item->conversion }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" required min="0" readonly
                        id="total_unidades_{{ $item->id_producto }}"
                        value="{{ $item->disponibles * $item->conversion }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_producto('{{ $item->id_producto }}')">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="cambiar_estado_producto('{{ $item->id_producto }}', '{{ $item->estado }}')">
                            <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'lock' : 'unlock' }}"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</div>

<script>
    function store_producto() {
        datos = {
            _token: '{{ csrf_token() }}',
            codigo_jire: $('#codigo_new').val(),
            nombre: $('#nombre_new').val(),
            stock_minimo: $('#stock_minimo_new').val(),
            disponibles: $('#disponibles_new').val(),
            conversion: $('#conversion_new').val(),
        }
        post_jquery_m('{{ url('bodega_productos/store_producto') }}', datos, function() {
            listar_reporte();
        }, 'tr_new_producto');
    }

    function update_producto(id) {
        datos = {
            _token: '{{ csrf_token() }}',
            codigo_jire: $('#codigo_' + id).val(),
            nombre: $('#nombre_' + id).val(),
            stock_minimo: $('#stock_minimo_' + id).val(),
            disponibles: $('#disponibles_' + id).val(),
            conversion: $('#conversion_' + id).val(),
            id: id,
        }
        post_jquery_m('{{ url('bodega_productos/update_producto') }}', datos, function() {
            //listar_reporte();
        }, 'tr_producto_' + id);
    }

    function cambiar_estado_producto(p, estado) {
        mensaje = {
            title: estado == 1 ? '<i class="fa fa-fw fa-trash"></i> Desactivar producto' :
                '<i class="fa fa-fw fa-unlock"></i> Activar producto',
            mensaje: estado == 1 ?
                '<div class="alert alert-danger text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de desactivar este producto?</div>' :
                '<div class="alert alert-info text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de activar este producto?</div>',
        };
        modal_quest('modal_delete_producto', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '25%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: p,
                };
                post_jquery_m('{{ url('bodega_productos/cambiar_estado_producto') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            });
    }
</script>
