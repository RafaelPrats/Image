<?php

Route::get('proyeccion_semana', 'ProyNintanga\ProyeccionesSemanaController@inicio');
Route::get('proyeccion_semana/listar_formulario', 'ProyNintanga\ProyeccionesSemanaController@listar_formulario');
Route::post('proyeccion_semana/grabar_proy', 'ProyNintanga\ProyeccionesSemanaController@grabar_proy');
Route::get('proyeccion_semana/exportar_reporte', 'ProyNintanga\ProyeccionesSemanaController@exportar_reporte');
Route::post('proyeccion_semana/add_semana', 'ProyNintanga\ProyeccionesSemanaController@add_semana');
Route::get('proyeccion_semana/add_longitudes', 'ProyNintanga\ProyeccionesSemanaController@add_longitudes');
Route::post('proyeccion_semana/store_longitud', 'ProyNintanga\ProyeccionesSemanaController@store_longitud');
Route::post('proyeccion_semana/update_longitud', 'ProyNintanga\ProyeccionesSemanaController@update_longitud');
Route::post('proyeccion_semana/delete_longitud', 'ProyNintanga\ProyeccionesSemanaController@delete_longitud');
