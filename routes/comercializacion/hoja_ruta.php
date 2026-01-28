<?php

Route::get('hoja_ruta', 'Comercializacion\HojaRutaController@inicio');
Route::get('hoja_ruta/listar_reporte', 'Comercializacion\HojaRutaController@listar_reporte');
Route::get('hoja_ruta/crear_despacho', 'Comercializacion\HojaRutaController@crear_despacho');
Route::post('hoja_ruta/seleccionar_transportista', 'Comercializacion\HojaRutaController@seleccionar_transportista');
Route::post('hoja_ruta/seleccionar_fecha', 'Comercializacion\HojaRutaController@seleccionar_fecha');
Route::post('hoja_ruta/store_despacho', 'Comercializacion\HojaRutaController@store_despacho');
Route::get('hoja_ruta/agregar_a_despacho', 'Comercializacion\HojaRutaController@agregar_a_despacho');
Route::post('hoja_ruta/agregar_a_despacho_confirmar', 'Comercializacion\HojaRutaController@agregar_a_despacho_confirmar');
Route::get('hoja_ruta/cambiar_a_despacho', 'Comercializacion\HojaRutaController@cambiar_a_despacho');
Route::post('hoja_ruta/cambiar_a_despacho_confirmar', 'Comercializacion\HojaRutaController@cambiar_a_despacho_confirmar');
Route::get('hoja_ruta/ver_despachos', 'Comercializacion\HojaRutaController@ver_despachos');
Route::get('hoja_ruta/ver_hoja_ruta', 'Comercializacion\HojaRutaController@ver_hoja_ruta');
Route::post('hoja_ruta/update_despacho', 'Comercializacion\HojaRutaController@update_despacho');
Route::get('hoja_ruta/exportar_despacho', 'Comercializacion\HojaRutaController@exportar_despacho');
Route::post('hoja_ruta/delete_despacho', 'Comercializacion\HojaRutaController@delete_despacho');
Route::get('hoja_ruta/descargar_flor_postco', 'Comercializacion\HojaRutaController@descargar_flor_postco');
Route::get('hoja_ruta/descargar_disponibilidad', 'Comercializacion\HojaRutaController@descargar_disponibilidad');
