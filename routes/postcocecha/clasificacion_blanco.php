<?php

Route::get('clasificacion_blanco', 'ClasificacionBlancoController@inicio');
Route::get('clasificacion_blanco/listar_clasificacion_blanco', 'ClasificacionBlancoController@listar_clasificacion_blanco');
Route::post('clasificacion_blanco/confirmar_pedidos', 'ClasificacionBlancoController@confirmar_pedidos');
Route::post('clasificacion_blanco/store_armar', 'ClasificacionBlancoController@store_armar');
Route::post('clasificacion_blanco/store_armar_row', 'ClasificacionBlancoController@store_armar_row');
Route::get('clasificacion_blanco/maduracion', 'ClasificacionBlancoController@maduracion');
Route::post('clasificacion_blanco/update_inventario', 'ClasificacionBlancoController@update_inventario');
Route::post('clasificacion_blanco/update_calsificacion_blanco', 'ClasificacionBlancoController@update_calsificacion_blanco');
Route::post('clasificacion_blanco/store_blanco', 'ClasificacionBlancoController@store_blanco');
Route::get('clasificacion_blanco/ver_rendimiento', 'ClasificacionBlancoController@ver_rendimiento');
Route::get('clasificacion_blanco/rendimiento_mesas', 'ClasificacionBlancoController@rendimiento_mesas');
Route::get('clasificacion_blanco/listar_combinaciones', 'ClasificacionBlancoController@listar_combinaciones');
Route::get('clasificacion_blanco/exportar_reporte', 'ClasificacionBlancoController@exportar_reporte');
Route::get('clasificacion_blanco/ver_receta', 'ClasificacionBlancoController@ver_receta');
Route::get('clasificacion_blanco/ver_cambios', 'ClasificacionBlancoController@ver_cambios');
Route::get('clasificacion_blanco/listar_combinaciones_row', 'ClasificacionBlancoController@listar_combinaciones_row');
Route::get('clasificacion_blanco/modal_armar_row', 'ClasificacionBlancoController@modal_armar_row');
Route::get('clasificacion_blanco/exportar_combinaciones', 'ClasificacionBlancoController@exportar_combinaciones');
Route::get('clasificacion_blanco/distribuir_trabajo', 'ClasificacionBlancoController@distribuir_trabajo');
Route::post('clasificacion_blanco/store_distribucion', 'ClasificacionBlancoController@store_distribucion');
Route::post('clasificacion_blanco/delete_distribucion', 'ClasificacionBlancoController@delete_distribucion');
Route::get('clasificacion_blanco/modal_inventario_row', 'ClasificacionBlancoController@modal_inventario_row');
Route::post('clasificacion_blanco/update_marcacion_inventario', 'ClasificacionBlancoController@update_marcacion_inventario');
Route::get('clasificacion_blanco/modal_inventario_color', 'ClasificacionBlancoController@modal_inventario_color');
Route::post('clasificacion_blanco/store_cambiar_presentacion', 'ClasificacionBlancoController@store_cambiar_presentacion');
