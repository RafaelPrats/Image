<div class="row">
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Cosecha <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_cosecha = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_cosecha += $item['cosecha'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['cosecha']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_cosecha) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Postcosecha <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_postcosecha = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_postcosecha += $item['postcosecha'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['postcosecha']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_postcosecha) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Flor de Baja <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_basura = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_basura += $item['basura'];
                    @endphp
                    <tr>
                        <th style="color: white">
                            Semana: {{ $item['semana']->codigo }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item['basura']) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_basura) }}</strong>
                </a>
            </p>
        </div>
    </div>
</div>
