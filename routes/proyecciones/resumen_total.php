<?php

Route::get('proy_resumen_total', 'Proyecciones\proyResumenTotalController@inicio');
Route::get('proy_resumen_total/listar_resumen_total', 'Proyecciones\proyResumenTotalController@listarProyecionResumenTotal');
Route::get('proy_resumen_total/exportar_reporte', 'Proyecciones\proyResumenTotalController@exportar_reporte');
