<?php

Route::get('cosecha_estimada', 'CosechaEstimadaController@inicio');
Route::get('cosecha_estimada/buscar_cosecha_estimada', 'CosechaEstimadaController@buscar_cosecha_estimada');
Route::get('cosecha_estimada/buscar_cosecha_estimada_new', 'CosechaEstimadaController@buscar_cosecha_estimada_new');
Route::get('cosecha_estimada/exportar_reporte', 'CosechaEstimadaController@exportar_reporte');
