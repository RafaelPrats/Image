<legend style="font-size: 1em" class="text-center">
    <i class="fa fa-fw fa-info-circle"></i> Crear nueva proyección en la semana
    <strong>{{$semana->codigo}}</strong>,
    variedad: <strong>{{$variedad->nombre}}</strong>
</legend>

<table class="table-bordered" style="width: 100%; border: 2px solid #9d9d9d;">
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Módulo
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <select name="id_modulo" id="id_modulo" style="width: 100%" class="input-yura_white"
                    onchange="$('#area').val($('#area_mod_'+$(this).val()).val())">
                <option value="">Seleccione...</option>
                @foreach($modulos as $mod)
                    <option value="{{$mod->id_modulo}}">{{$mod->nombre}}</option>
                @endforeach
            </select>
        </td>
    </tr>
    @foreach($modulos as $mod)
        <input type="hidden" id="area_mod_{{$mod->id_modulo}}" value="{{$mod->area}}">
    @endforeach
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Tipo
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <select name="tipo" id="tipo" style="width: 100%" class="input-yura_white">
                <option value="P">Poda</option>
                <option value="S" selected>Siembra</option>
            </select>
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Curva
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" name="curva" id="curva" style="width: 100%" class="text-center input-yura_white" value="{{$semana->curva}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Semana Cosecha
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="semana_poda_siembra" id="semana_poda_siembra" style="width: 100%" class="text-center input-yura_white"
                   value="{{$semana->semana_siembra}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Área
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="area" id="area" style="width: 100%" class="text-center input-yura_white" value="0">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Densidad x m<sup>2</sup>
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="densidad" id="densidad" style="width: 100%" class="text-center input-yura_white" value="0"
                   onkeyup="calcular_plantas_iniciales()">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Plantas Iniciales
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="plantas_iniciales" id="plantas_iniciales" style="width: 100%" class="text-center input-yura_white"
                   value="0">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            % Desecho
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="desecho" id="desecho" style="width: 100%" class="text-center input-yura_white"
                   value="{{$semana->desecho}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Tallos x Planta
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="tallos_planta" id="tallos_planta" style="width: 100%" class="text-center input-yura_white"
                   value="{{$semana->tallos_planta_siembra}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
            Tallos x Ramo
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" name="tallos_ramo" id="tallos_ramo" style="width: 100%" class="text-center input-yura_white"
                   value="{{$semana->tallos_ramo_siembra}}">
        </td>
    </tr>
</table>

<input type="hidden" id="id_variedad" value="{{$variedad->id_variedad}}">
<input type="hidden" id="id_semana" value="{{$semana->id_semana}}">

<div class="text-center" style="margin-top: 10px">
    <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_new_proyeccion()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function store_new_proyeccion() {
        datos = {
            _token: '{{csrf_token()}}',
            id_modulo: $('#id_modulo').val(),
            id_variedad: $('#id_variedad').val(),
            id_semana: $('#id_semana').val(),
            tipo: $('#tipo').val(),
            curva: $('#curva').val(),
            semana_poda_siembra: $('#semana_poda_siembra').val(),
            plantas_iniciales: $('#plantas_iniciales').val(),
            desecho: $('#desecho').val(),
            tallos_planta: $('#tallos_planta').val(),
            tallos_ramo: $('#tallos_ramo').val(),
        };
        if (datos['id_modulo'] != '')
            post_jquery('{{url('proy_cosecha/store_proyeccion')}}', datos, function () {
                //get_row_byModulo(datos['id_modulo']);
                listar_proyecciones();
                cerrar_modals();
            });
        else
            alerta('<div class="alert alert-danger text-center">Seleccione el módulo</div>');
    }

    function calcular_plantas_iniciales() {
        area = $('#area').val();
        densidad = $('#densidad').val();
        plantas_iniciales = area * densidad;
        $('#plantas_iniciales').val(plantas_iniciales);
    }
</script>