<?php

Route::get('modificaciones_pedidos','ModificacionesPedidosController@inicio');
Route::get('modificaciones_pedidos/listar_reporte','ModificacionesPedidosController@listar_reporte');
Route::get('modificaciones_pedidos/exportar_reporte','ModificacionesPedidosController@exportar_reporte');
Route::post('modificaciones_pedidos/cambiar_uso','ModificacionesPedidosController@cambiar_uso');

