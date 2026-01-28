<div class="nav-tabs-custom" style="cursor: move;">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#dinero-chart" data-toggle="tab" aria-expanded="true">Dinero</a></li>
        <li class=""><a href="#tallos-chart" data-toggle="tab" aria-expanded="false">Tallos</a></li>
        <li class=""><a href="#precio-chart" data-toggle="tab" aria-expanded="false">Precio x Tallo</a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="dinero-chart" style="position: relative">
            <canvas id="chart_annos_dinero" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="tallos-chart" style="position: relative">
            <canvas id="chart_annos_tallos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="precio-chart" style="position: relative">
            <canvas id="chart_annos_precio" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>


<script>
    construir_char_annos('Dinero', 'chart_annos_dinero');
    construir_char_annos('Tallos', 'chart_annos_tallos');
    construir_char_annos('Precio x Tallo', 'chart_annos_precio');

    function construir_char_annos(label, id) {
        labels = [];
        datasets = [];
        @foreach ($labels as $label)
            labels.push("{{ $label }}");
        @endforeach

        @foreach ($data as $pos_a => $a)
            data_list = [];
            @foreach ($a['valores'] as $item)
                if (label == 'Dinero')
                    data_list.push("{{ round($item->dinero, 2) }}");
                if (label == 'Tallos')
                    data_list.push("{{ $item->tallos }}");
                if (label == 'Precio x Tallo')
                    data_list.push("{{ round($item->dinero / $item->tallos, 2) }}");
            @endforeach

            datasets.push({
                label: '{{ $a['anno'] }}' + ' ',
                data: data_list,
                backgroundColor: '{{ getListColores()[$pos_a] }}',
                borderColor: '{{ getListColores()[$pos_a] }}',
                borderWidth: 1,
                fill: false,
            });
        @endforeach

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
