<?php

Route::get('clientes/admin_especificaciones', 'EspecificacionClienteController@admin_especificaciones');
Route::get('clientes/ver_especificacion', 'EspecificacionClienteController@ver_especificacion');
Route::get('clientes/add_especificacion', 'EspecificacionClienteController@add_especificacion');
Route::get('clientes/cargar_form_especificacion_empaque', 'EspecificacionClienteController@cargar_form_especificacion_empaque');
Route::get('clientes/cargar_form_detalle_especificacion_empaque', 'EspecificacionClienteController@cargar_form_detalle_especificacion_empaque');
Route::post('clientes/store_especificacion', 'EspecificacionClienteController@store_especificacion');
Route::get('clientes/listar_especificaciones', 'EspecificacionClienteController@listar_especificaciones');
Route::get('clientes/ver_especificaciones', 'EspecificacionClienteController@ver_especificaciones');
Route::post('clientes/update_especificaciones', 'EspecificacionClienteController@update_especificaciones');
Route::post('clientes/asignar_especificacion', 'EspecificacionClienteController@asignar_especificacion');
Route::get('clientes/obtener_calsificacion_ramos', 'EspecificacionClienteController@obtener_calsificacion_ramos');
Route::post('clientes/eliminar_especificaciones_masivamente', 'EspecificacionClienteController@eliminar_especificaciones_masivamente');
Route::post('clientes/actualizar_especificaciones_masivamente', 'EspecificacionClienteController@actualizar_especificaciones_masivamente');
Route::get('clientes/get_variedades_by_planta', 'EspecificacionClienteController@getVariedadesByPlanta');
Route::post('clientes/eliminar_detalle_pedido', 'EspecificacionClienteController@eliminar_detalle_pedido');
Route::get('clientes/get_logintud_especificacion_combo', 'EspecificacionClienteController@getLongitudEspecificacionCombo');
Route::get('clientes/set_presentacion_combo', 'EspecificacionClienteController@setPresentacionCombo');
Route::get('clientes/get_variedades_by_planta_editar_pedido', 'EspecificacionClienteController@get_variedades_by_planta_editar_pedido');
Route::post('clientes/crear_detalle_pedido_edicion', 'EspecificacionClienteController@crear_detalle_pedido_edicion');
Route::post('clientes/actualizar_detalle_pedido_edicion', 'EspecificacionClienteController@actualizar_detalle_pedido_edicion');
