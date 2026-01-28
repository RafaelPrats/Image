<div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
    @if(count($ciclos) > 0)
        <table class="table-bordered table-striped" style="border: 1px solid #9d9d9d; width: 100%" id="table_fenograma_costos">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Variedad
                </th>
                <th class="text-center th_yura_green">
                    Módulo
                </th>
                <th class="text-center th_yura_green">
                    P/S
                </th>
                <th class="text-center th_yura_green">
                    Área
                </th>
                <th class="text-center th_yura_green">
                    Días
                </th>
                <th class="text-center th_yura_green">
                    Semana Ini.
                </th>
                <th class="text-center bg-yura_dark">
                    Plantas
                </th>
                <th class="text-center bg-yura_dark">
                    Luz
                </th>
                <th class="text-center bg-yura_dark">
                    Giberélico
                </th>
                <th class="text-center bg-yura_dark">
                    Desbrote
                </th>
                <th class="text-center th_yura_green">
                    Total
                </th>
                <th class="text-center th_yura_green">
                    Costos/m<sup>2</sup>
                </th>
                <th class="text-center th_yura_green">
                    Costos/tallos
                </th>
            </tr>
            </thead>
            @php
                $total_plantas = 0;
                $total_luz = 0;
                $total_giberelico = 0;
                $total_desbrote = 0;
                $total_costos = 0;
                $total_costos_m2 = 0;
            @endphp
            <tbody>
            @foreach($ciclos as $pos => $c)
                @php
                    $modulo = $c->modulo;
                    $fen_costos = $c->fenograma_costos;
                    $getTallosCosechados = $c->getTallosCosechados();
                    $total_ciclo = 0;
                    if($fen_costos != ''){
                        $total_plantas += $fen_costos->plantas;
                        $total_luz += $fen_costos->luz;
                        $total_giberelico += $fen_costos->giberelico;
                        $total_desbrote += $fen_costos->desbrote;
                        $total_ciclo = $fen_costos->plantas + $fen_costos->luz + $fen_costos->giberelico + $fen_costos->desbrote;
                        $total_costos += $total_ciclo;
                        $total_costos_m2 += $c->area > 0 ? round($total_ciclo / $c->area, 2) : 0;
                    }
                @endphp
                <tr>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{$c->variedad->siglas}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{$modulo->nombre}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{$c->poda_siembra}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{number_format($c->area, 2)}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{difFechas($c->fecha_fin, $c->fecha_inicio)->days}}
                    </th>
                    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
                        {{getSemanaByDate($c->fecha_inicio)->codigo}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        ${{$fen_costos != '' ? number_format($fen_costos->plantas, 2) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        ${{$fen_costos != '' ? number_format($fen_costos->luz, 2) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" onmouseover="$(this).css('background-color', '#3cf7ff57')"
                        onmouseleave="$(this).css('background-color', '')">
                        <a href="javascript:void(0)" class="text-black"
                           onclick="ver_labores_giberelico('{{$c->id_ciclo}}', '{{$app_giberelico->id_aplicacion_matriz}}')">
                            ${{$fen_costos != '' ? number_format($fen_costos->giberelico, 2) : 0}}
                        </a>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" onmouseover="$(this).css('background-color', '#3cf7ff57')"
                        onmouseleave="$(this).css('background-color', '')">
                        <a href="javascript:void(0)" class="text-black"
                           onclick="ver_labores_desbrote('{{$c->id_ciclo}}', '{{$app_desbrote->id_aplicacion_matriz}}')">
                            ${{$fen_costos != '' ? number_format($fen_costos->desbrote, 2) : 0}}
                        </a>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        ${{number_format($total_ciclo, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        ${{$c->area > 0 ? number_format($total_ciclo / $c->area, 2) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @if($c->activo == 0)
                            ¢{{$getTallosCosechados > 0 ? number_format($total_ciclo / $getTallosCosechados, 4) * 100 : 0}}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            {{-- TOTALES --}}
            <tfoot>
            <tr id="tr_fijo_bottom_0">
                <th class="text-left th_yura_green" style="padding-left: 5px" colspan="6">
                    TOTALES
                </th>
                <th class="text-center bg-yura_dark">
                    ${{number_format($total_plantas, 2)}}
                </th>
                <th class="text-center bg-yura_dark">
                    ${{number_format($total_luz, 2)}}
                </th>
                <th class="text-center bg-yura_dark">
                    ${{number_format($total_giberelico, 2)}}
                </th>
                <th class="text-center bg-yura_dark">
                    ${{number_format($total_desbrote, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    ${{number_format($total_costos, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    ${{round($total_costos_m2 / count($ciclos), 2)}}
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
            </tfoot>
        </table>
    @endif
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    #tr_fijo_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 8;
    }
</style>

<script>
    function ver_labores_giberelico(ciclo, app_matriz) {
        datos = {
            ciclo: ciclo,
            app_matriz: app_matriz,
        };
        get_jquery('{{url('fenograma_costos/ver_labores_giberelico')}}', datos, function (retorno) {
            modal_view('modal-view_ver_labores_by_ciclo', retorno, '<i class="fa fa-fw fa-eye"></i> Labores del ciclo', true, false, '95%');
        });
    }
    function ver_labores_desbrote(ciclo, app_matriz) {
        datos = {
            ciclo: ciclo,
            app_matriz: app_matriz,
        };
        get_jquery('{{url('fenograma_costos/ver_labores_desbrote')}}', datos, function (retorno) {
            modal_view('modal-view_ver_labores_by_ciclo', retorno, '<i class="fa fa-fw fa-eye"></i> Labores del ciclo', true, false, '95%');
        });
    }
</script>