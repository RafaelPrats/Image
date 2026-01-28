<?php

Route::get('clasificadores', 'Postcosecha\ClasificadoresController@inicio');
Route::get('clasificadores/listar_reporte', 'Postcosecha\ClasificadoresController@listar_reporte');
Route::post('clasificadores/update_clasificador', 'Postcosecha\ClasificadoresController@update_clasificador');
Route::post('clasificadores/cambiar_estado_clasificador', 'Postcosecha\ClasificadoresController@cambiar_estado_clasificador');
Route::post('clasificadores/store_clasificador', 'Postcosecha\ClasificadoresController@store_clasificador');
