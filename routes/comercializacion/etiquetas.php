<?php

Route::get('etiquetas', 'Comercializacion\EtiquetasController@inicio');
Route::get('etiquetas/listar_reporte', 'Comercializacion\EtiquetasController@listar_reporte');
Route::get('etiquetas/descargar_etiqueta', 'Comercializacion\EtiquetasController@descargar_etiqueta');
Route::get('etiquetas/descargar_etiquetas_all', 'Comercializacion\EtiquetasController@descargar_etiquetas_all');
