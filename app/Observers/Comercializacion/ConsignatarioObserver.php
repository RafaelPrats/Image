<?php

namespace yura\Observers\Comercializacion;

use yura\Jobs\Sincronizacion\Comercializacion\StoreConsignatario;
use yura\Modelos\Consignatario;

class ConsignatarioObserver
{
    public function saved(Consignatario $consignatario)
    {
        StoreConsignatario::dispatch($consignatario)->onQueue('store_consignatario');
    }
}
