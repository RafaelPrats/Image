<?php

namespace yura\Observers\Cosecha;

use yura\Jobs\Sincronizacion\Cosecha\DeleteRecepcion;
use yura\Jobs\Sincronizacion\Cosecha\StoreRecepcion;
use yura\Modelos\Recepcion;

class RecepecionObserver
{
    public function saved(Recepcion $recepcion)
    {
        StoreRecepcion::dispatch($recepcion)->onQueue('store_recepcion');
    }

    public function deleted(Recepcion $recepcion)
    {
        DeleteRecepcion::dispatch($recepcion)->onQueue('delete_recepcion');
    }
}
