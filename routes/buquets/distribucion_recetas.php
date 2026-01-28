<?php

Route::get('distribucion_recetas', 'Buquets\DistribucionRecetaController@inicio');
Route::get('distribucion_recetas/listar_reporte', 'Buquets\DistribucionRecetaController@listar_reporte');
Route::get('distribucion_recetas/admin_recetaByPedido', 'Buquets\DistribucionRecetaController@admin_recetaByPedido');
Route::post('distribucion_recetas/store_agregar_variedades', 'Buquets\DistribucionRecetaController@store_agregar_variedades');