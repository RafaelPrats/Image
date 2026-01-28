<legend style="font-size: 1.2em; margin-bottom: 2px" class="text-center">
    Seleccione la(s) fecha(s) a agregar a la orden: "<b>{{ $pedido->orden_fija }}</b>"
</legend>
<input type="hidden" id="copiar_fecha_0" value="{{ opDiasFecha('+', 0, $pedido->fecha_pedido) }}">

<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_copiar_pedido">
    <tr>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_fecha()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr id="tr_copiar_fecha_1">
        <td style="border-color: #9d9d9d">
            <input type="date" class="text-center" style="width: 100%" id="copiar_fecha_1"
                value="{{ opDiasFecha('+', 7, $pedido->fecha_pedido) }}">
        </td>
        <td style="border-color: #9d9d9d" class="text-center">
            <button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fecha(1)">
                <i class="fa fa-fw fa-trash"></i>
            </button>
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_agregar_nueva_fecha('{{ $pedido->id_pedido }}')">
        <i class="fa fa-fw fa-copy"></i> GRABAR
    </button>
</div>

<script>
    var cantidad_fechas = 1;

    function add_fecha() {
        cantidad_fechas++;
        fecha = sum_dias_a_fecha((cantidad_fechas * 7) + 1, $('#copiar_fecha_0').val())
        $('#table_copiar_pedido').append('<tr id="tr_copiar_fecha_' + cantidad_fechas + '">' +
            '<td style="border-color: #9d9d9d">' +
            '<input type="date" class="text-center" style="width: 100%" id="copiar_fecha_' + cantidad_fechas +
            '" value="' + fecha + '">' +
            '</td>' +
            '<td style="border-color: #9d9d9d" class="text-center">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fecha(' + cantidad_fechas +
            ')">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function eliminar_fecha(p) {
        $('#tr_copiar_fecha_' + p).remove();
    }

    function store_agregar_nueva_fecha(id_ped) {
        data = [];
        for (var i = 1; i <= cantidad_fechas; i++) {
            if ($('#copiar_fecha_' + i).length) {
                if ($('#copiar_fecha_' + i).val() !== '')
                    data.push(
                        $('#copiar_fecha_' + i).val()
                    );
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            id_ped: id_ped
        }
        post_jquery_m('{{ url('historial_ordenes_fija/store_agregar_nueva_fecha') }}', datos, function() {
            cerrar_modals();
        })
    }
</script>
