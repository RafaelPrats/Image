@extends('layouts.adminlte.master')

@section('titulo')
    Importar Data
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Importar venta semanal
            <small class="text-color_yura">módulo de comercialización</small>
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
        <form id="form-importar_file" action="{{url('importar_ventas/importar_file')}}" method="POST">
            {!! csrf_field() !!}
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed" style="background-color: #e9ecef">
                    Archivo
                </div>
                <input type="file" id="file_importar" name="file_importar" required class="form-control input-group-addon"
                       accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">

                <div class="input-group-btn">
                    <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                        <i class="fa fa-fw fa-download"></i> Descargar plantilla
                    </button>
                    <button type="button" class="btn btn-yura_primary" onclick="importar_file()">
                        <i class="fa fa-fw fa-upload"></i> Importar archivo
                    </button>
                </div>
            </div>
        </form>
    </section>
@endsection

<script>
    function importar_file() {
        if ($('#form-importar_file').valid()) {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_file');
            var formData = new FormData(formulario[0]);
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('importar_ventas')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {
                            //location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }
    }

    function descargar_plantilla() {
        $.LoadingOverlay('show');
        window.open('{{url('importar_ventas/descargar_plantilla')}}', '_blank');
        $.LoadingOverlay('hide');
    }
</script>