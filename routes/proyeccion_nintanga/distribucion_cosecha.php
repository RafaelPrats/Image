<?php

Route::get('distribucion_cosecha', 'ProyNintanga\DistribucionCosechaController@inicio');
Route::get('distribucion_cosecha/listar_reporte', 'ProyNintanga\DistribucionCosechaController@listar_reporte');
Route::get('distribucion_cosecha/distribuir_mixtos', 'ProyNintanga\DistribucionCosechaController@distribuir_mixtos');
Route::post('distribucion_cosecha/store_distribucion', 'ProyNintanga\DistribucionCosechaController@store_distribucion');
Route::post('distribucion_cosecha/store_distribucion_mixtos_diaria', 'ProyNintanga\DistribucionCosechaController@store_distribucion_mixtos_diaria');
Route::post('distribucion_cosecha/duplicar_distribucion', 'ProyNintanga\DistribucionCosechaController@duplicar_distribucion');
Route::get('distribucion_cosecha/get_distribuciones_pendientes', 'ProyNintanga\DistribucionCosechaController@get_distribuciones_pendientes');
Route::post('distribucion_cosecha/eliminar_distribuciones', 'ProyNintanga\DistribucionCosechaController@eliminar_distribuciones');
