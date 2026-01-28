<?php

    Route::get('ingreso_guia_daes','GuiasDaesController@inicio');
    Route::get('ingreso_guia_daes/listado','GuiasDaesController@listado');
    Route::post('ingreso_guia_daes/actualiza_datos_envio','GuiasDaesController@actualiza_datos_envio');
