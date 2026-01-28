<?php

Route::get('resumen_plantas_madres', 'Propagacion\ResumenPtasMadresController@inicio');
Route::get('resumen_plantas_madres/listar_resumen', 'Propagacion\ResumenPtasMadresController@listar_resumen');
Route::get('resumen_plantas_madres/exportar_resumen', 'Propagacion\ResumenPtasMadresController@exportar_resumen');