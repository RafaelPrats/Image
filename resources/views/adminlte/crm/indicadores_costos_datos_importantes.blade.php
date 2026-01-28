<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#tab-total" data-toggle="tab">Total</a></li>
        <li><a href="#tab-mo" data-toggle="tab">MO</a></li>
        <li><a href="#tab-insumos" data-toggle="tab">Insumos</a></li>
        <li><a href="#tab-fijos" data-toggle="tab">Fijos</a></li>
        <li><a href="#tab-regalias" data-toggle="tab">Regalías</a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="tab-total">
            <canvas id="chart_total" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-mo">
            <canvas id="chart_mo" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-insumos">
            <canvas id="chart_insumos" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-fijos">
            <canvas id="chart_fijos" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-regalias">
            <canvas id="chart_regalias" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        $('.default').removeClass('active');
    }, 1);

    construir_char('Total', 'chart_total');
    construir_char('MO', 'chart_mo');
    construir_char('Insumos', 'chart_insumos');
    construir_char('Fijos', 'chart_fijos');
    construir_char('Regalías', 'chart_regalias');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        @foreach($resumen_costos as $pos_l => $item)
        labels.push('{{$item->codigo_semana}}');
        if (label == 'MO')
            data_list.push('{{$item->mano_obra}}');
        if (label == 'Insumos')
            data_list.push('{{$item->insumos}}');
        if (label == 'Fijos')
            data_list.push('{{$item->fijos}}');
        if (label == 'Regalías')
            data_list.push('{{$item->regalias}}');
        if (label == 'Total')
            data_list.push('{{$item->mano_obra + $item->insumos + $item->fijos + $item->regalias}}');
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