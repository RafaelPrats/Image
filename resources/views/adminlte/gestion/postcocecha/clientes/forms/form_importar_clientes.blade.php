<form id="form_importar_clientes" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-12" style="margin-bottom: 10px">
            <button type="button" class="btn btn-primary btn-block" onclick="descarga_fomato_carga_clientes()">
                <i class="fa fa-download"></i>
                Descargar formato de carga de clientes
            </button>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="Archivo">Archivo</label>
                <input type="file"  id="file" name="file" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required>
            </div>
        </div>
    </div>
</form>
