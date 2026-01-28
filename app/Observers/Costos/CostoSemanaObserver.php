<?php

namespace yura\Observers\Costos;

use yura\Jobs\Sincronizacion\Costos\StoreCostoSemana;
use yura\Modelos\CostosSemana;

class CostoSemanaObserver
{
    public function saved(CostosSemana $costoSemana)
    {
        StoreCostoSemana::dispatch($costoSemana)->onQueue('store_costo_semana');
    }
}
