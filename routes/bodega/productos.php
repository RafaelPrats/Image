<?php

Route::get('bodega_productos', 'Bodega\ProductosController@inicio');
Route::get('bodega_productos/listar_reporte', 'Bodega\ProductosController@listar_reporte');
Route::post('bodega_productos/update_producto', 'Bodega\ProductosController@update_producto');
Route::post('bodega_productos/cambiar_estado_producto', 'Bodega\ProductosController@cambiar_estado_producto');
Route::post('bodega_productos/store_producto', 'Bodega\ProductosController@store_producto');
