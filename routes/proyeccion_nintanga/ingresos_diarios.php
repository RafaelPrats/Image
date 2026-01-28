<?php

Route::get('ingresos_proy', 'ProyNintanga\IngresosController@inicio');
Route::get('ingresos_proy/listar_formulario', 'ProyNintanga\IngresosController@listar_formulario');
Route::post('ingresos_proy/grabar_proy', 'ProyNintanga\IngresosController@grabar_proy');
Route::get('ingresos_proy/exportar_reporte', 'ProyNintanga\IngresosController@exportar_reporte');
Route::post('ingresos_proy/cambiar_uso_corte', 'ProyNintanga\IngresosController@cambiar_uso_corte');
Route::post('ingresos_proy/update_factor_conversion', 'ProyNintanga\IngresosController@update_factor_conversion');
