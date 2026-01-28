<?php

Route::get('desechos_frio', 'DesechosCuartoFrioController@inicio');
Route::get('desechos_frio/listar_reporte', 'DesechosCuartoFrioController@listar_reporte');
Route::get('desechos_frio/exportar_reporte', 'DesechosCuartoFrioController@exportar_reporte');
