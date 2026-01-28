<div class="row">
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Dinero <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_dinero = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_dinero += $item->dinero;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            ${{ number_format($item->dinero, 2) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>${{ number_format($total_dinero, 2) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Precio x Tallo <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_precio = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_precio += round($item->dinero / $item->tallos, 2);
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            ${{ number_format($item->dinero / $item->tallos, 2) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>${{ number_format($total_precio / count($indicadores), 2) }}</strong>
                </a>
            </p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="div_indicadores border-radius_16" style="background-color: #30BBBB; margin-bottom: 5px">
            <legend class="text-center" style="font-size: 1.1em; margin-bottom: 5px; color: white">
                <strong>Tallos <sup>-4 semanas</sup></strong>
            </legend>
            <table style="width: 100%;">
                @php
                    $total_tallos = 0;
                @endphp
                @foreach ($indicadores as $item)
                    @php
                        $total_tallos += $item->tallos;
                    @endphp
                    <tr>
                        <th style="color: white">
                            {{ $item->semana }}
                        </th>
                        <th class="text-right">
                            {{ number_format($item->tallos) }}
                        </th>
                    </tr>
                @endforeach
            </table>
            <legend style="margin-bottom: 5px; color: white"></legend>
            <p class="text-center" style="margin-bottom: 0px">
                <a href="javascript:void(0)" class="text-center" style="color: white">
                    <strong>{{ number_format($total_tallos, 2) }}</strong>
                </a>
            </p>
        </div>
    </div>
</div>
