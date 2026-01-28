<form id="form_add_dato_exportacion" name="form_add_dato_exportacion" novalidate>
    <div class="col-md-12 text-right">
        <button type="button" class="btn btn-danger btn-xs hide">
            <i class="fa fa-minus" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-primary btn-xs" onclick="add_input_dato_exportacion()">
            <i class="fa fa-plus" aria-hidden="true"></i>
        </button>
    </div>
    <table width="100" class="table table-responsive" style="border:none">
        <tbody id="tbody_dato_exportacion">
            @if(isset($dato_exportacion))
                <tr>
                    <td style="border:none">
                        <input type="hidden" value="{{isset($dato_exportacion->id_dato_exportacion) ? $dato_exportacion->id_dato_exportacion : ""}}" id="id_dato_exportacion_1">
                        <label for="nombre">Nombre para el dato de exportación</label>
                        <input type="text" id="nombre" name="nombre" class="form-control nombre_dato_exportacion" minlength="2" required
                            onkeypress="return guion_bajo_string(this,event)" value="{{isset($dato_exportacion->nombre) ? $dato_exportacion->nombre : ""}}">
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</form>


