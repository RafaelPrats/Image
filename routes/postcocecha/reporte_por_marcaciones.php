<?php

Route::get('reporte_por_marcaciones', 'ReportePorMarcacionController@inicio');
Route::get('reporte_por_marcaciones/listar_filtros', 'ReportePorMarcacionController@listar_filtros');
Route::get('reporte_por_marcaciones/listar_combinaciones', 'ReportePorMarcacionController@listar_combinaciones');
