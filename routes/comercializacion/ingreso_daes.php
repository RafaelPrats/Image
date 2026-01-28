<?php

Route::get('ingreso_daes', 'Comercializacion\IngresoDaesController@inicio');
Route::get('ingreso_daes/listar_reporte', 'Comercializacion\IngresoDaesController@listar_reporte');
Route::post('ingreso_daes/update_daes', 'Comercializacion\IngresoDaesController@update_daes');
