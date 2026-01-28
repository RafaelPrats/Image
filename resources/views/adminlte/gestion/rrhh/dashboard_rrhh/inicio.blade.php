@extends('layouts.adminlte.master')

@section('titulo')
    Dashboard - RRHH
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Recursos Humanos</small>
        </h1>
       <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active text-color_yura">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>

    </section>

    <section class="content">
        <div id="div_indicadores">
            @include('adminlte.crm.rendimiento_desecho.partials.indicadores')
        </div>

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <strong>Gráficahhs</strong>
                </h3>

              
        <div class="row">
            <div class="col-xs-2">
                <div class="input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-calendar-check-o"></i> Rango
                        </span>
                        <select name="rango" id="rango" class="form-control input-yura_default" onchange="select_rango($(this).val())">
                            <option value="A">Anual</option>
                            <option value="M">Mensual</option>
                            <option value="S" selected="">Semanal</option>
                        </select>
                    </div>
                </div>

               
            <div class="col-xs-2">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Rango
                    </span>
                    <select name="filtro_predeterminado_rango" id="filtro_predeterminado_rango"
                            onchange="filtrar_predeterminado()" class="form-control input-yura_default">
                        <option value="1">1 Mes</option>
                        <option value="2">3 Meses</option>
                        <option value="3">6 Meses</option>
                        <option value="4">1 Año</option>
                    </select>
                </div>
            </div>
                     <div class="col-xs-2">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">Criterio
                    </span>
                    <select name="filtro_predeterminado_criterio" id="filtro_predeterminado_criterio"
                            onchange="filtrar_predeterminado()" class="form-control input-yura_default">
                        <option value="R" selected>Rendimiento</option>
                        <option value="D">Desecho</option>
                    </select>
                        </div>
                    </div>


                   <div class="col-md-2">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </span>
                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades',
                    '<option value= selected>Todos los tipos</option>')">
                        <option value="">Todas las variedades</option>
                        @foreach(getPlantas() as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
                    
                    <div class="col-md-2">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </span>
                    <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default "
                            onchange="filtrar_predeterminado()">
                        <option value="" selected>Todos los tipos</option>
                    </select>
                </div>
            </div>
                        
                        <div class="col-md-2-3">
                    <div class="form-group input-group">
                        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark dropdown-toggle bg-gray" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                            <i class="fa fa-calendar-minus-o"></i> Años
                            <span class="caret"></span></span>
                        <ul class="dropdown-menu">
                            @foreach($annos as $a)
                                <li>
                                    <a href="javascript:void(0)" onclick="select_anno('{{$a}}')"
                                       class="li_anno" id="li_anno_{{$a}}">
                                        {{$a}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                   
                    <input type="text" class="form-control" placeholder="Años" id="filtro_predeterminado_annos"
                           name="filtro_predeterminado_annos" readonly>

                 <span class="input-group-btn">
                    <div class="input-group-btn">
                         <button type="button" class="btn btn-yura_primary"onclick="filtrar_predeterminado()" title="Buscar">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </div>
                    </span>
                      </div>
                              </div>
                          </div>


  
            <div class="box-body">
                <div class="row">
                    <div class="col-md-9" id="div_graficas"></div>
                    <div class="col-md-3" id="div_today">
                        @include('adminlte.crm.rendimiento_desecho.partials.today')
                    </div>
                </div>
            </div>
        
    </section>
@endsection

@section('script_final')
    {{-- JS de Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>

    @include('adminlte.crm.rendimiento_desecho.script')
@endsection