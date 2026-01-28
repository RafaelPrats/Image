<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active li-default" id="li-precio_x_tallo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('precio_x_tallo')">Precio x Tallo</a>
        </li>
        <li class="li-default" id="li-precio_x_ramo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('precio_x_ramo')">Precio x Ramo</a>
        </li>
        <li class="li-default" id="li-productividad">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('productividad')">Productividad</a>
        </li>
        <li class="li-default" id="li-calibre">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('calibre')">Calibre</a>
        </li>
        <li class="li-default" id="li-tallos_m2">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('tallos_m2')">Tallos x m<sup>2</sup></a>
        </li>
        <li class="li-default" id="li-ciclo">
            <a href="javascript:void(0)" onclick="mostrar_ocultar_tab('ciclo')">Ciclo</a>
        </li>
    </ul>
    <div>
        <div class="default" id="tab-precio_x_tallo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_precio_x_tallo"></canvas>
                    <strong>
                        ${{round($precio_x_tallo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_precio_x_tallo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-precio_x_ramo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_precio_x_ramo"></canvas>
                    <strong>
                        {{round($precio_x_ramo, 2)}}%
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_precio_x_ramo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-productividad">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_productividad"></canvas>
                    <strong>
                        {{round($productividad, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_productividad" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-calibre">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_calibre"></canvas>
                    <strong>
                        {{round($calibre, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_calibre" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-tallos_m2">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_tallos_m2"></canvas>
                    <strong>
                        {{number_format($tallos_m2, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_tallos_m2" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
        <div class="default ocultar" id="tab-ciclo">
            <div class="row">
                <div class="col-md-3 text-center">
                    <canvas id="canvas_ciclo"></canvas>
                    <strong>
                        {{number_format($ciclo, 2)}}
                    </strong>
                </div>
                <div class="col-md-9">
                    <canvas id="chart_ciclo" width="100%" height="25" style="margin-top: 5px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var rangos_precio_ramo = [];
    @foreach(getIntervalosIndicador('D3') as $r)
    rangos_precio_ramo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_ramos_m2_anno = [];
    @foreach(getIntervalosIndicador('D8') as $r)
    rangos_ramos_m2_anno.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_calibre = [];
    @foreach(getIntervalosIndicador('D1') as $r)
    rangos_calibre.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_tallos_m2 = [];
    @foreach(getIntervalosIndicador('D12') as $r)
    rangos_tallos_m2.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_ciclo = [];
    @foreach(getIntervalosIndicador('DA1') as $r)
    rangos_ciclo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
            @endforeach

    var rangos_precio_tallo = [];
    @foreach(getIntervalosIndicador('D14') as $r)
    rangos_precio_tallo.push({
        desde: parseFloat('{{$r->desde}}'),
        hasta: parseFloat('{{$r->hasta}}'),
        color: '{{$r->color}}',
    });
    @endforeach

    render_gauge('canvas_precio_x_tallo', '{{round($precio_x_tallo, 2)}}', rangos_precio_tallo, true);
    render_gauge('canvas_precio_x_ramo', '{{round($precio_x_ramo, 2)}}', rangos_precio_ramo, true);
    render_gauge('canvas_productividad', '{{round($productividad, 2)}}', rangos_ramos_m2_anno, true);
    render_gauge('canvas_calibre', '{{round($calibre, 2)}}', rangos_calibre, true);
    render_gauge('canvas_tallos_m2', '{{round($tallos_m2, 2)}}', rangos_tallos_m2, true);
    render_gauge('canvas_ciclo', '{{round($ciclo, 2)}}', rangos_ciclo, true);

    construir_char('Precio x Tallo', 'chart_precio_x_tallo');
    construir_char('Precio x Ramo', 'chart_precio_x_ramo');
    construir_char('Productividad', 'chart_productividad');
    construir_char('Calibre', 'chart_calibre');
    construir_char('Tallos m2', 'chart_tallos_m2');
    construir_char('Ciclo', 'chart_ciclo');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($indicadores_4_semanas as $pos_l => $item)
        labels.push('{{$item->semana}}');
        if (label == 'Precio x Tallo')
            data_list.push('{{$item->precio_x_tallo}}');
        if (label == 'Precio x Ramo')
            data_list.push('{{$item->precio_x_ramo}}');
        if (label == 'Productividad')
            data_list.push('{{$item->productividad}}');
        if (label == 'Calibre')
            data_list.push('{{$item->calibre}}');
        if (label == 'Tallos m2')
            data_list.push('{{$item->tallos_m2}}');
        if (label == 'Ciclo')
            data_list.push('{{$item->ciclo}}');
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