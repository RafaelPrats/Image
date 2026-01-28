<?php

namespace yura\Observers\Indicador;

use yura\Jobs\Sincronizacion\Indicador\StoreIndicador;
use yura\Modelos\Indicador;

class IndicadorObserver
{
    public function saved(Indicador $Indicador)
    {
        info('se activo el observer indicador');
       // StoreIndicador::dispatch($Indicador)->onQueue('store_indicador');
    }
}
