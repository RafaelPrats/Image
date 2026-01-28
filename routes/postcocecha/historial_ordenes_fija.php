<?php

Route::get('historial_ordenes_fija', 'HistorialOrdenFijaController@inicio');
Route::get('historial_ordenes_fija/listar_reporte', 'HistorialOrdenFijaController@listar_reporte');
Route::get('historial_ordenes_fija/ver_toda_orden', 'HistorialOrdenFijaController@ver_toda_orden');
Route::get('historial_ordenes_fija/agregar_nueva_fecha', 'HistorialOrdenFijaController@agregar_nueva_fecha');
Route::post('historial_ordenes_fija/store_agregar_nueva_fecha', 'HistorialOrdenFijaController@store_agregar_nueva_fecha');
Route::post('historial_ordenes_fija/eliminar_pedido_orden_fija', 'HistorialOrdenFijaController@eliminar_pedido_orden_fija');
Route::post('historial_ordenes_fija/update_orden_fija', 'HistorialOrdenFijaController@update_orden_fija');
Route::post('historial_ordenes_fija/store_renovacion', 'HistorialOrdenFijaController@store_renovacion');
