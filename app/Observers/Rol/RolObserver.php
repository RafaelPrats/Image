<?php

namespace yura\Observers\Rol;

use yura\Modelos\Rol;
use yura\Jobs\Sincronizacion\Rol\Store;

class RolObserver
{
    public function saved(Rol $rol)
    {
        Store::dispatch($rol)->onQueue('store_rol');
    }
}
