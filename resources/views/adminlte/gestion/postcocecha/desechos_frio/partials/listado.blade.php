<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green columna_fija_left_0">
                VARIEDAD
            </th>
            <th class="text-center th_yura_green">
                COLOR
            </th>
            <th class="text-center th_yura_green">
                PRESENTACION
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                MEDIDA
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                TxR
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                RAMOS
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                T. TALLOS
            </th>
        </tr>
        @php
            $total_tallos = 0;
            $total_ramos = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            @php
                $variedad = $item->variedad;
                $total_tallos += $item->tallos_x_ramo * $item->cantidad;
                $total_ramos += $item->cantidad;
            @endphp
            <tr onmouseover="$(this).css('background-color','#ADD8E6')"
                onmouseleave="$(this).css('background-color','')">
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->planta->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->empaque_p->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->longitud_ramo }}cm
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->cantidad }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo * $item->cantidad }}
                </th>
            </tr>
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="5">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_ramos) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_tallos) }}
            </th>
        </tr>
    </table>
</div>

<style>
    .tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    .tr_fija_bottom_0 {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>
