<?php

namespace yura\Observers\Indicador;

use yura\Jobs\Sincronizacion\Indicador\StoreIndicadorVariedad;
use yura\Modelos\IndicadorVariedad;

class IndicadorVariedadObserver
{
    public function saved(IndicadorVariedad $indicadorVariedad)
    {
        //StoreIndicadorVariedad::dispatch($indicadorVariedad)->onQueue('store_indicador_variedad');
    }
}
