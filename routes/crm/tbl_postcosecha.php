<?php

Route::get('tbl_postcosecha', 'CRM\tblPostcosechaController@inicio');
Route::get('tbl_postcosecha/filtrar_tablas', 'CRM\tblPostcosechaController@filtrar_tablas');
Route::get('tbl_postcosecha/exportar_tabla', 'CRM\tblPostcosechaController@exportar_tabla');
Route::get('tbl_postcosecha/select_planta_semanal', 'CRM\tblPostcosechaController@select_planta_semanal');
Route::get('tbl_postcosecha/select_planta_diario', 'CRM\tblPostcosechaController@select_planta_diario');
