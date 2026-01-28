<?php

Route::get('cuarto_frio', 'CuartoFrioController@inicio');
Route::get('cuarto_frio/listar_inventarios', 'CuartoFrioController@listar_inventarios');
Route::get('cuarto_frio/add_new_inventarios', 'CuartoFrioController@add_new_inventarios');
Route::post('cuarto_frio/store_new_inventario', 'CuartoFrioController@store_new_inventario');
Route::post('cuarto_frio/add_inventario', 'CuartoFrioController@add_inventario');
Route::post('cuarto_frio/edit_inventario', 'CuartoFrioController@edit_inventario');
Route::post('cuarto_frio/botar_inventario', 'CuartoFrioController@botar_inventario');
Route::post('cuarto_frio/delete_dia', 'CuartoFrioController@delete_dia');
Route::post('cuarto_frio/save_dia', 'CuartoFrioController@save_dia');
Route::get('cuarto_frio/exportar_inventarios', 'CuartoFrioController@exportar_inventarios');
