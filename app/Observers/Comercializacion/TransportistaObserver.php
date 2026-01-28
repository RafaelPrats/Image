<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreTransportista;
use yura\Modelos\Transportista;

class TransportistaObserver
{
    public function saved(Transportista $transportista)
    {
        StoreTransportista::dispatch($transportista)->onQueue('store_transportista');
    }
}
