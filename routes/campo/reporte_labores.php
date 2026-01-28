<?php

Route::get('reporte_labores', 'Campo\ReporteLaboresController@inicio');
Route::get('reporte_labores/listar_reporte', 'Campo\ReporteLaboresController@listar_reporte');
Route::post('reporte_labores/seleccionar_tipo_labor', 'Campo\ReporteLaboresController@seleccionar_tipo_labor');