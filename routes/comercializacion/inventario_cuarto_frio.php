<?php

Route::get('inventario_cuarto_frio', 'Comercializacion\InventarioCuartoFrioController@inicio');
Route::get('inventario_cuarto_frio/listar_reporte', 'Comercializacion\InventarioCuartoFrioController@listar_reporte');
Route::get('inventario_cuarto_frio/modal_inventario', 'Comercializacion\InventarioCuartoFrioController@modal_inventario');
Route::post('inventario_cuarto_frio/botar_inventario', 'Comercializacion\InventarioCuartoFrioController@botar_inventario');
Route::post('inventario_cuarto_frio/delete_inventario', 'Comercializacion\InventarioCuartoFrioController@delete_inventario');
Route::get('inventario_cuarto_frio/agregar_inventario', 'Comercializacion\InventarioCuartoFrioController@agregar_inventario');
Route::post('inventario_cuarto_frio/store_grabar_inventario', 'Comercializacion\InventarioCuartoFrioController@store_grabar_inventario');
Route::get('inventario_cuarto_frio/descargar_reporte', 'Comercializacion\InventarioCuartoFrioController@descargar_reporte');
