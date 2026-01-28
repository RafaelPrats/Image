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
                    Usuario
                </th>
                <th class="th_yura_green text-center">
                    Fecha y Hora
                </th>
                <th class="th_yura_green text-center">
                    Cambio Fecha
                </th>
                {{-- <th class="th_yura_green text-center">
                </th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                <tr class="tr_modificaciones tr_modificaciones_?? " id="tr_modificacion_{{ $item->id_cambios_pedido }}"
                    onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_cliente }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_pta }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_var }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->presentacion }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->longitud_ramo }}cm
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ explode('|', $item->nombre_caja)[0] }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->caja }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @if ($item->piezas != null)
                            {{ $item->piezas }}
                        @else
                            <sup><em>MIXTOS</em></sup>
                        @endif
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->ramos }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->tallos }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->tallos_x_ramo }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre_usuario }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->fecha_registro }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ $item->cambio_fecha ? $item->fecha_actual : 'NO' }}
                    </td>
                    {{-- <td class="text-center" style="border-color: #9d9d9d">
                        <button type="button" class="btn btn-xs btn-yura_dark btn_relacionados"
                            id="btn_mostrar_relacionados" title="Mostrar solo RELACIONADOS"
                            onclick="$('.tr_modificaciones').addClass('hidden'); $('.btn_relacionados').toggleClass('hidden'); $('.tr_modificaciones_{{ $item->id_detalle_especificacionempaque }}').removeClass('hidden')">
                            <i class="fa fa-fw fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_dark btn_relacionados hidden"
                            id="btn_mostrar_relacionados" title="Mostrar TODO"
                            onclick="$('.tr_modificaciones').removeClass('hidden'); $('.btn_relacionados').toggleClass('hidden');">
                            <i class="fa fa-fw fa-eye-slash"></i>
                        </button>
                         <button type="button" class="btn btn-xs btn-yura_danger"
                                title="{{ $item->usar ? 'DESACTIVAR' : 'ACTIVAR' }}"
                                onclick="cambiar_uso('{{ $item->id_cambios_pedido }}')">
                                <i class="fa fa-fw fa-lock"></i>
                            </button> 
                    </td>
                    --}}
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados que mostrar
    </div>
@endif
