<?php

namespace yura\Observers\UnidadMedida;

use yura\Jobs\Sincronizacion\UnidadMedida\Store;
use yura\Modelos\UnidadMedida;

class UnidadMedidaObserver
{
    public function saved(UnidadMedida $unidadMedida)
    {
        Store::dispatch($unidadMedida)->onQueue('store_unidad_medida');
    }
}
