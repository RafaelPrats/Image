@if (count($listado) > 0)
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado_recepcion">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Variedad
                </th>
                <th class="text-center th_yura_green">
                    Color
                </th>
                <th class="text-center th_yura_green">
                    Longitud
                </th>
                <th class="text-center th_yura_green">
                    Mallas
                </th>
                <th class="text-center th_yura_green">
                    Tallos x Malla
                </th>
                <th class="text-center th_yura_green">
                    Total Tallos
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </thead>
        @php
            $total_tallos = 0;
        @endphp
        <tbody>
            @foreach ($listado as $item)
                @php
                    $variedad = $item->variedad;
                    $total_tallos += $item->cantidad_mallas * $item->tallos_x_malla;
                @endphp
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_planta_{{ $item->id_desglose_recepcion }}" style="width: 100%"
                            onchange="select_planta($(this).val(), 'edit_variedad_{{ $item->id_desglose_recepcion }}', 'edit_variedad_{{ $item->id_desglose_recepcion }}', '<option value= selected>Seleccione</option>')">
                            <option value="">Seleccione</option>
                            @foreach ($plantas as $p)
                                <option value="{{ $p->id_planta }}"
                                    {{ $p->id_planta == $variedad->id_planta ? 'selected' : '' }}>
                                    {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" class="ids_desglose" value="{{ $item->id_desglose_recepcion }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select id="edit_variedad_{{ $item->id_desglose_recepcion }}" style="width: 100%">
                            <option value="{{ $variedad->id_variedad }}">{{ $variedad->nombre }}</option>
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" id="edit_longitud_{{ $item->id_desglose_recepcion }}"
                            min="1" required style="width: 100%" value="{{ $item->longitud_ramo }}">
                        <span class="hidden">
                            {{ $item->longitud_ramo }}
                        </span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" min="0" required
                            id="edit_cantidad_mallas_{{ $item->id_desglose_recepcion }}" style="width: 100%"
                            value="{{ $item->cantidad_mallas }}">
                        <span class="hidden">
                            {{ $item->cantidad_mallas }}
                        </span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" class="text-center" min="0" required
                            id="edit_tallos_x_malla_{{ $item->id_desglose_recepcion }}" style="width: 100%"
                            value="{{ $item->tallos_x_malla }}">
                        <span class="hidden">
                            {{ $item->tallos_x_malla }}
                        </span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($item->cantidad_mallas * $item->tallos_x_malla) }}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_warning" title="Modificar"
                                onclick="update_desglose('{{ $item->id_desglose_recepcion }}')">
                                <i class="fa fa-fw fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                onclick="delete_desglose('{{ $item->id_desglose_recepcion }}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr id="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="5">
                    TOTAL
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_tallos) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </tfoot>
    </table>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>
