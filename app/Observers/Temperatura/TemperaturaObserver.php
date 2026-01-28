<?php

namespace yura\Observers\Temperatura;

use yura\Jobs\Sincronizacion\Temperatura\Store;
use yura\Modelos\Temperatura;

class TemperaturaObserver
{
    public function saved(Temperatura $temperatura)
    {
        Store::dispatch($temperatura)->onQueue('store_temperatura');
    }
}
