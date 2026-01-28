<?php

Route::get('resumen_verde', 'CRM\ResumenVerdeController@inicio');
Route::get('resumen_verde/buscar_resumen_verde', 'CRM\ResumenVerdeController@buscar_resumen_verde');
Route::post('resumen_verde/update_precio', 'CRM\ResumenVerdeController@update_precio');
Route::get('resumen_verde/listar_resumen_verde_semanal', 'CRM\ResumenVerdeController@listar_resumen_verde_semanal');