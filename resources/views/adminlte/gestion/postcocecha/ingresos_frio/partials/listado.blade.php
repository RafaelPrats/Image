@if (count($listado) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px;">
        <table class="table-bordered table-striped" width="100%" style="border: 2px solid #9d9d9d;" id="table_reporte">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green">
                        Fecha y Hora
                    </th>
                    <th class="text-center th_yura_green">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green">
                        Color
                    </th>
                    <th class="text-center th_yura_green">
                        Presentación
                    </th>
                    <th class="text-center th_yura_green">
                        Tallos x ramo
                    </th>
                    <th class="text-center th_yura_green">
                        Longitud
                    </th>
                    <th class="text-center th_yura_green">
                        Días
                    </th>
                    <th class="text-center th_yura_green">
                        Ramos
                    </th>
                    <th class="text-center th_yura_green">
                        Tallos
                    </th>
                    <th class="text-center th_yura_green">
                        Marcacion
                    </th>
                    <th class="text-center th_yura_green">
                        Dispo. Actual
                    </th>
                    <th class="text-center th_yura_green">
                        Basura
                    </th>
                </tr>
            </thead>

            <tbody>
                @php
                    $total_ramos = 0;
                    $total_tallos = 0;
                    $total_disponibles = 0;
                @endphp
                @foreach ($listado as $pos => $item)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        data-id_cuarto_frio="{{ $item->id_cuarto_frio }}">
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ convertDatetimeToText($item->fecha_registro) }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->pta_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->var_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->pres_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->tallos_x_ramo }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->longitud_ramo }}cm
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ difFechas(hoy(), $item->fecha)->days }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->cantidad }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->cantidad * $item->tallos_x_ramo }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->valor_marcacion }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->disponibles }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            NO
                        </th>
                    </tr>
                    @php
                        $total_ramos += $item->cantidad;
                        $total_tallos += $item->cantidad * $item->tallos_x_ramo;
                        $total_disponibles += $item->disponibles;
                    @endphp
                @endforeach
                @foreach ($basura as $pos => $item)
                    <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')"
                        data-id_inventario_basura="{{ $item->id_inventario_basura }}">
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ convertDatetimeToText($item->fecha_registro) }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->pta_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->var_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->pres_nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->tallos_x_ramo }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->longitud_ramo }}cm
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ difFechas(hoy(), $item->fecha)->days }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->cantidad }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->cantidad * $item->tallos_x_ramo }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $item->valor_marcacion }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            0
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            SI
                        </th>
                    </tr>
                    @php
                        $total_ramos += $item->cantidad;
                        $total_tallos += $item->cantidad * $item->tallos_x_ramo;
                        $total_disponibles += 0;
                    @endphp
                @endforeach
            </tbody>
            <tr id="tr_fija_bottom_0">
                <th class="padding_lateral_5 th_yura_green" colspan="7">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_ramos) }}
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_tallos) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_disponibles) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados
    </div>
@endif

<style>
    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 9;
    }
</style>
