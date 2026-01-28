@php
    $nCaja = 1;
@endphp

@foreach($pedido->detalles as $det_ped)

    @php

        $marcacionDetail = $detallePedidoDatoExportacion::join('dato_exportacion as de','detallepedido_datoexportacion.id_dato_exportacion','de.id_dato_exportacion')
        ->where([
            ['detallepedido_datoexportacion.id_detalle_pedido',$det_ped->id_detalle_pedido],
            ['de.nombre','DETAILS']
        ])->select('detallepedido_datoexportacion.valor')->first();

        $cantidad = $det_ped->cantidad;

    @endphp

    @foreach(range(1, $cantidad) as $caja)

        <table style="width: 100%" cellspacing="0" cellspadding="0">
            <tr>
                <td style="text-align: center">
                    <img src="{{public_path('images/Logo-Senae.jpg')}}"  width="60px">
                </td>
            </tr>
            <tr>
                <td style="text-align: center;font-size: 10px">
                    <div class="bar">
                        {!!$barCode->getBarcode(isset($pedido->envios) && $pedido->envios[0]->dae !='' ? strtoupper($pedido->envios[0]->dae) : '1234567890',$barCode::TYPE_CODE_128,2)!!}
                    </div>
                    {{isset($pedido->envios) && $pedido->envios[0]->dae !='' ? strtoupper($pedido->envios[0]->dae) : '1234567890'}}
                </td>
            </tr>
            <tr>
                <td>
                    <span style="font-size:10px">País de destino: <b>{{isset($pedido->envios) &&  $pedido->envios[0]->pais != null ? $pedido->envios[0]->pais->nombre : 'SIN PAIS'}}</b></span>
                </td>
            </tr>
            @if($pedido->cliente->detalle()->nombre_empresa_etiqueta)
                <tr>
                    <td style="text-align: center" >
                        <div style="font-size: 10px"> <b> {{strtoupper($pedido->empresa->nombre)}}</b> </div>
                    </td>
                </tr>
            @endif
            <tr>
                <td></td>
            </tr>
            <tr>
                <td style="font-size: 10px">
                    <b>SOLD TO: {{strtoupper($pedido->cliente->detalle()->nombre)}}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px">
                    <b>CONSIGNEE: {{isset($pedido->envios) &&  $pedido->envios[0]->consignatario != null ? $pedido->envios[0]->consignatario->nombre : 'SIN CONSIGNATARIO'}}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px">
                    <b>MAWB: {{isset($pedido->envios) ? $pedido->envios[0]->guia_madre : 'SIN GUIA'}}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px">
                    <b>HAWB: {{isset($pedido->envios) ? $pedido->envios[0]->guia_hija : 'SIN GUIA'}}</b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px">
                    <b>PACK DETAIL:</b>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="width: 100%">
                        <tr>
                            <td style="width: 200px;font-size: 10px"><b>BOX#: {{$nCaja}}</b></td>
                            <td style="font-size: 10px"><b>Ord.:</b></td>
                            <td style="font-size: 10px">
                                @php
                                    switch(explode('|',$det_ped->cliente_especificacion->especificacion->especificacionesEmpaque[0]->empaque->nombre)[1]){
                                        case '0.25':
                                            $emp= 'QB';
                                            break;
                                        case '0.5':
                                            $emp= 'HB';
                                            break;
                                        case '0.125':
                                            $emp= 'EB';
                                            break;
                                        default:
                                            $emp='';
                                            break;
                                    }
                                @endphp
                                {{$emp}}
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
            <tr>
                <td>

                </td>
            </tr>
        </table>
        <table style="width: 100%;font-size: 10px" >
            <tr>
                <td class="border"><b>PRODUCTO</b></td>
                <td class="border"><b>STEM</b></td>
                <td class="border"><b>COLOR</b></td>
                <td class="border"><b>90</b></td>
                <td class="border"><b>80</b></td>
                <td class="border"><b>70</b></td>
                <td class="border"><b>60</b></td>
                <td class="border"><b>50</b></td>
            </tr>
            @foreach($det_ped->cliente_especificacion->especificacion->especificacionesEmpaque as  $esp_emp)

                @foreach($esp_emp->detalles as $det_esp_emp)

                    @php

                        $distribucionAssorted = \yura\Modelos\DistribucionMixtos::where('ramos','>',0)
                        ->where('fecha', opDiasFecha('-', 1, $pedido->fecha_pedido))
                        ->where('id_cliente', $pedido->id_cliente)
                        ->where('id_pedido', $pedido->id_pedido)
                        ->where('id_detalle_pedido', $det_ped->id_detalle_pedido)
                        ->where('id_detalle_especificacionempaque', $det_esp_emp->id_detalle_especificacionempaque)->get();

                        $L50 ='';
                        $L60 ='';
                        $L70 ='';
                        $L80 ='';
                        $L90 ='';

                        if($det_esp_emp->longitud_ramo == 50)
                            $L50 = $det_esp_emp->cantidad;

                        if($det_esp_emp->longitud_ramo == 60)
                            $L60 = $det_esp_emp->cantidad;

                        if($det_esp_emp->longitud_ramo == 70)
                            $L70 = $det_esp_emp->cantidad;

                        if($det_esp_emp->longitud_ramo == 80)
                            $L80 = $det_esp_emp->cantidad;

                        if($det_esp_emp->longitud_ramo == 90)
                            $L90 = $det_esp_emp->cantidad;
                    @endphp

                    @if($distribucionAssorted->count())
                        @foreach ($distribucionAssorted as $disAssorted)
                            <tr>
                                <td class="border"> {{$disAssorted->planta->nombre}} </td>
                                <td class="border"> {{$det_esp_emp->tallos_x_ramos}} </td>
                                <td class="border" style="width: 150px"> {{$disAssorted->variedad()->nombre}} - ASSORTED </td>
                                <td class="border">
                                    @if ($det_esp_emp->longitud_ramo== 90)
                                        {{$disAssorted->ramos}}
                                    @endif </td>
                                <td class="border">
                                    @if ($det_esp_emp->longitud_ramo== 80)
                                        {{$disAssorted->ramos}}
                                    @endif
                                </td>
                                <td class="border">
                                    @if ($det_esp_emp->longitud_ramo== 70)
                                        {{$disAssorted->ramos}}
                                    @endif
                                </td>
                                <td class="border">
                                    @if ($det_esp_emp->longitud_ramo== 60)
                                        {{$disAssorted->ramos}}
                                    @endif
                                </td>
                                <td class="border">
                                    @if ($det_esp_emp->longitud_ramo== 50)
                                        {{$disAssorted->ramos}}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="border"> {{$det_esp_emp->variedad->planta->nombre}} </td>
                            <td class="border"> {{$det_esp_emp->tallos_x_ramos}} </td>
                            <td class="border"> {{$det_esp_emp->variedad->nombre}} </td>
                            <td class="border"> {{$L90}} </td>
                            <td class="border"> {{$L80}} </td>
                            <td class="border"> {{$L70}} </td>
                            <td class="border"> {{$L60}} </td>
                            <td class="border"> {{$L50}} </td>
                        </tr>
                    @endif
                @endforeach

            @endforeach
            <tr><td></td></tr>
            <tr><td></td></tr>
            <tr>
                <td style="text-align: center">
                    <img src="{{public_path('images/logo_agro_calidad.png')}}"  width="100px">
                </td>
            </tr>
            <tr>
                <td><span style="margin-left:50px;font-size:10px">1790996743001.05050802</span></td>
            </tr>
        </table>
        @php
            $nCaja++;
        @endphp
        {{-- @if($caja < $det_ped->cantidad)
            <div style="page-break-after:always;"></div>
        @endif --}}

        <div style="page-break-after:always;"></div>
    @endforeach

@endforeach


<style>
    div.bar div{
        margin: 0 auto;
    }

    .border{
        border: 1px solid;
    }
    table{
        border-collapse: collapse
    }
</style>

