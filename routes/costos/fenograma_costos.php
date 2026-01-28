<?php

Route::get('fenograma_costos', 'Costos\FenogramaCostosController@inicio');
Route::get('fenograma_costos/filtrar_ciclos', 'Costos\FenogramaCostosController@filtrar_ciclos');
Route::get('fenograma_costos/exportar_reporte', 'Costos\FenogramaCostosController@exportar_reporte');
Route::get('fenograma_costos/ver_labores_giberelico', 'Costos\FenogramaCostosController@ver_labores_giberelico');
Route::get('fenograma_costos/ver_labores_desbrote', 'Costos\FenogramaCostosController@ver_labores_desbrote');