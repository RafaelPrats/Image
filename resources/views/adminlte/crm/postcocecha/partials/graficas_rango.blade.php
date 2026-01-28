<div class="nav-tabs-custom" style="cursor: move;">
    <!-- Tabs within a box -->
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#cosecha-chart" data-toggle="tab" aria-expanded="false">
                Cosecha
            </a>
        </li>
        <li class="">
            <a href="#postcosecha-chart" data-toggle="tab" aria-expanded="true">
                Postcosecha
            </a>
        </li>
        <li class="">
            <a href="#desecho-chart" data-toggle="tab" aria-expanded="true">
                Flor de Baja
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="cosecha-chart" style="position: relative">
            <canvas id="chart_cosecha" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="postcosecha-chart" style="position: relative">
            <canvas id="chart_postcosecha" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="desecho-chart" style="position: relative">
            <canvas id="chart_desecho" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    construir_char('Cosecha', 'chart_cosecha');
    construir_char('Postcosecha', 'chart_postcosecha');
    construir_char('Flor de Baja', 'chart_desecho');

    function construir_char(label, id) {
        labels = [];
        datasets = [];
        data_list = [];
        data_tallos = [];
        if (label == 'Cosecha') {
            @for ($i = 0; $i < count($labels_cosecha); $i++)
                @if ($rango == 'S')
                    labels.push("{{ $labels_cosecha[$i]->codigo }}");
                @else
                    labels.push("{{ $labels_cosecha[$i] }}");
                @endif
                data_list.push("{{ $data_cosecha[$i]->cantidad }}");
            @endfor
        } else if (label == 'Postcosecha') {
            @for ($i = 0; $i < count($labels_postcosecha); $i++)
                @if ($rango == 'S')
                    labels.push("{{ $labels_postcosecha[$i]->codigo }}");
                @else
                    labels.push("{{ $labels_postcosecha[$i] }}");
                @endif
                data_list.push("{{ $data_postcosecha[$i]->cantidad }}");
            @endfor
        } else if (label == 'Flor de Baja') {
            @for ($i = 0; $i < count($labels_desecho); $i++)
                @if ($rango == 'S')
                    labels.push("{{ $labels_desecho[$i]->codigo }}");
                @else
                    labels.push("{{ $labels_desecho[$i] }}");
                @endif
                data_list.push("{{ $data_desecho[$i]->cantidad }}");
            @endfor
        }

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
