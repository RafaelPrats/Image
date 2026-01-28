<?php

Route::get('unidad_medida', 'UnidadMedidaController@inicio');
Route::get('unidad_medida/buscar_listado', 'UnidadMedidaController@buscar_listado');
Route::post('unidad_medida/store_unidad', 'UnidadMedidaController@store_unidad');
Route::post('unidad_medida/update_unidad', 'UnidadMedidaController@update_unidad');
