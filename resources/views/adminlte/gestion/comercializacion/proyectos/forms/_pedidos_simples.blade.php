<table style="width: 100%">
    <tbody>
        <tr>
            <td>
                <div class="input-group" style="min-width: 220px">
                    <span class="input-group-addon bg-yura_dark">
                        Planta
                    </span>
                    <select id="form_planta" class="form-control" style="width: 100%"
                        onchange="select_planta($(this).val(), 'form_variedad', 'form_variedad', '<option value=>Todos</option>', ''); buscar_form_especificaciones()">
                        <option value=""></option>
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group" style="min-width: 220px">
                    <span class="input-group-addon bg-yura_dark">
                        Variedad
                    </span>
                    <select id="form_variedad" class="form-control" style="width: 100%"
                        onchange="buscar_form_especificaciones()">
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group" style="min-width: 220px">
                    <span class="input-group-addon bg-yura_dark">
                        Cajas
                    </span>
                    <select id="form_caja" class="form-control" style="width: 100%"
                        onchange="buscar_form_especificaciones()">
                    </select>
                </div>
            </td>
            <td>
                <div class="input-group" style="min-width: 180px">
                    <span class="input-group-addon bg-yura_dark">
                        Ramos x Caja
                    </span>
                    <input type="number" id="form_ramos_x_caja" class="form-control text-center" style="width: 100%">
                </div>
            </td>
            <td>
                <div class="input-group" style="min-width: 180px">
                    <span class="input-group-addon bg-yura_dark">
                        Longitud
                    </span>
                    <input type="number" id="form_longitud" class="form-control text-center" style="width: 100%">
                </div>
            </td>
            <td>
                <div class="input-group" style="min-width: 180px">
                    <span class="input-group-addon bg-yura_dark">
                        Peso
                    </span>
                    <input type="number" id="form_peso" class="form-control text-center" style="width: 100%">
                    <div class="input-group-btn">
                        <button class="btn btn-yura_dark" onclick="buscar_form_especificaciones()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>

<div style="margin-top: 5px; width: 100%" id="div_form_especificacion"></div>
