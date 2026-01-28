<div id="table_consignatarios" style="overflow-y: scroll; max-height: 700px">
    @if (sizeof($listado) > 0)
        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
            id="table_content_consignatarios">
            <thead>
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green">
                        NOMBRE COMPLETO
                    </th>
                    <th class="text-center th_yura_green">
                        CIUDAD
                    </th>
                    <th class="text-center th_yura_green">
                        PAÍS
                    </th>
                    <th class="text-center th_yura_green">
                        IDENTIFICACIÓN
                    </th>
                    <th class="text-center th_yura_green">
                        TÉLEFONO
                    </th>
                    <th class="text-center th_yura_green">
                        CORREO
                    </th>
                    @if (es_server())
                        <th class="text-center th_yura_green">
                            OPCIONES
                        </th>
                    @endif
                </tr>
            </thead>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')"
                    onmouseleave="$(this).css('background-color','')">
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->nombre }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->ciudad }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->pais() != null ? $item->pais()->nombre : '' }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->identificacion }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->telefono }}</td>
                    <td style="border-color: #9d9d9d" class="text-center"><a
                            href="mailto:{{ $item->correo }}">{{ $item->correo }}</a></td>
                    @if (es_server())
                        <td style="border-color: #9d9d9d" class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-yura_default btn-xs" title="Ver consignatario"
                                    onclick="add_consignatario('{{ $item->id_consignatario }}')"
                                    id="btn_consignatario_{{ $item->id_consignatario }}">
                                    <i class="fa fa-fw fa-eye" style="color: black"></i>
                                </button>
                                <button class="btn btn-yura_{{ $item->estado == 1 ? 'danger' : 'warning' }} btn-xs"
                                    title="Eliminar consignatario"
                                    onclick="update_consignatario('{{ $item->id_consignatario }}','{{ $item->estado }}')">
                                    <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'ban' : 'check' }}"></i>
                                </button>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </table>
        <div id="pagination_listado_consignatarios">
            {!! str_replace('/?', '?', $listado->render()) !!}
        </div>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>
