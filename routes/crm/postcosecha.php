<?php

Route::get('crm_postcosecha', 'CRM\crmPostocechaController@inicio');
Route::get('crm_postcosecha/listar_graficas', 'CRM\crmPostocechaController@listar_graficas');
Route::get('crm_postcosecha/listar_ranking', 'CRM\crmPostocechaController@listar_ranking');
