<div id="table_clientes">
    @if (sizeof($listado) > 0)
        <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
            id="table_content_clientes">
            <thead>
                <tr style="background-color: #dd4b39; color: white" class="tr_fija_top_0">
                    <th class="text-center th_yura_green">
                        NOMBRE COMPLETO
                    </th>
                    <th class="text-center th_yura_green">
                        DIRECCIÓN
                    </th>
                    <th class="text-center th_yura_green">
                        PAÍS
                    </th>
                    <th class="text-center th_yura_green">
                        IDENTIFICACIÓN
                    </th>
                    <th class="text-center th_yura_green">
                        CORREO
                    </th>
                    <th class="text-center th_yura_green">
                        OPCIONES
                    </th>
                </tr>
            </thead>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')"
                    onmouseleave="$(this).css('background-color','')" class="{{ $item->estado == 1 ? '' : 'error' }}"
                    id="row_clientes_{{ $item->id_cliente }}">
                    <td style="border-color: #9d9d9d" class="text-center">{{ mb_strtoupper($item->nombre) }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->direccion }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->pa_nombre }}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{ $item->ruc }}</td>
                    <td style="border-color: #9d9d9d" class="text-center"><a
                            href="mailto:{{ $item->correo }}">{{ $item->correo }}</a></td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <button type="button" class="btn btn-yura_dark btn-xs" title="Ver detalles"
                            onclick="detalles_cliente('{{ $item->id_cliente }}')"
                            id="btn_view_usuario_{{ $item->id_cliente }}">
                            <i class="fa fa-fw fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>
