<?php

Route::get('distribucion_mixtos', 'Comercializacion\DistribucionMixtosController@inicio');
Route::get('distribucion_mixtos/listar_reporte', 'Comercializacion\DistribucionMixtosController@listar_reporte');
Route::post('distribucion_mixtos/store_distribucion_mixtos_diaria', 'Comercializacion\DistribucionMixtosController@store_distribucion_mixtos_diaria');
Route::post('distribucion_mixtos/duplicar_distribucion', 'Comercializacion\DistribucionMixtosController@duplicar_distribucion');
Route::get('distribucion_mixtos/distribuir_mixtos', 'Comercializacion\DistribucionMixtosController@distribuir_mixtos');
Route::post('distribucion_mixtos/store_distribucion', 'Comercializacion\DistribucionMixtosController@store_distribucion');
Route::post('distribucion_mixtos/eliminar_distribuciones', 'Comercializacion\DistribucionMixtosController@eliminar_distribuciones');
