<tr id="tr_nueva_especificacion_{{$cant_row+1}}">
    <td style="border-color: #9d9d9d">
        <select id="id_planta_{{$cant_row+1}}" style="width: 100%" name="id_planta" class="form-control" onchange="seleccionar_variedad('{{$cant_row+1}}')">
            <option selected disabled>Seleccione</option>
            @foreach($plantas as $p)
                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d">
        <select id="id_variedad_{{$cant_row+1}}" style="width: 100%" name="id_variedad" class="form-control">
            {{--<option selected disabled>Seleccione</option>--}}
        </select>
    </td>
    <td style="border-color: #9d9d9d">
        <select id="id_clasificacion_ramo_{{$cant_row+1}}" style="width: 100%" name="id_clasificacion_ramo" class="form-control">
            {{--<option selected disabled>Seleccione</option>--}}
            @foreach($clasificacion_ramo as $c)
                <option value="{{$c->id_clasificacion_ramo}}">{{$c->nombre}}</option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d">
        <select id="id_empaque_{{$cant_row+1}}" style="width: 100%" name="id_empaque" class="form-control">
            @foreach($empaque as $e)
                <option value="{{$e->id_empaque}}">{{mb_strtoupper(explode("|",$e->nombre)[0])}}</option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d">
        <input type="text" placeholder="Cantidad" id="ramo_x_caja_{{$cant_row+1}}" style="width: 100%" value="1" name="ramo_x_caja"
               class="form-control">
    </td>
    <td style="border-color: #9d9d9d">
        <select id="id_presentacion_{{$cant_row+1}}" style="width: 100%" name="id_presentacion" class="form-control">
            {{--<option selected disabled>Seleccione</option>--}}
            @foreach($presentacion as $p)
                <option value="{{$p->id_empaque}}">{{$p->nombre}}</option>
            @endforeach
        </select>
    </td>
    <td style="border-color: #9d9d9d">
        <input type="text" placeholder="Cantidad" id="tallos_x_ramo_{{$cant_row+1}}" style="width: 100%" name="tallos_x_ramo"
               class="form-control">
    </td>
    <td style="border-color: #9d9d9d">
        <input type="text" id="longitud_{{$cant_row+1}}" style="width: 100%" name="longitud" class="form-control">
    </td>
    <td style="border-color: #9d9d9d">
        <select id="id_unidad_medida_{{$cant_row+1}}" name="id_unidad_medida" style="width: 100%" class="form-control">
            {{--<option value="">Seleccione</option>--}}
            @foreach($unidad_medida as $u)
                <option value="{{$u->id_unidad_medida}}">{{$u->siglas}}</option>
            @endforeach
        </select>
    </td>
    <td id="td_btn_add_store_{{$cant_row+1}}" style="border-color: #9d9d9d" class="text-center">
    </td>
</tr>

<script>
    $(document).ready(()=>{
        $("select[name='id_planta'], select[name='id_variedad'], select[name='id_clasificacion_ramo'], select[name='id_empaque'], select[name='id_presentacion']").select2()
    })
</script>

<style>
    span.select2-selection{
        width: 140px!important;
    }

</style>
