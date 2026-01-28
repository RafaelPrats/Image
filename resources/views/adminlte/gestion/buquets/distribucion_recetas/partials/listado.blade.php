<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            PACKING
        </th>
        <th class="text-center th_yura_green">
            CLIENTES
        </th>
        <th class="text-center th_yura_green">
            RECETA
        </th>
        <th class="text-center th_yura_green">
            FLOR
        </th>
        <th class="text-center th_yura_green">
            CAJAS
        </th>
        <th class="text-center th_yura_green" colspan="2">
            TALLOS x RAMO
        </th>
        <th class="text-center th_yura_green">
            BUNCHES
        </th>
        <th class="text-center th_yura_green">
            TOTAL BUNCHES
        </th>
        <th class="text-center th_yura_green" colspan="2">
            TOTAL TALLOS
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>
    @foreach ($listado as $pos => $item)
        @foreach ($item['item_recetas'] as $pos_rec => $rec)
            <tr onmouseover="$('.tr_{{ $pos }}').addClass('bg-yura_dark')"
                onmouseleave="$('.tr_{{ $pos }}').removeClass('bg-yura_dark')" class="tr_{{ $pos }}"
                style="background-color: {{ $pos % 2 == 0 ? '#e6f7ff' : '' }}">
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['pedido']->packing }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['pedido']->cliente->detalle()->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_esp']->variedad->nombre }}
                    </th>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    <b>{{ $rec->planta->nombre }}</b>: {{ $rec->variedad()->nombre }}
                    <sup><b>{{ $rec->longitud_ramo }}cm</b></sup>
                </td>
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_ped']->cantidad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_esp']->tallos_x_ramos }}
                    </th>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $rec->tallos / ($item['ramos_x_caja'] * $item['det_ped']->cantidad) }}
                </td>
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['ramos_x_caja'] }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['ramos_x_caja'] * $item['det_ped']->cantidad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['ramos_x_caja'] * $item['det_ped']->cantidad * $item['det_esp']->tallos_x_ramos }}
                    </th>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $rec->tallos }}
                </td>
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_dark"
                                onclick="admin_recetaByPedido('{{ $item['det_ped']->id_detalle_pedido }}', '{{ $item['det_esp']->id_detalle_especificacionempaque }}')"
                                title="Administrar Receta">
                                <i class="fa fa-fw fa-gift"></i>
                            </button>
                        </div>
                    </th>
                @endif
            </tr>
        @endforeach
    @endforeach
</table>

<script>
    function admin_recetaByPedido(det_ped, det_esp) {
        datos = {
            det_ped: det_ped,
            det_esp: det_esp,
        };
        get_jquery('{{ url('distribucion_recetas/admin_recetaByPedido') }}', datos, function(retorno) {
            modal_view('modal_admin_recetaByPedido', retorno,
                '<i class="fa fa-fw fa-plus"></i> Administrar receta',
                true, false, '{{ isPC() ? '75%' : '' }}');
        });
    }
</script>
