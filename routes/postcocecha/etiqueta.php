<?php

    Route::get('etiqueta','EtiquetaController@inicio');
    Route::get('etiqueta/listado','EtiquetaController@listado');
    Route::post('etiqueta/exportar_excel','EtiquetaController@exportar_excel');
    Route::post('etiqueta/update_etiqueta_descargada','EtiquetaController@update_etiqueta_descargada');
    Route::post('etiqueta/imprimir_etiqueta','EtiquetaController@imprimir_etiqueta');
    Route::post('etiqueta/ver_pdf','EtiquetaController@ver_etiqueta');
    Route::get('etiqueta/pdf_etiqueta','EtiquetaController@pdf_etiqueta');
    Route::get('etiqueta/descargar_all_packings','EtiquetaController@descargar_all_packings');
