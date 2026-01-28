<?php

Route::get('distribucion_semana', 'ProyNintanga\DistribucionSemanaController@inicio');
Route::get('distribucion_semana/listar_formulario', 'ProyNintanga\DistribucionSemanaController@listar_formulario');
Route::post('distribucion_semana/update_dias_cosecha_semana', 'ProyNintanga\DistribucionSemanaController@update_dias_cosecha_semana');
Route::post('distribucion_semana/store_distribucion', 'ProyNintanga\DistribucionSemanaController@store_distribucion');
Route::get('distribucion_semana/seleccionar_planta', 'ProyNintanga\DistribucionSemanaController@seleccionar_planta');
Route::get('distribucion_semana/exportar_reporte', 'ProyNintanga\DistribucionSemanaController@exportar_reporte');
