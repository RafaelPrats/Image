<?php

namespace yura\Observers\Indicador;

use yura\Jobs\Sincronizacion\Indicador\StoreIndicadorSemana;
use yura\Modelos\IndicadorSemana;

class IndicadorSemanaObserver
{
    public function saved(IndicadorSemana $indicadorSemana)
    {
        StoreIndicadorSemana::dispatch($indicadorSemana)->onQueue('store_indicador_semana');
    }
}
