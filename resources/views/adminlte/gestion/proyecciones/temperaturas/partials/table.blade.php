<table style="width: 100%;">
    <tr>
        <td style="padding-right: 5px">
            <div class="form-group input-group">
                <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                    Desde
                </div>
                <input type="date" class="form-control input-yura_default" id="filtro_desde" value="{{$desde}}" required>
            </div>
        </td>
        <td style="padding: 0 5px 0 5px">
            <div class="form-group input-group">
                <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                    Hasta
                </div>
                <input type="date" class="form-control input-yura_default" id="filtro_hasta" value="{{$hasta}}" required>
            </div>
        </td>
        <td style="padding-left: 5px" class="text-right">
            <div class="form-group input-group">
                <div class="input-group-btn">
                    <button type="button" class="btn btn-yura_dark" onclick="listar_temperaturas()"
                        @if(es_server()) style="border-radius: 18px" @else style="border-radius: 18px 0 0 18px" @endif
                    >
                        <i class="fa fa-fw fa-search"></i> Buscar
                    </button>
                    @if(es_local())
                        <button type="button" class="btn btn-yura_primary" onclick="add_temperatura()" style="border-radius: 0 18px 18px 0">
                            <i class="fa fa-fw fa-plus"></i> Ingresar
                        </button>
                    @endif
                </div>
            </div>
        </td>
    </tr>
</table>

@if(count($listado) > 0)
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 mt-2 mt-md-0" style="overflow-y: scroll; max-height: 450px;">
            <table class="table-striped table-bordered" style="border: 1px solid #9d9d9d; width: 100%; border-radius: 18px 18px 0 0"
                   id="table_temperaturas">
                <thead>
                <tr id="tr_fijo_0">
                    <th class="th_yura_green" style="border-radius: 18px 0 0 0; padding-left: 5px">
                        Fecha
                    </th>
                    <th class="th_yura_green" style="padding-left: 5px">
                        Máxima
                    </th>
                    <th class="th_yura_green" style="padding-left: 5px">
                        Mínima
                    </th>
                    <th class="th_yura_green" style="padding-left: 5px">
                        Delta
                    </th>
                    <th class="th_yura_green" style="padding-left: 5px">
                        10 Días Acum. Delta
                    </th>
                    <th class="th_yura_green" style="padding-left: 5px">
                        Lluvia
                    </th>
                    <th class="th_yura_green" style="border-radius: 0 18px 0 0; padding-left: 5px">
                        21 Días Acum. Lluvia
                    </th>
                </tr>
                </thead>
                <tbody>
                @php
                    $prom_minima = 0;
                    $prom_maxima = 0;
                    $prom_delta = 0;
                    $total_lluvia = 0;
                @endphp
                @foreach($listado as $item)
                    @php
                        $prom_minima += $item->minima;
                        $prom_maxima += $item->maxima;
                        $prom_delta += ($item->maxima - $item->minima);
                        $total_lluvia += $item->lluvia;
                    @endphp
                    <tr onmouseover="$(this).css('background-color','#e5f7f3 !important');"
                        onmouseleave="$(this).css('background-color','');">
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            {{$item->fecha}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            {{$item->maxima}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            {{$item->minima}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            {{$item->maxima - $item->minima}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            @php
                                $acum_10_dias = \Illuminate\Support\Facades\DB::table('temperatura')
                                    ->select(DB::raw('sum(maxima - minima) as cantidad'))
                                    ->where('fecha', '>=', opDiasFecha('-', 9, $item->fecha))
                                    ->where('fecha', '<=', $item->fecha)
                                    ->get()[0]->cantidad;
                            @endphp
                            {{round($acum_10_dias, 2)}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            {{$item->lluvia}}
                        </td>
                        <td class="" style="border-color: #9d9d9d; padding-left: 5px">
                            @php
                                $acum_21_dias = \Illuminate\Support\Facades\DB::table('temperatura')
                                    ->select(DB::raw('sum(lluvia) as cantidad'))
                                    ->where('fecha', '>=', opDiasFecha('-', 20, $item->fecha))
                                    ->where('fecha', '<=', $item->fecha)
                                    ->get()[0]->cantidad;
                            @endphp
                            {{round($acum_21_dias, 2)}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tr>
                    <th class="th_yura_green">
                        TOTALES
                    </th>
                    <th class="th_yura_green">
                        {{round($prom_maxima / count($listado), 2)}}
                    </th>
                    <th class="th_yura_green">
                        {{round($prom_minima / count($listado), 2)}}
                    </th>
                    <th class="th_yura_green">
                        {{round($prom_delta / count($listado), 2)}}
                    </th>
                    <th class="th_yura_green">
                    </th>
                    <th class="th_yura_green">
                        {{$total_lluvia}}
                    </th>
                    <th class="th_yura_green">
                    </th>
                </tr>
            </table>
        </div>
    </div>

    <script>
        estructura_tabla('table_temperaturas', false, false);
        $('#table_temperaturas_wrapper .row:first').hide()
    </script>

    <style>
        #tr_fijo_0 th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>
@endif
