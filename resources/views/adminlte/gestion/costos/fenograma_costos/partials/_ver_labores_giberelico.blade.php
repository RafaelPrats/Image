@php
    $modulo = $ciclo->modulo;
@endphp
<legend class="text-center" style="font-size: 1em">{{$app_matriz->nombre}} aplicados al módulo: <strong>{{$modulo->nombre}}</strong></legend>

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                <div style="width: 50px">
                    Días
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 120px">
                    Fecha
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 80px">
                    Semana
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Rep.
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Camas
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Ltrs. x Cama
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Volumen
                </div>
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 70px">
                    Gib.
                </div>
            </th>
            @php
                $costo_total = 0;
                $camas_total = 0;
                $litros_total = 0;
                $volumen_total = 0;
                $gib_total = 0;
                $productos_total = [];
            @endphp
            @foreach($productos as $producto)
                <th class="text-center bg-yura_dark" colspan="3" style="padding-left: 5px; padding-right: 5px">
                    <div style="width: 200px">
                        {{$producto->nombre}}
                    </div>
                </th>
                @php
                    $productos_total[] = [
                        'dosis_normal' => 0,
                        'dosis_convertida' => 0,
                        'costo' => 0,
                    ];
                @endphp
            @endforeach
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark">
                    <div style="width: 140px">
                        {{$mo->nombre}}
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                Totales
            </th>
        </tr>
        @foreach($labores as $pos => $labor)
            @php
                $volumen = round($labor->camas * $labor->litro_x_cama);
                $costo_dia = 0;
                $camas_total += $labor->camas;
                $litros_total += $labor->litro_x_cama;
                $volumen_total += $volumen;
            @endphp
            <tr id="tr_labor_{{$labor->id_aplicacion_campo}}">
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{difFechas($labor->fecha, $ciclo->fecha_inicio)->days}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{convertDateToText($labor->fecha)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{getSemanaByDate($labor->fecha)->codigo}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$labor->repeticion}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$labor->camas}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$labor->litro_x_cama}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_volumen_{{$labor->id_aplicacion_campo}}">
                    {{$volumen}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef" id="td_gib_{{$labor->id_aplicacion_campo}}">
                </th>
                @foreach($productos as $pos_p => $producto)
                    @php
                        $detalle = $labor->getDetalleByProducto($producto->id_producto);
                        $dosis_normal = '';
                        $dosis_convertida = '';
                        $gib = 0;
                        $costo_producto = 0;
                        if ($detalle != ''){
                            $dosis_normal = $detalle->id_unidad_medida != '' ? $detalle->dosis . ' '. $detalle->unidad_medida->siglas : $detalle->dosis;
                            $productos_total[$pos_p]['dosis_normal'] += $detalle->dosis;

                            $dosis = $detalle->factor_conversion != '' ? round($detalle->dosis * $detalle->factor_conversion, 3) : $detalle->dosis;
                            $dosis = $dosis * $volumen;
                            if ($producto->nombre == 'ACIDO GIBERELICO ROBUST 90%') {
                                $dosis_acido_giberelico = $dosis;
                                $gib = $detalle->dosis * $detalle->factor_conversion;
                                $gib_total += $gib;
                            }
                            if ($producto->nombre == 'ALCOHOL POTABLE') {
                                $dosis = $detalle->factor_conversion != '' ? round($detalle->dosis * $detalle->factor_conversion, 2) : $detalle->dosis;
                                $dosis = $dosis * $dosis_acido_giberelico;
                            }
                            $dosis_convertida = $detalle->id_unidad_conversion != '' ? $dosis . ' '. $detalle->unidad_conversion->siglas : $dosis;
                            $productos_total[$pos_p]['dosis_convertida'] += $dosis;
                            $costo_producto = $dosis * $producto->precio;
                            $productos_total[$pos_p]['costo'] += $costo_producto;
                        }
                        $costo_dia += $costo_producto;
                    @endphp
                    @if($producto->nombre == 'ACIDO GIBERELICO ROBUST 90%')
                        <script>
                            $('#td_gib_{{$labor->id_aplicacion_campo}}').html('{{round($gib, 2)}}');
                        </script>
                    @endif
                    <td class="text-center" style="border-color: #9d9d9d" title="Dosis mezcla">
                        {{$dosis_normal}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" title="Dosis bodega">
                        {{$dosis_convertida}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" title="Costo">
                        @if($costo_producto > 0)
                            ${{round($costo_producto, 2)}}
                        @endif
                    </td>
                @endforeach
                @foreach($mano_obras as $mo)
                    @php
                        $detalle = $labor->getDetalleByManoObra($mo->id_mano_obra);
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' '. $detalle->unidad_medida->siglas : ''}}
                    </td>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; padding-right: 5px; padding-left: 5px">
                    ${{round($costo_dia, 3)}}
                </th>
                @php
                    $costo_total += $costo_dia;
                @endphp
            </tr>
        @endforeach
        {{-- TOTALES --}}
        <tr>
            <th class="text-center th_yura_green" style="padding-left: 5px" colspan="4">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{$camas_total}}
            </th>
            <th class="text-center th_yura_green">
                {{$litros_total}}
            </th>
            <th class="text-center th_yura_green">
                {{number_format($volumen_total)}}
            </th>
            <th class="text-center th_yura_green">
                {{round($gib_total, 2)}}
            </th>
            @foreach($productos_total as $t)
                <th class="text-center bg-yura_dark">
                    {{$t['dosis_normal']}}
                </th>
                <th class="text-center bg-yura_dark">
                    {{$t['dosis_convertida']}}
                </th>
                <th class="text-center bg-yura_dark">
                    ${{number_format($t['costo'], 2)}}
                </th>
            @endforeach
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark">
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                ${{number_format($costo_total, 2)}}
            </th>
        </tr>
    </table>
</div>

<style>
    .columna_fija_right_0 {
        position: sticky;
        right: 0;
        z-index: 9;
        background-color: #e9ecef;
    }
</style>