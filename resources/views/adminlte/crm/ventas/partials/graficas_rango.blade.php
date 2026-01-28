<div class="nav-tabs-custom" style="cursor: move;">
    <!-- Tabs within a box -->
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#dinero-chart" data-toggle="tab" aria-expanded="false">
                Dinero
            </a>
        </li>
        </li>
        <li class="">
            <a href="#tallos-chart" data-toggle="tab" aria-expanded="true">
                Tallos
            </a>
        </li>
        <li class="">
            <a href="#precio-chart" data-toggle="tab" aria-expanded="true">
                Precio x Tallo
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="dinero-chart" style="position: relative">
            <canvas id="chart_dinero" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="ramos-chart" style="position: relative">
            <canvas id="chart_ramos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="tallos-chart" style="position: relative">
            <canvas id="chart_tallos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="precio-chart" style="position: relative">
            <canvas id="chart_precio" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    construir_char('Dinero', 'chart_dinero');
    construir_char('Tallos', 'chart_tallos');
    construir_char('Precios', 'chart_precio');

    function construir_char(label, id) {
        labels = [];
        datasets = [];
        data_list = [];
        data_tallos = [];
        @for ($i = 0; $i < count($labels); $i++)
            labels.push("{{ $labels[$i] }}");
            if (label == 'Dinero')
                data_list.push("{{ round($data[$i]->dinero, 2) }}");
            else if (label == 'Tallos')
                data_list.push("{{ $data[$i]->tallos }}");
            else if (label == 'Precios')
                data_list.push("{{ $data[$i]->tallos > 0 ? round($data[$i]->dinero / $data[$i]->tallos, 2) : 0 }}");
        @endfor

        datasets = [{
            label: label + ' ',
            data: data_list,
            //backgroundColor: '#8c99ff54',
            borderColor: 'black',
            borderWidth: 1,
            fill: {{ $fill_grafica }},
        }];

        ctx = document.getElementById(id).getContext('2d');
        myChart = new Chart(ctx, {
            type: '{{ $tipo_grafica }}',
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
                        tension: 0.2, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'point' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function() {},
                    onHover: function() {},
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round', // "butt" || "round" || "square"
            }
        });
    }
</script>
