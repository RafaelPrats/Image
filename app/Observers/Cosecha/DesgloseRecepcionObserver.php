<?php

namespace yura\Observers\Cosecha;

use yura\Jobs\Sincronizacion\Cosecha\{
    StoreDesgloseRecepcion, DeleteDesgloseRecepcion
};
use yura\Modelos\DesgloseRecepcion;

class DesgloseRecepcionObserver
{
    public function saved(DesgloseRecepcion $desgloseRecepcion)
    {
        StoreDesgloseRecepcion::dispatch($desgloseRecepcion)->onQueue('store_desglose_recepcion');
    }

    public function deleted(DesgloseRecepcion $desgloseRecepcion)
    {
        DeleteDesgloseRecepcion::dispatch($desgloseRecepcion)->onQueue('delete_desglose_recepcion');
    }
}
