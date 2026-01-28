<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_form_add_unidad_medida">
    <thead>
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
            Nombre
        </th>
        <th class="text-center th_yura_green" style="width: 60px;">
            Siglas
        </th>
        <th class="text-center th_yura_green">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            Uso
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; padding-left: 5px; padding-right: 5px">
            Crear
        </th>
    </tr>
    </thead>
    <tbody>
    <tr id="tr_new_app">
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" id="new_nombre" style="width: 100%" placeholder="Nombre">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" class="text-center" id="new_siglas" style="width: 100%" placeholder="Siglas">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_tipo" style="width: 100%">
                <option value="L">Longitud</option>
                <option value="P">Peso</option>
                <option value="V">Volumen</option>
                <option value="T">Tiempo</option>
                <option value="O">Otro</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_uso" style="width: 100%">
                <option value="S">Sanidad</option>
                <option value="C">Cultural</option>
            </select>
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            @if(es_server())
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_unidad()" title="Crear">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                </div>
            @endif
        </td>
    </tr>
    </tbody>
</table>
