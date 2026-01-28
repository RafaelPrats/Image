<table class="table" style="width: 100%">
    <tr>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-calendar"></i> Fecha
                </span>
                <input type="date" max="{{ hoy() }}" id="new_fecha" required
                    class="text-center input-yura_default form-control" value="{{ hoy() }}">
            </div>
        </td>
        <td colspan="2">
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-leaf"></i> Variedad
                </span>
                <select name="new_planta" id="new_planta" class="input-yura_default form-control" style="width: 100%"
                    onchange="select_planta($(this).val(), 'new_variedad', 'new_variedad', '<option value=T>Todos</option>')">
                    <option value="">Seleccione</option>
                    @foreach ($plantas as $p)
                        <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-leaf"></i> Color
                </span>
                <select name="new_variedad" id="new_variedad" class="input-yura_default form-control"
                    style="width: 100%" required>
                    <option value="">Seleccione</option>
                </select>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-gift"></i> Presentación
                </span>
                <select name="new_presentacion" id="new_presentacion" class="input-yura_default form-control"
                    style="width: 100%">
                    @foreach ($presentaciones as $c)
                        <option value="{{ $c->id_empaque }}">{{ $c->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-tree"></i> Tallos x Ramo
                </span>
                <input type="number" min="0" id="new_tallos_x_ramo" required
                    class="text-center input-yura_default form-control">
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-tree"></i> Longitud <sup>cm</sup>
                </span>
                <input type="number" min="0" id="new_longitud" required
                    class="text-center input-yura_default form-control">
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    <i class="fa fa-fw fa-plus"></i> Cantidad
                </span>
                <input type="number" min="0" id="new_cantidad" required
                    class="text-center input-yura_default form-control">
            </div>
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_new_inventario()">
        <i class="fa fa-fw fa-save"></i> Grabar
    </button>
</div>

<script>
    function store_new_inventario() {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#new_fecha').val(),
            variedad: $('#new_variedad').val(),
            presentacion: $('#new_presentacion').val(),
            tallos_x_ramo: $('#new_tallos_x_ramo').val(),
            longitud: $('#new_longitud').val(),
            cantidad: $('#new_cantidad').val(),
        }
        post_jquery_m('{{ url('cuarto_frio/store_new_inventario') }}', datos, function() {
            cerrar_modals();
            add_new_inventarios();
            listar_inventarios();
        })
    }
</script>
