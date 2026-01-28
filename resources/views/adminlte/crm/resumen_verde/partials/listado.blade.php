<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green th_fija_left_0" colspan="3">
                <div style="width: 150px">
                    Semanas/Calibres
                </div>
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center th_yura_green" colspan="{{count($variedades) + 3}}">
                    <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="listar_resumen_verde_semanal('{{$sem->fecha_inicial}}', '{{$sem->fecha_final}}')">
                        {{$sem->codigo}}
                    </button>
                </th>
                @php
                    $var_array = [];
                    foreach($variedades as $var)
                        $var_array[] = 0;
                    array_push($totales, [
                        'variedades' => $var_array,
                        'venta' => 0,
                    ]);
                @endphp
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green th_fija_left_0" style="padding-right: 5px; padding-left: 5px">
                Calibres
            </th>
            <th class="text-center th_yura_green th_fija_left_1" colspan="2">
                Precios
            </th>
            @foreach($semanas as $sem)
                @foreach($variedades as $pos_v => $var)
                    <th class="text-center bg-yura_dark" style="border-color: white; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{$var->siglas}}
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white;">
                    Total
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white;">
                    Porcent.
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; border-right: 2px solid">
                    Venta
                </th>
            @endforeach
        </tr>
        @foreach($data as $item)
            <tr>
                <th class="text-center th_fija_left_0"
                    style="border-color: #9d9d9d; width: 50px; background-color: {{explode('|', $item['unitaria']->color)[0]}}; color: {{explode('|', $item['unitaria']->color)[1]}}">
                    {{explode('|',$item['unitaria']->nombre)[0]}}{{$item['unitaria']->siglas}}
                </th>
                <th class="text-center th_fija_left_1"
                    style="border-color: #9d9d9d; width: 80px">
                    <input type="number" placeholder="Precio" title="Precio de venta" class="text-center"
                           id="update_precio_{{$item['unitaria']->id_clasificacion_unitaria}}" min="0"
                           style="width: 100%; background-color: {{explode('|', $item['unitaria']->color)[0]}}; color: {{explode('|', $item['unitaria']->color)[1]}}"
                           value="{{$item['unitaria']->precio_venta}}">
                </th>
                <th class="text-center th_fija_left_2"
                    style="border-color: #9d9d9d; width: 30px; background-color: {{explode('|', $item['unitaria']->color)[0]}}">
                    <button type="button" class="btn btn-xs btn-yura_dark" title="Guardar precio de venta"
                            onclick="update_precio('{{$item['unitaria']->id_clasificacion_unitaria}}')">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                </th>
                @foreach($item['valores'] as $pos => $val)
                    @php
                        $total_sem_unitaria = 0;
                    @endphp
                    @foreach($val as $pos_v => $var)
                        <td class="text-center"
                            style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                            {{number_format($var['tallos'])}}
                        </td>
                        @php
                            $total_sem_unitaria += $var['tallos'];
                            $totales[$pos]['variedades'][$pos_v] += $var['tallos'];
                        @endphp
                    @endforeach
                    @php
                        $venta = $item['unitaria']->precio_venta > 0 ? $total_sem_unitaria * $item['unitaria']->precio_venta : 0;
                    @endphp
                    <th class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; background-color: #e9ecef">
                        {{number_format($total_sem_unitaria)}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; background-color: #e9ecef">
                        {{porcentaje($total_sem_unitaria, $totales_sem[$pos], 1)}}%
                    </th>
                    <th class="text-center"
                        style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px; border-right: 2px solid; background-color: #a9ffcc">
                        ${{number_format($venta, 2)}}
                    </th>
                    @php
                        $totales[$pos]['venta'] += $venta;
                    @endphp
                @endforeach
            </tr>
        @endforeach
        <tr>
            <th class="text-center th_yura_green th_fija_left_0" colspan="3">
                Totales
            </th>
            @foreach($totales as $t)
                @php
                    $total_sem = 0;
                @endphp
                @foreach($t['variedades'] as $pos_v => $var)
                    <th class="text-center bg-yura_dark"
                        style="border-color: white; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{number_format($var)}}
                    </th>
                    @php
                        $total_sem += $var;
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;">
                    {{number_format($total_sem)}}
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;" title="Precio x Tallo">
                    ¢{{$total_sem > 0 ? round(($t['venta'] / $total_sem) * 100, 3) : 0}}
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    ${{number_format($t['venta'], 2)}}
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green th_fija_left_0" colspan="3">
                Calibre
            </th>
            @foreach($total_cosechados as $pos_t => $t)
                @foreach($t as $pos_v => $var)
                    <th class="text-center bg-yura_dark"
                        style="border-color: white; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{round($var['calibre'], 2)}}
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;">
                    {{round($calibres[$pos_t], 2)}}
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;" title="Precio x Tallo">

                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green th_fija_left_0" colspan="3">
                Desechos
            </th>
            @foreach($totales as $pos_t => $t)
                @php
                    $total_sem = 0;
                @endphp
                @foreach($t['variedades'] as $pos_v => $var)
                    <th class="text-center bg-yura_dark"
                        style="border-color: white; padding-left: 5px; padding-right: 5px; {{$pos_v == 0 ? 'border-left: 2px solid' : ''}}">
                        {{100 - porcentaje($var, $total_cosechados[$pos_t][$pos_v]['cosecha'], 1)}}%
                    </th>
                    @php
                        $total_sem += $total_cosechados[$pos_t][$pos_v]['cosecha'];
                    @endphp
                @endforeach
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;">
                    {{100 - porcentaje($totales_sem[$pos_t], $total_sem, 1)}}%
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px;" title="Precio x Tallo">
                </th>
                <th class="text-center bg-yura_dark" style="border-color: white; padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    function update_precio(unitaria) {
        datos = {
            _token: '{{csrf_token()}}',
            unitaria: unitaria,
            precio: $('#update_precio_' + unitaria).val(),
        };
        post_jquery('{{url('resumen_verde/update_precio')}}', datos, function (retorno) {
            buscar_resumen_verde();
        });
    }
</script>

<style>
    .th_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9;
    }

    .th_fija_left_1 {
        position: sticky;
        left: 61px;
        z-index: 9;
    }

    .th_fija_left_2 {
        position: sticky;
        left: 120px;
        z-index: 9;
    }
</style>