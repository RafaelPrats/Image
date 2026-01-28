<?php

Route::get('especificaciones', 'Comercializacion\EspecificacionesController@inicio');
Route::get('especificaciones/listar_reporte', 'Comercializacion\EspecificacionesController@listar_reporte');
Route::get('especificaciones/add_especificaciones', 'Comercializacion\EspecificacionesController@add_especificaciones');
Route::post('especificaciones/store_especificaciones', 'Comercializacion\EspecificacionesController@store_especificaciones');
Route::post('especificaciones/update_especificaciones', 'Comercializacion\EspecificacionesController@update_especificaciones');
Route::post('especificaciones/delete_especificaciones', 'Comercializacion\EspecificacionesController@delete_especificaciones');