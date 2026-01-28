<?php

Route::get('importar_ventas', 'ImportarVentaController@inicio');
Route::get('importar_ventas/descargar_plantilla', 'ImportarVentaController@descargar_plantilla');
Route::post('importar_ventas/importar_file', 'ImportarVentaController@importar_file');
