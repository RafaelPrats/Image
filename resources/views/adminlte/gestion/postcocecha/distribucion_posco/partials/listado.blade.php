<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green">
                Variedad
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Color
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Presentacion
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Tallos x Ramo
            </th>
            <th class="padding_lateral_5 th_yura_green">
                Longitud
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                Clasificador
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                Marcacion
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                Cantidad
            </th>
            <th class="text-center bg-yura_dark hidden" style="width: 60px">
                Opciones
            </th>
        </tr>
        @php
            $total_ramos = 0;
        @endphp
        @foreach ($listado as $pos_i => $item)
            @php
                $color = getColorByNombre($item['item']->var_nombre);
                $bg_color = $color != '' ? $color->fondo : '';
                $text_color = $color != '' ? $color->texto : '';
            @endphp
            @foreach ($item['valores'] as $pos_v => $val)
                @php
                    $total_ramos += $val->cantidad;
                @endphp
                <tr onmouseover="$('.tr_combinacion_{{ $pos_i }}').addClass('bg-yura_dark')"
                    onmouseleave="$('.tr_combinacion_{{ $pos_i }}').removeClass('bg-yura_dark')">
                    @if ($pos_v == 0)
                        <th class="padding_lateral_5"
                            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}"
                            rowspan="{{ count($item['valores']) }}">
                            {{ $item['item']->pta_nombre }}
                        </th>
                        <th class="padding_lateral_5"
                            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}"
                            rowspan="{{ count($item['valores']) }}">
                            {{ $item['item']->var_nombre }}
                        </th>
                        <th class="padding_lateral_5"
                            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}"
                            rowspan="{{ count($item['valores']) }}">
                            {{ $item['item']->emp_nombre }}
                        </th>
                        <th class="padding_lateral_5"
                            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}"
                            rowspan="{{ count($item['valores']) }}">
                            {{ $item['item']->tallos_x_ramo }}
                        </th>
                        <th class="padding_lateral_5"
                            style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}"
                            rowspan="{{ count($item['valores']) }}">
                            {{ $item['item']->longitud }}cm
                        </th>
                    @endif
                    <th class="padding_lateral_5"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $val->clasificador->nombre }}
                    </th>
                    <th class="padding_lateral_5"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $val->valor_marcacion }}
                    </th>
                    <th class="padding_lateral_5"
                        style="border-color: {{ $text_color }}; background-color: {{ $bg_color }}; color: {{ $text_color }}">
                        {{ $val->cantidad }}
                    </th>
                    <th class="text-center hidden" style="border-color: #9d9d9d">
                        <div class="btn-groupo">
                            <button type="button" class="btn btn-xs btn-yura_warning">
                                <i class="fa fa-fw fa-save"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 text-right th_yura_green" colspan="7">
                TOTALES
            </th>
            <th class="padding_lateral_5 th_yura_green">
                {{ $total_ramos }}
            </th>
        </tr>
    </table>
</div>
