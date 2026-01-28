<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px;">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
        id="table_productos_seleccionados">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                NOMBRE
            </th>
            <th class="text-center th_yura_green">
                UNIDADES
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
        @php
            $pos = 0;
        @endphp
        @foreach ($empaque->productosByPlanta($planta) as $pos => $p)
            <tr id="tr_producto_seleccionado_{{ $pos + 1 }}">
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $p->producto->nombre }}
                    <input type="hidden" class="cant_producto_seleccionado" value="{{ $pos + 1 }}">
                    <input type="hidden" id="id_producto_seleccionado_{{ $pos + 1 }}"
                        value="{{ $p->id_producto }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="number" class="text-center" style="width: 100%"
                        id="cantidad_producto_seleccionado_{{ $pos + 1 }}" value="{{ $p->unidades }}">
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                        onclick="quitar_producto_seleccionado('{{ $pos + 1 }}')">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<script>
    cant_producto_seleccionado = {{ $pos + 1 }};
</script>
