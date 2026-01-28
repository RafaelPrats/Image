<?php
Route::get('clientes/listar_pedidos', 'PedidoController@listar_pedidos');
Route::get('clientes/ver_pedidos', 'PedidoController@ver_pedidos');
Route::get('clientes/add_pedido', 'PedidoController@add_pedido');
Route::post('clientes/store_pedidos', 'PedidoController@store_pedidos');
Route::get('clientes/inputs_pedidos', 'PedidoController@inputs_pedidos');
Route::get('clientes/form_eliminar_detalle_pedido_masivo', 'PedidoController@form_eliminar_detalle_pedido_masivo');
Route::post('clientes/eliminar_detalle_pedido_masivo', 'PedidoController@eliminar_detalle_pedido_masivo');
Route::get('clientes/inputs_pedidos_edit', 'PedidoController@inputs_pedidos_edit');
Route::post('clientes/store_especificacion_pedido', 'PedidoController@store_especificacion_pedido');
Route::get('clientes/actualizar_estado_pedido_detalle', 'PedidoController@actualizar_estado_pedido_detalle');
Route::post('clientes/cancelar_pedido', 'PedidoController@cancelar_pedido');
Route::get('clientes/opcion_pedido_fijo', 'PedidoController@opcion_pedido_fijo');
Route::get('clientes/add_fechas_pedido_fijo_personalizado', 'PedidoController@add_fechas_pedido_fijo_personalizado');
Route::get('clientes/buscar_saldos', 'YuraController@buscar_saldos');
Route::get('clientes/buscar_codigo_venture', 'PedidoController@buscar_codigo_venture');
Route::get('pedidos/facturar_pedido', 'PedidoController@facturar_pedido');
Route::get('pedidos/ver_factura_pedido/{id_pedido}', 'PedidoController@ver_factura_pedido');
Route::get('pedidos/desglose_pedido/{id_pedido}', 'PedidoController@desglose_pedido');
//URL PARA QUE LA FACTURACION FUNCIONE CON EL VENTURE
Route::get('pedidos/documento_pre_factura/{secuencial}/{cliente?}', 'ComprobanteController@ver_pre_factura_bd');
Route::post('pedidos/cambia_tipo_pedido','PedidoController@cambia_tipo_pedido');
Route::get('pedidos/modificar_comprobante','PedidoController@modificar_comprobante');
Route::post('pedidos/update_comprobante','PedidoController@update_comprobante');
Route::get('pedidos/editar_combo','PedidoController@editar_combo');
Route::post('pedidos/store_det_esp','PedidoController@store_det_esp');
Route::post('pedidos/update_det_esp','PedidoController@update_det_esp');
Route::post('pedidos/delete_det_esp','PedidoController@delete_det_esp');
Route::post('pedidos/update_orden_fija','PedidoController@update_orden_fija');
Route::post('pedidos/delete_orden_fija','PedidoController@delete_orden_fija');
Route::get('pedidos/mover_fecha_orden_fija','PedidoController@mover_fecha_orden_fija');
Route::post('pedidos/store_mover_fechas','PedidoController@store_mover_fechas');
Route::get('pedidos/copiar_pedido','PedidoController@copiar_pedido');
Route::post('pedidos/store_copiar_pedido','PedidoController@store_copiar_pedido');

Route::get('pedidos/agregar_pedido', 'PedidoController@agregar_pedido');
Route::post('pedidos/form_seleccionar_cliente', 'PedidoController@form_seleccionar_cliente');
Route::post('pedidos/form_seleccionar_planta', 'PedidoController@form_seleccionar_planta');
Route::get('pedidos/buscar_form_especificaciones', 'PedidoController@buscar_form_especificaciones');
Route::get('pedidos/agregar_combos_pedido', 'PedidoController@agregar_combos_pedido');
Route::get('pedidos/cargar_opciones_orden_fija', 'PedidoController@cargar_opciones_orden_fija');
Route::post('pedidos/grabar_pedido', 'PedidoController@grabar_pedido');

Route::get('pedidos/modificar_pedido', 'PedidoController@modificar_pedido');
Route::post('pedidos/update_pedido', 'PedidoController@update_pedido');
Route::post('pedidos/borrar_detalle_pedido', 'PedidoController@borrar_detalle_pedido');
Route::get('pedidos/duplicar_contenido_pedido', 'PedidoController@duplicar_contenido_pedido');
Route::post('pedidos/generar_packings', 'PedidoController@generar_packings');
Route::get('pedidos/obtener_historial_orden_fija', 'PedidoController@obtener_historial_orden_fija');
Route::post('pedidos/combinar_pedidos', 'PedidoController@combinar_pedidos');
Route::post('pedidos/separar_pedido', 'PedidoController@separar_pedido');
Route::post('pedidos/edit_seleccionar_planta', 'PedidoController@edit_seleccionar_planta');

Route::get('pedidos/ver_resumen', 'PedidoController@ver_resumen');
