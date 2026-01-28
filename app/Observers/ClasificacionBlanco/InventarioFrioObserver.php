<?php

namespace yura\Observers\ClasificacionBlanco;

use yura\Jobs\Sincronizacion\ClasificacionBlanco\StoreInventarioFrio;
use yura\Modelos\InventarioFrio;

class InventarioFrioObserver
{
    public function saved(InventarioFrio $inventarioFrio){

        StoreInventarioFrio::dispatch($inventarioFrio)->onQueue('store_inventario_frio');

    }
}
