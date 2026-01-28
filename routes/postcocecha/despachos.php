<?php

Route::get('despachos', 'DespachosController@inicio');
Route::get('despachos/listar_resumen_pedidos', 'DespachosController@listar_resumen_pedidos');
Route::post('despachos/update_stock_empaquetado', 'DespachosController@update_stock_empaquetado');
Route::get('despachos/ver_envios', 'DespachosController@ver_envios');
Route::post('despachos/crear_despacho', 'DespachosController@crear_despacho');
Route::get('despachos/list_camiones_conductores', 'DespachosController@list_camiones_conductores');
Route::get('despachos/list_placa_camion', 'DespachosController@list_placa_camion');
Route::post('despachos/store_despacho', 'DespachosController@store_despacho');
Route::get('despachos/descargar_despacho/{id_despacho}', 'DespachosController@descargar_despacho');
Route::get('despachos/ver_despachos', 'DespachosController@ver_despachos');
Route::post('despachos/update_estado_despachos', 'DespachosController@update_estado_despachos');
Route::post('despachos/update_despacho_detalle', 'DespachosController@update_despacho_detalle');
Route::get('despachos/distribuir_despacho', 'DespachosController@distribuir_despacho');
Route::get('despachos/add_pedido_piezas', 'DespachosController@add_pedido_piezas');
Route::get('despachos/exportar_pedidos_despacho', 'DespachosController@exportar_pedidos_despacho');
Route::post('despachos/exportar_preparacion_flor', 'DespachosController@exportar_preparacion_flor');
Route::get('despachos/exportar_excel_flor_posco', 'DespachosController@exportar_excel_flor_posco');
Route::get('despachos/exportar_listado_pedidos_despacho', 'DespachosController@exportar_excel_listado_despacho');
Route::post('despachos/exportar_pedidos_despacho_cuarto_frio', 'DespachosController@exportar_pedidos_despacho_cuarto_frio');
Route::post('despachos/unificar_pedidos', 'DespachosController@unificar_pedidos');
Route::post('despachos/dividir_marcaciones', 'DespachosController@dividir_marcaciones');
Route::get('despachos/exportar_jire_cabecera', 'DespachosController@exportar_jire_cabecera');
Route::post('despachos/exportar_jire_detalle_cabecera', 'DespachosController@exportar_jire_detalle_cabecera');
Route::get('despachos/descargar_packings_unificados', 'DespachosController@descargar_packings_unificados');
Route::post('despachos/check_pending_processes', 'DespachosController@get_pedido_proceso');
