<?php

namespace yura\Observers\Indicador;

use yura\Jobs\Sincronizacion\Indicador\StoreIndicadorVariedadSemana;
use yura\Modelos\IndicadorVariedadSemana;

class IndicadorVariedadSemanaObserver
{
    public function saved(IndicadorVariedadSemana $indicadorVariedadSemana)
    {
        StoreIndicadorVariedadSemana::dispatch($indicadorVariedadSemana)->onQueue('store_indicador_variedad_semana');
    }
}
