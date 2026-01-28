<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#tab-area" data-toggle="tab">Área <sup>ha</sup></a></li>
        <li><a href="#tab-venta" data-toggle="tab">Venta</a></li>
        <li><a href="#tab-tallos_cosechados" data-toggle="tab">Tallos cosechados</a></li>
        <li><a href="#tab-tallos_clasificados" data-toggle="tab">Tallos clasificados</a></li>
        <li><a href="#tab-cajas_exportadas" data-toggle="tab">Cajas exportadas</a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="tab-area">
            <canvas id="chart_area" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-venta">
            <canvas id="chart_venta" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-tallos_cosechados">
            <canvas id="chart_tallos_cosechados" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-tallos_clasificados">
            <canvas id="chart_tallos_clasificados" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-cajas_exportadas">
            <canvas id="chart_cajas_exportadas" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        $('.default').removeClass('active');
    }, 1);

    construir_char('Área', 'chart_area');
    construir_char('Venta', 'chart_venta');
    construir_char('Tallos cosechados', 'chart_tallos_cosechados');
    construir_char('Tallos clasificados', 'chart_tallos_clasificados');
    construir_char('Cajas exportadas', 'chart_cajas_exportadas');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($indicadores_4_semanas as $pos_l => $item)
        labels.push('{{$item->semana}}');
        if (label == 'Área')
            data_list.push('{{round($item->area, 2)}}');
        if (label == 'Venta')
            data_list.push('{{round($item->venta, 2)}}');
        if (label == 'Tallos cosechados')
            data_list.push('{{$item->tallos_cosechados}}');
        if (label == 'Tallos clasificados')
            data_list.push('{{$item->tallos_clasificados}}');
        if (label == 'Cajas exportadas')
            data_list.push('{{$item->cajas_exportadas}}');
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

</script>