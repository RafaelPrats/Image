@if (count($listado) > 0)
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_modificaciones">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="th_yura_green text-center">
                    Cliente
                </th>
                <th class="th_yura_green text-center">
                    Planta
                </th>
                <th class="th_yura_green text-center">
                    Color
                </th>
                <th class="th_yura_green text-center">
                    Presentación
                </th>
                <th class="th_yura_green text-center">
                    Longitud
                </th>
                <th class="th_yura_green text-center">
                    Caja
                </th>
                <th class="th_yura_green text-center">
                    Tipo Caja
                </th>
                <th class="th_yura_green text-center">
                    Piezas
                </th>
                <th class="th_yura_green text-center">
                    Ramos
                </th>
                <th class="th_yura_green text-center">
                    Tallos
                </th>
                <th class="th_yura_green text-center">
                    Tallos x Ramo
                </th>
                <th class="th_yura_green text-center">
                    Fecha y Hora
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $detalle_especificacionempaque = $item->detalle_especificacionempaque;
                    $variedad = $detalle_especificacionempaque->variedad;
                    $caja = $detalle_especificacionempaque->especificacion_empaque->empaque;
                @endphp
                <tr class="tr_modificaciones tr_modificaciones_{{ $item->id_detalle_especificacionempaque }} {{ $item->usar ? '' : 'error' }}"
                    id="tr_modificacion_{{ $item->id_pedido_modificacion }}">
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->cliente->detalle()->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->id_planta != null ? $item->planta->nombre : $variedad->planta->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->id_planta != null ? $item->getVariedad()->nombre : $variedad->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $detalle_especificacionempaque->empaque_p->nombre }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $detalle_especificacionempaque->longitud_ramo }}cm
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ explode('|', $caja->nombre)[0] }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $caja->siglas }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @if ($item->cantidad != null)
                            <strong>{{ $item->operador }}</strong>{{ $item->cantidad }}
                        @else
                            <sup><em>MIXTOS</em></sup>
                        @endif
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <strong>{{ $item->operador }}</strong>{{ $item->cantidad != null ? $item->cantidad * $item->ramos_x_caja : $item->ramos }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->cantidad != null ? $item->cantidad * $item->ramos_x_caja * $detalle_especificacionempaque->tallos_x_ramos : $item->tallos }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $detalle_especificacionempaque->tallos_x_ramos }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->fecha_registro }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados que mostrar
    </div>
@endif
