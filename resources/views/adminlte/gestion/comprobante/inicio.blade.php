@extends('layouts.adminlte.master')

@section('titulo')
    Comprobantes
@endsection

@section('contenido')
    @include('adminlte.gestion.partials.breadcrumb')
    <section class="content">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Administración de comprobantes electrónicos
                </h3>
                @if(es_Server())
                <button class="btn btn-yura_primary pull-right" onclick="subir_archivos_xml()" style="color: white"
                        onmouseover="$('#title_upload').html('Subir xml')" onmouseleave="$('#title_upload').html('')">
                    <i class="fa fa-cloud-upload"></i>
                    <em  id="title_upload"></em>
                </button>
                @endif
            </div>

            <div class="box-body" id="div_content_comprobante">
                <table width="100%">
                    <tr>
                        <td>
                            <div class="form-group">
                                <label for="id_configuracion_empresa">Empresa</label><br/>
                                <select class="form-control input-yura_default" id="id_configuracion_empresa_comproante" style="width:180px"
                                        name="id_configuracion_empresa_comproante">
                                    @foreach($empresas as $empresa)
                                        <option value="{{$empresa->id_configuracion_empresa}}"> {{$empresa->nombre}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="anno">Cliente</label><br/>
                                <select class="form-control input-yura_default" id="id_cliente" name="id_cliente" style="width: 200px;">
                                    <option value=""> Seleccione</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{$cliente->id_cliente}}"> {{$cliente->nombre}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="anno">Tipo comprobante</label><br/>
                                <select class="form-control input-yura_default" id="codigo_comprobante" name="codigo_comprobante"
                                        style="width: 200px;">
                                    @foreach($tiposCompbantes->where('nombre', "FACTURA") as $tipoCompbante)
                                        <option  {{$tipoCompbante->nombre === "FACTURA" ? "selected" : ""}} value="{{$tipoCompbante->codigo}}">{{ucwords($tipoCompbante->nombre)}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="anno">Estado</label><br/>
                                <select class="form-control input-yura_default" id="estado" name="estado">
                                    <option value=""> Seleccione</option>
                                    <option value="0"> No firmados</option>
                                    <option value="1" selected> Generados</option>
                                    <option value="3"> Devueltos</option>
                                    <option value="4"> Rechazados</option>
                                    <option value="5"> Aprobados por el SRI</option>
                                    <option value="6"> Anuladas</option>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label> Desde</label><br/>
                                <input type="date" class="form-control input-yura_default" id="desde" name="desde" style="width:150px"
                                       value="{{\Carbon\Carbon::now()->toDateString()}}">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label> Hasta </label><br/>
                                <input type="date" class="form-control input-yura_default" id="hasta" name="hasta" style="width:150px"
                                       value="{{\Carbon\Carbon::now()->toDateString()}}">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label style="visibility: hidden;"> .</label><br/>
                                <span class="">
                                    <button class="btn btn-yura_primary" onclick="buscar_listado_comprobante()" title="Buscar">
                                        <i class="fa fa-fw fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
                <div id="div_listado_comprobante"></div>
            </div>
        </div>
    </section>
@endsection
@section('script_final')
    @include('adminlte.gestion.comprobante.script')
@endsection
