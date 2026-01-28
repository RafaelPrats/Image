<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active li-default" id="li-campo_ha_semana">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('campo_ha_semana')">Campo/<sup>ha</sup>/semana</a>
        </li>
        <li class="li-default" id="li-cosecha_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('cosecha_x_tallo')">Cosecha x Tallo</a>
        </li>
        <li class="li-default" id="li-postcosecha_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('postcosecha_x_tallo')">Postcosecha x Tallo</a>
        </li>
        <li class="li-default" id="li-total_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('total_x_tallo')">Total x Tallo</a>
        </li>
    </ul>
    <div>
        <div class="default" id="tab-campo_ha_semana">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_campo_ha_semana"></canvas>
                    <strong>
                        {{number_format(explode('|', $costos_campo_semana)[0], 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_campo_ha_semana" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-cosecha_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_cosecha_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_cosecha_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_cosecha_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-postcosecha_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_postcosecha_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_postcosecha_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_postcosecha_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-total_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_total_x_tallo"></canvas>
                    <strong>
                        ¢{{number_format($costos_total_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_total_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var rangos_costos_campo_ha_semana = [];
    @foreach(getIntervalosIndicador('C3') as $r)
    rangos_costos_campo_ha_semana.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_costos_cosecha_tallo = [];
    @foreach(getIntervalosIndicador('C4') as $r)
    rangos_costos_cosecha_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_costos_postcosecha_tallo = [];
    @foreach(getIntervalosIndicador('C5') as $r)
    rangos_costos_postcosecha_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_costos_total_tallo = [];
    @foreach(getIntervalosIndicador('C6') as $r)
    rangos_costos_total_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
    @endforeach

    render_gauge('canvas_campo_ha_semana', '{{round(explode('|', $costos_campo_semana)[0], 2)}}', rangos_costos_campo_ha_semana, true);
    render_gauge('canvas_cosecha_x_tallo', '{{round($costos_cosecha_x_tallo, 2)}}', rangos_costos_cosecha_tallo, true);
    render_gauge('canvas_postcosecha_x_tallo', '{{round($costos_postcosecha_x_tallo, 2)}}', rangos_costos_postcosecha_tallo, true);
    render_gauge('canvas_total_x_tallo', '{{round($costos_total_x_tallo, 2)}}', rangos_costos_total_tallo, true);

    construir_char('Campo/ha/semana', 'chart_campo_ha_semana');
    construir_char('Cosecha x Tallo', 'chart_cosecha_x_tallo');
    construir_char('Postcosecha x Tallo', 'chart_postcosecha_x_tallo');
    construir_char('Costo Total x Tallo', 'chart_total_x_tallo');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($indicadores_4_semanas as $pos_l => $item)
        labels.push('{{$item->semana}}');
        if (label == 'Campo/ha/semana')
            data_list.push('{{$item->campo_ha_semana}}');
        if (label == 'Cosecha x Tallo')
            data_list.push('{{round($item->cosecha_x_tallo * 100, 2)}}');
        if (label == 'Postcosecha x Tallo')
            data_list.push('{{round($item->postcosecha_x_tallo * 100, 2)}}');
        if (label == 'Costo Total x Tallo')
            data_list.push('{{round($item->costo_total_x_tallo * 100, 2)}}');
        @endforeach

            datasets = [{
            label: label + ' ',
            data: data_list,
            //backgroundColor: '#8c99ff54',
            borderColor: 'black',
            borderWidth: 2,
            fill: false,
        }];

        ctx = document.getElementById(id).getContext('2d');
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                },
                elements: {
                    line: {
                        tension: 0, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'point' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function () {
                    },
                    onHover: function () {
                    },
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round',    // "butt" || "round" || "square"
            }
        });
    }

    function mostrar_ocultar_tab(id) {
        $('.li-default').removeClass('active');
        $('#li-' + id).addClass('active');
        $('.default').addClass('hidden');
        $('#tab-' + id).removeClass('hidden');
    }

    setTimeout(function () {
        $('.ocultar').addClass('hidden');
    }, 1)
</script>