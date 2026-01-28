<button type="button" class="btn btn-xs btn-yura_default pull-right" style="color: black" onclick="admin_colores()">
    <i class="fa fa-fw fa-gears"></i> Administrar Colores
</button>
<legend class="text-left text-white" style="margin-bottom: 5px">
    Manual de Usuario
</legend>
<b>PEDIDOS</b>
<br>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/1.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<br>
<b>1- FILTRO y BOTON DE AGREGAR PEDIDO:</b>
<br>
<small>
    <b>NOTA: </b>
    <em>Puedes usar el filtro para obtener los resultados deseados usando todas las opciones disponibles.</em>
    <br>
    <b>FORMULARIO DE NUEVO PEDIDO: </b>
    <em>El formulario para crear un nuevo pedido esta compuesto por un encabezado con Fecha, Cliente, Consignatario,
        Agencia y Tipo del Pedido. Cuenta también con un listado de todas las especificaciones del cliente seleccionado,
        para luego agregarlo a la Sección "Contenido del pedido". Y por último está un resumen de los totales del
        pedido.</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/2.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<br>
<small>
    <b>TIPO DE PEDIDO STANDING ORDER: </b>
    <em>Para crear un PEDIDO de tipo STANDING ORDER, debes seleccionar en el encabezado, el tipo STANDING ORDER. Este
        habilitará un nuevo sub-formulario para escoger las fechas en las que se repetirá el STANDING ORDER</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/3.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<br>
<b>2- LISTADO DE PEDIDOS:</b>
<br>
<small>
    <b>NOTA: </b>
    <em>En este listado cada pedido tiene un grupo de ACCIONES que serviran tanto para administrar los pedidos, como
        para obtener documentos correspondientes al pedido.</em>
    <img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/4.jpg') }}" alt=""
        style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
    <b>EDITAR PEDIDO: </b>
    <br>
    <em>El formulario para EDITAR un pedido, es similar al formulario de CREACION de un NUEVO PEDIDO.</em>
    <br>
    <em>Si el pedido es de tipo STANDING ORDER (1), en el formulario de EDITAR pedido, aparecerá un boton para
        ACTUALIZAR ORDEN FIJA (2).</em>
    <br>
    <em>El sub-formulario para ACTUALIZAR la ORDEN FIJA, permitira a travez de un CHECK (3) por cada fecha siguiente de
        la ORDEN FIJA, indicarle al sistema cuál o cuáles seran las fechas futuras a las que deben aplicar la
        actualización (MODIFICACION) del pedido seleccionado.</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/7.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<br>
<small>
    <b>PRE FACTURA y DESCARGAR PACKING LIST: </b>
    <br>
    <em>Estas dos opciones descargarán un pdf correspondiente a cada documento.</em>
</small>
<br>
<small>
    <b>CANCELAR PEDIDO: </b>
    <br>
    <em>Antes de CANCELAR un pedido, debes seleccionar que tipo de CANCELACION deseas hacer.</em>
    <br>
    <em>(1)Una cancelacion de tipo PÉRDIDA significa que la venta es considerada como una pérdida.</em>
    <br>
    <em>(2)Una cancelacion de tipo CANCELADO significa que la venta es considerada como una cancelación por parte del
        cliente.</em>
    <br>
    <em>(3)Una cancelacion de tipo ELIMINAR significa que deseas eliminar del sistema y base de datos dicho pedido.</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/5.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<small>
    <b>COPIAR PEDIDO: </b>
    <br>
    <em>Para copiar un pedido debe indicar las fechas en las que se crearan una COPIA del pedido seleccionado.</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/6.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<small>
    <b>OPCIONES UNICAS EN PEDIDOS TIPO STANDING ORDER: </b>
    <br>
    <em><b>MOVER FECHAS DE ORDEN DIJA:</b> esta es una opcion para cambiar de fechas los pedidos deseados pertenecientes
        a una ORDEN FIJA.</em>
    <br>
    <em><b>CANCELAR TODA LA ORDEN DIJA:</b> esta opcion es para cancelar todos los pedidos incluyendo los siguientes
        pedidos correspondiente a una ORDEN FIJA.</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/9.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
<b>3- OPCIONES DEL MODULO DE PEDIDOS:</b>
<br>
<small>
    <b>NOTA: </b>
    <em>Para usar algunas de estas opciones primero debes seleccionar con un CHECK los pedidos relacionados a la opcion
        deseada.</em>
    <br>
    <b>IMPORTAR AL JIRE: </b>
    <br>
    <em>Este botón descargará un archivo excel con las ventas para importarlo al sistema JIRE</em>
    <br>
    <b>GENERAR PACKINGS: </b>
    <br>
    <em>Este botón generará los numeros consecutivos correspondientes al packing, en aquellos pedidos seleccionados con
        un CHECK activo</em>
    <br>
    <b>DESCARGAR PACKINGS: </b>
    <br>
    <em>Este botón descargará un archivo excel con los packings, de aquellos pedidos seleccionados con
        un CHECK activo</em>
    <br>
    <b>COMBINAR PEDIDOS: </b>
    <br>
    <em>Este botón creará un pedido único unificado, con aquellos pedidos seleccionados con
        un CHECK activo</em>
    <br>
    <b>FLOR POSCO: </b>
    <br>
    <em>Este botón descargará un archivo excel con el desglose de flor, de todos los pedidos del listado</em>
    <br>
    <b>EXPORTAR A EXCEL: </b>
    <br>
    <em>Este botón descargará un archivo excel con el desglose de ventas, de todos los pedidos del listado</em>
</small>
<img src="{{ url('images/MANUAL/COMERCIALIZACION/PEDIDOS/8.jpg') }}" alt=""
    style="width: 100%; margin-bottom: 10px" class="sombra_estandar">
