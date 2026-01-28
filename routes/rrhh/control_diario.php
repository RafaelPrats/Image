<?php

Route::get('control_diario', 'RRHH\rrhhControlDiarioController@inicio');
Route::get('control_diario/listar_grupo', 'RRHH\rrhhControlDiarioController@listar_grupo');
Route::get('control_diario/listar_grupo_general', 'RRHH\rrhhControlDiarioController@listar_grupo_general');
Route::post('control_diario/guardar_horario', 'RRHH\rrhhControlDiarioController@guardar_control_diario');
Route::get('control_diario/buscar_control_diario', 'RRHH\rrhhControlDiarioController@buscar_control_diario');
Route::get('control_diario/buscar_control_diario_general', 'RRHH\rrhhControlDiarioController@buscar_control_diario_general');
Route::post('control_diario/guardar_control_personal', 'RRHH\rrhhControlDiarioController@guardar_control_personal');
Route::post('control_diario/update_control_personal', 'RRHH\rrhhControlDiarioController@update_control_personal');
Route::post('control_diario/store_control_personal', 'RRHH\rrhhControlDiarioController@store_control_personal');
