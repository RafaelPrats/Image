<div id="table_agencia_carga" style="overflow-y: scroll; max-height: 700px">
    @if (sizeof($listado) > 0)
        <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d"
            id="table_content_agencias_carga">
            <thead>
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="border-color: #9d9d9d">
                        Nombre
                    </th>
                    <th class="text-center th_yura_green" style="border-color: #9d9d9d">
                        Código
                    </th>
                    @if (es_server())
                        <th class="text-center th_yura_green" style="border-color: #9d9d9d">
                            Opciones
                        </th>
                    @endif
                </tr>
            </thead>
            @foreach ($listado as $key => $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')"
                    onmouseleave="$(this).css('background-color','')" class="{{ $item->estado == 1 ? '' : 'error' }}"
                    id="row_agencia_{{ $item->id_agencia_carga }}">
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->nombre }}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->identificacion }}
                    </td>
                    @if (es_server())
                        <td style="border-color: #9d9d9d" class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-yura_primary btn-xs" title="Editar"
                                    onclick="create_agencia_carga('{{ $item->id_agencia_carga }}','{{ csrf_token() }}')"
                                    id="ver_agencia_carga">
                                    <i class="fa fa-fw fa-pencil" style="color: black"></i>
                                </button>
                                <button type="button"
                                    class="btn {{ $item->estado == 1 ? 'btn-yura_warning' : 'btn-yura_danger' }} btn-xs"
                                    title="Desactivar"
                                    onclick="actualizar_agencia_carga('{{ $item->id_agencia_carga }}','{{ $item->estado }}')"
                                    id="boton_agencia_carga_{{ $item->id_agencia_carga }}">
                                    <i class="fa fa-fw {{ $item->estado == 1 ? 'fa-trash' : 'fa-unlock' }}"
                                        style="color: black" id="icon_agencia_carga_{{ $item->id_agencia_carga }}"></i>
                                </button>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </table>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>
