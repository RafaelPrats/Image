<legend style="font-size: 1em; margin-bottom: 2px" class="text-center">
    <strong>Distribuciones Pendientes de AYER</strong>
</legend>
@if (count($listado) > 0)
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                FLOR
            </th>
            <th class="text-center th_yura_green">
                CLIENTE
            </th>
            <th class="text-center th_yura_green">
                LONGITUD
            </th>
            <th class="text-center th_yura_green">
                TALLOS
            </th>
        </tr>
        @foreach ($listado as $item)
            @foreach ($item['valores'] as $v)
                <tr class="mouse-hand" onclick="cargar_url('distribucion_mixtos')" onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item['planta']->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $v['nombre'] }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $v['longitud_ramo'] }}cm
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $v['mixtos_x_cliente'] }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
@else
    <div class="alert alert-info text-center">
        Todo está distribuido para hoy
    </div>
@endif
