<?php

namespace yura\Observers\Semana;

use yura\Modelos\Semana;
use yura\Jobs\Sincronizacion\Semana\Store;

class SemanaObserver
{
    public function saved(Semana $semana)
    {
        Store::dispatch($semana)->onQueue('store_semana');
    }
}
