<?php

Route::get('postcosecha', 'Comercializacion\PostcosechaController@inicio');
Route::get('postcosecha/listar_reporte', 'Comercializacion\PostcosechaController@listar_reporte');
Route::post('postcosecha/buscar_presentaciones', 'Comercializacion\PostcosechaController@buscar_presentaciones');
Route::get('postcosecha/distribuir_trabajo', 'Comercializacion\PostcosechaController@distribuir_trabajo');
Route::post('postcosecha/store_distribucion', 'Comercializacion\PostcosechaController@store_distribucion');
Route::post('postcosecha/delete_distribucion', 'Comercializacion\PostcosechaController@delete_distribucion');
Route::get('postcosecha/actualizar_row', 'Comercializacion\PostcosechaController@actualizar_row');
Route::get('postcosecha/modal_armar_row', 'Comercializacion\PostcosechaController@modal_armar_row');
Route::post('postcosecha/store_armar_row', 'Comercializacion\PostcosechaController@store_armar_row');
Route::get('postcosecha/modal_inventario', 'Comercializacion\PostcosechaController@modal_inventario');
Route::post('postcosecha/store_cambios', 'Comercializacion\PostcosechaController@store_cambios');
Route::post('postcosecha/confirmar_pedidos', 'Comercializacion\PostcosechaController@confirmar_pedidos');
Route::get('postcosecha/ver_cambios', 'Comercializacion\PostcosechaController@ver_cambios');
Route::get('postcosecha/ver_inventario', 'Comercializacion\PostcosechaController@ver_inventario');
Route::post('postcosecha/update_marcacion_inventario', 'Comercializacion\PostcosechaController@update_marcacion_inventario');
