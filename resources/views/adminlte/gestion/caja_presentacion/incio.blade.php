@extends('layouts.adminlte.master')

@section('titulo')
    Cajas y presentaciones
@endsection

@section('contenido')
    @include('adminlte.gestion.partials.breadcrumb')
    <section class="content">
        <div class="text-right">
            <div class="btn-group">
                <button class="btn btn-yura_dark" onclick="add_empaque()">
                    <i class="fa fa-plus" aria-hidden="true"></i> Agregar empaque
                </button>
                <button class="btn btn-yura_primary" onclick="exportar_detalle_empaque()">
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Exportar excel de detalles de empaques
                </button>
                <button class="btn btn-yura_default" onclick="form_add_detalle_empaque()">
                    <i class="fa fa-upload" aria-hidden="true"></i> Importar Excel de detalles de empaques
                </button>
            </div>
        </div>

        <div class="box-body" id="div_content_caja_presentacion">
            <div id="div_listado_empaque"></div>
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }
    </style>
@endsection
@section('script_final')
    @include('adminlte.gestion.caja_presentacion.script')
@endsection
