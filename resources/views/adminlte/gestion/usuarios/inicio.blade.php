@extends('layouts.adminlte.master')

@section('titulo')
    Usuarios
@endsection

@section('script_inicio')
    {{--<script src="{{url('js/portada/login.js')}}"></script>--}}

    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/jsbn.js')}}"></script>
    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/jsbn2.js')}}"></script>
    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/prng4.js')}}"></script>
    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/rng.js')}}"></script>
    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/rsa.js')}}"></script>
    <script language="JavaScript" type="text/javascript" src="{{url('js/rsa/rsa2.js')}}"></script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Usuarios
            <small class="text-color_yura">módulo de administrador</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>
            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="div_content_usuarios">
            <table width="100%">
                <tr>
                    <td class="text-right">
                        <div class="btn-group" style="padding: 0px">
                            <button class="btn btn-yura_dark" onclick="buscar_listado()">
                                <i class="fa fa-fw fa-refresh"></i> Refrescar
                            </button>
                            @if(es_server())
                                <button class="btn btn-yura_default" onclick="add_usuario()">
                                    <i class="fa fa-fw fa-plus"></i> Añadir
                                </button>
                            @endif
                            <button class="btn btn-yura_primary" onclick="exportar_usuarios()">
                                <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                            </button>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="div_listado_usuarios" style="margin-top: 10px"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.usuarios.script')
@endsection
