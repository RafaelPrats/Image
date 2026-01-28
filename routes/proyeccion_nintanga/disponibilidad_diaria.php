<?php

Route::get('disponibilidad_diaria', 'ProyNintanga\DisponibilidadDiariaController@inicio');
Route::get('disponibilidad_diaria/listar_reporte', 'ProyNintanga\DisponibilidadDiariaController@listar_reporte');
Route::post('disponibilidad_diaria/store_disponibilidad_diaria', 'ProyNintanga\DisponibilidadDiariaController@store_disponibilidad_diaria');
Route::get('disponibilidad_diaria/exportar_reporte', 'ProyNintanga\DisponibilidadDiariaController@exportar_reporte');
