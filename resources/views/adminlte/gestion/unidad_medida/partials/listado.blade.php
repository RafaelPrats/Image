<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_unidades">
    <thead>
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
            Nombre
        </th>
        <th class="text-center th_yura_green">
            Siglas
        </th>
        <th class="text-center th_yura_green">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            Uso
        </th>
        <th class="text-center th_yura_green" style="width: 40px; border-radius: 0 18px 0 0">
            <div class="btn-group">
                <button type="button" class="btn btn-yura_dark btn-xs" onclick="buscar_listado()" title="Refrescar listado">
                    <i class="fa fa-fw fa-refresh"></i>
                </button>
            </div>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($listado as $item)
        <tr id="tr_unidad_{{$item->id_unidad_medida}}">
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" id="edit_nombre_{{$item->id_unidad_medida}}" value="{{$item->nombre}}"
                       style="width: 100%">
                <span class="hidden">{{$item->nombre}}</span>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" class="text-center" id="edit_siglas_{{$item->id_unidad_medida}}" value="{{$item->siglas}}"
                       style="width: 100%">
                <span class="hidden">{{$item->siglas}}</span>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="edit_tipo_{{$item->id_unidad_medida}}" style="width: 100%">
                    <option value="L" {{$item->tipo == 'L' ? 'selected' : ''}}>Longitud</option>
                    <option value="P" {{$item->tipo == 'P' ? 'selected' : ''}}>Peso</option>
                    <option value="V" {{$item->tipo == 'V' ? 'selected' : ''}}>Volumen</option>
                    <option value="T" {{$item->tipo == 'T' ? 'selected' : ''}}>Tiempo</option>
                    <option value="O" {{$item->tipo == 'O' ? 'selected' : ''}}>Otro</option>
                </select>
                <span class="hidden">{{$item->tipo}}</span>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="edit_uso_{{$item->id_unidad_medida}}" style="width: 100%">
                    <option value="S" {{$item->uso == 'S' ? 'selected' : ''}}>Sanidad</option>
                    <option value="C" {{$item->uso == 'C' ? 'selected' : ''}}>Cultural</option>
                </select>
                <span class="hidden">{{$item->uso}}</span>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                @if(es_server())
                    <div class="btn-group">
                        <button type="button" class="btn btn-yura_primary btn-xs" onclick="update_unidad('{{$item->id_unidad_medida}}')"
                                title="Editar">
                            <i class="fa fa-fw fa-edit"></i>
                        </button>
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
