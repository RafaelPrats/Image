<?php

Route::get('especificacion', 'EspecificacionController@inicio');
Route::get('especificacion/listado', 'EspecificacionController@listado_especificaciones');
Route::get('especificacion/form_asignacion_especificacion', 'EspecificacionController@form_asignacion_especificacion');
Route::get('especificacion/store_asignacion_especificacion', 'EspecificacionController@sotre_asignacion_especificacion');
Route::get('especificacion/verificar_pedido_especificacion', 'EspecificacionController@verificar_pedido_especificacion');
Route::get('especificacion/delete_asignacion_especificacion', 'EspecificacionController@delete_asignacion_especificacion');
Route::get('especificacion/add_row_especificacion', 'EspecificacionController@nueva_especificacion');
Route::post('especificacion/store_row_especificacion', 'EspecificacionController@store_row_especificacion');
Route::post('especificacion/delete_row_especificacion', 'EspecificacionController@delete_row_especificacion');
Route::post('especificacion/actualizar_row_especificacion', 'EspecificacionController@actualizar_row_especificacion');
Route::post('especificacion/cambiar_estado', 'EspecificacionController@cambiar_estado');
Route::post('especificacion/seleccionar_variedad_especificacion', 'EspecificacionController@seleccionar_variedad_especificacion');
Route::post('especificacion/descargar_especificaciones', 'EspecificacionController@descargar_especificaciones');



