<?php

Route::get('costos_hora', 'Costos\CostoHorasController@inicio');
Route::post('costos_hora/store_costo_horas', 'Costos\CostoHorasController@store_costo_horas');
Route::post('costos_hora/update_costo_horas', 'Costos\CostoHorasController@update_costo_horas');