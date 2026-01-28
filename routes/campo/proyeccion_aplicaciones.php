<?php

Route::get('proyeccion_aplicaciones', 'Campo\proyAplicacionesController@inicio');
Route::get('proyeccion_aplicaciones/buscar_listado', 'Campo\proyAplicacionesController@buscar_listado');
Route::get('proyeccion_aplicaciones/select_celda', 'Campo\proyAplicacionesController@select_celda');
Route::get('proyeccion_aplicaciones/cargar_labor_semanal', 'Campo\proyAplicacionesController@cargar_labor_semanal');
Route::post('proyeccion_aplicaciones/cambiar_estado_labor', 'Campo\proyAplicacionesController@cambiar_estado_labor');
Route::post('proyeccion_aplicaciones/modificar_labor', 'Campo\proyAplicacionesController@modificar_labor');
Route::get('proyeccion_aplicaciones/add_adicional', 'Campo\proyAplicacionesController@add_adicional');
Route::post('proyeccion_aplicaciones/store_adicional', 'Campo\proyAplicacionesController@store_adicional');
Route::post('proyeccion_aplicaciones/cambiar_estado_labor_continua', 'Campo\proyAplicacionesController@cambiar_estado_labor_continua');
Route::post('proyeccion_aplicaciones/update_detalle_app', 'Campo\proyAplicacionesController@update_detalle_app');
