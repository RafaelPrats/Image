<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            FECHA
        </th>
        <th class="text-center th_yura_green">
            RECETA
        </th>
        <th class="text-center th_yura_green">
            PRESENTACION
        </th>
        <th class="text-center th_yura_green">
            FLOR
        </th>
        <th class="text-center th_yura_green" colspan="2">
            TALLOS x RAMO
        </th>
        <th class="text-center th_yura_green">
            TOTAL BUNCHES
        </th>
        <th class="text-center th_yura_green" colspan="2">
            TOTAL TALLOS
        </th>
    </tr>
    @foreach ($listado as $pos => $item)
        @foreach ($item['item_recetas'] as $pos_rec => $rec)
            <tr onmouseover="$('.tr_{{ $pos }}').addClass('bg-yura_dark')"
                onmouseleave="$('.tr_{{ $pos }}').removeClass('bg-yura_dark')" class="tr_{{ $pos }}"
                style="background-color: {{ $pos % 2 == 0 ? '#e6f7ff' : '' }}">
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['pedido']->fecha_pedido }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_esp']->variedad->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_esp']->empaque_p->nombre }}
                    </th>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    <b>{{ $rec->planta->nombre }}</b>: {{ $rec->variedad()->nombre }}
                    <sup><b>{{ $rec->longitud_ramo }}cm</b></sup>
                </td>
                @if ($pos_rec == 0)
                    <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($item['item_recetas']) }}">
                        {{ $item['det_esp']->tallos_x_ramos }}
                    </th>
                @endif
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $rec->tallos / ($item['ramos_x_caja'] * $item['det_ped']->cantidad) }}
                </td>
                @if ($pos_rec == 0)
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
            </tr>
        @endforeach
    @endforeach
</table>
